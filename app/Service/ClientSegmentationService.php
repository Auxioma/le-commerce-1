<?php

declare(strict_types=1);

namespace App\Service;

use App\Core\Database;

/**
 * Segmentation automatique des clients du bar, recalculée à chaque
 * consultation à partir de leur activité réelle :
 *   - achats et recharges du portefeuille (date, heure, montant),
 *   - offres utilisées en caisse,
 *   - participations aux loteries et sondages,
 *   - parrainages, code postal, date de naissance, entreprise.
 *
 * Une « visite » = un jour où le client a fait au moins un achat, une
 * recharge ou utilisé une offre.
 *
 * Deux familles de segments :
 *   - le CYCLE DE VIE : exactement un par client (Nouveau, Fidèle, Perdu...),
 *     attribué selon la première règle vérifiée dans l'ordre de LIFECYCLE ;
 *   - les PROFILS : cumulables (Afterwork, Gros consommateur, Anniversaire...).
 *
 * Toutes les règles et leurs seuils sont ici : pour ajuster la segmentation,
 * il suffit de modifier les constantes ci-dessous (la légende affichée dans
 * l'admin se met à jour toute seule, elle est générée depuis definitions()).
 */
class ClientSegmentationService
{
    // --- Seuils (modifiables) ------------------------------------------
    public const NEW_CLIENT_DAYS        = 30;  // inscrit depuis moins de X jours
    public const INACTIVE_AFTER_DAYS    = 60;  // aucune visite depuis X jours
    public const LOST_AFTER_DAYS        = 180; // aucune visite depuis X jours
    public const REACTIVATION_GAP_DAYS  = 60;  // absence avant le retour
    public const RECENT_DAYS            = 30;  // « récemment »
    public const FREQUENCY_WINDOW_DAYS  = 90;  // fenêtre de calcul de la fréquence
    public const VIP_VISITS             = 24;  // ≈ 2 visites / semaine sur 90 j
    public const LOYAL_VISITS           = 12;  // ≈ 1 visite / semaine
    public const REGULAR_VISITS         = 6;   // ≈ 1 visite / 2 semaines
    public const PREMIUM_TOP_PERCENT    = 10;  // top X % des dépenses sur 90 j
    public const PREMIUM_MIN_SPEND      = 100; // ... avec au moins X € dépensés
    public const BIG_TICKET             = 20;  // panier moyen ≥ X €
    public const SMALL_TICKET           = 5;   // panier moyen < X €
    public const MIN_PURCHASES          = 3;   // achats minimum pour juger les habitudes
    public const AMBASSADOR_REFERRALS   = 1;   // clients parrainés
    public const EVENT_PARTICIPATIONS   = 2;   // loteries + sondages
    public const BIRTHDAY_DAYS          = 30;  // anniversaire dans les X prochains jours
    public const LOCAL_DEPARTMENTS      = ['76', '27', '60', '80']; // Seine-Maritime + voisins

    /** Ordre = priorité : la première règle vérifiée donne le cycle de vie. */
    private const LIFECYCLE = ['nouveau', 'potentiel', 'perdu', 'inactif', 'reactive', 'de_passage', 'vip', 'fidele', 'regulier', 'occasionnel'];

    private const PROFILES = ['premium', 'a_fideliser', 'ambassadeur', 'gros_consommateur', 'petit_consommateur', 'evenementiel', 'anniversaire', 'afterwork', 'touristique', 'professionnel'];

    /** Correspondance avec l'ancien champ users.segment (offres, zonage, tableau de bord). */
    private const LEGACY_SEGMENT = [
        'nouveau' => 'nouveau', 'potentiel' => 'nouveau',
        'vip' => 'fidele', 'fidele' => 'fidele', 'regulier' => 'fidele',
    ];

    /** @var array<int, array>|null cache de la requête en cours */
    private static ?array $cache = null;

