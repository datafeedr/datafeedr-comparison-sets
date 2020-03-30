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
