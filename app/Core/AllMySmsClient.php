<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Client minimaliste pour l'API HTTPS AllMySms (v9.0), sans dépendance
 * Composer (le projet reste 100 % PHP natif), utilisé pour diffuser les SMS
 * d'annonce de loterie et récupérer les statistiques de campagne (envoyés,
 * délivrés, clics) affichées dans /admin/loterie.
 *
 * Authentification : couple login / apiKey disponible sur l'espace client
 * https://manager.allmysms.com (rubrique API / Clé d'API & Paramètres).
 * Configuration via .env (voir .env.example) :
 * - ALLMYSMS_LOGIN
 * - ALLMYSMS_API_KEY
 * - ALLMYSMS_SENDER : nom d'expéditeur (3 à 11 caractères alphanumériques,
 *   sans accents) — n'est pris en compte par les opérateurs français que si
 *   le message contient la mention "STOP au 36180", sans quoi l'expéditeur
 *   revient à un numéro court.
 *
 * Documentation officielle : https://doc.allmysms.com/api/allmysms_api_https_v9.0_FR.pdf
 */
class AllMySmsClient
{
    private const API_BASE = 'https://api.allmysms.com/http/9.0';

    private string $login;
    private string $apiKey;
    private string $sender;

    private function __construct(string $login, string $apiKey, string $sender)
    {
        $this->login  = $login;
        $this->apiKey = $apiKey;
        $this->sender = $sender;
    }

    /**
     * Construit le client à partir des variables d'environnement (.env).
     * Renvoie null si login/apiKey ne sont pas configurés (l'appelant doit
     * alors se comporter comme si l'envoi de SMS marketing était désactivé).
     */
    public static function fromSettings(): ?self
    {
        $login  = trim((string) getenv('ALLMYSMS_LOGIN'));
        $apiKey = trim((string) getenv('ALLMYSMS_API_KEY'));
        $sender = trim((string) getenv('ALLMYSMS_SENDER')) ?: 'LeCommerce';

        if ($login === '' || $apiKey === '') {
            return null;
        }

        return new self($login, $apiKey, $sender);
    }

    /**
     * Envoie une campagne de SMS personnalisés (un message par destinataire,
     * avec le prénom injecté via la variable dynamique #param_1#) et active
     * le suivi de clics AllMySms sur le lien contenu dans le message
     * (raccourcissement + comptage automatiques, aucune action requise ici).
     *
     * @param string $message Corps du message ; doit contenir #param_1# (prénom) et l'URL à suivre.
     * @param array<int, array{phone: string, firstName: string}> $recipients
     * @param string|null $scheduledAt Date d'envoi différé (format YYYY-MM-JJ HH:MM:SS), ou null pour un envoi immédiat.
     * @return array{status:int, statusText:string, campaignId:?string, nbSms:int, nbContacts:int, creditsUsed:float, balance:?float, invalidNumbers:string}|null
     */
    public function sendCampaign(string $message, array $recipients, string $campaignName, ?string $scheduledAt = null): ?array
    {
        if ($recipients === []) {
            return null;
        }

        $sms = [];
        foreach ($recipients as $recipient) {
            $sms[] = [
                'MOBILEPHONE' => $recipient['phone'],
                'PARAM_1'     => $recipient['firstName'],
            ];
        }

        $smsData = [
            'DATA' => [
                'MESSAGE'       => $message,
                'DYNAMIC'       => '1',
                'CAMPAIGN_NAME' => $campaignName,
                'TPOA'          => $this->sender,
                'TRACKING'      => '1',
                'SMS'           => $sms,
            ],
        ];

        if ($scheduledAt !== null) {
            $smsData['DATA']['DATE'] = $scheduledAt;
        }

        $response = $this->call('POST', '/sendSms/', [
            'smsData' => json_encode($smsData, JSON_UNESCAPED_UNICODE),
        ]);

        if ($response === null) {
            return null;
        }

        return [
            'status'         => (int) ($response['status'] ?? 0),
            'statusText'     => (string) ($response['statusText'] ?? ''),
            'campaignId'     => $response['campaignId'] ?? null,
            'nbSms'          => (int) ($response['nbSms'] ?? 0),
            'nbContacts'     => (int) ($response['nbContacts'] ?? 0),
            'creditsUsed'    => (float) ($response['creditsUsed'] ?? 0),
            'balance'        => isset($response['balance']) ? (float) $response['balance'] : null,
            'invalidNumbers' => (string) ($response['invalidNumbers'] ?? ''),
        ];
    }

    /**
     * Statistiques d'une campagne SMS déjà envoyée : nombre envoyé, délivrés,
     * en attente, erreurs, ainsi que les clics sur le lien suivi.
     */
    public function campaignStats(string $campaignId): ?array
    {
        $response = $this->call('GET', '/getCampaignStats/', [
            'campaignId' => $campaignId,
            'type'       => 'sms',
            'msisdnList' => 0,
        ]);

        return $response['campaign'] ?? null;
    }

    /**
     * Solde du compte AllMySms (crédits SMS restants, montant en euros) —
     * affiché comme indicateur global sur la page /admin/loterie.
     */
    public function accountInfo(): ?array
    {
        return $this->call('GET', '/getInfo/', []);
    }

    /**
     * Requête générique vers l'API AllMySms (GET pour les consultations,
     * POST pour l'envoi de SMS dont le flux smsData peut dépasser la limite
     * de 1024 caractères imposée par le protocole http en GET).
     */
    private function call(string $method, string $path, array $params): ?array
    {
        $params += [
            'login'        => $this->login,
            'apiKey'       => $this->apiKey,
            'returnformat' => 'json',
        ];

        $url = self::API_BASE . $path;
        $ch  = curl_init();

        if ($method === 'GET') {
            curl_setopt($ch, CURLOPT_URL, $url . '?' . http_build_query($params));
        } else {
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);

        $body   = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($body === false) {
            error_log('[AllMySmsClient] Erreur cURL : ' . curl_error($ch));
            return null;
        }

        if ($status !== 200) {
            error_log('[AllMySmsClient] Réponse HTTP ' . $status . ' : ' . $body);
            return null;
        }

        $data = json_decode($body, true);
        if (!is_array($data)) {
            error_log('[AllMySmsClient] Réponse JSON invalide : ' . $body);
            return null;
        }

        return $data;
    }
}
