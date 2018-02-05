<?php

/**
 * Functions available from both the frontend and backend (admin).
 */

/**
 *
 * This is the main function.
 *
 * $source is array of data used to create compset.
 * $args is the setting stuff
 */


add_action( 'mycode_single_content', 'do_compset' );
function do_compset( $content ) {

	$source = array(
		/*'id' => '1234567890',*/
		'name'    => 'helios hat',
		'brand'   => 'outdoor research',
		'title'   => 'Great prices on 27" iMacs',
		'context' => 'wc_single_product_page',
	);

	$content = dfrcs_compset( $source );
	echo $content;
}

/**
 * @param $source
 *
 * @return string
 */
function dfrcs_compset( $source ) {

	global $compset;
	$compset = new Dfrcs( $source );
	$compset->create();
	$html = $compset->display();

	return $html;
}

/**
 * This looks at the DFRCS options and returns the value
 * for the supplied key.
 */
function dfrcs_get_option( $key ) {

	$dfrcs_options   = get_option( 'dfrcs_options' );
	$default_options = dfrcs_default_options();
	$options         = wp_parse_args( $dfrcs_options, $default_options );

	return apply_filters( 'dfrcs_get_option', $options[ $key ], $options, $key );
}

/**
 * Return default set of options.
 *
 * @return array Default options
 */
function dfrcs_default_options( $key = false ) {

	$options = array(
		'barcode_fields'           => array( 'upc', 'ean', 'isbn' ),
		'brand_name_stopwords'     => array( 'inc', 'co', 'the', 'intl', 'international' ),
		'cache_lifetime'           => ( DAY_IN_SECONDS * 3 ),
		'debug_fields'             => array(
			'name',
			'brand',
			'merchant',
			'merchant_id',
			'source',
			'source_id',
			'price',
			'saleprice',
			'finalprice',
			'image',
			'url',
		),
		'display_last_updated'     => '1',
		'display_method'           => 'data',
		'exclude_duplicate_fields' => array( 'merchant_id' ),
		'include_master_product'   => '1',
		'integrations'             => array(),
		'keyword_accuracy'         => 90,
		'link_text'                => __( 'View', DFRCS_DOMAIN ),
		'loading_text'             => __( 'Loading the best prices...', DFRCS_DOMAIN ),
		'mandatory_keywords'       => array( 'woman', 'women', 'womens', 'man', 'men', 'mens', 'kids', 'kid' ),
		'max_api_requests'         => 5,
		'min_viewing_cap'          => '',
		'minimum_num_products'     => 2,
		'no_results_message'       => 'Sorry, no prices available at this time.',
		'post_id'                  => '',
		'product_name_stopwords'   => array( 'sale', 'closeout', 'closeouts', 'for', 'the', 'with', 'new' ),
		'query_by_amazon'          => '1',
		'query_by_barcodes'        => '1',
		'query_by_model'           => '1',
		'query_by_name'            => '1',
		'title'                    => __( 'Compare {num_products} Prices', DFRCS_DOMAIN ),
	);

	if ( $key ) {
		return $options[ $key ];
	}

	return $options;

}

/**
 * Returns $compset obj if cache exists (and optionally not expired).
 *
 * Returns false otherwise.
 *
 * @param      $source
 * @param bool $expired_ok
 *
 * @return bool|Dfrcs
 */
function dfrcs_cached_compset( $source, $expired_ok = true ) {

	if ( empty( $source ) ) {
		return false;
	}

	$compset = new Dfrcs( $source );
	$compset->check_cache();

	if ( ! $compset->cache_exists ) {
		return false;
	}

	if ( $compset->cache_expired && ! $expired_ok ) {
		return false;
	}

	$compset->generate();

	$minimum_num_products = dfrcs_get_option( 'minimum_num_products' );

	if ( $compset->get_product_count() < $minimum_num_products ) {
		return false;
	}

	return $compset;
}

/**
 * Sort array of products by 'compset_orderby' field in 'compset_order' order.
 *
 * This sorts the entire array of products by the user defined order and orderby values.
 *
 * @since 0.9.0
 *
 * @link http://stackoverflow.com/a/3233009 (Get list of sort columns and their data to pass to array_multisort)
 *
 * @param array $products An array of products.
 * @param string $orderby Which field to perform the order on.
 * @param string $order Which direction to order products, asc or desc.
 *
 * @return array Sorted products array.
 */
