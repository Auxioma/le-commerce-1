<?php

namespace App\Core;

use App\Models\Settings;

/**
 * Client minimaliste pour l'API GA4 Data (Google Analytics), sans
 * dépendance Composer (le projet reste 100 % PHP natif) : génère un JWT
 * signé RS256 à partir du compte de service, l'échange contre un jeton
 * d'accès OAuth2, puis appelle l'endpoint runReport.
 *
 * Configuration attendue dans /admin/parametres (table `settings`) :
 * - ga4_property_id           : identifiant numérique de la propriété GA4
 *                               (ex: "properties/123456789" ou juste "123456789")
 * - ga4_service_account_json  : contenu JSON complet du fichier de clé du
 *                               compte de service Google (rôle "Lecteur"
 *                               sur la propriété GA4 concernée)
 */
class GoogleAnalyticsClient
{
    private const TOKEN_URL  = 'https://oauth2.googleapis.com/token';
    private const API_BASE   = 'https://analyticsdata.googleapis.com/v1beta';
    private const SCOPE      = 'https://www.googleapis.com/auth/analytics.readonly';
    private const CACHE_FILE = 'ga4_access_token.json';

    private string $propertyId;
    private array $credentials;

    private function __construct(string $propertyId, array $credentials)
    {
        $this->propertyId  = 'properties/' . preg_replace('/^properties\//', '', $propertyId);
        $this->credentials = $credentials;
    }

    /**
     * Construit le client à partir des paramètres enregistrés en base.
     * Renvoie null si la configuration est absente ou invalide (le
     * contrôleur doit alors afficher un message d'invite à configurer).
     */
    public static function fromSettings(): ?self
    {
        $propertyId = trim((string) Settings::get('ga4_property_id', ''));
        $json       = trim((string) Settings::get('ga4_service_account_json', ''));

        if ($propertyId === '' || $json === '') {
            return null;
        }

        $credentials = json_decode($json, true);
        if (!is_array($credentials) || empty($credentials['private_key']) || empty($credentials['client_email'])) {
            return null;
        }

        return new self($propertyId, $credentials);
    }

    /**
     * Récupère un jeton d'accès valide, depuis le cache disque si non
     * expiré, sinon en négociant un nouveau jeton auprès de Google.
     */
    private function accessToken(): ?string
    {
        $cachePath = dirname(__DIR__, 2) . '/storage/logs/' . self::CACHE_FILE;

        if (is_file($cachePath)) {
            $cached = json_decode((string) file_get_contents($cachePath), true);
            if (is_array($cached) && ($cached['expires_at'] ?? 0) > time() + 30) {
                return $cached['access_token'];
            }
        }

        $jwt = $this->buildJwt();
        if ($jwt === null) {
            return null;
        }

        $response = $this->httpPost(self::TOKEN_URL, [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion'  => $jwt,
        ], ['Content-Type: application/x-www-form-urlencoded']);

        $data = json_decode($response['body'] ?? '', true);
        if ($response['status'] !== 200 || empty($data['access_token'])) {
            error_log('[GoogleAnalyticsClient] Échec récupération du jeton OAuth2 : ' . ($response['body'] ?? 'réponse vide'));
            return null;
        }

        file_put_contents($cachePath, json_encode([
            'access_token' => $data['access_token'],
            'expires_at'   => time() + (int) ($data['expires_in'] ?? 3600),
        ]));

        return $data['access_token'];
    }

    /**
     * Construit et signe (RS256) le JWT nécessaire au flux
     * "JWT Bearer Token" OAuth2 pour compte de service Google.
     */
    private function buildJwt(): ?string
    {
        $now = time();
        $header = ['alg' => 'RS256', 'typ' => 'JWT'];
        $claims = [
            'iss'   => $this->credentials['client_email'],
            'scope' => self::SCOPE,
            'aud'   => self::TOKEN_URL,
            'exp'   => $now + 3600,
            'iat'   => $now,
        ];

        $segments = [
            self::base64UrlEncode(json_encode($header)),
            self::base64UrlEncode(json_encode($claims)),
        ];
        $signingInput = implode('.', $segments);

        $privateKey = openssl_pkey_get_private($this->credentials['private_key']);
        if ($privateKey === false) {
            error_log('[GoogleAnalyticsClient] Clé privée du compte de service invalide.');
            return null;
        }

        $signature = '';
        $ok = openssl_sign($signingInput, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        if (!$ok) {
            error_log('[GoogleAnalyticsClient] Échec de la signature JWT.');
            return null;
        }

        $segments[] = self::base64UrlEncode($signature);
        return implode('.', $segments);
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

        $response = $this->httpPost(
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
    private function httpPost(string $url, $body, array $headers): array
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
        curl_close($ch);

        return ['status' => $status, 'body' => $responseBody];
    }

    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
