<?php

/**
 * Display admin notices for each required plugin that needs to be
 * installed, activated and/or updated.
 *
 * @since 0.9.14
 */
function dfrcs_admin_notice_plugin_dependencies() {

	/**
	 * @var Dfrcs_Plugin_Dependency[] $dependencies
	 */
	$dependencies = array(
		new Dfrcs_Plugin_Dependency( 'Datafeedr API', 'datafeedr-api/datafeedr-api.php', '1.0.75' ),
	);

	foreach ( $dependencies as $dependency ) {

		$action = $dependency->action_required();

		if ( ! $action ) {
			continue;
		}

		echo '<div class="notice notice-error"><p>';
		echo $dependency->msg( 'Datafeedr Comparison Sets' );
		echo $dependency->link();
		echo '</p></div>';
	}
}

add_action( 'admin_notices', 'dfrcs_admin_notice_plugin_dependencies' );

/**
 * Add settings page as submenu to Datafeedr API plugin.
 */
add_action( 'admin_menu', 'dfrcs_admin_menu', 999 );
function dfrcs_admin_menu() {

	// Add link to "Comparison Sets" under "Datafeedr API"
	add_submenu_page(
		'dfrapi',
		__( 'Datafeedr Comparison Sets', DFRCS_DOMAIN ),
		__( 'Comparison Sets', DFRCS_DOMAIN ),
		'manage_options',
		'dfrcs_options',
		'dfrcs_options_output'
	);

	// Add page which allows users to add products to a compset. This doesn't need to appear in the navigation.
	add_submenu_page(
		'admin.php',
		'null',
		'null',
		'manage_options',
		'dfrcs_add_products',
		'dfrcs_add_products_output'
	);
}

add_action( 'admin_init', 'dfrcs_register_settings' );
function dfrcs_register_settings() {

	register_setting( 'dfrcs_options', 'dfrcs_options', 'dfrcs_options_validate' );

	// General Settings
	add_settings_section( 'dfrcs_options_general', 'General Settings', 'dfrcs_options_general_desc', 'dfrcs_options' );
	add_settings_field( 'dfrcs_cache_lifetime_setting', 'Cache Lifetime', 'dfrcs_cache_lifetime', 'dfrcs_options', 'dfrcs_options_general' );
	add_settings_field( 'dfrcs_max_api_requests_setting', 'Max. API Requests per Set', 'dfrcs_max_api_requests', 'dfrcs_options', 'dfrcs_options_general' );
	add_settings_field( 'dfrcs_integrations_setting', 'Integrations', 'dfrcs_integrations', 'dfrcs_options', 'dfrcs_options_general' );

	// Display Settings
	add_settings_section( 'dfrcs_options_display', 'Display Settings', 'dfrcs_options_display_desc', 'dfrcs_options' );
	add_settings_field( 'dfrcs_display_method_setting', 'Display Method', 'dfrcs_display_method', 'dfrcs_options', 'dfrcs_options_display' );
	add_settings_field( 'dfrcs_minimum_num_products_setting', 'Minimum Number of Results', 'dfrcs_minimum_num_products', 'dfrcs_options', 'dfrcs_options_display' );
	add_settings_field( 'dfrcs_display_last_updated_setting', 'Display "Last Updated"', 'dfrcs_display_last_updated', 'dfrcs_options', 'dfrcs_options_display' );
	add_settings_field( 'dfrcs_include_master_product_setting', 'Include Master in Results', 'dfrcs_include_master_product', 'dfrcs_options', 'dfrcs_options_display' );
	add_settings_field( 'dfrcs_title_setting', 'Comparison Set Title', 'dfrcs_title_setting', 'dfrcs_options', 'dfrcs_options_display' );
	add_settings_field( 'dfrcs_link_text_setting', 'Button Text', 'dfrcs_link_text_setting', 'dfrcs_options', 'dfrcs_options_display' );
	add_settings_field( 'dfrcs_loading_text_setting', 'Loading Text', 'dfrcs_loading_text_setting', 'dfrcs_options', 'dfrcs_options_display' );
	add_settings_field( 'dfrcs_min_viewing_cap_setting', 'Minimum Viewing Role', 'dfrcs_min_viewing_cap', 'dfrcs_options', 'dfrcs_options_display' );
	add_settings_field( 'dfrcs_debug_fields_setting', 'Debug Fields', 'dfrcs_debug_fields', 'dfrcs_options', 'dfrcs_options_display' );
	add_settings_field( 'dfrcs_no_results_message_setting', 'No Results Message', 'dfrcs_no_results_message_setting', 'dfrcs_options', 'dfrcs_options_display' );

	// Query Settings
	add_settings_section( 'dfrcs_options_query', 'Query Settings', 'dfrcs_options_query_desc', 'dfrcs_options' );
	add_settings_field( 'dfrcs_query_by_amazon_setting', 'Query Amazon', 'dfrcs_query_by_amazon', 'dfrcs_options', 'dfrcs_options_query' );
	add_settings_field( 'dfrcs_query_by_name_setting', 'Query by Product Name', 'dfrcs_query_by_name', 'dfrcs_options', 'dfrcs_options_query' );
	add_settings_field( 'dfrcs_query_by_model_setting', 'Query by Model Number', 'dfrcs_query_by_model', 'dfrcs_options', 'dfrcs_options_query' );
	add_settings_field( 'dfrcs_query_by_barcodes_setting', 'Query by Barcodes', 'dfrcs_query_by_barcodes', 'dfrcs_options', 'dfrcs_options_query' );
	add_settings_field( 'dfrcs_keyword_accuracy_setting', 'Keyword Accuracy', 'dfrcs_keyword_accuracy', 'dfrcs_options', 'dfrcs_options_query' );
	add_settings_field( 'dfrcs_exclude_duplicate_fields_setting', 'Exclude Duplicates Fields', 'dfrcs_exclude_duplicate_fields', 'dfrcs_options', 'dfrcs_options_query' );
	add_settings_field( 'dfrcs_barcode_fields_setting', 'Barcode Fields', 'dfrcs_barcode_fields', 'dfrcs_options', 'dfrcs_options_query' );
	add_settings_field( 'dfrcs_brand_name_stopwords_setting', 'Brand Name Stopwords', 'dfrcs_brand_name_stopwords', 'dfrcs_options', 'dfrcs_options_query' );
	add_settings_field( 'dfrcs_mandatory_keywords_setting', 'Mandatory Keywords', 'dfrcs_mandatory_keywords', 'dfrcs_options', 'dfrcs_options_query' );
	add_settings_field( 'dfrcs_product_name_stopwords_setting', 'Product Name Stopwords', 'dfrcs_product_name_stopwords', 'dfrcs_options', 'dfrcs_options_query' );
}

