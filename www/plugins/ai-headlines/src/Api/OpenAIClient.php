<?php

namespace AiHeadlines\Api;

use AiHeadlines\Utils\HeadlinePlaceHolder;
use AiHeadlines\Utils\PromptBuilder;

class OpenAIClient
{
    private string $api_key;

    private string $endpoint = 'https://api.openai.com/v1/chat/completions';

    private HeadlinePlaceHolder $placeholder;

    private PromptBuilder $promptBuilder;

    public function __construct(string $api_key)
    {
        $this->api_key = $api_key;
        $this->placeholder = new HeadlinePlaceHolder();
        $this->promptBuilder = new PromptBuilder();
    }

    /**
     * @param string $content
     * @return array<string, mixed> alebo fallback HeadlinePlaceHolder output
     */
    public function generateTitles(string $content): array
    {
        if (empty($this->api_key)) {
            return $this->placeholder->generate();
        }

        $prompt = $this->promptBuilder->build($content);

        $body_json = json_encode([
                'model' => 'gpt-4o-mini',
                'messages' => [['role' => 'user', 'content' => $prompt]],
            ]) ?: '{}';

        $response = wp_remote_post($this->endpoint, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type' => 'application/json',
            ],
            'body' => $body_json,
        ]);

        if (is_wp_error($response)) {
            return $this->placeholder->generate();
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (isset($body['error']['code']) && $body['error']['code'] === 'insufficient_quota') {
            return [
                'topic' => '',
                'titles' => [],
            ];
        }

        $contentResponse = $body['choices'][0]['message']['content'] ?? '';

        return $this->parseAiResponse($contentResponse);
    }

    /**
     * Vyčistí AI odpoveď (odstráni ```json bloky) a dekóduje JSON.
     *
     * @param string $responseContent
     * @return array<string, mixed>
     */
    private function parseAiResponse(string $responseContent): array
    {
        $clean = trim($responseContent);
        $clean = preg_replace('/^```[a-zA-Z]*\s*/', '', $clean);
        $clean = str_replace('```', '', (string)$clean);
        $clean = trim($clean);

        $parsed = json_decode($clean, true);

        if (json_last_error() === JSON_ERROR_NONE && isset($parsed['titles'])) {
            return $parsed;
        }

        return $this->placeholder->generate();
    }
}
