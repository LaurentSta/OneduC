<?php

namespace App\Data;

class ParcoursFormateur
{
    /**
     * @return array<string, array<string, mixed>>
     */
    public static function rawModules(): array
    {
        return [
            'prendre-ses-reperes' => self::moduleOne(),
            'organiser-ses-parcours' => self::moduleTwo(),
            'gerer-ses-groupes' => self::placeholderModule(
                'Module 3',
                'Parcours apprenant',
                'Comprendre le parcours apprenant pour ajuster son accompagnement',
                'Contenu a venir'
            ),
            'suivre-et-accompagner' => self::placeholderModule(
                'Module 4',
                'Engagement et gamification',
                'Mobiliser les leviers d engagement et de gamification',
                'Contenu a venir'
            ),
            'trouver-de-laide' => self::placeholderModule(
                'Module 5',
                'Prendre du recul',
                'Prendre du recul sur sa pratique dans un environnement numerique',
                'Contenu a venir'
            ),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function moduleOne(): array
    {
        return [
            'label' => 'Module 1',
            'title' => 'Decouvrir la plateforme',
            'full_title' => 'Decouvrir les fonctionnalites cles de la plateforme pour le formateur',
            'description' => 'Prendre ses reperes, comprendre les fonctions utiles et clarifier son role.',
            'specific_objective' => 'Prendre ses reperes dans l espace formateur.',
            'duration_label' => '38 a 39 min',
            'status_label' => 'Disponible',
            'is_under_construction' => true,
            'construction_label' => 'En cours de construction',
            'construction_note' => 'Ce module est en cours de construction. Certains contenus, exercices ou liens peuvent encore etre ajustes.',
            'trainer_name' => 'Equipe Oneduc',
            'level_label' => 'Tous niveaux',
            'cta_label' => 'Entrer dans le module',
            'progress_percentage' => 0,
            'presentation_video_embed_url' => null,
            'presentation_video_title' => 'Video de presentation du module 1',
            'presentation_video_note' => 'Emplacement prevu pour une courte video de presentation du module.',
            'presentation' => [
                'Prenez vos reperes dans Oneduc.',
                'Reperez les rubriques utiles et les premiers indicateurs.',
                'Clarifiez votre role et vos limites d action.',
            ],
            'goals' => [
                'Se reperer dans l espace formateur.',
                'Relier les fonctions aux besoins du terrain.',
                'Comprendre son perimetre d action.',
            ],
            'prerequisites' => [
                'Disposer d un acces formateur a la plateforme.',
                'Avoir realise une premiere connexion ou etre sur le point de la faire.',
                'Aucun prerequis technique supplementaire n est necessaire.',
            ],
            'chapters' => [
                'premiere-connexion' => [
                    'label' => 'Chapitre',
                    'pedagogical_label' => 'Objectif pedagogique',
                    'code' => '1.1',
                    'title' => 'Se reperer dans l espace formateur',
                    'description' => 'Identifier les zones cles, les fonctions utiles et les premiers indicateurs.',
                    'duration_label' => '23 min',
                    'objective' => 'Se reperer et trouver les fonctions utiles.',
                    'progress_percentage' => 0,
                    'lessons' => [
                        'entree-plateforme-espace-formateur' => self::lesson(
                            '1.1.1',
                            'Reperer la navigation',
                            '6 min',
                            'Trouver les zones cles de navigation.',
                            [
                                'pedagogical_intention' => 'Faire agir le formateur des la premiere minute plutot que de le laisser regarder passivement.',
                                'method' => 'Demonstrative interactive puis active guidee',
                                'learning_process' => 'S informer, se motiver, agir',
                                'subject' => 'Tableau de bord, menu lateral, acces rapides, parcours, groupes, outils et parametres.',
                                'activity' => 'Video-promesse courte puis capsule guidee avec interactions et hotspots.',
                                'evaluation' => 'Reussir 3 interactions sur 4 et associer au moins 5 hotspots sur 6 a leur usage.',
                            ]
                        ),
                        'rubriques-cles-usages-immediats' => self::lesson(
                            '1.1.2',
                            'Associer besoin et fonction',
                            '10 min',
                            'Relier un besoin a la bonne fonction.',
                            [
                                'pedagogical_intention' => 'Partir du besoin du formateur pour trouver la bonne fonctionnalite plutot que memoriser un catalogue d outils.',
                                'method' => 'Active guidee par mini-cas puis interrogative',
                                'learning_process' => 'S informer, associer',
                                'subject' => 'Fonctionnalites classees par usage : parcours, groupes, suivi, animation, reglages et notifications.',
                                'activity' => 'Casting des fonctionnalites a partir de mini-cas formules en "Je veux...".',
                                'evaluation' => 'Associer la bonne fonctionnalite au besoin pour 4 cas sur 5.',
                            ]
                        ),
                        'indicateurs-suivi' => self::lesson(
                            '1.1.3',
                            'Lire les premiers indicateurs',
                            '7 min',
                            'Reperer progression, engagement et reussite.',
                            [
                                'pedagogical_intention' => 'Montrer que les indicateurs servent a accompagner les apprenants, pas seulement a les consulter.',
                                'method' => 'Active et reflexive',
                                'learning_process' => 'S informer, categoriser',
                                'subject' => 'Progression, engagement et reussite : familles d indicateurs et localisation dans l espace formateur.',
                                'activity' => 'Exploration guidee d une capture du tableau de bord puis tri d indicateurs anonymises.',
                                'evaluation' => 'Classer 6 indicateurs sur 7 dans la bonne famille.',
                            ]
                        ),
                    ],
                ],
                'situer-role-formateur' => [
                    'label' => 'Chapitre',
                    'pedagogical_label' => 'Objectif pedagogique',
                    'code' => '1.2',
                    'title' => 'Comprendre son role',
                    'description' => 'Distinguer les profils, les droits et les limites d intervention.',
                    'duration_label' => '15 a 16 min',
                    'objective' => 'Savoir ce que le formateur peut faire.',
                    'progress_percentage' => 0,
                    'lessons' => [
                        'comparer-profils-acces' => self::lesson(
                            '1.2.1',
                            'Distinguer les profils',
                            '6 min',
                            'Comparer les profils et leurs acces.',
                            [
                                'pedagogical_intention' => 'Aider le formateur a reperer son perimetre d action dans Oneduc.',
                                'method' => 'Decouverte guidee par categorisation puis active',
                                'learning_process' => 'S informer, categoriser',
                                'subject' => 'Profils de la plateforme, droits associes, ce que chacun voit, peut faire ou ne peut pas faire.',
                                'activity' => 'Carte des terrains : classer des cartes-actions selon le profil competent.',
                                'evaluation' => 'Placer 7 cartes-actions sur 8 sur le bon terrain.',
                            ]
                        ),
                        'delimiter-role-formateur' => self::lesson(
                            '1.2.2',
                            'Delimiter son perimetre',
                            '6 min',
                            'Identifier ce qui releve du formateur.',
                            [
                                'pedagogical_intention' => 'Faire identifier les zones grises ou le formateur doit decider s il agit ou s il passe la main.',
                                'method' => 'Etude de cas puis interrogative',
                                'learning_process' => 'Analyser, decider',
                                'subject' => 'Situations-frontieres : demande stagiaire, intervention technique, modification globale ou action hors perimetre.',
                                'activity' => 'Que feriez-vous ? Mini-scenarios avec choix de posture.',
                                'evaluation' => 'Identifier la bonne posture pour 3 scenarios sur 4.',
                            ]
                        ),
                        'bilan-module-1' => self::lesson(
                            '1.2.3',
                            'Bilan du module 1 et ouverture vers le module 2',
                            '3 a 4 min',
                            'Faire le point avant le module 2.',
                            [
                                'type' => 'bilan',
                                'pedagogical_intention' => 'Marquer la progression, favoriser la prise de conscience et amorcer la mise en place d un environnement de formation.',
                                'method' => 'Transmissive courte puis interrogative',
                                'learning_process' => 'Synthetiser, se projeter',
                                'subject' => 'Synthese des reperes acquis : espace, fonctionnalites, suivi et role du formateur.',
                                'activity' => 'Vignette de synthese et ouverture sur le module 2.',
                                'evaluation' => 'Formuler les 3 acquis cles du module 1 et identifier la lecon suivante.',
                            ]
                        ),
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function moduleTwo(): array
    {
        return [
            'label' => 'Module 2',
            'title' => 'Creer son environnement',
            'full_title' => 'Mettre en place un premier environnement de formation dans Oneduc',
            'description' => 'Preparer un groupe, organiser les modules et securiser les acces.',
            'specific_objective' => 'Creer un environnement de formation pret a utiliser.',
            'duration_label' => '48 a 51 min',
            'presentation_video_embed_url' => null,
            'presentation_video_title' => 'Video de presentation du module 2',
            'presentation_video_note' => 'Emplacement prevu pour une courte video de presentation du module.',
            'introduction_scorm_directory' => 'modules/parcours_formateur/module_2_organiser_ses_parcours/introduction',
            'status_label' => 'Disponible',
            'trainer_name' => 'Equipe Oneduc',
            'level_label' => 'Tous niveaux',
            'cta_label' => 'Entrer dans le module',
            'progress_percentage' => 0,
            'presentation' => [
                'Preparez votre premier environnement de formation.',
                'Creez le groupe, ajoutez les stagiaires et organisez les modules.',
                'Verifiez que les acces sont prets avant l ouverture.',
            ],
            'goals' => [
                'Preparer les informations utiles.',
                'Creer un groupe de formation.',
                'Organiser les modules et les acces.',
                'Ajuster le groupe si besoin.',
            ],
            'prerequisites' => [
                'Avoir parcouru le module 1 ou disposer deja des reperes de base dans l espace formateur.',
                'Connaitre les rubriques principales liees aux parcours, aux groupes et au suivi.',
                'Aucun prerequis technique supplementaire n est necessaire.',
            ],
            'chapters' => [
                'preparer-les-contenus' => [
                    'label' => 'Chapitre',
                    'pedagogical_label' => 'Objectif pedagogique',
                    'code' => '2.1',
                    'title' => 'Preparer avant de creer',
                    'description' => 'Ce chapitre installe le reflexe de preparation : reconnaitre les zones du formulaire, distinguer ce qui releve des informations, des stagiaires et des modules, puis identifier ce qui bloque vraiment la creation d un groupe.',
                    'duration_label' => '11 min',
                    'objective' => 'Verifier que les informations indispensables sont pretes avant d ouvrir un groupe dans Oneduc.',
                    'tip' => 'Avant de creer, le formateur gagne du temps en separant trois choses : les parametres du groupe, les personnes a inscrire et les contenus a proposer.',
                    'progress_percentage' => 0,
                    'lessons' => [
                        'retrouver-les-espaces-de-preparation' => self::lesson(
                            '2.1.1',
                            'Identifier les elements essentiels',
                            '5 min',
                            'Identifier les elements indispensables.',
                            [
                                'pedagogical_intention' => 'Faire reperer les trois composantes principales de l interface Oneduc : informations, stagiaires et modules.',
                                'method' => 'Interrogative puis active',
                                'learning_process' => 'S informer, s entrainer',
                                'subject' => 'Composantes du formulaire Oneduc : informations, stagiaires et modules.',
                                'activity' => 'Tri du fil rouge : classer des cartes-info dans les trois zones du formulaire.',
                                'evaluation' => 'Placer les cartes-info dans les bonnes zones du formulaire.',
                                'scorm_directory' => 'modules/parcours_formateur/module_2_organiser_ses_parcours/chapitre_1_preparer_lenvironnement_de_formation/lecon_1_1_les_composants_indispensables',
                                'hide_scorm_next_button' => true,
                                'activity_page' => self::classificationActivity(
                                    'vous préparez la création d’un groupe de formation. Voici 10 données collectées en amont. Chacune appartient à une zone du formulaire Onéduc : à vous de la placer au bon endroit.',
                                    [
                                        ['id' => 'date_ouverture', 'label' => '1er avril 2026', 'category' => 'information'],
                                        ['id' => 'module_excel_avance', 'label' => 'Excel avancé', 'category' => 'module'],
                                        ['id' => 'prenom', 'label' => 'Thomas', 'category' => 'stagiaire'],
                                        ['id' => 'visible', 'label' => 'Statut : visible', 'category' => 'information'],
                                        ['id' => 'module_word_debutant', 'label' => 'Word débutant', 'category' => 'module'],
                                        ['id' => 'adresse_mail', 'label' => 't.lefebvre@esa2.fr', 'category' => 'stagiaire'],
                                        ['id' => 'date_debut', 'label' => '8 avril 2026', 'category' => 'information'],
                                        ['id' => 'module_powerpoint', 'label' => 'PowerPoint', 'category' => 'module'],
                                        ['id' => 'nom', 'label' => 'Lefebvre', 'category' => 'stagiaire'],
                                        ['id' => 'desactive', 'label' => 'Statut : désactivé', 'category' => 'information'],
                                    ]
                                ),
                            ]
                        ),
                        'distinguer-contenu-ressource-et-structure' => self::lesson(
                            '2.1.2',
                            'Preparer les informations utiles',
                            '6 min',
                            'Rassembler les informations utiles.',
                            [
                                'pedagogical_intention' => 'Anticiper les informations utiles pour eviter les blocages au moment de la creation.',
                                'method' => 'Active',
                                'learning_process' => 'S entrainer, produire',
                                'subject' => 'Intitule, description, liste des stagiaires, modules, dates, modalites d acces et co-formateurs.',
                                'activity' => 'Fiche de mise en route completee a partir du cas Hygiene alimentaire 2026.',
                                'evaluation' => 'Completer les 4 sections de la fiche de mise en route.',
                                'scorm_directory' => 'modules/parcours_formateur/module_2_organiser_ses_parcours/chapitre_1_preparer_lenvironnement_de_formation/lecon_1_2_distinguer_contenu_ressource_et_structure',
                                'hide_scorm_next_button' => true,
                                'activity_page' => self::informationPreparationActivity(),
                            ]
                        ),
                    ],
                ],
                'structurer-la-progression' => [
                    'label' => 'Chapitre',
                    'pedagogical_label' => 'Objectif pedagogique',
                    'code' => '2.2',
                    'title' => 'Creer et organiser',
                    'description' => 'Ce chapitre transforme la preparation en action : creer le groupe Hygiene alimentaire 2026, renseigner les stagiaires, organiser les modules attendus, puis construire un parcours lisible avec des outils numeriques places au bon moment.',
                    'duration_label' => '16 min',
                    'objective' => 'Creer un groupe puis construire un parcours coherent, dans le bon ordre, avec les modules et activites attendus.',
                    'tip' => 'La progression ne se limite pas a une liste de modules : elle raconte l ordre dans lequel l apprenant va avancer, avec des respirations et des activites utiles.',
                    'progress_percentage' => 0,
                    'lessons' => [
                        'creation-groupe-de-formation' => self::lesson(
                            '2.2.1',
                            'Creer un groupe de formation',
                            '8 min',
                            'Creer un groupe a partir de la fiche.',
                            [
                                'pedagogical_intention' => 'Faire le pont entre la preparation et le geste de creation concret.',
                                'method' => 'Demonstrative puis active guidee',
                                'learning_process' => 'Produire',
                                'subject' => 'Lecons de creation : informations, stagiaires, modules et modalites.',
                                'activity' => 'De la fiche au groupe : creation guidee dans un environnement de demonstration ou sandbox.',
                                'evaluation' => 'Creer un groupe correctement renseigne a partir de la fiche de mise en route.',
                                'layout' => 'scorm_form',
                                'embedded_form' => 'group_creation',
                                'completion_activity_key' => 'creation-groupe-finalisee',
                                'hide_scorm_next_button' => true,
                                'completion_validation' => [
                                    'type' => 'guided_group_creation',
                                    'required_module_ids' => [101, 102, 103],
                                ],
                                'scorm_parts' => [
                                    'introduction' => [
                                        'directory' => 'modules/parcours_formateur/module_2_organiser_ses_parcours/chapitre_2_structurer_la_progression/lecon_2_1_a_entete_creation_groupe_de_formation',
                                        'height' => 'full',
                                    ],
                                    'informations' => [
                                        'directory' => 'modules/parcours_formateur/module_2_organiser_ses_parcours/chapitre_2_structurer_la_progression/lecon_2_1_b_creation_groupe_de_formation_informations',
                                        'height' => 'compact',
                                        'form' => 'group_creation',
                                    ],
                                    'stagiaires' => [
                                        'directory' => 'modules/parcours_formateur/module_2_organiser_ses_parcours/chapitre_2_structurer_la_progression/lecon_2_1_c_creation_groupe_de_formation_stagiaires',
                                        'height' => 'compact',
                                        'form' => 'group_creation',
                                    ],
                                    'modules' => [
                                        'directory' => 'modules/parcours_formateur/module_2_organiser_ses_parcours/chapitre_2_structurer_la_progression/lecon_2_1_d_creation_groupe_de_formation_modules',
                                        'height' => 'compact',
                                        'form' => 'group_creation',
                                    ],
                                    'finalisation' => [
                                        'directory' => 'modules/parcours_formateur/module_2_organiser_ses_parcours/chapitre_2_structurer_la_progression/lecon_2_1_e_creation_groupe_de_formation_finalisation',
                                        'height' => 'compact',
                                        'form' => 'group_creation',
                                        'marks_completion' => true,
                                        'completion_requires_payload' => true,
                                    ],
                                ],
                            ]
                        ),
                        'creation-parcours' => self::lesson(
                            '2.2.2',
                            'Creer un parcours',
                            '8 min',
                            'Creer un parcours clair avant de l associer au groupe.',
                            [
                                'pedagogical_intention' => 'Faire comprendre l interet de construire un parcours reutilisable avant de l affecter a un groupe.',
                                'method' => 'Demonstrative puis active guidee',
                                'learning_process' => 'Analyser, produire',
                                'subject' => 'Creation d un parcours, choix des modules, ordre de progression et verification de la lisibilite pour l apprenant.',
                                'activity' => 'Simulateur de creation de parcours : construire une progression a partir de modules disponibles.',
                                'evaluation' => 'Creer un parcours coherent, lisible et pret a etre associe a un groupe.',
                                'layout' => 'scorm_form',
                                'embedded_form' => 'path_creation',
                                'completion_activity_key' => 'creation-parcours-finalisee',
                                'hide_scorm_next_button' => true,
                                'scorm_parts' => [
                                    'ouvrir-formulaire' => [
                                        'directory' => 'modules/parcours_formateur/module_2_organiser_ses_parcours/chapitre_2_structurer_la_progression/lecon_2_2_a_creation_parcours',
                                        'height' => 'full',
                                    ],
                                    'remplir-formulaire' => [
                                        'directory' => 'modules/parcours_formateur/module_2_organiser_ses_parcours/chapitre_2_structurer_la_progression/lecon_2_2_b_remplir_formulaire_creation_parcours',
                                        'height' => 'compact',
                                        'form' => 'path_creation',
                                    ],
                                    'felicitations' => [
                                        'directory' => 'modules/parcours_formateur/module_2_organiser_ses_parcours/chapitre_2_structurer_la_progression/lecon_2_2_c_creation_parcours_finalisation',
                                        'height' => 'compact',
                                        'form' => 'path_creation_finalisation',
                                        'marks_completion' => true,
                                    ],
                                ],
                                'scorm_slot_label' => 'SCORM et simulateur',
                                'activity_slot_label' => 'Simulateur a creer',
                            ]
                        ),
                    ],
                ],
                'mettre-en-place-un-parcours-coherent' => [
                    'label' => 'Chapitre',
                    'pedagogical_label' => 'Objectif pedagogique',
                    'code' => '2.3',
                    'title' => 'Ajuster et securiser',
                    'description' => 'Ce chapitre travaille les ajustements de terrain : ajouter un stagiaire, verifier son rattachement, debloquer Marc en lui renvoyant ses acces, puis modifier le contenu d un groupe lorsque le rythme de formation evolue.',
                    'duration_label' => '21 a 24 min',
                    'objective' => 'Ajuster un groupe existant, securiser les acces des stagiaires et modifier le contenu lorsque la situation change.',
                    'tip' => 'Quand un parcours est lance, le travail du formateur continue : il controle les acces, corrige les profils et ajuste les contenus sans casser la logique du groupe.',
                    'progress_percentage' => 0,
                    'lessons' => [
                        'associer-le-bon-parcours-au-bon-contexte' => self::lesson(
                            '2.3.1',
                            'Ajuster le groupe',
                            '8 a 10 min',
                            'Modifier un groupe et ajouter les stagiaires.',
                            [
                                'pedagogical_intention' => 'Faire comprendre que l ajustement du groupe et l affectation des stagiaires vont ensemble.',
                                'method' => 'Active avec appui demonstratif court',
                                'learning_process' => 'Analyser, associer, verifier',
                                'subject' => 'Onglet Stagiaires global, edition du groupe, saisie manuelle, import CSV et modes de connexion.',
                                'activity' => 'Une demande d ajustement : traiter des changements de date, de stagiaire et d ajout.',
                                'evaluation' => 'Effectuer les ajustements demandes et ajouter les stagiaires par les deux modes.',
                                'layout' => 'scorm_form',
                                'completion_activity_key' => 'ajustement-groupe-finalise',
                                'scorm_parts' => [
                                    'ajustement-groupe' => [
                                        'directory' => 'modules/parcours_formateur/module_2_organiser_ses_parcours/chapitre_3_ajuster_un_groupe/lecon_3_1_a_ajustement_groupe',
                                        'height' => 'full',
                                    ],
                                    'ajustement-groupe-suite' => [
                                        'directory' => 'modules/parcours_formateur/module_2_organiser_ses_parcours/chapitre_3_ajuster_un_groupe/lecon_3_1_b_ajustement_groupe',
                                        'height' => 'compact',
                                        'form' => 'stagiaires_index',
                                    ],
                                    'ajouter-stagiaire' => [
                                        'directory' => 'modules/parcours_formateur/module_2_organiser_ses_parcours/chapitre_3_ajuster_un_groupe/lecon_3_1_c_ajustement_groupe',
                                        'height' => 'compact',
                                        'form' => 'stagiaire_create',
                                    ],
                                    'ajustement-groupe-finalisation' => [
                                        'directory' => 'modules/parcours_formateur/module_2_organiser_ses_parcours/chapitre_3_ajuster_un_groupe/lecon_3_1_d_ajustement_groupe_finalisation',
                                        'height' => 'compact',
                                        'form' => 'stagiaires_index',
                                        'marks_completion' => true,
                                    ],
                                ],
                            ]
                        ),
                        'traiter-les-cas-particuliers' => self::lesson(
                            '2.3.2',
                            'Traiter les cas particuliers',
                            '10 min',
                            'Traiter un blocage ou une demande supplementaire.',
                            [
                                'pedagogical_intention' => 'Installer deux reflexes : resoudre un blocage et inscrire a un groupe complementaire quand le besoin le demande.',
                                'method' => 'Active par resolution de probleme et differenciation',
                                'learning_process' => 'Observer, analyser, corriger, differencier',
                                'subject' => 'Procedure a 5 niveaux face a un blocage d acces et inscription a un groupe complementaire.',
                                'activity' => 'Cas Marc et cas Sofia : resoudre un blocage puis inscrire a un second groupe.',
                                'evaluation' => 'Appliquer la procedure a 5 niveaux et inscrire Sofia a un second groupe.',
                                'layout' => 'scorm_form',
                                'scorm_parts' => [
                                    'cas-particulier' => [
                                        'directory' => 'modules/parcours_formateur/module_2_organiser_ses_parcours/chapitre_3_ajuster_un_groupe/lecon_3_2_a_cas_particulier',
                                        'height' => 'full',
                                    ],
                                    'debloquer-marc' => [
                                        'height' => 'form_only',
                                        'form' => 'marc_students_table',
                                    ],
                                    'modifier-profil-marc' => [
                                        'height' => 'form_only',
                                        'form' => 'marc_profile_message',
                                    ],
                                    'validation' => [
                                        'directory' => 'modules/parcours_formateur/module_2_organiser_ses_parcours/chapitre_3_ajuster_un_groupe/lecon_3_2_c_cas_particulier_finalisation',
                                        'height' => 'compact',
                                        'form' => 'marc_unlock_results',
                                        'marks_completion' => true,
                                    ],
                                    'modifier-contenu' => [
                                        'directory' => 'modules/parcours_formateur/module_2_organiser_ses_parcours/chapitre_3_ajuster_un_groupe/lecon_3_2_d_cas_particulier_modifier_contenu',
                                        'height' => 'compact',
                                        'form' => 'content_modification_intro',
                                    ],
                                    'modifier-contenu-groupe' => [
                                        'height' => 'form_only',
                                        'form' => 'content_modification_group_edit',
                                    ],
                                    'modifier-contenu-finalisation' => [
                                        'directory' => 'modules/parcours_formateur/module_2_organiser_ses_parcours/chapitre_3_ajuster_un_groupe/lecon_3_2_e_cas_particulier_modifier_contenu_finalisation',
                                        'height' => 'compact',
                                        'form' => 'content_modification_results',
                                        'marks_completion' => true,
                                    ],
                                ],
                            ]
                        ),
                        'bilan-module-2' => self::lesson(
                            '2.3.3',
                            'Bilan du module 2 et ouverture vers le module 3',
                            '3 a 4 min',
                            'Faire le point avant la suite.',
                            [
                                'type' => 'bilan',
                                'pedagogical_intention' => 'Marquer la progression et consolider les reperes d action avant de passer au pilotage.',
                                'method' => 'Transmissive courte puis interrogative',
                                'learning_process' => 'Synthetiser, se projeter',
                                'subject' => 'Synthese des capacites acquises : preparation, creation, organisation, ajustement et acces.',
                                'activity' => 'Vignette de synthese et check-list de mise en route.',
                                'evaluation' => 'Telecharger la check-list et acceder a la page de bilan native Oneduc.',
                                'layout' => 'scorm_form',
                                'completion_activity_key' => 'bilan-module-2-finalise',
                                'scorm_parts' => [
                                    'bilan' => [
                                        'directory' => 'modules/parcours_formateur/module_2_organiser_ses_parcours/chapitre_3_ajuster_un_groupe/lecon_3_3_bilan',
                                        'height' => 'full',
                                    ],
                                    'resultat-final' => [
                                        'height' => 'form_only',
                                        'form' => 'module_2_final_results',
                                        'marks_completion' => true,
                                    ],
                                ],
                            ]
                        ),
                    ],
                ],
            ],
        ];
    }

    /**
     * @param  array<array{id: string, label: string, category: string}>  $items
     * @return array<string, mixed>
     */
    private static function classificationActivity(string $scenario, array $items): array
    {
        return [
            'key' => 'classer-les-elements',
            'code' => 'Activité',
            'title' => 'Classer les éléments de préparation',
            'button_label' => 'Réaliser l’activité',
            'scenario' => $scenario,
            'instruction' => 'classez chaque élément dans la bonne catégorie : Informations, Stagiaires ou Modules.',
            'success_message' => 'Bravo, vous avez correctement classé les éléments utiles à la préparation du parcours.',
            'dropzones' => self::preparationDropzones(),
            'items' => $items,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function informationPreparationActivity(): array
    {
        return [
            'key' => 'preparer-informations-utiles',
            'type' => 'essential_sorting',
            'code' => 'Activité',
            'title' => 'Obligatoire ou ajoutable plus tard ?',
            'button_label' => 'Réaliser l’activité',
            'scenario' => 'vous vous apprêtez à créer le groupe Hygiène alimentaire 2026 — Promo 1 dans Onéduc.',
            'instruction_sections' => [
                [
                    'label' => 'Votre tâche',
                    'body' => 'Avant de valider le formulaire, distinguer les éléments qui bloquent la création du groupe. De ceux qui pourront être à ajouter ensuite.',
                ],
                [
                    'label' => 'Critère de tri',
                    'body' => 'Posez vous la seule question, si cet élément manque, est ce que vous pouvez créer le groupe ?',
                ],
                [
                    'label' => 'Exemple',
                    'body_html' => '<strong>L’intitulé du groupe est obligatoire</strong> Son nom ? Vous ne pouvez pas créer de groupe. <strong>Une description peut attendre</strong> Vous pourrez la compléter après la création.',
                ],
            ],
            'instruction' => 'glissez chaque élément dans la bonne colonne : Obligatoire pour créer le groupe ou Peut être ajouté plus tard.',
            'success_message' => 'Parfait. Vous avez raisonné avec le bon critère : est-ce que l’absence de cet élément bloque la création du groupe dans Onéduc ? Un intitulé, au moins un stagiaire et au moins un module sont nécessaires. Le reste peut être ajouté ou ajusté ensuite.',
            'feedback_messages' => [
                'A' => 'Parfait. Vous avez repéré ce qui bloque réellement la création du groupe dans Onéduc : un intitulé, au moins un stagiaire et au moins un module.',
                'B' => 'C’est juste. Quelques éléments peuvent faire hésiter, car ils sont utiles pédagogiquement. Retenez le critère : est-ce que leur absence empêche Onéduc de créer le groupe ?',
                'C' => 'Petit détour par la case consigne, et ça repart. Pour créer le groupe, il faut les trois indispensables : un intitulé, au moins un stagiaire et au moins un module. Les dates, le statut, la description, le coformateur et les ressources peuvent patienter gentiment.',
            ],
            'dropzones' => [
                ['id' => 'essentiel', 'label' => 'Obligatoire pour créer le groupe', 'description' => 'Sans cet élément, Onéduc ne peut pas créer le groupe.'],
                ['id' => 'facultatif', 'label' => 'Peut être ajouté plus tard', 'description' => 'Onéduc peut créer le groupe sans cet élément. Vous pourrez l’ajouter ou l’ajuster ensuite.'],
            ],
            'items' => [
                ['id' => 'description', 'type_label' => 'Description', 'label' => 'Formation pour les nouveaux salariés', 'category' => 'facultatif', 'feedback' => 'La description aide à comprendre le groupe, mais elle n’est pas bloquante. Elle peut être complétée ensuite.'],
                ['id' => 'module', 'type_label' => 'Module', 'label' => 'Sécurité alimentaire 2026', 'category' => 'essentiel', 'feedback' => 'Au moins un module est nécessaire pour associer un contenu de formation au groupe.'],
                ['id' => 'dates', 'type_label' => 'Dates', 'label' => 'du 8 janvier au 31 mars 2026', 'category' => 'facultatif', 'feedback' => 'Les dates sont utiles pour cadrer la formation, mais Onéduc peut créer le groupe sans dates précises. Elles peuvent être ajustées ensuite.'],
                ['id' => 'stagiaire', 'type_label' => 'Stagiaire', 'label' => 'Marie Dupont', 'category' => 'essentiel', 'feedback' => 'Au moins un stagiaire est nécessaire pour préparer l’accès au groupe.'],
                ['id' => 'ressource', 'type_label' => 'Ressource', 'label' => 'fiche PDF Bonnes pratiques HACCP', 'category' => 'facultatif', 'feedback' => 'La ressource enrichit la formation, mais elle n’est pas indispensable à la création du groupe.'],
                ['id' => 'intitule', 'type_label' => 'Intitulé', 'label' => 'Hygiène alimentaire 2026 — Promo 1', 'category' => 'essentiel', 'feedback' => 'L’intitulé est obligatoire : sans nom, Onéduc ne peut pas créer le groupe.'],
                ['id' => 'statut', 'type_label' => 'Statut', 'label' => 'en attente', 'category' => 'facultatif', 'feedback' => 'Le statut permet de préparer ou d’activer le groupe, mais il peut être modifié après la création.'],
                ['id' => 'coformateur', 'type_label' => 'Coformateur', 'label' => 'Karim Benali', 'category' => 'facultatif', 'feedback' => 'Le coformateur facilite l’encadrement, mais il n’est pas nécessaire pour créer le groupe. Il peut être ajouté plus tard.'],
            ],
        ];
    }

    /**
     * @return array<array{id: string, label: string, description: string}>
     */
    private static function preparationDropzones(): array
    {
        return [
            ['id' => 'information', 'label' => 'Informations', 'description' => 'Paramètres, dates et repères généraux utiles à la préparation.'],
            ['id' => 'stagiaire', 'label' => 'Stagiaires', 'description' => 'Données d’identification et de contact des participants.'],
            ['id' => 'module', 'label' => 'Modules', 'description' => 'Contenus pédagogiques que les stagiaires vont suivre.'],
        ];
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private static function lesson(string $code, string $title, string $duration, string $objective, array $overrides = []): array
    {
        return array_merge([
            'code' => $code,
            'title' => $title,
            'duration_label' => $duration,
            'type' => 'objectif',
            'objective' => $objective,
            'pedagogical_intention' => '',
            'method' => '',
            'learning_process' => '',
            'subject' => '',
            'activity' => '',
            'evaluation' => '',
            'resources' => 'Contenu de lecon a integrer.',
            'scorm_slot_label' => 'Contenu de lecon',
            'activity_slot_label' => 'Activite a creer',
        ], $overrides);
    }

    /**
     * @return array<string, mixed>
     */
    private static function placeholderModule(string $label, string $title, string $fullTitle, string $duration): array
    {
        return [
            'label' => $label,
            'title' => $title,
            'full_title' => $fullTitle,
            'description' => 'Ce module sera structure dans une prochaine phase.',
            'specific_objective' => $fullTitle . '.',
            'duration_label' => $duration,
            'status_label' => 'En cours de construction',
            'is_under_construction' => true,
            'construction_label' => 'En cours de construction',
            'construction_note' => 'Ce module est en cours de construction. Certains contenus, exercices ou liens peuvent encore etre ajustes.',
            'trainer_name' => 'Equipe Oneduc',
            'level_label' => 'Tous niveaux',
            'cta_label' => 'Voir le parcours',
            'progress_percentage' => 0,
            'presentation_video_embed_url' => null,
            'presentation_video_note' => 'Video de presentation a ajouter ulterieurement.',
            'presentation' => [
                'Ce module est conserve dans la structure globale du parcours, mais il n est pas prioritaire pour le moment.',
                'Son detail pedagogique sera ajoute quand les modules 1 et 2 seront stabilises.',
            ],
            'goals' => [
                'Objectifs a definir dans une prochaine phase.',
            ],
            'prerequisites' => [
                'Le detail de ce module sera ajoute ulterieurement.',
            ],
            'chapters' => [],
        ];
    }
}
