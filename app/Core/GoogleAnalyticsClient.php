<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Client minimaliste pour l'API GA4 Data (Google Analytics), sans
 * dépendance Composer (le projet reste 100 % PHP natif), qui appelle
 * l'endpoint runReport après authentification.
 *
 * Authentification : OAuth 2.0 "utilisateur" — un identifiant client Web
 * (Google Cloud Console → Identifiants) associé à un refresh_token obtenu
 * une fois pour toutes via la connexion admin depuis /admin/parametres
 * (voir AdminGoogleOAuthController). Toute la configuration vit dans le
 * fichier .env du serveur (jamais en base de données, jamais dans un
 * fichier JSON versionné) :
 *
 * - GA4_PROPERTY_ID          : identifiant numérique de la propriété GA4
 * - GA4_OAUTH_CLIENT_ID      : client_id de l'identifiant OAuth Web
 * - GA4_OAUTH_CLIENT_SECRET  : client_secret de ce même identifiant
 * - GA4_OAUTH_REFRESH_TOKEN  : obtenu (et réécrit dans .env) automatiquement
 *                              par AdminGoogleOAuthController::callback()
 */
class GoogleAnalyticsClient
{
    private const TOKEN_URL = 'https://oauth2.googleapis.com/token';
    private const API_BASE  = 'https://analyticsdata.googleapis.com/v1beta';
    private const SCOPE     = 'https://www.googleapis.com/auth/analytics.readonly';

    /** Jeton d'accès mis en cache le temps de la requête HTTP (plusieurs
     *  runReport() sont appelés par page), jamais persisté sur disque. */
    private static ?string $cachedAccessToken = null;

    private string $propertyId;
    private string $clientId;
    private string $clientSecret;
    private string $refreshToken;

    private function __construct(string $propertyId, string $clientId, string $clientSecret, string $refreshToken)
    {
        $this->propertyId   = 'properties/' . preg_replace('/^properties\//', '', $propertyId);
        $this->clientId     = $clientId;
        $this->clientSecret = $clientSecret;
        $this->refreshToken = $refreshToken;
    }

    /**
     * Construit le client à partir des variables d'environnement (.env).
     * Renvoie null si la configuration est absente ou incomplète (le
     * contrôleur doit alors afficher un message d'invite à configurer).
     */
    public static function fromSettings(): ?self
    {
        $propertyId   = trim((string) getenv('GA4_PROPERTY_ID'));
        $clientId     = trim((string) getenv('GA4_OAUTH_CLIENT_ID'));
        $clientSecret = trim((string) getenv('GA4_OAUTH_CLIENT_SECRET'));
        $refreshToken = trim((string) getenv('GA4_OAUTH_REFRESH_TOKEN'));

        if ($propertyId === '' || $clientId === '' || $clientSecret === '' || $refreshToken === '') {
            return null;
        }

        return new self($propertyId, $clientId, $clientSecret, $refreshToken);
    }

    /**
     * Échange un code d'autorisation (reçu sur /callback/google/analytics)
     * contre un refresh_token, puis le persiste dans .env. Renvoie false si
     * l'échange échoue ou si Google ne renvoie pas de refresh_token (arrive
     * si l'utilisateur a déjà autorisé l'appli sans `prompt=consent`).
     */
    public static function exchangeAuthorizationCode(string $code, string $redirectUri): bool
    {
        $clientId     = trim((string) getenv('GA4_OAUTH_CLIENT_ID'));
        $clientSecret = trim((string) getenv('GA4_OAUTH_CLIENT_SECRET'));
        if ($clientId === '' || $clientSecret === '') {
            error_log('[GoogleAnalyticsClient] GA4_OAUTH_CLIENT_ID / GA4_OAUTH_CLIENT_SECRET absents de .env.');
            return false;
        }

        $response = self::httpPost(self::TOKEN_URL, [
            'grant_type'    => 'authorization_code',
            'code'          => $code,
            'client_id'     => $clientId,
            'client_secret' => $clientSecret,
            'redirect_uri'  => $redirectUri,
        ], ['Content-Type: application/x-www-form-urlencoded']);

        $data = json_decode($response['body'] ?? '', true);
        if ($response['status'] !== 200 || empty($data['refresh_token'])) {
            error_log('[GoogleAnalyticsClient] Échec de l\'échange du code d\'autorisation OAuth : ' . ($response['body'] ?? 'réponse vide'));
            return false;
        }

        self::persistEnvValue('GA4_OAUTH_REFRESH_TOKEN', $data['refresh_token']);
        self::$cachedAccessToken = null;

        return true;
    }