    /**
     * Libellé, couleur et règle (en clair) de chaque segment, pour la légende.
     *
     * @return array{lifecycle: array<string, array{label:string, color:string, rule:string}>, profiles: array<string, array{label:string, color:string, rule:string}>}
     */
    public static function definitions(): array
    {
        $w = self::FREQUENCY_WINDOW_DAYS;

        return [
            'lifecycle' => [
                'nouveau'     => ['label' => 'Client nouveau', 'color' => 'bg-blue-100 text-blue-800',
                    'rule' => 'Inscrit depuis moins de ' . self::NEW_CLIENT_DAYS . ' jours.'],
                'potentiel'   => ['label' => 'Client potentiel', 'color' => 'bg-sky-100 text-sky-800',
                    'rule' => 'Inscrit, mais aucune visite enregistrée (ni achat, ni recharge, ni offre utilisée).'],
                'perdu'       => ['label' => 'Client perdu', 'color' => 'bg-red-100 text-red-800',
                    'rule' => 'Aucune visite depuis plus de ' . self::LOST_AFTER_DAYS . ' jours.'],
                'inactif'     => ['label' => 'Client inactif', 'color' => 'bg-gray-200 text-gray-700',
                    'rule' => 'Aucune visite depuis plus de ' . self::INACTIVE_AFTER_DAYS . ' jours.'],
                'reactive'    => ['label' => 'Client réactivé', 'color' => 'bg-lime-100 text-lime-800',
                    'rule' => 'Revenu dans les ' . self::RECENT_DAYS . ' derniers jours après au moins ' . self::REACTIVATION_GAP_DAYS . ' jours d\'absence.'],
                'de_passage'  => ['label' => 'Client de passage', 'color' => 'bg-stone-100 text-stone-700',
                    'rule' => 'Une seule visite au total.'],
                'vip'         => ['label' => 'Client VIP', 'color' => 'bg-amber-100 text-amber-800',
                    'rule' => 'Au moins ' . self::VIP_VISITS . ' visites sur les ' . $w . ' derniers jours (2 fois par semaine ou plus).'],
                'fidele'      => ['label' => 'Client fidèle', 'color' => 'bg-green-100 text-green-800',
                    'rule' => 'Au moins ' . self::LOYAL_VISITS . ' visites sur les ' . $w . ' derniers jours (environ 1 fois par semaine).'],
                'regulier'    => ['label' => 'Client régulier', 'color' => 'bg-emerald-50 text-emerald-700',
                    'rule' => 'Au moins ' . self::REGULAR_VISITS . ' visites sur les ' . $w . ' derniers jours (environ toutes les 2 semaines).'],
                'occasionnel' => ['label' => 'Client occasionnel', 'color' => 'bg-yellow-100 text-yellow-800',
                    'rule' => 'Vient de temps en temps : moins de ' . self::REGULAR_VISITS . ' visites sur les ' . $w . ' derniers jours.'],
            ],
            'profiles' => [
                'premium'            => ['label' => 'Client premium', 'color' => 'bg-purple-100 text-purple-800',
                    'rule' => 'Fait partie des ' . self::PREMIUM_TOP_PERCENT . ' % de clients qui dépensent le plus sur ' . $w . ' jours (minimum ' . self::PREMIUM_MIN_SPEND . ' €).'],
                'a_fideliser'        => ['label' => 'Client à fidéliser', 'color' => 'bg-orange-100 text-orange-800',
                    'rule' => 'Venu dans les ' . self::RECENT_DAYS . ' derniers jours mais moins d\'une fois par semaine : le bon moment pour une offre.'],
                'ambassadeur'        => ['label' => 'Client ambassadeur', 'color' => 'bg-pink-100 text-pink-800',
                    'rule' => 'A fait inscrire au moins ' . self::AMBASSADOR_REFERRALS . ' client' . (self::AMBASSADOR_REFERRALS > 1 ? 's' : '') . ' grâce au parrainage.'],
                'gros_consommateur'  => ['label' => 'Gros consommateur', 'color' => 'bg-rose-100 text-rose-800',
                    'rule' => 'Panier moyen d\'au moins ' . self::BIG_TICKET . ' € (sur ' . self::MIN_PURCHASES . ' achats minimum).'],
                'petit_consommateur' => ['label' => 'Petit consommateur', 'color' => 'bg-slate-100 text-slate-700',
                    'rule' => 'Panier moyen inférieur à ' . self::SMALL_TICKET . ' € (sur ' . self::MIN_PURCHASES . ' achats minimum).'],
                'evenementiel'       => ['label' => 'Client événementiel', 'color' => 'bg-fuchsia-100 text-fuchsia-800',
                    'rule' => 'Au moins ' . self::EVENT_PARTICIPATIONS . ' participations aux loteries/sondages, ou la moitié de ses visites le vendredi/samedi soir (après 20 h).'],
                'anniversaire'       => ['label' => 'Client anniversaire', 'color' => 'bg-red-50 text-red-600',
                    'rule' => 'Son anniversaire tombe dans les ' . self::BIRTHDAY_DAYS . ' prochains jours (date de naissance renseignée sur sa fiche).'],
                'afterwork'          => ['label' => 'Client afterwork', 'color' => 'bg-indigo-100 text-indigo-800',
                    'rule' => 'Au moins la moitié de ses achats en semaine entre 17 h et 20 h (' . self::MIN_PURCHASES . ' achats minimum).'],
                'touristique'        => ['label' => 'Client touristique', 'color' => 'bg-cyan-100 text-cyan-800',
                    'rule' => 'Code postal hors Seine-Maritime et départements voisins (' . implode(', ', self::LOCAL_DEPARTMENTS) . ').'],
                'professionnel'      => ['label' => 'Client professionnel', 'color' => 'bg-teal-100 text-teal-800',
                    'rule' => 'Une entreprise est renseignée sur sa fiche.'],
            ],
        ];
    }

