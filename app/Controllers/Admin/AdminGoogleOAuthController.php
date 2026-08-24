<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\GoogleAnalyticsClient;
use App\Core\Middleware;

/**
 * Connexion OAuth 2.0 "utilisateur" à Google Analytics (GA4), utilisée
 * lorsqu'aucun compte de service n'est disponible (ex : création de clés de
 * compte de service désactivée par une politique d'organisation Google
 * Cloud). L'admin s'authentifie une seule fois avec son compte Google ayant
 * accès à la propriété GA4 ; le refresh_token obtenu est ensuite conservé
 * sur le serveur (voir GoogleAnalyticsClient) pour renouveler
 * automatiquement les jetons d'accès à chaque appel à l'API.
 */
class AdminGoogleOAuthController extends Controller
{
    private const AUTH_URL   = 'https://accounts.google.com/o/oauth2/v2/auth';
    private const SCOPE      = 'https://www.googleapis.com/auth/analytics.readonly';
    private const STATE_KEY  = 'ga4_oauth_state';

    /**
     * Démarre le flux : redirige vers l'écran de consentement Google.
     */
    public function connect(): void
    {
        Middleware::requireRole('admin');

        $clientId = trim((string) getenv('GA4_OAUTH_CLIENT_ID'));
        if ($clientId === '') {
            $this->setFlash('error', 'Aucun identifiant OAuth Google trouvé (variables GA4_OAUTH_CLIENT_ID / GA4_OAUTH_CLIENT_SECRET absentes de .env).');
            $this->redirect('/admin/parametres');
            return;
        }

        $state = bin2hex(random_bytes(16));
        $_SESSION[self::STATE_KEY] = $state;

        $params = [
            'client_id'              => $clientId,
            'redirect_uri'           => $this->redirectUri(),
            'response_type'          => 'code',
            'scope'                  => self::SCOPE,
            'access_type'            => 'offline',
            // Force le renvoi d'un refresh_token même si l'admin a déjà
            // autorisé l'application par le passé.
            'prompt'                 => 'consent',
            'state'                  => $state,
            'include_granted_scopes' => 'true',
        ];

        header('Location: ' . self::AUTH_URL . '?' . http_build_query($params));
        exit;
    }

    /**
     * Réception du retour de Google (redirect_uri enregistré dans le client
     * OAuth : /callback/google/analytics). Échange le code contre un
     * refresh_token puis revient sur la page des paramètres.
     */
    public function callback(): void
    {
        Middleware::requireRole('admin');

        $error = $this->input('error');
        if ($error !== null) {
            $this->setFlash('error', 'Connexion Google refusée ou annulée (' . $error . ').');
            $this->redirect('/admin/parametres');
            return;
        }

        $state = $this->input('state');
        $expectedState = $_SESSION[self::STATE_KEY] ?? null;
        unset($_SESSION[self::STATE_KEY]);

        if ($state === null || $expectedState === null || !hash_equals($expectedState, $state)) {
            $this->setFlash('error', 'Requête de connexion Google invalide (état de sécurité incorrect). Merci de réessayer.');
            $this->redirect('/admin/parametres');
            return;
        }

        $code = $this->input('code');
        if (empty($code)) {
            $this->setFlash('error', 'Google n\'a renvoyé aucun code d\'autorisation.');
            $this->redirect('/admin/parametres');
            return;
        }

        $ok = GoogleAnalyticsClient::exchangeAuthorizationCode((string) $code, $this->redirectUri());
        if (!$ok) {
            $this->setFlash('error', 'Échec de la connexion à Google Analytics. Consultez les journaux serveur pour le détail.');
            $this->redirect('/admin/parametres');
            return;
        }

        $this->setFlash('success', 'Connexion à Google Analytics réussie.');
        $this->redirect('/admin/parametres');
    }

    /**
     * Révoque la connexion OAuth locale (supprime le refresh_token stocké
     * sur le serveur, sans toucher au consentement côté compte Google).
     */
    public function disconnect(): void
    {
        Middleware::requireRole('admin');
        $this->verifyCsrf();

        GoogleAnalyticsClient::disconnectOAuth();

        $this->setFlash('success', 'Connexion à Google Analytics déconnectée.');
        $this->redirect('/admin/parametres');
    }

    /**
     * Reconstruit l'URL de redirection exacte attendue par Google (doit
     * correspondre mot pour mot à l'un des redirect_uris enregistrés dans
     * la Google Cloud Console pour ce client OAuth).
     */
    private function redirectUri(): string
    {
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return 'https://' . $host . '/callback/google/analytics';
    }
}
