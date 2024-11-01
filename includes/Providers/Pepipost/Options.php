<?php

namespace WOPMailSMTP\Providers\Pepipost;

use WOPMailSMTP\Providers\OptionAbstract;

/**
 * Class Option.
 *
 * @since 1.0.0
 */
class Option extends OptionAbstract {

	/**
	 * Pepipost constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		parent::__construct(
			array(
				'logo_url' => wop_wordpress_mail_smtp()->plugin_url . '/assets/images/pepipost.png',
				'slug'     => 'pepipost',
				'title'    => esc_html__( 'Pepipost', 'wop-wordpress-smtp-mail' ),
			)
		);
	}
}
