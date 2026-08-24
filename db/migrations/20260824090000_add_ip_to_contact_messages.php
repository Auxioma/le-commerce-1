<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddIpToContactMessages extends AbstractMigration
{
    public function change(): void
    {
        // Adresse IP de l'expéditeur, utilisée pour limiter la fréquence de
        // soumission du formulaire de contact public (anti-spam).
        $this->table('contact_messages')
            ->addColumn('ip', 'string', ['limit' => 45, 'null' => true, 'after' => 'message'])
            ->addIndex(['ip', 'created_at'])
            ->update();
    }
}
