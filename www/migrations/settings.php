<?php
require_once __DIR__ . '/autoload.php';

// --- Reset a migrácia nastavení stránky ---
echo "=== Migrácia nastavení stránky ===\n";

// --- Konfigurácia ---
$settings = [
    'blogname'           => getenv('TITLE'),
    'blogdescription'    => getenv('DESCRIPTION'),
    'siteurl'            => getenv('DOMAIN'),
    'home'               => getenv('DOMAIN'),
    'admin_email'        => getenv('ADMIN_EMAIL'),
    'timezone_string'    => getenv('TIMEZONE_STRING'),
    'date_format'        => getenv('DATE_FORMAT'),
    'time_format'        => getenv('TIME_FORMAT'),
    'start_of_week'      => getenv('START_OF_WEEK'),
    'WPLANG'             => getenv('LOCALE'),
    'default_category'   => getenv('DEFAULT_CATEGORY'),
    'permalink_structure'=> getenv('PERMALINK_STRUCTURE'),
    'mailserver_url' => getenv('MAILSERVER_URL'),
    'mailserver_login' => getenv('MAILSERVER_LOGIN'),
    'mailserver_pass' => getenv('MAILSERVER_PASS'),
    'mailserver_port' => getenv('MAILSERVER_PORT'),
    'ping_sites' => '',
];

// --- Uloženie základných WP options ---
foreach ($settings as $key => $value) {
    update_option($key, $value);
    echo "→ Nastavené: $key = $value\n";
}

// --- Logo a favicon ---
require_once(ABSPATH . 'wp-admin/includes/file.php');
require_once(ABSPATH . 'wp-admin/includes/media.php');
require_once(ABSPATH . 'wp-admin/includes/image.php');

function upload_media($url, $desc = '') {
    $tmp = download_url($url);
    if (is_wp_error($tmp)) return null;

    $file_array = [
        'name' => basename($url),
        'tmp_name' => $tmp,
    ];

    $id = media_handle_sideload($file_array, 0, $desc);
    if (is_wp_error($id)) {
        @unlink($file_array['tmp_name']);
        return null;
    }
    return $id;
}

// --- Logo ---
$logo_url = 'https://dummyimage.com/400x100/ff6600/ffffff.png&text=' . getenv('TITLE');
$logo_id = upload_media($logo_url, 'Site Logo');
if ($logo_id) {
    set_theme_mod('custom_logo', $logo_id);
    echo "→ Logo nahraté (ID: $logo_id)\n";
}

// --- Favicon ---
$favicon_url = 'https://dummyimage.com/64x64/ff6600/ffffff.png&text=S';
$favicon_id = upload_media($favicon_url, 'Favicon');
if ($favicon_id) {
    update_option('site_icon', $favicon_id);
    echo "→ Favicon nahratý (ID: $favicon_id)\n";
}

// --- Reading / Writing defaults ---
//update_option('show_on_front', 'posts');
update_option('posts_per_page', 12);
update_option('default_comment_status', 'closed');
update_option('default_ping_status', 'closed');
add_filter('locale', function($locale){
    return getenv('LOCALE');
});
// --- Nastavenie rewrite rules pre blog štruktúru ---
global $wp_rewrite;
$wp_rewrite->set_permalink_structure($settings['permalink_structure']);
//$wp_rewrite->category_base = 'blog';
$wp_rewrite->flush_rules(false);

echo "=== Migrácia hotová. Nastavenia webu boli inicializované pre ". getenv('TITLE') ." ===\n";