/**
 * Build settings page.
 */
function dfrcs_options_output() {
	echo '<div class="wrap" id="dfrcs_options">';
	echo '<h2>' . __( 'Datafeedr Comparison Sets', DFRCS_DOMAIN ) . '</h2>';

	$options = get_option( 'dfrcs_options' );
	echo '<form method="post" action="options.php">';
	wp_nonce_field( 'dfrcs-update_options' );
	settings_fields( 'dfrcs_options' );
	do_settings_sections( 'dfrcs_options' );
	submit_button();
	echo '</form>';
	echo '</div>';
}

function dfrcs_options_general_desc() {
}

function dfrcs_options_display_desc() {
	echo '<p>';
	echo __( 'These settings control how the comparison set appears on your website.', DFRCS_DOMAIN );
	echo '</p>';
}

function dfrcs_options_query_desc() {
	echo '<p>';
	echo __( 'These settings control how comparison sets are generated.', DFRCS_DOMAIN );
	echo '</p>';
}

function dfrcs_cache_lifetime() {
	$key     = 'cache_lifetime';
	$name    = 'dfrcs_options[' . $key . ']';
	$value   = dfrcs_get_option( $key );
	$default = dfrcs_default_options( $key );
	echo '<input type="text" name="' . $name . '" value="' . $value . '"> ' . __( 'seconds', DFRCS_DOMAIN );
	echo '<p class="description">';
	echo __( 'How often, in seconds, should your comparison sets be updated. The lower the number, the more API requests will be required.', DFRCS_DOMAIN );
	echo '<br />';
	echo '<strong>' . __( 'Important: ', DFRCS_DOMAIN ) . '</strong>';
	echo __( 'If you are displaying Amazon products, Cache Lifetime should NOT be greater than 86400.', DFRCS_DOMAIN );
	echo '<br />';
	echo '<small>' . __( 'Default: ', DFRCS_DOMAIN ) . $default . '</small>';
	echo '</p>';
}

function dfrcs_max_api_requests() {
	$key     = 'max_api_requests';
	$name    = 'dfrcs_options[' . $key . ']';
	$value   = dfrcs_get_option( $key );
	$default = dfrcs_default_options( $key );
	echo '<input type="text" name="' . $name . '" value="' . $value . '"> ' . __( 'requests', DFRCS_DOMAIN );
	echo '<p class="description">';
	echo __( 'The maximum number of API requests to use when creating or updating a comparison set.', DFRCS_DOMAIN );
	echo '<br />';
	echo '<small>' . __( 'Default: ', DFRCS_DOMAIN ) . $default . '</small>';
	echo '</p>';
}

function dfrcs_integrations() {
	$key   = 'integrations';
	$name  = 'dfrcs_options[' . $key . '][]';
	$value = dfrcs_get_option( $key );

	// WooCommerce
	$woocommerce_checked = ( in_array( 'woocommerce', $value ) ) ? ' checked="checked"' : '';
	echo '<label for="dfrcs_woocommerce_integration">';
	echo '<input type="checkbox" name="' . $name . '" value="woocommerce" id="dfrcs_woocommerce_integration"' . $woocommerce_checked . '> WooCommerce';
	echo '<small> - ' . __( 'Generate a comparison set for a WooCommerce product when the product is viewed.', DFRCS_DOMAIN ) . '</small>';
	echo '</label>';

	echo '<p class="description">';
	echo __( 'Enable specific integrations.', DFRCS_DOMAIN );
	echo '</p>';
}

function dfrcs_display_method() {
	$key     = 'display_method';
	$name    = 'dfrcs_options[' . $key . ']';
	$value   = dfrcs_get_option( $key );
	$default = dfrcs_default_options( $key );

	echo '<select name="' . $name . '">';
	echo '<option value="php" ' . selected( $value, 'php', false ) . '>PHP</option>';
	echo '<option value="data" ' . selected( $value, 'data', false ) . '>AJAX</option>';
	echo '</select>';

	echo '<p class="description">';
	echo __( 'The method to display your comparison set after it has been generated and cached.', DFRCS_DOMAIN );
	echo '<br />';
	echo '<small>' . __( 'Default: ', DFRCS_DOMAIN );
	echo ( 'php' == $default ) ? 'PHP' : 'AJAX';
	echo '</small></p>';
}

function dfrcs_minimum_num_products() {
	$key     = 'minimum_num_products';
	$name    = 'dfrcs_options[' . $key . ']';
	$value   = dfrcs_get_option( $key );
	$default = dfrcs_default_options( $key );
	echo '<input type="text" name="' . $name . '" value="' . $value . '"> ' . __( 'results', DFRCS_DOMAIN );
	echo '<p class="description">';
	echo __( 'If the comparison set contains fewer products than this number, the set will not be displayed.', DFRCS_DOMAIN );
	echo '<br />';
	echo '<small>' . __( 'Default: ', DFRCS_DOMAIN ) . $default . '</small>';
	echo '</p>';
}

function dfrcs_display_last_updated() {
	$key     = 'display_last_updated';
	$name    = 'dfrcs_options[' . $key . ']';
	$value   = dfrcs_get_option( $key );
	$default = dfrcs_default_options( $key );

	// Yes
	echo '<label for="dfrcs_display_last_updated_true">';
	echo '<input type="radio" name="' . $name . '" value="1" id="dfrcs_display_last_updated_true"' . checked( '1', $value, false ) . '> Yes' . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
	echo '</label>';

	// No
	echo '<label for="dfrcs_display_last_updated_false">';
	echo '<input type="radio" name="' . $name . '" value="0" id="dfrcs_display_last_updated_false"' . checked( '0', $value, false ) . '> No';
	echo '</label>';

	echo '<p class="description">';
	echo __( 'Display the date and time the comparison set was last updated.', DFRCS_DOMAIN );
	echo '<br />';
	echo '<small>' . __( 'Default: ', DFRCS_DOMAIN );
	echo ( '1' == $default ) ? __( 'Yes', DFRCS_DOMAIN ) : __( 'No', DFRCS_DOMAIN );
	echo '</small></p>';
}

