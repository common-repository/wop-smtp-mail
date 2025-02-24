<?php

namespace WOPMailSMTP\Providers;

use WOPMailSMTP\MailCatcher;
use WOPMailSMTP\Options;

/**
 * Class MailerAbstract.
 *
 * @since 1.0.0
 */
abstract class MailerAbstract implements MailerInterface {

	/**
	 * Which response code from HTTP provider is considered to be successful?
	 *
	 * @var int
	 */
	protected $email_sent_code = 200;
	/**
	 * @var Options
	 */
	protected $options;
	/**
	 * @var MailCatcher
	 */
	protected $phpmailer;
	/**
	 * @var string
	 */
	protected $mailer = '';

	/**
	 * URL to make an API request to.
	 *
	 * @var string
	 */
	protected $url = '';
	/**
	 * @var array
	 */
	protected $headers = array();
	/**
	 * @var array
	 */
	protected $body = array();
	/**
	 * @var mixed
	 */
	protected $response = array();

	/**
	 * Mailer constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param MailCatcher $phpmailer
	 */
	public function __construct( MailCatcher $phpmailer ) {

		if ( empty( $this->url ) ) {
			return;
		}

		$this->options = new Options();
		$this->mailer  = $this->options->get( 'mail', 'mailer' );

		$this->process_phpmailer( $phpmailer );
	}

	/**
	 * Re-use the MailCatcher class methods and properties.
	 *
	 * @since 1.0.0
	 *
	 * @param MailCatcher $phpmailer
	 */
	protected function process_phpmailer( $phpmailer ) {

		// Make sure that we have access to PHPMailer class methods.
		if ( ! $phpmailer instanceof MailCatcher ) {
			return;
		}

		$this->phpmailer = $phpmailer;

		$this->set_headers( $this->phpmailer->getCustomHeaders() );
		$this->set_from( $this->phpmailer->From, $this->phpmailer->FromName );
		$this->set_recipients(
			array(
				'to'  => $this->phpmailer->getToAddresses(),
				'cc'  => $this->phpmailer->getCcAddresses(),
				'bcc' => $this->phpmailer->getBccAddresses(),
			)
		);
		$this->set_subject( $this->phpmailer->Subject );
		$this->set_content(
			array(
				'html' => $this->phpmailer->Body,
				'text' => $this->phpmailer->AltBody,
			)
		);
		$this->set_return_path( $this->phpmailer->From );
		$this->set_reply_to( $this->phpmailer->getReplyToAddresses() );

		/*
		 * In some cases we will need to modify the internal structure
		 * of the body content, if attachments are present.
		 * So lets make this call the last one.
		 */
		$this->set_attachments( $this->phpmailer->getAttachments() );
	}

	/**
	 * @inheritdoc
	 */
	public function set_subject( $subject ) {

		$this->set_body_param(
			array(
				'subject' => $subject,
			)
		);
	}

	/**
	 * Set the request params, that goes to the body of the HTTP request.
	 *
	 * @since 1.0.0
	 *
	 * @param array $param Key=>value of what should be sent to a 3rd party API.
	 *
	 * @internal param array $params
	 */
	protected function set_body_param( $param ) {
		$this->body = $this->array_merge_recursive( $this->body, $param );
	}

	/**
	 * @inheritdoc
	 */
	public function set_headers( $headers ) {

		foreach ( $headers as $header ) {
			$name  = isset( $header[0] ) ? $header[0] : false;
			$value = isset( $header[1] ) ? $header[1] : false;

			if ( empty( $name ) || empty( $value ) ) {
				continue;
			}

			$this->set_header( $name, $value );
		}
	}

