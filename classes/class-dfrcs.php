<?php

/**
 * Public methods.
 * $compset->create();
 * $compset->display();
 */
class Dfrcs {

	// Public Properties
	public $source; // Object
	public $cached = false;
	public $cache_checked = false;
	public $cache_is_expired = true;
	public $date_created = '1970-01-01 00:00:00';
	public $date_updated = '1970-01-01 00:00:00';
	public $num_products = 0;
	public $num_requests = 0;
	public $num_merchants = 0;
	public $lowest_priced_product = array();
	public $highest_priced_product = array();
	public $log = array();
	public $products = array();
	public $args = array();
	public $data = array();
	public $removed = array();
	public $added = array();
	public $errors = array();

	// Private Properties
	private $display_method = 'data';
	private $template;
	private $context;
	private $css_class;
	private $css_id;
	private $encoded_source;

	public function __construct( $source ) {

		if ( empty( $source ) || ! $source ) {
			$this->log( 'ERROR', 'Source ($source) is empty or does not exist.' );

			return;
		}

		$this->set_source( $source );
		$this->set_encoded_source();
		$this->set_arguments();
		$this->set_cache();
		$this->set_context();
		$this->set_css_class();
		$this->set_css_id();
		$this->set_template();
		$this->set_display_method();

	}

	private function set_cache() {

		if ( $this->cache_checked ) {
			return;
		}

		$this->cached           = dfrcs_select( $this->source->hash );
		$this->cache_is_expired = ( $this->cache_is_expired( $this->cached['updated'] ) ) ? true : false;

		$this->removed      = unserialize( $this->cached['removed'] );
		$this->added        = unserialize( $this->cached['added'] );
		$this->date_updated = $this->cached['updated'];
		$this->date_created = $this->cached['created'];

		if ( $this->cached && ! $this->cache_is_expired ) {

			$this->products = unserialize( $this->cached['products'] );
			$this->products = $this->filter_products();
			$this->log      = unserialize( $this->cached['log'] );

			$this->set_num_products();
			$this->set_num_merchants();
			$this->set_lowest_price();
			$this->set_highest_price();

			$this->log( 'compset/cached', 'Yes' );

		}

		$this->cache_checked = true;
	}

	/**
	 * This creates the compset.
	 *
	 * If there is a cached version and it's not expired, this will not create a compset.
	 * Also, if the display method is 'data', this will not create a compset.
	 *
	 * @since 0.9.0
	 *
	 * @return null This method does not return anything.
	 */
	public function create() {

		// If cache is set and not expired, all required values are already set.
		if ( $this->cached && ! $this->cache_is_expired ) {
			return;
		}

		// If current site visitor is a bot, just return. Don't create Comparison Sets for bots.
		if ( dfrcs_visitor_is_bot() ) {
			return;
		}

		// Don't run queries if display method is "data".
		if ( 'data' == $this->display_method ) {
			return;
		}

		if ( $this->source->id ) {
			$source = $this->get_product_from_source_id( $this->source->id );
			if ( $source ) {
				$this->source->build( $source );
			} else {
				$this->log( 'ERROR', __( 'No source data. Only an ID which does not correspond to any product.', DFRCS_DOMAIN ) );

				return;
			}
		}

		try {
			$this->query_amazon();
			$this->query_by_barcodes();
			$this->query_by_model();
			$this->query_by_name();
			$this->add_custom_products();
		} catch ( DatafeedrError $exception ) {
			$this->log( 'ERROR', __( 'DatafeedrError Exception: ' . print_r( $exception, true ), DFRCS_DOMAIN ) );
			$this->errors[] = $exception;

			return;
		}

		$this->products = $this->filter_products();

		$this->set_num_products();
		$this->set_num_merchants();
		$this->set_lowest_price();
		$this->set_highest_price();

		$this->log( 'removed_ids', $this->removed );

		$this->source->set_final_source();
		$this->log( 'final_source', $this->source->final );

		$this->log_compset_details();
		$this->log( 'compset/cached', 'No' );

		if ( $this->cached ) {
			$this->date_updated = current_time( 'mysql' );
			$this->log( 'compset/date_updated', $this->date_updated );
			$this->update();
		} else {
			$this->date_created = current_time( 'mysql' );
			$this->date_updated = current_time( 'mysql' );
			$this->log( 'compset/date_created', $this->date_created );
			$this->log( 'compset/date_updated', $this->date_updated );
			$this->insert();
		}

		do_action( 'dfrcs_compset_creation_complete', $this );
	}

	/**
	 * Returns the HTML to display on the frontend.
	 *
	 * This returns an empty string if the compset should not be displayed (for various reasons).
	 * This also returns an empty string if the display method is not of an expected type.
	 * This also returns the value of display_data() if the display_method is 'data'.
	 *
	 * @since 0.9.0
	 *
	 * @return string $html Returns $html for display.
	 */
	public function display() {

		$html = '';

		if ( $msg = $this->dont_display_compset() ) {
			if ( ! dfrcs_can_manage_compset() ) {
				return $html;
			} else {
				$this->log( 'compset/IMPORTANT', $msg );
			}
		}

		if ( ! empty( $this->errors ) ) {
			if ( dfrcs_can_manage_compset() ) {
				$html = '<div><strong>This compset is not displaying because the following DatafeedrError exception was thrown: </strong>';
				$html .= '<pre>' . print_r( $this->errors, true ) . '</pre></div>';
			} else {
				$html = ( ! empty( $no_results_message = dfrcs_no_results_message() ) ) ?
					'<div class="dfrcs_no_results_message">' . esc_html( $no_results_message ) . '</div>' :
					'';
			}

			return $html;
		}

		if ( ! in_array( $this->display_method, array( 'data', 'ajax', 'php' ) ) ) {
			return $html;
		}

		// Querying data isn't necessary if we are just loading "data" tags.
		if ( 'data' == $this->display_method ) {
			$func = 'display_' . $this->display_method;
			$html .= $this->$func();

			return $html;
		}

		$func = 'display_' . $this->display_method;
		$html .= $this->$func();

		return $html;
	}