function dfrcs_sort_products( $products, $orderby, $order ) {

	if ( empty( $products ) ) {
		return $products;
	}

	$sort = array();
	foreach ( $products as $k => $v ) {
		$sort[ $orderby ][ $k ] = $v[ $orderby ];
	}

	$order = ( 'desc' == $order ) ? SORT_DESC : SORT_ASC;
	array_multisort( $sort[ $orderby ], $order, SORT_NUMERIC, $products );

	return $products;
}

function dfrcs_products() {
	global $compset;
	if ( ! empty( $compset->products ) ) {
		return apply_filters( 'dfrcs_products', $compset->products, $compset );
	}

	return false;
}

function dfrcs_title() {

	global $compset;

	$s = array(
		'{num_products}',
		'{num_merchants}',
		'{lowest_price}',
		'{highest_price}',
		'{product_name}',
	);

	$r = array(
		$compset->num_products,
		$compset->num_merchants,
		dfrcs_currency( $compset->lowest_priced_product ) . dfrapi_int_to_price( $compset->lowest_priced_product['finalprice'] ),
		dfrcs_currency( $compset->highest_priced_product ) . dfrapi_int_to_price( $compset->highest_priced_product['finalprice'] ),
		$compset->source->final['name'],
	);

	$msg = '';
	if ( ! $compset->meets_min_num_product_requirement() && dfrcs_can_manage_compset() ) {
		$admin_tip = __( 'This product is hidden to non-admins because it does not meet the "Minimum Number of Results" setting.',
			DFRCS_DOMAIN );
		$msg       = '<span title="' . esc_attr( $admin_tip ) . '"> ' . __( '[HIDDEN]', DFRCS_DOMAIN ) . '</span>';
	}

	return apply_filters( 'dfrcs_title', str_replace( $s, $r, $compset->args['title'] ), $compset ) . $msg;
}

function dfrcs_image( $product = array() ) {

	if ( ! empty( $product ) ) {
		$p = $product;
	} else {
		global $dfrcs_product;
		$p = $dfrcs_product;
	}

	$html  = '';
	$image = ( isset( $p['image'] ) && ! empty( $p['image'] ) ) ? trim( $p['image'] ) : '';

	if ( ! empty( $image ) ) {
		$html .= '<img src="' . $image . '" />';
	}

	return apply_filters( 'dfrcs_image', $html, $p );
}

function dfrcs_logo( $product = array() ) {

	if ( ! empty( $product ) ) {
		$p = $product;
	} else {
		global $dfrcs_product;
		$p = $dfrcs_product;
	}

	$merchant    = $p['merchant'];
	$merchant_id = $p['merchant_id'];
	$url         = 'https://images.datafeedr.com/m/' . $merchant_id . '.jpg';
	$title       = esc_attr( $merchant );

	$html = "<img onerror='this.parentNode.className += \" dfrcs_missing_logo\"' src='$url' alt='$title' title='$title'><span>$merchant</span>";

	return apply_filters( 'dfrcs_logo', $html, $p );
}

function dfrcs_currency( $product = array() ) {

	if ( ! empty( $product ) ) {
		$p = $product;
	} else {
		global $dfrcs_product;
		$p = $dfrcs_product;
	}

	$currency = ( isset( $p['currency'] ) )
		? dfrapi_currency_code_to_sign( $p['currency'] )
		: dfrapi_currency_code_to_sign( 'USD' );

	return apply_filters( 'dfrcs_currency', $currency, $p );
}

function dfrcs_price( $product = array() ) {

	if ( ! empty( $product ) ) {
		$p = $product;
	} else {
		global $dfrcs_product;
		$p = $dfrcs_product;
	}

	$html = '';

	$sign          = dfrcs_currency( $p );
	$sign_position = dfrcs_currency_sign_position( $sign );
	$prepend_sign  = ( 'prepend' == $sign_position ) ? $sign : '';
	$append_sign   = ( 'append' == $sign_position ) ? $sign : '';

	if ( '0' == $p['finalprice'] ) {
		$html .= '<span class="amount">' . apply_filters( 'dfrcs_price_zero', __( 'Click', DFRCS_DOMAIN ),
				$p ) . '</span>';
	} elseif ( isset( $p['saleprice'] ) ) {
		$html .= '<del><span class="amount">' . $prepend_sign . dfrapi_int_to_price( $p['price'] ) . $append_sign . '</span></del> ';
		$html .= '<ins><span class="amount">' . $prepend_sign . dfrapi_int_to_price( $p['finalprice'] ) . $append_sign . '</span></ins>';
	} else {
		$html .= '<span class="amount">' . $prepend_sign . dfrapi_int_to_price( $p['finalprice'] ) . $append_sign . '</span>';
	}

	return apply_filters( 'dfrcs_price', $html, $p );
}

