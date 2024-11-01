<?php

namespace WOPMailSMTP\Admin\Pages;

use WOPMailSMTP\MailCatcher;
use WOPMailSMTP\Options;
use WOPMailSMTP\WP;
use WOPMailSMTP\Admin\PageAbstract;


class Test extends PageAbstract {

	protected $slug = 'test';

	public function get_label() {
		return esc_html__( 'Email Test', 'wop-wordpress-smtp-mail' );
	}

	
	public function get_title() {
		return $this->get_label();
	}

	
	public function display() {
		?>

		<form method="POST" action="">
			<?php $this->wp_nonce_field(); ?>

			<!-- Test Email Section Title -->
			<div class="wop-wordpress-smtp-mail-setting-row wop-wordpress-smtp-mail-setting-row-content wop-wordpress-smtp-mail-clear section-heading no-desc" id="wop-wordpress-smtp-mail-setting-row-email-heading">
				<div class="wop-wordpress-smtp-mail-setting-field">
					<h2><?php esc_html_e( 'Send a Test Email', 'wop-wordpress-smtp-mail' ); ?></h2>
				</div>
			</div>

			<!-- Test Email -->
			<div id="wop-wordpress-smtp-mail-setting-row-test_email" class="wop-wordpress-smtp-mail-setting-row wop-wordpress-smtp-mail-setting-row-email wop-wordpress-smtp-mail-clear">
				<div class="wop-wordpress-smtp-mail-setting-label">
					<label for="wop-wordpress-smtp-mail-setting-test_email"><?php esc_html_e( 'Enter Your Email', 'wop-wordpress-smtp-mail' ); ?></label>
				</div>
				<div class="wop-wordpress-smtp-mail-setting-field">
					<input name="wop-wordpress-smtp-mail[test_email]" type="email" id="wop-wordpress-smtp-mail-setting-test_email" spellcheck="false" required />
					<p class="desc">
						<?php esc_html_e( 'Type an email address here and then click a button below to generate a test email.', 'wop-wordpress-smtp-mail' ); ?>
					</p>
				</div>
			</div>
             <div id="wop-wordpress-smtp-mail-setting-row-test_msg" class="wop-wordpress-smtp-mail-setting-row wop-wordpress-smtp-mail-setting-row-email wop-wordpress-smtp-mail-clear">
				<div class="wop-wordpress-smtp-mail-setting-label">
					<label for="wop-wordpress-smtp-mail-setting-test_msg"><?php esc_html_e( 'Enter Your Subject', 'wop-wordpress-smtp-mail' ); ?></label>
				</div>
				<div class="wop-wordpress-smtp-mail-setting-field">
					<input name="wop-wordpress-smtp-mail[test_msg]" type="text" id="wop-wordpress-smtp-mail-setting-test_msg" spellcheck="false"  />
					
				</div>
			</div>
			
			<p class="wop-wordpress-smtp-mail-submit">
				<button type="submit" class="wop-wordpress-smtp-mail-btn wop-wordpress-smtp-mail-btn-md wop-wordpress-smtp-mail-btn-orange"><?php esc_html_e( 'Send Email', 'wop-wordpress-smtp-mail' ); ?></button>
			</p>
		</form>

		<?php
	}