	private function display_php() {
		$html = $this->display_open_html_wrapper();
		ob_start();
		include( $this->template );
		$html .= ob_get_contents();
		ob_end_clean();
		$html .= $this->display_date();
		$html .= $this->display_log();
		$html .= $this->display_close_html_wrapper();

		return $html;
	}

	private function display_data() {
		$html = $this->display_open_html_wrapper();
		$html .= $this->display_close_html_wrapper();

		return $html;
	}

	private function display_ajax() {
		$html = '';
		ob_start();
		include( $this->template );
		$html .= ob_get_contents();
		ob_end_clean();
		$html .= $this->display_date();
		$html .= $this->display_log();

		return $html;
	}

	private function display_admin_actions() {

		$html = '';

		if ( ! dfrcs_can_manage_compset() ) {
			return $html;
		}

		// Add products URL
		$url = add_query_arg( array( 'page' => 'dfrcs_add_products', 'hash' => $this->source->hash ), admin_url() );

		$html .= '<div class="dfrcs_compset_actions">';
		$html .= '<strong>' . __( 'Compset Admin', DFRCS_DOMAIN ) . '</strong>';
		$html .= '<a href="#" class="action refresh" title="' . __( 'Refresh this comparison set', DFRCS_DOMAIN ) . '">' . __( 'Refresh Cache', DFRCS_DOMAIN ) . '</a>';
		$html .= '<a href="#" class="action debug" title="' . __( 'View information about this comparison set.', DFRCS_DOMAIN ) . '">' . __( 'View Debug', DFRCS_DOMAIN ) . '</a>';
		$html .= '<a href="' . $url . '" class="action dfrcs_add" target="_blank" name="compsetWindow" title="' . __( 'Add additional products to this comparison set.', DFRCS_DOMAIN ) . '">' . __( 'Add Products', DFRCS_DOMAIN ) . '</a>';
		$html .= '<a href="#" class="action manage" title="' . __( 'Remove (or restore) products from this comparison set.', DFRCS_DOMAIN ) . '">' . __( 'Manage Products', DFRCS_DOMAIN ) . '</a>';
		$html .= '</div>';

		$html .= '<script type="text/javascript"> jQuery(function ($) { $(".dfrcs_add").popupWindow({ centerBrowser:1,height:600,width:640, }); }); </script>';

		return $html;
	}

	private function display_log() {

		$html = '';

		if ( ! dfrcs_can_manage_compset() ) {
			return $html;
		}

		$html .= '<div class="dfrcs_compset_debug"><pre>';
		$html .= print_r( $this->log, true );
		$html .= '</pre></div>';

		return $html;
	}

	private function set_display_method() {

		if ( isset( $this->source->original['display_method'] ) ) {
			$display_method = $this->source->original['display_method']; // Most likely, this will be 'ajax'.
		} else {
			$display_method = ( $this->cached ) ? dfrcs_get_option( 'display_method' ) : 'data'; // either 'php' or 'data'
		}

		$display_method = apply_filters( 'dfrcs_display_method', $display_method, $this );

		$this->display_method = $display_method;
	}

	private function set_encoded_source() {
		$source               = $this->source->original;
		$source               = serialize( $source );
		$source               = base64_encode( $source );
		$this->encoded_source = $source;
	}

	private function set_context() {
		$context       = ( isset( $this->source->original['context'] ) ) ? $this->source->original['context'] : 'default';
		$this->context = apply_filters( 'dfrcs_context', $context, $this );
	}

	private function set_css_class() {
		$this->css_class = apply_filters( 'dfrcs_class', 'dfrcs_' . $this->context, $this );
	}

	private function set_css_id() {
		$this->css_id = 'hash_' . $this->source->hash;
	}

