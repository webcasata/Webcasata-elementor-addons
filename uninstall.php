<?php
/**
 * Runs when the plugin is deleted from the Plugins screen: removes the
 * settings and the cached Vimeo thumbnails. Nothing the editor built with the
 * widgets (Elementor page data) is touched.
 *
 * @package Webcasata_Elementor_Addons
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Delete this plugin's data on the current site.
 */
function webcasata_addons_uninstall_site() {
	global $wpdb;

	delete_option( 'webcasata_addons_settings' );

	// Cached Vimeo thumbnails (transients "wv_vimeo_*" and their timeouts).
	// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
			$wpdb->esc_like( '_transient_wv_vimeo_' ) . '%',
			$wpdb->esc_like( '_transient_timeout_wv_vimeo_' ) . '%'
		)
	);
	// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
}

if ( is_multisite() ) {
	foreach ( get_sites( [ 'fields' => 'ids' ] ) as $webcasata_addons_blog_id ) {
		switch_to_blog( $webcasata_addons_blog_id );
		webcasata_addons_uninstall_site();
		restore_current_blog();
	}
} else {
	webcasata_addons_uninstall_site();
}