/**
 * Returns 'append' if sign should be appended to price. Otherwise returns 'prepend'.
 *
 * @since 0.9.1
 *
 * @param $sign Values of dfrapi_currency_code_to_sign()
 *
 * @return string 'append' or 'prepend'
 */
function dfrcs_currency_sign_position( $sign ) {
	$appended_signs = array( 'kr' );
	$position       = ( in_array( $sign, $appended_signs ) ) ? 'append' : 'prepend';

	return apply_filters( 'dfrcs_currency_sign_position', $position, $sign );
}

function dfrcs_url( $product = array() ) {

	if ( ! empty( $product ) ) {
		$p = $product;
	} else {
		global $dfrcs_product;
		$p = $dfrcs_product;
	}

	$dfrapi_version = ( defined( 'DFRAPI_VERSION' ) ) ? DFRAPI_VERSION : '0';

	// In version 1.0.60 of the Datafeedr API plugin, support for Amazon products was added to dfrapi_url().
	if ( version_compare( $dfrapi_version, '1.0.60', '>=' ) ) {
		$url = dfrapi_url( $p );
	} else {
		$url = ( $p['merchant_id'] == '7777' ) ? $p['url'] : dfrapi_url( $p );
	}

	return apply_filters( 'dfrcs_link', $url, $p );
}

function dfrcs_link_text( $product = array() ) {

	global $compset;

	if ( ! empty( $product ) ) {
		$p = $product;
	} else {
		global $dfrcs_product;
		$p = $dfrcs_product;
	}

	return apply_filters( 'dfrcs_link_text', $compset->args['link_text'], $p, $compset );
}

function dfrcs_row_class( $product = array() ) {

	if ( ! empty( $product ) ) {
		$p = $product;
	} else {
		global $dfrcs_product;
		$p = $dfrcs_product;
	}

	$html = 'dfrcs_product_' . $p['_id'];

	if ( 0 == $p['_display'] ) {
		$html .= ' dfrcs_removed';
	}

	return apply_filters( 'dfrcs_row_class', $html, $p );
}

function dfrcs_promo( $product = array() ) {

	if ( ! empty( $product ) ) {
		$p = $product;
	} else {
		global $dfrcs_product;
		$p = $dfrcs_product;
	}

	$html = '';

	if ( isset( $p['promo'] ) ) {
		$html .= '<div class="dfrcs_promo">' . esc_html__( $p['promo'] ) . '</div>';
	}

	return apply_filters( 'dfrcs_promo', $html, $p );
}

function dfrcs_no_results_message() {

	global $compset;

	$message = dfrcs_get_option( 'no_results_message' );

	return apply_filters( 'dfrcs_no_results_message', $message, $compset );
}