	private function filter_products() {

		$filtered_products = $all_products = $this->products;

		/**
		 * Sort the products.
		 *
		 * Do this first so additional filters (below) remove the desired products from the compset.
		 */
		$order             = apply_filters( 'dfrcs_order', 'asc', $this );
		$orderby           = apply_filters( 'dfrcs_orderby', 'finalprice', $this );
		$filtered_products = dfrcs_sort_products( $filtered_products, $orderby, $order );

		// Exclude duplicates
		$fields            = dfrcs_get_option( 'exclude_duplicate_fields' );
		$filtered_products = dfrcs_exclude_products_by_fields( $filtered_products, $fields );

		// Exclude master product from result set.
		$include_master = dfrcs_get_option( 'include_master_product' );
		if ( ! $include_master && ! empty ( $this->source->id ) ) {
			$master_id = 'id_' . $this->source->id;

			$filtered_products[ $master_id ]['_excluded'] = 1;
		}

		/**
		 * Set '_added' for products manually added to compset.
		 * Set '_removed' for products manually removed from compset.
		 */
		foreach ( $filtered_products as $product ) {

			if ( in_array( $product['_id'], (array) $this->added ) ) {
				$filtered_products[ 'id_' . $product['_id'] ]['_added'] = 1;
			}

			if ( in_array( $product['_id'], (array) $this->removed ) ) {
				$filtered_products[ 'id_' . $product['_id'] ]['_removed'] = 1;
			}
		}

		/**
		 * Set "_display" status to 0 or 1 depending on special keys.
		 *
		 * Special Keys
		 *
		 * _added - a product that was added using the search form
		 * _removed - a product that was removed by clicking the "remove" link.
		 * _excluded - a product that was excluded from the set because of the 'master product' or 'exclude dups' rule.
		 */
		foreach ( $filtered_products as $product ) {
			if ( isset( $product['_added'] ) && 1 == $product['_added'] ) {
				$filtered_products[ 'id_' . $product['_id'] ]['_display'] = 1;
			} elseif ( isset( $product['_excluded'] ) && 1 == $product['_excluded'] ) {
				$filtered_products[ 'id_' . $product['_id'] ]['_display'] = 0;
			} elseif ( isset( $product['_removed'] ) && 1 == $product['_removed'] ) {
				$filtered_products[ 'id_' . $product['_id'] ]['_display'] = 0;
			} else {
				$filtered_products[ 'id_' . $product['_id'] ]['_display'] = 1;
			}

			if ( ! isset( $product['merchant_id'] ) || empty( $product['merchant_id'] ) ) {
				$filtered_products[ 'id_' . $product['_id'] ]['_display'] = 0;
			}
		}

		// Return filtered products array.
		return apply_filters( 'dfrcs_filter_products', $filtered_products, $all_products );
	}

	private function set_num_products() {

		$i = 0;

		if ( ! empty( $this->products ) ) {
			foreach ( $this->products as $product ) {
				if ( 1 == $product['_display'] ) {
					$i ++;
				}
			}
		}

		$this->num_products = $i;
	}

	private function set_num_merchants() {

		$merchant_names = array();

		if ( ! empty( $this->products ) ) {
			foreach ( $this->products as $product ) {
				if ( 1 == $product['_display'] ) {
					$merchant_names[] = $product['merchant'];
				}
			}
		}

		$merchant_names = array_unique( $merchant_names );

		$this->num_merchants = count( $merchant_names );
	}

	private function set_template() {

		$dir = get_stylesheet_directory() . '/dfrcs-templates/';

		$paths = array(
			$dir . $this->context . '.php',
			$dir . 'default.php',
			DFRCS_PATH . 'templates/default.php',
		);

		foreach ( $paths as $path ) {
			if ( file_exists( $path ) ) {
				$template = $path;
				break;
			}
		}

		$this->log( 'template_options', $paths );
		$this->log( 'current_template', $template );

		$template = apply_filters( 'dfrcs_template', $template, $this );

		$this->template = $template;
	}

	/**
	 * Sets the $this->lowest_price property to the product with the lowest price.
	 */
	private function set_lowest_price() {
		$orderby  = 'finalprice';
		$order    = 'asc';
		$products = dfrcs_sort_products( $this->products, $orderby, $order );
		foreach ( $products as $product ) {
			if ( $product['finalprice'] > 0 && $product['_display'] == 1 ) {
				$this->lowest_priced_product = $product;
				break;
			}
		}
	}

	private function set_highest_price() {
		$orderby  = 'finalprice';
		$order    = 'desc';
		$products = dfrcs_sort_products( $this->products, $orderby, $order );
		foreach ( $products as $product ) {
			if ( $product['finalprice'] > 0 ) {
				$this->highest_priced_product = $product;
				break;
			}
		}
	}

	private function display_open_html_wrapper() {

		if ( 'php' == $this->display_method ) {
			$method = 'dfrcs_php';
		} elseif ( 'data' == $this->display_method ) {
			$method = 'dfrcs_ajax';
		} else {
			$method = '';
		}

		$classes = $method . ' ' . $this->css_class;
		$html    = '<div class="dfrcs">';
		$html    .= "<div class='dfrcs_inner $classes' data-dfrcs='$this->encoded_source' id='$this->css_id'>";

		return $html;
	}

	private function display_close_html_wrapper() {
		$html = '</div>'; // <div class='dfrcs_inner dfrcs_php'>
		$html .= $this->display_admin_actions();
		$html .= '</div>'; // <div class='dfrcs'>

		return $html;
	}

	private function display_date() {

		$html = '';

		if ( ! $this->meets_min_num_product_requirement() && ! dfrcs_can_manage_compset() ) {
			return $html;
		}

		$html .= '<div class="dfrcs_last_updated">';
		if ( dfrcs_get_option( 'display_last_updated' ) ) {
			$html .= __( 'Last updated', DFRCS_DOMAIN ) . ': ';
			$html .= $this->date_updated;
		}
		$html .= '</div>';

		return $html;
	}

	private function set_source( $source ) {
		$source_obj = new Dfrcs_Source( $source );
		$source_obj->build( $source );
		$this->source = $source_obj;
		$this->log( 'compset', array() );
		$this->log( 'hash', $this->source->hash );
		$this->log( 'original_source', $this->source->original );
	}

