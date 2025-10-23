<?php

namespace AiHeadlines\Utils;

class PromptBuilder
{
    private HeadlinePlaceHolder $placeholder;

    public function __construct()
    {
        $this->placeholder = new HeadlinePlaceHolder();
    }

    public function build(string $content): string
    {

        $jsonTemplate = $this->placeholder->generate();
        $prompt = "Analyzuj nasledujúci článok a odpovedz vo formáte JSON presne podľa ukážky nižšie. Identifikuj hlavnú tému článku a navrhni 3–5 SEO-priateľských titulkov.\n";
        $prompt .= json_encode($jsonTemplate, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $prompt .= "\n\nObsah článku:\n\n" . strip_tags($content);
        $body_json = json_encode([
                'model' => get_option('ai_model'),
                'messages' => [
                    ['role' => 'system', 'content' => 'Tvoja jediná úloha je vrátiť validný JSON podľa špecifikovanej štruktúry.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'response_format' => ['type' => 'json_object'],
                'temperature' => 0.3,
            ]) ?: '{}';

        return $body_json;
    }
}