function dfrcs_product_actions( $product = array() ) {

	$html = '';

	if ( ! dfrcs_can_manage_compset() ) {
		return $html;
	}

	if ( ! empty( $product ) ) {
		$p = $product;
	} else {
		global $dfrcs_product;
		$p = $dfrcs_product;
	}

	$display  = ( isset( $p['_display'] ) && ( 1 == $p['_display'] ) ) ? true : false;
	$auto     = ( isset( $p['_auto'] ) && ( 1 == $p['_auto'] ) ) ? true : false;
	$added    = ( isset( $p['_added'] ) && ( 1 == $p['_added'] ) ) ? true : false;
	$removed  = ( isset( $p['_removed'] ) && ( 1 == $p['_removed'] ) ) ? true : false;
	$excluded = ( isset( $p['_excluded'] ) && ( 1 == $p['_excluded'] ) ) ? true : false;

	$status = array(
		'auto'     => $auto,
		'added'    => $added,
		'removed'  => $removed,
		'excluded' => $excluded,
		'display'  => $display,
	);

	$status = json_encode( $status );

	$remove_link   = ' <a href="#" class="dfrcs_remove_product" data-dfrcs-pid="' . $p['_id'] . '" data-dfrcs-status=\'' . $status . '\'>' . __( 'Remove Product',
			DFRCS_DOMAIN ) . '</a>';
	$restore_link  = ' <a href="#" class="dfrcs_restore_product" data-dfrcs-pid="' . $p['_id'] . '" data-dfrcs-status=\'' . $status . '\'>' . __( 'Restore Product',
			DFRCS_DOMAIN ) . '</a>';
	$settings_link = ' <a href="' . add_query_arg( array( 'page' => 'dfrcs_options' ),
			admin_url( 'admin.php' ) ) . '" target="_blank">' . __( 'Change Settings', DFRCS_DOMAIN ) . '</a>';

	$html .= '<div class="dfrcs_product_actions">';

	if ( ! $display ) {

		$html .= ( $added ) ? __( 'This product was manually added to this comparison set.',
				DFRCS_DOMAIN ) . $remove_link : '';
		$html .= ( $removed && ! $excluded ) ? __( 'This product was manually removed from this comparison set.',
				DFRCS_DOMAIN ) . $restore_link : '';
		$html .= ( $excluded ) ? __( 'This product was automatically removed from this comparison set either by the "Include Master in Results" or "Exclude Duplicates Fields" setting.',
				DFRCS_DOMAIN ) . $settings_link . ' or ' . $restore_link : '';
		//$html .= '<a href="#" class="dfrcs_unremove_product" data-dfrcs-pid="' . $p['_id'] . '">' . __( 'Restore Product', DFRCS_DOMAIN ) . '</a>';

	} else {

		$html .= ( $added && $excluded ) ? __( 'This product was originally automatically excluded from this comparison set but then manually added.',
				DFRCS_DOMAIN ) . $remove_link : '';
		$html .= ( ! $added ) ? __( 'This product was automatically added to this comparison set.',
				DFRCS_DOMAIN ) . $remove_link : '';
		$html .= ( $added && ! $excluded ) ? __( 'This product was manually added to this comparison set.',
				DFRCS_DOMAIN ) . $remove_link : '';
		//$html .= '<a href="#" class="dfrcs_remove_product" data-dfrcs-pid="' . $p['_id'] . '">' . __( 'Remove Product', DFRCS_DOMAIN ) . '</a>';
	}

	$html .= '</div>';

	return $html;
}

function dfrcs_product_debug( $product = array() ) {

	if ( ! empty( $product ) ) {
		$p = $product;
	} else {
		global $dfrcs_product;
		$p = $dfrcs_product;
	}

	$html = '';

	if ( ! dfrcs_can_manage_compset() ) {
		return $html;
	}

	$html .= '<div class="dfrcs_compset_debug"><pre>';

	$fields = dfrcs_get_option( 'debug_fields' );

	array_unshift( $fields, '_id' );
	array_push( $fields, '_auto', '_added', '_removed', '_excluded', '_display' );

	$fields = apply_filters( 'dfrcs_product_debug_fields', array_unique( $fields ), $p );

	foreach ( $fields as $field ) {
		if ( isset( $p[ $field ] ) ) {

			if ( 'url' == $field ) {
				$url  = dfrcs_url( $p );
				$html .= "<strong>$field</strong> - <a style='display:inline;' target='_blank' href='$url'>$url</a>\n";

			} elseif ( 'image' == $field ) {
				$img  = $p[ $field ];
				$html .= "<strong>$field</strong> - <a style='display:inline;' target='_blank' href='$img'>$img</a>\n";

			} elseif ( 'price' == $field || 'saleprice' == $field || 'finalprice' == $field ) {
				$currency = dfrcs_currency( $p );
				$price    = dfrapi_int_to_price( $p[ $field ] );
				$val      = $currency . $price;
				$html     .= "<strong>$field</strong> - $val\n";

			} else {
				$val  = $p[ $field ];
				$html .= "<strong>$field</strong> - $val\n";
			}
		}
	}

	$html .= '</pre></div>';

	return $html;
}

