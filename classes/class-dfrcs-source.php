<?php


class Dfrcs_Source {

	public $hash = '';
	public $id = false; // int or bool
	public $name = '';
	public $brand = '';
	public $model = array();
	public $barcodes = array();
	public $filters = array();
	public $original = array();
	public $final = array();

	public function __construct( $source ) {
		$this->set_id( $source );
		$this->set_original_source( $source );
		$this->set_hash( $source );
	}

	private function set_id( $source ) {
		$this->id = ( ( ! empty( $source['id'] ) ) ) ? sanitize_text_field( $source['id'] ) : false;
	}

	private function set_original_source( $source ) {
		$this->original = $source;
	}

	private function set_hash( $source ) {

		$allowed_source_fields = dfrcs_get_default_source_keys();

		foreach ( $source as $k => $v ) {
			if ( ! in_array( $k, $allowed_source_fields ) ) {
				unset( $source[ $k ] );
			}
		}

		$source     = array_map( 'strval', $source );
		$source     = dfrcs_sort_source( $source );
		$this->hash = md5( serialize( $source ) );
	}

	public function set_final_source() {
		$this->final = array(
			'id'       => $this->id,
			'name'     => $this->name,
			'brand'    => $this->brand,
			'model'    => $this->model,
			'barcodes' => $this->barcodes,
			'filters'  => $this->filters,
		);
	}

	// No DB queries required. DB queries will happen in Dfrcs class.
	public function build( $source ) {

		$this->set_name( $source );
		$this->set_brand( $source );
		$this->set_model();
		$this->set_barcodes( $source );
		$this->set_filters( $source );
		$this->set_final_source();
	}

	private function set_name( $source ) {
		if ( empty ( $this->name ) ) {
			$this->name = ( isset( $source['name'] ) ) ? trim( $source['name'] ) : '';
		}
	}

	private function set_brand( $source ) {
		if ( empty ( $this->brand ) ) {
			$brand       = ( isset( $source['brand'] ) ) ? trim( $source['brand'] ) : '';
			$this->brand = apply_filters( 'dfrcs_source_set_brand', $brand, $this );
		}
	}

	private function set_model() {

		$name = str_replace( $this->brand, '', $this->name );
		$name = str_replace( '/', ' ', $name );

		preg_match_all( '/(?<=\s(?=\S*\d)(?=\S*[A-Z]))([A-Z\d]+-)*[A-Z\d]+(?=\s)/', " $name ", $matches );
		$min_len = apply_filters( 'dfrcs_model_number_min_len', 6 );

		if ( ! empty( $matches[0] ) ) {
			// Sort by string-length descending.
			usort( $matches[0], function ( $a, $b ) {
				return strlen( $b ) - strlen( $a );
			} );

			// Return longest match.
			foreach ( $matches[0] as $model ) {
				$model = trim( $model );
				$model = trim( $model, '-' );
				if ( strlen( $model ) >= $min_len ) {
					$this->model[] = $model;
					break;
				}
			}
		}

		$this->model = array_filter( array_unique( $this->model ) );
	}

	private function set_barcodes( $source ) {

		$barcodes       = array();
		$barcode_fields = dfrcs_get_option( 'barcode_fields' );

		foreach ( $barcode_fields as $barcode_field ) {
			$barcodes[] = ( isset( $source[ $barcode_field ] ) ) ? trim( $source[ $barcode_field ] ) : '';
		}

		$barcodes = array_filter( array_unique( $barcodes ) );

		foreach ( $barcodes as $code ) {
			if ( strlen( $code ) > 9 ) {
				$code = ltrim( $code, '0' );
				$max  = ( 14 - strlen( $code ) );
				for ( $i = 0; $i <= $max; $i ++ ) { // Prepend with leading zeros.
					$this->barcodes[] = str_repeat( '0', $i ) . $code;
				}
			}
		}

		$this->barcodes[] = ( isset( $source['asin'] ) ) ? trim( $source['asin'] ) : '';
		$this->barcodes[] = ( isset( $source['isbn'] ) ) ? trim( $source['isbn'] ) : '';
		$this->barcodes   = array_filter( array_unique( $this->barcodes ) );
	}

	private function set_filters( $source ) {

		if ( ! isset( $source['filters'] ) || empty( $source['filters'] ) ) {
			return;
		}

		parse_str( $source['filters'], $filter_args );

		$valid_filters = dfrcs_valid_filters();

		$filters = array_intersect_key( $filter_args, array_flip( $valid_filters ) );

		/* Not needed, the search function converts the price automatically.
		// Convert prices to int values for all "price" fields.
		foreach ( $filters as $k => $v ) {
			if ( preg_match( "/price/i", $k ) ) {
				$filters[ $k ] = dfrapi_price_to_int( $v );
			}
		}
		*/

		$this->filters = $filters;
	}


}



















