<?php
/**
 * Uninstall script for SigUkExp Expire Content
 *
 * @package SigUkExp_ExpireContent
 * @since 1.0.0
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Remove all plugin data when uninstalled.
 */
function sigukexp_expire_content_uninstall() {
	global $wpdb;

	// Meta keys used by the plugin.
	$meta_keys = array(
		'sigukexp_expiration_date',
		'sigukexp_expiration_time',
		'sigukexp_expiration_action',
		'sigukexp_expiration_url',
	);

	// Remove all post meta for the plugin using delete_metadata.
	foreach ( $meta_keys as $meta_key ) {
		delete_metadata( 'post', 0, $meta_key, '', true );
	}

	// Clear any cached data.
	wp_cache_flush();
}

sigukexp_expire_content_uninstall();