    /** @return array<string, array{label:string, color:string, rule:string}> tous les segments, à plat */
    public static function allDefinitions(): array
    {
        $defs = self::definitions();
        return $defs['lifecycle'] + $defs['profiles'];
    }

    /**
     * Segments de tous les clients, indexés par id :
     * [id => ['lifecycle' => key, 'profiles' => [key...], 'reasons' => [key => texte], 'metrics' => [...]]]
     */
    public static function computeAll(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        $db = Database::connection();
        $today = new \DateTimeImmutable('today');
        $windowStart = $today->modify('-' . self::FREQUENCY_WINDOW_DAYS . ' days')->format('Y-m-d');

        $clients = $db->query(
            "SELECT u.id, u.created_at, u.postal_code, u.birth_date, u.company_name, u.segment,
                    (SELECT COUNT(*) FROM users r WHERE r.referred_by = u.id AND r.deleted_at IS NULL) AS referrals,
                    (SELECT COUNT(*) FROM lottery_entries le WHERE le.user_id = u.id)
                  + (SELECT COUNT(*) FROM poll_votes pv WHERE pv.user_id = u.id) AS participations
             FROM users u
             WHERE u.role = 'client' AND u.deleted_at IS NULL"
        )->fetchAll();

        // Une ligne par client et par jour de visite (achats/recharges réussis + offres utilisées).
        $visitRows = $db->query(
            "SELECT user_id, day, SUM(spent) AS spent, SUM(purchases) AS purchases,
                    SUM(afterwork) AS afterwork, MAX(weekend_night) AS weekend_night
             FROM (
                 SELECT w.user_id, DATE(t.created_at) AS day,
                        IF(t.type = 'debit', t.amount, 0) AS spent,
                        t.type = 'debit' AS purchases,
                        t.type = 'debit' AND WEEKDAY(t.created_at) < 5 AND HOUR(t.created_at) BETWEEN 17 AND 19 AS afterwork,
                        DAYOFWEEK(t.created_at) IN (6, 7) AND HOUR(t.created_at) >= 20 AS weekend_night
                 FROM wallet_transactions t JOIN wallets w ON w.id = t.wallet_id
                 WHERE t.status = 'reussi' AND t.type IN ('debit', 'recharge')
                 UNION ALL
                 SELECT o.user_id, DATE(o.used_at), 0, 0, 0,
                        DAYOFWEEK(o.used_at) IN (6, 7) AND HOUR(o.used_at) >= 20
                 FROM offer_redemptions o
                 WHERE o.status = 'utilisee' AND o.used_at IS NOT NULL
             ) v
             GROUP BY user_id, day
             ORDER BY user_id, day"
        )->fetchAll();

        $visits = [];
        foreach ($visitRows as $row) {
            $visits[(int) $row['user_id']][] = $row;
        }

        // Métriques par client
        $metrics = [];
        foreach ($clients as $client) {
            $id = (int) $client['id'];
            $days = $visits[$id] ?? [];
            $m = [
                'signup_days'    => (int) (new \DateTimeImmutable(substr((string) $client['created_at'], 0, 10)))->diff($today)->days,
                'visits_total'   => count($days),
                'visits_window'  => 0,
                'last_visit'     => null,
                'days_since_last'=> null,
                'spend_window'   => 0.0,
                'spend_total'    => 0.0,
                'purchases'      => 0,
                'afterwork'      => 0,
                'weekend_nights' => 0,
                'reactivated'    => false,
                'referrals'      => (int) $client['referrals'],
                'participations' => (int) $client['participations'],
            ];

            $previousDay = null;
            foreach ($days as $day) {
                $m['spend_total'] += (float) $day['spent'];
                $m['purchases']   += (int) $day['purchases'];
                $m['afterwork']   += (int) $day['afterwork'];
                $m['weekend_nights'] += (int) $day['weekend_night'];
                if ($day['day'] >= $windowStart) {
                    $m['visits_window']++;
                    $m['spend_window'] += (float) $day['spent'];
                }
                // Retour après une longue absence, dans la période récente
                if ($previousDay !== null) {
                    $gap = (int) (new \DateTimeImmutable($previousDay))->diff(new \DateTimeImmutable($day['day']))->days;
                    $sinceVisit = (int) (new \DateTimeImmutable($day['day']))->diff($today)->days;
                    if ($gap >= self::REACTIVATION_GAP_DAYS && $sinceVisit <= self::RECENT_DAYS) {
                        $m['reactivated'] = true;
                    }
                }
                $previousDay = $day['day'];
            }
            if ($previousDay !== null) {
                $m['last_visit'] = $previousDay;
                $m['days_since_last'] = (int) (new \DateTimeImmutable($previousDay))->diff($today)->days;
            }
            $m['avg_ticket'] = $m['purchases'] > 0 ? $m['spend_total'] / $m['purchases'] : 0.0;

            $metrics[$id] = $m;
        }

        $premiumThreshold = self::premiumThreshold($metrics);

        $result = [];
        foreach ($clients as $client) {
            $id = (int) $client['id'];
            $m = $metrics[$id];
            [$lifecycle, $lifecycleReason] = self::lifecycleFor($m);
            $profiles = self::profilesFor($client, $m, $premiumThreshold, $lifecycle, $today);

            $result[$id] = [
                'lifecycle' => $lifecycle,
                'profiles'  => array_keys($profiles),
                'reasons'   => [$lifecycle => $lifecycleReason] + $profiles,
                'metrics'   => $m,
            ];
        }

        self::syncLegacySegments($clients, $result);

        return self::$cache = $result;
    }