	private function set_arguments() {

		$defaults = array();
		$keys     = dfrcs_get_default_arg_keys();

		foreach ( $keys as $key ) {
			$defaults[ $key ] = dfrcs_get_option( $key );
		}

		$args       = wp_parse_args( $this->source->original, $defaults );
		$args       = apply_filters( 'dfrcs_arguments', $args, $this );
		$this->args = $args;
	}

	private function dont_display_compset() {

		$min_viewing_cap = dfrcs_get_option( 'min_viewing_cap' );

		if ( ! current_user_can( $min_viewing_cap ) && ! empty( $min_viewing_cap ) ) {
			return __( 'This compset is not displaying to non-admins because of the "min_viewing_cap" restriction.', DFRCS_DOMAIN );
		}

		if ( dfrcs_visitor_is_bot() ) {
			return __( 'This compset is not displaying to bots, crawlers or search engines.', DFRCS_DOMAIN );
		}

		// add do_action here to allow overriding based on other criteria.
		return false;
	}

	private function cache_is_expired( $updated ) {
		$expiry_ts  = current_time( 'timestamp' ) - dfrcs_get_option( 'cache_lifetime' );
		$updated_ts = strtotime( $updated );
		if ( $updated_ts < $expiry_ts ) {
			return true;
		}

		return false;
	}

	/**
	 * Inserts the comp set into the cache table.
	 *
	 * @since 0.9.0
	 *
	 * @global object $wpdb WordPress DB Object.
	 */
	private function insert() {

		global $wpdb;

		$table = $wpdb->prefix . DFRCS_TABLE;

		$wpdb->insert(
			$table,
			array(
				'hash'         => $this->source->hash,
				'products'     => serialize( $this->products ),
				'log'          => serialize( $this->log ),
				'num_requests' => $this->num_requests,
				'created'      => $this->date_created,
				'updated'      => $this->date_updated,
			),
			array(
				'%s',
				'%s',
				'%s',
				'%d',
				'%s',
				'%s',
			)
		);
	}

	/**
	 * Updates the comp set with new data.
	 *
	 * @since 0.9.0
	 *
	 * @global object $wpdb WordPress DB Object.
	 */
	private function update() {

		global $wpdb;

		$table = $wpdb->prefix . DFRCS_TABLE;

		$wpdb->update(
			$table,
			array(
				'products'     => serialize( $this->products ),
				'log'          => serialize( $this->log ),
				'num_requests' => $this->num_requests,
				'updated'      => $this->date_updated,
			),
			array( 'hash' => $this->source->hash ),
			array(
				'%s',
				'%s',
				'%d',
				'%s',
			),
			array( '%s' )
		);
	}

	private function log_compset_details() {
		$this->log( 'compset/cached', ( $this->cached ) ? 'Yes' : 'No' );
		$this->log( 'compset/date_created', $this->date_created );
		$this->log( 'compset/date_updated', $this->date_updated );
		$this->log( 'compset/num_products', $this->num_products );
		$this->log( 'compset/num_merchants', $this->num_merchants );
		$this->log( 'compset/num_requests', $this->num_requests );
		$this->log( 'compset/lowest_priced_product', $this->lowest_priced_product );
		$this->log( 'compset/highest_priced_product', $this->highest_priced_product );
	}

	private function get_product_from_source_id( $id ) {

		// Try to get locally stored product.
		$dfrps_product = $this->query_by_dfrps_product( $id );
		if ( $dfrps_product ) {
			$dfrps_source = array_values( $this->products );
			$dfrps_source = array_shift( $dfrps_source );

			return $dfrps_source;
		}

		// There is no locally stored product info so query the Datafeedr API for product info.
		$dfrapi_product = $this->query_by_id( $id );
		if ( $dfrapi_product ) {
			$dfrapi_source = array_values( $this->products );
			$dfrapi_source = array_shift( $dfrapi_source );

			return $dfrapi_source;
		}

		// There's no product that matches so return false.
		return false;
	}

	/**
	 * Query Datafeedr API for product with a specific ID.
	 *
	 * Query the Datafeedr API for product with a specific ID. We use this when the user has supplied the 'id' value.
	 *
	 * If products are found, products are added to the $this->products property and then the
	 * method returns true.
	 *
	 * @since 0.9.0
	 *
	 * @param string $id A unique Datafeedr product ID.
	 *
	 * @return boolean true if products are found, false if no query was run or no products were found.
	 */
	private function query_by_dfrps_product( $id ) {

		// If product ID doesn't exist, return false.
		if ( empty( $id ) ) {
			return false;
		}

		// This means the DFRPS plugin is not even installed and _dfrps_product is not available.
		if ( ! function_exists( 'dfrps_get_post_obj_by_postmeta' ) ) {
			return false;
		}

		// Attempt to get product data stored in postmeta table.
		$post = dfrps_get_post_obj_by_postmeta( '_dfrps_product_id', $id, 'IN' );
		if ( $post ) {
			$this->log( 'query_by_dfrps_product/product_id', $id );

			$product = get_post_meta( $post->ID, '_dfrps_product', true );
		}

		// We found product data in the postmeta table so add that array to $this->products.
		if ( ! empty( $product ) ) {

			$this->log( 'query_by_dfrps_product/response_count', 1 );
			$this->log( 'query_by_dfrps_product/response', $product );

			$product['_auto']              = 1;
			$this->products[ 'id_' . $id ] = $product;

			return true;
		}

		$this->log( 'query_by_dfrps_product/response', __( 'No Results', DFRCS_DOMAIN ) );

		return false;
	}

