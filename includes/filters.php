<?php

/**
 * Exit if accessed directly
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add Datafeedr API Plugins settings and configuration to the WordPress
 * Site Health Info section (WordPress Admin Area > Tools > Site Health).
 *
 * @return array
 */
add_filter( 'debug_information', function ( $info ) {

	global $wpdb;

	$table              = $wpdb->prefix . DFRCS_TABLE;
	$total_compsets     = (string) absint( $wpdb->get_var( "SELECT COUNT(*) FROM $table" ) );
	$days               = absint( apply_filters( 'dfrcs_prune_compsets_cron_job_days', 30 ) );
	$total_old_compsets = (string) absint( $wpdb->get_var( "SELECT COUNT(*) FROM $table WHERE `updated` < (NOW() - INTERVAL $days DAY)" ) );

	$min_viewing_cap         = dfrcs_get_option( 'min_viewing_cap' );
	$min_viewing_cap_display = __( 'None', 'datafeedr-comparison-sets' );
	foreach ( get_editable_roles() as $key => $role ) {
		if ( $min_viewing_cap == $key ) {
			$min_viewing_cap_display = $role['name'];
		}
	}

	$info['datafeedr-comparison-sets-plugin'] = [
		'label'       => __( 'Datafeedr Comparison Sets Plugin', 'datafeedr-comparison-sets' ),
		'description' => '',
		'fields'      => [
			'total_compsets'                    => [
				'label' => __( 'Total Comparison Sets', 'datafeedr-comparison-sets' ),
				'value' => $total_compsets,
				'debug' => $total_compsets,
			],
			'prune_compsets_cron_job_days'      => [
				'label' => __( 'Sets Considered Old After', 'datafeedr-comparison-sets' ),
				'value' => $days . ' ' . __( 'days', 'datafeedr-comparison-sets' ),
				'debug' => $days,
			],
			'total_old_compsets'                => [
				'label' => __( 'Total Number of Old Sets', 'datafeedr-comparison-sets' ),
				'value' => $total_old_compsets,
				'debug' => $total_old_compsets,
			],
			'cache_lifetime'                    => [
				'label' => __( 'Cache Lifetime', 'datafeedr-comparison-sets' ),
				'value' => dfrcs_get_option( 'cache_lifetime' ),
				'debug' => dfrcs_get_option( 'cache_lifetime' ),
			],
			'max_api_requests'                  => [
				'label' => __( 'Max. API Requests per Set', 'datafeedr-comparison-sets' ),
				'value' => dfrcs_get_option( 'max_api_requests' ),
				'debug' => dfrcs_get_option( 'max_api_requests' ),
			],
			'integrations'                      => [
				'label' => __( 'Integrations', 'datafeedr-comparison-sets' ),
				'value' => implode( ', ', dfrcs_get_option( 'integrations' ) ),
				'debug' => implode( ', ', dfrcs_get_option( 'integrations' ) ),
			],
			'prune_records'                     => [
				'label' => __( 'Delete Old Sets', 'datafeedr-comparison-sets' ),
				'value' => dfrcs_get_option( 'prune_records' ) == '1' ? __( 'Yes', 'datafeedr-comparison-sets' ) : __( 'No', 'datafeedr-comparison-sets' ),
				'debug' => dfrcs_get_option( 'prune_records' ),
			],
			'display_method'                    => [
				'label' => __( 'Display Method', 'datafeedr-comparison-sets' ),
				'value' => dfrcs_get_option( 'display_method' ) === 'data' ? __( 'AJAX', 'datafeedr-comparison-sets' ) : __( 'PHP', 'datafeedr-comparison-sets' ),
				'debug' => dfrcs_get_option( 'display_method' ),
			],
			'minimum_num_products'              => [
				'label' => __( 'Minimum Number of Results', 'datafeedr-comparison-sets' ),
				'value' => dfrcs_get_option( 'minimum_num_products' ),
				'debug' => dfrcs_get_option( 'minimum_num_products' ),
			],
			'display_last_updated'              => [
				'label' => __( 'Display "Last Updated"', 'datafeedr-comparison-sets' ),
				'value' => dfrcs_get_option( 'display_last_updated' ) == '1' ? __( 'Yes', 'datafeedr-comparison-sets' ) : __( 'No', 'datafeedr-comparison-sets' ),
				'debug' => dfrcs_get_option( 'display_last_updated' ),
			],
			'include_master_product'            => [
				'label' => __( 'Include Master in Results', 'datafeedr-comparison-sets' ),
				'value' => dfrcs_get_option( 'include_master_product' ) == '1' ? __( 'Yes', 'datafeedr-comparison-sets' ) : __( 'No', 'datafeedr-comparison-sets' ),
				'debug' => dfrcs_get_option( 'include_master_product' ),
			],
			'title'                             => [
				'label' => __( 'Comparison Set Title', 'datafeedr-comparison-sets' ),
				'value' => dfrcs_get_option( 'title' ),
				'debug' => dfrcs_get_option( 'title' ),
			],
			'link_text'                         => [
				'label' => __( 'Button Text', 'datafeedr-comparison-sets' ),
				'value' => dfrcs_get_option( 'link_text' ),
				'debug' => dfrcs_get_option( 'link_text' ),
			],
			'loading_text'                      => [
				'label' => __( 'Loading Text', 'datafeedr-comparison-sets' ),
				'value' => dfrcs_get_option( 'loading_text' ),
				'debug' => dfrcs_get_option( 'loading_text' ),
			],
			'min_viewing_cap'                   => [
				'label' => __( 'Minimum Viewing Role', 'datafeedr-comparison-sets' ),
				'value' => $min_viewing_cap_display,
				'debug' => $min_viewing_cap_display,
			],
			'debug_fields'                      => [
				'label' => __( 'Debug Fields', 'datafeedr-comparison-sets' ),
				'value' => implode( ', ', dfrcs_get_option( 'debug_fields' ) ),
				'debug' => implode( ', ', dfrcs_get_option( 'debug_fields' ) ),
			],
			'no_results_message'                => [
				'label' => __( 'No Results Message', 'datafeedr-comparison-sets' ),
				'value' => dfrcs_get_option( 'no_results_message' ),
				'debug' => dfrcs_get_option( 'no_results_message' ),
			],
			'used_label'                        => [
				'label' => __( 'Used Label', 'datafeedr-comparison-sets' ),
				'value' => dfrcs_get_option( 'used_label' ),
				'debug' => dfrcs_get_option( 'used_label' ),
			],
			'display_image'                     => [
				'label' => __( 'Display Image', 'datafeedr-comparison-sets' ),
				'value' => absint( dfrcs_get_option( 'display_image' ) ) === 1 ? __( 'Yes', 'datafeedr-comparison-sets' ) : __( 'No', 'datafeedr-comparison-sets' ),
				'debug' => absint( dfrcs_get_option( 'display_image' ) ) === 1 ? __( 'Yes', 'datafeedr-comparison-sets' ) : __( 'No', 'datafeedr-comparison-sets' ),
			],
			'display_logo'                      => [
				'label' => __( 'Display Logo', 'datafeedr-comparison-sets' ),
				'value' => absint( dfrcs_get_option( 'display_logo' ) ) === 1 ? __( 'Yes', 'datafeedr-comparison-sets' ) : __( 'No', 'datafeedr-comparison-sets' ),
				'debug' => absint( dfrcs_get_option( 'display_logo' ) ) === 1 ? __( 'Yes', 'datafeedr-comparison-sets' ) : __( 'No', 'datafeedr-comparison-sets' ),
			],
			'display_price'                     => [
				'label' => __( 'Display Price', 'datafeedr-comparison-sets' ),
				'value' => absint( dfrcs_get_option( 'display_price' ) ) === 1 ? __( 'Yes', 'datafeedr-comparison-sets' ) : __( 'No', 'datafeedr-comparison-sets' ),
				'debug' => absint( dfrcs_get_option( 'display_price' ) ) === 1 ? __( 'Yes', 'datafeedr-comparison-sets' ) : __( 'No', 'datafeedr-comparison-sets' ),
			],
			'display_button'                    => [
				'label' => __( 'Display Button', 'datafeedr-comparison-sets' ),
				'value' => absint( dfrcs_get_option( 'display_button' ) ) === 1 ? __( 'Yes', 'datafeedr-comparison-sets' ) : __( 'No', 'datafeedr-comparison-sets' ),
				'debug' => absint( dfrcs_get_option( 'display_button' ) ) === 1 ? __( 'Yes', 'datafeedr-comparison-sets' ) : __( 'No', 'datafeedr-comparison-sets' ),
			],
			'display_promo'                     => [
				'label' => __( 'Display Promo', 'datafeedr-comparison-sets' ),
				'value' => absint( dfrcs_get_option( 'display_promo' ) ) === 1 ? __( 'Yes', 'datafeedr-comparison-sets' ) : __( 'No', 'datafeedr-comparison-sets' ),
				'debug' => absint( dfrcs_get_option( 'display_promo' ) ) === 1 ? __( 'Yes', 'datafeedr-comparison-sets' ) : __( 'No', 'datafeedr-comparison-sets' ),
			],
			'query_by_amazon'                   => [
				'label' => __( 'Query Amazon', 'datafeedr-comparison-sets' ),
				'value' => absint( dfrcs_get_option( 'query_by_amazon' ) ) === 1 ? __( 'Yes', 'datafeedr-comparison-sets' ) : __( 'No', 'datafeedr-comparison-sets' ),
				'debug' => absint( dfrcs_get_option( 'query_by_amazon' ) ) === 1 ? __( 'Yes', 'datafeedr-comparison-sets' ) : __( 'No', 'datafeedr-comparison-sets' ),
			],
			'query_by_name'                     => [
				'label' => __( 'Query by Product Name', 'datafeedr-comparison-sets' ),
				'value' => absint( dfrcs_get_option( 'query_by_name' ) ) === 1 ? __( 'Yes', 'datafeedr-comparison-sets' ) : __( 'No', 'datafeedr-comparison-sets' ),
				'debug' => absint( dfrcs_get_option( 'query_by_name' ) ) === 1 ? __( 'Yes', 'datafeedr-comparison-sets' ) : __( 'No', 'datafeedr-comparison-sets' ),
			],
			'query_by_model'                    => [
				'label' => __( 'Query by Model Number', 'datafeedr-comparison-sets' ),
				'value' => absint( dfrcs_get_option( 'query_by_model' ) ) === 1 ? __( 'Yes', 'datafeedr-comparison-sets' ) : __( 'No', 'datafeedr-comparison-sets' ),
				'debug' => absint( dfrcs_get_option( 'query_by_model' ) ) === 1 ? __( 'Yes', 'datafeedr-comparison-sets' ) : __( 'No', 'datafeedr-comparison-sets' ),
			],
			'query_by_barcodes'                 => [
				'label' => __( 'Query by Barcodes', 'datafeedr-comparison-sets' ),
				'value' => absint( dfrcs_get_option( 'query_by_barcodes' ) ) === 1 ? __( 'Yes', 'datafeedr-comparison-sets' ) : __( 'No', 'datafeedr-comparison-sets' ),
				'debug' => absint( dfrcs_get_option( 'query_by_barcodes' ) ) === 1 ? __( 'Yes', 'datafeedr-comparison-sets' ) : __( 'No', 'datafeedr-comparison-sets' ),
			],
			'keyword_accuracy'                  => [
				'label' => __( 'Keyword Accuracy', 'datafeedr-comparison-sets' ),
				'value' => dfrcs_get_option( 'keyword_accuracy' ),
				'debug' => dfrcs_get_option( 'keyword_accuracy' ),
			],
			'use_amazon_data_in_search'         => [
				'label' => __( 'Use Amazon Data', 'datafeedr-comparison-sets' ),
				'value' => absint( dfrcs_get_option( 'use_amazon_data_in_search' ) ) === 1 ? __( 'Yes', 'datafeedr-comparison-sets' ) : __( 'No', 'datafeedr-comparison-sets' ),
				'debug' => absint( dfrcs_get_option( 'use_amazon_data_in_search' ) ) === 1 ? __( 'Yes', 'datafeedr-comparison-sets' ) : __( 'No', 'datafeedr-comparison-sets' ),
			],
			'exclude_duplicate_fields'          => [
				'label' => __( 'Exclude Duplicates Fields', 'datafeedr-comparison-sets' ),
				'value' => implode( ', ', dfrcs_get_option( 'exclude_duplicate_fields' ) ),
				'debug' => implode( ', ', dfrcs_get_option( 'exclude_duplicate_fields' ) ),
			],
			'barcode_fields'                    => [
				'label' => __( 'Barcode Fields', 'datafeedr-comparison-sets' ),
				'value' => implode( ', ', dfrcs_get_option( 'barcode_fields' ) ),
				'debug' => implode( ', ', dfrcs_get_option( 'barcode_fields' ) ),
			],
			'brand_name_stopwords'              => [
				'label' => __( 'Brand Name Stopwords', 'datafeedr-comparison-sets' ),
				'value' => implode( ', ', dfrcs_get_option( 'brand_name_stopwords' ) ),
				'debug' => implode( ', ', dfrcs_get_option( 'brand_name_stopwords' ) ),
			],
			'mandatory_keywords'                => [
				'label' => __( 'Mandatory Keywords', 'datafeedr-comparison-sets' ),
				'value' => implode( ', ', dfrcs_get_option( 'mandatory_keywords' ) ),
				'debug' => implode( ', ', dfrcs_get_option( 'mandatory_keywords' ) ),
			],
			'product_name_stopwords'            => [
				'label' => __( 'Product Name Stopwords', 'datafeedr-comparison-sets' ),
				'value' => implode( ', ', dfrcs_get_option( 'product_name_stopwords' ) ),
				'debug' => implode( ', ', dfrcs_get_option( 'product_name_stopwords' ) ),
			],
			'amazon_disclaimer_title'           => [
				'label' => __( 'Amazon Disclaimer Title', 'datafeedr-comparison-sets' ),
				'value' => dfrcs_get_option( 'amazon_disclaimer_title' ),
				'debug' => dfrcs_get_option( 'amazon_disclaimer_title' ),
			],
			'amazon_disclaimer_message'         => [
				'label' => __( 'Amazon Disclaimer Message', 'datafeedr-comparison-sets' ),
				'value' => dfrcs_get_option( 'amazon_disclaimer_message' ),
				'debug' => dfrcs_get_option( 'amazon_disclaimer_message' ),
			],
			'amazon_disclaimer_anchor'          => [
				'label' => __( 'Amazon More Info Link', 'datafeedr-comparison-sets' ),
				'value' => dfrcs_get_option( 'amazon_disclaimer_anchor' ),
				'debug' => dfrcs_get_option( 'amazon_disclaimer_anchor' ),
			],
			'amazon_disclaimer_date_format'     => [
				'label' => __( 'Amazon Date Format', 'datafeedr-comparison-sets' ),
				'value' => dfrcs_get_option( 'amazon_disclaimer_date_format' ),
				'debug' => dfrcs_get_option( 'amazon_disclaimer_date_format' ),
			],
			'amazon_disclaimer_timezone_format' => [
				'label' => __( 'Amazon Date Timezone', 'datafeedr-comparison-sets' ),
				'value' => dfrcs_get_option( 'amazon_disclaimer_timezone_format' ),
				'debug' => dfrcs_get_option( 'amazon_disclaimer_timezone_format' ),
			],
		]
	];

	return $info;
} );