function dfrcs_include_master_product() {
	$key     = 'include_master_product';
	$name    = 'dfrcs_options[' . $key . ']';
	$value   = dfrcs_get_option( $key );
	$default = dfrcs_default_options( $key );

	// Yes
	echo '<label for="dfrcs_include_master_product_true">';
	echo '<input type="radio" name="' . $name . '" value="1" id="dfrcs_include_master_product_true"' . checked( '1', $value, false ) . '> Yes' . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
	echo '</label>';

	// No
	echo '<label for="dfrcs_include_master_product_false">';
	echo '<input type="radio" name="' . $name . '" value="0" id="dfrcs_include_master_product_false"' . checked( '0', $value, false ) . '> No';
	echo '</label>';

	echo '<p class="description">';
	echo __( 'If a single product is used to generate the comparison set, should that product be included in the results?', DFRCS_DOMAIN );
	echo '<br />';
	echo '<small>' . __( 'Default: ', DFRCS_DOMAIN );
	echo ( '1' == $default ) ? __( 'Yes', DFRCS_DOMAIN ) : __( 'No', DFRCS_DOMAIN );
	echo '</small></p>';
}

function dfrcs_title_setting() {
	$key     = 'title';
	$name    = 'dfrcs_options[' . $key . ']';
	$value   = dfrcs_get_option( $key );
	$default = dfrcs_default_options( $key );
	echo '<input type="text" name="' . $name . '" value="' . esc_attr( $value ) . '" class="regular-text">';
	echo '<p class="description">';
	echo __( 'The text to display above the comparison set.', DFRCS_DOMAIN );
	echo '<br />';
	echo __( 'Available placeholders: ', DFRCS_DOMAIN );
	echo '<code>{product_name}</code> <code>{lowest_price}</code> <code>{highest_price}</code> <code>{num_products}</code> <code>{num_merchants}</code>';
	echo '<br />';
	echo '<small>' . __( 'Default: ', DFRCS_DOMAIN ) . $default . '</small>';
	echo '</p>';
}

function dfrcs_link_text_setting() {
	$key     = 'link_text';
	$name    = 'dfrcs_options[' . $key . ']';
	$value   = dfrcs_get_option( $key );
	$default = dfrcs_default_options( $key );
	echo '<input type="text" name="' . $name . '" value="' . esc_attr( $value ) . '">';
	echo '<p class="description">';
	echo __( 'The text to display on the button.', DFRCS_DOMAIN );
	echo '<br />';
	echo '<small>' . __( 'Default: ', DFRCS_DOMAIN ) . $default . '</small>';
	echo '</p>';
}

function dfrcs_loading_text_setting() {
	$key     = 'loading_text';
	$name    = 'dfrcs_options[' . $key . ']';
	$value   = dfrcs_get_option( $key );
	$default = dfrcs_default_options( $key );
	echo '<input type="text" name="' . $name . '" value="' . esc_attr( $value ) . '" class="regular-text">';
	echo '<p class="description">';
	echo __( 'The text to display while the comparison set is loading.', DFRCS_DOMAIN );
	echo '<br />';
	echo '<small>' . __( 'Default: ', DFRCS_DOMAIN ) . $default . '</small>';
	echo '</p>';
}

function dfrcs_min_viewing_cap() {
	$key     = 'min_viewing_cap';
	$name    = 'dfrcs_options[' . $key . ']';
	$value   = dfrcs_get_option( $key );
	$default = dfrcs_default_options( $key );
	$roles   = get_editable_roles();

	echo '<select name="' . $name . '">';
	echo '<option value="" ' . selected( $value, '', false ) . '>' . __( 'None', DFRCS_DOMAIN ) . '</option>';
	foreach ( $roles as $key => $role ) {
		echo '<option value="' . $key . '" ' . selected( $value, $key, false ) . '>' . __( $role['name'], DFRCS_DOMAIN ) . '</option>';
	}
	echo '</select>';

	echo '<p class="description">';
	echo __( 'The minimum user role required to view a comparison set.', DFRCS_DOMAIN );
	echo '<br />';
	echo '<small>' . __( 'Default: ', DFRCS_DOMAIN ) . __( 'None', DFRCS_DOMAIN ) . '</small>';
	echo '</p>';
}

function dfrcs_debug_fields() {
	$key     = 'debug_fields';
	$name    = 'dfrcs_options[' . $key . ']';
	$value   = implode( ', ', dfrcs_get_option( $key ) );
	$default = implode( ', ', dfrcs_default_options( $key ) );
	echo '<input type="text" name="' . $name . '" value="' . esc_attr( $value ) . '" class="large-text">';
	echo '<p class="description">';
	echo __( 'The fields to display to admins when viewing product debug information. Fields should be comma separated.', DFRCS_DOMAIN );
	echo '<br />';
	echo '<small>' . __( 'Default: ', DFRCS_DOMAIN ) . $default . '</small>';
	echo '</p>';
}

function dfrcs_no_results_message_setting() {
	$key     = 'no_results_message';
	$name    = 'dfrcs_options[' . $key . ']';
	$value   = dfrcs_get_option( $key );
	$default = dfrcs_default_options( $key );
	echo '<input type="text" name="' . $name . '" value="' . esc_attr( $value ) . '" class="large-text">';
	echo '<p class="description">';
	echo __( 'The text to display if there are no results in the comparison set.', DFRCS_DOMAIN );
	echo '<br />';
	echo '<small>' . __( 'Default: ', DFRCS_DOMAIN ) . $default . '</small>';
	echo '</p>';
}

function dfrcs_query_by_amazon() {
	$key     = 'query_by_amazon';
	$name    = 'dfrcs_options[' . $key . ']';
	$value   = dfrcs_get_option( $key );
	$default = dfrcs_default_options( $key );

	// Yes
	echo '<label for="dfrcs_query_by_amazon_true">';
	echo '<input type="radio" name="' . $name . '" value="1" id="dfrcs_query_by_amazon_true"' . checked( '1', $value, false ) . '> Yes' . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
	echo '</label>';

	// No
	echo '<label for="dfrcs_query_by_amazon_false">';
	echo '<input type="radio" name="' . $name . '" value="0" id="dfrcs_query_by_amazon_false"' . checked( '0', $value, false ) . '> No';
	echo '</label>';

	// API config page.
	$url = add_query_arg( array( 'page' => 'dfrapi' ), admin_url( 'admin.php' ) );

	echo '<p class="description">';
	echo __( 'Add products from Amazon to your comparison sets. Amazon API keys are required and can be configured ', DFRCS_DOMAIN );
	echo '<a href="' . $url . '">' . __( 'here', DFRCS_DOMAIN ) . '</a>.';
	echo '<br />';
	echo '<strong>' . __( 'Note: ', DFRCS_DOMAIN ) . '</strong>';
	echo __( 'Information from Amazon is also used in building the rest of your comparison sets so it\'s recommended that this is set to "Yes".', DFRCS_DOMAIN );
	echo '<br />';
	echo '<small>' . __( 'Default: ', DFRCS_DOMAIN );
	echo ( '1' == $default ) ? __( 'Yes', DFRCS_DOMAIN ) : __( 'No', DFRCS_DOMAIN );
	echo '</small></p>';
}

