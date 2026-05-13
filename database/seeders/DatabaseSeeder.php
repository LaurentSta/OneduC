<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Group;
use App\Models\LectureObjective;
use App\Models\Module;
use App\Models\ModuleLecture;
use App\Models\ModuleSection;
use App\Models\QuizOption;
use App\Models\QuizQuestion;
use App\Models\SubCategory;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ADMIN
        User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'prenom' => 'Super',
                'name' => 'Admin',
                'username' => 'admin',
                'password' => Hash::make('111'),
                'role' => 'admin',
                'status' => 1,
            ]
        );

        // FORMATEUR
        $formateur = User::updateOrCreate(
            ['email' => 'instructor@gmail.com'],
            [
                'prenom' => 'Jean',
                'name' => 'Formateur',
                'username' => 'formateur',
                'password' => Hash::make('111'),
                'role' => 'formateur',
                'status' => 1,
                'adhesion_status' => 'active',
                'adhesion_valid_until' => now()->addYear()->toDateString(),
                'adhesion_verified_at' => now(),
            ]
        );

        // OBSERVATEUR
        $observateur = User::updateOrCreate(
            ['email' => 'observateur@gmail.com'],
            [
                'prenom' => 'Claire',
                'name' => 'Observatrice',
                'username' => 'observateur',
                'password' => Hash::make('111'),
                'role' => 'observateur',
                'status' => 1,
                'phone' => '0600000000',
                'address' => '12 rue des Observateurs, Paris',
                'societe' => 'Mission d observation pedagogique',
            ]
        );

        // STAGIAIRE
        User::updateOrCreate(
            ['email' => 'user@gmail.com'],
            [
                'prenom' => 'Laura',
                'name' => 'Stagiaire',
                'username' => 'stagiaire',
                'password' => Hash::make('111'),
                'role' => 'stagiaire',
                'status' => 1,
            ]
        );

        $catalogue = [
            [
                'category_name' => 'Developpement Web',
                'category_description' => 'Formations autour du frontend, du backend et des outils web modernes.',
                'subcategories' => [
                    [
                        'subcategory_name' => 'Laravel',
                        'subcategory_description' => 'Creation d applications web avec Laravel, Eloquent et Blade.',
                        'modules' => [
                            [
                                'module_title' => 'Laravel de zero a production',
                                'module_name' => 'Laravel de zero a production',
                                'description' => 'Un module pratique pour poser les bases d un projet Laravel complet, de la configuration aux bonnes pratiques de developpement.',
                                'objectifs' => [
                                    'Comprendre la structure d un projet Laravel',
                                    'Creer des routes, controllers et vues',
                                    'Manipuler la base de donnees avec Eloquent',
                                ],
                                'label' => 'Debutant',
                                'duree' => '8 heures',
                                'resources' => 'Support PDF, exercices pratiques',
                                'prerequi' => 'Connaitre les bases de PHP et HTML.',
                                'certificat' => true,
                                'bestseller' => true,
                                'status' => 1,
                            ],
                            [
                                'module_title' => 'API REST avec Laravel',
                                'module_name' => 'API REST avec Laravel',
                                'description' => 'Concevoir une API REST securisee avec authentification, validation et gestion des reponses JSON.',
                                'objectifs' => [
                                    'Structurer une API REST',
                                    'Utiliser les Form Requests',
                                    'Mettre en place une authentification API',
                                ],
                                'label' => 'Intermediaire',
                                'duree' => '6 heures',
                                'resources' => 'Postman collection, exemples JSON',
                                'prerequi' => 'Avoir deja manipule Laravel sur un projet simple.',
                                'certificat' => true,
                                'status' => 1,
                            ],
                        ],
                    ],
                    [
                        'subcategory_name' => 'JavaScript',
                        'subcategory_description' => 'Programmation JavaScript moderne pour le web interactif.',
                        'modules' => [
                            [
                                'module_title' => 'JavaScript moderne',
                                'module_name' => 'JavaScript moderne',
                                'description' => 'Apprendre les fondamentaux ES6+, la manipulation du DOM et les bases de l asynchrone.',
                                'objectifs' => [
                                    'Maitriser les variables, fonctions et tableaux',
                                    'Manipuler le DOM efficacement',
                                    'Comprendre promises et async await',
                                ],
                                'label' => 'Debutant',
                                'duree' => '5 heures',
                                'resources' => 'Fiches memo, mini projets',
                                'prerequi' => 'Bases de HTML et CSS.',
                                'vedette' => true,
                                'status' => 1,
                            ],
                        ],
                    ],
                ],
            ],
            [
                'category_name' => 'Bureautique',
                'category_description' => 'Parcours pour gagner en efficacite avec les outils bureautiques du quotidien.',
                'subcategories' => [
                    [
                        'subcategory_name' => 'Excel',
                        'subcategory_description' => 'Creation de tableaux, formules et tableaux de bord sur Excel.',
                        'modules' => [
                            [
                                'module_title' => 'Excel pour debutants',
                                'module_name' => 'Excel pour debutants',
                                'description' => 'Decouvrir Excel, mettre en forme un tableau et utiliser les fonctions essentielles.',
                                'objectifs' => [
                                    'Saisir et organiser des donnees',
                                    'Utiliser les formules de base',
                                    'Creer un tableau clair et lisible',
                                ],
                                'label' => 'Debutant',
                                'duree' => '4 heures',
                                'resources' => 'Classeur d exercices',
                                'prerequi' => 'Aucun prerequis.',
                                'status' => 1,
                            ],
                        ],
                    ],
                    [
                        'subcategory_name' => 'PowerPoint',
                        'subcategory_description' => 'Conception de presentations claires et professionnelles.',
                        'modules' => [
                            [
                                'module_title' => 'Presentations impactantes avec PowerPoint',
                                'module_name' => 'Presentations impactantes avec PowerPoint',
                                'description' => 'Construire des diapositives efficaces et presenter une idee de facon visuelle.',
                                'objectifs' => [
                                    'Structurer une presentation',
                                    'Utiliser les modeles et animations avec moderation',
                                    'Ameliorer la lisibilite des slides',
                                ],
                                'label' => 'Debutant',
                                'duree' => '3 heures',
                                'resources' => 'Modele de presentation',
                                'prerequi' => 'Aucun prerequis.',
                                'surevalue' => true,
                                'status' => 1,
                            ],
                        ],
                    ],
                ],
            ],
            [
                'category_name' => 'Hygiene alimentaire 2026',
                'category_description' => 'Modules de demonstration pour construire un parcours autour de l hygiene, de l alimentation et du nettoyage.',
                'subcategories' => [
                    [
                        'subcategory_name' => 'Alimentation et hygiene',
                        'subcategory_description' => 'Reperes pratiques pour former aux bonnes pratiques d hygiene alimentaire.',
                        'modules' => [
                            [
                                'module_title' => 'Securite alimentaire 2026',
                                'module_name' => 'Securite alimentaire 2026',
                                'description' => 'Identifier les risques alimentaires et appliquer les principes de securite attendus en restauration.',
                                'objectifs' => [
                                    'Reconnaitre les principaux dangers alimentaires',
                                    'Appliquer les gestes de prevention',
                                    'Savoir reagir face a une situation a risque',
                                ],
                                'label' => 'Essentiel',
                                'duree' => '2 heures',
                                'resources' => 'Fiches pratiques, quiz, cas de situation',
                                'prerequi' => 'Aucun prerequis.',
                                'certificat' => true,
                                'status' => 1,
                            ],
                            [
                                'module_title' => 'Hygiene en cuisine professionnelle',
                                'module_name' => 'Hygiene en cuisine professionnelle',
                                'description' => 'Mettre en place les gestes d hygiene personnelle, materielle et organisationnelle en cuisine.',
                                'objectifs' => [
                                    'Respecter les regles d hygiene personnelle',
                                    'Organiser un poste de travail propre',
                                    'Limiter les contaminations pendant la production',
                                ],
                                'label' => 'Essentiel',
                                'duree' => '2 heures',
                                'resources' => 'Checklist, video de gestes, mise en pratique',
                                'prerequi' => 'Aucun prerequis.',
                                'status' => 1,
                            ],
                            [
                                'module_title' => 'Nettoyage et desinfection des espaces',
                                'module_name' => 'Nettoyage et desinfection des espaces',
                                'description' => 'Choisir les bons produits, appliquer un protocole et controler l efficacite du nettoyage.',
                                'objectifs' => [
                                    'Distinguer nettoyage et desinfection',
                                    'Respecter les dosages et temps de contact',
                                    'Completer un plan de nettoyage',
                                ],
                                'label' => 'Pratique',
                                'duree' => '1 heure 30',
                                'resources' => 'Plan de nettoyage, fiche protocole',
                                'prerequi' => 'Aucun prerequis.',
                                'status' => 1,
                            ],
                            [
                                'module_title' => 'Conservation et chaine du froid',
                                'module_name' => 'Conservation et chaine du froid',
                                'description' => 'Maitriser les temperatures, les durees de conservation et les controles de la chaine du froid.',
                                'objectifs' => [
                                    'Identifier les temperatures critiques',
                                    'Controler les equipements frigorifiques',
                                    'Reagir en cas de rupture de chaine du froid',
                                ],
                                'label' => 'Essentiel',
                                'duree' => '1 heure 30',
                                'resources' => 'Tableau de temperatures, cas pratiques',
                                'prerequi' => 'Aucun prerequis.',
                                'status' => 1,
                            ],
                            [
                                'module_title' => 'Allergenes et information client',
                                'module_name' => 'Allergenes et information client',
                                'description' => 'Identifier les allergenes majeurs et transmettre une information fiable au client.',
                                'objectifs' => [
                                    'Reconnaitre les allergenes reglementaires',
                                    'Securiser l information client',
                                    'Limiter les risques de contamination croisee',
                                ],
                                'label' => 'Reglementaire',
                                'duree' => '1 heure',
                                'resources' => 'Fiche allergenes, quiz de reperage',
                                'prerequi' => 'Aucun prerequis.',
                                'status' => 1,
                            ],
                            [
                                'module_title' => 'Reception et stockage des denrees',
                                'module_name' => 'Reception et stockage des denrees',
                                'description' => 'Controler les livraisons, ranger les denrees et assurer une rotation adaptee des stocks.',
                                'objectifs' => [
                                    'Verifier une livraison',
                                    'Organiser le stockage par famille de produits',
                                    'Appliquer la methode premier entre premier sorti',
                                ],
                                'label' => 'Pratique',
                                'duree' => '1 heure 30',
                                'resources' => 'Grille de controle, exercice de classement',
                                'prerequi' => 'Aucun prerequis.',
                                'status' => 1,
                            ],
                            [
                                'module_title' => 'Prevention des contaminations croisees',
                                'module_name' => 'Prevention des contaminations croisees',
                                'description' => 'Organiser les flux, separer les produits sensibles et reduire les contaminations croisees.',
                                'objectifs' => [
                                    'Reperer les situations a risque',
                                    'Separer les circuits propres et sales',
                                    'Choisir le materiel adapte a chaque usage',
                                ],
                                'label' => 'Essentiel',
                                'duree' => '1 heure 30',
                                'resources' => 'Scenarios, schema de circulation',
                                'prerequi' => 'Aucun prerequis.',
                                'status' => 1,
                            ],
                            [
                                'module_title' => 'Equilibre alimentaire et menus',
                                'module_name' => 'Equilibre alimentaire et menus',
                                'description' => 'Composer des menus simples en tenant compte des familles d aliments et des besoins des publics.',
                                'objectifs' => [
                                    'Identifier les familles d aliments',
                                    'Composer un menu equilibre',
                                    'Adapter une proposition aux besoins d un public',
                                ],
                                'label' => 'Approfondissement',
                                'duree' => '2 heures',
                                'resources' => 'Fiches menus, atelier de composition',
                                'prerequi' => 'Aucun prerequis.',
                                'status' => 1,
                            ],
                            [
                                'module_title' => 'Gestion des dechets alimentaires',
                                'module_name' => 'Gestion des dechets alimentaires',
                                'description' => 'Mettre en place le tri, limiter le gaspillage et appliquer les gestes de gestion des dechets.',
                                'objectifs' => [
                                    'Identifier les types de dechets',
                                    'Mettre en place un tri adapte',
                                    'Reduire le gaspillage alimentaire',
                                ],
                                'label' => 'Pratique',
                                'duree' => '1 heure',
                                'resources' => 'Affichage de tri, activite de diagnostic',
                                'prerequi' => 'Aucun prerequis.',
                                'status' => 1,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        foreach ($catalogue as $categoryData) {
            $subcategories = $categoryData['subcategories'];
            unset($categoryData['subcategories']);

            $category = Category::updateOrCreate(
                ['category_slug' => Str::slug($categoryData['category_name'])],
                array_merge($categoryData, [
                    'category_slug' => Str::slug($categoryData['category_name']),
                ])
            );

            foreach ($subcategories as $subcategoryData) {
                $modules = $subcategoryData['modules'];
                unset($subcategoryData['modules']);

                $subcategory = SubCategory::updateOrCreate(
                    ['subcategory_slug' => Str::slug($subcategoryData['subcategory_name'])],
                    array_merge($subcategoryData, [
                        'category_id' => $category->id,
                        'subcategory_slug' => Str::slug($subcategoryData['subcategory_name']),
                    ])
                );

                foreach ($modules as $moduleData) {
                    $module = Module::updateOrCreate(
                        ['module_name_slug' => Str::slug($moduleData['module_name'])],
                        array_merge([
                            'category_id' => $category->id,
                            'subcategory_id' => $subcategory->id,
                            'formateur_id' => $formateur->id,
                            'module_name_slug' => Str::slug($moduleData['module_name']),
                            'module_image' => null,
                            'header_image' => null,
                            'module_video' => null,
                            'evaluation_id' => null,
                            'certificat' => false,
                            'bestseller' => false,
                            'vedette' => false,
                            'surevalue' => false,
                            'status' => 1,
                        ], $moduleData)
                    );

                    $this->seedModuleContent($module, $formateur);
                }
            }
        }

        $this->seedInstructorGroup($formateur, $observateur);
    }

    private function seedInstructorGroup(User $formateur, User $observateur): void
    {
        $stagiaires = [
            [
                'email' => 'user@gmail.com',
                'prenom' => 'Laura',
                'name' => 'Stagiaire',
                'username' => 'stagiaire',
            ],
            [
                'email' => 'amina.stagiaire@gmail.com',
                'prenom' => 'Amina',
                'name' => 'Diallo',
                'username' => 'amina_stagiaire',
            ],
            [
                'email' => 'youssef.stagiaire@gmail.com',
                'prenom' => 'Youssef',
                'name' => 'Benali',
                'username' => 'youssef_stagiaire',
            ],
        ];

        $groupStudents = [];

        foreach ($stagiaires as $stagiaireData) {
            $groupStudents[] = User::updateOrCreate(
                ['email' => $stagiaireData['email']],
                array_merge($stagiaireData, [
                    'password' => Hash::make('111'),
                    'role' => 'stagiaire',
                    'status' => 1,
                    'formateur_id' => $formateur->id,
                ])
            );
        }

        $group = Group::updateOrCreate(
            ['name' => 'Groupe Demo Formateur'],
            [
                'description' => 'Groupe de demonstration relie au formateur seed et utilise pour tester l affectation des stagiaires et des modules.',
                'temporary_password' => 'demo111',
                'instructor_id' => $formateur->id,
            ]
        );

        $now = now();

        DB::table('group_user')->updateOrInsert(
            [
                'group_id' => $group->id,
                'user_id' => $formateur->id,
            ],
            [
                'role_in_group' => 'formateur',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        DB::table('group_user')->updateOrInsert(
            [
                'group_id' => $group->id,
                'user_id' => $observateur->id,
            ],
            [
                'role_in_group' => 'observateur',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        foreach ($groupStudents as $stagiaire) {
            DB::table('group_user')->updateOrInsert(
                [
                    'group_id' => $group->id,
                    'user_id' => $stagiaire->id,
                ],
                [
                    'role_in_group' => 'stagiaire',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        $hygieneModules = Module::query()
            ->where('formateur_id', $formateur->id)
            ->whereIn('module_name', [
                'Securite alimentaire 2026',
                'Hygiene en cuisine professionnelle',
                'Nettoyage et desinfection des espaces',
                'Conservation et chaine du froid',
                'Allergenes et information client',
                'Reception et stockage des denrees',
                'Prevention des contaminations croisees',
                'Equilibre alimentaire et menus',
                'Gestion des dechets alimentaires',
            ])
            ->orderBy('id')
            ->get();

        if ($hygieneModules->isNotEmpty()) {
            foreach ($hygieneModules as $position => $module) {
                DB::table('group_module')->updateOrInsert(
                    [
                        'group_id' => $group->id,
                        'module_id' => $module->id,
                    ],
                    [
                        'position' => $position + 1,
                    ]
                );
            }

            return;
        }

        $module = Module::query()
            ->where('formateur_id', $formateur->id)
            ->orderBy('id')
            ->first();

        if ($module) {
            DB::table('group_module')->updateOrInsert(
                [
                    'group_id' => $group->id,
                    'module_id' => $module->id,
                ],
                [
                    'position' => 1,
                ]
            );
        }
    }

    private function seedModuleContent(Module $module, User $formateur): void
    {
        foreach ($this->buildModuleSections($module) as $sectionData) {
            $lectures = $sectionData['lectures'];
            unset($sectionData['lectures']);

            $section = ModuleSection::updateOrCreate(
                [
                    'module_id' => $module->id,
                    'section_title' => $sectionData['section_title'],
                ],
                array_merge($sectionData, [
                    'module_id' => $module->id,
                ])
            );

            foreach ($lectures as $lectureIndex => $lectureData) {
                $objectives = $lectureData['objectives'];
                $questions = $lectureData['questions'];

                unset($lectureData['objectives'], $lectureData['questions']);

                $lecture = ModuleLecture::updateOrCreate(
                    [
                        'module_id' => $module->id,
                        'section_id' => $section->id,
                        'lecture_title' => $lectureData['lecture_title'],
                    ],
                    array_merge([
                        'module_id' => $module->id,
                        'section_id' => $section->id,
                        'position' => $lectureIndex + 1,
                        'url' => null,
                        'scorm_path' => null,
                        'scorm_package_id' => null,
                        'scorm_package_version_id' => null,
                        'use_active_scorm_version' => false,
                        'content_type' => 'scorm',
                        'slides_status' => 'none',
                        'slides_path' => null,
                        'slides_source_path' => null,
                        'slides_error' => null,
                        'slides_converted_at' => null,
                        'slide_count' => 0,
                        'question_count' => count($questions),
                        'quiz_enabled' => true,
                        'quiz_questions_per_attempt' => count($questions),
                    ], $lectureData, [
                        'question_count' => count($questions),
                        'quiz_enabled' => true,
                        'quiz_questions_per_attempt' => count($questions),
                    ])
                );

                foreach ($objectives as $objectiveIndex => $objectiveData) {
                    LectureObjective::updateOrCreate(
                        [
                            'lecture_id' => $lecture->id,
                            'position' => $objectiveIndex + 1,
                        ],
                        array_merge($objectiveData, [
                            'lecture_id' => $lecture->id,
                            'position' => $objectiveIndex + 1,
                        ])
                    );
                }

                foreach ($questions as $questionIndex => $questionData) {
                    $options = $questionData['options'];
                    unset($questionData['options']);

                    $question = QuizQuestion::updateOrCreate(
                        [
                            'lecture_id' => $lecture->id,
                            'question_text' => $questionData['question_text'],
                        ],
                        array_merge($questionData, [
                            'lecture_id' => $lecture->id,
                            'created_by' => $formateur->id,
                            'is_active' => true,
                        ])
                    );

                    foreach ($options as $optionIndex => $optionData) {
                        QuizOption::updateOrCreate(
                            [
                                'question_id' => $question->id,
                                'position' => $optionIndex + 1,
                            ],
                            array_merge($optionData, [
                                'question_id' => $question->id,
                                'position' => $optionIndex + 1,
                            ])
                        );
                    }
                }
            }
        }
    }

    private function buildModuleSections(Module $module): array
    {
        $moduleName = $module->module_name ?: $module->module_title ?: 'ce module';
        $goals = is_array($module->objectifs) ? array_values($module->objectifs) : [];

        $chapterOneGoal = $goals[0] ?? 'Comprendre les fondamentaux';
        $chapterTwoGoal = $goals[1] ?? 'Mettre en pratique les notions';
        $chapterThreeGoal = $goals[2] ?? 'Valider les acquis';

        return [
            [
                'section_title' => 'Chapitre 1 - Demarrer avec les bases',
                'objectif' => $chapterOneGoal,
                'methode' => 'Presentation guidee, demonstration pas a pas et questions flash.',
                'contexte' => "Ce chapitre pose les reperes essentiels pour bien demarrer {$moduleName}.",
                'section_html' => $this->buildSectionHtml(
                    $moduleName,
                    'Demarrer avec les bases',
                    [
                        "Comprendre le cadre global de {$moduleName}",
                        'Identifier le vocabulaire indispensable',
                        'Prendre en main les premiers outils ou concepts',
                    ]
                ),
                'lectures' => [
                    [
                        'lecture_title' => 'Lecon 1 - Vue d ensemble du module',
                        'duration' => 18,
                        'url' => 'https://example.test/modules/' . Str::slug($moduleName) . '/vue-ensemble',
                        'objectives' => [
                            [
                                'title' => "Identifier le perimetre de {$moduleName}",
                                'description' => "La lecon presente les notions centrales, le vocabulaire et la logique generale de {$moduleName}.",
                            ],
                            [
                                'title' => 'Reperer les resultats attendus',
                                'description' => 'Le stagiaire comprend ce qu il devra savoir faire a la fin du parcours.',
                            ],
                        ],
                        'questions' => [
                            [
                                'type' => 'single',
                                'question_text' => "Quel est l objectif principal de cette premiere lecon de {$moduleName} ?",
                                'options' => [
                                    ['option_text' => 'Poser les bases et le vocabulaire du module', 'is_correct' => true],
                                    ['option_text' => 'Passer directement au projet final', 'is_correct' => false],
                                    ['option_text' => 'Supprimer la phase de decouverte', 'is_correct' => false],
                                ],
                            ],
                            [
                                'type' => 'boolean',
                                'question_text' => 'Cette lecon sert a comprendre le contexte avant la mise en pratique.',
                                'options' => [
                                    ['option_text' => 'Vrai', 'is_correct' => true],
                                    ['option_text' => 'Faux', 'is_correct' => false],
                                ],
                            ],
                        ],
                    ],
                    [
                        'lecture_title' => 'Lecon 2 - Premiere prise en main',
                        'duration' => 22,
                        'url' => 'https://example.test/modules/' . Str::slug($moduleName) . '/prise-en-main',
                        'objectives' => [
                            [
                                'title' => 'Appliquer les notions vues en introduction',
                                'description' => "Le stagiaire realise une premiere manipulation concrete autour de {$moduleName}.",
                            ],
                            [
                                'title' => 'Eviter les erreurs frequentes',
                                'description' => 'La lecon met en avant les points de vigilance des debutants.',
                            ],
                        ],
                        'questions' => [
                            [
                                'type' => 'single',
                                'question_text' => 'Quelle approche est recommandee pendant une premiere prise en main ?',
                                'options' => [
                                    ['option_text' => 'Suivre une methode pas a pas', 'is_correct' => true],
                                    ['option_text' => 'Tout modifier sans repere', 'is_correct' => false],
                                    ['option_text' => 'Ignorer les exemples proposes', 'is_correct' => false],
                                ],
                            ],
                            [
                                'type' => 'boolean',
                                'question_text' => 'Les erreurs frequentes doivent etre identifiees des le debut.',
                                'options' => [
                                    ['option_text' => 'Vrai', 'is_correct' => true],
                                    ['option_text' => 'Faux', 'is_correct' => false],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'section_title' => 'Chapitre 2 - Mise en pratique',
                'objectif' => $chapterTwoGoal,
                'methode' => 'Atelier guide, exercices corriges et application sur un cas concret.',
                'contexte' => "Ce chapitre transforme les notions de {$moduleName} en actions concretes.",
                'section_html' => $this->buildSectionHtml(
                    $moduleName,
                    'Mise en pratique',
                    [
                        'Experimenter une methode de travail claire',
                        'Produire un resultat concret a partir d un cas simple',
                        'Verifier la bonne comprehension a chaque etape',
                    ]
                ),
                'lectures' => [
                    [
                        'lecture_title' => 'Lecon 3 - Atelier guide',
                        'duration' => 25,
                        'url' => 'https://example.test/modules/' . Str::slug($moduleName) . '/atelier-guide',
                        'objectives' => [
                            [
                                'title' => 'Suivre un scenario de travail complet',
                                'description' => "La lecon accompagne pas a pas la realisation d une tache type liee a {$moduleName}.",
                            ],
                            [
                                'title' => 'Structurer sa methode',
                                'description' => 'Le stagiaire apprend a reproduire une demarche claire et reutilisable.',
                            ],
                        ],
                        'questions' => [
                            [
                                'type' => 'single',
                                'question_text' => 'Quel benefice apporte un atelier guide dans ce module ?',
                                'options' => [
                                    ['option_text' => 'Mettre en pratique avec un fil conducteur', 'is_correct' => true],
                                    ['option_text' => 'Supprimer toute verification', 'is_correct' => false],
                                    ['option_text' => 'Remplacer tout apprentissage progressif', 'is_correct' => false],
                                ],
                            ],
                            [
                                'type' => 'boolean',
                                'question_text' => 'Une methode reproductible aide a gagner en autonomie.',
                                'options' => [
                                    ['option_text' => 'Vrai', 'is_correct' => true],
                                    ['option_text' => 'Faux', 'is_correct' => false],
                                ],
                            ],
                        ],
                    ],
                    [
                        'lecture_title' => 'Lecon 4 - Cas pratique',
                        'duration' => 28,
                        'url' => 'https://example.test/modules/' . Str::slug($moduleName) . '/cas-pratique',
                        'objectives' => [
                            [
                                'title' => 'Resoudre une situation proche du terrain',
                                'description' => "Le stagiaire mobilise les acquis du module {$moduleName} sur un cas concret.",
                            ],
                            [
                                'title' => 'Justifier les choix realises',
                                'description' => 'La lecon encourage a expliquer la methode et les decisions prises.',
                            ],
                        ],
                        'questions' => [
                            [
                                'type' => 'single',
                                'question_text' => 'Dans un cas pratique, que doit faire en priorite le stagiaire ?',
                                'options' => [
                                    ['option_text' => 'Analyser le besoin avant d agir', 'is_correct' => true],
                                    ['option_text' => 'Choisir une solution au hasard', 'is_correct' => false],
                                    ['option_text' => 'Eviter toute justification', 'is_correct' => false],
                                ],
                            ],
                            [
                                'type' => 'boolean',
                                'question_text' => 'Expliquer ses choix permet de consolider la comprehension.',
                                'options' => [
                                    ['option_text' => 'Vrai', 'is_correct' => true],
                                    ['option_text' => 'Faux', 'is_correct' => false],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'section_title' => 'Chapitre 3 - Validation et consolidation',
                'objectif' => $chapterThreeGoal,
                'methode' => 'Revision, auto-evaluation et mini projet de synthese.',
                'contexte' => "Ce chapitre permet de verifier que les acquis de {$moduleName} sont bien maitrises.",
                'section_html' => $this->buildSectionHtml(
                    $moduleName,
                    'Validation et consolidation',
                    [
                        'Mesurer la progression du stagiaire',
                        'Verifier la maitrise des notions cles',
                        'Clore le module par une synthese operationnelle',
                    ]
                ),
                'lectures' => [
                    [
                        'lecture_title' => 'Lecon 5 - Evaluation des acquis',
                        'duration' => 20,
                        'url' => 'https://example.test/modules/' . Str::slug($moduleName) . '/evaluation',
                        'objectives' => [
                            [
                                'title' => 'Verifier la comprehension des notions cles',
                                'description' => 'La lecon permet de faire le point sur les acquis essentiels du module.',
                            ],
                            [
                                'title' => 'Identifier les points a renforcer',
                                'description' => 'Le stagiaire repere rapidement les themes a revoir avant la suite.',
                            ],
                        ],
                        'questions' => [
                            [
                                'type' => 'single',
                                'question_text' => 'A quoi sert principalement une evaluation des acquis ?',
                                'options' => [
                                    ['option_text' => 'Mesurer la comprehension et cibler les revisions', 'is_correct' => true],
                                    ['option_text' => 'Supprimer les retours pedagogiques', 'is_correct' => false],
                                    ['option_text' => 'Ignorer les objectifs du module', 'is_correct' => false],
                                ],
                            ],
                            [
                                'type' => 'boolean',
                                'question_text' => 'Identifier ses points faibles aide a mieux progresser.',
                                'options' => [
                                    ['option_text' => 'Vrai', 'is_correct' => true],
                                    ['option_text' => 'Faux', 'is_correct' => false],
                                ],
                            ],
                        ],
                    ],
                    [
                        'lecture_title' => 'Lecon 6 - Mini projet final',
                        'duration' => 30,
                        'url' => 'https://example.test/modules/' . Str::slug($moduleName) . '/mini-projet',
                        'objectives' => [
                            [
                                'title' => 'Mobiliser l ensemble des acquis',
                                'description' => "Le stagiaire realise une synthese pratique de tout ce qu il a vu dans {$moduleName}.",
                            ],
                            [
                                'title' => 'Se projeter dans une utilisation reelle',
                                'description' => 'La lecon relie le parcours a une situation professionnelle ou operationnelle.',
                            ],
                        ],
                        'questions' => [
                            [
                                'type' => 'single',
                                'question_text' => 'Quel est le role d un mini projet final ?',
                                'options' => [
                                    ['option_text' => 'Assembler les acquis dans une realisation complete', 'is_correct' => true],
                                    ['option_text' => 'Repartir de zero sans lien avec le module', 'is_correct' => false],
                                    ['option_text' => 'Eviter toute mise en situation', 'is_correct' => false],
                                ],
                            ],
                            [
                                'type' => 'boolean',
                                'question_text' => 'Le mini projet final aide a passer de la theorie a une production concrete.',
                                'options' => [
                                    ['option_text' => 'Vrai', 'is_correct' => true],
                                    ['option_text' => 'Faux', 'is_correct' => false],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    private function buildSectionHtml(string $moduleName, string $chapterTitle, array $items): string
    {
        $listItems = array_map(
            static fn (string $item) => '<li>' . $item . '</li>',
            $items
        );

        return '<h3>' . $chapterTitle . '</h3>'
            . '<p>Contenu de demonstration pour le module ' . $moduleName . '.</p>'
            . '<ul>' . implode('', $listItems) . '</ul>';
    }
}
