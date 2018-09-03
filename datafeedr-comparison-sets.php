<?php
/*
Plugin Name: Datafeedr Comparison Sets
Plugin URI: https://www.datafeedr.com
Description: Automatically create price comparison sets for your WooCommerce products or by using a shortcode.
Author: datafeedr.com
Author URI: https://www.datafeedr.com
License: GPL v3
Requires at least: 3.8
Tested up to: 4.9.8
Version: 0.9.22

WC requires at least: 3.0
WC tested up to: 3.4.5

Datafeedr Comparison Sets Plugin
Copyright (C) 2018, Datafeedr - help@datafeedr.com

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
define( 'DFRCS_VERSION', '0.9.22' );
define( 'DFRCS_DB_VERSION', '0.9.0' );
define( 'DFRCS_URL', plugin_dir_url( __FILE__ ) );
define( 'DFRCS_PATH', plugin_dir_path( __FILE__ ) );
define( 'DFRCS_BASENAME', plugin_basename( __FILE__ ) );
define( 'DFRCS_DOMAIN', 'datafeedr-comparison-sets' );
define( 'DFRCS_PREFIX', 'dfrcs' );
define( 'DFRCS_TABLE', 'dfrcs_compsets' );

/**
 * Load upgrade file.
 */
require_once( DFRCS_PATH . 'includes/upgrade.php' );

/**
 * Require classes.
 */
require_once( DFRCS_PATH . 'classes/class-datafeedr-plugin-dependency.php' );
require_once( DFRCS_PATH . 'classes/class-dfrcs.php' );
require_once( DFRCS_PATH . 'classes/class-dfrcs-source.php' );

/**
 * Include functions file.
 */
require_once( DFRCS_PATH . 'includes/functions.php' );

/**
 * Include backend functions file while in /wp-admin.
 */
if ( is_admin() ) {
	require_once( DFRCS_PATH . 'includes/functions-admin.php' );
}

/**
 * Require actions and filters.
 */
require_once( DFRCS_PATH . 'includes/actions.php' );
require_once( DFRCS_PATH . 'includes/filters.php' );

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
function dfrcs_activate() {

	// Add default options if 'dfrcs_options' does not exist in the options table.
	$options = get_option( 'dfrcs_options' );
	if ( ! $options ) {
		add_option( 'dfrcs_options', dfrcs_default_options() );
	}

	// Create comparison set database table.
	dfrcs_create_compsets_table();
}




