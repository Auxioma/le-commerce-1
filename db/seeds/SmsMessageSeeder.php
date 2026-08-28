<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

/**
 * Fixture générée depuis la base de données (instantané du 2026-08-28).
 * Reproduit à l'identique le contenu de la table `sms_messages`, identifiants inclus.
 */
final class SmsMessageSeeder extends AbstractSeed
{
    public function getDependencies(): array
    {
        return ['UserSeeder'];
    }

    public function run(): void
    {
        if ($this->fetchRow('SELECT 1 FROM `sms_messages` LIMIT 1')) {
            return;
        }

        $this->table('sms_messages')->insert([
            ['id' => 1, 'user_id' => 1, 'direction' => 'sortant', 'content' => 'Bonjour Jean, votre solde portefeuille est de 58,40€. Merci de votre fidélité !', 'sent_at' => '2026-08-17 13:42:57'],
            ['id' => 2, 'user_id' => 2, 'direction' => 'entrant', 'content' => 'Bonjour, avez-vous encore des tickets pour le tirage FDJ de ce soir ?', 'sent_at' => '2026-08-18 13:42:57'],
            ['id' => 3, 'user_id' => 2, 'direction' => 'sortant', 'content' => 'Bonjour Sophie, oui il reste des tickets, à très vite !', 'sent_at' => '2026-08-18 13:42:57'],
        ])->saveData();
    }
}
