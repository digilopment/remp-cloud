<?php

namespace AiHeadlines\Utils;

class HeadlinePlaceHolder
{
    /**
     * @return array{topic: string, titles: string[]}
     */
    public function generate(): array
    {
        return [
            'topic' => 'Main Topic Title',
            'titles' => [
                'Demo Title ' . rand(10, 100),
                'Demo Title ' . rand(10, 100),
                'Demo Title ' . rand(10, 100),
            ],
        ];
    }
}