function dfrcs_query_by_name() {
	$key     = 'query_by_name';
	$name    = 'dfrcs_options[' . $key . ']';
	$value   = dfrcs_get_option( $key );
	$default = dfrcs_default_options( $key );

	// Yes
	echo '<label for="dfrcs_query_by_name_true">';
	echo '<input type="radio" name="' . $name . '" value="1" id="dfrcs_query_by_name_true"' . checked( '1', $value, false ) . '> Yes' . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
	echo '</label>';

	// No
	echo '<label for="dfrcs_query_by_name_false">';
	echo '<input type="radio" name="' . $name . '" value="0" id="dfrcs_query_by_name_false"' . checked( '0', $value, false ) . '> No';
	echo '</label>';

	echo '<p class="description">';
	echo __( 'Query the Datafeedr API by product name to build your comparison set.', DFRCS_DOMAIN );
	echo '<br />';
	echo '<strong>' . __( 'Note: ', DFRCS_DOMAIN ) . '</strong>';
	echo __( 'It\'s recommended that this is set to "Yes".', DFRCS_DOMAIN );
	echo '<br />';
	echo '<small>' . __( 'Default: ', DFRCS_DOMAIN );
	echo ( '1' == $default ) ? __( 'Yes', DFRCS_DOMAIN ) : __( 'No', DFRCS_DOMAIN );
	echo '</small></p>';
}

function dfrcs_query_by_model() {
	$key     = 'query_by_model';
	$name    = 'dfrcs_options[' . $key . ']';
	$value   = dfrcs_get_option( $key );
	$default = dfrcs_default_options( $key );

	// Yes
	echo '<label for="dfrcs_query_by_model_true">';
	echo '<input type="radio" name="' . $name . '" value="1" id="dfrcs_query_by_model_true"' . checked( '1', $value, false ) . '> Yes' . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
	echo '</label>';

	// No
	echo '<label for="dfrcs_query_by_model_false">';
	echo '<input type="radio" name="' . $name . '" value="0" id="dfrcs_query_by_model_false"' . checked( '0', $value, false ) . '> No';
	echo '</label>';

	echo '<p class="description">';
	echo __( 'Query the Datafeedr API by product model number to build your comparison set.', DFRCS_DOMAIN );
	echo '<br />';
	echo '<strong>' . __( 'Note: ', DFRCS_DOMAIN ) . '</strong>';
	echo __( 'It\'s recommended that this is set to "Yes".', DFRCS_DOMAIN );
	echo '<br />';
	echo '<small>' . __( 'Default: ', DFRCS_DOMAIN );
	echo ( '1' == $default ) ? __( 'Yes', DFRCS_DOMAIN ) : __( 'No', DFRCS_DOMAIN );
	echo '</small></p>';
}

function dfrcs_query_by_barcodes() {
	$key     = 'query_by_barcodes';
	$name    = 'dfrcs_options[' . $key . ']';
	$value   = dfrcs_get_option( $key );
	$default = dfrcs_default_options( $key );

	// Yes
	echo '<label for="dfrcs_query_by_barcodes_true">';
	echo '<input type="radio" name="' . $name . '" value="1" id="dfrcs_query_by_barcodes_true"' . checked( '1', $value, false ) . '> Yes' . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
	echo '</label>';

	// No
	echo '<label for="dfrcs_query_by_barcodes_false">';
	echo '<input type="radio" name="' . $name . '" value="0" id="dfrcs_query_by_barcodes_false"' . checked( '0', $value, false ) . '> No';
	echo '</label>';

	echo '<p class="description">';
	echo __( 'Query the Datafeedr API by product barcodes (ie. UPC, EAN) to build your comparison set.', DFRCS_DOMAIN );
	echo '<br />';
	echo '<strong>' . __( 'Note: ', DFRCS_DOMAIN ) . '</strong>';
	echo __( 'It\'s recommended that this is set to "Yes".', DFRCS_DOMAIN );
	echo '<br />';
	echo '<small>' . __( 'Default: ', DFRCS_DOMAIN );
	echo ( '1' == $default ) ? __( 'Yes', DFRCS_DOMAIN ) : __( 'No', DFRCS_DOMAIN );
	echo '</small></p>';
}

function dfrcs_keyword_accuracy() {
	$key     = 'keyword_accuracy';
	$name    = 'dfrcs_options[' . $key . ']';
	$value   = dfrcs_get_option( $key );
	$default = dfrcs_default_options( $key );

	$options = array( 70, 75, 80, 85, 90, 95, 100 );

	echo '<select name="' . $name . '">';
	foreach ( $options as $option ) {
		echo '<option value="' . $option . '" ' . selected( $value, $option, false ) . '>' . $option . '%</option>';
	}
	echo '</select>';

	echo '<p class="description">';
	echo __( 'The percentage of words that must appear in a product\'s name in order for the product to be included in the comparison set.', DFRCS_DOMAIN );
	echo '<br />';
	echo '<small>' . __( 'Default: ', DFRCS_DOMAIN ) . $default . '%</small>';
	echo '</p>';
}

function dfrcs_exclude_duplicate_fields() {
	$key     = 'exclude_duplicate_fields';
	$name    = 'dfrcs_options[' . $key . ']';
	$value   = implode( ', ', dfrcs_get_option( $key ) );
	$default = implode( ', ', dfrcs_default_options( $key ) );
	echo '<input type="text" name="' . $name . '" value="' . esc_attr( $value ) . '" class="large-text">';
	echo '<p class="description">';
	echo __( 'Exclude duplicates by the following fields. Values should be comma separated.', DFRCS_DOMAIN );
	echo '<br />';
	echo '<small>' . __( 'Default: ', DFRCS_DOMAIN ) . $default . '</small>';
	echo '</p>';
}

function dfrcs_barcode_fields() {
	$key     = 'barcode_fields';
	$name    = 'dfrcs_options[' . $key . ']';
	$value   = implode( ', ', dfrcs_get_option( $key ) );
	$default = implode( ', ', dfrcs_default_options( $key ) );
	echo '<input type="text" name="' . $name . '" value="' . esc_attr( $value ) . '" class="large-text">';
	echo '<p class="description">';
	echo __( 'Use these fields to query based on barcode values. Values should be comma separated.', DFRCS_DOMAIN );
	echo '<br />';
	echo '<small>' . __( 'Default: ', DFRCS_DOMAIN ) . $default . '</small>';
	echo '</p>';
}

