<?php

namespace WOPMailSMTP\Providers\Sendgrid;

use WOPMailSMTP\Providers\MailerAbstract;

/**
 * Class Mailer.
 *
 * @since 1.0.0
 */
class Mailer extends MailerAbstract {

	/**
	 * Which response code from HTTP provider is considered to be successful?
	 *
	 * @var int
	 */
	protected $email_sent_code = 202;

	/**
	 * URL to make an API request to.
	 *
	 * @var string
	 */
	protected $url = '#';

	/**
	 * Mailer constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param \PHPMailer $phpmailer
	 */
	public function __construct( $phpmailer ) {

		// We want to prefill everything from \PHPMailer class.
		parent::__construct( $phpmailer );

		$this->set_header( 'Authorization', 'Bearer ' . $this->options->get( $this->mailer, 'api_key' ) );
		$this->set_header( 'content-type', 'application/json' );
	}

	/**
	 * Redefine the way email body is returned.
	 * By default we are sending an array of data.
	 * SendGrid requires a JSON, so we encode the body.
	 *
	 * @since 1.0.0
	 */
	public function get_body() {

		$body = parent::get_body();

		return wp_json_encode( $body );
	}

	/**
	 * @inheritdoc
	 */
	public function set_from( $email, $name = '' ) {

		if ( ! filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
			return;
		}

		$from['email'] = $email;

		if ( ! empty( $name ) ) {
			$from['name'] = $name;
		}

		$this->set_body_param(
			array(
				'from' => $from,
			)
		);
	}

	/**
	 * @inheritdoc
	 */
	public function set_recipients( $recipients ) {

		if ( empty( $recipients ) ) {
			return;
		}

		// Allow for now only these recipient types.
		$default = array( 'to', 'cc', 'bcc' );
		$data    = array();

		foreach ( $recipients as $type => $emails ) {
			if (
				! in_array( $type, $default, true ) ||
				empty( $emails ) ||
				! is_array( $emails )
			) {
				continue;
			}

			$data[ $type ] = array();

			// Iterate over all emails for each type.
			// There might be multiple cc/to/bcc emails.
			foreach ( $emails as $email ) {
				$holder = array();
				$addr   = isset( $email[0] ) ? $email[0] : false;
				$name   = isset( $email[1] ) ? $email[1] : false;

				if ( ! filter_var( $addr, FILTER_VALIDATE_EMAIL ) ) {
					continue;
				}

				$holder['email'] = $addr;
				if ( ! empty( $name ) ) {
					$holder['name'] = $name;
				}

				array_push( $data[ $type ], $holder );
			}
		}

		if ( ! empty( $data ) ) {
			$this->set_body_param(
				array(
					'personalizations' => array( $data ),
				)
			);
		}
	}

	/**
	 * @inheritdoc
	 */
	public function set_content( $content ) {

		if ( empty( $content ) ) {
			return;
		}

		if ( is_array( $content ) ) {

			$default = array( 'text', 'html' );
			$data    = array();

			foreach ( $content as $type => $body ) {
				if (
					! in_array( $type, $default, true ) ||
					empty( $body )
				) {
					continue;
				}

				$content_type  = 'text/plain';
				$content_value = $body;

				if ( $type === 'html' ) {
					$content_type = 'text/html';
				}

				$data[] = array(
					'type'  => $content_type,
					'value' => $content_value,
				);
			}

			$this->set_body_param(
				array(
					'content' => $data,
				)
			);
		} else {
			$data['type']  = 'text/plain';
			$data['value'] = $content;

			if ( $this->phpmailer->ContentType === 'text/html' ) {
				$data['type'] = 'text/html';
			}

			$this->set_body_param(
				array(
					'content' => array( $data ),
				)
			);
		}
	}

	/**
	 * SendGrid accepts an array of files content in body, so we will include all files and send.
	 * Doesn't handle exceeding the limits etc, as this is done and reported be SendGrid API.
	 *
	 * @since 1.0.0
	 *
	 * @param array $attachments
	 */
	public function set_attachments( $attachments ) {

		if ( empty( $attachments ) ) {
			return;
		}

		$data = array();

		foreach ( $attachments as $attachment ) {
			$file = false;

			/*
			 * We are not using WP_Filesystem API as we can't reliably work with it.
			 * It is not always available, same as credentials for FTP.
			 */
			try {
				if ( is_file( $attachment[0] ) && is_readable( $attachment[0] ) ) {
					$file = file_get_contents( $attachment[0] );
				}
			} catch ( \Exception $e ) {
				$file = false;
			}

			if ( $file === false ) {
				continue;
			}

			$data[] = array(
				'content'     => base64_encode( $file ),
				'type'        => $attachment[4],
				'filename'    => $attachment[1],
				'disposition' => $attachment[6],
			);
		}

		if ( ! empty( $data ) ) {
			$this->set_body_param(
				array(
					'attachments' => $data,
				)
			);
		}
	}

	/**
	 * @inheritdoc
	 */
	public function set_reply_to( $reply_to ) {

		if ( empty( $reply_to ) ) {
			return;
		}

		$data = array();

		foreach ( $reply_to as $key => $emails ) {
			if (
				empty( $emails ) ||
				! is_array( $emails )
			) {
				continue;
			}

			$addr = isset( $emails[0] ) ? $emails[0] : false;
			$name = isset( $emails[1] ) ? $emails[1] : false;

			if ( ! filter_var( $addr, FILTER_VALIDATE_EMAIL ) ) {
				continue;
			}

			$data['email'] = $addr;
			if ( ! empty( $name ) ) {
				$data['name'] = $name;
			}

			break;
		}

		if ( ! empty( $data ) ) {
			$this->set_body_param(
				array(
					'reply_to' => $data,
				)
			);
		}
	}

	/**
	 * SendGrid doesn't support sender or return_path params.
	 * So we do nothing.
	 *
	 * @since 1.0.0
	 *
	 * @param string $email
	 */
	public function set_return_path( $email ) {
	}
}
