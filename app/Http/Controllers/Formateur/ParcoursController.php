<?php

namespace App\Http\Controllers\Formateur;

use App\Http\Controllers\Controller;

class ParcoursController extends Controller
{
    public function index()
    {
        $catalogue = $this->catalogue();

        return view('formateur.parcours.index', [
            'pageTitle' => 'Parcours formateur',
            'parcoursModules' => $catalogue,
            'activeModuleKey' => null,
            'activeChapterKey' => null,
            'activeLessonKey' => null,
            'breadcrumbs' => [
                ['label' => 'Parcours formateur', 'url' => route('formateur.parcours.index')],
            ],
        ]);
    }

    public function showModule(string $module)
    {
        $catalogue = $this->catalogue();
        abort_unless(isset($catalogue[$module]), 404);

        $currentModule = $catalogue[$module];

        return view('formateur.parcours.module', [
            'pageTitle' => $currentModule['title'],
            'parcoursModules' => $catalogue,
            'activeModuleKey' => $module,
            'activeChapterKey' => null,
            'activeLessonKey' => null,
            'currentModule' => $currentModule,
            'breadcrumbs' => [
                ['label' => 'Parcours formateur', 'url' => route('formateur.parcours.index')],
                ['label' => $currentModule['title'], 'url' => $currentModule['url']],
            ],
        ]);
    }

    public function showChapter(string $module, string $chapter)
    {
        $catalogue = $this->catalogue();
        abort_unless(isset($catalogue[$module]), 404);
        abort_unless(isset($catalogue[$module]['chapters'][$chapter]), 404);

        $currentModule = $catalogue[$module];
        $currentChapter = $currentModule['chapters'][$chapter];

        return view('formateur.parcours.chapter', [
            'pageTitle' => $currentChapter['title'],
            'parcoursModules' => $catalogue,
            'activeModuleKey' => $module,
            'activeChapterKey' => $chapter,
            'activeLessonKey' => null,
            'currentModule' => $currentModule,
            'currentChapter' => $currentChapter,
            'breadcrumbs' => [
                ['label' => 'Parcours formateur', 'url' => route('formateur.parcours.index')],
                ['label' => $currentModule['title'], 'url' => $currentModule['url']],
                ['label' => $currentChapter['title'], 'url' => $currentChapter['url']],
            ],
        ]);
    }

