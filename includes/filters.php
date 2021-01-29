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
			'total_compsets'               => [
				'label' => __( 'Total Comparison Sets', 'datafeedr-comparison-sets' ),
				'value' => $total_compsets,
				'debug' => $total_compsets,
			],
			'prune_compsets_cron_job_days' => [
				'label' => __( 'Sets Considered Old After', 'datafeedr-comparison-sets' ),
				'value' => $days . ' ' . __( 'days', 'datafeedr-comparison-sets' ),
				'debug' => $days,
			],
			'total_old_compsets'           => [
				'label' => __( 'Total Number of Old Sets', 'datafeedr-comparison-sets' ),
				'value' => $total_old_compsets,
				'debug' => $total_old_compsets,
			],
			'cache_lifetime'               => [
				'label' => __( 'Cache Lifetime', 'datafeedr-comparison-sets' ),
				'value' => dfrcs_get_option( 'cache_lifetime' ),
				'debug' => dfrcs_get_option( 'cache_lifetime' ),
			],
			'max_api_requests'             => [
				'label' => __( 'Max. API Requests per Set', 'datafeedr-comparison-sets' ),
				'value' => dfrcs_get_option( 'max_api_requests' ),
				'debug' => dfrcs_get_option( 'max_api_requests' ),
			],
			'integrations'                 => [
				'label' => __( 'Integrations', 'datafeedr-comparison-sets' ),
				'value' => implode( ', ', dfrcs_get_option( 'integrations' ) ),
				'debug' => implode( ', ', dfrcs_get_option( 'integrations' ) ),
			],
			'prune_records'                => [
				'label' => __( 'Delete Old Sets', 'datafeedr-comparison-sets' ),
				'value' => dfrcs_get_option( 'prune_records' ) == '1' ? __( 'Yes', 'datafeedr-comparison-sets' ) : __( 'No', 'datafeedr-comparison-sets' ),
				'debug' => dfrcs_get_option( 'prune_records' ),
			],
			'display_method'               => [
				'label' => __( 'Display Method', 'datafeedr-comparison-sets' ),
				'value' => dfrcs_get_option( 'display_method' ) == 'data' ? __( 'AJAX', 'datafeedr-comparison-sets' ) : __( 'PHP', 'datafeedr-comparison-sets' ),
				'debug' => dfrcs_get_option( 'display_method' ),
			],
			'minimum_num_products'         => [
				'label' => __( 'Minimum Number of Results', 'datafeedr-comparison-sets' ),
				'value' => dfrcs_get_option( 'minimum_num_products' ),
				'debug' => dfrcs_get_option( 'minimum_num_products' ),
			],
			'display_last_updated'         => [
				'label' => __( 'Display "Last Updated"', 'datafeedr-comparison-sets' ),
				'value' => dfrcs_get_option( 'display_last_updated' ) == '1' ? __( 'Yes', 'datafeedr-comparison-sets' ) : __( 'No', 'datafeedr-comparison-sets' ),
				'debug' => dfrcs_get_option( 'display_last_updated' ),
			],
			'include_master_product'       => [
				'label' => __( 'Include Master in Results', 'datafeedr-comparison-sets' ),
				'value' => dfrcs_get_option( 'include_master_product' ) == '1' ? __( 'Yes', 'datafeedr-comparison-sets' ) : __( 'No', 'datafeedr-comparison-sets' ),
				'debug' => dfrcs_get_option( 'include_master_product' ),
			],
			'title'                        => [
				'label' => __( 'Comparison Set Title', 'datafeedr-comparison-sets' ),
				'value' => dfrcs_get_option( 'title' ),
				'debug' => dfrcs_get_option( 'title' ),
			],
			'link_text'                    => [
				'label' => __( 'Button Text', 'datafeedr-comparison-sets' ),
				'value' => dfrcs_get_option( 'link_text' ),
				'debug' => dfrcs_get_option( 'link_text' ),
			],
			'loading_text'                 => [
				'label' => __( 'Loading Text', 'datafeedr-comparison-sets' ),
				'value' => dfrcs_get_option( 'loading_text' ),
				'debug' => dfrcs_get_option( 'loading_text' ),
			],
			'min_viewing_cap'              => [
				'label' => __( 'Minimum Viewing Role', 'datafeedr-comparison-sets' ),
				'value' => $min_viewing_cap_display,
				'debug' => $min_viewing_cap_display,
			],
			'debug_fields'                 => [
				'label' => __( 'Debug Fields', 'datafeedr-comparison-sets' ),
				'value' => implode( ', ', dfrcs_get_option( 'debug_fields' ) ),
				'debug' => implode( ', ', dfrcs_get_option( 'debug_fields' ) ),
			],
			'no_results_message'           => [
				'label' => __( 'No Results Message', 'datafeedr-comparison-sets' ),
				'value' => dfrcs_get_option( 'no_results_message' ),
				'debug' => dfrcs_get_option( 'no_results_message' ),
			],
			'used_label'                   => [
				'label' => __( 'Used Label', 'datafeedr-comparison-sets' ),
				'value' => dfrcs_get_option( 'used_label' ),
				'debug' => dfrcs_get_option( 'used_label' ),
			],
			'query_by_amazon'              => [
				'label' => __( 'Query Amazon', 'datafeedr-comparison-sets' ),
				'value' => dfrcs_get_option( 'query_by_amazon' ) == '1' ? __( 'Yes', 'datafeedr-comparison-sets' ) : __( 'No', 'datafeedr-comparison-sets' ),
				'debug' => dfrcs_get_option( 'query_by_amazon' ),
			],
			'query_by_name'                => [
				'label' => __( 'Query by Product Name', 'datafeedr-comparison-sets' ),
				'value' => dfrcs_get_option( 'query_by_name' ) == '1' ? __( 'Yes', 'datafeedr-comparison-sets' ) : __( 'No', 'datafeedr-comparison-sets' ),
				'debug' => dfrcs_get_option( 'query_by_name' ),
			],
			'query_by_model'               => [
				'label' => __( 'Query by Model Number', 'datafeedr-comparison-sets' ),
				'value' => dfrcs_get_option( 'query_by_model' ) == '1' ? __( 'Yes', 'datafeedr-comparison-sets' ) : __( 'No', 'datafeedr-comparison-sets' ),
				'debug' => dfrcs_get_option( 'query_by_model' ),
			],
			'query_by_barcodes'            => [
				'label' => __( 'Query by Barcodes', 'datafeedr-comparison-sets' ),
				'value' => dfrcs_get_option( 'query_by_barcodes' ) == '1' ? __( 'Yes', 'datafeedr-comparison-sets' ) : __( 'No', 'datafeedr-comparison-sets' ),
				'debug' => dfrcs_get_option( 'query_by_barcodes' ),
			],
			'keyword_accuracy'             => [
				'label' => __( 'Keyword Accuracy', 'datafeedr-comparison-sets' ),
				'value' => dfrcs_get_option( 'keyword_accuracy' ),
				'debug' => dfrcs_get_option( 'keyword_accuracy' ),
			],
			'exclude_duplicate_fields'     => [
				'label' => __( 'Exclude Duplicates Fields', 'datafeedr-comparison-sets' ),
				'value' => implode( ', ', dfrcs_get_option( 'exclude_duplicate_fields' ) ),
				'debug' => implode( ', ', dfrcs_get_option( 'exclude_duplicate_fields' ) ),
			],
			'barcode_fields'               => [
				'label' => __( 'Barcode Fields', 'datafeedr-comparison-sets' ),
				'value' => implode( ', ', dfrcs_get_option( 'barcode_fields' ) ),
				'debug' => implode( ', ', dfrcs_get_option( 'barcode_fields' ) ),
			],
			'brand_name_stopwords'         => [
				'label' => __( 'Brand Name Stopwords', 'datafeedr-comparison-sets' ),
				'value' => implode( ', ', dfrcs_get_option( 'brand_name_stopwords' ) ),
				'debug' => implode( ', ', dfrcs_get_option( 'brand_name_stopwords' ) ),
			],
			'mandatory_keywords'           => [
				'label' => __( 'Mandatory Keywords', 'datafeedr-comparison-sets' ),
				'value' => implode( ', ', dfrcs_get_option( 'mandatory_keywords' ) ),
				'debug' => implode( ', ', dfrcs_get_option( 'mandatory_keywords' ) ),
			],
			'product_name_stopwords'       => [
				'label' => __( 'Product Name Stopwords', 'datafeedr-comparison-sets' ),
				'value' => implode( ', ', dfrcs_get_option( 'product_name_stopwords' ) ),
				'debug' => implode( ', ', dfrcs_get_option( 'product_name_stopwords' ) ),
			],
		]
	];

	return $info;
} );
