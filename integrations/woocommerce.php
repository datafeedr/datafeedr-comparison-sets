<?php

/**
 * Display compset on WooCommerce product page.
 *
 * Description.
 *
 * @since 0.9.0
 *
 * @return string Formatted html of compset.
 */
add_action( 'woocommerce_after_single_product_summary', 'dfrcs_wc_single_product_page_compset', dfrcs_wc_compset_priority() );
function dfrcs_wc_single_product_page_compset() {
	$source = dfrcs_wc_get_source_of_product();

	$source['context'] = 'wc_single_product_page';
	echo dfrcs_compset( $source );
}

/**
 * @param WC_Product $product - This can only be a $product Object.
 *
 * @return array|mixed|void
 */
function dfrcs_wc_get_source_of_product( $product = false ) {

	// If no $product object was passed to this function, get the global $product object.
	if ( ! $product ) {
		global $product;
	}

	$source = array();
	$dfrps  = get_post_meta( $product->get_id(), '_dfrps_product', true );

	if ( ! empty( $dfrps ) ) {
		// Set $source "id".
		$source['id'] = $dfrps['_id'];

	} else {

		// Get product name.
		$name = apply_filters( 'dfrcs_wc_product_name', $product->get_title(), $product );

		// Get product's brand name.
		$slug  = apply_filters( 'dfrcs_wc_brand_slug', 'pa_brand' );
		$brand = wc_get_product_terms( $product->get_id(), $slug, array( 'fields' => 'names' ) );
		$brand = array_shift( $brand );
		$brand = apply_filters( 'dfrcs_wc_product_brand', $brand, $product );

		// Set $source "name" & "brand".
		$source['name']  = $name;
		$source['brand'] = $brand;
	}

	$source['post_id'] = get_the_ID();

	// Allow the modification of the $source array and $method.
	$source = apply_filters( 'dfrcs_wc_source', $source, $product );

	return $source;
}

/**
 * Returns priority for displaying compset on single WC product page.
 *
 * This function returns the add_action() priority for displaying the compset on the single WooCommerce
 * product page.
 *
 *      0  = Above Tabs (default)
 *      12 = Above Upsell / Under Description
 *      17 = Above Related Products
 *      25 = Below Related Products
 *
 * @since 0.9.0
 *
 * @return integer Returns the display priority..
 */
function dfrcs_wc_compset_priority() {
	return apply_filters( 'dfrcs_wc_compset_priority', 0 );
}