function dfrcs_brand_name_stopwords() {
	$key     = 'brand_name_stopwords';
	$name    = 'dfrcs_options[' . $key . ']';
	$value   = implode( ', ', dfrcs_get_option( $key ) );
	$default = implode( ', ', dfrcs_default_options( $key ) );
	echo '<input type="text" name="' . $name . '" value="' . esc_attr( $value ) . '" class="large-text">';
	echo '<p class="description">';
	echo __( 'Words to strip from brand names before querying for matches. Values should be comma separated.', DFRCS_DOMAIN );
	echo '<br />';
	echo '<small>' . __( 'Default: ', DFRCS_DOMAIN ) . $default . '</small>';
	echo '</p>';
}

function dfrcs_mandatory_keywords() {
	$key     = 'mandatory_keywords';
	$name    = 'dfrcs_options[' . $key . ']';
	$value   = implode( ', ', dfrcs_get_option( $key ) );
	$default = implode( ', ', dfrcs_default_options( $key ) );
	echo '<input type="text" name="' . $name . '" value="' . esc_attr( $value ) . '" class="large-text">';
	echo '<p class="description">';
	echo __( 'Prevent these keywords from being ignored when querying for matches. Values should be comma separated.', DFRCS_DOMAIN );
	echo '<br />';
	echo '<small>' . __( 'Default: ', DFRCS_DOMAIN ) . $default . '</small>';
	echo '</p>';
}

function dfrcs_product_name_stopwords() {
	$key     = 'product_name_stopwords';
	$name    = 'dfrcs_options[' . $key . ']';
	$value   = implode( ', ', dfrcs_get_option( $key ) );
	$default = implode( ', ', dfrcs_default_options( $key ) );
	echo '<input type="text" name="' . $name . '" value="' . esc_attr( $value ) . '" class="large-text">';
	echo '<p class="description">';
	echo __( 'Remove these words from a product name before querying for matches. Values should be comma separated.', DFRCS_DOMAIN );
	echo '<br />';
	echo '<small>' . __( 'Default: ', DFRCS_DOMAIN ) . $default . '</small>';
	echo '</p>';
}

function dfrcs_options_validate( $input ) {

	// Cache Lifetime
	$newinput['cache_lifetime'] = intval( $input['cache_lifetime'] );
	if ( $newinput['cache_lifetime'] <= 0 ) {
		$newinput['cache_lifetime'] = dfrcs_default_options( 'cache_lifetime' );
	}

	// Maximum API Requests
	$newinput['max_api_requests'] = intval( $input['max_api_requests'] );
	if ( $newinput['max_api_requests'] <= 0 ) {
		$newinput['max_api_requests'] = dfrcs_default_options( 'max_api_requests' );
	}

	// Integrations
	if ( isset( $input['integrations'] ) && is_array( $input['integrations'] ) ) {
		$newinput['integrations'] = array_unique( $input['integrations'] );
	} else {
		$newinput['integrations'] = array();
	}

	// Display Method
	if ( in_array( $input['display_method'], array( 'php', 'data' ) ) ) {
		$newinput['display_method'] = $input['display_method'];
	} else {
		$newinput['display_method'] = dfrcs_default_options( 'display_method' );
	}

	// Minimum Number of Results
	$newinput['minimum_num_products'] = intval( $input['minimum_num_products'] );
	if ( $newinput['minimum_num_products'] <= 0 ) {
		$newinput['minimum_num_products'] = dfrcs_default_options( 'minimum_num_products' );
	}

	// Display Last Updated
	if ( isset( $input['display_last_updated'] ) && ( '1' == $input['display_last_updated'] ) ) {
		$newinput['display_last_updated'] = '1';
	} else {
		$newinput['display_last_updated'] = '0';
	}

	// Include Master Product
	if ( isset( $input['include_master_product'] ) && ( '1' == $input['include_master_product'] ) ) {
		$newinput['include_master_product'] = '1';
	} else {
		$newinput['include_master_product'] = '0';
	}

	// Title (can be empty)
	$newinput['title'] = trim( $input['title'] );

	// Link Text
	$newinput['link_text'] = trim( $input['link_text'] );
	if ( empty( $newinput['link_text'] ) ) {
		$newinput['link_text'] = dfrcs_default_options( 'link_text' );
	}

	// Loading Text
	$newinput['loading_text'] = trim( $input['loading_text'] );
	if ( empty( $newinput['loading_text'] ) ) {
		$newinput['loading_text'] = dfrcs_default_options( 'loading_text' );
	}

	// Minimum Viewing Capability
	$newinput['min_viewing_cap'] = trim( $input['min_viewing_cap'] );

	// Debug Fields
	$newinput['debug_fields'] = trim( $input['debug_fields'] );
	if ( empty( $newinput['debug_fields'] ) ) {
		$newinput['debug_fields'] = implode( ', ', dfrcs_default_options( 'debug_fields' ) );
	}
	$newinput['debug_fields'] = str_replace( " ", "", $newinput['debug_fields'] );
	$newinput['debug_fields'] = explode( ",", $newinput['debug_fields'] );
	$newinput['debug_fields'] = array_filter( array_unique( $newinput['debug_fields'] ) );

	// No Results Message
	$newinput['no_results_message'] = trim( $input['no_results_message'] );

	// Query Amazon
	if ( isset( $input['query_by_amazon'] ) && ( '1' == $input['query_by_amazon'] ) ) {
		$newinput['query_by_amazon'] = '1';
	} else {
		$newinput['query_by_amazon'] = '0';
	}

	// Query by Name
	if ( isset( $input['query_by_name'] ) && ( '1' == $input['query_by_name'] ) ) {
		$newinput['query_by_name'] = '1';
	} else {
		$newinput['query_by_name'] = '0';
	}

	// Query by Model Number
	if ( isset( $input['query_by_model'] ) && ( '1' == $input['query_by_model'] ) ) {
		$newinput['query_by_model'] = '1';
	} else {
		$newinput['query_by_model'] = '0';
	}
	// Query by Barcodes
	if ( isset( $input['query_by_barcodes'] ) && ( '1' == $input['query_by_barcodes'] ) ) {
		$newinput['query_by_barcodes'] = '1';
	} else {
		$newinput['query_by_barcodes'] = '0';
	}

	// Keyword Accuracy
	if ( in_array( $input['keyword_accuracy'], array( 70, 75, 80, 85, 90, 95, 100 ) ) ) {
		$newinput['keyword_accuracy'] = $input['keyword_accuracy'];
	} else {
		$newinput['keyword_accuracy'] = dfrcs_default_options( 'keyword_accuracy' );
	}

	// Exclude Duplicate Fields
	$newinput['exclude_duplicate_fields'] = trim( $input['exclude_duplicate_fields'] );
	if ( empty( $newinput['exclude_duplicate_fields'] ) ) {
		$newinput['exclude_duplicate_fields'] = array();
	} else {
		$newinput['exclude_duplicate_fields'] = str_replace( " ", "", $newinput['exclude_duplicate_fields'] );
		$newinput['exclude_duplicate_fields'] = explode( ",", $newinput['exclude_duplicate_fields'] );
		$newinput['exclude_duplicate_fields'] = array_filter( array_unique( $newinput['exclude_duplicate_fields'] ) );
	}

	// Barcode Fields
	$newinput['barcode_fields'] = trim( $input['barcode_fields'] );
	if ( empty( $newinput['barcode_fields'] ) ) {
		$newinput['barcode_fields'] = implode( ', ', dfrcs_default_options( 'barcode_fields' ) );
	}
	$newinput['barcode_fields'] = str_replace( " ", "", $newinput['barcode_fields'] );
	$newinput['barcode_fields'] = explode( ",", $newinput['barcode_fields'] );
	$newinput['barcode_fields'] = array_filter( array_unique( $newinput['barcode_fields'] ) );

	// Brand Name Stopwords
	$newinput['brand_name_stopwords'] = trim( $input['brand_name_stopwords'] );
	if ( empty( $newinput['brand_name_stopwords'] ) ) {
		$newinput['brand_name_stopwords'] = implode( ', ', dfrcs_default_options( 'brand_name_stopwords' ) );
	}
	$newinput['brand_name_stopwords'] = str_replace( " ", "", $newinput['brand_name_stopwords'] );
	$newinput['brand_name_stopwords'] = explode( ",", $newinput['brand_name_stopwords'] );
	$newinput['brand_name_stopwords'] = array_filter( array_unique( $newinput['brand_name_stopwords'] ) );

	// Mandatory Keywords
	$newinput['mandatory_keywords'] = trim( $input['mandatory_keywords'] );
	if ( empty( $newinput['mandatory_keywords'] ) ) {
		$newinput['mandatory_keywords'] = implode( ', ', dfrcs_default_options( 'mandatory_keywords' ) );
	}
	$newinput['mandatory_keywords'] = str_replace( " ", "", $newinput['mandatory_keywords'] );
	$newinput['mandatory_keywords'] = explode( ",", $newinput['mandatory_keywords'] );
	$newinput['mandatory_keywords'] = array_filter( array_unique( $newinput['mandatory_keywords'] ) );

	// Product Name Stopwords
	$newinput['product_name_stopwords'] = trim( $input['product_name_stopwords'] );
	if ( empty( $newinput['product_name_stopwords'] ) ) {
		$newinput['product_name_stopwords'] = implode( ', ', dfrcs_default_options( 'product_name_stopwords' ) );
	}
	$newinput['product_name_stopwords'] = str_replace( " ", "", $newinput['product_name_stopwords'] );
	$newinput['product_name_stopwords'] = explode( ",", $newinput['product_name_stopwords'] );
	$newinput['product_name_stopwords'] = array_filter( array_unique( $newinput['product_name_stopwords'] ) );

	return $newinput;
}

