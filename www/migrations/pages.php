<?php
require_once __DIR__ . '/autoload.php';

// --- Struktúra stránok: [názov => [obsah, podradené stránky]] ---
$pages = [
    "O nás" => [
        "content" => "Startitup je slovenský online magazín o technológiách, startupoch, biznise a lifestyle. Prinášame aktuálne správy, rozhovory a recenzie.",
        "children" => [
            "Redakcia" => "Náš tím tvoria novinári, technologickí nadšenci a odborníci na biznis spravodajstvo.",
            "Naša misia" => "Poskytovať rýchle, relevantné a zrozumiteľné informácie zo sveta technológií a startupov.",
        ]
    ],
    "Kontakt" => [
        "content" => "Kontaktujte nás pre spoluprácu, PR alebo otázky týkajúce sa obsahu.",
        "children" => [
            "Kontaktný formulár" => "Pre rýchle otázky využite náš online kontaktný formulár.",
            "Redakcia" => "info@startitup.sk"
        ]
    ],
    "Ochrana osobných údajov" => [
        "content" => "Vaše súkromie je pre nás dôležité. Dodržiavame GDPR a chránime vaše osobné údaje.",
        "children" => []
    ],
    "Obchodné podmienky" => [
        "content" => "Všeobecné podmienky používania nášho webu.",
        "children" => []
    ],
];

// --- WooCommerce systémové stránky ---
$woocommerce_pages = [
    'Obchod' => ['content' => '[woocommerce_shop]', 'slug' => 'shop'],
    'Košík' => ['content' => '[woocommerce_cart]', 'slug' => 'cart'],
    'Pokladňa' => ['content' => '[woocommerce_checkout]', 'slug' => 'checkout'],
    'Môj účet' => ['content' => '[woocommerce_my_account]', 'slug' => 'my-account'],
];

// --- Spoj všetky stránky ---
$pages = array_merge($pages, $woocommerce_pages);

// --- Funkcia na priradenie náhodného obrázku ako featured image ---
function attach_random_image($post_id) {
    $image_url = "https://dummyimage.com/1200x600/eeeeee/111111.jpg&text=" . rand(1, 9999);
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/media.php');
    require_once(ABSPATH . 'wp-admin/includes/image.php');

    $media = media_sideload_image($image_url, $post_id, null, 'id');
    if (!is_wp_error($media)) {
        set_post_thumbnail($post_id, $media);
    }
}

// --- Vymaz všetky existujúce stránky ---
$all_pages = get_posts(['post_type' => 'page', 'numberposts' => -1]);
foreach ($all_pages as $p) {
    wp_delete_post($p->ID, true);
}

// --- Vytvorenie stránok ---
foreach ($pages as $title => $data) {
    $slug = $data['slug'] ?? sanitize_title($title);

    $parent_id = wp_insert_post([
        'post_title'   => $title,
        'post_name'    => $slug,
        'post_content' => $data['content'],
        'post_status'  => 'publish',
        'post_type'    => 'page',
        'post_author'  => 1,
    ]);

    if ($parent_id) {
        attach_random_image($parent_id);
        if (!empty($data['children'])) {
            foreach ($data['children'] as $child_title => $child_content) {
                $child_id = wp_insert_post([
                    'post_title'   => $child_title,
                    'post_content' => $child_content,
                    'post_status'  => 'publish',
                    'post_type'    => 'page',
                    'post_author'  => 1,
                    'post_parent'  => $parent_id
                ]);
                if ($child_id) {
                    attach_random_image($child_id);
                }
            }
        }
    }
}

// --- WooCommerce integrácia: nastavenie stránok v option tabuľke ---
update_option('woocommerce_shop_page_id', get_page_by_path('shop')->ID);
update_option('woocommerce_cart_page_id', get_page_by_path('cart')->ID);
update_option('woocommerce_checkout_page_id', get_page_by_path('checkout')->ID);
update_option('woocommerce_myaccount_page_id', get_page_by_path('my-account')->ID);

echo "Migrácia hotová. Prezentačné + WooCommerce stránky úspešne vytvorené.\n";
