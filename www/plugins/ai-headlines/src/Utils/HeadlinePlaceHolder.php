<?php

namespace AiHeadlines\Utils;

class HeadlinePlaceHolder
{
    public function generate(): array
    {
        return [
            'topic' => 'Sample Topic',
            'titles' => [
                'Demo Title ' . rand(10, 100),
                'Demo Title ' . rand(10, 100),
                'Demo Title ' . rand(10, 100),
            ],
        ];
    }
}
