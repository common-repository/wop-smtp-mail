<?php

namespace WOPMailSMTP\Admin\Pages;

use WOPMailSMTP\Admin\PageAbstract;
use WOPMailSMTP\Options;
use WOPMailSMTP\WP;

/**
 * Class Settings is part of Area, displays general settings of the plugin.
 *
 * @since 1.0.0
 */
class Settings extends PageAbstract {

	/**
	 * @var string Slug of a tab.
	 */
	protected $slug = 'settings';

	/**
	 * @inheritdoc
	 */
	public function get_label() {
		return esc_html__( 'Settings', 'wop-wordpress-smtp-mail' );
	}

	/**
	 * @inheritdoc
	 */
	public function get_title() {
		return $this->get_label();
	}

	/**
	 * @inheritdoc
	 */
	public function display() {

		$options = new Options();
		$mailer  = $options->get( 'mail', 'mailer' );
		?>

		<form method="POST" action="">
			<?php $this->wp_nonce_field(); ?>

			<!-- Mail Section Title -->
			<div class="wop-wordpress-smtp-mail-setting-row wop-wordpress-smtp-mail-setting-row-content wop-wordpress-smtp-mail-clear section-heading no-desc" id="wop-wordpress-smtp-mail-setting-row-email-heading">
				<div class="wop-wordpress-smtp-mail-setting-field">
					<h2><?php esc_html_e( 'Mail Settings', 'wop-wordpress-smtp-mail' ); ?></h2>
					<p> You can request your hosting provider for the SMTP details of your site.</p>
				</div>
			</div>

			<!-- From Email -->
			<div id="wop-wordpress-smtp-mail-setting-row-from_email" class="wop-wordpress-smtp-mail-setting-row wop-wordpress-smtp-mail-setting-row-email wop-wordpress-smtp-mail-clear">
				<div class="wop-wordpress-smtp-mail-setting-label">
					<label for="wop-wordpress-smtp-mail-setting-from_email"><?php esc_html_e( 'From Email', 'wop-wordpress-smtp-mail' ); ?></label>
				</div>
				<div class="wop-wordpress-smtp-mail-setting-field">
					<input name="wop-wordpress-smtp-mail[mail][from_email]" type="email"
						value="<?php echo esc_attr( $options->get( 'mail', 'from_email' ) ); ?>"
						<?php echo $options->is_const_defined( 'mail', 'from_email' ) ? 'disabled' : ''; ?>
						id="wop-wordpress-smtp-mail-setting-from_email" spellcheck="false"
					/>
					<p class="desc">
						<?php esc_html_e( 'You can specify the email address that emails should be sent from.', 'wop-wordpress-smtp-mail' ); ?><br/>
						<?php
						printf(
							/* translators: %s - default email address. */
							esc_html__( 'If you leave this blank, the default one will be used: %s.', 'wop-wordpress-smtp-mail' ),
							'<code>' . wop_wordpress_mail_smtp()->get_processor()->get_default_email() . '</code>'
						);
						?>
					</p>
					<p class="desc">
						<?php esc_html_e( 'Please note if you are sending using an email provider (Gmail, Yahoo, Hotmail, Outlook.com, etc) this setting should be your email address for that account.', 'wop-wordpress-smtp-mail' ); ?>
					</p>
				</div>
			</div>

			<!-- From Name -->
			<div id="wop-wordpress-smtp-mail-setting-row-from_name" class="wop-wordpress-smtp-mail-setting-row wop-wordpress-smtp-mail-setting-row-text wop-wordpress-smtp-mail-clear">
				<div class="wop-wordpress-smtp-mail-setting-label">
					<label for="wop-wordpress-smtp-mail-setting-from_name"><?php esc_html_e( 'From Name', 'wop-wordpress-smtp-mail' ); ?></label>
				</div>
				<div class="wop-wordpress-smtp-mail-setting-field">
					<input name="wop-wordpress-smtp-mail[mail][from_name]" type="text"
						value="<?php echo esc_attr( $options->get( 'mail', 'from_name' ) ); ?>"
						<?php echo $options->is_const_defined( 'mail', 'from_name' ) ? 'disabled' : ''; ?>
						id="wop-wordpress-smtp-mail-setting-from-name" spellcheck="false"
					/>
					<p class="desc">
						<?php esc_html_e( 'You can specify the name that emails should be sent from.', 'wop-wordpress-smtp-mail' ); ?><br/>
						<?php
						printf(
							/* translators: %s - WordPress. */
							esc_html__( 'If you leave this blank, the emails will be sent from %s.', 'wop-wordpress-smtp-mail' ),
							'<code>WordPress</code>'
						);
						?>
					</p>
				</div>
			</div>

			<!-- Mailer -->
			<div id="wop-wordpress-smtp-mail-setting-row-mailer" class="wop-wordpress-smtp-mail-setting-row wop-wordpress-smtp-mail-setting-row-mailer wop-wordpress-smtp-mail-clear">
				<div class="wop-wordpress-smtp-mail-setting-label">
					<label for="wop-wordpress-smtp-mail-setting-mailer"><?php esc_html_e( 'Mailer', 'wop-wordpress-smtp-mail' ); ?></label>
				</div>
				<div class="wop-wordpress-smtp-mail-setting-field">
					<div class="wop-wordpress-smtp-mail-mailers">

						<?php foreach ( wop_wordpress_mail_smtp()->get_providers()->get_options_all() as $provider ) : ?>

							<div class="wop-wordpress-smtp-mail-mailer <?php echo $mailer === $provider->get_slug() ? 'active' : ''; ?>">
								<!--<div class="wop-wordpress-smtp-mail-mailer-image">
									<img src="<?php //echo esc_url( $provider->get_logo_url() ); ?>"
										alt="<?php //echo esc_attr( $provider->get_title() ); ?>">
								</div>-->

								<div class="wop-wordpress-smtp-mail-mailer-text">
									<input id="wop-wordpress-smtp-mail-setting-mailer-<?php echo esc_attr( $provider->get_slug() ); ?>"
										type="radio" name="wop-wordpress-smtp-mail[mail][mailer]"
										value="<?php echo esc_attr( $provider->get_slug() ); ?>"
										<?php checked( $provider->get_slug(), $mailer ); ?>
										<?php echo $options->is_const_defined( 'mail', 'mailer' ) ? 'disabled' : ''; ?>
									/>
									<label for="wop-wordpress-smtp-mail-setting-mailer-<?php echo esc_attr( $provider->get_slug() ); ?>"><?php echo $provider->get_title(); ?></label>
								</div>
							</div>

						<?php endforeach; ?>

					</div>
				</div>
			</div>

			<!-- Return Path -->
			

			<!-- Mailer Options -->
			<div class="wop-wordpress-smtp-mail-mailer-options">
				<?php foreach ( wop_wordpress_mail_smtp()->get_providers()->get_options_all() as $provider ) : ?>

					<div class="wop-wordpress-smtp-mail-mailer-option wop-wordpress-smtp-mail-mailer-option-<?php echo esc_attr( $provider->get_slug() ); ?> <?php echo $mailer === $provider->get_slug() ? 'active' : 'hidden'; ?>">

						<!-- Mailer Option Title -->
						<?php $provider_desc = $provider->get_description(); ?>
						<div class="wop-wordpress-smtp-mail-setting-row wop-wordpress-smtp-mail-setting-row-content wop-wordpress-smtp-mail-clear section-heading <?php empty( $provider_desc ) ? 'no-desc' : ''; ?>" id="wop-wordpress-smtp-mail-setting-row-email-heading">
							<div class="wop-wordpress-smtp-mail-setting-field">
								<h2><?php echo $provider->get_title(); ?></h2>
								<?php if ( ! empty( $provider_desc ) ) : ?>
									<p class="desc"><?php echo $provider_desc; ?></p>
								<?php endif; ?>
							</div>
						</div>

						<?php $provider->display_options(); ?>
					</div>

				<?php endforeach; ?>

			</div>

			<p class="wop-wordpress-smtp-mail-submit">
				<button type="submit" class="wop-wordpress-smtp-mail-btn wop-wordpress-smtp-mail-btn-md wop-wordpress-smtp-mail-btn-orange"><?php esc_html_e( 'Save Settings', 'wop-wordpress-smtp-mail' ); ?></button>
			</p>

		</form>

		<?php
	}

