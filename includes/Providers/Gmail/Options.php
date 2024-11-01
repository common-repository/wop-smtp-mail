<?php

namespace WOPMailSMTP\Providers\Gmail;

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
				'logo_url'    => wop_wordpress_mail_smtp()->plugin_url . '/assets/images/gmail.png',
				'slug'        => 'gmail',
				'title'       => esc_html__( 'Gmail', 'wop-wordpress-smtp-mail' ),
				'description' => sprintf(
					wp_kses(
						/* translators: %1$s - opening link tag; %2$s - closing link tag. */
						__( 'Send emails using your Gmail or G Suite (formerly Google Apps) account, all while keeping your login credentials safe. Other Google SMTP methods require enabling less secure apps in your account and entering your password. However, this integration uses the Google API to improve email delivery issues while keeping your site secure.<br><br>Read our %1$sGmail documentation%2$s to learn how to configure Gmail or G Suite.', 'wop-wordpress-smtp-mail' ),
						array(
							'br' => array(),
							'a'  => array(
								'href'   => array(),
								'rel'    => array(),
								'target' => array(),
							),
						)
					),
					'<a href="https://wpforms.com/how-to-securely-send-wordpress-emails-using-gmail-smtp/" target="_blank" rel="noopener noreferrer">',
					'</a>'
				),
				'php'         => '5.5',
			)
		);
	}

	/**
	 * @inheritdoc
	 */
	public function display_options() {

		// Do not display options if PHP version is not correct.
		if ( ! $this->is_php_correct() ) {
			$this->display_php_warning();

			return;
		}
		?>

		<!-- Client ID -->
		<div id="wop-wordpress-smtp-mail-setting-row-<?php echo esc_attr( $this->get_slug() ); ?>-client_id" class="wop-wordpress-smtp-mail-setting-row wop-wordpress-smtp-mail-setting-row-text wop-wordpress-smtp-mail-clear">
			<div class="wop-wordpress-smtp-mail-setting-label">
				<label for="wop-wordpress-smtp-mail-setting-<?php echo esc_attr( $this->get_slug() ); ?>-client_id"><?php esc_html_e( 'Client ID', 'wop-wordpress-smtp-mail' ); ?></label>
			</div>
			<div class="wop-wordpress-smtp-mail-setting-field">
				<input name="wop-wordpress-smtp-mail[<?php echo esc_attr( $this->get_slug() ); ?>][client_id]" type="text"
					value="<?php echo esc_attr( $this->options->get( $this->get_slug(), 'client_id' ) ); ?>"
					<?php echo $this->options->is_const_defined( $this->get_slug(), 'client_id' ) ? 'disabled' : ''; ?>
					id="wop-wordpress-smtp-mail-setting-<?php echo esc_attr( $this->get_slug() ); ?>-client_id" spellcheck="false"
				/>
			</div>
		</div>

		<!-- Client Secret -->
		<div id="wop-wordpress-smtp-mail-setting-row-<?php echo esc_attr( $this->get_slug() ); ?>-client_secret" class="wop-wordpress-smtp-mail-setting-row wop-wordpress-smtp-mail-setting-row-text wop-wordpress-smtp-mail-clear">
			<div class="wop-wordpress-smtp-mail-setting-label">
				<label for="wop-wordpress-smtp-mail-setting-<?php echo esc_attr( $this->get_slug() ); ?>-client_secret"><?php esc_html_e( 'Client Secret', 'wop-wordpress-smtp-mail' ); ?></label>
			</div>
			<div class="wop-wordpress-smtp-mail-setting-field">
				<input name="wop-wordpress-smtp-mail[<?php echo esc_attr( $this->get_slug() ); ?>][client_secret]" type="text"
					value="<?php echo esc_attr( $this->options->get( $this->get_slug(), 'client_secret' ) ); ?>"
					<?php echo $this->options->is_const_defined( $this->get_slug(), 'client_secret' ) ? 'disabled' : ''; ?>
					id="wop-wordpress-smtp-mail-setting-<?php echo esc_attr( $this->get_slug() ); ?>-client_secret" spellcheck="false"
				/>
			</div>
		</div>

		<!-- Authorized redirect URI -->
		<div id="wop-wordpress-smtp-mail-setting-row-<?php echo esc_attr( $this->get_slug() ); ?>-client_redirect" class="wop-wordpress-smtp-mail-setting-row wop-wordpress-smtp-mail-setting-row-text wop-wordpress-smtp-mail-clear">
			<div class="wop-wordpress-smtp-mail-setting-label">
				<label for="wop-wordpress-smtp-mail-setting-<?php echo esc_attr( $this->get_slug() ); ?>-client_redirect"><?php esc_html_e( 'Authorized redirect URI', 'wop-wordpress-smtp-mail' ); ?></label>
			</div>
			<div class="wop-wordpress-smtp-mail-setting-field">
				<input type="text" readonly="readonly"
					value="<?php echo esc_attr( Auth::get_plugin_auth_url() ); ?>"
					id="wop-wordpress-smtp-mail-setting-<?php echo esc_attr( $this->get_slug() ); ?>-client_redirect"
				/>
				<button type="button" class="wop-wordpress-smtp-mail-btn wop-wordpress-smtp-mail-btn-md wop-wordpress-smtp-mail-btn-light-grey wop-wordpress-smtp-mail-setting-copy"
					title="<?php esc_attr_e( 'Copy URL to clipboard', 'wop-wordpress-smtp-mail' ); ?>"
					data-source_id="wop-wordpress-smtp-mail-setting-<?php echo esc_attr( $this->get_slug() ); ?>-client_redirect">
					<span class="dashicons dashicons-admin-page"></span>
				</button>
				<p class="desc">
					<?php esc_html_e( 'This is the path on your site that you will be redirected to after you have authenticated with Google.', 'wop-wordpress-smtp-mail' ); ?>
					<br>
					<?php esc_html_e( 'You need to copy this URL into "Authorized redirect URIs" field for you web application on Google APIs site for your project there.', 'wop-wordpress-smtp-mail' ); ?>
				</p>
			</div>
		</div>

		<!-- Auth users button -->
		<?php $auth = new Auth(); ?>
		<?php if ( $auth->is_clients_saved() && $auth->is_auth_required() ) : ?>
			<div id="wop-wordpress-smtp-mail-setting-row-<?php echo esc_attr( $this->get_slug() ); ?>-authorize" class="wop-wordpress-smtp-mail-setting-row wop-wordpress-smtp-mail-setting-row-text wop-wordpress-smtp-mail-clear">
				<div class="wop-wordpress-smtp-mail-setting-label">
					<label><?php esc_html_e( 'Authorize', 'wop-wordpress-smtp-mail' ); ?></label>
				</div>
				<div class="wop-wordpress-smtp-mail-setting-field">
					<a href="<?php echo esc_url( $auth->get_google_auth_url() ); ?>" class="wop-wordpress-smtp-mail-btn wop-wordpress-smtp-mail-btn-md wop-wordpress-smtp-mail-btn-orange">
						<?php esc_html_e( 'Allow plugin to send emails using your Google account', 'wop-wordpress-smtp-mail' ); ?>
					</a>
					<p class="desc">
						<?php esc_html_e( 'Click the button above to confirm authorization.', 'wop-wordpress-smtp-mail' ); ?>
					</p>
				</div>
			</div>
		<?php endif; ?>

		<?php
	}
}
