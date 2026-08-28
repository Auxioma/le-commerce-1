<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddUserIdToContactMessages extends AbstractMigration
{
    public function change(): void
    {
        // Lien optionnel vers le client connecté quand le message provient de
        // l'espace client (/mon-compte/aide) plutôt que du formulaire public
        // anonyme. Permet d'afficher au client l'historique de ses demandes.
        $this->table('contact_messages')
            ->addColumn('user_id', 'integer', ['signed' => false, 'null' => true, 'after' => 'id'])
            ->addIndex(['user_id'])
            ->addForeignKey('user_id', 'users', 'id', ['delete' => 'SET_NULL', 'update' => 'NO_ACTION'])
            ->update();
    }
}