function dfrcs_products_debug( $products ) {

	$array = array();

	if ( empty( $products ) ) {
		return $array;
	}

	$i = 0;

	$fields = dfrcs_get_option( 'debug_fields' );
	$fields = apply_filters( 'dfrcs_products_debug_fields', $fields, $products );

	foreach ( $products as $p ) {

		$array[ $i ] = array();

		foreach ( $fields as $field ) {
			if ( isset( $p[ $field ] ) ) {
				if ( 'url' == $field ) {
					$url                   = dfrcs_url( $p );
					$array[ $i ][ $field ] = '<a href="' . $url . '" target="_blank">' . $url . '</a>';
				} elseif ( 'image' == $field ) {
					$img                   = $p[ $field ];
					$array[ $i ][ $field ] = '<a href="' . $img . '" target="_blank">' . $img . '</a>';
				} elseif ( 'price' == $field || 'saleprice' == $field || 'finalprice' == $field ) {
					$currency              = dfrcs_currency( $p );
					$price                 = dfrapi_int_to_price( $p[ $field ] );
					$val                   = $currency . $price;
					$array[ $i ][ $field ] = $val;
				} else {
					$array[ $i ][ $field ] = $p[ $field ];
				}
			}
		}

		ksort( $array[ $i ] );

		$i ++;
	}

	return $array;
}

/**
 * Remove all but 1 product from $products array where $fields are duplicates.
 *
 * @param array $products An array of products.
 * @param array $fields An array of fields to base exclusion on.
 * @param int $iteration
 *
 * @return array|string
 */
function dfrcs_exclude_products_by_fields( $products, $fields, $iteration = 0 ) {

	if ( empty( $fields ) || empty( $products ) ) {
		return $products;
	}

	if ( ! isset( $fields[ $iteration ] ) ) {
		return $products;
	}

	$field = $fields[ $iteration ]; // Like "merchant_id"
	$track = array();

	foreach ( $products as $k => $v ) {
		if ( in_array( $v[ $field ], $track ) ) {
			$products[ $k ]['_excluded'] = 1;
		}
		$track[] = $v[ $field ];
	}

	$iteration ++;
	$products = dfrcs_exclude_products_by_fields( $products, $fields, $iteration );

	return $products;

}

/**
 * A function that can explode any single-dimensional array into a full blown tree structure,
 * based on the delimiters found in it's keys.
 *
 * @link http://kvz.io/blog/2007/10/03/convert-anything-to-tree-structures-in-php/
 *
 * @param        $array
 * @param string $delimiter
 * @param bool $baseval
 *
 * @return array|bool
 */
function dfrcs_explode_tree( $array, $delimiter = '/', $baseval = false ) {

	if ( ! is_array( $array ) ) {
		return false;
	}

	$split_re   = '/' . preg_quote( $delimiter, '/' ) . '/';
	$return_arr = array();

	foreach ( $array as $key => $val ) {

		// Get parent parts and the current leaf
		$parts     = preg_split( $split_re, $key, - 1, PREG_SPLIT_NO_EMPTY );
		$leaf_part = array_pop( $parts );

		// Build parent structure
		$parent_arr = &$return_arr;
		foreach ( $parts as $part ) {
			if ( ! isset( $parent_arr[ $part ] ) ) {
				$parent_arr[ $part ] = array();
			} elseif ( ! is_array( $parent_arr[ $part ] ) ) {
				if ( $baseval ) {
					$parent_arr[ $part ] = array( '__base_val' => $parent_arr[ $part ] );
				} else {
					$parent_arr[ $part ] = array();
				}
			}
			$parent_arr = &$parent_arr[ $part ];
		}

		// Add the final part to the structure
		if ( empty( $parent_arr[ $leaf_part ] ) ) {
			$parent_arr[ $leaf_part ] = $val;
		} elseif ( $baseval && is_array( $parent_arr[ $leaf_part ] ) ) {
			$parent_arr[ $leaf_part ]['__base_val'] = $val;
		}
	}

	return $return_arr;
}

/**
 * Sort the $source array by it's keys.
 *
 * @since 0.9.0
 *
 * @param array $source The unsorted $src_product array.
 *
 * @return array A key-sorted $source array.
 */
function dfrcs_sort_source( $source ) {
	if ( ! empty( $source ) ) {
		if ( version_compare( phpversion(), '5.4', '<' ) ) {
			natsort( $source );
		} else {
			ksort( $source, SORT_NATURAL | SORT_FLAG_CASE );
		}
	}

	return $source;
}

function dfrcs_get_default_source_keys() {
	$barcodes = dfrcs_get_option( 'barcode_fields' );
	$keys     = array_merge( $barcodes, array( 'id', 'name', 'brand', 'model', 'filters' ) );

	return $keys;
}

