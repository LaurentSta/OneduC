<?php

namespace App\Domains\ModulesFormateur\Support;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class MistralClient
{
    public function chat(string $systemPrompt, string $userPrompt, int $timeoutSeconds = 90, ?int $maxTokens = null): string
    {
        $apiKey = config('services.mistral.api_key');
        if (! $apiKey) {
            throw new RuntimeException("La clé API Mistral n'est pas configurée.");
        }

        $response = Http::withToken($apiKey)
            ->timeout($timeoutSeconds)
            ->post('https://api.mistral.ai/v1/chat/completions', [
                'model' => config('services.mistral.model', 'mistral-large-latest'),
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

        $content = $response->json('choices.0.message.content');
        if (! is_string($content) || trim($content) === '') {
            throw new RuntimeException('La réponse de Mistral est vide ou invalide.');
        }

        return $content;
    }

    /**
     * @return array<string, bool>
     */
    public function moderate(string $text): array
    {
        $apiKey = config('services.mistral.api_key');
        if (! $apiKey) {
            throw new RuntimeException("La clé API Mistral n'est pas configurée.");
        }

        $response = Http::withToken($apiKey)
            ->timeout(30)
            ->post('https://api.mistral.ai/v1/moderations', [
                'model' => 'mistral-moderation-latest',
                'input' => [$text],
            ]);

        if ($response->failed()) {
            throw new RuntimeException("L'appel à l'API de modération Mistral a échoué (HTTP {$response->status()}).");
        }

        return $response->json('results.0.categories') ?? [];
    }
}
