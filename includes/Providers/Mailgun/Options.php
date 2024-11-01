<?php

namespace WOPMailSMTP\Providers\Mailgun;

use WOPMailSMTP\Providers\OptionAbstract;

/**
 * Class Option.
 *
 * @since 1.0.0
 */
class Options extends OptionAbstract {

	/**
	 * Mailgun constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		parent::__construct(
			array(
				'logo_url'    => wop_wordpress_mail_smtp()->plugin_url . '/assets/images/mailgun.png',
				'slug'        => 'mailgun',
				'title'       => esc_html__( 'Mailgun', 'wop-wordpress-smtp-mail' ),
				'description' => sprintf(
					wp_kses(
						/* translators: %1$s - opening link tag; %2$s - closing link tag; %3$s - opening link tag; %4$s - closing link tag. */
						__( '%1$sMailgun%2$s is one of the leading transactional email services trusted by over 10,000 website and application developers. They provide users 10,000 free emails per month.<br><br>Read our %3$sMailgun documentation%4$s to learn how to configure Mailgun and improve your email deliverability.', 'wop-wordpress-smtp-mail' ),
						array(
							'br' => array(),
							'a'  => array(
								'href'   => array(),
								'rel'    => array(),
								'target' => array(),
							),
						)
					),
					'<a href="https://www.mailgun.com" target="_blank" rel="noopener noreferrer">',
					'</a>',
					'<a href="https://wordpress.com/how-to-send-wordpress-emails-with-mailgun/" target="_blank" rel="noopener noreferrer">',
					'</a>'
				),
			)
		);
	}

	/**
	 * @inheritdoc
	 */
	public function display_options() {
		?>

		<!-- API Key -->
		<div id="wop-wordpress-smtp-mail-setting-row-<?php echo esc_attr( $this->get_slug() ); ?>-api_key" class="wop-wordpress-smtp-mail-setting-row wop-wordpress-smtp-mail-setting-row-text wop-wordpress-smtp-mail-clear">
			<div class="wop-wordpress-smtp-mail-setting-label">
				<label for="wop-wordpress-smtp-mail-setting-<?php echo esc_attr( $this->get_slug() ); ?>-api_key"><?php esc_html_e( 'Private API Key', 'wop-wordpress-smtp-mail' ); ?></label>
			</div>
			<div class="wop-wordpress-smtp-mail-setting-field">
				<input name="wop-wordpress-smtp-mail[<?php echo esc_attr( $this->get_slug() ); ?>][api_key]" type="text"
					value="<?php echo esc_attr( $this->options->get( $this->get_slug(), 'api_key' ) ); ?>"
					<?php echo $this->options->is_const_defined( $this->get_slug(), 'api_key' ) ? 'disabled' : ''; ?>
					id="wop-wordpress-smtp-mail-setting-<?php echo esc_attr( $this->get_slug() ); ?>-api_key" spellcheck="false"
				/>
				<p class="desc">
					<?php
					printf(
						/* translators: %s - API key link. */
						esc_html__( 'Follow this link to get an API Key from Mailgun: %s.', 'wop-wordpress-smtp-mail' ),
						'<a href="https://app.mailgun.com/app/account/security" target="_blank" rel="noopener noreferrer">' .
						esc_html__( 'Get a Private API Key', 'wop-wordpress-smtp-mail' ) .
						'</a>'
					);
					?>
				</p>
			</div>
		</div>

		<!-- Domain -->
		<div id="wop-wordpress-smtp-mail-setting-row-<?php echo esc_attr( $this->get_slug() ); ?>-domain" class="wop-wordpress-smtp-mail-setting-row wop-wordpress-smtp-mail-setting-row-text wop-wordpress-smtp-mail-clear">
			<div class="wop-wordpress-smtp-mail-setting-label">
				<label for="wop-wordpress-smtp-mail-setting-<?php echo esc_attr( $this->get_slug() ); ?>-domain"><?php esc_html_e( 'Domain Name', 'wop-wordpress-smtp-mail' ); ?></label>
			</div>
			<div class="wop-wordpress-smtp-mail-setting-field">
				<input name="wop-wordpress-smtp-mail[<?php echo esc_attr( $this->get_slug() ); ?>][domain]" type="text"
					value="<?php echo esc_attr( $this->options->get( $this->get_slug(), 'domain' ) ); ?>"
					<?php echo $this->options->is_const_defined( $this->get_slug(), 'domain' ) ? 'disabled' : ''; ?>
					id="wop-wordpress-smtp-mail-setting-<?php echo esc_attr( $this->get_slug() ); ?>-domain" spellcheck="false"
				/>
				<p class="desc">
					<?php
					printf(
						/* translators: %s - Domain Name link. */
						esc_html__( 'Follow this link to get a Domain Name from Mailgun: %s.', 'wop-wordpress-smtp-mail' ),
						'<a href="https://app.mailgun.com/app/domains" target="_blank" rel="noopener noreferrer">' .
						esc_html__( 'Get a Domain Name', 'wop-wordpress-smtp-mail' ) .
						'</a>'
					);
					?>
				</p>
			</div>
		</div>

		<?php
	}
}
