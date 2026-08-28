<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

/**
 * Fixture générée depuis la base de données (instantané du 2026-08-28).
 * Reproduit à l'identique le contenu de la table `scheduled_messages`, identifiants inclus.
 */
final class ScheduledMessageSeeder extends AbstractSeed
{
    public function getDependencies(): array
    {
        return ['UserSeeder'];
    }

    public function run(): void
    {
        if ($this->fetchRow('SELECT 1 FROM `scheduled_messages` LIMIT 1')) {
            return;
        }

        $this->table('scheduled_messages')->insert([
            ['id' => 1, 'user_id' => 1, 'channel' => 'whatsapp', 'content' => 'Rappel : votre offre Happy Hour expire ce soir à 20h !', 'scheduled_at' => '2026-08-20 13:42:57', 'status' => 'programme', 'created_at' => '2026-08-19 15:42:57'],
            ['id' => 2, 'user_id' => 2, 'channel' => 'sms', 'content' => 'N\'oubliez pas de valider votre inscription à la loterie avant vendredi.', 'scheduled_at' => '2026-08-21 13:42:57', 'status' => 'programme', 'created_at' => '2026-08-19 15:42:57'],
        ])->saveData();
    }
}
