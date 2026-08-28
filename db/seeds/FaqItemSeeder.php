<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

final class FaqItemSeeder extends AbstractSeed
{
    public function run(): void
    {
        if ($this->fetchRow('SELECT 1 FROM faq_items LIMIT 1')) {
            return;
        }

        $rows = [];
        $order = 0;
        $add = function (string $category, string $question, string $answer) use (&$rows, &$order): void {
            $rows[] = [
                'category'     => $category,
                'question'     => $question,
                'answer'       => $answer,
                'sort_order'   => ++$order,
                'is_published' => 1,
            ];
        };

        // --- Mon compte ---
        $add('Mon compte', "Comment créer un compte fidélité ?",
            "Rendez-vous sur la page « Inscription » du site, renseignez votre prénom, votre nom et votre numéro de téléphone, puis choisissez un mot de passe. Votre portefeuille et votre code de parrainage sont créés automatiquement.");
        $add('Mon compte', "J'ai oublié mon mot de passe, que faire ?",
            "Sur la page de connexion, cliquez sur « Mot de passe oublié ». Vous recevrez un lien de réinitialisation valable 1 heure. Si vous n'avez pas d'adresse e-mail enregistrée, passez en boutique avec une pièce d'identité.");
        $add('Mon compte', "Comment modifier mes informations personnelles ?",
            "Depuis votre espace, ouvrez « Mes informations ». Vous pouvez y mettre à jour votre nom, votre téléphone, votre e-mail et votre adresse. Les changements sont pris en compte immédiatement.");
        $add('Mon compte', "Comment supprimer mon compte ?",
            "Envoyez-nous une demande via le formulaire en bas de cette page en choisissant le sujet « Mon compte ». Votre compte et vos données personnelles seront supprimés sous 30 jours, conformément au RGPD. Le solde éventuel de votre portefeuille vous sera remboursé.");

        // --- Portefeuille & paiement ---
        $add('Portefeuille & paiement', "Comment recharger mon portefeuille ?",
            "En caisse, indiquez le montant à créditer et réglez par carte, espèces, Apple Pay ou Google Pay. Le crédit est disponible instantanément et visible dans « Mes transactions ».");
        $add('Portefeuille & paiement', "Où puis-je utiliser le solde de mon portefeuille ?",
            "Le solde est utilisable pour tous vos achats en boutique (bar, tabac, presse, jeux, services). Présentez le QR code de votre portefeuille en caisse.");
        $add('Portefeuille & paiement', "Mon solde est-il remboursable ?",
            "Oui. Vous pouvez demander le remboursement du solde disponible à tout moment en caisse ou via le formulaire de cette page. Le remboursement est effectué par le même moyen de paiement que la dernière recharge.");
        $add('Portefeuille & paiement', "Je ne retrouve pas une recharge dans mon historique",
            "Les transactions apparaissent dans « Mes transactions » quelques secondes après le passage en caisse. Si une opération manque après 24 h, contactez-nous avec la date, le montant et le moyen de paiement utilisé.");

        // --- Offres & avantages ---
        $add('Offres & avantages', "Comment profiter d'une offre qui m'est proposée ?",
            "Ouvrez « Mes offres », présentez le code (ou le QR code) de l'offre en caisse avant sa date d'expiration. Chaque offre est utilisable une seule fois, sauf mention contraire.");
        $add('Offres & avantages', "Pourquoi je ne vois aucune offre ?",
            "Les offres dépendent de votre profil client (nouveau, occasionnel, fidèle) et des campagnes en cours. De nouvelles offres arrivent régulièrement : gardez un œil sur vos notifications.");
        $add('Offres & avantages', "Comment gagner des points de fidélité ?",
            "Vous cumulez des points à chaque achat et en participant aux sondages. Les points ouvrent droit à des avantages visibles dans « Mes avantages ».");

        // --- Loterie ---
        $add('Loterie', "Comment participer à une loterie ?",
            "Depuis « Loterie », cliquez sur « Participer » à la loterie active. Un ticket avec un code unique vous est attribué : une seule participation par personne et par tirage.");
        $add('Loterie', "Comment savoir si j'ai gagné ?",
            "À la date du tirage, le gagnant est désigné automatiquement. Vous êtes prévenu dans vos notifications et, le cas échéant, par message. Le lot est à retirer en boutique.");

        // --- Sondages ---
        $add('Sondages', "À quoi servent les sondages ?",
            "Ils nous aident à améliorer la boutique (choix des bières pression, matchs diffusés, nouveaux services…). Certains sondages offrent une récompense : points, crédit ou participation à un tirage.");
        $add('Sondages', "Puis-je changer ma réponse à un sondage ?",
            "Non, le vote est définitif pour garantir des résultats fiables. Prenez le temps de choisir avant de valider.");

        // --- Parrainage ---
        $add('Parrainage', "Comment fonctionne le parrainage ?",
            "Partagez votre code (visible dans « Parrainage »). Dès qu'un ami s'inscrit avec ce code et effectue sa première recharge, vous recevez automatiquement 10 € de crédit.");
        $add('Parrainage', "Y a-t-il une limite au nombre de filleuls ?",
            "Non, vous pouvez parrainer autant de personnes que vous le souhaitez. Chaque filleul validé vous rapporte 10 €.");

        // --- Géolocalisation & proximité ---
        $add('Géolocalisation & proximité', "Pourquoi le site me demande ma position ?",
            "Si vous avez activé les offres de proximité, votre position sert uniquement à vous envoyer une offre lorsque vous passez près de la boutique. Vous pouvez désactiver cette option à tout moment dans « Mes informations ».");
        $add('Géolocalisation & proximité', "Ma position est-elle enregistrée en permanence ?",
            "Non. Seule votre dernière position connue est conservée, et uniquement si vous avez donné votre accord. Elle n'est jamais partagée avec des tiers.");

        // --- Données personnelles ---
        $add('Données personnelles', "Que faites-vous de mes données ?",
            "Vos données servent à gérer votre compte fidélité, votre portefeuille et à vous adresser des offres pertinentes. Elles ne sont ni vendues ni cédées. Consultez notre politique de confidentialité pour le détail.");
        $add('Données personnelles', "Comment exercer mes droits RGPD ?",
            "Vous pouvez demander l'accès, la rectification ou la suppression de vos données via le formulaire de cette page (sujet « Données personnelles ») ou par courrier à l'adresse de la boutique.");

        $this->table('faq_items')->insert($rows)->saveData();
    }
}