    public function showLesson(string $module, string $chapter, string $lesson)
    {
        $catalogue = $this->catalogue();
        abort_unless(isset($catalogue[$module]), 404);
        abort_unless(isset($catalogue[$module]['chapters'][$chapter]), 404);
        abort_unless(isset($catalogue[$module]['chapters'][$chapter]['lessons'][$lesson]), 404);

        $currentModule = $catalogue[$module];
        $currentChapter = $currentModule['chapters'][$chapter];
        $currentLesson = $currentChapter['lessons'][$lesson];
        $lessonSequence = [];

        foreach ($currentModule['chapters'] as $chapterKey => $chapterItem) {
            foreach ($chapterItem['lessons'] as $lessonKey => $lessonItem) {
                $lessonSequence[] = [
                    'chapter_key' => $chapterKey,
                    'chapter_title' => $chapterItem['title'],
                    'lesson_key' => $lessonKey,
                    'lesson' => $lessonItem,
                ];
            }
        }

        $lessonIndex = collect($lessonSequence)->search(
            fn (array $item): bool => $item['chapter_key'] === $chapter && $item['lesson_key'] === $lesson
        );

        $previousLesson = $lessonIndex !== false && isset($lessonSequence[$lessonIndex - 1])
            ? $lessonSequence[$lessonIndex - 1]['lesson']
            : null;

        $nextLesson = $lessonIndex !== false && isset($lessonSequence[$lessonIndex + 1])
            ? $lessonSequence[$lessonIndex + 1]['lesson']
            : null;

        return view('formateur.parcours.lesson', [
            'pageTitle' => $currentLesson['title'],
            'parcoursModules' => $catalogue,
            'activeModuleKey' => $module,
            'activeChapterKey' => $chapter,
            'activeLessonKey' => $lesson,
            'currentModule' => $currentModule,
            'currentChapter' => $currentChapter,
            'currentLesson' => $currentLesson,
            'previousLesson' => $previousLesson,
            'nextLesson' => $nextLesson,
            'breadcrumbs' => [
                ['label' => 'Parcours formateur', 'url' => route('formateur.parcours.index')],
                ['label' => $currentModule['title'], 'url' => $currentModule['url']],
                ['label' => $currentChapter['title'], 'url' => $currentChapter['url']],
                ['label' => $currentLesson['code'], 'url' => $currentLesson['url']],
            ],
        ]);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function catalogue(): array
    {
        $modules = $this->rawModules();

        foreach ($modules as $moduleKey => &$module) {
            $module['url'] = route('formateur.parcours.modules.show', ['module' => $moduleKey]);
            $module['chapter_count'] = count($module['chapters']);
            $module['lesson_count'] = array_reduce(
                $module['chapters'],
                fn (int $carry, array $chapter): int => $carry + count($chapter['lessons'] ?? []),
                0
            );

            $firstChapterKey = array_key_first($module['chapters']);
            $module['first_chapter_url'] = $firstChapterKey
                ? route('formateur.parcours.chapters.show', ['module' => $moduleKey, 'chapter' => $firstChapterKey])
                : $module['url'];

            foreach ($module['chapters'] as $chapterKey => &$chapter) {
                $chapter['url'] = route('formateur.parcours.chapters.show', [
                    'module' => $moduleKey,
                    'chapter' => $chapterKey,
                ]);
                $chapter['lesson_count'] = count($chapter['lessons']);

                $firstLessonKey = array_key_first($chapter['lessons']);
                $chapter['first_lesson_url'] = $firstLessonKey
                    ? route('formateur.parcours.lessons.show', [
                        'module' => $moduleKey,
                        'chapter' => $chapterKey,
                        'lesson' => $firstLessonKey,
                    ])
                    : $chapter['url'];

                foreach ($chapter['lessons'] as $lessonKey => &$lesson) {
                    $lesson['url'] = route('formateur.parcours.lessons.show', [
                        'module' => $moduleKey,
                        'chapter' => $chapterKey,
                        'lesson' => $lessonKey,
                    ]);

                    if (!empty($lesson['scorm_directory'])) {
                        $publicScormIndex = public_path(trim($lesson['scorm_directory'], '/') . '/index_lms.html');
                        $lesson['scorm_url'] = file_exists($publicScormIndex)
                            ? asset(trim($lesson['scorm_directory'], '/') . '/index_lms.html')
                            : null;
                    } else {
                        $lesson['scorm_url'] = null;
                    }
                }
                unset($lesson);
            }
            unset($chapter);
        }
        unset($module);

        return $modules;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function rawModules(): array
    {
        return [
            'prendre-ses-reperes' => [
                'label' => 'Module 1',
                'title' => 'Prendre ses reperes',
                'description' => 'Comprendre l espace formateur, la navigation, les rubriques principales et le role du formateur.',
                'duration_label' => '36 min',
                'status_label' => 'Disponible',
                'trainer_name' => 'Equipe Oneduc',
                'level_label' => 'Tous niveaux',
                'cta_label' => 'Voir le parcours',
                'progress_percentage' => 0,
                'presentation' => [
                    'Ce module a pour objectif de permettre au formateur de prendre ses reperes dans la plateforme des ses premiers usages.',
                    'Il clarifie les grandes zones de navigation, les rubriques utiles au quotidien et le role specifique du formateur parmi les differents profils d acces.',
                    'A travers ce module, le formateur decouvrira comment entrer dans son espace, comprendre ce qu il peut faire immediatement et situer les actions qui relevent d autres profils.',
                ],
                'goals' => [
                    'Se reperer rapidement dans l espace formateur.',
                    'Associer les rubriques principales a des besoins concrets d usage.',
                    'Comprendre les differences entre les profils d acces.',
                ],
                'prerequisites' => [
                    'Disposer d un acces formateur a la plateforme.',
                    'Avoir realise une premiere connexion ou etre sur le point de la faire.',
                    'Aucun prerequis technique supplementaire n est necessaire.',
                ],
                'chapters' => [
                    'premiere-connexion' => [
                        'label' => 'Chapitre 1',
                        'title' => 'La premiere connexion',
                        'description' => 'Entrer dans la plateforme, reperer l espace formateur et identifier les rubriques utiles des les premiers instants.',
                        'duration_label' => '12 min',
                        'objective' => 'Se reperer dans la plateforme Oneduc en tant que formateur et identifier les principales rubriques utiles a son activite.',
                        'progress_percentage' => 0,
                        'lessons' => [
                            'entree-plateforme-espace-formateur' => [
                                'code' => '1.1',
                                'title' => 'Entrer dans la plateforme et reperer l espace formateur',
                                'duration_label' => '5 min',
                                'objective' => 'Localiser les principales zones de navigation de l espace formateur.',
                                'pedagogical_intention' => 'Reduire la friction du premier acces en donnant immediatement des reperes simples dans l interface.',
                                'method' => 'Demonstrative puis active guidee',
                                'learning_process' => 'Observer, localiser',
                                'subject' => 'Le point d entree dans la plateforme, la premiere connexion, le tableau de bord, le menu lateral et les acces rapides.',
                                'activity' => 'Screencast d accueil ou demonstration courte avec hotspots sur les principales zones de navigation.',
                                'resources' => 'Captures d ecran de la page d accueil, de l ecran d enregistrement si necessaire et du cockpit formateur.',
                            ],
                            'rubriques-cles-usages-immediats' => [
                                'code' => '1.2',
                                'title' => 'Reperer les rubriques cles et leurs usages immediats',
                                'duration_label' => '7 min',
                                'objective' => 'Distinguer les rubriques essentielles de la plateforme et reperer les principales fonctionnalites utiles a l organisation du parcours, a la gestion des groupes et au suivi des apprenants.',
                                'pedagogical_intention' => 'Aider le formateur a s orienter rapidement dans l interface et a associer chaque rubrique a un besoin concret.',
                                'method' => 'Active, guidee par mini-cas',
                                'learning_process' => 'Observer, distinguer, associer',
                                'subject' => 'Les rubriques liees aux parcours, aux groupes, au suivi, aux outils d animation et aux reglages generaux.',
                                'activity' => 'Mini-cas de type "Je veux..." dans lesquels l apprenant doit retrouver la bonne rubrique ou la bonne fonctionnalite.',
                                'resources' => 'Captures annotees de l interface, cartes d actions et activite d association a creer.',
                            ],
                        ],
                    ],
                    'relier-les-fonctionnalites' => [
                        'label' => 'Chapitre 2',
                        'title' => 'Relier les fonctionnalites',
                        'description' => 'Associer les outils de la plateforme a des usages pedagogiques concrets pour organiser, suivre et animer.',
                        'duration_label' => '12 min',
                        'objective' => 'Relier les fonctionnalites cles a des usages pedagogiques concrets.',
                        'progress_percentage' => 0,
                        'lessons' => [
                            'organisation-structuration-parcours' => [
                                'code' => '2.1',
                                'title' => 'Associer les fonctions d organisation a la structuration d un parcours',
                                'duration_label' => '4 min',
                                'objective' => 'Associer les fonctionnalites d organisation aux besoins de structuration d un parcours de formation.',
                                'pedagogical_intention' => 'Faire comprendre que les outils d organisation servent a construire une progression et non seulement a ranger des contenus.',
                                'method' => 'Active',
                                'learning_process' => 'Associer, justifier',
                                'subject' => 'Les fonctionnalites utiles pour structurer un parcours, ordonner les contenus et preparer une progression pedagogique coherente.',
                                'activity' => 'Etude de mini-cas avec choix de la fonctionnalite la plus adaptee a un besoin de structuration.',
                                'resources' => 'Cas pedagogiques courts, captures d ecran et feedbacks argumentes.',
                            ],
                            'fonctions-de-suivi' => [
                                'code' => '2.2',
                                'title' => 'Associer les fonctions de suivi',
                                'duration_label' => '4 min',
                                'objective' => 'Relier les fonctionnalites de suivi aux actions de relance, de soutien et d accompagnement des apprenants.',
                                'pedagogical_intention' => 'Faire percevoir le suivi comme un levier d accompagnement et non comme une simple consultation d indicateurs.',
                                'method' => 'Active et reflexive',
                                'learning_process' => 'Relier, interpreter',
                                'subject' => 'Les informations de progression, d activite et de resultats qui permettent d ajuster l accompagnement.',
                                'activity' => 'Association entre donnees de suivi et actions possibles du formateur a partir d un mini-scenario.',
                                'resources' => 'Exemples d indicateurs, cas d apprenants et tableau d association.',
                            ],
                            'animation-engagement-apprenants' => [
                                'code' => '2.3',
                                'title' => 'Associer les fonctions d animation a l engagement des apprenants',
                                'duration_label' => '4 min',
                                'objective' => 'Relier les fonctionnalites d animation aux situations favorisant la participation, l interaction et l engagement des apprenants.',
                                'pedagogical_intention' => 'Montrer que certaines fonctionnalites servent a soutenir l attention et la participation tout au long de la formation.',
                                'method' => 'Active',
                                'learning_process' => 'Relier, choisir',
                                'subject' => 'Les outils ou fonctions qui soutiennent l animation, l interaction et l engagement dans un parcours de formation.',
                                'activity' => 'Activite d association entre un objectif d animation et la fonctionnalite la plus pertinente.',
                                'resources' => 'Tableau d usages, exemples de situations pedagogiques et feedbacks.',
                            ],
                        ],
                    ],
                    'situer-role-formateur' => [
                        'label' => 'Chapitre 3',
                        'title' => 'Situer le role du formateur parmi les differents profils d acces',
                        'description' => 'Comprendre ce que le formateur peut faire, ce qui releve des autres profils et comment se repartissent les espaces.',
                        'duration_label' => '12 min',
                        'objective' => 'Situer le role du formateur parmi les differents profils d acces de l environnement Oneduc.',
                        'progress_percentage' => 0,
                        'lessons' => [
                            'comparer-profils-acces' => [
                                'code' => '3.1',
                                'title' => 'Comparer les profils d acces',
                                'duration_label' => '4 min',
                                'objective' => 'Distinguer les fonctionnalites accessibles au formateur, a l administrateur, au stagiaire et a l observateur.',
                                'pedagogical_intention' => 'Aider l apprenant a comprendre son perimetre d action reel dans l environnement Oneduc.',
                                'method' => 'Interrogative puis active',
                                'learning_process' => 'Distinguer, comparer',
                                'subject' => 'Les grandes differences entre les profils d acces et leurs possibilites d action respectives.',
                                'activity' => 'Tableau comparatif a completer ou activite de tri des actions selon le profil utilisateur.',
                                'resources' => 'Tableau des profils, exemples d actions et consigne de tri.',
                            ],
                            'differencier-espaces-niveaux-acces' => [
                                'code' => '3.2',
                                'title' => 'Differencier les espaces et les niveaux d acces',
                                'duration_label' => '4 min',
                                'objective' => 'Differencier les espaces, droits et niveaux d acces selon le profil utilisateur.',
                                'pedagogical_intention' => 'Faire visualiser les differences entre les espaces consultes et manipules selon les profils.',
                                'method' => 'Active',
                                'learning_process' => 'Differencier, reperer',
                                'subject' => 'Les differences entre espace formateur, espace stagiaire, espace administrateur et acces observateur.',
                                'activity' => 'Activite de comparaison a partir de captures d ecran ou de vignettes d interface.',
                                'resources' => 'Captures des differents espaces, activite de comparaison et feedback visuel.',
                            ],
                            'situer-responsabilites-formateur' => [
                                'code' => '3.3',
                                'title' => 'Situer les responsabilites du formateur',
                                'duration_label' => '4 min',
                                'objective' => 'Situer les responsabilites et les limites d intervention du formateur dans l organisation generale de l environnement Oneduc.',
                                'pedagogical_intention' => 'Clarifier ce qui releve de la responsabilite du formateur et ce qui depend d un autre profil.',
                                'method' => 'Reflexive puis active',
                                'learning_process' => 'Situer, delimiter',
                                'subject' => 'Ce que le formateur peut organiser, suivre, animer ou ajuster, et ce qu il ne peut pas administrer directement.',
                                'activity' => 'Mini-cas ou QCM de decision sur le profil competent pour realiser une action donnee.',
                                'resources' => 'Cas d usage, consignes de decision et correction commentee.',
                            ],
                        ],
                    ],
                ],
            ],
            'organiser-ses-parcours' => [
                'label' => 'Module 2',
                'title' => 'Organiser ses parcours',
                'description' => 'Preparer l environnement de formation, mettre en place un groupe et securiser l acces des stagiaires.',
                'duration_label' => '33 min',
                'status_label' => 'Disponible',
                'trainer_name' => 'Equipe Oneduc',
                'level_label' => 'Tous niveaux',
                'cta_label' => 'Voir le parcours',
                'progress_percentage' => 0,
                'presentation' => [
                    'Ce module accompagne le formateur dans la mise en place concrete de son environnement de formation dans Oneduc.',
                    'Il montre comment preparer les informations utiles, creer un groupe a partir d un besoin, organiser son contenu et parametrer les acces indispensables.',
                    'A travers ce module, le formateur apprend aussi a ajuster un groupe existant et a corriger les premiers problemes d acces rencontres par les stagiaires.',
                ],
                'goals' => [
                    'Identifier les elements necessaires a la mise en place d un environnement de formation dans Oneduc.',
                    'Mettre en place un groupe de formation en organisant les modules et les parametres necessaires a son fonctionnement.',
                    'Gerer un groupe existant et securiser l acces des stagiaires au parcours.',
                ],
                'prerequisites' => [
                    'Avoir parcouru le module 1 ou disposer deja des reperes de base dans l espace formateur.',
                    'Connaitre les rubriques principales liees aux formations et aux groupes.',
                    'Aucun prerequis technique supplementaire n est necessaire.',
                ],
                'chapters' => [
                    'preparer-les-contenus' => [
                        'label' => 'Chapitre 1',
                        'title' => 'Preparer la mise en place d un environnement de formation',
                        'description' => 'Identifier les composants utiles et preparer les informations necessaires a l ouverture d un premier environnement de formation.',
                        'duration_label' => '11 min',
                        'objective' => 'Identifier les elements necessaires a la mise en place d un environnement de formation dans Oneduc.',
                        'progress_percentage' => 0,
                        'lessons' => [
                            'retrouver-les-espaces-de-preparation' => [
                                'code' => '1.1',
                                'title' => 'Identifier les elements essentiels',
                                'duration_label' => '5 min',
                                'scorm_directory' => 'modules/parcours_formateur/module_2_organiser_ses_parcours/chapitre_1_preparer_lenvironnement_de_formation/lecon_1_1_les_composants_indispensables/LesComposantsIndispensables',
                                'objective' => 'Identifier les elements indispensables a la mise en place d un premier environnement de formation.',
                                'pedagogical_intention' => 'Donner des reperes simples pour distinguer les composants de base a reunir avant toute mise en place.',
                                'method' => 'Demonstrative puis guidee',
                                'learning_process' => 'Observer, reperer',
                                'subject' => 'Les composants essentiels a preparer : groupe, modules, informations de cadrage et premiers points d acces utiles.',
                                'activity' => 'Parcours guide avec captures d ecran et reperage des composants indispensables avant l ouverture du parcours.',
                                'resources' => 'Captures annotees de l espace formateur, schema simple des composants et consigne de reperage.',
                                'editorial' => [
                                    'intro' => [
                                        'Avant de creer un environnement de formation, le formateur a besoin d identifier les composants indispensables a reunir dans la plateforme.',
                                        'Cette lecon sert a poser un premier cadre simple : quels elements preparer, a quoi ils servent et dans quel ordre les verifier avant ouverture.',
                                    ],
                                    'focus_cards' => [
                                        [
                                            'title' => 'Le groupe de formation',
                                            'body' => 'C est l espace central a partir duquel on rattache les stagiaires, les modules et les parametres utiles au bon fonctionnement du parcours.',
                                        ],
                                        [
                                            'title' => 'Les modules associes',
                                            'body' => 'Ils constituent le contenu mis a disposition des stagiaires. Leur presence et leur ordre doivent etre verifies des le depart.',
                                        ],
                                        [
                                            'title' => 'Les informations de cadrage',
                                            'body' => 'Le nom du groupe, son objectif, le public vise et les parametres d acces facilitent une mise en route plus claire.',
                                        ],
                                        [
                                            'title' => 'Les acces stagiaires',
                                            'body' => 'Ils doivent etre anticipes des la preparation pour que le groupe soit fonctionnel et accessible au bon moment.',
                                        ],
                                    ],
                                    'steps' => [
                                        'Identifier le groupe ou le futur groupe de formation concerne.',
                                        'Verifier les modules qui devront etre associes au groupe.',
                                        'Rassembler les informations utiles a l ouverture du parcours.',
                                        'Anticiper la maniere dont les stagiaires accederont ensuite au contenu.',
                                    ],
                                    'checklist' => [
                                        'Je sais quels composants doivent etre prets avant l ouverture de la formation.',
                                        'Je distingue le groupe, les modules et les informations de cadrage.',
                                        'Je peux verifier rapidement si l environnement est pret a etre utilise.',
                                        'Je peux expliquer le role de chaque composant indispensable.',
                                    ],
                                    'placeholder_note' => 'Cette lecon est volontairement presentee en version editoriale provisoire. Le contenu interactif SCORM viendra ensuite prendre place dans cette meme vue.',
                                ],
                            ],
                            'distinguer-contenu-ressource-et-structure' => [
                                'code' => '1.2',
                                'title' => 'Preparer les informations utiles',
                                'duration_label' => '6 min',
                                'objective' => 'Preparer les informations necessaires a la creation et a l ouverture d un parcours de formation.',
                                'pedagogical_intention' => 'Aider le formateur a anticiper les informations a renseigner pour ouvrir un parcours dans de bonnes conditions.',
                                'method' => 'Active, guidee par mini-cas',
                                'learning_process' => 'Preparer, associer',
                                'subject' => 'Les informations utiles a preparer : intitule, finalite, public vise, modules mobilises et conditions d acces.',
                                'activity' => 'Mini-cas dans lequel l apprenant prepare les informations a renseigner avant creation d un parcours.',
                                'resources' => 'Fiche de preparation, exemples de parametres et correction argumentee.',
                            ],
                        ],
                    ],
                    'structurer-la-progression' => [
                        'label' => 'Chapitre 2',
                        'title' => 'Mettre en place un groupe de formation et structurer son contenu',
                        'description' => 'Creer un groupe de formation, organiser les modules associes et parametrer les acces utiles a son fonctionnement.',
                        'duration_label' => '11 min',
                        'objective' => 'Mettre en place un groupe de formation en organisant les modules et les parametres necessaires a son fonctionnement.',
                        'progress_percentage' => 0,
                        'lessons' => [
                            'ordonner-les-etapes-du-parcours' => [
                                'code' => '2.1',
                                'title' => 'Creer un groupe de formation a partir d un besoin',
                                'duration_label' => '5 min',
                                'objective' => 'Creer un groupe de formation dans Oneduc a partir d un besoin donne.',
                                'pedagogical_intention' => 'Montrer comment passer d un besoin de formation a la creation concrete d un groupe exploitable.',
                                'method' => 'Demonstrative puis active',
                                'learning_process' => 'Observer, reproduire',
                                'subject' => 'Les etapes de creation d un groupe de formation et les premiers parametres a definir.',
                                'activity' => 'Mise en situation guidee de creation d un groupe a partir d un besoin formule.',
                                'resources' => 'Scenario de besoin, captures d ecran de creation et fiche pas a pas.',
                            ],
                            'rendre-la-progresson-lisible-pour-lapprenant' => [
                                'code' => '2.2',
                                'title' => 'Organiser les modules du groupe et parametrer l acces',
                                'duration_label' => '6 min',
                                'objective' => 'Organiser les modules associes a un groupe et parametrer l acces afin de rendre la formation fonctionnelle et accessible aux stagiaires.',
                                'pedagogical_intention' => 'Faire comprendre que la creation du groupe ne suffit pas et qu il faut aussi organiser son contenu et ses conditions d acces.',
                                'method' => 'Active et reflexive',
                                'learning_process' => 'Organiser, parametrer',
                                'subject' => 'Le choix des modules associes, leur organisation et les reglages d acces utiles a l ouverture du groupe.',
                                'activity' => 'Mini-cas de parametrage d un groupe pour le rendre utilisable par les stagiaires.',
                                'resources' => 'Exemple de groupe, liste de modules et guide simple de parametrage.',
                            ],
                        ],
                    ],
                    'mettre-en-place-un-parcours-coherent' => [
                        'label' => 'Chapitre 3',
                        'title' => 'Gerer le groupe et securiser l acces des stagiaires',
                        'description' => 'Ajuster un groupe existant, y associer les stagiaires et corriger les premiers blocages d acces rencontres.',
                        'duration_label' => '11 min',
                        'objective' => 'Gerer un groupe de formation en ajustant les acces et en securisant l entree des stagiaires dans le parcours.',
                        'progress_percentage' => 0,
                        'lessons' => [
                            'associer-le-bon-parcours-au-bon-contexte' => [
                                'code' => '3.1',
                                'title' => 'Ajuster le groupe et associer les stagiaires',
                                'duration_label' => '5 min',
                                'objective' => 'Ajuster un groupe de formation existant et y associer les stagiaires de maniere coherente.',
                                'pedagogical_intention' => 'Montrer qu un groupe peut evoluer et qu il doit etre ajuste avant ou pendant son usage.',
                                'method' => 'Active',
                                'learning_process' => 'Ajuster, associer',
                                'subject' => 'Les actions utiles pour corriger un groupe existant et y ajouter les bons stagiaires.',
                                'activity' => 'Mise en situation d ajustement de groupe avec association de stagiaires a partir d un cas concret.',
                                'resources' => 'Cas d usage, liste de stagiaires et fiche de verification.',
                            ],
                            'controler-le-parcours-avant-diffusion' => [
                                'code' => '3.2',
                                'title' => 'Diagnostiquer et corriger un probleme d acces',
                                'duration_label' => '6 min',
                                'objective' => 'Identifier et corriger un probleme d acces au parcours pour un stagiaire.',
                                'pedagogical_intention' => 'Installer les premiers reflexes de diagnostic lorsqu un stagiaire ne parvient pas a entrer dans le parcours.',
                                'method' => 'Demonstrative puis active',
                                'learning_process' => 'Diagnostiquer, corriger',
                                'subject' => 'Les causes frequentes de blocage d acces et les verifications simples a mener pour les corriger.',
                                'activity' => 'Mini-cas de diagnostic a partir d un stagiaire qui ne peut pas acceder a sa formation.',
                                'resources' => 'Cas de blocage, grille de diagnostic et correction pas a pas.',
                            ],
                        ],
                    ],
                ],
            ],
            'gerer-ses-groupes' => [
                'label' => 'Module 3',
                'title' => 'Gerer ses groupes',
                'description' => 'Creer, retrouver et administrer les groupes, ainsi que rattacher les bons apprenants.',
                'duration_label' => 'Contenu a venir',
                'status_label' => 'En preparation',
                'trainer_name' => 'Equipe Oneduc',
                'level_label' => 'Tous niveaux',
                'cta_label' => 'Voir le parcours',
                'progress_percentage' => 0,
                'presentation' => [
                    'Ce module viendra ensuite aider le formateur a creer ses groupes, retrouver les bons espaces et rattacher les apprenants de facon plus fluide.',
                    'Il conservera la meme structure module detail, chapitre et lecon pour rester coherent avec le reste du parcours formateur.',
                ],
                'goals' => [
                    'Comprendre la logique de gestion des groupes.',
                    'Retrouver les bons espaces d administration de groupe.',
                    'Rattacher les bons apprenants au bon endroit.',
                ],
                'prerequisites' => [
                    'Avoir parcouru les reperes de base du module 1.',
                    'Le detail pedagogique de ce module sera ajoute dans une prochaine phase.',
                ],
                'chapters' => [],
            ],
            'suivre-et-accompagner' => [
                'label' => 'Module 4',
                'title' => 'Suivre et accompagner',
                'description' => 'Lire les indicateurs utiles, reperer les besoins, suivre la progression et agir au bon moment.',
                'duration_label' => 'Contenu a venir',
                'status_label' => 'En preparation',
                'trainer_name' => 'Equipe Oneduc',
                'level_label' => 'Tous niveaux',
                'cta_label' => 'Voir le parcours',
                'progress_percentage' => 0,
                'presentation' => [
                    'Ce module viendra ensuite aider le formateur a lire les indicateurs utiles, reperer les besoins et intervenir au bon moment.',
                    'Il reprendra la meme structure que les autres modules pour garder une navigation simple et homogene.',
                ],
                'goals' => [
                    'Lire les indicateurs les plus utiles.',
                    'Identifier les besoins de relance ou de soutien.',
                    'Agir au bon moment dans le suivi des apprenants.',
                ],
                'prerequisites' => [
                    'Avoir les reperes de base dans l espace formateur.',
                    'Les contenus pedagogiques de ce module seront completes ulterieurement.',
                ],
                'chapters' => [],
            ],
            'trouver-de-laide' => [
                'label' => 'Module 5',
                'title' => 'Trouver de l aide',
                'description' => 'Retrouver les ressources utiles, les points d appui, l assistance et les reponses aux questions frequentes.',
                'duration_label' => 'Contenu a venir',
                'status_label' => 'En preparation',
                'trainer_name' => 'Equipe Oneduc',
                'level_label' => 'Tous niveaux',
                'cta_label' => 'Voir le parcours',
                'progress_percentage' => 0,
                'presentation' => [
                    'Ce module viendra centraliser les ressources, les aides contextuelles et les points de contact utiles aux formateurs.',
                    'Il servira de porte d entree simple vers la documentation, les reponses frequentes et les appuis disponibles.',
                ],
                'goals' => [
                    'Savoir ou chercher selon le besoin rencontre.',
                    'Retrouver les ressources utiles plus rapidement.',
                    'Identifier les bons points d appui et d assistance.',
                ],
                'prerequisites' => [
                    'Aucun prerequis specifique.',
                    'Le detail de ce module sera ajoute dans une prochaine phase.',
                ],
                'chapters' => [],
            ],
        ];
    }
}
