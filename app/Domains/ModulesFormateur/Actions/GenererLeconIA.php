<?php

namespace App\Domains\ModulesFormateur\Actions;

use App\Domains\ModulesFormateur\Support\ExtracteurTexteDocument;
use App\Domains\ModulesFormateur\Support\GardeFouPromptIA;
use App\Domains\ModulesFormateur\Support\LimiteurBudgetTokensIA;
use App\Domains\ModulesFormateur\Support\LimiteurGenerationIA;
use App\Domains\ModulesFormateur\Support\MistralClient;
use App\Models\Module;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use RuntimeException;

class GenererLeconIA
{
    private const MAX_SOURCE_CHARS = 18000;

    private const SYSTEM_PROMPT = <<<'PROMPT'
Tu es un assistant pédagogique qui transforme un document source en une leçon structurée pour une formation en ligne.
Réponds UNIQUEMENT avec un objet JSON de la forme :
{"title": "Titre concis de la leçon", "blocks": [{"type": "text", "html": "<h2>...</h2><p>...</p>"}]}
Règles :
- Structure la leçon en 5 à 7 blocs "text" qui suivent toujours ce plan, dans cet ordre :
  1. Accroche : 2 à 3 phrases qui contextualisent l'intérêt du sujet pour le lecteur.
  2. Développement : 2 à 4 blocs, un par sous-partie du contenu source, chacun débutant par un titre <h3>, suivi d'explications et d'une liste à puces des points clés ou des étapes.
  3. Exemple concret : un bloc <h3>Exemple concret</h3> avec une mise en situation issue ou dérivée du document source.
  4. À retenir : un dernier bloc <h3>À retenir</h3> avec une liste de 3 à 5 points de synthèse, dont le point le plus important mis en avant dans un <blockquote>.
- Utilise uniquement les balises HTML suivantes dans "html" : p, br, strong, em, u, ul, ol, li, h2, h3, h4, blockquote, a, code, pre.
- Reformule et structure le contenu de façon pédagogique, sans inventer d'informations absentes du document.
- N'ajoute aucun texte hors de l'objet JSON.
PROMPT;

    public function __construct(
        private readonly ExtracteurTexteDocument $extracteur,
        private readonly MistralClient $mistral,
        private readonly GardeFouPromptIA $gardeFou,
        private readonly LimiteurGenerationIA $limiteur,
        private readonly LimiteurBudgetTokensIA $limiteurBudget,
        private readonly ImporterImagesDocument $importerImages,
    ) {}

    /**
     * @return array{title: string, blocks_json: string}
     */
    public function execute(UploadedFile $document, Module $module, int $trainerId): array
    {
        if ($this->limiteur->tropDeTentatives($trainerId)) {
            throw new RuntimeException('Limite de 3 générations IA par jour atteinte. Réessayez demain.');
        }

        if ($this->limiteurBudget->budgetDepasse($trainerId)) {
            throw new RuntimeException(
                'Vous avez atteint votre plafond mensuel de '.number_format($this->limiteurBudget->limiteMensuelle(), 0, ',', ' ').' tokens IA. Réessayez le mois prochain.'
            );
        }

        $sourceText = Str::limit($this->extracteur->extract($document), self::MAX_SOURCE_CHARS, '');

        $this->gardeFou->verifier($sourceText, $trainerId);
        $this->limiteur->enregistrerTentative($trainerId);

        $raw = $this->mistral->chat(
            self::SYSTEM_PROMPT,
            "Voici le contenu du document source à transformer en leçon :\n\n".$sourceText,
            timeoutSeconds: 150,
            trainerId: $trainerId,
        );

        $decoded = json_decode($raw, true);
        if (! is_array($decoded) || ! isset($decoded['blocks']) || ! is_array($decoded['blocks'])) {
            throw new RuntimeException("La réponse de l'IA n'a pas pu être interprétée.");
        }

        $title = trim((string) ($decoded['title'] ?? ''));
        if ($title === '') {
            $title = pathinfo($document->getClientOriginalName(), PATHINFO_FILENAME);
        }

        $blocks = array_merge($decoded['blocks'], $this->importerImages->importer($document, $module));

        return [
            'title' => Str::limit($title, 255, ''),
            'blocks_json' => json_encode($blocks),
        ];
    }
}