    public static function forUser(int $id): ?array
    {
        return self::computeAll()[$id] ?? null;
    }

    /** Ids des clients appartenant à un segment (cycle de vie ou profil). */
    public static function idsInSegment(string $segment): array
    {
        $ids = [];
        foreach (self::computeAll() as $id => $seg) {
            if ($seg['lifecycle'] === $segment || in_array($segment, $seg['profiles'], true)) {
                $ids[] = $id;
            }
        }
        return $ids;
    }

    /** Nombre de clients par segment. */
    public static function counts(): array
    {
        $counts = array_fill_keys(array_keys(self::allDefinitions()), 0);
        foreach (self::computeAll() as $seg) {
            $counts[$seg['lifecycle']]++;
            foreach ($seg['profiles'] as $profile) {
                $counts[$profile]++;
            }
        }
        return $counts;
    }

    // ------------------------------------------------------------------

    /** @return array{0:string, 1:string} [segment, raison] */
    private static function lifecycleFor(array $m): array
    {
        $w = self::FREQUENCY_WINDOW_DAYS;
        $visitsLabel = $m['visits_window'] . ' visite' . ($m['visits_window'] > 1 ? 's' : '') . ' sur les ' . $w . ' derniers jours';

        return match (true) {
            $m['signup_days'] < self::NEW_CLIENT_DAYS
                => ['nouveau', 'Inscrit il y a ' . $m['signup_days'] . ' jour' . ($m['signup_days'] > 1 ? 's' : '') . '.'],
            $m['visits_total'] === 0
                => ['potentiel', 'Aucune visite depuis son inscription il y a ' . $m['signup_days'] . ' jours.'],
            $m['days_since_last'] > self::LOST_AFTER_DAYS
                => ['perdu', 'Dernière visite il y a ' . $m['days_since_last'] . ' jours.'],
            $m['days_since_last'] > self::INACTIVE_AFTER_DAYS
                => ['inactif', 'Dernière visite il y a ' . $m['days_since_last'] . ' jours.'],
            $m['reactivated']
                => ['reactive', 'Revenu récemment après plus de ' . self::REACTIVATION_GAP_DAYS . ' jours d\'absence.'],
            $m['visits_total'] === 1
                => ['de_passage', 'Une seule visite, le ' . date('d/m/Y', strtotime($m['last_visit'])) . '.'],
            $m['visits_window'] >= self::VIP_VISITS    => ['vip', ucfirst($visitsLabel) . '.'],
            $m['visits_window'] >= self::LOYAL_VISITS  => ['fidele', ucfirst($visitsLabel) . '.'],
            $m['visits_window'] >= self::REGULAR_VISITS => ['regulier', ucfirst($visitsLabel) . '.'],
            default => ['occasionnel', ucfirst($visitsLabel) . ' (' . $m['visits_total'] . ' au total).'],
        };
    }

