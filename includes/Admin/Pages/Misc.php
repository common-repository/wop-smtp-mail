<?php

namespace WOPMailSMTP\Admin\Pages;

use WOPMailSMTP\Admin\PageAbstract;
use WOPMailSMTP\Options;
use WOPMailSMTP\WP;

/**
 * Class Misc is part of Area, displays different plugin-related settings of the plugin (not related to emails).
 *
 * @since 1.0.0
 */
class Misc extends PageAbstract {
	/**
	 * @var string Slug of a tab.
	 */
	protected $slug = 'misc';

	/**
	 * @inheritdoc
	 */
	public function get_label() {
		return esc_html__( 'Misc', 'wop-wordpress-smtp-mail' );
	}

	/**
	 * @inheritdoc
	 */
	public function get_title() {
		return $this->get_label();
	}

	/**
	 * @inheritdoc
	 */
	public function display() {

		$options = new Options();
		?>

		<form method="POST" action="">
			<?php $this->wp_nonce_field(); ?>

			<!-- General Section Title -->
			<div class="wop-wordpress-smtp-mail-setting-row wop-wordpress-smtp-mail-setting-row-content wop-wordpress-smtp-mail-clear section-heading no-desc" id="wop-wordpress-smtp-mail-setting-row-email-heading">
				<div class="wop-wordpress-smtp-mail-setting-field">
					<h2><?php esc_html_e( 'General', 'wop-wordpress-smtp-mail' ); ?></h2>
				</div>
			</div>

			<!-- Hide Announcements -->
			<div id="wop-wordpress-smtp-mail-setting-row-am_notifications_hidden" class="wop-wordpress-smtp-mail-setting-row wop-wordpress-smtp-mail-setting-row-checkbox wop-wordpress-smtp-mail-clear">
				<div class="wop-wordpress-smtp-mail-setting-label">
					<label for="wop-wordpress-smtp-mail-setting-am_notifications_hidden"><?php esc_html_e( 'Hide Announcements', 'wop-wordpress-smtp-mail' ); ?></label>
				</div>
				<div class="wop-wordpress-smtp-mail-setting-field">
					<input name="wop-wordpress-smtp-mail[general][am_notifications_hidden]" type="checkbox"
						value="true" <?php checked( true, $options->get( 'general', 'am_notifications_hidden' ) ); ?>
						id="wop-wordpress-smtp-mail-setting-am_notifications_hidden"
					/>
					<label for="wop-wordpress-smtp-mail-setting-am_notifications_hidden"><?php esc_html_e( 'Check this if you would like to hide plugin announcements and update details.', 'wop-wordpress-smtp-mail' ); ?></label>
				</div>
			</div>

			<p class="wop-wordpress-smtp-mail-submit">
				<button type="submit" class="wop-wordpress-smtp-mail-btn wop-wordpress-smtp-mail-btn-md wop-wordpress-smtp-mail-btn-orange"><?php esc_html_e( 'Save Settings', 'wop-wordpress-smtp-mail' ); ?></button>
			</p>

		</form>

		<?php
	}

	/**
	 * @inheritdoc
	 */
	public function process_post( $data ) {

		$this->check_admin_referer();

		$options = new Options();

		// Unchecked checkbox doesn't exist in $_POST, so we need to ensure we actually have it.
		if ( empty( $data['general']['am_notifications_hidden'] ) ) {
			$data['general']['am_notifications_hidden'] = false;
		}

		$to_save = array_merge( $options->get_all(), $data );

		// All the sanitization is done there.
		$options->set( $to_save );

		WP::add_admin_notice(
			esc_html__( 'Settings were successfully saved.', 'wop-wordpress-smtp-mail' ),
			WP::ADMIN_NOTICE_SUCCESS
		);
	}
}
