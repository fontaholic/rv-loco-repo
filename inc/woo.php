<?php

defined( 'ABSPATH' ) or die;

/**
 * Auto Complete all WooCommerce virtual orders.
 *
 * @param  int 	$order_id The order ID to check
 * @return void
 */
function custom_woocommerce_auto_complete_virtual_orders( $order_id ) {

	// if there is no order id, exit
	if ( ! $order_id ) {
		return;
	}

	// get the order and its exit
	$order = wc_get_order( $order_id );
	$items = $order->get_items();

	// if there are no items, exit
	if ( 0 >= count( $items ) ) {
		return;
	}

	// go through each item
	foreach ( $items as $item ) {
		// if it is a variation
		if ( '0' != $item['variation_id'] ) {
			// make a product based upon variation
			$product = new WC_Product( $item['variation_id'] );
		} else {
			// else make a product off of the product id
			$product = new WC_Product( $item['product_id'] );

		}

		// if the product isn't virtual, exit
		if ( ! $product->is_virtual() ) {
			return;
		}
	}

	/*
	 * If we made it this far, then all of our items are virual
	 * We set the order to completed.
	 */
	$order->update_status( 'completed' );
}
add_action( 'woocommerce_thankyou', 'custom_woocommerce_auto_complete_virtual_orders' );


// woo gp - navigation - empty cart redirect to shop
add_action( 'template_redirect', 'empty_cart_redirect' );
function empty_cart_redirect(){
	if ( ! function_exists( 'is_cart' ) || ! function_exists( 'WC' ) ) return;

	if ( is_cart() && WC()->cart->is_empty() ) {
		wp_safe_redirect( get_permalink( wc_get_page_id( 'shop' ) ) );
		exit();
	}
}


//Add clear cart button on checkout page
	// check for empty-cart get param to clear the cart
add_action( 'init', 'woocommerce_clear_cart_url' );
function woocommerce_clear_cart_url() {
	global $woocommerce;
	if ( isset( $_GET['empty-cart'] ) ) {
		$woocommerce->cart->empty_cart();
	}
}
add_action( 'woocommerce_cart_actions', 'patricks_add_clear_cart_button', 20 );
function patricks_add_clear_cart_button() {
	echo "<a class='button' href='?empty-cart=true'>" . __( 'Empty Cart', 'woocommerce' ) . "</a>";
}

add_filter( 'woocommerce_breadcrumb_defaults', 'woo_change_breadcrumb_home_text' );
/**
 * Change the breadcrumb home text from "Home" to "Shop".
 * @param  array $defaults The default array items.
 * @return array           Modified array
 */
function woo_change_breadcrumb_home_text( $defaults ) {
	$defaults['home'] = 'Store';

	return $defaults;
}

add_filter( 'woocommerce_breadcrumb_home_url', 'woo_custom_breadrumb_home_url' );
/**
 * Change the breadcrumb home link URL from / to /shop.
 * @return string New URL for Home link item.
 */
function woo_custom_breadrumb_home_url() {
	return '/store/';
}

add_filter( 'wc_add_to_cart_message_html', 'ywp_change_add_to_cart_message_html', 10, 2 );
function ywp_change_add_to_cart_message_html( $message, $products ) {

	$count = 0;
	$titles = array();
	foreach ( $products as $product_id => $qty ) {
		$titles[] = ( $qty > 1 ? absint( $qty ) . ' &times; ' : '' ) . sprintf( _x( '&ldquo;%s&rdquo;', 'Item name in quotes', 'woocommerce' ), strip_tags( get_the_title( $product_id ) ) );
		$count += $qty;
	}

	$titles     = array_filter( $titles );
	$added_text = sprintf( _n(
		'%s is added to your basket.', // Singular
		'%s are added to your basket.', // Plural
		$count, // Number of products added
		'woocommerce' // Textdomain
		), wc_format_list_of_items( $titles )
	);
	return sprintf(
		'<a href="%s">%s</a> <a href="%s" class="button wc-forward">%s</a>',
		esc_url( wc_get_checkout_url() ),
		esc_html( $added_text ),
		esc_url( wc_get_checkout_url() ),
		esc_html__( 'Go to checkout', 'woocommerce' )
	);
}
