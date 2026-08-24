<?php

declare(strict_types=1);

/**
 * Front Controller — point d'entrée unique de l'application
 * Toutes les requêtes HTTP passent par ce fichier (voir .htaccess)
 */

use App\Core\App;
use App\Core\Router;

require dirname(__DIR__) . '/vendor/autoload.php';

App::boot();

// BASE_PATH : chemin du dossier /public si le projet n'est pas servi à la racine du domaine
// (laisser vide '' quand on lance via `php -S localhost:8000 -t public`)
define('BASE_PATH', '');

$router = new Router();
require dirname(__DIR__) . '/app/routes.php';

$router->resolve();
