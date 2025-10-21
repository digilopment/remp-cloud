<?php

namespace AiHeadlines;

use AiHeadlines\Admin\ACFIntegration;
use AiHeadlines\Admin\AdminSettings;
use AiHeadlines\Admin\AdminUI;
use AiHeadlines\Api\Routes;
use AiHeadlines\Cli\GenerateTitlesCommand;
use WP_CLI;

class Plugin
{
    public function init(): void
    {
        add_action('init', [$this, 'register_hooks']);
    }

    public function register_hooks(): void
    {
        (new AdminUI())->register();
        (new ACFIntegration())->register();
        (new Routes())->register();
        (new AdminSettings())->register();

        if (defined('WP_CLI') && WP_CLI) {
            /** @phpstan-ignore-next-line */
            WP_CLI::add_command('ai-headlines', new GenerateTitlesCommand());
        }
    }
}
