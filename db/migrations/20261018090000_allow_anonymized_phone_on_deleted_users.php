<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * À la suppression, un client est anonymisé (e-mail aléatoire, téléphone
 * « 00 00 00 00 00 », voir User::anonymizeAndDelete()). Plusieurs clients
 * supprimés partagent donc le même numéro : l'unicité du téléphone ne porte
 * plus que sur les clients actifs, via la colonne générée phone_active
 * (= phone_whatsapp tant que le client n'est pas supprimé, NULL sinon ;
 * MySQL autorise plusieurs NULL sur un index unique).
 */
final class AllowAnonymizedPhoneOnDeletedUsers extends AbstractMigration
{
    public function up(): void
    {
        $this->execute(
            "ALTER TABLE users
                ADD COLUMN phone_active VARCHAR(20)
                    GENERATED ALWAYS AS (IF(deleted_at IS NULL, phone_whatsapp, NULL)) STORED,
                DROP INDEX phone_whatsapp,
                ADD UNIQUE INDEX phone_active (phone_active),
                ADD INDEX phone_whatsapp (phone_whatsapp)"
        );
    }

    /** NB : échoue si plusieurs clients supprimés ont déjà été anonymisés (numéros identiques). */
    public function down(): void
    {
        $this->execute(
            "ALTER TABLE users
                DROP INDEX phone_whatsapp,
                DROP INDEX phone_active,
                DROP COLUMN phone_active,
                ADD UNIQUE INDEX phone_whatsapp (phone_whatsapp)"
        );
    }
}