	/**
	 * Query Datafeedr API for product with a specific ID.
	 *
	 * Query the Datafeedr API for product with a specific ID. We use this when the user has supplied the 'id' value.
	 *
	 * If products are found, products are added to the $this->products property and then the
	 * method returns true.
	 *
	 * @since 0.9.0
	 *
	 * @param string $id A unique Datafeedr product ID.
	 *
	 * @return boolean true if products are found, false if no query was run or no products were found.
	 */
	private function query_by_id( $id ) {

		// If product ID doesn't exist, return false.
		if ( empty( $id ) ) {
			return false;
		}

		if ( $this->too_many_api_requests() ) {
			$this->log( 'query_by_id', __( 'Halted. Reached API request limit of ', DFRCS_DOMAIN ) . $this->too_many_api_requests() . '.' );

			return false;
		}

		// Build and send API request .
		$request = array();

		$request[] = array(
			'field'    => 'id',
			'operator' => 'is',
			'value'    => $id,
		);

		$response = dfrapi_api_get_products_by_query( $request, 1 );

		$this->num_requests ++;

		$this->log( 'query_by_id/api_request', $response['params'] );

		// The API returned 0 results so return empty $products array().
		if ( ! isset( $response['products'] ) || empty( $response['products'] ) ) {
			$this->log( 'query_by_id/api_response', __( 'No Results', DFRCS_DOMAIN ) );

			return false;
		}

		$this->log( 'query_by_id/api_response_count', count( $response['products'] ) );
		$this->log( 'query_by_id/api_response', dfrcs_products_debug( $response['products'] ) );

		// Loop through products and set the "_id" as the array key like "id_1234567890".
		foreach ( $response['products'] as $p ) {
			$p['_auto']                          = 1;
			$this->products[ 'id_' . $p['_id'] ] = $p;
		}

		return true;
	}

	/**
	 * Query Amazon for a matching product.
	 *
	 * Here we query the Amazon API using the Datafeedr API wrapper for one matching product for this comp set.
	 *
	 * A few things to note...
	 *
	 * $fields is an array of the types of queries we should perform. Example: barcodes or name. We first try to
	 * query Amazon by barcodes because it will usually return more accurate results. However, if no barcodes exist
	 * then we query Amazon by name.
	 *
	 * Another thing about this method is that if we are provided with barcodes and a barcodes query returns 0 results,
	 * then the method will be run again (via the continue; statement) and a query based on the name will be run.
	 *
	 * Therefore, this method has the possibility of generating 2+ API queries.
	 *
	 * If products are found, only the first product is added to the $this->products property and then the
	 * method returns true to prevent any other queries being run.
	 *
	 * @since 0.9.0
	 *
	 * @param array $fields An array of fields to query the Amazon API on. Default: 'barcodes' and 'name'.
	 *
	 * @return boolean true if products are found, false if no query was run or no products were found.
	 */
	private function query_amazon() {

		$param = false;

		$fields = array( 'asin', 'isbn', 'barcodes', 'name' );

		if ( ! dfrcs_get_option( 'query_by_amazon' ) ) {
			return;
		}

		// Check if Amazon API keys exist and return if they do not.
		if ( ! function_exists( 'dfrapi_get_amazon_keys' ) || ! $amazon = dfrapi_get_amazon_keys() ) {
			$this->log( 'query_amazon/result', __( 'Halted. No Amazon Keys available.', DFRCS_DOMAIN ) );

			return;
		}

		if ( empty( $fields ) ) {
			return;
		}

		foreach ( $fields as $field ) {

			// ASIN
			if ( isset( $this->source->original['asin'] ) && 'asin' == $field ) {
				$param = $this->source->original['asin'];
			}

			// ISBN
			if ( isset( $this->source->original['isbn'] ) && 'isbn' == $field ) {
				$param = $this->source->original['isbn'];
			}

			// EAN, UPC or other unique identifiers.
			if ( ! empty( $this->source->barcodes ) && 'barcodes' == $field ) {
				$param = $this->get_ean13( $this->source->barcodes );
			}

			// Product Name
			if ( ! empty( $this->source->name ) && 'name' == $field ) {
				$param = ( ! empty( $this->source->brand ) ) ? $this->source->brand . ' ' . $this->source->name : $this->source->name;
			}

			if ( ! $param ) {
				continue;
			}

			// too many queries.
			if ( $this->too_many_api_requests() ) {
				$this->log( 'query_amazon_by_' . $field . '/result', __( 'Halted. Reached API request limit of ', DFRCS_DOMAIN ) . $this->too_many_api_requests() . '.' );

				return;
			}

			$api    = dfrapi_api( dfrapi_get_transport_method() );
			$locale = ( isset( $this->source->filters['amazon_locale'] ) ) ? $this->source->filters['amazon_locale'] : $amazon['amazon_locale'];
			$search = $api->amazonSearchRequest( $amazon['amazon_access_key_id'], $amazon['amazon_secret_access_key'], $amazon['amazon_tracking_id'], $locale );
			$search->addParam( 'Keywords', $param );

			$this->log( 'query_amazon_by_' . $field . '/api_request/locale', $locale );
			$this->log( 'query_amazon_by_' . $field . '/api_request/keyword', $param );

			// Add MinimumPrice filter.
			if ( isset( $this->source->filters['finalprice_min'] ) ) {
				$search->addParam( 'MinimumPrice', $this->source->filters['finalprice_min'] );
				$this->log( 'query_amazon_by_' . $field . '/api_request/finalprice_min', $this->source->filters['finalprice_min'] );
			}

			// Add MaximumPrice filter.
			if ( isset( $this->source->filters['finalprice_max'] ) ) {
				$search->addParam( 'MaximumPrice', $this->source->filters['finalprice_max'] );
				$this->log( 'query_amazon_by_' . $field . '/api_request/finalprice_max', $this->source->filters['finalprice_max'] );
			}

			$response = $search->execute();

			$this->num_requests ++;

			// No results so try again using the next item in the $fields array.
			if ( empty( $response ) ) {
				$this->log( 'query_amazon_by_' . $field . '/api_response', __( 'No Results', DFRCS_DOMAIN ) );
				continue;
			}

			// Just get and process the first product returned (ie. most relevant)
			$product = $response[0];

			$this->log( 'query_amazon_by_' . $field . '/api_response_count', count( $product ) );
			$this->log( 'query_amazon_by_' . $field . '/api_response', $product );

			// Hardcode 'asin' field.
			$asin = ( isset( $product['asin'] ) ) ? $product['asin'] : $product['suid'];

			// Add additional model numbers &/or barcodes but only if this product has not been removed.
			if ( ! in_array( $asin, (array) $this->removed ) ) {
				$this->source->build( $product );
			} else {
				$this->log( 'query_amazon_by_' . $field . '/removed', $asin );
			}

			$id = $asin;

			// Add product to $this->products array().
			$this->products[ 'id_' . $id ]                = $product;
			$this->products[ 'id_' . $id ]['merchant']    = 'Amazon ' . strtoupper( $locale );
			$this->products[ 'id_' . $id ]['_id']         = $asin;
			$this->products[ 'id_' . $id ]['merchant_id'] = '7777';
			$this->products[ 'id_' . $id ]['finalprice']  = ( isset( $product['saleprice'] ) ) ? $product['saleprice'] : $product['price'];
			$this->products[ 'id_' . $id ]['_auto']       = 1;

			break; // Break out of foreach ( $fields as $field ) { since we have our product.
		}
	}

