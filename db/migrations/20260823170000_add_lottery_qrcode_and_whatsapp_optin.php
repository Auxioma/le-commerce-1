<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddLotteryQrcodeAndWhatsappOptin extends AbstractMigration
{
    public function change(): void
    {
        // Jeton public unique encodé dans le QR code de chaque loterie
        // (URL publique : /loterie/{qr_token}). Nullable + index unique :
        // MySQL autorise plusieurs NULL sur un index unique, donc les
        // loteries déjà existantes (créées avant ce jeton) ne bloquent pas
        // la migration ; il est ensuite renseigné pour toutes les loteries,
        // existantes ou nouvelles, par l'application.
        $this->table('lotteries')
            ->addColumn('qr_token', 'string', ['limit' => 32, 'null' => true, 'after' => 'status'])
            ->addIndex(['qr_token'], ['unique' => true])
            ->update();

        // Consentement à être recontacté par WhatsApp (marketing), distinct
        // des messages transactionnels (ex. annonce de gain de loterie).
        // Collecté notamment via le formulaire public de participation à
        // une loterie (scan du QR code).
        $this->table('users')
            ->addColumn('whatsapp_opt_in', 'boolean', ['default' => false, 'after' => 'geolocation_opt_in'])
            ->changeColumn('registration_source', 'enum', [
                'values' => ['bar', 'tabac', 'jeux_services', 'pmu', 'nirio', 'loterie'],
                'default' => 'bar',
            ])
            ->update();
    }
}
