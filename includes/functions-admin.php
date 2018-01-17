<?php

// Create comparison set database table.
function dfrcs_create_compsets_table() {
	global $wpdb;
	$table           = $wpdb->prefix . DFRCS_TABLE;
	$charset_collate = $wpdb->get_charset_collate();
	$sql             = "
		CREATE TABLE IF NOT EXISTS $table
		(
			id bigint(20) unsigned NOT NULL auto_increment,
			hash varchar(32) DEFAULT '',
			products LONGTEXT NOT NULL,
			log LONGTEXT NOT NULL,
			removed LONGTEXT NOT NULL,
			added LONGTEXT NOT NULL,
			num_requests int(11) NOT NULL DEFAULT '0',
			last_query LONGTEXT NOT NULL,
			updated TIMESTAMP,
			created TIMESTAMP,
			PRIMARY KEY  (id),
			KEY hash (hash)
		) $charset_collate ";
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
}
