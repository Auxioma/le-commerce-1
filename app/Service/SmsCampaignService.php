<?php

declare(strict_types=1);

namespace App\Service;

use App\Core\AllMySmsClient;
use App\Models\User;

/**
 * Diffusion SMS marketing via l'API AllMySms — annonce automatiquement une
 * nouvelle loterie à tous les clients actifs, et expose les statistiques de
 * campagne (envoyés/délivrés/clics) consommées par /admin/loterie.
 */
final class SmsCampaignService
{
    /** Longueur maximale du lot affiché dans le SMS, pour garder un message court. */
    private const MAX_PRIZE_LENGTH = 60;

    private ?AllMySmsClient $client;

    public function __construct()
    {
        $this->client = AllMySmsClient::fromSettings();
    }

    public function isConfigured(): bool
    {
        return $this->client !== null;
    }

    /**
     * Diffuse par SMS l'ouverture d'une nouvelle loterie à tous les clients
     * actifs disposant d'un numéro de téléphone, avec un court message
     * publicitaire et le lien de participation — automatiquement raccourci
     * et suivi (clics) par AllMySms.
     *
     * @return array{campaignId:?string, nbSms:int, nbContacts:int}|null null si l'API n'est pas configurée, si aucun destinataire n'est éligible, ou si l'envoi échoue.
     */
    public function announceLottery(array $lottery, string $publicUrl): ?array
    {
        if ($this->client === null) {
            return null;
        }

        $recipients = [];
        foreach (User::activeClientsForSelect() as $client) {
            $phone = trim((string) ($client['phone_whatsapp'] ?? ''));
            if ($phone === '') {
                continue;
            }
            $recipients[] = ['phone' => $phone, 'firstName' => (string) $client['first_name']];
        }

        if ($recipients === []) {
            return null;
        }

        $prize = $lottery['prize'];
        if (mb_strlen($prize) > self::MAX_PRIZE_LENGTH) {
            $prize = mb_substr($prize, 0, self::MAX_PRIZE_LENGTH - 3) . '...';
        }

        $message = sprintf(
            'Bonjour #param_1# ! Le Commerce lance une loterie : %s a gagner. Participez ici : %s STOP au 36180',
            $prize,
            $publicUrl
        );

        $scheduledAt = $this->nextAllowedSendTime();

        $result = $this->client->sendCampaign(
            $message,
            $recipients,
            'Loterie #' . $lottery['id'] . ' - ' . $lottery['title'],
            $scheduledAt
        );

        if ($result === null || empty($result['campaignId'])) {
            return null;
        }

        return [
            'campaignId' => $result['campaignId'],
            'nbSms'      => $result['nbSms'],
            'nbContacts' => $result['nbContacts'],
            'scheduledAt' => $scheduledAt,
        ];
    }

    /**
     * L'envoi de SMS commerciaux est interdit en France entre 20h00 et 8h00
     * du lundi au samedi, ainsi que toute la journée le dimanche (sanctions
     * légales en cas de non-respect, cf. doc. AllMySms). Si une loterie est
     * publiée hors de ce créneau, le SMS est programmé (paramètre DATE de
     * l'API) pour le prochain jour ouvré à 9h00 plutôt qu'envoyé aussitôt.
     *
     * NB : ne couvre pas le calendrier des jours fériés français.
     *
     * @return string|null Date/heure programmée (Y-m-d H:i:s), ou null pour un envoi immédiat.
     */
    private function nextAllowedSendTime(): ?string
    {
        $now = new \DateTimeImmutable('now', new \DateTimeZone('Europe/Paris'));
        $dayOfWeek = (int) $now->format('N'); // 1 (lundi) à 7 (dimanche)
        $hour = (int) $now->format('G');

        if ($dayOfWeek !== 7 && $hour >= 8 && $hour < 20) {
            return null;
        }

        $next = $now->setTime(9, 0, 0);
        if ($dayOfWeek === 7 || $hour >= 20) {
            $next = $next->modify('+1 day');
        }
        while ((int) $next->format('N') === 7) {
            $next = $next->modify('+1 day');
        }

        return $next->format('Y-m-d H:i:s');
    }

    /**
     * Statistiques d'une campagne SMS déjà envoyée (délivrés, en attente,
     * erreurs, clics), ou null si l'API n'est pas configurée / indisponible.
     */
    public function campaignStats(string $campaignId): ?array
    {
        return $this->client?->campaignStats($campaignId);
    }

    /**
     * Solde du compte AllMySms (crédits restants, montant en euros).
     */
    public function accountInfo(): ?array
    {
        return $this->client?->accountInfo();
    }
}
