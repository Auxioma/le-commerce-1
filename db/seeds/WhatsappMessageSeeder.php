<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

/**
 * Fixture générée depuis la base de données (instantané du 2026-08-28).
 * Reproduit à l'identique le contenu de la table `whatsapp_messages`, identifiants inclus.
 */
final class WhatsappMessageSeeder extends AbstractSeed
{
    public function getDependencies(): array
    {
        return ['UserSeeder'];
    }

    public function run(): void
    {
        if ($this->fetchRow('SELECT 1 FROM `whatsapp_messages` LIMIT 1')) {
            return;
        }

        $this->table('whatsapp_messages')->insert([
            ['id' => 1, 'user_id' => 8, 'direction' => 'sortant', 'content' => '🎉 Félicitations devaux !
Vous avez remporté la loterie « Tirage de rentrée » : Panier gourmand (valeur 40€) !
Passez en boutique pour récupérer votre lot.', 'sent_at' => '2026-08-23 13:59:28'],
            ['id' => 2, 'user_id' => 8, 'direction' => 'sortant', 'content' => '🎉 Félicitations devaux !
Vous avez remporté la loterie « ppp » : voiture fereri !
Passez en boutique pour récupérer votre lot.', 'sent_at' => '2026-08-24 10:11:17'],
        ])->saveData();
    }
}
