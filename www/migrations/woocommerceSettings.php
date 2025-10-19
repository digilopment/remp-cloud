<?php
require_once __DIR__ . '/autoload.php';

// --- Spusti WooCommerce Pages ---
if (function_exists('wc_create_pages')) {
    wc_create_pages();
}

// --- Nastavenie základných možností ---
update_option('woocommerce_default_country', 'SK');
update_option('woocommerce_currency', 'EUR');
update_option('woocommerce_currency_pos', 'right_space');
update_option('woocommerce_price_thousand_sep', ' ');
update_option('woocommerce_price_decimal_sep', ',');
update_option('woocommerce_price_num_decimals', '2');
update_option('woocommerce_store_address', 'Testovacia 1');
update_option('woocommerce_store_city', 'Bratislava');
update_option('woocommerce_store_postcode', '81101');
update_option('woocommerce_store_address_2', '');
update_option('woocommerce_default_customer_address', 'geolocation');
update_option('woocommerce_enable_taxes', 'yes');
update_option('woocommerce_calc_taxes', 'yes');
update_option('woocommerce_ship_to_countries', 'all');

// --- “Turn on lights” – spusti e-shop ---
update_option('woocommerce_enable_catalog', 'no');       // vypni režim katalógu
update_option('woocommerce_manage_stock', 'yes');        // povoľ správu skladu
update_option('woocommerce_stock_status', 'instock');    // produkty na sklade
update_option('woocommerce_hide_out_of_stock_items', 'no'); // zobraz produkty aj ak sú vypredané
update_option('woocommerce_store_is_open', 'yes');       // custom flag pre “open store” (ak používaš vlastný check)

// Označ setup wizard ako dokončený
update_option('woocommerce_setup_wizard', 'done');

// Vymaz transienty wizardu
delete_transient('_wc_setup_wizard');
delete_transient('_wc_setup_admin_notice');
delete_transient('wc_store_notice');
delete_transient('wc_settings');
// --- Načítaj front-end súbory WooCommerce ---
if (function_exists('wc')) {
    wc()->frontend_includes();
}
// --- Prepíš rewrite pravidlá ---
global $wp_rewrite;
$wp_rewrite->flush_rules(true);

// --- Vymazanie transientov a cache ---
global $wpdb;
$wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_%'");
wp_cache_flush();

// --- Nastavenie permalinkov pre produkty na /shop/%product_cat%/ ---
$permalink_settings = get_option('woocommerce_permalinks', []);
$permalink_settings['product_base']  = '/shop/%product_cat%';
$permalink_settings['category_base'] = 'shop';
update_option('woocommerce_permalinks', $permalink_settings);

// Prepíš rewrite pravidlá
global $wp_rewrite;
$wp_rewrite->flush_rules(true);

// --- Hotovo ---
echo "Migrácia WooCommerce hotová: stránky vytvorené, možnosti nastavené, e-shop spustený, cache vymazaná, permalinky /shop/%product_cat%/ nastavené.\n";