	/**
	 * @inheritdoc
	 */
	public function set_header( $name, $value ) {

		$process_value = function ( $value ) {
			// Remove HTML tags.
			$filtered = wp_strip_all_tags( $value, false );
			// Remove multi-lines/tabs.
			$filtered = preg_replace( '/[\r\n\t ]+/', ' ', $filtered );
			// Remove whitespaces.
			$filtered = trim( $filtered );

			// Remove octets.
			$found = false;
			while ( preg_match( '/%[a-f0-9]{2}/i', $filtered, $match ) ) {
				$filtered = str_replace( $match[0], '', $filtered );
				$found    = true;
			}

			if ( $found ) {
				// Strip out the whitespace that may now exist after removing the octets.
				$filtered = trim( preg_replace( '/ +/', ' ', $filtered ) );
			}

			return $filtered;
		};

		$name = sanitize_text_field( $name );
		if ( empty( $name ) ) {
			return;
		}

		$value = $process_value( $value );

		$this->headers[ $name ] = $value;
	}

	/**
	 * @inheritdoc
	 */
	public function get_body() {
		return apply_filters( 'wp_mail_smtp_providers_mailer_get_body', $this->body );
	}

	/**
	 * @inheritdoc
	 */
	public function get_headers() {
		return apply_filters( 'wp_mail_smtp_providers_mailer_get_headers', $this->headers );
	}

	/**
	 * @inheritdoc
	 */
	public function send() {

		$params = $this->array_merge_recursive( $this->get_default_params(), array(
			'headers' => $this->get_headers(),
			'body'    => $this->get_body(),
		) );

		$response = wp_safe_remote_post( $this->url, $params );

		$this->process_response( $response );
	}

	/**
	 * We might need to do something after the email was sent to the API.
	 * In this method we preprocess the response from the API.
	 *
	 * @since 1.0.0
	 *
	 * @param array|\WP_Error $response
	 */
	protected function process_response( $response ) {

		if ( is_wp_error( $response ) ) {
			return;
		}

		if ( isset( $response['body'] ) && $this->is_json( $response['body'] ) ) {
			$response['body'] = json_decode( $response['body'] );
		}

		$this->response = $response;
	}

	/**
	 * Get the default params, required for wp_safe_remote_post().
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	protected function get_default_params() {

		return apply_filters( 'wp_mail_smtp_providers_mailer_get_default_params', array(
			'timeout'     => 15,
			'httpversion' => '1.1',
			'blocking'    => true,
		) );
	}

	/**
	 * @inheritdoc
	 */
	public function is_email_sent() {

		$is_sent = false;

		if ( wp_remote_retrieve_response_code( $this->response ) === $this->email_sent_code ) {
			$is_sent = true;
		}

		return apply_filters( 'wp_mail_smtp_providers_mailer_is_email_sent', $is_sent );
	}

	/**
	 * @inheritdoc
	 */
	public function is_php_compatible() {

		$options = wop_wordpress_mail_smtp()->get_providers()->get_options( $this->mailer );

		return version_compare( phpversion(), $options->get_php_version(), '>=' );
	}

	/**
	 * Check whether the string is a JSON or not.
	 *
	 * @since 1.0.0
	 *
	 * @param string $string
	 *
	 * @return bool
	 */
	protected function is_json( $string ) {
		return is_string( $string ) && is_array( json_decode( $string, true ) ) && ( json_last_error() === JSON_ERROR_NONE ) ? true : false;
	}

	/**
	 * Merge recursively, including a proper substitution of values in sub-arrays when keys are the same.
	 * It's more like array_merge() and array_merge_recursive() combined.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	protected function array_merge_recursive() {

		$arrays = func_get_args();

		if ( count( $arrays ) < 2 ) {
			return isset( $arrays[0] ) ? $arrays[0] : array();
		}

		$merged = array();

		while ( $arrays ) {
			$array = array_shift( $arrays );

			if ( ! is_array( $array ) ) {
				return array();
			}

			if ( empty( $array ) ) {
				continue;
			}

			foreach ( $array as $key => $value ) {
				if ( is_string( $key ) ) {
					if (
						is_array( $value ) &&
						array_key_exists( $key, $merged ) &&
						is_array( $merged[ $key ] )
					) {
						$merged[ $key ] = call_user_func( __FUNCTION__, $merged[ $key ], $value );
					} else {
						$merged[ $key ] = $value;
					}
				} else {
					$merged[] = $value;
				}
			}
		}

		return $merged;
	}
}
