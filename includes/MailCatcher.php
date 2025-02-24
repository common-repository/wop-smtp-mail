<?php

namespace WOPMailSMTP;

// Load PHPMailer class, so we can subclass it.
if ( ! class_exists( 'PHPMailer', false ) ) {
	require_once ABSPATH . WPINC . '/class-phpmailer.php';
}

/**
 * Class MailCatcher replaces the \PHPMailer and modifies the email sending logic.
 * Thus, we can use other mailers API to do what we need, or stop emails completely.
 *
 * @since 1.0.0
 */
class MailCatcher extends \PHPMailer {

	/**
	 * Modify the default send() behaviour.
	 * For those mailers, that relies on PHPMailer class - call it directly.
	 * For others - init the correct provider and process it.
	 *
	 * @since 1.0.0
	 *
	 * @throws \phpmailerException Throws when sending via PhpMailer fails for some reason.
	 *
	 * @return bool
	 */
	public function send() {

		$options = new Options();
		$mailer  = $options->get( 'mail', 'mailer' );

		// Use the default PHPMailer, as we inject our settings there for certain providers.
		if (
			$mailer === 'mail' ||
			$mailer === 'smtp' ||
			$mailer === 'pepipost'
		) {
			return parent::send();
		}

		// Prepare everything (including the message) for sending.
		if ( ! $this->preSend() ) {
			return false;
		}

		$mailer = wop_wordpress_mail_smtp()->get_providers()->get_mailer( $mailer, $this );

		if ( ! $mailer ) {
			return false;
		}

		if ( ! $mailer->is_php_compatible() ) {
			return false;
		}

		/*
		 * Send the actual email.
		 * We reuse everything, that was preprocessed for usage in \PHPMailer.
		 */
		$mailer->send();

		return $mailer->is_email_sent();
	}
}
