<?php

declare(strict_types=1);

/**
 * Fonctions d'affichage partagées entre les vues publiques (Accueil, Le Bar...)
 */

if (!function_exists('beerGlass')) {
    /**
     * Silhouettes de verre CC0 (domaine public, sans attribution requise) —
     * "Alcohol glass beer pint/goblet/pilsner flute" issues de Wikimedia
     * Commons (https://commons.wikimedia.org/wiki/Category:Beer_glasses),
     * recolorées selon la teinte de la boisson.
     */
    function beerGlass(string $tone = 'amber', string $shape = 'pint'): string
    {
        $palette = [
            'amber' => ['#f0a83c', '#d6862a'],
            'dark'  => ['#4a2c1c', '#2f1b10'],
            'copper'=> ['#c9702e', '#a5551c'],
            'gold'  => ['#f4c542', '#dba526'],
        ][$tone] ?? ['#f0a83c', '#d6862a'];

        $shapes = [
            // Alcohol glass beer pint.svg
            'pint' => [
                'path' => 'M12 22.375c-4.531 0-4.219-.438-4.219-.438l-1-18.219h10.438l-1 18.219c0 .001.312.438-4.219.438z',
                'foam' => ['cx' => 12, 'cy' => 4.1, 'rx' => 5.2, 'ry' => 1.1],
            ],
            // Alcohol glass beer goblet.svg
            'goblet' => [
                'path' => 'M14.585 19.534c-1.417-.708-1.396-1.625-1.396-1.854 0-.063.032-.168.073-.301.286-.294.466-.684.466-1.115 0-.302-.103-.577-.253-.821-.007-.361-.017-.694-.017-.72 0-.188.125-.793 1.382-1.5 1.257-.709 2.841-2.833 2.841-5s-.285-3.647-.367-3.938c-.084-.291-.25-.425-.469-.433-.22-.008-4.845 0-4.845 0s-4.625-.008-4.844 0c-.218.007-.385.14-.468.432s-.367 1.771-.367 3.938 1.583 4.292 2.84 5c1.257.707 1.382 1.312 1.382 1.5 0 .025-.01.34-.016.69-.16.249-.257.538-.257.851 0 .436.182.829.473 1.123.039.129.07.231.07.293 0 .229.021 1.146-1.396 1.854s-2.145 1.416-2 1.854c.145.438 2.229.465 4.583.465s4.439-.028 4.585-.465c.145-.436-.583-1.145-2-1.853z',
                'foam' => ['cx' => 12, 'cy' => 3.3, 'rx' => 4.4, 'ry' => 1],
            ],
            // Alcohol glass beer pilsner flute.svg
            'flute' => [
                'path' => 'M14.956 22.453s.06-.094-1.39-.713c-.336-.236-.404-.742-.404-.742l1.97-17.864H8.868l1.97 17.864s-.069.506-.405.742c-1.449.619-1.389.713-1.389.713s-.701.531 2.956.531 2.956-.531 2.956-.531z',
                'foam' => ['cx' => 12, 'cy' => 3.9, 'rx' => 3.4, 'ry' => 0.9],
            ],
        ];
        $glass = $shapes[$shape] ?? $shapes['pint'];

        return '
        <svg viewBox="0 0 24 24" class="w-10 h-14 mx-auto">
          <path d="' . $glass['path'] . '" fill="' . $palette[0] . '" stroke="' . $palette[1] . '" stroke-width="0.4"/>
          <ellipse cx="' . $glass['foam']['cx'] . '" cy="' . $glass['foam']['cy'] . '" rx="' . $glass['foam']['rx'] . '" ry="' . $glass['foam']['ry'] . '" fill="#fffdf8" opacity="0.92"/>
        </svg>';
    }
}

/**
 * Fonction de mapping catégorie boisson -> teinte du verre stylisé
 */
if (!function_exists('drinkTone')) {
    function drinkTone(string $category): string
    {
        return [
            'biere_blonde' => 'amber',
            'biere_brune'  => 'dark',
            'biere_ambree' => 'copper',
        ][$category] ?? 'gold';
    }
}

/**
 * Fonction de mapping catégorie boisson -> forme de verre (variété visuelle)
 */
if (!function_exists('drinkShape')) {
    function drinkShape(string $category): string
    {
        return [
            'biere_blonde' => 'flute',
            'biere_brune'  => 'goblet',
            'biere_ambree' => 'goblet',
        ][$category] ?? 'pint';
    }
}

/**
 * Ajoute un paramètre de version (date de dernière modification du fichier)
 * à une URL d'asset, pour forcer le navigateur à recharger le fichier après
 * chaque `npm run build` au lieu de servir une version mise en cache.
 */
if (!function_exists('assetVersion')) {
    function assetVersion(string $relativePath): string
    {
        $fullPath = dirname(__DIR__) . '/public/' . ltrim($relativePath, '/');
        $version = file_exists($fullPath) ? filemtime($fullPath) : time();
        return BASE_PATH . '/' . ltrim($relativePath, '/') . '?v=' . $version;
    }
}
