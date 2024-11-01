<?php

namespace WOPMailSMTP\Providers\SMTP;

use WOPMailSMTP\Providers\OptionAbstract;

/**
 * Class SMTP.
 *
 * @since 1.0.0
 */
class Options extends OptionAbstract {

	/**
	 * SMTP constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		parent::__construct(
			array(
				'logo_url'    => wop_wordpress_mail_smtp()->plugin_url . '/assets/images/smtp.png',
				'slug'        => 'smtp',
				'title'       => esc_html__( 'Other SMTP', 'wop-wordpress-smtp-mail' ),
				/* translators: %1$s - opening link tag; %2$s - closing link tag. */
				'description' => sprintf(
					wp_kses(
						__( 'Use the SMTP details provided by your hosting provider or email service.', 'wop-wordpress-smtp-mail' ),
						array(
							'br' => array(),
							'a'  => array(
								'href'   => array(),
								'rel'    => array(),
								'target' => array(),
							),
						)
					),
					'<a href="https://wordpress.com/docs/how-to-set-up-smtp-using-the-wop-wordpress-smtp-mail-plugin/" target="_blank" rel="noopener noreferrer">',
					'</a>'
				),
			)
		);
	}
}
