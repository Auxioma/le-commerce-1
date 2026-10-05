<?php

declare(strict_types=1);

namespace App\Service;

/**
 * Détection heuristique du spam sur le formulaire de contact public.
 * Retourne la raison du rejet (pour le log) ou null si le message semble légitime.
 */
class ContactSpamFilter
{
    /** Mots/expressions typiques des spams SEO, crypto, pharma, etc. */
    private const KEYWORDS = [
        'seo', 'backlink', 'search engine', 'google ranking', 'first page of google', 'rank higher',
        'web traffic', 'website traffic', 'organic traffic', 'domain authority', 'guest post',
        'link building', 'digital marketing', 'marketing agency', 'lead generation', 'leads for your',
        'web design', 'redesign your website', 'website development', 'app development',
        'crypto', 'bitcoin', 'btc', 'ethereum', 'forex', 'binary option', 'investment opportunity',
        'casino', 'betting', 'viagra', 'cialis', 'pharmacy', 'weight loss', 'porn', 'sexy', 'dating',
        'loan', 'credit repair', 'unsubscribe', 'opt-out', 'opt out', 'whatsapp me', 'telegram',
        'business proposal', 'outsourcing', 'virtual assistant', 'chatgpt', 'ai chatbot', 'video explainer',
        'free trial', 'limited offer', 'click here', 'dear sir', 'dear madam', 'hello dear',
        'cheap', 'discount code', 'promote your', 'increase your sales', 'boost your',
    ];

    public static function check(string $name, string $email, string $message): ?string
    {
        $all = $name . ' ' . $email . ' ' . $message;
        $lower = mb_strtolower($all);

        // Liens : aucun dans le nom, au plus un dans le message ; BBCode/HTML = spam.
        if (preg_match('~https?://|www\.~i', $name)) {
            return 'lien dans le nom';
        }
        if (preg_match_all('~https?://|www\.~i', $message) > 1) {
            return 'plusieurs liens';
        }
        if (preg_match('~\[url|\[link|<a\s|</a>~i', $message)) {
            return 'balise de lien';
        }

        // Alphabets que la clientèle locale n'utilise pas (cyrillique, CJK, arabe, hébreu, thaï…).
        if (preg_match('/[\p{Cyrillic}\p{Han}\p{Hiragana}\p{Katakana}\p{Hangul}\p{Arabic}\p{Hebrew}\p{Thai}]/u', $all)) {
            return 'alphabet étranger';
        }

        // Mots-clés (recherche sur mots entiers pour éviter les faux positifs).
        foreach (self::KEYWORDS as $keyword) {
            if (preg_match('/\b' . preg_quote($keyword, '/') . '\b/u', $lower)) {
                return 'mot-clé : ' . $keyword;
            }
        }

        // Nom aléatoire généré par un bot, ex. « xKqTzRbWmP » : un seul mot,
        // long, avec plusieurs majuscules au milieu.
        if (!preg_match('/\s/', $name) && mb_strlen($name) >= 8
            && preg_match_all('/\p{Lu}/u', mb_substr($name, 1)) >= 2) {
            return 'nom aléatoire';
        }

        // Message trop court pour être une vraie demande.
        if (mb_strlen($message) < 10) {
            return 'message trop court';
        }

        // Message quasi entièrement en anglais : la clientèle écrit en français.
        if (self::looksEnglish($lower)) {
            return 'message en anglais';
        }

        return null;
    }

    /**
     * Repère les messages rédigés en anglais (la grande majorité des spams reçus)
     * en comptant des mots-outils anglais vs français.
     */
    private static function looksEnglish(string $text): bool
    {
        $words = preg_split('/[^\p{L}\']+/u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        if (count($words) < 8) {
            return false;
        }

        $en = ['the', 'and', 'you', 'your', 'are', 'with', 'for', 'this', 'that', 'have', 'can', 'will', 'our', 'we', 'is', 'of', 'to', 'hi', 'hello', 'would', 'business', 'website'];
        $fr = ['le', 'la', 'les', 'et', 'vous', 'votre', 'est', 'avec', 'pour', 'ce', 'cette', 'que', 'qui', 'nous', 'de', 'des', 'du', 'un', 'une', 'je', 'bonjour', 'merci', 'pas'];

        $enCount = 0;
        $frCount = 0;
        foreach ($words as $word) {
            if (in_array($word, $en, true)) {
                $enCount++;
            } elseif (in_array($word, $fr, true)) {
                $frCount++;
            }
        }

        return $enCount >= 4 && $enCount > $frCount * 2;
    }
}
