<?php

namespace App\Services\Llm;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class GeminiLlmClient implements LlmClient
{
    public function __construct(
        private readonly string $apiKey,
        private readonly string $model,
    ) {}

    public function rewrite(string $source, array $protectedTokens): string
    {
        $tokenList = $protectedTokens === [] ? 'None detected.' : implode(', ', $protectedTokens);
        $prompt = <<<PROMPT
Rewrite the following customer support reply to sound clear, warm, and concise. Keep every factual claim unchanged. Return only the rewritten reply, with no quotation marks or explanation.

Protected tokens must appear exactly as provided, with the same spelling and punctuation: {$tokenList}

Reply:
{$source}
PROMPT;

        $response = Http::timeout(12)
            ->acceptJson()
            ->post('https://generativelanguage.googleapis.com/v1beta/models/'.rawurlencode($this->model).':generateContent?key='.rawurlencode($this->apiKey), [
                'contents' => [[
                    'parts' => [['text' => $prompt]],
                ]],
                'generationConfig' => [
                    'temperature' => 0.35,
                    'maxOutputTokens' => 1024,
                ],
            ])
            ->throw()
            ->json('candidates.0.content.parts.0.text');

        if (! is_string($response) || trim($response) === '') {
            throw new RuntimeException('Gemini returned an empty paraphrase.');
        }

        return trim($response);
    }

    public function name(): string
    {
        return 'Gemini';
    }
}