/**
 * Adds "Configuration" link under plugin name on Plugins page.
 *
 * @param array $links
 *
 * @return array
 */
function dfrcs_action_links( $links ) {
	return array_merge(
		$links,
		array(
			'config' => '<a href="' . admin_url( 'admin.php?page=dfrcs_options' ) . '">' . __( 'Configuration', DFRCS_DOMAIN ) . '</a>',
		)
	);
}

add_filter( 'plugin_action_links_' . 'datafeedr-comparison-sets/datafeedr-comparison-sets.php', 'dfrcs_action_links' );

/**
 * Adds link to Support and Documentation underneath plugin description on Plugins page.
 *
 * @param array $links
 * @param string $plugin_file
 *
 * @return mixed
 */
function dfrcs_plugin_row_meta( $links, $plugin_file ) {
	if ( $plugin_file === 'datafeedr-comparison-sets/datafeedr-comparison-sets.php' ) {
		$links[] = sprintf( '<a href="' . DFRAPI_DOCS_URL . '" target="_blank">%s</a>', __( 'Documentation', 'datafeedr-comparison-sets' ) );
		$links[] = sprintf( '<a href="' . DFRAPI_HELP_URL . '" target="_blank">%s</a>', __( 'Support', 'datafeedr-comparison-sets' ) );
	}

	return $links;
}

