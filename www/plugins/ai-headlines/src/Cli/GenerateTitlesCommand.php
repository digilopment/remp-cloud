<?php

namespace AiHeadlines\Cli;

use AiHeadlines\Api\OpenAIClient;
use AiHeadlines\Storage\TitlesRepository;
use WP_CLI;
use WP_CLI_Command;

class GenerateTitlesCommand extends WP_CLI_Command
{
    protected TitlesRepository $titlesRepo;

    protected OpenAIClient $client;

    public function __construct()
    {
        $api_key = get_option('ai_openai_api_key', '');
        $this->client = new OpenAIClient((string) $api_key);
        $this->titlesRepo = new TitlesRepository();
    }

    /**
     * Hromadné generovanie AI nadpisov.
     *
     * ## OPTIONS
     *
     * <target>
     * : Buď "all", category ID, alebo zoznam post IDs oddelený čiarkou (napr. 12,34,56)
     *
     * [--renew]
     * : Ak je prítomný tento parameter, existujúce záznamy sa vymažú a nanovo vygenerujú.
     *
     * ## EXAMPLES
     *
     *     wp ai-headlines generate all #all-articles
     *     wp ai-headlines generate 5  #category
     *     wp ai-headlines generate 5, #singl-article
     *     wp ai-headlines generate 12,34,56 #articles
     *     wp ai-headlines generate all --renew
     *
     * @when after_wp_load
     * @param array<int, string> $args
     * @param array<string, mixed> $assoc_args
     * @return void
     */
    public function generate(array $args, array $assoc_args): void
    {
        list($target) = $args;
        $renew = isset($assoc_args['renew']) ? true : false;

        if ($target === 'all') {
            $posts = get_posts([
                'post_type' => 'post',
                'numberposts' => -1,
                'post_status' => ['publish', 'draft'],
            ]);
        } elseif (is_numeric($target)) {
            $posts = get_posts([
                'category' => intval($target),
                'post_type' => 'post',
                'numberposts' => -1,
                'post_status' => ['publish', 'draft'],
            ]);
        } else {
            $ids = array_map('intval', explode(',', $target));
            $posts = get_posts([
                'post_type' => 'post',
                'post__in' => $ids,
                'numberposts' => -1,
                'post_status' => ['publish', 'draft'],
            ]);
        }

        if (empty($posts)) {
            WP_CLI::warning('No posts found for the given target.');
            return;
        }

        foreach ($posts as $post) {
            $post_id = $post->ID;
            $existing = $this->titlesRepo->getByPostId($post_id);
            
            if ($existing && !$renew) {
                WP_CLI::log("Post ID {$post_id} already has titles. Skipping.");
                continue;
            }

            if ($existing && $renew) {
                $this->titlesRepo->deleteByPostId($post_id);
                
                WP_CLI::log("Post ID {$post_id}: existing titles deleted (renew mode).");
            }

            $post_content = $post->post_content;
            $titles = $this->client->generateTitles($post_content);

            $this->titlesRepo->save($post_id, $titles);

            WP_CLI::success("Post ID {$post_id} processed and titles saved.");
        }

        
        WP_CLI::success('All selected posts processed successfully.');
    }
}
