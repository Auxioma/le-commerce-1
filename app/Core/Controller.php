<?php

declare(strict_types=1);

namespace App\Core;

use App\Service\ShopSettingsService;

/**
 * Contrôleur de base
 * Fournit le rendu de vues (Twig) avec layout et les helpers communs
 */
abstract class Controller
{
    protected array $sharedData = [];

    public function __construct()
    {
        $appConfig = require dirname(__DIR__, 2) . '/config/app.php';
        $currentUser = Middleware::user();

        // Données partagées automatiquement avec toutes les vues (config boutique,
        // paramètres modifiables, libellés constants...) — préparées une seule fois
        // par le Service dédié, pour que les vues n'appellent jamais un Model.
        $shopData = (new ShopSettingsService())->build($appConfig['shop'], $currentUser);

        $this->sharedData['app'] = $appConfig;
        $this->sharedData['shop'] = $shopData['shop'];
        $this->sharedData['settings'] = $shopData['settings'];
        $this->sharedData['registrationSourceLabels'] = $shopData['registrationSourceLabels'];
        $this->sharedData['clientLabelColors'] = $shopData['clientLabelColors'];
        $this->sharedData['unreadMessagesCount'] = $shopData['unreadMessagesCount'];
        $this->sharedData['currentUri'] = $this->currentUri();
        $this->sharedData['currentUser'] = $currentUser;
        $this->sharedData['flash'] = $this->pullFlash();
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
     * Affiche une vue Twig enveloppée dans un layout
     *
     * @param string $view   chemin relatif dans Views, ex: "home/index" (résout home/index.twig)
     * @param array  $data   données transmises à la vue
     * @param string $layout nom du layout dans Views/layouts (sans extension), ou '' pour aucun layout
     */
    protected function view(string $view, array $data = [], string $layout = 'main'): void
    {
        $data = array_merge($this->sharedData, $data);
        $content = View::render($view, $data);

        $layoutTemplate = "layouts/{$layout}";
        if ($layout !== '' && View::exists($layoutTemplate)) {
            echo View::render($layoutTemplate, array_merge($data, ['content' => $content]));
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