	/**
	 * Query Datafeedr API for products with matching barcodes.
	 *
	 * Query the Datafeedr API for products with matching barcodes. $barcodes may contain many unique values. They
	 * will be imploded for the query.
	 *
	 * If products are found, products are added to the $this->products property and then the
	 * method returns true.
	 *
	 * @since 0.9.0
	 *
	 * @param array $barcodes An array of unique codes like EAN, UPC, ISBN or ASIN values.
	 *
	 * @return boolean true if products are found, false if no query was run or no products were found.
	 */
	private function query_by_barcodes() {

		if ( ! dfrcs_get_option( 'query_by_barcodes' ) ) {
			return;
		}

		if ( empty( $this->source->barcodes ) ) {
			return;
		}

		if ( $this->too_many_api_requests() ) {
			$this->log( 'query_by_barcodes', __( 'Halted. Reached API request limit of ', DFRCS_DOMAIN ) . $this->too_many_api_requests() . '.' );

			return;
		}

		$this->log( 'query_by_barcodes/barcodes', $this->source->barcodes );

		$request = array();

		$request[] = array(
			'field'    => 'any',
			'operator' => 'contain',
			'value'    => implode( "|", $this->source->barcodes ),
		);

		$request[] = array(
			'field'    => 'sort',
			'operator' => '+finalprice'
		);

		$request[] = array(
			'field'    => 'duplicates',
			'operator' => 'is',
			'value'    => 'merchant_id',
		);

		$request = $this->apply_query_filters( $request );

		$request = apply_filters( 'dfrcs_query_by_barcodes', $request, $this );

		$response = dfrapi_api_get_products_by_query( $request, 100 );

		$this->num_requests ++;

		$this->log( 'query_by_barcodes/api_request', $response['params'] );

		// The API returned 0 results so return empty $products array().
		if ( ! isset( $response['products'] ) || empty( $response['products'] ) ) {
			$this->log( 'query_by_barcodes/api_response', __( 'No Results', DFRCS_DOMAIN ) );

			return;
		}

		$this->log( 'query_by_barcodes/api_response_count', count( $response['products'] ) );
		$this->log( 'query_by_barcodes/api_response', dfrcs_products_debug( $response['products'] ) );

		// Loop through products and set the "_id" as the array key like "id_1234567890".
		foreach ( $response['products'] as $p ) {
			$p['_auto']                          = 1;
			$this->products[ 'id_' . $p['_id'] ] = $p;
		}
	}