function dfrcs_get_default_arg_keys() {
	$keys = array(
		'link_text',
		'title',
		'post_id',
	);

	return $keys;
}

// @todo [future] 2016-02-04 13:33:10 - Maybe make this configurable. Maybe not.
// Changed from 'edit_plugins' to 'manage_options' on 2017-04-18 10:07:36
function dfrcs_can_manage_compset() {
	if ( current_user_can( 'manage_options' ) ) {
		return true;
	}

	return false;
}

/**
 * Changed 'updated' column to way in the past.
 *
 * @since 0.9.0
 *
 * @global object $wpdb WordPress DB Object.
 */
function dfrcs_refresh_compset( $hash ) {

	if ( ! dfrcs_can_manage_compset() ) {
		return;
	}

	global $wpdb;

	$updated = '1970-01-01 00:00:00';

	$table = $wpdb->prefix . DFRCS_TABLE;

	$wpdb->update(
		$table,
		array( 'updated' => $updated ),
		array( 'hash' => $hash ),
		array( '%s' ),
		array( '%s' )
	);
}

function dfrcs_select( $hash ) {

	global $wpdb;

	$hash = trim( $hash );
	if ( empty( $hash ) ) {
		return false;
	}

	$table = $wpdb->prefix . DFRCS_TABLE;

	$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE hash = %s", $hash ), ARRAY_A );
	if ( null !== $row ) {
		return $row;
	}

	return false;
}

function dfrcs_update_last_query( $hash, $query ) {

	global $wpdb;

	$hash = trim( $hash );
	if ( empty( $hash ) ) {
		return false;
	}

	$table = $wpdb->prefix . DFRCS_TABLE;

	$wpdb->update(
		$table,
		array( 'last_query' => serialize( $query ) ),
		array( 'hash' => $hash ),
		array( '%s' ),
		array( '%s' )
	);
}


function dfrcs_display_search_results( $products, $hash ) {

	$html = '';

	if ( empty( $products ) ) {
		return $html;
	}

	if ( empty( $hash ) ) {
		_e( 'Missing comparison set hash.', DFRCS_DOMAIN );

		return;
	}

	$compset = dfrcs_select( $hash );

	if ( ! $compset ) {
		_e( 'Invalid comparison set.', DFRCS_DOMAIN );

		return;
	}

	$included = dfrcs_extract_all_product_ids_from_products_array( unserialize( $compset['products'] ) );
	$removed  = unserialize( $compset['removed'] );
	$added    = unserialize( $compset['added'] );

	$html .= '<table id="dfrcs_search_results">';

	foreach ( $products as $product ) {

		$image    = ( isset( $product['image'] ) ) ? '<a href="' . $product['image'] . '" target="_blank"><img src="' . $product['image'] . '" /></a>' : '';
		$name     = ( isset( $product['name'] ) ) ? $product['name'] : 'n/a';
		$name     = $name . ' <span class="id" title="Product ID">' . $product['_id'] . '</span>';
		$network  = ( isset( $product['source'] ) ) ? '<span class="source" title="Affiliate Network">' . $product['source'] . '</span>' : '';
		$merchant = ( isset( $product['merchant'] ) ) ? '<span class="merchant" title="Merchant">' . $product['merchant'] . '</span>' : '';
		$brand    = ( isset( $product['brand'] ) ) ? '<span class="brand" title="Brand">' . $product['brand'] . '</span>' : '';
		$price    = ( isset( $product['price'] ) ) ? '<span class="price" title="Price">' . dfrcs_price( $product ) . '</span>' : '';

		$state = 'available';
		if ( in_array( $product['_id'], (array) $removed ) ) {
			$state = 'removed';
		} elseif ( in_array( $product['_id'], (array) $included ) || in_array( $product['_id'], (array) $added ) ) {
			$state = 'included';
		}

		if ( 'available' == $state ) {
			$action = '<span class="add" title="Add this product to your comparison set." data-dfrcs-pid="' . $product['_id'] . '">Add</span>';
		} elseif ( 'removed' == $state ) {
			$action = '<span class="removed" title="This product was removed from your comparison set.">Removed</span>';
		} elseif ( 'included' == $state ) {
			$action = '<span class="included" title="This product is already included in your comparison set.">Included</span>';
		}


		$html .= '<tr class="row">';
		$html .= '<td class="image">' . $image . '</td>';
		$html .= '<td class="details">';
		$html .= '<div class="name">' . $name . '</div>';
		$html .= '<div class="nmb">' . $network . $merchant . $brand . '</div>';
		$html .= '<div class="pricing">' . $price . '</div>';
		$html .= '</td>';
		$html .= '<td class="action">' . $action . '</td>';
		$html .= '</tr>';
	}


	$html .= '</table>';

	return $html;

}

