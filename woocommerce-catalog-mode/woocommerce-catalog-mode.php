<?php
/*
Plugin Name: WooCommerce Catalog Mode
Description: Превращает WooCommerce-магазин в каталог, убирая корзину, кнопку «Добавить в корзину» и цены.
Version: 0.1
Author: Grok (xAI) & Andrew Arutunyan
Text Domain: woocommerce-catalog-mode
*/

// Проверка, что плагин активируется только если WooCommerce активен
function wcm_check_woocommerce_active() {
    if (!class_exists('WooCommerce')) {
        deactivate_plugins(plugin_basename(__FILE__));
        add_action('admin_notices', 'wcm_woocommerce_not_active_notice');
    }
}
add_action('admin_init', 'wcm_check_woocommerce_active');

function wcm_woocommerce_not_active_notice() {
    ?>
    <div class="error">
        <p><?php _e('Плагин WooCommerce Catalog Mode требует активного плагина WooCommerce для работы. Пожалуйста, установите и активируйте WooCommerce.', 'woocommerce-catalog-mode'); ?></p>
    </div>
    <?php
}

// Удаление кнопки «Добавить в корзину» на страницах магазина и товаров
function wcm_remove_add_to_cart_button() {
    remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart');
    remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
}
add_action('init', 'wcm_remove_add_to_cart_button');

// Скрытие цен
function wcm_hide_prices() {
    return false;
}
add_filter('woocommerce_get_price_html', 'wcm_hide_prices');

// Отключение страниц корзины и оформления заказа
function wcm_disable_cart_checkout() {
    if (is_cart() || is_checkout()) {
        wp_redirect(home_url());
        exit;
    }
}
add_action('template_redirect', 'wcm_disable_cart_checkout');

// Скрытие значка корзины в шапке через CSS
function wcm_hide_cart_icon() {
    ?>
    <style>
        .woocommerce .cart, .woocommerce .cart-contents {
            display: none !important;
        }
    </style>
    <?php
}
add_action('wp_head', 'wcm_hide_cart_icon');

// Добавление страницы каталога при активации плагина
function wcm_create_catalog_page() {
    $catalog_page = array(
        'post_title'   => __('Каталог', 'woocommerce-catalog-mode'),
        'post_content' => '[products columns="4" limit="12" paginate="true"]',
        'post_status'  => 'publish',
        'post_type'    => 'page',
        'post_author'  => 1,
    );

    // Проверяем, существует ли страница с таким названием
    if (!get_page_by_title('Каталог')) {
        wp_insert_post($catalog_page);
    }
}
register_activation_hook(__FILE__, 'wcm_create_catalog_page');

// Локализация плагина
function wcm_load_textdomain() {
    load_plugin_textdomain('woocommerce-catalog-mode', false, dirname(plugin_basename(__FILE__)) . '/languages/');
}
add_action('plugins_loaded', 'wcm_load_textdomain');