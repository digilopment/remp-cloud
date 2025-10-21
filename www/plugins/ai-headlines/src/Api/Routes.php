<?php

namespace AiHeadlines\Api;

use AiHeadlines\Api\OpenAIClient;
use AiHeadlines\Storage\TitlesRepository;

class Routes
{
    private TitlesRepository $titlesRepository;

    private OpenAIClient $client;

    public function __construct()
    {
        $this->titlesRepository = new TitlesRepository();
        $this->client = new OpenAIClient(get_option('ai_openai_api_key'));
    }

    public function register()
    {
        add_action('wp_ajax_ai_headlines', [$this, 'getHeadlines']);
        add_action('wp_ajax_ai_set_title', [$this, 'setHeadline']);
    }

    public function setHeadline()
    {
        check_ajax_referer('ai_headlines', 'nonce');

        $post_id = intval($_POST['post_id']);
        $title = sanitize_text_field($_POST['title']);

        if (current_user_can('edit_post', $post_id)) {
            wp_update_post([
                'ID' => $post_id,
                'post_title' => $title,
            ]);
            wp_send_json_success();
        } else {
            wp_send_json_error('Nedostatočné práva.');
        }
    }

    public function getHeadlines()
    {
        check_ajax_referer('ai_headlines', 'nonce');

        $post_id = intval($_POST['post_id']);
        if (!$post_id) {
            wp_send_json_error('Neplatné ID článku.');
        }

        $force = isset($_POST['force']) ? boolval($_POST['force']) : false;
        $existing = $this->titlesRepository->getByPostId($post_id);

        if ($existing && !$force) {
            wp_send_json_success([
                'topic' => $existing->topic,
                'titles' => json_decode($existing->titles),
            ]);
        }

        $content = get_post_field('post_content', $post_id);
        $response = $this->client->generateTitles($content);

        if (isset($response['titles']) && isset($response['topic']) && count($response['titles']) > 0) {
            $this->titlesRepository->deleteByPostId($post_id);
            $this->titlesRepository->save($post_id, $response);
        }

        wp_send_json_success($response);
    }
}