function dfrcs_extract_all_product_ids_from_products_array( $products ) {

	$arr = array();

	if ( empty( $products ) ) {
		return $arr;
	}

	foreach ( $products as $product ) {
		$arr[] = $product['_id'];
	}

	return $arr;
}

/**
 * Returns an array of valid types of search filters.
 *
 * @return array
 */
function dfrcs_valid_filters() {

	$filters = array(
		'currency',
		'amazon_locale',
		'image',
		'onsale',
		'direct_url',
		'saleprice_min',
		'saleprice_max',
		'finalprice_min',
		'finalprice_max',
	);

	return apply_filters( 'dfrcs_valid_filters', $filters );
}

/**
 * Returns true if current User Agent is in the $bots array. Else returns false.
 *
 * @link https://github.com/monperrus/crawler-user-agents/
 *
 * @return boolean True if visitor is bot. Else false.
 */
function dfrcs_visitor_is_bot() {

	$user_agent = ( isset( $_SERVER['HTTP_USER_AGENT'] ) ) ? $_SERVER['HTTP_USER_AGENT'] : '';

	$bots = array(
		'360Spider',
		'A6-Indexer',
		'Aboundex',
		'acoonbot',
		'adbeat_bot',
		'AddThis',
		'Adidxbot',
		'ADmantX',
		'AdsBot',
		'AdvBot',
		'ahrefsbot',
		'aihitbot',
		'AISearchBot',
		'antibot',
		'Applebot',
		'arabot',
		'archive.org_bot',
		'backlinkcrawler',
		'baiduspider',
		'betaBot',
		'bibnum.bnf',
		'biglotron',
		'bingbot',
		'BingPreview',
		'binlar',
		'blekkobot',
		'blexbot',
		'bnf.fr_bot',
		'brainobot',
		'BUbiNG',
		'buzzbot',
		'CapsuleChecker',
		'careerbot',
		'CC Metadata Scaper',
		'ccbot',
		'changedetection',
		'citeseerxbot',
		'Cliqzbot',
		'coccoc',
		'collection@infegy.com',
		'content crawler spider',
		'convera',
		'crawler4j',
		'CrystalSemanticsBot',
		'cXensebot',
		'CyberPatrol',
		'datagnionbot',
		'deadlinkchecker',
		'DeuSu',
		'discobot',
		'Domain Re-Animator Bot',
		'domaincrawler',
		'dotbot',
		'drupact',
		'DuckDuckBot',
		'ec2linkfinder',
		'edisterbot',
		'elisabot',
		'Embedly',
		'europarchive.org',
		'exabot',
		'ezooms',
		'facebook',
		'facebookexternalhit',
		'Facebot',
		'FAST Enterprise Crawler',
		'FAST-WebCrawler',
		'findlink',
		'findthatfile',
		'findxbot',
		'fluffy',
		'fr-crawler',
		'g00g1e.net',
		'gigablast',
		'gigabot',
		'GingerCrawler',
		'Gluten Free Crawler',
		'gnam gnam spider',
		'Google-Adwords-Instant',
		'Googlebot',
		'GrapeshotCrawler',
		'grub.org',
		'gslfbot',
		'heritrix',
		'httpunit',
		'httrack',
		'ia_archiver',
		'ichiro',
		'integromedb',
		'intelium_bot',
		'InterfaxScanBot',
		'ip-web-crawler.com',
		'ips-agent',
		'iskanie',
		'IstellaBot',
		'it2media-domain-crawler',
		'java',
		'jyxobot',
		'lb-spider',
		'libwww',
		'Linguee Bot',
		'linkdex',
		'linkdexbot',
		'lipperhey',
		'Livelapbot',
		'lssbot',
		'lssrocketcrawler',
		'ltx71',
		'Mediapartners-Google',
		'MegaIndex',
		'memorybot',
		'MetaURI',
		'MJ12bot',
		'mlbot',
		'MojeekBot',
		'msnbot',
		'msrbot',
		'NerdByNature.Bot',
		'nerdybot',
		'netEstate NE Crawler',
		'netresearchserver',
		'ngbot',
		'niki-bot',
		'nutch',
		'OpenHoseBot',
		'openindexspider',
		'OrangeBot',
		'page2rss',
		'panscient',
		'phpcrawl',
		'postrank',
		'proximic',
		'psbot',
		'purebot',
		'Python-urllib',
		'Qwantify',
		'RankActiveLinkBot',
		'redditbot',
		'RetrevoPageAnalyzer',
		'rogerbot',
		'RU_Bot',
		'SafeDNSBot',
		'SafeSearch microdata crawler',
		'Scanbot',
		'Scrapy',
		'Screaming Frog SEO Spider',
		'scribdbot',
		'seekbot',
		'SemanticScholarBot',
		'SemrushBot',
		'seokicks-robot',
		'seznambot',
		'SimpleCrawler',
		'sistrix crawler',
		'sitebot',
		'siteexplorer.info',
		'SkypeUriPreview',
		'Slack-ImgProxy',
		'Slackbot',
		'slurp',
		'smtbot',
		'sogou',
		'Sonic',
		'spbot',
		'speedy',
		'summify',
		'Sysomos',
		'tagoobot',
		'teoma',
		'toplistbot',
		'Trove',
		'turnitinbot',
		'TweetmemeBot',
		'twengabot',
		'Twitterbot',
		'urlappendbot',
		'UsineNouvelleCrawler',
		'Veoozbot',
		'voilabot',
		'Voyager',
		'wbsearchbot',
		'web-archive-net.com.bot',
		'webcompanycrawler',
		'webcrawler',
		'webmon',
		'WeSEE:Search',
		'WhatsApp',
		'wocbot',
		'woriobot',
		'wotbox',
		'xovibot',
		'y!j-asr',
		'yacybot',
		'yandex.combots',
		'yandexbot',
		'yanga',
		'yeti',
		'yoozBot',
		'ZoomBot',
	);

	$bots = apply_filters( 'dfrcs_bots', $bots );

	foreach ( $bots as $bot ) {
		if ( stripos( $user_agent, $bot ) !== false ) {
			return true;
		}
	}

	return false;
}

