<?php

declare(strict_types=1);

namespace App\Core;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;

/**
 * Moteur de rendu (Twig). Les vues (app/Views/**\/*.twig) ne font que de la
 * présentation : les seules fonctions exposées ici sont des helpers de
 * présentation pure (CSRF, images déjà résolues, gabarits de verres...),
 * jamais un accès direct aux Models/à la base de données.
 */
final class View
{
    private static ?Environment $twig = null;

    public static function environment(): Environment
    {
        if (self::$twig === null) {
            $loader = new FilesystemLoader(dirname(__DIR__) . '/Views');
            $cacheDir = dirname(__DIR__, 2) . '/storage/cache/twig';

            self::$twig = new Environment($loader, [
                'cache' => $cacheDir,
                'auto_reload' => true,
                'autoescape' => 'html',
                'strict_variables' => false,
            ]);

            self::$twig->addGlobal('base_path', defined('BASE_PATH') ? BASE_PATH : '');

            self::$twig->addFunction(new TwigFunction('csrf_field', [Csrf::class, 'field'], ['is_safe' => ['html']]));
            self::$twig->addFunction(new TwigFunction('csrf_token', [Csrf::class, 'token']));

            // $settings = tableau brut settings (clé => valeur) déjà transmis par le Controller,
            // aucune requête n'est faite depuis la vue.
            self::$twig->addFunction(new TwigFunction('site_image', static function (array $settings, string $slug, string $default = ''): string {
                $path = $settings['img_' . $slug] ?? null;
                $basePath = defined('BASE_PATH') ? BASE_PATH : '';
                return $path ? $basePath . $path : $default;
            }));

            self::$twig->addFunction(new TwigFunction('beer_glass', 'beerGlass', ['is_safe' => ['html']]));
            self::$twig->addFunction(new TwigFunction('drink_tone', 'drinkTone'));
            self::$twig->addFunction(new TwigFunction('drink_shape', 'drinkShape'));
            self::$twig->addFunction(new TwigFunction('asset_version', 'assetVersion'));

            // Motif pseudo-QR décoratif (pas un vrai QR code) : grille de carrés déterministe
            // à partir d'un code, utilisée sur les cartes wallet/loterie/offre.
            self::$twig->addFunction(new TwigFunction('qr_pattern', static function (string $code, int $gridSize = 12, int $cellSize = 7, int $offset = 6): string {
                mt_srand(crc32($code));
                $svg = '';
                for ($y = 0; $y < $gridSize; $y++) {
                    for ($x = 0; $x < $gridSize; $x++) {
                        if (mt_rand(0, 100) > 52) {
                            $svg .= '<rect x="' . ($offset + $x * $cellSize) . '" y="' . ($offset + $y * $cellSize) . '" width="' . $cellSize . '" height="' . $cellSize . '" fill="#111"/>';
                        }
                    }
                }
                return $svg;
            }, ['is_safe' => ['html']]));

            // Choix déterministe (ex. couleur d'avatar) à partir d'une chaîne, indépendant
            // de la position dans une liste — même logique que l'ancien crc32($x) % $max en PHP.
            self::$twig->addFunction(new TwigFunction('hash_mod', static fn (string $seed, int $max): int => crc32($seed) % $max));
        }

        return self::$twig;
    }

    public static function render(string $template, array $data = []): string
    {
        return self::environment()->render(self::normalize($template), $data);
    }

    public static function exists(string $template): bool
    {
        return self::environment()->getLoader()->exists(self::normalize($template));
    }

    private static function normalize(string $template): string
    {
        return str_ends_with($template, '.twig') ? $template : $template . '.twig';
    }
}
