<?php

namespace App\Domains\ModulesFormateur\Actions;

use App\Domains\ModulesFormateur\Support\ExtracteurTexteDocument;
use App\Domains\ModulesFormateur\Support\GardeFouPromptIA;
use App\Domains\ModulesFormateur\Support\LimiteurBudgetTokensIA;
use App\Domains\ModulesFormateur\Support\LimiteurGenerationIA;
use App\Domains\ModulesFormateur\Support\MistralClient;
use App\Models\Module;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class GenererStructureFormationIA
{
    private const MAX_SOURCE_CHARS = 18000;

    private const SYSTEM_PROMPT = <<<'PROMPT'
Tu es un concepteur pédagogique qui construit une formation complète en ligne à partir d'un thème et/ou d'un document source donné par un formateur.
Réponds UNIQUEMENT avec un objet JSON de la forme :
{
  "title": "Titre de la formation",
  "description": "Description courte de la formation (2-3 phrases)",
  "objectifs": ["Objectif pédagogique 1", "Objectif pédagogique 2"],
  "chapters": [
    {
      "title": "Titre du chapitre",
      "lessons": [
        {
          "title": "Titre de la leçon",
          "blocks": [{"type": "text", "html": "<h2>...</h2><p>...</p>"}]
        }
      ]
    }
  ]
}
Règles :
- Formule tous les titres (formation, chapitres, leçons) de façon complète et concise : 30 caractères maximum. N'utilise jamais de points de suspension ou de troncature — si une idée ne tient pas en 30 caractères, reformule-la plus court plutôt que de la couper.
- Propose 3 à 5 objectifs pédagogiques, un verbe d'action mesurable et observable par objectif (ex : identifier, expliquer, appliquer, analyser, évaluer, concevoir). N'utilise jamais de verbes non mesurables comme "comprendre", "savoir", "connaître" ou "être sensibilisé à".
- Chaque objectif doit énoncer un résultat observable et vérifiable (ex : "Vous serez capable d'identifier les 3 principaux risques de phishing" plutôt que "Vous comprendrez les risques de phishing"). Un seul verbe d'action principal par objectif.
- Structure la progression des chapitres selon les niveaux cognitifs de la taxonomie de Bloom : commence par les niveaux "se souvenir" et "comprendre", termine par "appliquer", "analyser" ou "évaluer" si le niveau du public le permet.
- Adapte le vocabulaire, la complexité des explications et les exemples utilisés au profil du public cible fourni par le formateur (niveau, contexte).
- Propose entre 3 et 5 chapitres, chacun avec 2 à 4 leçons.
- Chaque leçon doit contenir 5 à 7 blocs "text" qui suivent toujours ce plan, dans cet ordre :
  1. Accroche : 2 à 3 phrases qui contextualisent l'intérêt du sujet pour le public visé.
  2. Développement : 2 à 4 blocs, un par sous-partie, chacun débutant par un titre <h3>, suivi d'explications et d'une liste à puces des points clés ou des étapes.
  3. Exemple concret : un bloc <h3>Exemple concret</h3> avec une mise en situation ancrée dans le contexte du public cible.
  4. À retenir : un dernier bloc <h3>À retenir</h3> avec une liste de 3 à 5 points de synthèse, dont le point le plus important mis en avant dans un <blockquote>.
- Utilise uniquement les balises HTML suivantes dans "html" : p, br, strong, em, u, ul, ol, li, h2, h3, h4, blockquote, a, code, pre.
- N'ajoute aucun texte hors de l'objet JSON.
PROMPT;

    private const NIVEAUX = [
        'debutant' => 'Débutant',
        'intermediaire' => 'Intermédiaire',
        'avance' => 'Avancé',
        'mixte' => 'Mixte (niveaux variés)',
    ];

    public function __construct(
        private readonly CreerModule $creerModule,
        private readonly CreerChapitre $creerChapitre,
        private readonly CreerLecon $creerLecon,
        private readonly MistralClient $mistral,
        private readonly ExtracteurTexteDocument $extracteur,
        private readonly GardeFouPromptIA $gardeFou,
        private readonly LimiteurGenerationIA $limiteur,
        private readonly LimiteurBudgetTokensIA $limiteurBudget,
        private readonly ImporterImagesDocument $importerImages,
    ) {}

    public function execute(
        ?string $theme,
        ?UploadedFile $document,
        int $trainerId,
        ?string $niveauPublic = null,
        ?string $contextePublic = null,
        ?string $contraintesPublic = null,
        ?array $contexteCatalogue = null,
    ): Module {
        if ($this->limiteur->tropDeTentatives($trainerId)) {
            throw new RuntimeException('Limite de '.$this->limiteur->limiteQuotidienne($trainerId).' générations IA par jour atteinte. Réessayez demain.');
        }

        if ($this->limiteurBudget->budgetDepasse($trainerId)) {
            throw new RuntimeException(
                'Vous avez atteint votre plafond mensuel de '.number_format($this->limiteurBudget->limiteMensuelle($trainerId), 0, ',', ' ').' tokens IA. Réessayez le mois prochain.'
            );
        }

        $theme = trim((string) $theme);
        $sourceText = $document ? Str::limit($this->extracteur->extract($document), self::MAX_SOURCE_CHARS, '') : '';

        if ($theme === '' && $sourceText === '') {
            throw new RuntimeException("Merci de renseigner un thème ou d'importer un document.");
        }

        $this->gardeFou->verifier($theme."\n\n".$sourceText, $trainerId);
        $this->limiteur->enregistrerTentative($trainerId);

        $userPromptParts = [];
        if ($theme !== '') {
            $userPromptParts[] = 'Thème demandé par le formateur : '.$theme;
        }

        $profil = [];
        if ($niveauPublic !== null && isset(self::NIVEAUX[$niveauPublic])) {
            $profil[] = 'Niveau : '.self::NIVEAUX[$niveauPublic];
        }
        if ($contextePublic !== null && trim($contextePublic) !== '') {
            $profil[] = 'Contexte / secteur : '.trim($contextePublic);
        }
        if ($contraintesPublic !== null && trim($contraintesPublic) !== '') {
            $profil[] = 'Contraintes ou pré-requis : '.trim($contraintesPublic);
        }
        if ($profil !== []) {
            $userPromptParts[] = "Profil du public cible :\n- ".implode("\n- ", $profil);
        }

        if ($sourceText !== '') {
            $userPromptParts[] = "Document source à structurer en formation :\n\n".$sourceText;
        }

        $raw = $this->mistral->chat(
            self::SYSTEM_PROMPT,
            implode("\n\n", $userPromptParts),
            timeoutSeconds: 300,
            maxTokens: 20000,
            trainerId: $trainerId,
        );

        $decoded = json_decode($raw, true);
        if (! is_array($decoded) || ! isset($decoded['chapters']) || ! is_array($decoded['chapters']) || $decoded['chapters'] === []) {
            throw new RuntimeException("La réponse de l'IA n'a pas pu être interprétée.");
        }

        $title = trim((string) ($decoded['title'] ?? ''));
        if ($title === '') {
            $title = $theme !== '' ? $theme : pathinfo($document->getClientOriginalName(), PATHINFO_FILENAME);
        }

        $objectifs = collect((array) ($decoded['objectifs'] ?? []))
            ->map(fn ($objectif) => trim(strip_tags((string) $objectif)))
            ->filter()
            ->map(fn ($objectif) => Str::limit($objectif, 255, ''))
            ->take(8)
            ->values()
            ->all();

        return DB::transaction(function () use ($decoded, $title, $objectifs, $trainerId, $document, $contexteCatalogue) {
            $donneesModule = [
                'module_title' => Str::limit($title, 255, ''),
                'description' => $decoded['description'] ?? null,
                'objectifs' => $objectifs !== [] ? $objectifs : null,
                'category_id' => $contexteCatalogue['category_id'] ?? null,
            ];

            $module = $contexteCatalogue !== null
                ? $this->creerModule->creerFormationCatalogueVide(
                    $donneesModule,
                    $trainerId,
                    $contexteCatalogue['formateur_id'] ?? null,
                )
                : $this->creerModule->creerModuleVide($donneesModule, $trainerId);

            $imageBlocks = $document ? $this->importerImages->importer($document, $module) : [];
            $isFirstLesson = true;

            foreach ($decoded['chapters'] as $chapterData) {
                if (! is_array($chapterData) || empty($chapterData['title'])) {
                    continue;
                }

                $section = $this->creerChapitre->execute($module, Str::limit((string) $chapterData['title'], 255, ''));

                foreach ((array) ($chapterData['lessons'] ?? []) as $lessonData) {
                    if (! is_array($lessonData) || empty($lessonData['title'])) {
                        continue;
                    }

                    $blocks = (array) ($lessonData['blocks'] ?? []);
                    if ($isFirstLesson && $imageBlocks !== []) {
                        $blocks = array_merge($blocks, $imageBlocks);
                        $isFirstLesson = false;
                    }

                    $this->creerLecon->execute(
                        $section,
                        Str::limit((string) $lessonData['title'], 255, ''),
                        json_encode($blocks)
                    );
                }
            }

            return $module->fresh();
        });
    }
}
