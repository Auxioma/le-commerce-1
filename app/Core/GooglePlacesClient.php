<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Client minimaliste pour l'API Google Places (New) v1, sans dépendance
 * Composer, qui récupère la note globale, le nombre d'avis et les derniers
 * avis de la fiche établissement Google (page d'accueil et page admin
 * "Avis Google").
 *
 * Authentification : simple clé API (Google Cloud Console > API et services
 * > Identifiants). Le Place ID et la clé API vivent dans le fichier .env,
 * sous deux variables parmi GOOGLE_PLACE_ID / GOOGLE_PLACES_API_KEY /
 * GEOLOCATION_API_KEY (un outil externe réinjecte régulièrement ces deux
 * dernières en bas du fichier, avec les valeurs sous des noms différents) :
 * on ne se fie donc pas au nom de la variable mais au FORMAT de la valeur
 * — une clé API Google commence toujours par "AIza", un Place ID jamais.
 *
 * Le résultat complet est mis en cache sur disque
 * (storage/cache/google-places.json) pour éviter d'appeler l'API à chaque
 * chargement de page.
 *
 * Limite Google : l'API ne renvoie jamais plus de 5 avis, même si
 * userRatingCount est bien plus élevé (c'est une restriction de l'API, pas
 * un bug ici) — pour l'historique complet, seul l'ajout manuel (page admin)
 * permet d'afficher davantage de témoignages.
 */
class GooglePlacesClient
{
    private const API_URL   = 'https://places.googleapis.com/v1/places/';
    private const FIELDS    = 'rating,userRatingCount,reviews,googleMapsUri';
    private const CACHE_TTL = 3600; // 1 heure

    private string $placeId;
    private string $apiKey;

    private function __construct(string $placeId, string $apiKey)
    {
        $this->placeId = $placeId;
        $this->apiKey  = $apiKey;
    }

    /**
     * Construit le client à partir des variables d'environnement (.env),
     * en identifiant la clé API et le Place ID par leur format plutôt que
     * par le nom de la variable qui les porte (voir docblock de la classe).
     * Renvoie null si l'un des deux est introuvable (l'appelant doit alors
     * se rabattre sur les valeurs par défaut de config/app.php).
     */
    public static function fromSettings(): ?self
    {
        ['apiKey' => $apiKey, 'placeId' => $placeId] = self::detectCredentials();

        if ($apiKey === null || $placeId === null) {
            return null;
        }

        return new self($placeId, $apiKey);
    }

    /**
     * Lien Google Maps permettant à un client de laisser une note et un
     * avis, construit à partir du Place ID (aucun appel API nécessaire).
     */
    public static function writeReviewUrl(): ?string
    {
        $placeId = self::detectCredentials()['placeId'];
        if ($placeId === null) {
            return null;
        }

        return 'https://search.google.com/local/writereview?placeid=' . rawurlencode($placeId);
    }

    /**
     * @return array{apiKey: ?string, placeId: ?string}
     */
    private static function detectCredentials(): array
    {
        $candidates = array_filter(array_map(
            static fn (string $name) => trim((string) getenv($name)),
            ['GOOGLE_PLACE_ID', 'GOOGLE_PLACES_API_KEY', 'GEOLOCATION_API_KEY']
        ), static fn (string $v) => $v !== '');

        $apiKey = null;
        $placeId = null;
        foreach ($candidates as $value) {
            if (str_starts_with($value, 'AIza')) {
                $apiKey ??= $value;
            } else {
                $placeId ??= $value;
            }
        }

        return ['apiKey' => $apiKey, 'placeId' => $placeId];
    }

    /**
     * Note globale et nombre total d'avis.
     *
     * @return array{rating: float, total: int}|null
     */
    public function summary(): ?array
    {
        $data = $this->fetch();
        if ($data === null) {
            return null;
        }

        return ['rating' => $data['rating'], 'total' => $data['total']];
    }

