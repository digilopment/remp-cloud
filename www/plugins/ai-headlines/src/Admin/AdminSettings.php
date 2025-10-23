<?php

namespace AiHeadlines\Admin;

class AdminSettings
{

    private const SETTINGS = [
        'ai_openai_api_key' => [
            'label' => 'OpenAI API Key',
            'default' => '',
            'type' => 'text',
        ],
        'ai_model' => [
            'label' => 'OpenAI API Model',
            'default' => 'gpt-5',
            'type' => 'select',
            'options' => [
                'gpt-4' => 'GPT-4',
                'gpt-4o' => 'GPT-4o',
                'gpt-4o-mini' => 'GPT-4o-Mini',
                'gpt-5' => 'GPT-5',
            ],
        ],
    ];

    public function register(): void
    {
        add_action('admin_init', [$this, 'register_settings']);
    }

    public function register_settings(): void
    {
        foreach (self::SETTINGS as $option => $config) {
            register_setting('general', $option, [
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default' => $config['default'],
            ]);

            add_settings_field(
                $option,
                $config['label'],
                fn() => $this->render_field($option, $config),
                'general',
                'default'
            );
        }
    }

    /**
     * @param string $option
     * @param array{label: string, default: string, type: string, options?: array<string, string>} $config
     */
    private function render_field(string $option, array $config): void
    {
        $value = esc_attr(get_option($option, $config['default']));

        if ($config['type'] === 'select' && isset($config['options'])) {
            echo '<select id="' . esc_attr($option) . '" name="' . esc_attr($option) . '" class="regular-text">';
            foreach ($config['options'] as $key => $label) {
                $selected = selected($value, $key, false);
                echo '<option value="' . esc_attr($key) . '" ' . $selected . '>' . esc_html($label) . '</option>';
            }
            echo '</select>';
        } else {
            printf(
                '<input type="text" id="%1$s" name="%1$s" value="%2$s" class="regular-text">',
                esc_attr($option),
                $value
            );
        }
    }
}