function dfrcs_add_products_output() {

	if ( ! isset( $_GET['hash'] ) ) {
		_e( 'Missing comparison set hash.', DFRCS_DOMAIN );

		return;
	}

	$compset = dfrcs_select( $_GET['hash'] );
	$hash    = $_GET['hash'];

	if ( ! $compset ) {
		_e( 'Invalid comparison set.', DFRCS_DOMAIN );

		return;
	}

	if ( ! empty( $compset['last_query'] ) ) {
		$last_query = unserialize( $compset['last_query'] );
	} else {
		$last_query = array( 'field' => 'any', 'value' => '' );
	}

	?>

	<div id="dfrcs_search_form_wrapper" class="stuffbox">

		<p><strong><?php _e( 'Search for products to add to this comparison set.', DFRCS_DOMAIN ); ?></strong></p>

		<form>
			<?php
			$sform = new Dfrapi_SearchForm();
			echo $sform->render( '_dfrcs_query', $last_query );
			?>
		</form>

		<div class="actions">
			<span class="dfrcs_raw_query">
				<a href="#" id="dfrcs_view_raw_query"><?php _e( 'view api request', DFRCS_DOMAIN ); ?></a>
			</span>
			<input type="hidden" name="dfrcs_hash" id="dfrcs_hash" value="<?php echo $hash; ?>"/>
			<input name="search" type="submit" class="button" id="dfrcs_search"
			       value="<?php echo __( 'Search', DFRCS_DOMAIN ); ?>"/>
		</div>
		<div id="div_dfrcs_search_results"></div>

	</div>
	<?php
}

/**
 * Hide admin menus and display search from. This is strictly for pop-ups.
 */
add_action( 'admin_head', 'dfrcs_hide_admin_interface' );
function dfrcs_hide_admin_interface() {
	if ( isset( $_GET['page'] ) && ( 'dfrcs_add_products' == $_GET['page'] ) ) { ?>
		<style type="text/css">
			html,
			html.wp-toolbar,
			#wpbody,
			#wpcontent {
				margin: 0 !important;
				padding: 0 !important;
				background: #fff !important;
			}

			.stuffbox {
				border: 0 !important;
				box-shadow: none;
			}

		</style>
		<script type="text/javascript">
			jQuery(document).ready(function ($) {
				$("#wpwrap > #adminmenumain").remove();
				$("#wpwrap > #wpcontent > #wpadminbar").remove();
				$("#wpbody-content > #dfrcs_search_form_wrapper").prevAll().remove();
				$("#wpwrap > #wpfooter").remove();
			});
		</script>
		<?php
	}
}


/**
 * Include integration files and add action so others can include their own.
 */
add_action( 'init', 'dfrcs_include_integration_files' );
function dfrcs_include_integration_files() {
	$integrations = dfrcs_get_option( 'integrations' );
	if ( ! empty( $integrations ) ) {
		foreach ( $integrations as $integration ) {
			require_once( DFRCS_PATH . 'integrations/' . $integration . '.php' );
		}
	}
}

/**
 * Enqueue private JS and CSS
 */
