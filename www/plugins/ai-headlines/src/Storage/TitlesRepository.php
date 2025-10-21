<?php

namespace AiHeadlines\Storage;

use wpdb;

class TitlesRepository
{

    private const TABLE_NAME = 'ai_headlines_suggestions';

    private wpdb $wpdb;

    public function __construct()
    {
        global $wpdb;
        $this->wpdb = $wpdb;
    }

    private function getTableName(): string
    {
        return $this->wpdb->prefix . self::TABLE_NAME;
    }

    public static function createTable(): void
    {
        global $wpdb;
        $table = $wpdb->prefix . self::TABLE_NAME;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            post_id BIGINT UNSIGNED NOT NULL,
            topic TEXT,
            titles LONGTEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    public static function dropTable(): void
    {
        global $wpdb;
        $table = $wpdb->prefix . self::TABLE_NAME;
        $wpdb->query("DROP TABLE IF EXISTS {$table}");
    }

    public function getByPostId(int $postId): ?object
    {
        $table = $this->getTableName();
        return $this->wpdb->get_row($this->wpdb->prepare("SELECT * FROM {$table} WHERE post_id = %d LIMIT 1", $postId));
    }

    public function save(int $postId, array $data): void
    {
        $this->wpdb->insert($this->getTableName(), [
            'post_id' => $postId,
            'topic' => sanitize_text_field($data['topic'] ?? ''),
            'titles' => wp_json_encode($data['titles'] ?? []),
        ]);
    }

    public function deleteByPostId(int $postId): void
    {
        $this->wpdb->delete($this->getTableName(), ['post_id' => $postId], ['%d']);
    }
}
