<?php

namespace App\Domains\ModulesFormateur\Support;

use App\Models\ConsommationIA;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class MistralClient
{
    public function chat(string $systemPrompt, string $userPrompt, int $timeoutSeconds = 90, ?int $maxTokens = null, ?int $trainerId = null): string
    {
        $apiKey = config('services.mistral.api_key');
        if (! $apiKey) {
            throw new RuntimeException("La clé API Mistral n'est pas configurée.");
        }

        $model = config('services.mistral.model', 'mistral-large-latest');

        $response = Http::withToken($apiKey)
            ->timeout($timeoutSeconds)
            ->post('https://api.mistral.ai/v1/chat/completions', [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userPrompt],
                ],
                'temperature' => 0.3,
                'response_format' => ['type' => 'json_object'],
                ...($maxTokens ? ['max_tokens' => $maxTokens] : []),
            ]);

        if ($response->failed()) {
            throw new RuntimeException("L'appel à l'API Mistral a échoué (HTTP {$response->status()}).");
        }

        $this->logUsage('chat', $model, $trainerId, $response->json('usage', []));

        $content = $response->json('choices.0.message.content');
        if (! is_string($content) || trim($content) === '') {
            throw new RuntimeException('La réponse de Mistral est vide ou invalide.');
        }

        return $content;
    }

    /**
     * @return array<string, bool>
     */
    public function moderate(string $text, ?int $trainerId = null): array
    {
        $apiKey = config('services.mistral.api_key');
        if (! $apiKey) {
            throw new RuntimeException("La clé API Mistral n'est pas configurée.");
        }

        $model = 'mistral-moderation-latest';

        $response = Http::withToken($apiKey)
            ->timeout(30)
            ->post('https://api.mistral.ai/v1/moderations', [
                'model' => $model,
                'input' => [$text],
            ]);

        if ($response->failed()) {
            throw new RuntimeException("L'appel à l'API de modération Mistral a échoué (HTTP {$response->status()}).");
        }

        $this->logUsage('moderate', $model, $trainerId, $response->json('usage', []));

        return $response->json('results.0.categories') ?? [];
    }

    /**
     * @param  array<string, mixed>  $usage
     */
    private function logUsage(string $type, string $model, ?int $trainerId, array $usage): void
    {
        Log::channel('mistral')->info($type, [
            'trainer_id' => $trainerId,
            'model' => $model,
            'prompt_tokens' => $usage['prompt_tokens'] ?? null,
            'completion_tokens' => $usage['completion_tokens'] ?? null,
            'total_tokens' => $usage['total_tokens'] ?? null,
        ]);

        ConsommationIA::create([
            'formateur_id' => $trainerId,
            'type' => $type,
            'model' => $model,
            'prompt_tokens' => $usage['prompt_tokens'] ?? null,
            'completion_tokens' => $usage['completion_tokens'] ?? null,
            'total_tokens' => $usage['total_tokens'] ?? null,
        ]);
    }
}