	/**
	 * @inheritdoc
	 */
	public function process_post( $data ) {

		$this->check_admin_referer();

		if ( isset( $data['test_email'] ) ) {
			$data['test_email'] = filter_var( $data['test_email'], FILTER_VALIDATE_EMAIL );
		}

		if ( empty( $data['test_email'] ) ) {
			WP::add_admin_notice(
				esc_html__( 'Test failed. Please use a valid email address and try to resend the test email.', 'wop-wordpress-smtp-mail' ),
				WP::ADMIN_NOTICE_WARNING
			);
			return;
		}

		global $phpmailer;

		// Make sure the PHPMailer class has been instantiated.
		if ( ! is_object( $phpmailer ) || ! is_a( $phpmailer, 'PHPMailer' ) ) {
			require_once ABSPATH . WPINC . '/class-phpmailer.php';
			$phpmailer = new MailCatcher( true );
		}

		// Set SMTPDebug level, default is 2 (commands + data + connection status).
		$phpmailer->SMTPDebug = apply_filters( 'wp_mail_smtp_admin_test_email_smtp_debug', 2 );

		// Start output buffering to grab smtp debugging output.
		ob_start();

		// Send the test mail.
		$result = wp_mail(
			$data['test_email'],
			$data['test_msg'],
			/* translators: %s - email address a test email will be sent to. */
			'WOP Wordpress Mail SMTP: ' . sprintf( esc_html__( 'This is Test email to %s', 'wop-wordpress-smtp-mail' ), $data['test_email'] ),
			sprintf(
				/* translators: %s - mailer name. */
				esc_html__( 'This email was sent by %s mailer, and generated by the WOP Wordpress Mail SMTP WordPress plugin.', 'wop-wordpress-smtp-mail' ),
				wop_wordpress_mail_smtp()->get_providers()->get_options( Options::init()->get( 'mail', 'mailer' ) )->get_title()
			)
		);

		// Grab the smtp debugging output.
		$smtp_debug = ob_get_clean();

		/*
		 * Do the actual sending.
		 */
		if ( $result ) {
			WP::add_admin_notice(
				esc_html__( 'Your email was sent successfully!', 'wop-wordpress-smtp-mail' ),
				WP::ADMIN_NOTICE_SUCCESS
			);
		} else {
			$error = $this->get_debug_messages( $phpmailer, $smtp_debug );

			WP::add_admin_notice(
				'<p><strong>' . esc_html__( 'There was a problem while sending a test email.', 'wop-wordpress-smtp-mail' ) . '</strong></p>' .
				'<p>' . esc_html__( 'The related debugging output is shown below:', 'wop-wordpress-smtp-mail' ) . '</p>' .
				'<blockquote>' . $error . '</blockquote>',
				WP::ADMIN_NOTICE_ERROR
			);
		}
	}

	/**
	 * Prepare debug information, that will help users to identify the error.
	 *
	 * @param \PHPMailer $phpmailer
	 * @param string $smtp_debug
	 *
	 * @return string
	 */
	protected function get_debug_messages( $phpmailer, $smtp_debug ) {

		global $wp_version;

		$errors = array();

		$versions_text = '<h3>Versions</h3>';

		$versions_text .= '<strong>WordPress:</strong> ' . $wp_version . '<br>';
		$versions_text .= '<strong>PHP:</strong> ' . PHP_VERSION . '<br>';
		$versions_text .= '<strong>WOP Wordpress Mail SMTP:</strong> ' . WPMS_PLUGIN_VER;

		$errors[] = $versions_text;

		$phpmailer_text = '<h3>PHPMailer</h3>';

		$phpmailer_text .= '<strong>ErrorInfo:</strong> ' . make_clickable( $phpmailer->ErrorInfo ) . '<br>';
		$phpmailer_text .= '<strong>Mailer:</strong> ' . $phpmailer->Mailer . '<br>';
		$phpmailer_text .= '<strong>Host:</strong> ' . $phpmailer->Host . '<br>';
		$phpmailer_text .= '<strong>Port:</strong> ' . $phpmailer->Port . '<br>';
		$phpmailer_text .= '<strong>SMTPSecure:</strong> ' . $this->pvar( $phpmailer->SMTPSecure ) . '<br>';
		$phpmailer_text .= '<strong>SMTPAutoTLS:</strong> ' . $this->pvar( $phpmailer->SMTPAutoTLS ) . '<br>';
		$phpmailer_text .= '<strong>SMTPAuth:</strong> ' . $this->pvar( $phpmailer->SMTPAuth );
		if ( ! empty( $phpmailer->SMTPOptions ) ) {
			$phpmailer_text .= '<br><strong>SMTPOptions:</strong> ' . $this->pvar( $phpmailer->SMTPOptions );
		}

		$errors[] = $phpmailer_text;

		if ( ! empty( $smtp_debug ) ) {
			$errors[] = '<h3>SMTP Debug</h3>' . nl2br( $smtp_debug );
		}

		return implode( '</p><p>', $errors );
	}

	/**
	 * Get the proper variable content output to debug.
	 *
	 * @param mixed $var
	 *
	 * @return string
	 */
	protected function pvar( $var = '' ) {

		ob_start();

		echo '<code>';

		if ( is_bool( $var ) || empty( $var ) ) {
			var_dump( $var );
		} else {
			print_r( $var );
		}

		echo '</code>';

		return ob_get_clean();
	}
}