	/**
	 * Query Datafeedr API for products with matching model numbers.
	 *
	 * Query the Datafeedr API for products with matching model numbers. $model may contain many values. They
	 * will be imploded for the query.
	 *
	 * Additionally, if the $source also contains a brand value, that will be used in the query.
	 *
	 * If products are found, products are added to the $this->products property and then the
	 * method returns true.
	 *
	 * @since 0.9.0
	 *
	 * @param array $model An array of possible model numbers.
	 *
	 * @return boolean true if products are found, false if no query was run or no products were found.
	 */
	private function query_by_model() {

		if ( ! dfrcs_get_option( 'query_by_model' ) ) {
			return;
		}

		if ( empty( $this->source->model ) ) {
			return;
		}

		if ( $this->too_many_api_requests() ) {
			$this->log( 'query_by_model', __( 'Halted. Reached API request limit of ', DFRCS_DOMAIN ) . $this->too_many_api_requests() . '.' );

			return;
		}

		$this->log( 'query_by_model/model', $this->source->model );

		$request = array();

		if ( ! empty( $this->source->brand ) ) {
			$request[] = array(
				'field'    => 'any',
				'operator' => 'match',
				'value'    => $this->source->brand
			);
		}

		$request[] = array(
			'field'    => 'name',
			'operator' => 'contain',
			'value'    => implode( "|", $this->source->model ),
		);

		$request[] = array(
			'field'    => 'sort',
			'operator' => '+finalprice'
		);

		$request[] = array(
			'field'    => 'duplicates',
			'operator' => 'is',
			'value'    => 'merchant_id',
		);

		$request = $this->apply_query_filters( $request );

		$request = apply_filters( 'dfrcs_query_by_model', $request, $this );

		$response = dfrapi_api_get_products_by_query( $request, 100 );

		$this->num_requests ++;

		$this->log( 'query_by_model/api_request', $response['params'] );

		// The API returned 0 results so return empty $products array().
		if ( ! isset( $response['products'] ) || empty( $response['products'] ) ) {
			$this->log( 'query_by_model/api_response', __( 'No Results', DFRCS_DOMAIN ) );

			return;
		}

		$this->log( 'query_by_model/api_response_count', count( $response['products'] ) );
		$this->log( 'query_by_model/api_response', dfrcs_products_debug( $response['products'] ) );

		// Loop through products and set the "_id" as the array key like "id_1234567890".
		foreach ( $response['products'] as $p ) {
			$p['_auto']                          = 1;
			$this->products[ 'id_' . $p['_id'] ] = $p;
		}
	}

	/**
	 * Query Datafeedr API for products with matching the product name.
	 *
	 * Query the Datafeedr API for products with matching the product's name. The brand is extracted from the name
	 * as well as other stopwords to help create a high quality, keyword dense search string.
	 *
	 * Additionally, if $brand is available, it will be a mandatory search term. Same with other 'mandatory_keywords'.
	 *
	 * If products are found, products are added to the $this->products property and then the
	 * method returns true.
	 *
	 * @since 0.9.0
	 *
	 * @param string $name The product's name.
	 * @param string $brand Optional. The product's brand.
	 *
	 * @return boolean true if products are found, false if no query was run or no products were found.
	 */
	private function query_by_name() {

		if ( ! dfrcs_get_option( 'query_by_name' ) ) {
			return;
		}

		if ( empty( $this->source->name ) ) {
			return;
		}

		if ( $this->too_many_api_requests() ) {
			$this->log( 'query_by_name', __( 'Halted. Reached API request limit of ', DFRCS_DOMAIN ) . $this->too_many_api_requests() . '.' );

			return;
		}

		$name  = $this->source->name;
		$brand = $this->source->brand;

		$this->log( 'query_by_name/name', $name );
		$this->log( 'query_by_name/brand', $brand );

		// Parse $brand_name if it exists.
		if ( isset( $brand ) && ! empty ( $brand ) ) {

			// Allow user to perform custom parsing on brand name before we do our own.
			$brand_name = apply_filters( 'dfrcs_pre_parse_brand_name', $brand, $this );

			// Sanitize brand name and explode on "-" delimiter.
			$brand_name = explode( "-", sanitize_title( $brand_name ) );

			// Remove brand name stopwords from brand name.
			$brand_name = array_diff( $brand_name, dfrcs_get_option( 'brand_name_stopwords' ) );

			// Convert brand name array into a string again.
			$brand_name = implode( " ", $brand_name );

			// Allow user to perform custom parsing on brand name after we do our own.
			$brand_name = apply_filters( 'dfrcs_post_parse_brand_name', $brand_name, $this );
		}


		// Allow user to perform custom parsing on product name before we do our own.
		$product_name = apply_filters( 'dfrcs_pre_parse_product_name', $name, $this );

		// Replace /, ( and ) with empty space.
		$product_name = str_replace( array( "/", "(", ")" ), " ", $product_name );

		// Remove all single letters from product name. @http://stackoverflow.com/a/5572799
		$product_name = preg_replace( '/\b\D\b\s?/', ' ', $product_name );

		// Remove brand name from product name to get a higher density of product name keywords.
		$product_name = ( ! empty( $brand_name ) ) ? str_ireplace( $brand_name, " ", $product_name ) : $product_name;

		// Sanitize product name and explode on "-" delimiter. Remove duplicate values.
		$product_name = array_unique( explode( "-", sanitize_title( $product_name ) ) );

		// Now we get words that we MUST use in our API query. These words will be excluded from the quorum search.
		$mandatory_keywords = array_intersect( $product_name, dfrcs_get_option( 'mandatory_keywords' ) );

		// Convert array of $mandatory_keywords into a string if array is not empty.
		$mandatory_keywords = ( ! empty( $mandatory_keywords ) ) ? implode( " ", $mandatory_keywords ) . ' ' : '';

		// Remove $mandatory_keywords from the product name. These will be included.
		$product_name = array_diff( $product_name, dfrcs_get_option( 'mandatory_keywords' ) );

		// Remove product name stopwords from product name.
		$product_name = array_diff( $product_name, dfrcs_get_option( 'product_name_stopwords' ) );

		// Get word count and set up quorum search.
		$word_count = count( $product_name );
		$quorum     = round( $word_count * ( dfrcs_get_option( 'keyword_accuracy' ) / 100 ) );

		// Convert product name into a string again.
		$product_name = implode( " ", $product_name );

		// Allow user to perform custom parsing on product name after we do our own.
		$product_name = apply_filters( 'dfrcs_post_parse_product_name', $product_name, $this );

		// Build and send API request .
		$request = array();

		if ( ! empty( $brand_name ) ) {
			$request[] = array(
				'field'    => 'any',
				'operator' => 'match',
				'value'    => $brand_name
			);
		}

		$product_name = ( ! empty( $product_name ) ) ? '"' . $product_name . '"~' . $quorum : '';
		if ( ! empty( $mandatory_keywords ) || ! empty( $product_name ) ) {
			$request[] = array(
				'field'    => 'name',
				'operator' => 'match',
				'value'    => $mandatory_keywords . $product_name,
			);
		}

		$request[] = array(
			'field'    => 'sort',
			'operator' => '+finalprice'
		);

		$request[] = array(
			'field'    => 'duplicates',
			'operator' => 'is',
			'value'    => 'merchant_id',
		);

		$request = $this->apply_query_filters( $request );

		$request = apply_filters( 'dfrcs_query_by_name', $request, $this );

		$response = dfrapi_api_get_products_by_query( $request, 100 );

		$this->num_requests ++;

		$this->log( 'query_by_name/api_request', $response['params'] );

		// The API returned 0 results so return empty $products array().
		if ( ! isset( $response['products'] ) || empty( $response['products'] ) ) {
			$this->log( 'query_by_name/api_response', __( 'No Results', DFRCS_DOMAIN ) );

			return;
		}

		$this->log( 'query_by_name/api_response_count', count( $response['products'] ) );
		$this->log( 'query_by_name/api_response', dfrcs_products_debug( $response['products'] ) );

		// Loop through products and set the "_id" as the array key like "id_1234567890".
		foreach ( $response['products'] as $p ) {
			$p['_auto']                          = 1;
			$this->products[ 'id_' . $p['_id'] ] = $p;
		}
	}

