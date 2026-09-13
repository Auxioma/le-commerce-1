<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class UpdateShopPhoneSetting extends AbstractMigration
{
    public function up(): void
    {
        $this->execute(
            "INSERT INTO settings (`key`, `value`) VALUES ('shop_phone', '02 35 90 50 16')
             ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)"
        );
    }

    public function down(): void
    {
        // Correction de donnee non reversible : ne pas restaurer un ancien numero public.
    }
}
