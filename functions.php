<?php

function theme_enqueue_styles() {
    wp_enqueue_style( 'child-style', get_stylesheet_directory_uri() . '/style.css', [] );

    if ( function_exists( 'is_woocommerce' ) && ( is_woocommerce() || is_cart() || is_checkout() || is_account_page() ) ) {
        wp_enqueue_style( 'child-woocommerce', get_stylesheet_directory_uri() . '/woocommerce.css', [], wp_get_theme()->get( 'Version' ) );
    }
}
add_action( 'wp_enqueue_scripts', 'theme_enqueue_styles', 9999 );

function avada_lang_setup() {
	$lang = get_stylesheet_directory() . '/languages';
	load_child_theme_textdomain( 'Avada', $lang );
}
add_action( 'after_setup_theme', 'avada_lang_setup' );

/**
 * Single product page — remove unwanted elements.
 */
function song_village_remove_single_product_elements() {
    if ( ! is_singular( 'product' ) ) {
        return;
    }
    // Remove product image / gallery
    remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20 );
    // Remove SKU, categories, tags
    remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
    // Remove Additional Information / tabs section
    remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );
}
add_action( 'wp', 'song_village_remove_single_product_elements' );

/**
 * Output product description inside the summary, above the add-to-cart (priority 30).
 */
function song_village_output_product_description() {
    global $product;
    if ( ! $product ) {
        return;
    }
    $description = $product->get_description();
    if ( $description ) {
        echo '<div class="product-description">' . wp_kses_post( wpautop( $description ) ) . '</div>';
    }
}
add_action( 'woocommerce_single_product_summary', 'song_village_output_product_description', 25 );

/**
 * Hide Stripe Express Checkout (Apple Pay / Google Pay) on single product pages.
 * It remains enabled on the checkout page.
 */
add_filter( 'wc_stripe_hide_payment_request_on_product_page', '__return_true' );
