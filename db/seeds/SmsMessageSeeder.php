<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

final class SmsMessageSeeder extends AbstractSeed
{
    public function getDependencies(): array
    {
        return ['UserSeeder'];
    }

    public function run(): void
    {
        if ($this->fetchRow('SELECT 1 FROM sms_messages LIMIT 1')) {
            return;
        }

        $ago = fn(string $interval) => date('Y-m-d H:i:s', strtotime("-{$interval}"));

        $this->table('sms_messages')->insert([
            ['user_id' => 1, 'direction' => 'sortant', 'content' => 'Bonjour Jean, votre solde portefeuille est de 58,40€. Merci de votre fidélité !', 'sent_at' => $ago('2 days')],
            ['user_id' => 2, 'direction' => 'entrant', 'content' => 'Bonjour, avez-vous encore des tickets pour le tirage FDJ de ce soir ?', 'sent_at' => $ago('1 day')],
            ['user_id' => 2, 'direction' => 'sortant', 'content' => 'Bonjour Sophie, oui il reste des tickets, à très vite !', 'sent_at' => $ago('1 day')],
        ])->saveData();
    }
}