add_action( 'admin_enqueue_scripts', 'dfrcs_admin_enqueue_scripts', 9999999 );
function dfrcs_admin_enqueue_scripts() {

	wp_enqueue_style( 'dfrcs_compsets_style', DFRCS_URL . 'css/style-admin.css', array(), DFRCS_VERSION );

	if ( isset( $_GET['page'] ) && ( 'dfrcs_add_products' == $_GET['page'] ) ) {

		wp_enqueue_script( 'dfrcs_search_js', DFRCS_URL . 'js/search.js', array( 'jquery' ), DFRCS_VERSION, false );

		// Localize the script with new data
		$translation_array = array(
			'ajax_url'           => admin_url( 'admin-ajax.php' ),
			'nonce'              => wp_create_nonce( 'dfrcs_ajax_nonce' ),
			'searching'          => __( 'Searching...', DFRCS_DOMAIN ),
			'search'             => __( 'Search', DFRCS_DOMAIN ),
			'searching_products' => __( 'Searching products...', DFRCS_DOMAIN ),
		);
		wp_localize_script( 'dfrcs_search_js', 'dfrcs', $translation_array );
	}
}

/**
 * Enqueue public Javascript & CSS.
 */
add_action( 'wp_enqueue_scripts', 'dfrcs_enqueue_scripts', 9999999 );
function dfrcs_enqueue_scripts() {

	// Javascript
	wp_enqueue_script( 'dfrcs_compsets', DFRCS_URL . 'js/compsets.js', array( 'jquery' ), DFRCS_VERSION, false );

	// CSS
	wp_enqueue_style( 'dfrcs_compsets_style', DFRCS_URL . 'css/style.css', array(), DFRCS_VERSION );

	// Dynamic CSS
	$loading_text = dfrcs_get_option( 'loading_text' );
	$data         = '.dfrcs_loading:after { content: "' . $loading_text . '"; }';
	$data         = wp_kses( $data, array( "\'", '\"' ) );
	wp_add_inline_style( 'dfrcs_compsets_style', $data );

	// Google Fonts
	wp_enqueue_style( 'dfrcs_google_fonts', 'https://fonts.googleapis.com/css?family=Roboto:400,700', false );

	// Admin CSS
	if ( is_admin() ) {
		wp_enqueue_style( 'dfrcs_compsets_style', DFRCS_URL . 'css/style-admin.css', array(), DFRCS_VERSION );
	}

	// Localize the script with new data
	$translation_array = array(
		'ajax_url'         => admin_url( 'admin-ajax.php' ),
		'nonce'            => wp_create_nonce( 'dfrcs_ajax_nonce' ),
		'post_id'          => ( get_the_ID() ) ? (string) get_the_ID() : 0,
		'remove_product'   => __( 'Remove Product', DFRCS_DOMAIN ),
		'unremove_product' => __( 'Restore Product', DFRCS_DOMAIN ),
	);
	wp_localize_script( 'dfrcs_compsets', 'dfrcs', $translation_array );
}

add_action( 'wp_ajax_dfrcs_output_compset_ajax', 'dfrcs_output_compset_ajax' );
add_action( 'wp_ajax_nopriv_dfrcs_output_compset_ajax', 'dfrcs_output_compset_ajax' );
function dfrcs_output_compset_ajax() {

	check_ajax_referer( 'dfrcs_ajax_nonce', 'dfrcs_security' );

	$request = $_REQUEST;
	$source  = $request['source'];
	$source  = base64_decode( $source );
	$source  = unserialize( $source );

	$source['display_method'] = 'ajax';

	$html = dfrcs_compset( $source );
	echo $html;
	die();
}

add_action( 'wp_ajax_dfrcs_refresh_compset_ajax', 'dfrcs_refresh_compset_ajax' );
function dfrcs_refresh_compset_ajax() {

	check_ajax_referer( 'dfrcs_ajax_nonce', 'dfrcs_security' );

	$request = $_REQUEST;

	if ( ! isset( $request['hash'] ) || empty( $request['hash'] ) ) {
		die();
	}

	$hash = str_replace( "#hash_", "", $request['hash'] );
	dfrcs_refresh_compset( $hash );

	$source = $request['source'];
	$source = base64_decode( $source );
	$source = unserialize( $source );

	$source['display_method'] = 'ajax';

	$html = dfrcs_compset( $source );

	echo $html;
	die();
}

/**
 * @todo - do the following
 *
 * For the functions dfrcs_restore_product() and dfrcs_remove_product(), we need to follow this logic:
 *
 * - We need to set _auto = 1 for any product added to a set automatically.
 * - Then we will have 4 special product keys: _auto, _added, _removed, _excluded
 * - We will also have 1 special key set: _display
 *
 *
 * Auto Added products - This product was auto added. Remove it.
 *    _auto=1
 *    _display=1
 *
 * Auto Added product manually removed - This product was automatically added and manually removed. Restore it.
 *    _auto=1
 *    _removed=1
 *    _display=0
 *
 * Auto added product automatically excluded - This product was automatically removed based on settings. Restore it.
 *    _auto=1
 *    _excluded=1
 *    _display=0
 *
 * Manually added product - This product was manually added. Remove it.
 *    _auto=0
 *    _added=1
 *    _display=0
 *
 * Manually added product removed
 * The product should not exist in the "added" or "removed" DB columns.
 *
 */

add_action( 'wp_ajax_dfrcs_remove_product', 'dfrcs_remove_product' );
function dfrcs_remove_product() {

	check_ajax_referer( 'dfrcs_ajax_nonce', 'dfrcs_security' );

	global $wpdb;

	$request = $_REQUEST;

	if ( ! isset( $request['hash'] ) || empty( $request['hash'] ) ) {
		die();
	}

	if ( ! isset( $request['pid'] ) || empty( $request['pid'] ) ) {
		die();
	}

	$hash   = str_replace( "#hash_", "", $request['hash'] );
	$pid    = trim( $request['pid'] );
	$status = $request['status'];

	$compset = dfrcs_select( $hash );

	if ( ! $compset ) {
		echo '';
		die;
	}

	// Handle the "removed" array.
	$removed = unserialize( $compset['removed'] );
	if ( ! $removed ) {
		$removed = array();
	}

	// Handle the "added" array.
	$added = unserialize( $compset['added'] );
	if ( ! $added ) {
		$added = array();
	}

	// If product was added automatically, add it to the "removed" column.
	if ( "true" == $status['auto'] ) {
		$removed[] = $pid;
		$removed   = array_filter( array_unique( $removed ) );
	}

	// If product was added manually, remove it from the "added" column.
	if ( "true" == $status['added'] && "false" == $status['auto'] ) {
		if ( ( $key = array_search( $pid, $added ) ) !== false ) {
			unset( $added[ $key ] );
		}
		$added = array_filter( array_unique( $added ) );
	}

	$updated = '1970-01-01 00:00:00';
	$table   = $wpdb->prefix . DFRCS_TABLE;

	$wpdb->update(
		$table,
		array(
			'removed' => serialize( $removed ),
			'added'   => serialize( $added ),
			'updated' => $updated,
		),
		array( 'hash' => $hash ),
		array(
			'%s',
			'%s',
		),
		array( '%s' )
	);

	echo '';
	die();
}

