<?php

namespace WOPMailSMTP\Providers;

use WOPMailSMTP\MailCatcher;

/**
 * Class Loader.
 *
 * @since 1.0.0
 */
class Loader {

	/**
	 * Key is the mailer option, value is the path to its classes.
	 *
	 * @var array
	 */
	protected $providers = array(
		'mail'     => '\WOPMailSMTP\Providers\Mail\\',
		'smtp'     => '\WOPMailSMTP\Providers\SMTP\\',
		'gmail'    => '\WOPMailSMTP\Providers\Gmail\\',
		'mailgun'  => '\WOPMailSMTP\Providers\Mailgun\\',
		'sendgrid' => '\WOPMailSMTP\Providers\Sendgrid\\',
		'pepipost' => '\WOPMailSMTP\Providers\Pepipost\\',
		
	);

	/**
	 * @var \WPMailSMTP\MailCatcher
	 */
	private $phpmailer;

	/**
	 * Get all the supported providers.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_providers() {
		return apply_filters( 'wp_mail_smtp_providers_loader_get_providers', $this->providers );
	}

	/**
	 * Get a single provider FQN-path based on its name.
	 *
	 * @since 1.0.0
	 *
	 * @param string $provider
	 *
	 * @return array
	 */
	public function get_provider_path( $provider ) {
		$provider = sanitize_key( $provider );

		return apply_filters(
			'wp_mail_smtp_providers_loader_get_provider_path',
			isset( $this->providers[ $provider ] ) ? $this->providers[ $provider ] : null,
			$provider
		);
	}

	/**
	 * Get the provider options, if exists.
	 *
	 * @since 1.0.0
	 *
	 * @param string $provider
	 *
	 * @return \WPMailSMTP\Providers\OptionAbstract|null
	 */
	public function get_options( $provider ) {
		return $this->get_entity( $provider, 'Options' );
	}

	/**
	 * Get all options of all providers.
	 *
	 * @since 1.0.0
	 *
	 * @return \WPMailSMTP\Providers\OptionAbstract[]
	 */
	public function get_options_all() {
		$options = array();

		foreach ( $this->get_providers() as $provider => $path ) {

			$option = $this->get_options( $provider );

			if ( ! $option instanceof OptionAbstract ) {
				continue;
			}

			$slug  = $option->get_slug();
			$title = $option->get_title();

			if ( empty( $title ) || empty( $slug ) ) {
				continue;
			}

			$options[] = $option;
		}

		return apply_filters( 'wp_mail_smtp_providers_loader_get_providers_all', $options );
	}

	/**
	 * Get the provider mailer, if exists.
	 *
	 * @since 1.0.0
	 *
	 * @param string $provider
	 * @param MailCatcher $phpmailer
	 *
	 * @return \WPMailSMTP\Providers\MailerAbstract|null
	 */
	public function get_mailer( $provider, $phpmailer ) {

		if ( $phpmailer instanceof MailCatcher ) {
			$this->phpmailer = $phpmailer;
		}

		return $this->get_entity( $provider, 'Mailer' );
	}

	/**
	 * Get the provider auth, if exists.
	 *
	 * @param string $provider
	 *
	 * @return \WPMailSMTP\Providers\AuthAbstract|null
	 */
	public function get_auth( $provider ) {
		return $this->get_entity( $provider, 'Auth' );
	}

	/**
	 * Get a generic entity based on the request.
	 *
	 * @uses ReflectionClass
	 *
	 * @since 1.0.0
	 *
	 * @param string $provider
	 * @param string $request
	 *
	 * @return null
	 */
	protected function get_entity( $provider, $request ) {

		$provider = sanitize_key( $provider );
		$request  = sanitize_text_field( $request );
		$path     = $this->get_provider_path( $provider );
		$entity   = null;

		if ( empty( $path ) ) {
			return $entity;
		}

		try {
			$reflection = new \ReflectionClass( $path . $request );

			if ( file_exists( $reflection->getFileName() ) ) {
				$class = $path . $request;
				if ( $this->phpmailer ) {
					$entity = new $class( $this->phpmailer );
				} else {
					$entity = new $class();
				}
			}
		} catch ( \Exception $e ) {
			// TODO: save error message later to display a user.
			$entity = null;
		}

		return apply_filters( 'wp_mail_smtp_providers_loader_get_entity', $entity, $provider, $request );
	}
}
