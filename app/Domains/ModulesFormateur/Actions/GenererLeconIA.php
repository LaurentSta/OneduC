<?php

namespace App\Domains\ModulesFormateur\Actions;

use App\Domains\ModulesFormateur\Support\ExtracteurTexteDocument;
use App\Domains\ModulesFormateur\Support\GardeFouPromptIA;
use App\Domains\ModulesFormateur\Support\LimiteurGenerationIA;
use App\Domains\ModulesFormateur\Support\MistralClient;
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
- Découpe le contenu en plusieurs blocs "text" cohérents (un bloc par grande partie du document).
- Utilise uniquement les balises HTML suivantes dans "html" : p, br, strong, em, u, ul, ol, li, h2, h3, h4, blockquote, a, code, pre.
- Reformule et structure le contenu de façon pédagogique, sans inventer d'informations absentes du document.
- N'ajoute aucun texte hors de l'objet JSON.
PROMPT;

    public function __construct(
        private readonly ExtracteurTexteDocument $extracteur,
        private readonly MistralClient $mistral,
        private readonly GardeFouPromptIA $gardeFou,
        private readonly LimiteurGenerationIA $limiteur,
    ) {}

    /**
     * @return array{title: string, blocks_json: string}
     */
    public function execute(UploadedFile $document, int $trainerId): array
    {
        if ($this->limiteur->tropDeTentatives($trainerId)) {
            throw new RuntimeException('Limite de 3 générations IA par jour atteinte. Réessayez demain.');
        }

        $sourceText = Str::limit($this->extracteur->extract($document), self::MAX_SOURCE_CHARS, '');

        $this->gardeFou->verifier($sourceText);
        $this->limiteur->enregistrerTentative($trainerId);

        $raw = $this->mistral->chat(
            self::SYSTEM_PROMPT,
            "Voici le contenu du document source à transformer en leçon :\n\n".$sourceText
        );

        $decoded = json_decode($raw, true);
        if (! is_array($decoded) || ! isset($decoded['blocks']) || ! is_array($decoded['blocks'])) {
            throw new RuntimeException("La réponse de l'IA n'a pas pu être interprétée.");
        }

        $title = trim((string) ($decoded['title'] ?? ''));
        if ($title === '') {
            $title = pathinfo($document->getClientOriginalName(), PATHINFO_FILENAME);
        }

        return [
            'title' => Str::limit($title, 255, ''),
            'blocks_json' => json_encode($decoded['blocks']),
        ];
    }
}