/**
 * Wrapper for dfrcs_get_compset_product_field().
 *
 * @since 0.9.17
 *
 * @param Dfrcs $compset
 * @param string $field Example: finalprice, url, price, merchant, merchant_id, etc...
 *
 * @return mixed|int|string False if invalid or int or string value if found.
 */
function dfrcs_get_lowest_priced_product_field( Dfrcs $compset, $field ) {
	return dfrcs_get_compset_product_field( $compset, $field, 'lowest_priced_product' );
}

/**
 * Wrapper for dfrcs_get_compset_product_field().
 *
 * @since 0.9.17
 *
 * @param Dfrcs $compset
 * @param string $field Example: finalprice, url, price, merchant, merchant_id, etc...
 *
 * @return mixed|int|string False if invalid or int or string value if found.
 */
function dfrcs_get_highest_priced_product_field( Dfrcs $compset, $field ) {
	return dfrcs_get_compset_product_field( $compset, $field, 'highest_priced_product' );
}

/**
 * Returns a specific field from either the highest priced product or lowest
 * priced product in a Comparison Set.
 *
 * If neither of those products exist in the Comparison Set or the Comparison Set is
 * not cached, then this function returns false.
 *
 * @since 0.9.17
 *
 * @param Dfrcs $compset
 * @param string $field Example: finalprice, url, price, merchant, merchant_id, etc...
 * @param string $select Either "lowest_priced_product" or "highest_priced_product".
 *
 * @return mixed|int|string False if invalid or int or string value if found.
 */
function dfrcs_get_compset_product_field( Dfrcs $compset, $field, $select ) {

	// We do not have a cached Comparison Set. Return false.
	if ( ! $compset->cached ) {
		return false;
	}

	// The lowest priced product (or highest priced product) does not exist in this Comparison Set. Return false.
	if ( ! isset( $compset->{$select} ) || empty( $compset->{$select} ) ) {
		return false;
	}

	$product = $compset->{$select};

	// The $field does not exist for this product. Return false.
	if ( ! isset( $product[ $field ] ) ) {
		return false;
	}

	return $product[ $field ];
}
