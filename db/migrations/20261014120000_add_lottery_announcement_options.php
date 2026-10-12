<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Annonce des loteries : choix du mode de diffusion (SMS et/ou e-mail) et
 * des segments de clients ciblés (voir LotteryAnnouncementService).
 * Ajoute aussi la désinscription des e-mails commerciaux côté client
 * (lien obligatoire dans chaque e-mail de communication).
 */
final class AddLotteryAnnouncementOptions extends AbstractMigration
{
    public function change(): void
    {
        $this->table('lotteries')
            ->addColumn('announce_channels', 'string', ['limit' => 20, 'null' => true, 'after' => 'qr_token'])
            ->addColumn('announce_segments', 'string', ['limit' => 500, 'null' => true, 'after' => 'announce_channels'])
            ->addColumn('email_recipients_count', 'integer', ['signed' => false, 'null' => true, 'after' => 'sms_recipients_count'])
            ->update();

        $this->table('users')
            ->addColumn('email_unsubscribed_at', 'datetime', ['null' => true, 'after' => 'whatsapp_opt_in'])
            ->update();
    }
}
