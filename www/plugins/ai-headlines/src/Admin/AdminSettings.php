<?php

namespace AiHeadlines\Admin;

class AdminSettings
{
    const OPTION_NAME = 'ai_openai_api_key';

    public function register(): void
    {
        add_action('admin_init', [$this, 'register_setting']);
    }

    public function register_setting(): void
    {
        register_setting('general', self::OPTION_NAME, [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '',
        ]);

        add_settings_field(
            self::OPTION_NAME,
            'OpenAI API Key',
            [$this, 'render_field'],
            'general',
            'default'
        );
    }

    public function render_field(): void
    {
        $value = get_option(self::OPTION_NAME, '');
        echo '<input type="text" id="' . self::OPTION_NAME . '" name="' . self::OPTION_NAME . '" value="' . esc_attr($value) . '" class="regular-text">';
    }
}
