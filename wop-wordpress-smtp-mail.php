<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


spl_autoload_register( function ( $class ) {

	list( $plugin_space ) = explode( '\\', $class );
	if ( $plugin_space !== 'WOPMailSMTP' ) {
		return;
	}

	$PLUGIN_PATH = 'wop-smtp-mail';

	// Default directory for all code is plugin's /includes/.
	$base_dir = WP_PLUGIN_DIR . '/' . $PLUGIN_PATH . '/includes/';

	// Get the relative class name.
	$relative_class = substr( $class, strlen( $plugin_space ) + 1 );

	// Prepare a path to a file.
	$file = wp_normalize_path( $base_dir . $relative_class . '.php' );

	// If the file exists, require it.
	if ( is_readable( $file ) ) {
		/** @noinspection PhpIncludeInspection */
		require_once $file;
	}
} );

/**
 * Global function-holder. Works similar to a singleton's instance().
 *
 * @since 1.0.0
 *
 * @return WPMailSMTP\Core
 */
function wop_wordpress_mail_smtp() {
	/**
	 * @var \WPMailSMTP\Core
	 */
	static $core;

	if ( ! isset( $core ) ) {
		$core = new \WOPMailSMTP\Core();
	}

	return $core;
}

wop_wordpress_mail_smtp();
