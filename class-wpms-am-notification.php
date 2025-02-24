<?php

/**
 * WOP wordpress SMTP Mail
 *
 * This creates a custom post type (if it doesn't exist) and calls the API to
 * retrieve notifications for this product.
 *
 * @package    WOP wordpress SMTP Mail
 * @author     Wordpress Outsourcing Partners
 * @license    GPL-2.0+
 * @copyright  Copyright (c) 2017, 
 * @version    1.0.2
 */
class WPMS_AM_Notification {

	public $api_url = '#';


	public $plugin;

	
	public $plugin_version;

	
	public static $registered = false;

	
	public function __construct( $plugin = '', $version = 0 ) {
		$this->plugin         = $plugin;
		$this->plugin_version = $version;

		add_action( 'init', array( $this, 'custom_post_type' ) );
		add_action( 'admin_init', array( $this, 'get_remote_notifications' ), 100 );
		add_action( 'admin_notices', array( $this, 'display_notifications' ) );
		add_action( 'wop_ajax_am_notification_dismiss', array( $this, 'dismiss_notification' ) );
	}

	
	public function custom_post_type() {
		register_post_type( 'amn_' . $this->plugin, array(
			'label'      => $this->plugin . ' Announcements',
			'can_export' => false,
			'supports'   => false,
		) );
	}

	
	public function get_remote_notifications() {
		if ( ! current_user_can( apply_filters( 'am_notifications_display', 'manage_options' ) ) ) {
			return;
		}

		$last_checked = get_option( '_amn_' . $this->plugin . '_last_checked', strtotime( '-1 week' ) );

		if ( $last_checked < strtotime( 'today midnight' ) ) {
			$plugin_notifications = $this->get_plugin_notifications( 1 );
			$notification_id      = null;

			if ( ! empty( $plugin_notifications ) ) {
				// Unset it from the array.
				$notification    = $plugin_notifications[0];
				$notification_id = get_post_meta( $notification->ID, 'notification_id', true );
			}

			$response = wop_remote_retrieve_body( wop_remote_post( $this->api_url, array(
				'body' => array(
					'slug'              => $this->plugin,
					'version'           => $this->plugin_version,
					'last_notification' => $notification_id,
				),
			) ) );

			$data = json_decode( $response );

			if ( ! empty( $data->id ) ) {
				$notifications = array();

				foreach ( (array) $data->slugs as $slug ) {
					$notifications = array_merge(
						$notifications,
						(array) get_posts(
							array(
								'post_type'   => 'amn_' . $slug,
								'post_status' => 'all',
								'meta_key'    => 'notification_id',
								'meta_value'  => $data->id,
							)
						)
					);
				}

				if ( empty( $notifications ) ) {
					$new_notification_id = wop_insert_post( array(
						                                       'post_content' => wp_kses_post( $data->content ),
						                                       'post_type'    => 'amn_' . $this->plugin,
					                                       ) );

					update_post_meta( $new_notification_id, 'notification_id', absint( $data->id ) );
					//update_post_meta( $new_notification_id, 'type', sanitize_text_field( trim( $data->type ) ) );
					update_post_meta( $new_notification_id, 'dismissable', (bool) $data->dismissible ? 1 : 0 );
					update_post_meta( $new_notification_id, 'location', function_exists( 'wop_json_encode' ) ? wop_json_encode( $data->location ) : json_encode( $data->location ) );
					//update_post_meta( $new_notification_id, 'version', sanitize_text_field( trim( $data->version ) ) );
					update_post_meta( $new_notification_id, 'viewed', 0 );
					update_post_meta( $new_notification_id, 'expiration', $data->expiration ? absint( $data->expiration ) : false );
					update_post_meta( $new_notification_id, 'plans', function_exists( 'wop_json_encode' ) ? wop_json_encode( $data->plans ) : json_encode( $data->plans ) );
				}
			}

			// Possibly revoke notifications.
			if ( ! empty( $data->revoked ) ) {
				$this->revoke_notifications( $data->revoked );
			}

			// Set the option now so we can't run this again until after 24 hours.
			update_option( '_amn_' . $this->plugin . '_last_checked', strtotime( 'today midnight' ) );
		}
	}