    /** @return array<string, string> [profil => raison] */
    private static function profilesFor(array $client, array $m, float $premiumThreshold, string $lifecycle, \DateTimeImmutable $today): array
    {
        $euros = static fn(float $v): string => number_format($v, 2, ',', ' ') . ' €';
        $profiles = [];

        if ($m['spend_window'] > 0 && $m['spend_window'] >= $premiumThreshold) {
            $profiles['premium'] = $euros($m['spend_window']) . ' dépensés sur ' . self::FREQUENCY_WINDOW_DAYS . ' jours.';
        }
        if ($m['days_since_last'] !== null && $m['days_since_last'] <= self::RECENT_DAYS
            && $m['visits_window'] < self::LOYAL_VISITS && $lifecycle !== 'nouveau') {
            $profiles['a_fideliser'] = 'Dernière visite il y a ' . $m['days_since_last'] . ' j, ' . $m['visits_window'] . ' visite(s) sur ' . self::FREQUENCY_WINDOW_DAYS . ' j.';
        }
        if ($m['referrals'] >= self::AMBASSADOR_REFERRALS) {
            $profiles['ambassadeur'] = $m['referrals'] . ' client' . ($m['referrals'] > 1 ? 's' : '') . ' parrainé' . ($m['referrals'] > 1 ? 's' : '') . '.';
        }
        if ($m['purchases'] >= self::MIN_PURCHASES && $m['avg_ticket'] >= self::BIG_TICKET) {
            $profiles['gros_consommateur'] = 'Panier moyen de ' . $euros($m['avg_ticket']) . ' sur ' . $m['purchases'] . ' achats.';
        }
        if ($m['purchases'] >= self::MIN_PURCHASES && $m['avg_ticket'] < self::SMALL_TICKET) {
            $profiles['petit_consommateur'] = 'Panier moyen de ' . $euros($m['avg_ticket']) . ' sur ' . $m['purchases'] . ' achats.';
        }
        $weekendShare = $m['visits_total'] > 0 ? $m['weekend_nights'] / $m['visits_total'] : 0;
        if ($m['participations'] >= self::EVENT_PARTICIPATIONS) {
            $profiles['evenementiel'] = $m['participations'] . ' participations aux loteries et sondages.';
        } elseif ($m['visits_total'] >= self::MIN_PURCHASES && $weekendShare >= 0.5) {
            $profiles['evenementiel'] = round($weekendShare * 100) . ' % de ses visites le vendredi ou samedi soir.';
        }
        if (!empty($client['birth_date'])) {
            $daysToBirthday = self::daysUntilBirthday((string) $client['birth_date'], $today);
            if ($daysToBirthday <= self::BIRTHDAY_DAYS) {
                $profiles['anniversaire'] = $daysToBirthday === 0
                    ? 'C\'est son anniversaire aujourd\'hui !'
                    : 'Anniversaire dans ' . $daysToBirthday . ' jour' . ($daysToBirthday > 1 ? 's' : '') . ' (' . date('d/m', strtotime((string) $client['birth_date'])) . ').';
            }
        }
        if ($m['purchases'] >= self::MIN_PURCHASES && $m['afterwork'] / $m['purchases'] >= 0.5) {
            $profiles['afterwork'] = round($m['afterwork'] / $m['purchases'] * 100) . ' % de ses achats en semaine entre 17 h et 20 h.';
        }
        $postalCode = trim((string) ($client['postal_code'] ?? ''));
        if ($postalCode !== '' && !in_array(substr($postalCode, 0, 2), self::LOCAL_DEPARTMENTS, true)) {
            $profiles['touristique'] = 'Code postal ' . $postalCode . '.';
        }
        if (trim((string) ($client['company_name'] ?? '')) !== '') {
            $profiles['professionnel'] = 'Entreprise : ' . trim((string) $client['company_name']) . '.';
        }

        return $profiles;
    }

