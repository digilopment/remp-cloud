<?php

namespace AiHeadlines\Admin;

class ACFIntegration
{
    public function register()
    {
        add_action('add_meta_boxes', [$this, 'add_ai_meta_box']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
    }

    public function add_ai_meta_box()
    {
        add_meta_box(
            'ai_headlines_box',
            'AI Headlines',
            [$this, 'render_meta_box'],
            'post',
            'side',
            'default',
            null
        );
    }

    public function render_meta_box($post)
    {
        if ($post->post_status !== 'draft') {
            echo '<p>AI tlačidlo sa zobrazí len pre koncepty.</p>';
            return;
        }

        $nonce = wp_create_nonce('ai_headlines');
        echo '<div style="display:flex; align-items:center; gap:10px;">';
        echo '<button id="ai-headlines" data-nonce="' . $nonce . '" class="button button-primary">Navrhnúť AI nadpisy</button>';
        echo '<label style="display:flex; align-items:center; gap:5px;">';
        echo '<input type="checkbox" id="ai-headlines-force" data-nonce="' . $nonce . '" name="ai_headlines_force" value="1">';
        echo 'Navrhnúť nové';
        echo '</label>';
        echo '</div>';
        echo '<div id="ai-headlines-output" style="margin-top:10px;"></div>';
    }

    public function enqueue_scripts($hook)
    {
        if (!in_array($hook, ['post.php', 'post-new.php'])) {
            return;
        }

        wp_enqueue_script(
            'ai-headlines-admin',
            plugin_dir_url(__DIR__ . '/../../') . 'assets/js/admin.js',
            ['jquery'],
            '1.0.0',
            true
        );

        wp_localize_script('ai-headlines-admin', 'AiHeadlines', [
            'ajax_url' => admin_url('admin-ajax.php'),
        ]);
    }
}
