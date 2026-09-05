<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddLotterySmsCampaign extends AbstractMigration
{
    public function change(): void
    {
        // Identifiant de campagne AllMySms retourné lors de l'envoi du SMS
        // d'annonce (à la publication de la loterie), utilisé pour récupérer
        // les statistiques d'envoi (getCampaignStats) affichées dans
        // /admin/loterie. Nullable : les loteries en brouillon ou créées
        // avant cette fonctionnalité n'ont pas de campagne associée.
        $this->table('lotteries')
            ->addColumn('sms_campaign_id', 'string', ['limit' => 40, 'null' => true, 'after' => 'qr_token'])
            ->addColumn('sms_recipients_count', 'integer', ['signed' => false, 'null' => true, 'after' => 'sms_campaign_id'])
            ->update();
    }
}
