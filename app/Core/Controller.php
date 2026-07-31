<?php

namespace App\Core;

/**
 * Contrôleur de base
 * Fournit le rendu de vues avec layout et les helpers communs
 */
abstract class Controller
{
    protected array $sharedData = [];

    public function __construct()
    {
        // Données partagées automatiquement avec toutes les vues (config boutique, etc.)
        $this->sharedData['app']  = require dirname(__DIR__, 2) . '/config/app.php';
        $this->sharedData['shop'] = $this->buildShopData($this->sharedData['app']['shop']);
        $this->sharedData['currentUri'] = $this->currentUri();
        $this->sharedData['currentUser'] = Middleware::user();
        $this->sharedData['flash'] = $this->pullFlash();
    }

    /**
     * Fusionne les paramètres modifiables (table `settings`, Lot 10) par-dessus
     * les valeurs par défaut de config/app.php, pour que les changements faits
     * depuis /admin/parametres soient reflétés partout sans toucher au code.
     */
    private function buildShopData(array $defaults): array
    {
        $overrides = \App\Models\Settings::all();
        if (empty($overrides)) {
            return $defaults;
        }

        return array_merge($defaults, [
            'name'      => $overrides['shop_name'] ?? $defaults['name'],
            'address'   => $overrides['shop_address'] ?? $defaults['address'],
            'zipcode'   => $overrides['shop_zipcode'] ?? $defaults['zipcode'],
            'city'      => $overrides['shop_city'] ?? $defaults['city'],
            'phone'     => $overrides['shop_phone'] ?? $defaults['phone'],
            'phone_href'=> isset($overrides['shop_phone']) ? self::phoneHref($overrides['shop_phone']) : $defaults['phone_href'],
            'whatsapp'      => $overrides['shop_whatsapp'] ?? $defaults['whatsapp'],
            'whatsapp_href' => self::resolveWhatsappHref($overrides, $defaults),
            'email'     => $overrides['shop_email'] ?? $defaults['email'],
            'hours' => [
                'lun_sam' => $overrides['hours_lun_sam'] ?? $defaults['hours']['lun_sam'],
                'dim'     => $overrides['hours_dim'] ?? $defaults['hours']['dim'],
            ],
            'social' => [
                'facebook'  => $overrides['social_facebook'] ?? $defaults['social']['facebook'],
                'instagram' => $overrides['social_instagram'] ?? $defaults['social']['instagram'],
            ],
            'latitude'  => isset($overrides['latitude']) ? (float) $overrides['latitude'] : $defaults['latitude'],
            'longitude' => isset($overrides['longitude']) ? (float) $overrides['longitude'] : $defaults['longitude'],
            'legal' => [
                'forme_juridique'       => $overrides['legal_forme_juridique'] ?? $defaults['legal']['forme_juridique'],
                'capital_social'        => $overrides['legal_capital_social'] ?? $defaults['legal']['capital_social'],
                'siret'                 => $overrides['legal_siret'] ?? $defaults['legal']['siret'],
                'rcs_numero'            => $overrides['legal_rcs_numero'] ?? $defaults['legal']['rcs_numero'],
                'rcs_ville'             => $overrides['legal_rcs_ville'] ?? $defaults['legal']['rcs_ville'],
                'directeur_publication' => $overrides['legal_directeur_publication'] ?? $defaults['legal']['directeur_publication'],
                'hebergeur_nom'         => $overrides['legal_hebergeur_nom'] ?? $defaults['legal']['hebergeur_nom'],
                'hebergeur_adresse'     => $overrides['legal_hebergeur_adresse'] ?? $defaults['legal']['hebergeur_adresse'],
                'hebergeur_telephone'   => $overrides['legal_hebergeur_telephone'] ?? $defaults['legal']['hebergeur_telephone'],
            ],
        ]);
    }

    /**
     * Détermine le numéro WhatsApp effectif : le champ dédié s'il a été
     * renseigné en admin, sinon le téléphone du commerce (comportement
     * historique, avant que les deux numéros ne soient dissociables).
     */
    private static function resolveWhatsappHref(array $overrides, array $defaults): string
    {
        $whatsapp = $overrides['shop_whatsapp'] ?? $defaults['whatsapp'] ?? '';
        if ($whatsapp !== '') {
            return self::phoneHref($whatsapp);
        }

        $phone = $overrides['shop_phone'] ?? $defaults['phone'];
        return self::phoneHref($phone);
    }

    /**
     * Normalise un numéro français saisi en admin (ex. "07 81 77 15 52")
     * vers le format international utilisé par les liens tel:/wa.me
     * (ex. "+33781771552").
     */
    private static function phoneHref(string $phone): string
    {
        $digits = preg_replace('/[^\d+]/', '', $phone);
        if (str_starts_with($digits, '+')) {
            return $digits;
        }
        if (str_starts_with($digits, '0')) {
            return '+33' . substr($digits, 1);
        }
        return '+' . $digits;
    }

    /**
     * Récupère puis efface le message flash stocké en session
     * (affiché une seule fois, juste après une redirection).
     */
    protected function pullFlash(): ?array
    {
        if (empty($_SESSION['_flash'])) {
            return null;
        }
        $flash = $_SESSION['_flash'];
        unset($_SESSION['_flash']);
        return $flash;
    }

    protected function setFlash(string $type, string $message): void
    {
        $_SESSION['_flash'] = ['type' => $type, 'message' => $message];
    }

    /**
     * Vérifie le jeton CSRF envoyé en POST ; interrompt la requête si invalide.
     */
    protected function verifyCsrf(): void
    {
        if (!Csrf::verify($this->input('_csrf'))) {
            http_response_code(419);
            die('Jeton de sécurité invalide ou expiré. Merci de recharger la page et réessayer.');
        }
    }

    /**
     * Affiche une vue enveloppée dans le layout principal
     *
     * @param string $view   chemin relatif dans Views, ex: "home/index"
     * @param array  $data   données transmises à la vue
     * @param string $layout nom du layout dans Views/layouts (sans extension)
     */
    protected function view(string $view, array $data = [], string $layout = 'main'): void
    {
        $data = array_merge($this->sharedData, $data);
        extract($data);

        $viewFile = dirname(__DIR__) . "/Views/{$view}.php";
        if (!file_exists($viewFile)) {
            throw new \RuntimeException("Vue introuvable : {$view}");
        }

        // Capture le contenu de la vue pour l'injecter dans le layout
        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        $layoutFile = dirname(__DIR__) . "/Views/layouts/{$layout}.php";
        if (file_exists($layoutFile)) {
            require $layoutFile;
        } else {
            echo $content;
        }
    }

    /**
     * Répond en JSON (utilisé par les endpoints appelés en Ajax/fetch)
     */
    protected function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    protected function redirect(string $to): void
    {
        header('Location: ' . BASE_PATH . $to);
        exit;
    }

    protected function currentUri(): string
    {
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        if (BASE_PATH !== '' && str_starts_with($uri, BASE_PATH)) {
            $uri = substr($uri, strlen(BASE_PATH));
        }
        return '/' . trim($uri, '/');
    }

    protected function input(string $key, $default = null)
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }
}
