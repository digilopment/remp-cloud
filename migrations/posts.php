<?php
require_once __DIR__ . '/autoload.php';

// --- Konfigurácia ---
$post_count = 100;
$category_names = [
    "Technológie", "Lifestyle", "Cestovanie", "Zdravie", "Šport", "Biznis",
    "Veda", "Kultúra", "Zábava", "Gastro", "Móda", "Automoto", "Gaming",
    "Vzdelávanie", "Umenie"
];
$tag_pool = [
    "inovácie","2025","tipy","trendy","novinky","recenzia","návod",
    "slovensko","svet","inšpirácia","technológia","úspech","zdravie",
    "recept","štýl","motivácia","cestovanie","fit","ekológia","energie"
];

// --- Vymaž existujúce články, kategórie, tagy a stránku blog ---
$all_posts = get_posts(['post_type' => 'post', 'numberposts' => -1]);
foreach ($all_posts as $p) wp_delete_post($p->ID, true);

$all_cats = get_terms(['taxonomy' => 'category', 'hide_empty' => false]);
foreach ($all_cats as $c) wp_delete_term($c->term_id, 'category');

$all_tags = get_terms(['taxonomy' => 'post_tag', 'hide_empty' => false]);
foreach ($all_tags as $t) wp_delete_term($t->term_id, 'post_tag');

$blog_page = get_page_by_path('blog');
if($blog_page) wp_delete_post($blog_page->ID, true);

// --- Vytvor kategórie ---
$category_ids = [];
foreach ($category_names as $cat_name) {
    $term = wp_insert_term($cat_name, 'category', [
        'slug' => sanitize_title($cat_name)
    ]);
    if (!is_wp_error($term)) $category_ids[] = $term['term_id'];
}

// --- Funkcie na generovanie obsahu ---
function fake_sentence($words = 8){
    $wordlist = ["moderný","trend","výskum","novinka","produkt","ľudia","technológia","zdravie","riešenie","digitalizácia","marketing","cestovanie","projekt","úspech","vývoj","život","systém","aplikácia","zmena","trh","energia","tím","budúcnosť","mobil","internet","štýl","komfort","inovácia"];
    $sentence = [];
    for($i=0;$i<$words;$i++) $sentence[] = $wordlist[array_rand($wordlist)];
    return ucfirst(implode(" ",$sentence)) . ".";
}

function fake_paragraph($sentences = 4){
    $out = '';
    for($i=0;$i<$sentences;$i++) $out .= fake_sentence(rand(6,12)) . ' ';
    return "<p>$out</p>";
}

function fake_article($paragraphs = 4){
    $content = '';
    for($i=0;$i<$paragraphs;$i++) $content .= fake_paragraph(rand(3,6));
    return $content;
}

function attach_random_image($post_id){
    $image_url = "https://dummyimage.com/1200x600/cccccc/111111.jpg&text=Post+".rand(1,10000);
    require_once(ABSPATH.'wp-admin/includes/file.php');
    require_once(ABSPATH.'wp-admin/includes/media.php');
    require_once(ABSPATH.'wp-admin/includes/image.php');
    $media = media_sideload_image($image_url,$post_id,null,'id');
    if(!is_wp_error($media)) set_post_thumbnail($post_id,$media);
}

// --- Generuj články ---
for($i=1;$i<=$post_count;$i++){
    $title = "Článok č. $i";
    $content = fake_article(rand(3,6));

    $post_id = wp_insert_post([
        'post_title' => $title,
        'post_content' => $content,
        'post_status' => 'publish',
        'post_type' => 'post',
        'post_author' => 1,
    ]);

    if($post_id){
        // náhodná kategória
        $rand_cat = $category_ids[array_rand($category_ids)];
        wp_set_post_terms($post_id, [$rand_cat], 'category');

        // náhodné tagy
        $rand_tags = (array)array_rand(array_flip($tag_pool), rand(2,5));
        wp_set_post_terms($post_id, $rand_tags, 'post_tag');

        // featured image
        attach_random_image($post_id);
    }
}

// --- Vytvor stránku Blog ---
$blog_page_id = wp_insert_post([
    'post_title' => 'Blog',
    'post_name' => 'blog',
    'post_status' => 'publish',
    'post_type' => 'page',
    'post_content' => 'Všetky články nájdete nižšie.',
]);

// --- Priraď stránku Blog ako "Posts page" ---
if($blog_page_id){
    update_option('page_for_posts', $blog_page_id);
    update_option('show_on_front', 'page');
}

// --- Nastavenie permalink štruktúry na /blog/{kategoria}/{post} ---
function custom_blog_permalinks() {
    global $wp_rewrite;
    $wp_rewrite->set_permalink_structure('/blog/%category%/%postname%/');
    $wp_rewrite->category_base = 'blog';
    $wp_rewrite->flush_rules(false);
}
add_action('init', 'custom_blog_permalinks');

// --- Hotovo ---
echo "Migrácia hotová: $post_count článkov, stránka Blog vytvorená. /blog/ listing článkov funguje.\n";
