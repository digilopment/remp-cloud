<?php
/**
 * Plugin Name: AI Headlines
 * Description: AI generovanie SEO nadpisov cez OpenAI pre Classic Editor.
 * Version: 1.0.0
 * Author: Tomáš Doubek
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';

use AiHeadlines\Plugin;
use AiHeadlines\Storage\TitlesRepository;

register_activation_hook(__FILE__, [TitlesRepository::class, 'createTable']);
register_uninstall_hook(__FILE__, [TitlesRepository::class, 'dropTable']);

add_action('plugins_loaded', function () {
    (new Plugin())->init();
});
