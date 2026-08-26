<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Bootstrap de l'application
 */
class App
{
    public static function boot(): void
    {
        self::configureLogging();
        self::loadEnv();

        $appConfig = require dirname(__DIR__, 2) . '/config/app.php';

        date_default_timezone_set($appConfig['timezone']);

        if ($appConfig['debug']) {
            ini_set('display_errors', '1');
            error_reporting(E_ALL);
        } else {
            ini_set('display_errors', '0');
        }

        session_start();
    }

    /**
     * Redirige les logs PHP (error_log, warnings, exceptions non catchées)
     * vers storage/logs/app.txt plutôt que le log système par défaut.
     * Extension .txt (plutôt que .log) pour l'ouvrir facilement d'un double-clic.
     */
    private static function configureLogging(): void
    {
        $logDir = dirname(__DIR__, 2) . '/storage/logs';

        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        ini_set('log_errors', '1');
        ini_set('error_log', $logDir . '/app.txt');
    }

    /**
     * Charge les variables du fichier .env dans l'environnement (getenv/$_ENV),
     * car aucune dépendance externe (phpdotenv) n'est utilisée.
     */
    private static function loadEnv(): void
    {
        $envFile = dirname(__DIR__, 2) . '/.env';

        if (!file_exists($envFile)) {
            error_log("[App::loadEnv] Fichier .env introuvable : $envFile");
            return;
        }

        self::loadEnvFile($envFile);
    }

    /**
     * Charge un fichier .env dans l'environnement (getenv/$_ENV).
     *
     * Le parsing est fait ligne à ligne "à la main" plutôt qu'avec
     * parse_ini_file() : le lexer INI natif de PHP échoue sur des caractères
     * pourtant courants dans des valeurs (parenthèses, &, |, !, ~, ^...) dès
     * qu'ils ne sont pas quotés, quel que soit le mode de scan utilisé. Ce
     * parseur maison n'a pas cette limite : chaque valeur est prise telle
     * quelle, sans grammaire d'expression à respecter.
     *
     * Public (contrairement à loadEnv()) : réutilisé tel quel par phinx.php,
     * qui tourne en CLI hors du bootstrap web (pas de session/logging à configurer).
     */
    public static function loadEnvFile(string $envFile): void
    {
        if (!file_exists($envFile)) {
            return;
        }

        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $vars = [];

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '' || $line[0] === ';' || $line[0] === '#') {
                continue;
            }

            $equalsPos = strpos($line, '=');
            if ($equalsPos === false) {
                continue;
            }

            $key = trim(substr($line, 0, $equalsPos));
            $value = trim(substr($line, $equalsPos + 1));
            if ($key === '') {
                continue;
            }

            $isQuoted = strlen($value) >= 2
                && (($value[0] === '"' && $value[-1] === '"') || ($value[0] === "'" && $value[-1] === "'"));
            if ($isQuoted) {
                $value = substr($value, 1, -1);
            } elseif (preg_match('/^(.*?)\s+#/', $value, $m)) {
                // Commentaire en fin de ligne sur une valeur non quotée (ex. "FOO=bar #note"),
                // seulement si un espace précède le '#' pour ne pas tronquer une valeur
                // qui contient légitimement un '#' collé (URL avec ancre, couleur hexa...).
                $value = rtrim($m[1]);
            }

            $vars[$key] = $value;
        }

        foreach ($vars as $key => $value) {
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}