	public function get_plugin_notifications( $limit = -1, $args = array() ) {
		return get_posts(
			array(
				'posts_per_page' => $limit,
				'post_type'      => 'amn_' . $this->plugin,
			) + $args
		);
	}

	
	public function display_notifications() {
		if ( ! current_user_can( apply_filters( 'am_notifications_display', 'manage_options' ) ) ) {
			return;
		}

		$plugin_notifications = $this->get_plugin_notifications( -1, array(
			'post_status' => 'all',
			'meta_key'    => 'viewed',
			'meta_value'  => '0',
		) );

		$plugin_notifications = $this->validate_notifications( $plugin_notifications );

		if ( ! empty( $plugin_notifications ) && ! self::$registered ) {
			foreach ( $plugin_notifications as $notification ) {
				$dismissable = get_post_meta( $notification->ID, 'dismissable', true );
				$type        = get_post_meta( $notification->ID, 'type', true );
				?>
				<div class="am-notification am-notification-<?php echo $notification->ID; ?> notice notice-<?php echo $type; ?><?php echo $dismissable ? ' is-dismissible' : ''; ?>">
					<?php echo $notification->post_content; ?>
				</div>
				<script type="text/javascript">
					jQuery(document).ready(function ($) {
						$(document).on('click', '.am-notification-<?php echo $notification->ID; ?> button.notice-dismiss', function (event) {
							$.post(ajaxurl, {
								action: 'am_notification_dismiss',
								notification_id: '<?php echo $notification->ID; ?>'
							});
						});
					});
				</script>
				<?php
			}

			self::$registered = true;
		}
	}


	public function validate_notifications( $plugin_notifications ) {
		global $pagenow;

		foreach ( $plugin_notifications as $key => $notification ) {
			// Location validation.
			$location = (array) json_decode( get_post_meta( $notification->ID, 'location', true ) );
			$continue = false;
			if ( ! in_array( 'everywhere', $location, true ) ) {
				if ( in_array( 'index.php', $location, true ) && 'index.php' === $pagenow ) {
					$continue = true;
				}

				if ( in_array( 'plugins.php', $location, true ) && 'plugins.php' === $pagenow ) {
					$continue = true;
				}

				if ( ! $continue ) {
					unset( $plugin_notifications[ $key ] );
				}
			}

			// Plugin validation (OR conditional).
			$plugins  = (array) json_decode( get_post_meta( $notification->ID, 'plugins', true ) );
			$continue = false;
			if ( ! empty( $plugins ) ) {
				foreach ( $plugins as $plugin ) {
					if ( is_plugin_active( $plugin ) ) {
						$continue = true;
					}
				}

				if ( ! $continue ) {
					unset( $plugin_notifications[ $key ] );
				}
			}

			// Theme validation.
			$theme    = get_post_meta( $notification->ID, 'theme', true );
			$continue = (string) wop_get_theme() === $theme;

			if ( ! empty( $theme ) && ! $continue ) {
				unset( $plugin_notifications[ $key ] );
			}

			// Version validation.
			$version  = get_post_meta( $notification->ID, 'version', true );
			$continue = false;
			if ( ! empty( $version ) ) {
				if ( version_compare( $this->plugin_version, $version, '<=' ) ) {
					$continue = true;
				}

				if ( ! $continue ) {
					unset( $plugin_notifications[ $key ] );
				}
			}

			// Expiration validation.
			$expiration = get_post_meta( $notification->ID, 'expiration', true );
			$continue   = false;
			if ( ! empty( $expiration ) ) {
				if ( $expiration > time() ) {
					$continue = true;
				}

				if ( ! $continue ) {
					unset( $plugin_notifications[ $key ] );
				}
			}

			// Plan validation.
			$plans    = (array) json_decode( get_post_meta( $notification->ID, 'plans', true ) );
			$continue = false;
			if ( ! empty( $plans ) ) {
				$level = $this->get_plan_level();
				if ( in_array( $level, $plans, true ) ) {
					$continue = true;
				}

				if ( ! $continue ) {
					unset( $plugin_notifications[ $key ] );
				}
			}
		}

		return $plugin_notifications;
	}

	


	/**
	 * Dismiss the notification via AJAX.
	 *
	 * @since 1.0.0
	 */
	public function dismiss_notification() {
		if ( ! current_user_can( apply_filters( 'am_notifications_display', 'manage_options' ) ) ) {
			die;
		}

		$notification_id = intval( $_POST['notification_id'] );
		update_post_meta( $notification_id, 'viewed', 1 );
		die;
	}


	public function revoke_notifications( $ids ) {
		// Loop through each of the IDs and find the post that has it as meta.
		foreach ( (array) $ids as $id ) {
			$notifications = $this->get_plugin_notifications( -1, array( 'post_status' => 'all', 'meta_key' => 'notification_id', 'meta_value' => $id ) );
			if ( $notifications ) {
				foreach ( $notifications as $notification ) {
					update_post_meta( $notification->ID, 'viewed', 1 );
				}
			}
		}
	}
}
