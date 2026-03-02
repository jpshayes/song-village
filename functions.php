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
 * Hide Stripe Express Checkout (Apple Pay / Google Pay) on single product pages.
 * It remains enabled on the checkout page.
 */
add_filter( 'wc_stripe_hide_payment_request_on_product_page', '__return_true' );

/**
 * WORKEXCHANGE2026 coupon — clear cart and enforce exactly 1x Sustaining variation (ID 273)
 * of Song Village Gathering 2026 (product ID 254).
 */
add_action( 'woocommerce_applied_coupon', function( $coupon_code ) {

    if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
        return;
    }

    $coupon_code = strtolower( $coupon_code );

    // Only respond to this single coupon.
    if ( 'workexchange2026' !== $coupon_code ) {
        return;
    }

    // Prevent recursion.
    static $running = false;
    if ( $running ) {
        return;
    }
    $running = true;

    $event_product_id        = 254; // parent variable product
    $sustaining_variation_id = 273; // sustaining variation
    $qty                     = 1;

    // If cart already contains exactly the sustaining ticket at qty 1, do nothing.
    $already_correct = ( WC()->cart->get_cart_contents_count() === 1 );
    if ( $already_correct ) {
        foreach ( WC()->cart->get_cart() as $item ) {
            if ( (int) $item['product_id'] === $event_product_id && (int) $item['variation_id'] === $sustaining_variation_id && (int) $item['quantity'] === 1 ) {
                $running = false;
                return;
            }
        }
    }

    // Empty cart and enforce sustaining tier qty 1.
    WC()->cart->empty_cart();

    $added = WC()->cart->add_to_cart(
        $event_product_id,
        $qty,
        $sustaining_variation_id
    );

    if ( $added ) {
        wc_add_notice( 'Work Exchange code applied. Your cart has been set to Sustaining registration (1 ticket).', 'notice' );
    } else {
        wc_add_notice( 'Work Exchange code applied, but we could not add the Sustaining registration. Please contact support.', 'error' );
    }

    $running = false;

}, 10, 1 );

/**
 * WORKEXCHANGE2026 coupon — force price to $275 on product 254 while coupon is in cart.
 * Also zeroes out the coupon's own discount amount so it doesn't double-discount.
 */
add_action( 'woocommerce_before_calculate_totals', function( $cart ) {

    if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
        return;
    }

    if ( ! $cart || ! $cart->has_discount( 'workexchange2026' ) ) {
        return;
    }

    foreach ( $cart->get_cart() as $cart_item ) {
        if ( isset( $cart_item['product_id'] ) && (int) $cart_item['product_id'] === 254 ) {
            $cart_item['data']->set_price( 275 );
        }
    }

}, 5, 1 ); // Priority 5 — runs before WC applies coupon discounts.

/**
 * Zero out WORKEXCHANGE2026 coupon discount amount so the set_price override
 * is the only pricing change — prevents double-discounting.
 */
add_filter( 'woocommerce_coupon_get_discount_amount', function( $discount, $discounting_amount, $cart_item, $single, $coupon ) {
    if ( 'workexchange2026' === strtolower( $coupon->get_code() ) ) {
        return 0;
    }
    return $discount;
}, 10, 5 );

/**
 * Force cart recalculation after WORKEXCHANGE2026 is applied so totals update immediately.
 */
add_action( 'woocommerce_applied_coupon', function( $coupon_code ) {
    if ( 'workexchange2026' === strtolower( $coupon_code ) && WC()->cart ) {
        WC()->cart->calculate_totals();
    }
}, 20 );
