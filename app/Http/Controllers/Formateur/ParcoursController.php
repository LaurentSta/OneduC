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
        $lessonKeys = array_keys($currentChapter['lessons']);
        $lessonIndex = array_search($lesson, $lessonKeys, true);

        $previousLesson = $lessonIndex !== false && $lessonIndex > 0
            ? $currentChapter['lessons'][$lessonKeys[$lessonIndex - 1]]
            : null;

        $nextLesson = $lessonIndex !== false && isset($lessonKeys[$lessonIndex + 1])
            ? $currentChapter['lessons'][$lessonKeys[$lessonIndex + 1]]
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
                'label' => 'Etape 1',
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
                'label' => 'Etape 2',
                'title' => 'Organiser ses parcours',
                'description' => 'Decouvrir ou preparer les contenus, structurer une formation et mettre en place un parcours coherent.',
                'duration_label' => 'Contenu a venir',
                'status_label' => 'En preparation',
                'trainer_name' => 'Equipe Oneduc',
                'level_label' => 'Tous niveaux',
                'cta_label' => 'Voir le parcours',
                'progress_percentage' => 0,
                'presentation' => [
                    'Ce module viendra ensuite aider le formateur a preparer ses contenus, structurer ses sequences et rendre le parcours plus lisible pour les apprenants.',
                    'Il reprendra la meme logique module detail, chapitre et lecon afin de garder une experience homogene dans tout le parcours formateur.',
                ],
                'goals' => [
                    'Identifier ou preparer les contenus utiles.',
                    'Structurer une formation dans un ordre coherent.',
                    'Prevoir la suite sans melanger ce parcours au moteur principal de la plateforme.',
                ],
                'prerequisites' => [
                    'Avoir parcouru le module 1 ou disposer deja des reperes de base dans l espace formateur.',
                    'Les contenus detailes et les SCORM de ce module seront ajoutes dans une prochaine phase.',
                ],
                'chapters' => [],
            ],
            'gerer-ses-groupes' => [
                'label' => 'Etape 3',
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
                'label' => 'Etape 4',
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
                'label' => 'Etape 5',
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
