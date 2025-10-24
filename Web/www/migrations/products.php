<?php
require_once __DIR__ . '/autoload.php';

$product_count = getenv('NUM_OF_PRODUCTS_TO_IMPORT');
$category_names = [
    "E-booky",
    "Online kurzy",
    "Predplatné",
    "Tech reporty",
    "Startup eventy",
    "Merchandising",
    "Webináre",
    "Video tutoriály",
    "Školenia",
    "Analýzy a infografiky"
];
$desc_length = 250;

// --- Vymaz produkty a kategorie ---
$all_products = get_posts(['post_type'=>'product','numberposts'=>-1]);
foreach($all_products as $p){
    wp_delete_post($p->ID,true);
}

$all_cats = get_terms(['taxonomy'=>'product_cat','hide_empty'=>false]);
foreach($all_cats as $c){
    wp_delete_term($c->term_id,'product_cat');
}

// --- Vytvor kategorie ---
$category_ids = [];
foreach($category_names as $cat_name){
    $term = wp_insert_term($cat_name,'product_cat');
    if(!is_wp_error($term)){
        $category_ids[] = $term['term_id'];
    }
}


// --- Funkcia na nahodny text s vetami ---
function random_text($length = 250) {
    $text = '';
    $words = [];
    $alphabet = 'abcdefghijklmnopqrstuvwxyz';
    
    while (strlen(implode(' ', $words)) < $length) {
        // náhodná dĺžka slova 3-10
        $word_length = rand(3, 10);
        $word = '';
        for ($i = 0; $i < $word_length; $i++) {
            $word .= $alphabet[rand(0, strlen($alphabet) - 1)];
        }
        $words[] = $word;
        
        // náhodne ukonči vetu bodkou a veľkým písmenom
        if (count($words) % rand(8, 15) === 0) {
            $words[count($words)-1] .= '.';
            // prvé písmeno nasledujúceho slova bude veľké
            if (isset($words[count($words)])) {
                $words[count($words)] = ucfirst($words[count($words)]);
            }
        }
    }
    
    $text = implode(' ', $words);
    // skrát text, ak je dlhší než požadované
    return substr($text, 0, $length);
}


// --- Funkcia na pridanie nahodneho image ---
function attach_random_image($post_id){
    $image_url = "https://dummyimage.com/800x520/eeeeee/111111.jpg&text=". getenv('TITLE') ."+" . rand(1,10000);
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/media.php');
    require_once(ABSPATH . 'wp-admin/includes/image.php');

    $media = media_sideload_image($image_url, $post_id, null, 'id');
    if(!is_wp_error($media)){
        set_post_thumbnail($post_id, $media);
    }
}

// --- Vytvor produkty ---
for($i=1;$i<=$product_count;$i++){
    $name = getenv('TITLE') ." Produkt $i";
    $price = rand(5,49) . ".00";
    $stock = rand(1,50);
    $desc = random_text($desc_length);
    
    $post_id = wp_insert_post([
        'post_title' => $name,
        'post_content' => $desc,
        'post_status' => 'publish',
        'post_type' => 'product'
    ]);
    
    if($post_id){
        wp_set_object_terms($post_id,'simple','product_type');

        update_post_meta($post_id,'_regular_price',$price);
        update_post_meta($post_id,'_price',$price);
        update_post_meta($post_id,'_stock',$stock);
        update_post_meta($post_id,'_stock_status','instock');
        update_post_meta($post_id,'_manage_stock','yes');

        // prirad 1-2 nahodne kategorie
        $rand_cats = (array)array_rand(array_flip($category_ids),rand(1,2));
        wp_set_object_terms($post_id,$rand_cats,'product_cat');

        // prirad nahodny obrazok
        attach_random_image($post_id);
    }
}
flush_rewrite_rules(false);
echo "Migracia hotova, $product_count produktov pre ". getenv('TITLE') ." importovanych s nahodnymi obrazkami.\n";
