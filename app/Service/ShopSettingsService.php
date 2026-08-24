<?php

declare(strict_types=1);

namespace App\Service;

use App\Models\ClientLabel;
use App\Models\ContactMessage;
use App\Models\Settings;
use App\Models\User;

/**
 * Prépare toutes les données "globales" dont les vues ont besoin (identité de
 * l'établissement, paramètres modifiables, libellés constants) à partir des
 * Models, une seule fois par requête, pour que les vues n'aient jamais à
 * appeler un Model directement.
 */
final class ShopSettingsService
{
    /**
     * @param array<string, mixed> $defaults config/app.php['shop']
     * @param array<string, mixed>|null $currentUser utilisateur connecté (ou null)
     * @return array{
     *     shop: array<string, mixed>,
     *     settings: array<string, string>,
     *     registrationSourceLabels: array<string, string>,
     *     clientLabelColors: string[],
     *     unreadMessagesCount: int,
     * }
     */
    public function build(array $defaults, ?array $currentUser): array
    {
        $settings = Settings::all();

        return [
            'shop' => $this->mergeShopData($defaults, $settings),
            'settings' => $settings,
            'registrationSourceLabels' => User::SOURCE_LABELS,
            'clientLabelColors' => ClientLabel::COLORS,
            'unreadMessagesCount' => ($currentUser['role'] ?? null) === 'admin'
                ? ContactMessage::countUnread()
                : 0,
        ];
    }

    private function mergeShopData(array $defaults, array $overrides): array
    {
        if (empty($overrides)) {
            return $defaults;
        }

        return array_merge($defaults, [
            'name' => $overrides['shop_name'] ?? $defaults['name'],
            'address' => $overrides['shop_address'] ?? $defaults['address'],
            'zipcode' => $overrides['shop_zipcode'] ?? $defaults['zipcode'],
            'city' => $overrides['shop_city'] ?? $defaults['city'],
            'phone' => $overrides['shop_phone'] ?? $defaults['phone'],
            'phone_href' => isset($overrides['shop_phone']) ? self::phoneHref($overrides['shop_phone']) : $defaults['phone_href'],
            'whatsapp' => $overrides['shop_whatsapp'] ?? $defaults['whatsapp'],
            'whatsapp_href' => self::resolveWhatsappHref($overrides, $defaults),
            'email' => $overrides['shop_email'] ?? $defaults['email'],
            'hours' => [
                'lun_sam' => $overrides['hours_lun_sam'] ?? $defaults['hours']['lun_sam'],
                'dim' => $overrides['hours_dim'] ?? $defaults['hours']['dim'],
            ],
            'social' => [
                'facebook' => $overrides['social_facebook'] ?? $defaults['social']['facebook'],
                'instagram' => $overrides['social_instagram'] ?? $defaults['social']['instagram'],
            ],
            'latitude' => isset($overrides['latitude']) ? (float) $overrides['latitude'] : $defaults['latitude'],
            'longitude' => isset($overrides['longitude']) ? (float) $overrides['longitude'] : $defaults['longitude'],
            'streetview_embed_url' => $overrides['streetview_embed_url'] ?? ($defaults['streetview_embed_url'] ?? ''),
            'legal' => [
                'forme_juridique' => $overrides['legal_forme_juridique'] ?? $defaults['legal']['forme_juridique'],
                'capital_social' => $overrides['legal_capital_social'] ?? $defaults['legal']['capital_social'],
                'siret' => $overrides['legal_siret'] ?? $defaults['legal']['siret'],
                'rcs_numero' => $overrides['legal_rcs_numero'] ?? $defaults['legal']['rcs_numero'],
                'rcs_ville' => $overrides['legal_rcs_ville'] ?? $defaults['legal']['rcs_ville'],
                'directeur_publication' => $overrides['legal_directeur_publication'] ?? $defaults['legal']['directeur_publication'],
                'hebergeur_nom' => $overrides['legal_hebergeur_nom'] ?? $defaults['legal']['hebergeur_nom'],
                'hebergeur_adresse' => $overrides['legal_hebergeur_adresse'] ?? $defaults['legal']['hebergeur_adresse'],
                'hebergeur_telephone' => $overrides['legal_hebergeur_telephone'] ?? $defaults['legal']['hebergeur_telephone'],
            ],
        ]);
    }

    /**
     * Détermine le numéro WhatsApp effectif : le champ dédié s'il a été
     * renseigné en admin, sinon le téléphone du commerce.
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
}