    /**
     * Révoque la connexion OAuth locale (efface le refresh_token de .env).
     * N'invalide pas le jeton côté Google — l'admin peut aussi le faire
     * depuis https://myaccount.google.com/permissions.
     */
    public static function disconnectOAuth(): void
    {
        self::persistEnvValue('GA4_OAUTH_REFRESH_TOKEN', '');
        self::$cachedAccessToken = null;
    }

    public static function isOAuthAuthorized(): bool
    {
        return trim((string) getenv('GA4_OAUTH_REFRESH_TOKEN')) !== '';
    }

    public static function hasOAuthClient(): bool
    {
        return trim((string) getenv('GA4_OAUTH_CLIENT_ID')) !== ''
            && trim((string) getenv('GA4_OAUTH_CLIENT_SECRET')) !== '';
    }

    /**
     * Valeur actuelle de GA4_PROPERTY_ID (pour affichage en lecture seule
     * dans /admin/parametres), ou null si non définie.
     */
    public static function propertyId(): ?string
    {
        $value = trim((string) getenv('GA4_PROPERTY_ID'));
        return $value !== '' ? $value : null;
    }

    /**
     * Récupère un jeton d'accès valide (renouvelé via le refresh_token),
     * mis en cache en mémoire pour la durée de la requête HTTP courante.
     */
    private function accessToken(): ?string
    {
        if (self::$cachedAccessToken !== null) {
            return self::$cachedAccessToken;
        }

        $response = self::httpPost(self::TOKEN_URL, [
            'grant_type'    => 'refresh_token',
            'refresh_token' => $this->refreshToken,
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
        ], ['Content-Type: application/x-www-form-urlencoded']);

        $data = json_decode($response['body'] ?? '', true);
        if ($response['status'] !== 200 || empty($data['access_token'])) {
            error_log('[GoogleAnalyticsClient] Échec du renouvellement du jeton OAuth : ' . ($response['body'] ?? 'réponse vide'));
            return null;
        }

        return self::$cachedAccessToken = $data['access_token'];
    }

    /**
     * Exécute un rapport GA4 (runReport) et renvoie le résultat brut décodé,
     * ou null en cas d'échec (jeton, réseau, propriété invalide...).
     */
    public function runReport(array $body): ?array
    {
        $token = $this->accessToken();
        if ($token === null) {
            return null;
        }

        $response = self::httpPost(
            self::API_BASE . '/' . $this->propertyId . ':runReport',
            json_encode($body),
            ['Content-Type: application/json', 'Authorization: Bearer ' . $token]
        );

        if ($response['status'] !== 200) {
            error_log('[GoogleAnalyticsClient] Échec runReport (' . $response['status'] . ') : ' . ($response['body'] ?? ''));
            return null;
        }

        return json_decode($response['body'], true);
    }

    /**
     * Requête cURL générique en POST, avec corps déjà encodé (string) ou
     * tableau à encoder en x-www-form-urlencoded.
     */
    private static function httpPost(string $url, $body, array $headers): array
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($body) ? http_build_query($body) : $body);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $responseBody = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($responseBody === false) {
            error_log('[GoogleAnalyticsClient] Erreur cURL : ' . curl_error($ch));
        }

        return ['status' => $status, 'body' => $responseBody];
    }

    /**
     * Met à jour (ou ajoute) une variable dans le fichier .env du serveur,
     * et reflète immédiatement le changement dans le process courant
     * (getenv/$_ENV) pour que la requête en cours en tienne compte sans
     * attendre le prochain redémarrage.
     */
    private static function persistEnvValue(string $key, string $value): void
    {
        $envPath = dirname(__DIR__, 2) . '/.env';
        if (!is_file($envPath)) {
            error_log('[GoogleAnalyticsClient] Fichier .env introuvable, impossible d\'enregistrer ' . $key);
            return;
        }

        $lines = file($envPath, FILE_IGNORE_NEW_LINES);
        $found = false;

        foreach ($lines as $i => $line) {
            if (preg_match('/^\s*' . preg_quote($key, '/') . '\s*=/', $line)) {
                $lines[$i] = $key . '=' . $value;
                $found = true;
                break;
            }
        }

        if (!$found) {
            $lines[] = $key . '=' . $value;
        }

        file_put_contents($envPath, implode(PHP_EOL, $lines) . PHP_EOL);

        putenv($key . '=' . $value);
        $_ENV[$key] = $value;
    }
}
