<?php
/**
 * Uninstall script for Signalfire Expire Content
 *
 * @package SignalfireExpireContent
 * @since 1.0.0
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Remove all plugin data when uninstalled.
 */
function signalfire_expire_content_uninstall() {
	global $wpdb;

	// Meta keys used by the plugin.
	$meta_keys = array(
		'sec_expiration_date',
		'sec_expiration_time',
		'sec_expiration_action',
		'sec_expiration_url',
	);

	// Remove all post meta for the plugin using delete_metadata.
	foreach ( $meta_keys as $meta_key ) {
		delete_metadata( 'post', 0, $meta_key, '', true );
	}

	// Clear any cached data.
	wp_cache_flush();
}

signalfire_expire_content_uninstall();