<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddStandardRegistrationSources extends AbstractMigration
{
    /**
     * Le champ "Comment nous avez-vous connu ?" du formulaire d'inscription
     * proposait jusqu'ici des rayons du magasin (bar/tabac/pmu/nirio...),
     * alors qu'il s'agit d'une question marketing classique : on ajoute les
     * choix standards habituels (bouche à oreille, réseaux sociaux,
     * recherche internet, passage devant la boutique, publicité, autre),
     * sans retirer les anciennes valeurs pour ne pas invalider les comptes
     * déjà enregistrés ni casser les graphiques d'acquisition existants.
     */
    public function change(): void
    {
        $this->table('users')
            ->changeColumn('registration_source', 'enum', [
                'values' => [
                    'bar', 'tabac', 'jeux_services', 'pmu', 'nirio', 'loterie',
                    'bouche_a_oreille', 'reseaux_sociaux', 'recherche_internet',
                    'passage_devant', 'publicite', 'autre',
                ],
                'default' => 'bar',
            ])
            ->update();
    }
}
