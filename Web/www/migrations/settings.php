<?php
require_once __DIR__ . '/autoload.php';

echo "=== Migrácia nastavení stránky ===\n";

/**
 * ================================
 *  ZÁKLADNÉ NASTAVENIA WEBU
 * ================================
 */
$settings = [
    'blogname'            => getenv('TITLE'),
    'blogdescription'     => getenv('DESCRIPTION'),
    'siteurl'             => getenv('DOMAIN'),
    'home'                => getenv('DOMAIN'),
    'admin_email'         => getenv('ADMIN_EMAIL'),
    'timezone_string'     => getenv('TIMEZONE_STRING'),
    'date_format'         => getenv('DATE_FORMAT'),
    'time_format'         => getenv('TIME_FORMAT'),
    'start_of_week'       => getenv('START_OF_WEEK'),
    'WPLANG'              => getenv('LOCALE'),
    'default_category'    => getenv('DEFAULT_CATEGORY'),
    'permalink_structure' => getenv('PERMALINK_STRUCTURE'),
    'mailserver_url'      => getenv('MAILSERVER_URL'),
    'mailserver_login'    => getenv('MAILSERVER_LOGIN'),
    'mailserver_pass'     => getenv('MAILSERVER_PASS'),
    'mailserver_port'     => getenv('MAILSERVER_PORT'),
    'ping_sites'          => '',
    'logo_url'            => getenv('LOGO_URL') ?: 'https://dummyimage.com/400x100/ff6600/ffffff.png&text=' . urlencode(getenv('TITLE')),
    'favicon_url'         => getenv('FAVICON_URL') ?: 'https://github.githubassets.com/favicons/favicon.png',
];

foreach ($settings as $key => $value) {
    if (!in_array($key, ['logo_url', 'favicon_url'], true)) {
        update_option($key, $value);
        echo "→ Nastavené: {$key} = {$value}\n";
    }
}

require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

function upload_image_and_get_attachment_id(string $url, string $title = '')
{
    $allow_ico = static function ($mimes) {
        $mimes['ico'] = 'image/x-icon';
        return $mimes;
    };
    add_filter('upload_mimes', $allow_ico);

    $id = media_sideload_image($url, 0, $title, 'id');

    remove_filter('upload_mimes', $allow_ico);

    if (is_wp_error($id)) {
        echo "✗ Chyba pri uploade „{$title}“: {$id->get_error_message()}\n";
        return false;
    }

    return (int) $id;
}

/**
 * ================================
 *  LOGO A FAVICON
 * ================================
 */
$logo_id = upload_image_and_get_attachment_id($settings['logo_url'], getenv('TITLE') . ' Logo');
if ($logo_id) {
    update_option('custom_logo', $logo_id);
    echo "→ Logo nahraté (ID: {$logo_id})\n";
}

$favicon_id = upload_image_and_get_attachment_id($settings['favicon_url'], 'Favicon');
if ($favicon_id) {
    update_option('site_icon', $favicon_id);
    echo "→ Favicon nahratý (ID: {$favicon_id})\n";
}


update_option('posts_per_page', 12);
update_option('default_comment_status', 'closed');
update_option('default_ping_status', 'closed');

add_filter('locale', fn() => getenv('LOCALE'));


global $wp_rewrite;
$wp_rewrite->set_permalink_structure($settings['permalink_structure']);
$wp_rewrite->flush_rules(false);

echo "=== Migrácia hotová. Web inicializovaný pre: " . getenv('TITLE') . " ===\n";