	private function apply_query_filters( $request ) {

		$filters = $this->source->filters;

		foreach ( $filters as $k => $v ) {

			if ( preg_match( "/_min/i", $k ) ) {

				// Handle minimum prices
				$request[] = array(
					'field'    => substr( $k, 0, - 4 ),
					'operator' => 'gt',
					'value'    => $v,
				);

			} elseif ( preg_match( "/_max/i", $k ) ) {

				// Handle maximum prices
				$request[] = array(
					'field'    => substr( $k, 0, - 4 ),
					'operator' => 'lt',
					'value'    => $v,
				);

			} elseif ( 'currency' == $k ) {

				// Handle currency field.
				$request[] = array(
					'field'    => $k,
					'operator' => 'is',
					'value'    => $v,
				);

			} else {

				// Handle other fields.
				$request[] = array(
					'field'    => $k,
					'operator' => ( '1' == $v ) ? 'yes' : 'no',
				);
			}
		}

		return $request;
	}

	private function add_custom_products() {

		if ( empty( $this->added ) ) {
			$this->log( 'add_custom_products/results', __( 'No product IDs.', DFRCS_DOMAIN ) );

			return;
		}

		$response = dfrapi_api_get_products_by_id( array_filter( $this->added, 'is_numeric' ), 100 );

		if ( empty( $response['products'] ) ) {
			$this->log( 'add_custom_products/api_response', __( 'No Results', DFRCS_DOMAIN ) );

			return;
		}

		$this->log( 'add_custom_products/product_ids', $this->added );
		$this->log( 'add_custom_products/api_response_count', count( $response['products'] ) );
		$this->log( 'add_custom_products/api_response', dfrcs_products_debug( $response['products'] ) );

		foreach ( $response['products'] as $p ) {

			$p['_added'] = '1';

			$this->products[ 'id_' . $p['_id'] ] = $p;
		}
	}

	/**
	 * Returns the 13 character EAN value from barcodes array.
	 *
	 * @since 0.9.0
	 *
	 * @param array $barcodes An array of unique codes such as EAN, UPC, ASIN and ISBN.
	 *
	 * @return string The first 13 character long code from $barcodes.
	 */
	private function get_ean13( $barcodes ) {
		if ( is_array( $barcodes ) && ! empty( $barcodes ) ) {
			foreach ( $barcodes as $barcode ) {
				if ( strlen( $barcode ) == 13 ) {
					return $barcode;
				}
			}
		}

		return false;
	}

	private function too_many_api_requests() {
		$max_api_requests = dfrcs_get_option( 'max_api_requests' );
		if ( $this->num_requests >= $max_api_requests ) {
			return $max_api_requests;
		}

		return false;
	}

	public function meets_min_num_product_requirement() {
		$min_products_required = dfrcs_get_option( 'minimum_num_products' );
		if ( $this->num_products >= $min_products_required ) {
			return true;
		}

		return false;
	}

	private function log( $path, $val ) {
		$msg       = dfrcs_explode_tree( array( $path => $val ) );
		$this->log = array_replace_recursive( $this->log, $msg );
	}
}

