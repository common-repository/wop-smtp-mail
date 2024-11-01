<?php

namespace WOPMailSMTP\Providers\Mail;

use WOPMailSMTP\Providers\OptionAbstract;

/**
 * Class Option.
 *
 * @since 1.0.0
 */
class Options extends OptionAbstract {

	/**
	 * Mail constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		parent::__construct(
			array(
				'logo_url' => wop_wordpress_mail_smtp()->plugin_url . '/assets/images/php.png',
				'slug'     => 'mail',
				'title'    => esc_html__( 'Default (none)', 'wop-wordpress-smtp-mail' ),
			)
		);
	}

	/**
	 * @inheritdoc
	 */
	public function display_options() {
		?>

		<blockquote>
			<?php esc_html_e( 'You currently have the native WordPress option selected. Please select any other Mailer option above to continue the setup.', 'wop-wordpress-smtp-mail' ); ?>
		</blockquote>

		<?php
	}
}
