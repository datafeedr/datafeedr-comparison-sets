<?php

/**
 * Exit if accessed directly
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BEGIN
 *
 * Get previous version stored in database.
 */
$previous_version = get_option( 'dfrcs_version', false );


/**
 * Upgrade functions go here...
 */


/**
 * END
 *
 * Now that any upgrade functions are performed, update version in database.
 */
update_option( 'dfrcs_version', DFRCS_VERSION );
