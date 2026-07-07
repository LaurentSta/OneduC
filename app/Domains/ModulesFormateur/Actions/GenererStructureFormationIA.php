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
- Propose 3 à 5 objectifs pédagogiques concis, formulés à la deuxième personne du pluriel (ex : "Vous saurez identifier...", "Vous serez capable de...").
- Propose entre 3 et 5 chapitres, chacun avec 2 à 4 leçons.
- Chaque leçon doit avoir un contenu rédigé et structuré (plusieurs blocs "text" si besoin), pas juste un titre.
- Utilise uniquement les balises HTML suivantes dans "html" : p, br, strong, em, u, ul, ol, li, h2, h3, h4, blockquote, a, code, pre.
- Structure la progression de façon pédagogique (du plus simple au plus avancé).
- N'ajoute aucun texte hors de l'objet JSON.
PROMPT;

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

    public function execute(?string $theme, ?UploadedFile $document, int $trainerId): Module
    {
        if ($this->limiteur->tropDeTentatives($trainerId)) {
            throw new RuntimeException('Limite de 3 générations IA par jour atteinte. Réessayez demain.');
        }

        if ($this->limiteurBudget->budgetDepasse($trainerId)) {
            throw new RuntimeException(
                'Vous avez atteint votre plafond mensuel de '.number_format($this->limiteurBudget->limiteMensuelle(), 0, ',', ' ').' tokens IA. Réessayez le mois prochain.'
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
        if ($sourceText !== '') {
            $userPromptParts[] = "Document source à structurer en formation :\n\n".$sourceText;
        }

        $raw = $this->mistral->chat(
            self::SYSTEM_PROMPT,
            implode("\n\n", $userPromptParts),
            timeoutSeconds: 260,
            maxTokens: 12000,
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

        return DB::transaction(function () use ($decoded, $title, $objectifs, $trainerId, $document) {
            $module = $this->creerModule->creerModuleVide([
                'module_title' => Str::limit($title, 255, ''),
                'description' => $decoded['description'] ?? null,
                'objectifs' => $objectifs !== [] ? $objectifs : null,
            ], $trainerId);

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
