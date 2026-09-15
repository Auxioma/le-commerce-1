<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class UpdateOpeningHours extends AbstractMigration
{
    public function up(): void
    {
        $this->execute(
            "INSERT INTO settings (`key`, `value`) VALUES
                ('hours_lun_sam', '07h - 19h30'),
                ('hours_dim', '08h - 18h30')
             ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)"
        );
    }

    public function down(): void
    {
        // Public data correction: do not restore obsolete opening hours.
    }
}