add_filter( 'plugin_row_meta', 'dfrcs_plugin_row_meta', 10, 2 );

/**
 * Enable the "promo" section for Amazon products.
 *
 * @param bool $display Whether to display the Promo section or not.
 * @param array $dfrcs_product The product array.
 *
 * @return bool
 */
function dfrcs_display_enable_promo_for_amazon_products( bool $display, array $dfrcs_product ): bool {
	return absint( $dfrcs_product['merchant_id'] ) === 7777 ? true : $display;
}

add_filter( 'dfrcs_display_promo', 'dfrcs_display_enable_promo_for_amazon_products', 10, 2 );

/**
 * Append Amazon disclaimer to the promo field.
 *
 * @param string $html
 * @param array $product
 *
 * @return string
 */
function dfrcs_display_amazon_disclaimer( string $html, array $product = [] ): string {

	global $compset;

	if ( empty( $product ) ) {
		return $html;
	}

	if ( absint( $product['merchant_id'] ) !== 7777 ) {
		return $html;
	}

	// Get from options
	$title           = dfrcs_get_option( 'amazon_disclaimer_title' );
	$anchor          = dfrcs_get_option( 'amazon_disclaimer_anchor' );
	$message         = dfrcs_get_option( 'amazon_disclaimer_message' );
	$date_format     = dfrcs_get_option( 'amazon_disclaimer_date_format' );
	$timezone_format = dfrcs_get_option( 'amazon_disclaimer_timezone_format' );

	$tld = dfrapi_str_before( dfrapi_str_after( strtolower( $product['url'] ), 'amazon' ), '/' );

	$amazon     = apply_filters( 'dfrcs_amazon_disclaimer_company_name', sprintf( 'Amazon%s', $tld ), $compset, $product );
	$finalprice = apply_filters( 'dfrcs_amazon_disclaimer_product_price', dfrapi_get_price( $product['finalprice'], $product['currency'], 'amazon-disclaimer' ), $compset, $product );
	$timestamp  = apply_filters( 'dfrcs_amazon_disclaimer_timestamp', date( esc_html( $date_format ), strtotime( $compset->date_updated ) ), $compset, $product );
	$timezone   = apply_filters( 'dfrcs_amazon_disclaimer_timezone', date_i18n( esc_html( $timezone_format ) ), $compset, $product );
	$anchor     = apply_filters( 'dfrcs_amazon_disclaimer_anchor', $anchor, $compset, $product );

	$translations = [
		'{amazon}'       => esc_html( $amazon ),
		'{finalprice}'   => esc_html( $finalprice ),
		'{timestamp}'    => esc_html( $timestamp ),
		'{timezone}'     => esc_html( $timezone ),
		'{product_name}' => esc_html( $product['name'] ),
	];

	$translations = apply_filters( 'dfrcs_amazon_disclaimer_translations', $translations, $compset, $product );

	$title = apply_filters( 'dfrcs_amazon_disclaimer_title', $title, $compset, $product );
	$title = strtr( $title, $translations );

	$message = apply_filters(
		'dfrcs_amazon_disclaimer_message',
		strtr( $message, $translations ),
		$compset,
		$product
	);

	if ( ! $title && ! $message && ! $anchor ) {
		return $html;
	}

	$disclaimer_format = apply_filters(
		'dfrcs_amazon_disclaimer_html_format',
		'<details class="dfrcs_amazon_disclaimer"><summary>%1$s <span>%2$s</span></summary><p>%3$s</p></details>',
		$compset,
		$product
	);

	$disclaimer = sprintf(
		$disclaimer_format,
		esc_html( $title ),
		esc_html( $anchor ),
		wp_kses( $message, [ 'br' => [], 'a' => [], 'strong' => [], 'em' => [] ] )
	);

	return $html . $disclaimer;
}

add_filter( 'dfrcs_promo', 'dfrcs_display_amazon_disclaimer', 10, 2 );

