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
     * Modules présentés dans le suivi administrateur du parcours formateur.
     *
     * @return array<int, array{number: int, key: string, label: string, title: string}>
     */
    public static function trainerPathModules(): array
    {
        $trainerPathModules = [];

        foreach (self::rawModules() as $moduleKey => $module) {
            $moduleNumber = count($trainerPathModules) + 1;
            $trainerPathModules[] = [
                'number' => $moduleNumber,
                'key' => $moduleKey,
                'label' => (string) ($module['label'] ?? 'Module '.$moduleNumber),
                'title' => (string) ($module['title'] ?? ''),
            ];
        }

        return $trainerPathModules;
    }

    /**
     * Étapes obligatoires utilisées pour déterminer si un module est terminé.
     *
     * @return array<string, string>
     */
    public static function moduleCompletionRequirements(string $moduleKey): array
    {
        $requirements = [];
        $module = self::rawModules()[$moduleKey] ?? null;

        foreach (($module['chapters'] ?? []) as $chapterKey => $chapter) {
            foreach (($chapter['lessons'] ?? []) as $lessonKey => $lesson) {
                $activity = $lesson['activity_page'] ?? null;

                if (is_array($activity) && ! empty($activity['key'])) {
                    $requirements[self::activityStatusKey((string) $chapterKey, (string) $lessonKey, (string) $activity['key'])] = (string) ($activity['type'] ?? 'sorting');
                }

                $completionActivityKey = $lesson['completion_activity_key'] ?? null;

                if (is_string($completionActivityKey) && $completionActivityKey !== '') {
                    $requirements[self::activityStatusKey((string) $chapterKey, (string) $lessonKey, $completionActivityKey)] = (string) ($lesson['completion_activity_type'] ?? 'guided_group_creation');
                }
            }
        }

        return $requirements;
    }

    public static function activityStatusKey(string $chapterKey, string $lessonKey, string $activityKey): string
    {
        return implode('.', [$chapterKey, $lessonKey, $activityKey]);
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
            'title' => 'Mettre en place un environnement de formation',
            'full_title' => 'Mettre en place un environnement de formation dans Onéduc',
            'description' => 'Préparer un groupe, organiser les modules et sécuriser les accès.',
            'specific_objective' => 'Créer un environnement de formation prêt à utiliser.',
            'duration_label' => '48 à 51 min',
            'presentation_video_embed_url' => null,
            'presentation_video_title' => 'Vidéo de présentation du module 2',
            'presentation_video_note' => 'Emplacement prévu pour une courte vidéo de présentation du module.',
            'introduction_scorm_directory' => 'modules/parcours_formateur/module_2_organiser_ses_parcours/introduction',
            'illustration_path' => 'images/svg/ImageModule2.svg',
            'status_label' => 'Disponible',
            'trainer_name' => 'Équipe Onéduc',
            'level_label' => 'Tous niveaux',
            'cta_label' => 'Entrer dans le module',
            'progress_percentage' => 0,
            'presentation' => [
                'Préparez votre premier environnement de formation.',
                'Créez le groupe, ajoutez les stagiaires et organisez les modules.',
                'Vérifiez que les accès sont prêts avant l’ouverture.',
            ],
            'goals' => [
                'Préparer les informations utiles.',
                'Créer un groupe de formation.',
                'Organiser les modules et les accès.',
                'Ajuster le groupe si besoin.',
            ],
            'prerequisites' => [
                'Avoir parcouru le module 1 ou disposer déjà des repères de base dans l’espace formateur.',
                'Connaître les rubriques principales liées aux parcours, aux groupes et au suivi.',
                'Aucun prérequis technique supplémentaire n’est nécessaire.',
            ],
            'chapters' => [
                'preparer-les-contenus' => [
                    'label' => 'Chapitre',
                    'pedagogical_label' => 'Objectif pédagogique',
                    'code' => '2.1',
                    'title' => 'Préparer avant de créer',
                    'description' => 'Ce chapitre installe le réflexe de préparation : reconnaître les zones du formulaire, distinguer ce qui relève des informations, des stagiaires et des modules, puis identifier ce qui bloque vraiment la création d’un groupe.',
                    'duration_label' => '11 min',
                    'objective' => 'Vérifier que les informations indispensables sont prêtes avant d’ouvrir un groupe dans Onéduc.',
                    'tip' => 'Avant de créer, le formateur gagne du temps en séparant trois choses : les paramètres du groupe, les personnes à inscrire et les contenus à proposer.',
                    'progress_percentage' => 0,
                    'lessons' => [
                        'retrouver-les-espaces-de-preparation' => self::lesson(
                            '2.1.1',
                            'Identifier les éléments essentiels',
                            '5 min',
                            'Identifier les éléments indispensables.',
                            [
                                'pedagogical_intention' => 'Faire repérer les trois composantes principales de l’interface Onéduc : informations, stagiaires et modules.',
                                'method' => 'Interrogative puis active',
                                'learning_process' => 'S’informer, s’entraîner',
                                'subject' => 'Composantes du formulaire Onéduc : informations, stagiaires et modules.',
                                'activity' => 'Tri du fil rouge : classer des cartes-info dans les trois zones du formulaire.',
                                'evaluation' => 'Placer les cartes-info dans les bonnes zones du formulaire.',
                                'scorm_directory' => 'modules/parcours_formateur/module_2_organiser_ses_parcours/chapitre_1_preparer_lenvironnement_de_formation/lecon_1_1_les_composants_indispensables',
                                'hide_scorm_next_button' => true,
                                'activity_page' => self::classificationActivity(
                                    '',
                                    [
                                        ['id' => 'titre_groupe', 'label' => 'Titre du groupe', 'category' => 'information', 'feedback' => "Le titre identifie le groupe : il se renseigne à l'étape Informations."],
                                        ['id' => 'date_debut', 'label' => 'Date de début', 'category' => 'information', 'feedback' => 'Une date situe le groupe dans le temps : c’est une Information, étape 1.'],
                                        ['id' => 'date_fin', 'label' => 'Date de fin', 'category' => 'information', 'feedback' => 'Une date situe le groupe dans le temps : c’est une Information, étape 1.'],
                                        ['id' => 'pierre_dupont', 'label' => 'Pierre Dupont', 'category' => 'stagiaire', 'feedback' => 'C’est le nom d’un participant : il s’ajoute à l’étape Stagiaires.'],
                                        ['id' => 'aurelie_martin', 'label' => 'Aurélie Martin', 'category' => 'stagiaire', 'feedback' => 'C’est le nom d’un participant : il s’ajoute à l’étape Stagiaires.'],
                                        ['id' => 'email_stagiaire', 'label' => 'Email du stagiaire', 'category' => 'stagiaire', 'feedback' => 'Une adresse de contact concerne un participant : étape Stagiaires.'],
                                        ['id' => 'module_word_debutant', 'label' => 'Word débutant', 'category' => 'module', 'feedback' => 'C’est un contenu de formation : il s’ajoute à l’étape Modules.'],
                                        ['id' => 'module_excel_avance', 'label' => 'Excel avancé', 'category' => 'module', 'feedback' => 'C’est un contenu de formation : il s’ajoute à l’étape Modules.'],
                                        ['id' => 'module_powerpoint', 'label' => 'PowerPoint', 'category' => 'module', 'feedback' => 'C’est un contenu de formation : il s’ajoute à l’étape Modules.'],
                                    ]
                                ),
                            ]
                        ),
                        'distinguer-contenu-ressource-et-structure' => self::lesson(
                            '2.1.2',
                            'Préparer les informations utiles',
                            '6 min',
                            'Rassembler les informations utiles.',
                            [
                                'pedagogical_intention' => 'Anticiper les informations utiles pour éviter les blocages au moment de la création.',
                                'method' => 'Active',
                                'learning_process' => 'S’entraîner, produire',
                                'subject' => 'Intitulé, description, liste des stagiaires, modules, dates, modalités d’accès et co-formateurs.',
                                'activity' => 'Fiche de mise en route complétée à partir du cas Hygiène alimentaire 2026.',
                                'evaluation' => 'Compléter les 4 sections de la fiche de mise en route.',
                                'scorm_directory' => 'modules/parcours_formateur/module_2_organiser_ses_parcours/chapitre_1_preparer_lenvironnement_de_formation/lecon_1_2_distinguer_contenu_ressource_et_structure',
                                'hide_scorm_next_button' => true,
                                'activity_page' => self::informationPreparationActivity(),
                            ]
                        ),
                    ],
                ],
                'structurer-la-progression' => [
                    'label' => 'Chapitre',
                    'pedagogical_label' => 'Objectif pédagogique',
                    'code' => '2.2',
                    'title' => 'Créer et organiser',
                    'description' => 'Ce chapitre transforme la préparation en action : créer le groupe Hygiène alimentaire 2026, renseigner les stagiaires, organiser les modules attendus, puis construire un parcours lisible avec des outils numériques placés au bon moment.',
                    'duration_label' => '16 min',
                    'objective' => 'Créer un groupe puis construire un parcours cohérent, dans le bon ordre, avec les modules et activités attendus.',
                    'tip' => 'La progression ne se limite pas à une liste de modules : elle raconte l’ordre dans lequel l’apprenant va avancer, avec des respirations et des activités utiles.',
                    'progress_percentage' => 0,
                    'lessons' => [
                        'creation-groupe-de-formation' => self::lesson(
                            '2.2.1',
                            'Créer un groupe de formation',
                            '8 min',
                            'Créer un groupe à partir de la fiche.',
                            [
                                'pedagogical_intention' => 'Faire le pont entre la préparation et le geste de création concret.',
                                'method' => 'Démonstrative puis active guidée',
                                'learning_process' => 'Produire',
                                'subject' => 'Leçons de création : informations, stagiaires, modules et modalités.',
                                'activity' => 'De la fiche au groupe : création guidée dans un environnement de démonstration.',
                                'evaluation' => 'Créer un groupe correctement renseigné à partir de la fiche de mise en route.',
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
                            'Créer un parcours',
                            '8 min',
                            'Créer un parcours clair avant de l’associer au groupe.',
                            [
                                'pedagogical_intention' => 'Faire comprendre l’intérêt de construire un parcours réutilisable avant de l’affecter à un groupe.',
                                'method' => 'Démonstrative puis active guidée',
                                'learning_process' => 'Analyser, produire',
                                'subject' => 'Création d’un parcours, choix des modules, ordre de progression et vérification de la lisibilité pour l’apprenant.',
                                'activity' => 'Simulateur de création de parcours : construire une progression à partir de modules disponibles.',
                                'evaluation' => 'Créer un parcours cohérent, lisible et prêt à être associé à un groupe.',
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
                                'activity_slot_label' => 'Simulateur à créer',
                            ]
                        ),
                    ],
                ],
                'mettre-en-place-un-parcours-coherent' => [
                    'label' => 'Chapitre',
                    'pedagogical_label' => 'Objectif pédagogique',
                    'code' => '2.3',
                    'title' => 'Ajuster et sécuriser',
                    'description' => 'Ce chapitre travaille les ajustements de terrain : ajouter un stagiaire, vérifier son rattachement, débloquer Marc en lui renvoyant ses accès, puis modifier le contenu d’un groupe lorsque le rythme de formation évolue.',
                    'duration_label' => '21 à 24 min',
                    'objective' => 'Ajuster un groupe existant, sécuriser les accès des stagiaires et modifier le contenu lorsque la situation change.',
                    'tip' => 'Quand un parcours est lancé, le travail du formateur continue : il contrôle les accès, corrige les profils et ajuste les contenus sans casser la logique du groupe.',
                    'progress_percentage' => 0,
                    'lessons' => [
                        'associer-le-bon-parcours-au-bon-contexte' => self::lesson(
                            '2.3.1',
                            'Ajuster le groupe',
                            '8 à 10 min',
                            'Modifier un groupe et ajouter les stagiaires.',
                            [
                                'pedagogical_intention' => 'Faire comprendre que l ajustement du groupe et l affectation des stagiaires vont ensemble.',
                                'method' => 'Active avec appui démonstratif court',
                                'learning_process' => 'Analyser, associer, vérifier',
                                'subject' => 'Onglet Stagiaires global, édition du groupe, saisie manuelle, import CSV et modes de connexion.',
                                'activity' => 'Une demande d’ajustement : traiter des changements de date, de stagiaire et d’ajout.',
                                'evaluation' => 'Effectuer les ajustements demandés et ajouter les stagiaires par les deux modes.',
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
                            'Traiter un blocage ou une demande supplémentaire.',
                            [
                                'pedagogical_intention' => 'Installer deux réflexes : résoudre un blocage et inscrire à un groupe complémentaire quand le besoin le demande.',
                                'method' => 'Active par résolution de problème et différenciation',
                                'learning_process' => 'Observer, analyser, corriger, différencier',
                                'subject' => 'Procédure à 5 niveaux face à un blocage d’accès et inscription à un groupe complémentaire.',
                                'activity' => 'Cas Marc et cas Sofia : résoudre un blocage puis inscrire à un second groupe.',
                                'evaluation' => 'Appliquer la procédure à 5 niveaux et inscrire Sofia à un second groupe.',
                                'layout' => 'scorm_form',
                                'completion_activity_key' => 'cas-particuliers-finalises',
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
                            '3 à 4 min',
                            'Faire le point avant la suite.',
                            [
                                'type' => 'bilan',
                                'pedagogical_intention' => 'Marquer la progression et consolider les repères d’action avant de passer au pilotage.',
                                'method' => 'Transmissive courte puis interrogative',
                                'learning_process' => 'Synthétiser, se projeter',
                                'subject' => 'Synthèse des capacités acquises : préparation, création, organisation, ajustement et accès.',
                                'activity' => 'Vignette de synthèse et check-list de mise en route.',
                                'evaluation' => 'Télécharger la check-list et accéder à la page de bilan native Onéduc.',
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
                                    'questionnaire' => [
                                        'height' => 'form_only',
                                        'form' => 'module_2_usability_questionnaire',
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
     * Retourne le questionnaire d'évaluation disponible pour un module.
     * Les modules 1, 3 et 4 pourront être ajoutés ici lorsqu'ils disposeront de leur formulaire.
     *
     * @return array<string, mixed>|null
     */
    public static function moduleUsabilityQuestionnaire(string $moduleKey): ?array
    {
        return match ($moduleKey) {
            'organiser-ses-parcours' => self::moduleTwoUsabilityQuestionnaire(),
            default => null,
        };
    }

    /**
     * Référentiel du questionnaire d'évaluation affiché à la fin du module 2.
     *
     * @return array<string, mixed>
     */
    private static function moduleTwoUsabilityQuestionnaire(): array
    {
        return [
            'module' => [
                'number' => 2,
                'key' => 'organiser-ses-parcours',
                'code' => 'module-2',
                'title' => 'Mettre en place un environnement de formation',
            ],
            'questionnaire' => [
                'key' => 'utilisabilite-percue',
                'version' => 1,
            ],
            'dimensions' => [
                [
                    'id' => 'contenu_percu',
                    'title' => 'Contenu perçu',
                    'items' => [
                        ['number' => 1, 'label' => 'Le libellé des textes était clair.'],
                        ['number' => 2, 'label' => 'Le contenu (textes, images, voix off, vidéos) était facile à comprendre.'],
                        ['number' => 3, 'label' => 'Le contenu m’a paru utile pour mon métier de formateur.'],
                        ['number' => 4, 'label' => 'Le contenu correspondait à ce que j’attendais.'],
                    ],
                ],
                [
                    'id' => 'effort_cognitif_percu',
                    'title' => 'Effort cognitif perçu',
                    'items' => [
                        ['number' => 5, 'label' => 'J’ai appris à utiliser le module rapidement.'],
                        ['number' => 6, 'label' => 'Suivre le module s’est fait sans effort.'],
                        // Cet item inversé doit être recodé lors de l'analyse.
                        ['number' => 7, 'label' => 'Suivre le module m’a fatigué.', 'reversed' => true],
                    ],
                ],
                [
                    'id' => 'guidage_visuel_percu',
                    'title' => 'Guidage visuel perçu',
                    'items' => [
                        ['number' => 8, 'label' => 'Les couleurs m’ont aidé à distinguer les différents éléments à l’écran.'],
                        ['number' => 9, 'label' => 'Les éléments mis en évidence m’ont aidé à repérer ce qui était important.'],
                        ['number' => 10, 'label' => 'Le style des illustrations a soutenu ma compréhension.'],
                    ],
                ],
                [
                    'id' => 'reperage_dans_le_parcours',
                    'title' => 'Repérage dans le parcours',
                    'items' => [
                        ['number' => 11, 'label' => 'Je savais toujours où j’en étais dans le parcours.'],
                        ['number' => 12, 'label' => 'Je comprenais comment passer d’un écran au suivant.'],
                        ['number' => 13, 'label' => 'L’enchaînement des leçons m’a paru logique.'],
                    ],
                ],
                [
                    'id' => 'activites_et_simulateurs',
                    'title' => 'Activités et simulateurs',
                    'items' => [
                        ['number' => 14, 'label' => 'Les consignes des activités étaient claires.'],
                        ['number' => 15, 'label' => 'Je comprenais ce qu’on attendait de moi dans chaque activité.'],
                        ['number' => 16, 'label' => 'Les simulateurs reflétaient bien l’usage réel d’Onéduc.'],
                        ['number' => 17, 'label' => 'Le retour après chaque activité m’a aidé à savoir si j’avais réussi.'],
                    ],
                ],
            ],
            'scale' => [
                ['value' => '1', 'short' => '1', 'label' => 'Pas du tout d’accord'],
                ['value' => '2', 'short' => '2', 'label' => 'Plutôt pas d’accord'],
                ['value' => '3', 'short' => '3', 'label' => 'Ni d’accord ni pas d’accord'],
                ['value' => '4', 'short' => '4', 'label' => 'Plutôt d’accord'],
                ['value' => '5', 'short' => '5', 'label' => 'Tout à fait d’accord'],
                ['value' => 'NA', 'short' => 'NA', 'label' => 'Non applicable'],
            ],
            'open_questions' => [
                ['item_number' => 18, 'label' => 'Qu’est-ce qui vous a le plus aidé dans ce module ?'],
                ['item_number' => 19, 'label' => 'Qu’est-ce qui mériterait d’être clarifié ou amélioré ?'],
            ],
        ];
    }

    /**
     * Modules fictifs proposés dans le simulateur de création de parcours.
     *
     * @return array<int, array{id: int, title: string, lesson_count: int, question_count: int, duration_label: string}>
     */
    public static function pathCreationSimulationModules(): array
    {
        return [
            [
                'id' => 101,
                'title' => 'Securite alimentaire 2026',
                'lesson_count' => 5,
                'question_count' => 8,
                'duration_label' => '45 min',
            ],
            [
                'id' => 102,
                'title' => 'Hygiene en cuisine professionnelle',
                'lesson_count' => 4,
                'question_count' => 6,
                'duration_label' => '40 min',
            ],
            [
                'id' => 103,
                'title' => 'Nettoyage et desinfection des espaces',
                'lesson_count' => 4,
                'question_count' => 7,
                'duration_label' => '50 min',
            ],
            [
                'id' => 104,
                'title' => 'Conservation et chaine du froid',
                'lesson_count' => 4,
                'question_count' => 6,
                'duration_label' => '40 min',
            ],
            [
                'id' => 105,
                'title' => 'Allergenes et information client',
                'lesson_count' => 3,
                'question_count' => 5,
                'duration_label' => '35 min',
            ],
            [
                'id' => 106,
                'title' => 'Reception et stockage des denrees',
                'lesson_count' => 4,
                'question_count' => 6,
                'duration_label' => '45 min',
            ],
            [
                'id' => 107,
                'title' => 'Prevention des contaminations croisees',
                'lesson_count' => 5,
                'question_count' => 8,
                'duration_label' => '55 min',
            ],
            [
                'id' => 108,
                'title' => 'Equilibre alimentaire et menus',
                'lesson_count' => 3,
                'question_count' => 5,
                'duration_label' => '30 min',
            ],
            [
                'id' => 109,
                'title' => 'Gestion des dechets alimentaires',
                'lesson_count' => 3,
                'question_count' => 4,
                'duration_label' => '30 min',
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
            'instruction' => 'Faites glisser chaque étiquette dans la bonne étape de préparation. Étape 1, Informations : les paramètres du groupe (titre, dates). Étape 2, Stagiaires : les personnes et leurs coordonnées. Étape 3, Modules : les contenus de formation que suivront les stagiaires.',
            'success_message' => 'Vous avez correctement réparti les éléments dans les trois étapes de préparation.',
            'result_title' => "C'est noté !",
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
            'success_message' => "Vous avez distingué ce qui est obligatoire pour créer le groupe de ce qui peut s'ajouter ensuite.",
            'result_title' => "C'est noté !",
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
                ['id' => 'description', 'type_label' => 'Description', 'label' => 'Formation pour les nouveaux salariés', 'category' => 'facultatif', 'feedback' => "Ajoutable plus tard : elle précise le cadre, mais n'est pas exigée pour créer le groupe."],
                ['id' => 'module', 'type_label' => 'Module', 'label' => 'Sécurité alimentaire 2026', 'category' => 'essentiel', 'feedback' => 'Obligatoire : un groupe doit contenir au moins un module.'],
                ['id' => 'dates', 'type_label' => 'Dates', 'label' => 'du 15 janvier au 31 mars 2026', 'category' => 'facultatif', 'feedback' => 'Ajoutable plus tard : la période peut être renseignée après la création.'],
                ['id' => 'stagiaire', 'type_label' => 'Stagiaire', 'label' => 'Marie Dupont', 'category' => 'essentiel', 'feedback' => 'Obligatoire : un groupe se crée avec au moins un stagiaire (nom, prénom, mail).'],
                ['id' => 'ressource', 'type_label' => 'Ressource', 'label' => 'fiche PDF Bonnes pratiques HACCP', 'category' => 'facultatif', 'feedback' => 'Ajoutable plus tard : les ressources s’ajoutent après la création du groupe.'],
                ['id' => 'intitule', 'type_label' => 'Intitulé', 'label' => 'Hygiène alimentaire 2026 — Promo 1', 'category' => 'essentiel', 'feedback' => 'Obligatoire : sans nom, Onéduc ne peut pas créer le groupe.'],
                ['id' => 'statut', 'type_label' => 'Statut', 'label' => 'en attente', 'category' => 'facultatif', 'feedback' => 'Ajoutable plus tard : le statut peut être modifié une fois le groupe créé.'],
                ['id' => 'coformateur', 'type_label' => 'Coformateur', 'label' => 'Karim Benali', 'category' => 'facultatif', 'feedback' => 'Ajoutable plus tard : un coformateur peut être associé ensuite.'],
            ],
        ];
    }

    /**
     * @return array<array{id: string, label: string, step_label: string, description: string}>
     */
    private static function preparationDropzones(): array
    {
        return [
            ['id' => 'information', 'label' => 'Informations', 'step_label' => 'Étape 1', 'description' => 'Paramètres, dates et repères généraux utiles à la préparation.'],
            ['id' => 'stagiaire', 'label' => 'Stagiaires', 'step_label' => 'Étape 2', 'description' => 'Données d’identification et de contact des participants.'],
            ['id' => 'module', 'label' => 'Modules', 'step_label' => 'Étape 3', 'description' => 'Contenus pédagogiques que les stagiaires vont suivre.'],
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
            'resources' => 'Contenu de leçon à intégrer.',
            'scorm_slot_label' => 'Contenu de leçon',
            'activity_slot_label' => 'Activité à créer',
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
