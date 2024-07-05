<?php
/*
Plugin Name: Datafeedr Comparison Sets
Plugin URI: https://www.datafeedr.com
Description: Automatically create price comparison sets for your WooCommerce products or by using a shortcode.
Author: datafeedr.com
Author URI: https://www.datafeedr.com
Text Domain: datafeedr-comparison-sets
License: GPL v3
Requires PHP: 7.4
Requires at least: 3.8
Tested up to: 6.6-RC2
Version: 0.9.71

WC requires at least: 3.0
WC tested up to: 9.0

Datafeedr Comparison Sets Plugin
Copyright (C) 2024, Datafeedr - help@datafeedr.com

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * Exit if accessed directly
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Define constants.
 */
define( 'DFRCS_VERSION', '0.9.71' );
define( 'DFRCS_DB_VERSION', '0.9.0' );
define( 'DFRCS_URL', plugin_dir_url( __FILE__ ) );
define( 'DFRCS_PATH', plugin_dir_path( __FILE__ ) );
define( 'DFRCS_BASENAME', plugin_basename( __FILE__ ) );
define( 'DFRCS_PLUGIN_FILE', __FILE__ ); // /absolute/path/to/wp-content/plugins/datafeedr-comparison-sets/datafeedr-comparison-sets.php
define( 'DFRCS_DOMAIN', 'datafeedr-comparison-sets' );
define( 'DFRCS_PREFIX', 'dfrcs' );
define( 'DFRCS_TABLE', 'dfrcs_compsets' );

/**
 * Load upgrade file.
 */
require_once dirname( DFRCS_PLUGIN_FILE ) . '/includes/upgrade.php';

/**
 * Require classes.
 */
require_once dirname( DFRCS_PLUGIN_FILE ) . '/classes/class-datafeedr-plugin-dependency.php';
require_once dirname( DFRCS_PLUGIN_FILE ) . '/classes/class-dfrcs.php';
require_once dirname( DFRCS_PLUGIN_FILE ) . '/classes/class-dfrcs-source.php';

/**
 * Include functions file.
 */
require_once dirname( DFRCS_PLUGIN_FILE ) . '/includes/functions.php';

/**
 * Include backend functions file while in /wp-admin.
 */
if ( is_admin() ) {
	require_once dirname( DFRCS_PLUGIN_FILE ) . '/includes/functions-admin.php';
}

/**
 * Require actions and filters.
 */
require_once dirname( DFRCS_PLUGIN_FILE ) . '/includes/actions.php';
require_once dirname( DFRCS_PLUGIN_FILE ) . '/includes/filters.php';

/**
 * Declaring WooCommerce HPOS compatibility.
 *
 * @see https://github.com/woocommerce/woocommerce/wiki/High-Performance-Order-Storage-Upgrade-Recipe-Book
 *
 * @since 0.9.70
 */
add_action( 'before_woocommerce_init', function () {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
} );

/**
 * Do initial set up.
 *
 * 1. Add default options to options table.
 * 2. Create compsets database table.
 *
 * @since 0.9.0
 *
 */
register_activation_hook( __FILE__, 'dfrcs_activate' );
function dfrcs_activate( bool $network_wide ) {

	// Check that minimum WordPress requirement has been met.
	$version = get_bloginfo( 'version' );
	if ( version_compare( $version, '3.8', '<' ) ) {
		deactivate_plugins( DFRCS_BASENAME );
		wp_die( __(
			'The Datafeedr Comparison Sets Plugin could not be activated because it requires WordPress version 3.8 or greater. Please upgrade your installation of WordPress.',
			'datafeedr-comparison-sets'
		) );
	}

	// Check that plugin is not being activated at the Network level on Multisite sites.
	if ( $network_wide && is_multisite() ) {
		deactivate_plugins( DFRCS_BASENAME );
		wp_die( __(
			'The Datafeedr Comparison Sets plugin cannot be activated at the Network-level. Please activate the Datafeedr Comparison Sets plugin at the Site-level instead.',
			'datafeedr-comparison-sets'
		) );
	}

	// Add default options if 'dfrcs_options' does not exist in the options table.
	$options = get_option( 'dfrcs_options' );
	if ( ! $options ) {
		add_option( 'dfrcs_options', dfrcs_default_options() );
	}

	// Create comparison set database table.
	dfrcs_create_compsets_table();
}




