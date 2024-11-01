<?php

namespace WOPMailSMTP;

class WP {

	protected static $admin_notices = array();
	
	const ADMIN_NOTICE_SUCCESS = 'notice-success';
	
	const ADMIN_NOTICE_ERROR = 'notice-error';
	
	const ADMIN_NOTICE_INFO = 'notice-info';
	
	const ADMIN_NOTICE_WARNING = 'notice-warning';

	public static function is_doing_ajax() {

		if ( function_exists( 'wp_doing_ajax' ) ) {
			return wp_doing_ajax();
		}

		return ( defined( 'DOING_AJAX' ) && DOING_AJAX );
	}

	
	public static function in_wp_admin() {
		return ( is_admin() && ! self::is_doing_ajax() );
	}

	
	public static function add_admin_notice( $message, $class = self::ADMIN_NOTICE_INFO ) {

		self::$admin_notices[] = array(
			'message' => $message,
			'class'   => $class,
		);
	}

	
	public static function display_admin_notices() {

		foreach ( (array) self::$admin_notices as $notice ) : ?>

			<div id="message" class="<?php echo esc_attr( $notice['class'] ); ?> notice is-dismissible">
				<p>
					<?php echo $notice['message']; ?>
				</p>
			</div>

			<?php
		endforeach;
	}

	
	public static function is_debug() {
		return defined( 'WP_DEBUG' ) && WP_DEBUG;
	}


	public static function wpdb() {

		global $wpdb;

		return $wpdb;
	}

	
	public static function asset_min() {

		$min = '.min';

		if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
			$min = '';
		}

		return $min;
	}
}