	/**
	 * @inheritdoc
	 */
	public function process_post( $data ) {

		$this->check_admin_referer();

		$options = new Options();
		$old_opt = $options->get_all();

		$to_redirect = false;

		// Old and new Gmail client id/secret values are different - we need to invalidate tokens and scroll to Auth button.
		if (
			$options->get( 'mail', 'mailer' ) === 'gmail' &&
			(
				$options->get( 'gmail', 'client_id' ) !== $data['gmail']['client_id'] ||
				$options->get( 'gmail', 'client_secret' ) !== $data['gmail']['client_secret']
			)
		) {
			unset( $old_opt['gmail'] );

			if (
				! empty( $data['gmail']['client_id'] ) &&
				! empty( $data['gmail']['client_secret'] )
			) {
				$to_redirect = true;
			}
		}

		// New gmail clients data will be added from new $data, except the old access/refresh_token.
		$to_save = array_merge( $old_opt, $data );

		// All the sanitization is done there.
		$options->set( $to_save );

		if ( $to_redirect ) {
			wp_redirect( $_POST['_wp_http_referer'] . '#wop-wordpress-smtp-mail-setting-row-gmail-authorize' );
			exit;
		}

		WP::add_admin_notice(
			esc_html__( 'Settings were successfully saved.', 'wop-wordpress-smtp-mail' ),
			WP::ADMIN_NOTICE_SUCCESS
		);
	}
}
