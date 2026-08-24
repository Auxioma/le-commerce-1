<?php

namespace App\Core;

/**
 * Router
 * Fait correspondre une URL + méthode HTTP à [Contrôleur, action]
 * Supporte les paramètres dynamiques du type /clients/{id}
 */
class Router
{
    /** @var array<string, array<string, array{0:string,1:string}>> */
    protected array $routes = [
        'GET'  => [],
        'POST' => [],
        'PUT'  => [],
        'DELETE' => [],
    ];

    public function get(string $uri, string $controller, string $action): void
    {
        $this->addRoute('GET', $uri, $controller, $action);
    }

    public function post(string $uri, string $controller, string $action): void
    {
        $this->addRoute('POST', $uri, $controller, $action);
    }

    public function put(string $uri, string $controller, string $action): void
    {
        $this->addRoute('PUT', $uri, $controller, $action);
    }

    public function delete(string $uri, string $controller, string $action): void
    {
        $this->addRoute('DELETE', $uri, $controller, $action);
    }

    protected function addRoute(string $method, string $uri, string $controller, string $action): void
    {
        $uri = '/' . trim($uri, '/');
        $this->routes[$method][$uri] = [$controller, $action];
    }

    /**
     * Résout l'URI courante vers un couple [Contrôleur, action, params]
     */
    public function resolve(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri    = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

        // Retire le sous-dossier de base éventuel (si le projet n'est pas à la racine)
        $basePath = BASE_PATH;
        if ($basePath !== '' && str_starts_with($uri, $basePath)) {
            $uri = substr($uri, strlen($basePath));
        }
        $uri = '/' . trim($uri, '/');
        if ($uri === '//') {
            $uri = '/';
        }

        foreach ($this->routes[$method] ?? [] as $pattern => $target) {
            $params = $this->match($pattern, $uri);
            if ($params !== null) {
                [$controllerClass, $action] = $target;
                $this->dispatch($controllerClass, $action, $params);
                return;
            }
        }

        $this->notFound();
    }

    protected function match(string $pattern, string $uri): ?array
    {
        $patternParts = explode('/', trim($pattern, '/'));
        $uriParts     = explode('/', trim($uri, '/'));

        if (count($patternParts) !== count($uriParts)) {
            return null;
        }

        $params = [];
        foreach ($patternParts as $i => $part) {
            if (preg_match('/^\{(\w+)\}$/', $part, $m)) {
                $params[$m[1]] = $uriParts[$i];
            } elseif ($part !== $uriParts[$i]) {
                return null;
            }
        }

        return $params;
    }

    protected function dispatch(string $controllerClass, string $action, array $params): void
    {
        if (!class_exists($controllerClass)) {
            $this->notFound();
            return;
        }

        $controller = new $controllerClass();

        if (!method_exists($controller, $action)) {
            $this->notFound();
            return;
        }

        call_user_func_array([$controller, $action], $params);
    }

    protected function notFound(): void
    {
        http_response_code(404);
        if (View::exists('errors/404')) {
            echo View::render('errors/404');
        } else {
            echo '404 - Page non trouvée';
        }
    }
}
