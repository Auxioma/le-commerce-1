<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateMessagingTables extends AbstractMigration
{
    public function change(): void
    {
        $commonOpts = [
            'id' => 'id',
            'signed' => false,
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ];

        // Journal des messages WhatsApp
        $this->table('whatsapp_messages', $commonOpts)
            ->addColumn('user_id', 'integer', ['signed' => false])
            ->addColumn('direction', 'enum', ['values' => ['sortant', 'entrant'], 'default' => 'sortant'])
            ->addColumn('content', 'text')
            ->addColumn('sent_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addForeignKey('user_id', 'users', 'id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])
            ->create();

        // Journal des messages SMS (miroir de whatsapp_messages)
        $this->table('sms_messages', $commonOpts)
            ->addColumn('user_id', 'integer', ['signed' => false])
            ->addColumn('direction', 'enum', ['values' => ['sortant', 'entrant'], 'default' => 'sortant'])
            ->addColumn('content', 'text')
            ->addColumn('sent_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addForeignKey('user_id', 'users', 'id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])
            ->create();

        // Messages programmés (email / sms / whatsapp), envoyés plus tard
        $this->table('scheduled_messages', $commonOpts)
            ->addColumn('user_id', 'integer', ['signed' => false])
            ->addColumn('channel', 'enum', ['values' => ['whatsapp', 'sms', 'email'], 'default' => 'whatsapp'])
            ->addColumn('content', 'text')
            ->addColumn('scheduled_at', 'datetime')
            ->addColumn('status', 'enum', ['values' => ['programme', 'envoye', 'annule'], 'default' => 'programme'])
            ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addForeignKey('user_id', 'users', 'id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])
            ->create();

        // Étiquettes libres posées sur un client
        $this->table('client_labels', $commonOpts)
            ->addColumn('user_id', 'integer', ['signed' => false])
            ->addColumn('label', 'string', ['limit' => 40])
            ->addColumn('color', 'string', ['limit' => 20, 'default' => 'gray'])
            ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['user_id', 'label'], ['unique' => true, 'name' => 'uniq_client_label'])
            ->addForeignKey('user_id', 'users', 'id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])
            ->create();

        // Notes internes admin sur un client
        $this->table('client_notes', $commonOpts)
            ->addColumn('user_id', 'integer', ['signed' => false])
            ->addColumn('content', 'text')
            ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addForeignKey('user_id', 'users', 'id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])
            ->create();
    }
}
