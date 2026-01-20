<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    protected string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/';

    public function generateMemoryFromPrompt(string $prompt): array
    {
        $apiKey = config('services.gemini.api_key');
        $model = config('services.gemini.model', 'gemini-2.5-flash');

        if (! $apiKey) {
            throw new \Exception('Gemini API key is not configured.');
        }

        try {
            $url = "{$this->baseUrl}{$model}:generateContent?key={$apiKey}";

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->timeout(600)->post($url, [
                'contents' => [
                    [
                        'parts' => [
                            [
                                'text' => $this->buildSystemPrompt($prompt),
                            ],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'topK' => 40,
                    'topP' => 0.95,
                    'maxOutputTokens' => 8192,
                    'responseMimeType' => 'application/json',
                ],
            ]);

            if ($response->failed()) {
                Log::error('Gemini API Error', $response->json());
                throw new \Exception('Failed to communicate with Gemini API: ' . $response->body());
            }

            $data = $response->json();
            $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '{}';

            return json_decode($text, true) ?? [];

        } catch (\Exception $e) {
            Log::error('Gemini Generation Exception', ['message' => $e->getMessage()]);
            throw $e;
        }
    }

    protected function buildSystemPrompt(string $userPrompt): string
    {
        return <<<EOT
You are an intelligent assistant for a Memory Management System.
Your task is to generate a structured JSON object representing a "Memory" based on the user's prompt.

User Prompt: "{$userPrompt}"

The output MUST be a valid JSON object with the following structure:
{
    "title": "A concise title for the memory",
    "slug": "url-friendly-slug",
    "organization": "Default Organization (or inferred)",
    "repository": "Default Repository (or inferred)",
    "scope_type": "One of: 'system', 'organization', 'repository', 'user'",
    "memory_type": "One of: 'business_rule', 'decision_log', 'preference', 'system_constraint', 'documentation_ref', 'tech_stack'",
    "current_content": "The full markdown content of the memory. Be detailed.",
    "status": "One of: 'draft', 'verified', 'locked', 'deprecated', 'active' (Default: active)",
    "importance": 3 (integer 1-10),
    "metadata": {
        "key_from_content": "value_from_content",
        "another_key": "another_value"
    }
}

Ensure the JSON is valid and the content is helpful and formatted in Markdown.
For 'scope_type' and 'memory_type', ONLY use the allowed values listed above.
For 'metadata': Extract relevant key-value pairs derived directly from the generated content (e.g., specific versions, main concepts, configuration keys, or parameters). Do not nest objects.
EOT;
    }
    public function enhanceMemory(array $currentData, string $instruction): array
    {
        $apiKey = config('services.gemini.api_key');
        $model = config('services.gemini.model', 'gemini-1.5-flash');

        if (! $apiKey) {
            throw new \Exception('Gemini API key is not configured.');
        }

        try {
            $url = "{$this->baseUrl}{$model}:generateContent?key={$apiKey}";

            // Filter out large/unneeded fields to save tokens if necessary, but keep context
            $context = json_encode($currentData);

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->timeout(600)->post($url, [
                'contents' => [
                    [
                        'parts' => [
                            [
                                'text' => $this->buildEnhancePrompt($context, $instruction),
                            ],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'topK' => 40,
                    'topP' => 0.95,
                    'maxOutputTokens' => 8192,
                    'responseMimeType' => 'application/json',
                ],
            ]);

            if ($response->failed()) {
                Log::error('Gemini API Error', $response->json());
                throw new \Exception('Failed to communicate with Gemini API: ' . $response->body());
            }

            $data = $response->json();
            $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '{}';

            return json_decode($text, true) ?? [];

        } catch (\Exception $e) {
            Log::error('Gemini Enhancement Exception', ['message' => $e->getMessage()]);
            throw $e;
        }
    }

    protected function buildEnhancePrompt(string $context, string $instruction): string
    {
        return <<<EOT
You are an intelligent assistant for a Memory Management System.
Your task is to ENHANCE an existing "Memory" object based on the user's instruction.

Current Memory JSON:
{$context}

User Instruction for Enhancement:
"{$instruction}"

(If the instruction is empty, improve the clarity, detail, and structure of the content, and ensure metadata is complete).

The output MUST be a valid JSON object with the same structure as the input, but with improved content/metadata.
Structure constraints:
- "scope_type": One of: 'system', 'organization', 'repository', 'user'
- "memory_type": One of: 'business_rule', 'decision_log', 'preference', 'system_constraint', 'documentation_ref', 'tech_stack'
- "status": One of: 'draft', 'verified', 'locked', 'deprecated', 'active'
- "metadata": Key-value pairs extracted from content.

Ensure the "current_content" is improved according to the instruction (e.g., fixed grammar, added examples, expanded details) and formatted in Markdown.
EOT;
    }
}
