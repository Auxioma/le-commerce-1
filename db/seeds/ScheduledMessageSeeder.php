<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

final class ScheduledMessageSeeder extends AbstractSeed
{
    public function getDependencies(): array
    {
        return ['UserSeeder'];
    }

    public function run(): void
    {
        if ($this->fetchRow('SELECT 1 FROM scheduled_messages LIMIT 1')) {
            return;
        }

        $in = fn(string $interval) => date('Y-m-d H:i:s', strtotime("+{$interval}"));

        $this->table('scheduled_messages')->insert([
            ['user_id' => 1, 'channel' => 'whatsapp', 'content' => 'Rappel : votre offre Happy Hour expire ce soir à 20h !', 'scheduled_at' => $in('1 day'), 'status' => 'programme'],
            ['user_id' => 2, 'channel' => 'sms', 'content' => "N'oubliez pas de valider votre inscription à la loterie avant vendredi.", 'scheduled_at' => $in('2 days'), 'status' => 'programme'],
        ])->saveData();
    }
}