    /**
     * Les derniers avis Google (5 maximum, limite de l'API), du plus récent
     * au plus ancien, sous une forme proche de celle des avis ajoutés
     * manuellement (author_name/rating/comment/published_at) pour pouvoir
     * réutiliser le même gabarit d'affichage.
     *
     * @return list<array{author_name: string, rating: int, comment: string, published_at: string, source_url: string}>
     */
    public function reviews(): array
    {
        return $this->fetch()['reviews'] ?? [];
    }

    /**
     * Lien vers la fiche Google Maps de l'établissement (pour "Voir la
     * fiche Google"), ou null si l'API n'a pas pu être interrogée.
     */
    public function mapsUri(): ?string
    {
        return $this->fetch()['mapsUri'] ?? null;
    }

    /**
     * Appelle l'API Google Places (New) v1 une seule fois (résultat mis en
     * cache disque une heure), et renvoie note/total/avis/lien Maps. Renvoie
     * null en cas d'échec (réseau, quota, clé invalide...).
     *
     * @return array{rating: float, total: int, reviews: array, mapsUri: ?string}|null
     */
    private function fetch(): ?array
    {
        $cacheFile = dirname(__DIR__, 2) . '/storage/cache/google-places.json';

        $cached = $this->readCache($cacheFile);
        if ($cached !== null) {
            return $cached;
        }

        $ch = curl_init(self::API_URL . rawurlencode($this->placeId) . '?languageCode=fr');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'X-Goog-Api-Key: ' . $this->apiKey,
            'X-Goog-FieldMask: ' . self::FIELDS,
        ]);
        $body = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($body === false || $status !== 200) {
            error_log('[GooglePlacesClient] Erreur cURL ou HTTP (' . $status . ') : ' . ($body !== false ? $body : curl_error($ch)));
            return null;
        }

        $data = json_decode($body, true);
        if (!isset($data['rating'])) {
            error_log('[GooglePlacesClient] Réponse Places API (New) invalide : ' . $body);
            return null;
        }

        $result = [
            'rating'  => (float) $data['rating'],
            'total'   => (int) ($data['userRatingCount'] ?? 0),
            'reviews' => array_map([self::class, 'normalizeReview'], $data['reviews'] ?? []),
            'mapsUri' => $data['googleMapsUri'] ?? null,
        ];

        $this->writeCache($cacheFile, $result);

        return $result;
    }

    /**
     * @return array{author_name: string, rating: int, comment: string, published_at: string, source_url: string}
     */
    private static function normalizeReview(array $review): array
    {
        return [
            'author_name'  => $review['authorAttribution']['displayName'] ?? 'Client Google',
            'rating'       => (int) ($review['rating'] ?? 5),
            'comment'      => $review['text']['text'] ?? ($review['originalText']['text'] ?? ''),
            'published_at' => $review['publishTime'] ?? '',
            'source_url'   => $review['googleMapsUri'] ?? '',
        ];
    }

    private function readCache(string $cacheFile): ?array
    {
        if (!is_file($cacheFile)) {
            return null;
        }

        $raw = file_get_contents($cacheFile);
        $data = $raw !== false ? json_decode($raw, true) : null;

        if (!is_array($data) || !isset($data['expires_at'], $data['rating'], $data['total'], $data['reviews'])) {
            return null;
        }

        if ($data['expires_at'] < time()) {
            return null;
        }

        return [
            'rating'  => (float) $data['rating'],
            'total'   => (int) $data['total'],
            'reviews' => $data['reviews'],
            'mapsUri' => $data['mapsUri'] ?? null,
        ];
    }

    private function writeCache(string $cacheFile, array $result): void
    {
        $dir = dirname($cacheFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        file_put_contents($cacheFile, json_encode([
            'rating'     => $result['rating'],
            'total'      => $result['total'],
            'reviews'    => $result['reviews'],
            'mapsUri'    => $result['mapsUri'],
            'expires_at' => time() + self::CACHE_TTL,
        ]));
    }
}