add_action( 'wp_ajax_dfrcs_restore_product', 'dfrcs_restore_product' );
function dfrcs_restore_product() {

	check_ajax_referer( 'dfrcs_ajax_nonce', 'dfrcs_security' );

	global $wpdb;

	$request = $_REQUEST;

	if ( ! isset( $request['hash'] ) || empty( $request['hash'] ) ) {
		die();
	}

	if ( ! isset( $request['pid'] ) || empty( $request['pid'] ) ) {
		die();
	}

	$hash   = str_replace( "#hash_", "", $request['hash'] );
	$pid    = trim( $request['pid'] );
	$status = $request['status'];

	$compset = dfrcs_select( $hash );

	if ( ! $compset ) {
		echo '';
		die;
	}

	// Get removed IDs.
	$removed = unserialize( $compset['removed'] );
	if ( ! $removed ) {
		$removed = array();
	}

	// Get added IDs.
	$added = unserialize( $compset['added'] );
	if ( ! $added ) {
		$added = array();
	}

	// If product has been removed but was originally added automatically, remove from "removed" column,
	if ( "true" == $status['removed'] && "true" == $status['auto'] ) {
		$key = array_search( $pid, $removed );
		if ( $key !== false ) {
			unset( $removed[ $key ] );
		}

		$removed = array_filter( array_unique( $removed ) );
	}

	// If product has been excluded but was originally added automatically, add it to the "added" column to force it to appear.
	if ( "true" == $status['excluded'] && "true" == $status['auto'] ) {
		$added[] = $pid;
		$added   = array_filter( array_unique( $added ) );
	}

	$updated = '1970-01-01 00:00:00';
	$table   = $wpdb->prefix . DFRCS_TABLE;

	$wpdb->update(
		$table,
		array(
			'removed' => serialize( $removed ),
			'added'   => serialize( $added ),
			'updated' => $updated,
		),
		array( 'hash' => $hash ),
		array(
			'%s',
			'%s',
		),
		array( '%s' )
	);

	echo '';
	die();
}

add_action( 'wp_ajax_dfrcs_add_product', 'dfrcs_add_product' );
function dfrcs_add_product() {

	check_ajax_referer( 'dfrcs_ajax_nonce', 'dfrcs_security' );

	global $wpdb;

	$request = $_REQUEST;

	if ( ! isset( $request['hash'] ) || empty( $request['hash'] ) ) {
		die();
	}

	if ( ! isset( $request['pid'] ) || empty( $request['pid'] ) ) {
		die();
	}

	$hash = str_replace( "#hash_", "", $request['hash'] );
	$pid  = trim( $request['pid'] );

	$compset = dfrcs_select( $hash );

	if ( ! $compset ) {
		echo '';
		die;
	}

	$added = unserialize( $compset['added'] );

	if ( ! $added ) {
		$added = array();
	}

	$added[] = $pid;
	$added   = array_filter( array_unique( $added ) );
	$updated = '1970-01-01 00:00:00';
	$table   = $wpdb->prefix . DFRCS_TABLE;

	$wpdb->update(
		$table,
		array(
			'added'   => serialize( $added ),
			'updated' => $updated,
		),
		array( 'hash' => $hash ),
		array(
			'%s',
			'%s',
		),
		array( '%s' )
	);

	echo 'done';
	die;
}

/**
 * Add a shortcode.
 * [dfrcs name="Women’s Marmot Jena Vest" brand="Marmot" title="Best Prices for Jena Vest"]
 *
 */
add_shortcode( 'dfrcs', 'dfrcs_shortcode' );
function dfrcs_shortcode( $atts ) {

	$post_type = ( $type = get_post_type() ) ? '_' . $type : '';

	$defaults = array(
		'context' => 'shortcode' . $post_type,
		'post_id' => get_the_ID(),
	);

	$source = wp_parse_args( $atts, $defaults );

	return dfrcs_compset( $source );
}


/**
 * Add debug CSS and JS script to head.
 */
add_action( 'wp_head', 'dfrcs_add_debug_scripts_to_head' );
function dfrcs_add_debug_scripts_to_head() {    // Don't show if not admin.

	if ( ! dfrcs_can_manage_compset() ) {
		return '';
	}

	// Add CSS.
	echo '
	<style type="text/css">
	.dfrcs_compset_debug { display: none; }
	</style>
	';

	// Add JS
	echo '
	<script>
	jQuery(document).ready(function( $ ) {
		$( "button" ).click(function() {
  			$( ".dfrcs_compset_debug" ).toggle( "slow" );
		});
	});
	</script>
	';
}


add_action( 'wp_ajax_dfrcs_ajax_get_products', 'dfrcs_ajax_get_products' );
function dfrcs_ajax_get_products() {

	check_ajax_referer( 'dfrcs_ajax_nonce', 'dfrcs_security' );

	if ( ! isset( $_REQUEST['hash'] ) ) {
		echo 'no hash';
		die;
	}

	$hash = $_REQUEST['hash'];

	// Get products that should be excluded in the search.
	$compset = dfrcs_select( $hash );
	$removed = ( ! empty( $compset['removed'] ) ) ? unserialize( $compset['removed'] ) : array();
	$removed = array_filter( $removed, 'is_numeric' ); // Remove non-numeric IDs (ie. Amazon IDs)

	// Isolate the query
	if ( isset ( $_REQUEST['query'] ) ) {
		parse_str( $_REQUEST['query'], $query );
	}

	// Query API.
	if ( ! empty( $query['_dfrcs_query'] ) ) {
		$data = dfrapi_api_get_products_by_query( $query['_dfrcs_query'], 100, 1, $removed );
	}

	// Print any errors.
	if ( is_array( $data ) && array_key_exists( 'dfrapi_api_error', $data ) ) {
		echo dfrapi_output_api_error( $data );
		die;
	}

	if ( isset( $data['params'] ) && ! empty( $data['params'] ) ) {
		echo '<div class="dfrcs_api_info" id="dfrcs_raw_api_query">';
		echo '<div class="dfrcs_head">' . __( 'API Request', DFRCS_DOMAIN ) . '</div>';
		echo '<div class="dfrcs_query"><span>' . dfrapi_display_api_request( $data['params'] ) . '</span></div>';
		echo '</div>';
	}

	// Save the query so the form is filled in by default.
	dfrcs_update_last_query( $hash, $query['_dfrcs_query'] );

	if ( ! empty( $data['products'] ) ) {
		echo dfrcs_display_search_results( $data['products'], $hash );
	}

	die;
}