    /** Seuil « premium » : top X % des dépenses sur la fenêtre, avec un minimum. */
    private static function premiumThreshold(array $metrics): float
    {
        $spends = array_values(array_filter(array_column($metrics, 'spend_window'), static fn($v) => $v > 0));
        if (!$spends) {
            return INF;
        }
        rsort($spends);
        $index = max(0, (int) ceil(count($spends) * self::PREMIUM_TOP_PERCENT / 100) - 1);
        return max((float) self::PREMIUM_MIN_SPEND, (float) $spends[$index]);
    }

    private static function daysUntilBirthday(string $birthDate, \DateTimeImmutable $today): int
    {
        $month = (int) substr($birthDate, 5, 2);
        $day   = (int) substr($birthDate, 8, 2);
        $year  = (int) $today->format('Y');
        // 29 février : fêté le 28 les années non bissextiles
        $make = static fn(int $y) => new \DateTimeImmutable(sprintf('%04d-%02d-%02d', $y, $month, $month === 2 && $day === 29 && !checkdate(2, 29, $y) ? 28 : $day));
        $next = $make($year);
        if ($next < $today) {
            $next = $make($year + 1);
        }
        return (int) $today->diff($next)->days;
    }

    /**
     * Tient à jour l'ancien champ users.segment (nouveau / fidèle /
     * occasionnel), encore utilisé par le ciblage des offres et du zonage.
     */
    private static function syncLegacySegments(array $clients, array $result): void
    {
        $stmt = null;
        foreach ($clients as $client) {
            $id = (int) $client['id'];
            $legacy = self::LEGACY_SEGMENT[$result[$id]['lifecycle']] ?? 'occasionnel';
            if ($client['segment'] !== $legacy) {
                $stmt ??= Database::connection()->prepare('UPDATE users SET segment = :segment, updated_at = updated_at WHERE id = :id');
                $stmt->execute(['segment' => $legacy, 'id' => $id]);
            }
        }
    }
}
