<?php

namespace App\Support\Parcours;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RegistreOutilsParcours
{
    /**
     * Les données d'exécution sont interdites dans un modèle de parcours.
     * Une session réelle sera créée plus tard, au lancement de l'activité.
     *
     * @var array<int, string>
     */
    private const CLES_RUNTIME_INTERDITES = [
        'access_code',
        'code_acces',
        'responses',
        'reponses',
        'results',
        'resultats',
        'participants',
        'entries',
        'votes',
        'scores',
        'session_id',
        'group_id',
        'groupe_id',
        'user_id',
        'utilisateur_id',
        'formateur_id',
    ];

    /**
     * @return array<string, array<string, mixed>>
     */
    public function tous(): array
    {
        return collect($this->definitions())
            ->map(function (array $definition, string $cle): array {
                $definition['cle'] = $cle;
                $definition['actif'] = $this->estActif($cle);

                return $definition;
            })
            ->all();
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function actifs(): array
    {
        return collect($this->tous())
            ->filter(fn (array $definition): bool => $definition['actif'])
            ->all();
    }

    public function reconnait(string $cle): bool
    {
        return array_key_exists($cle, $this->definitions());
    }

    public function estActif(string $cle): bool
    {
        $definition = $this->definitions()[$cle] ?? null;

        if ($definition === null) {
            return false;
        }

        if ($cle === 'page-collaborative') {
            return filled(config('services.hedgedoc.base_url'));
        }

        $configurationActivation = $definition['configuration_activation'] ?? null;

        return $configurationActivation === null
            || (bool) config($configurationActivation, false);
    }

    public function libelle(string $cle): string
    {
        return (string) ($this->definitions()[$cle]['libelle'] ?? $cle);
    }

    public function executionDisponible(string $cle): bool
    {
        return (bool) ($this->definitions()[$cle]['execution_disponible'] ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function configurationParDefaut(string $cle): array
    {
        return $this->definitions()[$cle]['configuration_defaut'] ?? [];
    }

    /**
     * @param  array<string, mixed>  $configuration
     * @return array<string, mixed>
     *
     * @throws ValidationException
     */
    public function valider(string $cle, array $configuration, string $prefixeErreur = 'configuration'): array
    {
        if (! $this->reconnait($cle)) {
            throw ValidationException::withMessages([
                $prefixeErreur => 'Cet outil n’est pas reconnu par le registre des parcours.',
            ]);
        }

        if (! $this->estActif($cle)) {
            throw ValidationException::withMessages([
                $prefixeErreur => 'Cet outil est actuellement désactivé dans la configuration Oneduc.',
            ]);
        }

        $cleInterdite = $this->trouverCleRuntimeInterdite($configuration);

        if ($cleInterdite !== null) {
            throw ValidationException::withMessages([
                $prefixeErreur => "La clé « {$cleInterdite} » contient une donnée d’exécution qui ne peut pas être enregistrée dans un modèle.",
            ]);
        }

        $definition = $this->definitions()[$cle];
        $validateur = Validator::make($configuration, $definition['regles']);

        if ($validateur->fails()) {
            $messages = collect($validateur->errors()->toArray())
                ->mapWithKeys(fn (array $erreurs, string $champ): array => [
                    $prefixeErreur.'.'.$champ => $erreurs,
                ])
                ->all();

            throw ValidationException::withMessages($messages);
        }

        return $validateur->validated();
    }

    /**
     * @param  array<string, mixed>  $configuration
     */
    private function trouverCleRuntimeInterdite(array $configuration): ?string
    {
        foreach ($configuration as $cle => $valeur) {
            $cleNormalisee = Str::snake(Str::ascii((string) $cle));

            if (in_array($cleNormalisee, self::CLES_RUNTIME_INTERDITES, true)) {
                return (string) $cle;
            }

            if (is_array($valeur)) {
                $cleEnfant = $this->trouverCleRuntimeInterdite($valeur);

                if ($cleEnfant !== null) {
                    return $cleEnfant;
                }
            }
        }

        return null;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function definitions(): array
    {
        $reglesCommunes = [
            'titre' => ['required', 'string', 'max:255'],
            'consigne' => ['nullable', 'string', 'max:2000'],
        ];

        return [
            'nuage-de-mots' => [
                'libelle' => 'Nuage de mots',
                'configuration_activation' => null,
                'execution_disponible' => false,
                'configuration_defaut' => ['titre' => 'Nuage de mots', 'consigne' => null, 'questions' => ['Question à compléter'], 'duree_minutes' => null],
                'regles' => $reglesCommunes + [
                    'questions' => ['required', 'array', 'min:1', 'max:10'],
                    'questions.*' => ['required', 'string', 'max:500'],
                    'duree_minutes' => ['nullable', 'integer', 'min:1', 'max:120'],
                ],
            ],
            'sondage' => [
                'libelle' => 'Sondage',
                'configuration_activation' => null,
                'execution_disponible' => false,
                'configuration_defaut' => ['titre' => 'Sondage', 'consigne' => null, 'questions' => [['intitule' => 'Question à compléter', 'choix' => ['Choix 1', 'Choix 2']]]],
                'regles' => $reglesCommunes + $this->reglesQuestionsChoix(false),
            ],
            'quiz' => [
                'libelle' => 'Quiz en direct',
                'configuration_activation' => null,
                'execution_disponible' => false,
                'configuration_defaut' => ['titre' => 'Quiz en direct', 'consigne' => null, 'questions' => [['intitule' => 'Question à compléter', 'choix' => ['Choix 1', 'Choix 2'], 'bonne_reponse' => 'Choix 1']]],
                'regles' => $reglesCommunes + $this->reglesQuestionsChoix(true),
            ],
            'tableau-blanc' => $this->definitionSimple('Tableau blanc'),
            'mur-questions' => $this->definitionSimple('Mur de questions'),
            'vrai-faux' => [
                'libelle' => 'Vrai ou Faux',
                'configuration_activation' => 'outils.vraifaux.enabled',
                'execution_disponible' => true,
                'configuration_defaut' => ['titre' => 'Vrai ou Faux', 'consigne' => null, 'affirmations' => [['texte' => 'Affirmation à compléter', 'reponse' => true]]],
                'regles' => $reglesCommunes + [
                    'affirmations' => ['required', 'array', 'min:1', 'max:50'],
                    'affirmations.*' => ['required', 'array:texte,reponse'],
                    'affirmations.*.texte' => ['required', 'string', 'max:1000'],
                    'affirmations.*.reponse' => ['required', 'boolean'],
                ],
            ],
            'buzzer' => [
                'libelle' => 'Buzzer Quiz',
                'configuration_activation' => 'outils.buzzer.enabled',
                'execution_disponible' => false,
                'configuration_defaut' => ['titre' => 'Buzzer Quiz', 'consigne' => null, 'questions' => ['Question à compléter']],
                'regles' => $reglesCommunes + [
                    'questions' => ['required', 'array', 'min:1', 'max:100'],
                    'questions.*' => ['required', 'string', 'max:1000'],
                ],
            ],
            'echelle' => [
                'libelle' => 'Échelle de positionnement',
                'configuration_activation' => 'outils.echelle.enabled',
                'execution_disponible' => false,
                'configuration_defaut' => ['titre' => 'Échelle de positionnement', 'consigne' => null, 'questions' => ['Question à compléter'], 'minimum' => 1, 'maximum' => 5],
                'regles' => $reglesCommunes + [
                    'questions' => ['required', 'array', 'min:1', 'max:50'],
                    'questions.*' => ['required', 'string', 'max:1000'],
                    'minimum' => ['required', 'integer', 'min:0', 'max:10'],
                    'maximum' => ['required', 'integer', 'gt:minimum', 'max:20'],
                ],
            ],
            'composants' => [
                'libelle' => 'Zone de clic',
                'configuration_activation' => 'outils.composants.enabled',
                'execution_disponible' => false,
                'configuration_defaut' => ['titre' => 'Zone de clic', 'consigne' => null, 'image' => null, 'zones' => [['libelle' => 'Zone à définir', 'x' => 0, 'y' => 0, 'largeur' => 10, 'hauteur' => 10]]],
                'regles' => $reglesCommunes + [
                    'image' => ['nullable', 'string', 'max:2048'],
                    'zones' => ['required', 'array', 'min:1', 'max:50'],
                    'zones.*' => ['required', 'array:libelle,x,y,largeur,hauteur'],
                    'zones.*.libelle' => ['required', 'string', 'max:255'],
                    'zones.*.x' => ['required', 'numeric', 'between:0,100'],
                    'zones.*.y' => ['required', 'numeric', 'between:0,100'],
                    'zones.*.largeur' => ['required', 'numeric', 'between:0,100'],
                    'zones.*.hauteur' => ['required', 'numeric', 'between:0,100'],
                ],
            ],
            'roue' => [
                'libelle' => 'Roue aléatoire',
                'configuration_activation' => null,
                'execution_disponible' => false,
                'configuration_defaut' => ['titre' => 'Roue aléatoire', 'consigne' => null, 'elements' => ['Élément 1', 'Élément 2']],
                'regles' => $reglesCommunes + [
                    'elements' => ['required', 'array', 'min:2', 'max:200'],
                    'elements.*' => ['required', 'string', 'max:255'],
                ],
            ],
            'minuteur' => [
                'libelle' => 'Minuteur',
                'configuration_activation' => 'outils.minuteur.enabled',
                'execution_disponible' => false,
                'configuration_defaut' => ['titre' => 'Minuteur', 'consigne' => null, 'duree_secondes' => 300],
                'regles' => $reglesCommunes + [
                    'duree_secondes' => ['required', 'integer', 'min:1', 'max:86400'],
                ],
            ],
            'emargement' => $this->definitionSimple('Émargement'),
            'page-collaborative' => [
                ...$this->definitionSimple('Page collaborative'),
                'configuration_activation' => 'services.hedgedoc.base_url',
            ],
            'pendu' => [
                'libelle' => 'Jeu du pendu',
                'configuration_activation' => 'outils.pendu.enabled',
                'execution_disponible' => false,
                'configuration_defaut' => ['titre' => 'Jeu du pendu', 'consigne' => null, 'mots' => [['mot' => 'ONEDUC', 'indice' => 'Plateforme de formation']]],
                'regles' => $reglesCommunes + [
                    'mots' => ['required', 'array', 'min:1', 'max:100'],
                    'mots.*' => ['required', 'array:mot,indice'],
                    'mots.*.mot' => ['required', 'string', 'max:100'],
                    'mots.*.indice' => ['nullable', 'string', 'max:500'],
                ],
            ],
            'memoire' => [
                'libelle' => 'Jeu de mémoire',
                'configuration_activation' => 'outils.memoire.enabled',
                'execution_disponible' => false,
                'configuration_defaut' => ['titre' => 'Jeu de mémoire', 'consigne' => null, 'paires' => [['recto' => 'Carte A', 'verso' => 'Carte B']]],
                'regles' => $reglesCommunes + $this->reglesPaires('paires'),
            ],
            'carrousel' => [
                'libelle' => 'Carrousel',
                'configuration_activation' => 'outils.carrousel.enabled',
                'execution_disponible' => false,
                'configuration_defaut' => ['titre' => 'Carrousel', 'consigne' => null, 'diapositives' => [['titre' => 'Diapositive 1', 'contenu' => 'Contenu à compléter']]],
                'regles' => $reglesCommunes + [
                    'diapositives' => ['required', 'array', 'min:1', 'max:100'],
                    'diapositives.*' => ['required', 'array:titre,contenu'],
                    'diapositives.*.titre' => ['required', 'string', 'max:255'],
                    'diapositives.*.contenu' => ['nullable', 'string', 'max:10000'],
                ],
            ],
            'cartes-retourner' => [
                'libelle' => 'Cartes à retourner',
                'configuration_activation' => 'outils.cartes_retourner.enabled',
                'execution_disponible' => false,
                'configuration_defaut' => ['titre' => 'Cartes à retourner', 'consigne' => null, 'cartes' => [['recto' => 'Question', 'verso' => 'Réponse']]],
                'regles' => $reglesCommunes + $this->reglesPaires('cartes'),
            ],
            'tri-cartes' => [
                'libelle' => 'Cartes à trier',
                'configuration_activation' => 'outils.tri_cartes.enabled',
                'execution_disponible' => false,
                'configuration_defaut' => ['titre' => 'Cartes à trier', 'consigne' => null, 'categories' => ['Catégorie A', 'Catégorie B'], 'cartes' => [['texte' => 'Carte à classer', 'categorie' => 'Catégorie A']]],
                'regles' => $reglesCommunes + [
                    'categories' => ['required', 'array', 'min:2', 'max:20'],
                    'categories.*' => ['required', 'string', 'max:255', 'distinct'],
                    'cartes' => ['required', 'array', 'min:1', 'max:200'],
                    'cartes.*' => ['required', 'array:texte,categorie'],
                    'cartes.*.texte' => ['required', 'string', 'max:1000'],
                    'cartes.*.categorie' => ['required', 'string', 'max:255'],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function definitionSimple(string $libelle): array
    {
        return [
            'libelle' => $libelle,
            'configuration_activation' => null,
            'execution_disponible' => false,
            'configuration_defaut' => ['titre' => $libelle, 'consigne' => null],
            'regles' => [
                'titre' => ['required', 'string', 'max:255'],
                'consigne' => ['nullable', 'string', 'max:2000'],
            ],
        ];
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function reglesQuestionsChoix(bool $avecBonneReponse): array
    {
        $cles = $avecBonneReponse ? 'intitule,choix,bonne_reponse' : 'intitule,choix';
        $regles = [
            'questions' => ['required', 'array', 'min:1', 'max:50'],
            'questions.*' => ['required', 'array:'.$cles],
            'questions.*.intitule' => ['required', 'string', 'max:1000'],
            'questions.*.choix' => ['required', 'array', 'min:2', 'max:10'],
            'questions.*.choix.*' => ['required', 'string', 'max:500', 'distinct'],
        ];

        if ($avecBonneReponse) {
            $regles['questions.*.bonne_reponse'] = ['required', 'string', 'max:500'];
        }

        return $regles;
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function reglesPaires(string $cle): array
    {
        return [
            $cle => ['required', 'array', 'min:1', 'max:100'],
            $cle.'.*' => ['required', 'array:recto,verso'],
            $cle.'.*.recto' => ['required', 'string', 'max:2000'],
            $cle.'.*.verso' => ['required', 'string', 'max:2000'],
        ];
    }
}
