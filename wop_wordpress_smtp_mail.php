<?php
/*
Plugin Name: WOP wordpress SMTP Mail
Version:     1.0.3
Plugin URI:  https://wordpress.org/plugins/wop-smtp-mail/
Author:      Devender Kumar
Author URI:  https://devenderkumar.com/
Description: Configure a SMTP server to send email from your WordPress site
License:     GPL2
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

define( 'WPMS_PLUGIN_VER', '1.0.3' );
define( 'WPMS_PHP_VER', '5.3' );


if ( version_compare( phpversion(), WPMS_PHP_VER, '>=' ) ) {
	require_once dirname( __FILE__ ) . '/wop-wordpress-smtp-mail.php';
	return;
}

/**
 * Array of options and their default values.
 * This is horrible, should be cleaned up at some point.
 */
global $wp_options;
$wp_options = array(
	'mail_from'                            => '',
	'mail_from_name'                       => '',
	'mailer'                               => 'smtp',
	'mail_set_return_path'                 => 'false',
	'smtp_host'                            => 'localhost',
	'smtp_port'                            => '25',
	'smtp_ssl'                             => 'none',
	'smtp_auth'                            => false,
	'smtp_user'                            => '',
	'smtp_pass'                            => '',
	'pepipost_user'                        => '',
	'pepipost_pass'                        => '',
	'pepipost_port'                        => '2525',
	'pepipost_ssl'                         => 'none',
	'wop_mail_smtp_am_notifications_hidden' => '',
);

/**
 * Activation function. This function creates the required options and defaults.
 */
if ( ! function_exists( 'wp_mail_smtp_activate' ) ) :
	/**
	 * What to do on plugin activation.
	 */
	function wp_mail_smtp_activate() {

		global $wp_options;

		// Create the required options...
		foreach ( $wp_options as $name => $val ) {
			add_option( $name, $val );
		}
	}
endif;

if ( ! function_exists( 'wp_mail_smtp_whitelist_options' ) ) :
	/**
	 * Whitelist plugin options.
	 *
	 * @param array $whitelist_options
	 *
	 * @return mixed
	 */
	function wp_mail_smtp_whitelist_options( $whitelist_options ) {

		global $wp_options;

		// Add our options to the array.
		$whitelist_options['email'] = array_keys( $wp_options );

		return $whitelist_options;
	}
endif;



	function phpmailer_init_smtp( $phpmailer ) {
		/*
		 * If constants are defined, apply them.
		 * We should have defined all required constants before using them.
		 */
		if (
			defined( 'WPMS_ON' ) && WPMS_ON &&
			defined( 'WPMS_MAILER' )
		) {
			$phpmailer->Mailer = WPMS_MAILER;

			if ( defined( 'WPMS_SET_RETURN_PATH' ) && WPMS_SET_RETURN_PATH ) {
				$phpmailer->Sender = $phpmailer->From;
			}

			if (
				WPMS_MAILER === 'smtp' &&
				defined( 'WPMS_SSL' ) &&
				defined( 'WPMS_SMTP_HOST' ) &&
				defined( 'WPMS_SMTP_PORT' )
			) {
				$phpmailer->SMTPSecure = WPMS_SSL;
				$phpmailer->Host       = WPMS_SMTP_HOST;
				$phpmailer->Port       = WPMS_SMTP_PORT;

				if (
					defined( 'WPMS_SMTP_AUTH' ) && WPMS_SMTP_AUTH &&
					defined( 'WPMS_SMTP_USER' ) &&
					defined( 'WPMS_SMTP_PASS' )
				) {
					$phpmailer->SMTPAuth = true;
					$phpmailer->Username = WPMS_SMTP_USER;
					$phpmailer->Password = WPMS_SMTP_PASS;
				}
			}
		} else {
			$option_mailer    = get_option( 'mailer' );
			$option_smtp_host = get_option( 'smtp_host' );
			$option_smtp_ssl  = get_option( 'smtp_ssl' );

			// Check that mailer is not blank, and if mailer=smtp, host is not blank.
			if (
				! $option_mailer ||
				( 'smtp' === $option_mailer && ! $option_smtp_host )
			) {
				return;
			}

			// If the mailer is pepipost, make sure we have a username and password.
			if ( 'pepipost' === $option_mailer && ( ! get_option( 'pepipost_user' ) && ! get_option( 'pepipost_pass' ) ) ) {
				return;
			}

			// Set the mailer type as per config above, this overrides the already called isMail method.
			$phpmailer->Mailer = $option_mailer;

			// Set the Sender (return-path) if required.
			if ( get_option( 'mail_set_return_path' ) ) {
				$phpmailer->Sender = $phpmailer->From;
			}

			// Set the SMTPSecure value, if set to none, leave this blank.
			$phpmailer->SMTPSecure = $option_smtp_ssl;
			if ( 'none' === $option_smtp_ssl ) {
				$phpmailer->SMTPSecure  = '';
				$phpmailer->SMTPAutoTLS = false;
			}

			// If we're sending via SMTP, set the host.
			if ( 'smtp' === $option_mailer ) {
				// Set the other options.
				$phpmailer->Host = $option_smtp_host;
				$phpmailer->Port = get_option( 'smtp_port' );

				// If we're using smtp auth, set the username & password.
				if ( get_option( 'smtp_auth' ) === 'true' ) {
					$phpmailer->SMTPAuth = true;
					$phpmailer->Username = get_option( 'smtp_user' );
					$phpmailer->Password = get_option( 'smtp_pass' );
				}
			} elseif ( 'pepipost' === $option_mailer ) {
				// Set the Pepipost settings.
				$phpmailer->Mailer     = 'smtp';
				$phpmailer->Host       = 'smtp.pepipost.com';
				$phpmailer->Port       = get_option( 'pepipost_port' );
				$phpmailer->SMTPSecure = get_option( 'pepipost_ssl' ) === 'none' ? '' : get_option( 'pepipost_ssl' );
				$phpmailer->SMTPAuth   = true;
				$phpmailer->Username   = get_option( 'pepipost_user' );
				$phpmailer->Password   = get_option( 'pepipost_pass' );
			}
		}

		// You can add your own options here, see the phpmailer documentation for more info: http://phpmailer.sourceforge.net/docs/.
		/** @noinspection PhpUnusedLocalVariableInspection It's passed by reference. */
		$phpmailer = apply_filters( 'wp_mail_smtp_custom_options', $phpmailer );
	}


	function wp_mail_smtp_options_page() {

		global $phpmailer;

		// Make sure the PHPMailer class has been instantiated
		// (copied verbatim from wp-includes/pluggable.php)
		// (Re)create it, if it's gone missing.
		if ( ! is_object( $phpmailer ) || ! is_a( $phpmailer, 'PHPMailer' ) ) {
			require_once ABSPATH . WPINC . '/class-phpmailer.php';
			$phpmailer = new PHPMailer( true );
		}

		// Send a test mail if necessary.
		if (
			isset( $_POST['wpms_action'] ) &&
			esc_html__( 'Send Test', 'wop-wordpress-smtp-mail' ) === sanitize_text_field( $_POST['wpms_action'] ) &&
			is_email( $_POST['to'] )
		) {

			check_admin_referer( 'test-email' );

			// Set up the mail variables.
			$to      = sanitize_email( $_POST['to'] );
			/* translators: %s - email address where test mail will be sent to. */
			$subject = 'WOP Wordpress Mail SMTP: ' . sprintf( esc_html__( 'Test mail to %s', 'wop-wordpress-smtp-mail' ), $to );
			$message = esc_html__( 'This is a test email generated by the WOP Wordpress Mail SMTP WordPress plugin.', 'wop-wordpress-smtp-mail' );

			// Set SMTPDebug level, default is 2 (commands + data + connection status).
			$phpmailer->SMTPDebug = apply_filters( 'wp_mail_smtp_admin_test_email_smtp_debug', 2 );

			// Start output buffering to grab smtp debugging output.
			ob_start();

			// Send the test mail.
			$result = wp_mail( $to, $subject, $message );

			// Grab the smtp debugging output.
			$smtp_debug = ob_get_clean();

			// Output the response.
			?>
			<div id="message" class="updated notice is-dismissible"><p><strong><?php esc_html_e( 'Test Message Sent', 'wop-wordpress-smtp-mail' ); ?></strong></p>
				<p><?php esc_html_e( 'The result was:', 'wop-wordpress-smtp-mail' ); ?></p>
				<pre><?php var_dump( $result ); ?></pre>

				<p><?php esc_html_e( 'The full debugging output is shown below:', 'wop-wordpress-smtp-mail' ); ?></p>
				<pre><?php print_r( $phpmailer ); ?></pre>

				<p><?php esc_html_e( 'The SMTP debugging output is shown below:', 'wop-wordpress-smtp-mail' ); ?></p>
				<pre><?php echo $smtp_debug; ?></pre>
			</div>
			<?php

			// Destroy $phpmailer so it doesn't cause issues later.
			unset( $phpmailer );
		}

		?>
		<div class="wrap">
			<h2>
				<?php esc_html_e( 'WOP Wordpress Mail SMTP Settings', 'wop-wordpress-smtp-mail' ); ?>
			</h2>

			<form method="post" action="<?php echo admin_url( 'options.php' ); ?>">
				<?php wp_nonce_field( 'email-options' ); ?>

				<table class="form-table">
					<tr valign="top">
						<th scope="row">
							<label for="mail_from"><?php esc_html_e( 'From Email', 'wop-wordpress-smtp-mail' ); ?></label>
						</th>
						<td>
							<input name="mail_from" type="email" id="mail_from" value="<?php print( get_option( 'mail_from' ) ); ?>" size="40" class="regular-text"/>

							<p class="description">
								<?php
								esc_html_e( 'You can specify the email address that emails should be sent from. If you leave this blank, the default email will be used.', 'wop-wordpress-smtp-mail' );
								if ( get_option( 'db_version' ) < 6124 ) {
									print( '<br /><span style="color: red;">' );
									_e( '<strong>Please Note:</strong> You appear to be using a version of WordPress prior to 2.3. Please ignore the From Name field and instead enter Name&lt;email@domain.com&gt; in this field.', 'wop-wordpress-smtp-mail' );
									print( '</span>' );
								}
								?>
							</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="mail_from_name"><?php esc_html_e( 'From Name', 'wop-wordpress-smtp-mail' ); ?></label>
						</th>
						<td>
							<input name="mail_from_name" type="text" id="mail_from_name" value="<?php print( get_option( 'mail_from_name' ) ); ?>" size="40" class="regular-text"/>

							<p class="description">
								<?php esc_html_e( 'You can specify the name that emails should be sent from. If you leave this blank, the emails will be sent from WordPress.', 'wop-wordpress-smtp-mail' ); ?>
							</p>
						</td>
					</tr>
				</table>

				<table class="form-table">
					<tr valign="top">
						<th scope="row">
							<?php esc_html_e( 'Mailer', 'wop-wordpress-smtp-mail' ); ?>
						</th>
						<td>
							<fieldset>
								<legend class="screen-reader-text">
									<span><?php esc_html_e( 'Mailer', 'wop-wordpress-smtp-mail' ); ?></span>
								</legend>

								<p>
									<input id="mailer_smtp" class="wpms_mailer" type="radio" name="mailer" value="smtp" <?php checked( 'smtp', get_option( 'mailer' ) ); ?> />
									<label for="mailer_smtp"><?php esc_html_e( 'Send all WordPress emails via SMTP.', 'wop-wordpress-smtp-mail' ); ?></label>
								</p>
								<p>
									<input id="mailer_mail" class="wpms_mailer" type="radio" name="mailer" value="mail" <?php checked( 'mail', get_option( 'mailer' ) ); ?> />
									<label for="mailer_mail"><?php esc_html_e( 'Use the PHP mail() function to send emails.', 'wop-wordpress-smtp-mail' ); ?></label>
								</p>

								<?php if ( wop_mail_smtp_is_pepipost_active() ) : ?>
									<p>
										<input id="mailer_pepipost" class="wpms_mailer" type="radio" name="mailer" value="pepipost" <?php checked( 'pepipost', get_option( 'mailer' ) ); ?> />
										<label for="mailer_pepipost"><?php esc_html_e( 'Use Pepipost SMTP to send emails.', 'wop-wordpress-smtp-mail' ); ?></label>
									</p>
									<p class="description">
										<?php
										printf(
											/* translators: %1$s - link start; %2$s - link end. */
											esc_html__( 'Looking for high inbox delivery? Try Pepipost with easy setup and free emails. Learn more %1$shere%2$s.', 'wop-wordpress-smtp-mail' ),
											
											'</a>'
										);
										?>
									</p>
								<?php endif; ?>
							</fieldset>
						</td>
					</tr>
				</table>

				<table class="form-table">
					<tr valign="top">
						<th scope="row">
							<?php esc_html_e( 'Return Path', 'wop-wordpress-smtp-mail' ); ?>
						</th>
						<td>
							<fieldset>
								<legend class="screen-reader-text">
									<span><?php esc_html_e( 'Return Path', 'wop-wordpress-smtp-mail' ); ?></span>
								</legend>

								<label for="mail_set_return_path">
									<input name="mail_set_return_path" type="checkbox" id="mail_set_return_path" value="true" <?php checked( 'true', get_option( 'mail_set_return_path' ) ); ?> />
									<?php esc_html_e( 'Set the return-path to match the From Email', 'wop-wordpress-smtp-mail' ); ?>
								</label>

								<p class="description">
									<?php esc_html_e( 'Return Path indicates where non-delivery receipts - or bounce messages - are to be sent.', 'wop-wordpress-smtp-mail' ); ?>
								</p>
							</fieldset>
						</td>
					</tr>
				</table>

				<table class="form-table">
					<tr valign="top">
						<th scope="row">
							<?php _e( 'Hide Announcements', 'wop-wordpress-smtp-mail' ); ?>
						</th>
						<td>
							<fieldset>
								<legend class="screen-reader-text">
									<span><?php _e( 'Hide Announcements', 'wop-wordpress-smtp-mail' ); ?></span>
								</legend>

								<label for="wp_mail_smtp_am_notifications_hidden">
									<input name="wp_mail_smtp_am_notifications_hidden" type="checkbox" id="wp_mail_smtp_am_notifications_hidden" value="true" <?php checked( 'true', get_option( 'wp_mail_smtp_am_notifications_hidden' ) ); ?> />
									<?php _e( 'Check this if you would like to hide plugin announcements and update details.', 'wop-wordpress-smtp-mail' ); ?>
								</label>
							</fieldset>
						</td>
					</tr>
				</table>

				<p class="submit">
					<input type="submit" name="submit" id="submit" class="button-primary" value="<?php esc_attr_e( 'Save Changes', 'wop-wordpress-smtp-mail' ); ?>"/>
				</p>

				<div id="wpms_section_smtp" class="wpms_section">
					<h3>
						<?php esc_html_e( 'SMTP Options', 'wop-wordpress-smtp-mail' ); ?>
					</h3>
					<p><?php esc_html_e( 'These options only apply if you have chosen to send mail by SMTP above.', 'wop-wordpress-smtp-mail' ); ?></p>

					<table class="form-table">
						<tr valign="top">
							<th scope="row">
								<label for="smtp_host"><?php esc_html_e( 'SMTP Host', 'wop-wordpress-smtp-mail' ); ?></label>
							</th>
							<td>
								<input name="smtp_host" type="text" id="smtp_host" value="<?php print( get_option( 'smtp_host' ) ); ?>" size="40" class="regular-text"/>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">
								<label for="smtp_port"><?php esc_html_e( 'SMTP Port', 'wop-wordpress-smtp-mail' ); ?></label>
							</th>
							<td>
								<input name="smtp_port" type="text" id="smtp_port" value="<?php print( get_option( 'smtp_port' ) ); ?>" size="6" class="regular-text"/>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"><?php esc_html_e( 'Encryption', 'wop-wordpress-smtp-mail' ); ?> </th>
							<td>
								<fieldset>
									<legend class="screen-reader-text">
										<span><?php esc_html_e( 'Encryption', 'wop-wordpress-smtp-mail' ); ?></span>
									</legend>

									<input id="smtp_ssl_none" type="radio" name="smtp_ssl" value="none" <?php checked( 'none', get_option( 'smtp_ssl' ) ); ?> />
									<label for="smtp_ssl_none">
										<span><?php esc_html_e( 'No encryption.', 'wop-wordpress-smtp-mail' ); ?></span>
									</label><br/>

									<input id="smtp_ssl_ssl" type="radio" name="smtp_ssl" value="ssl" <?php checked( 'ssl', get_option( 'smtp_ssl' ) ); ?> />
									<label for="smtp_ssl_ssl">
										<span><?php esc_html_e( 'Use SSL encryption.', 'wop-wordpress-smtp-mail' ); ?></span>
									</label><br/>

									<input id="smtp_ssl_tls" type="radio" name="smtp_ssl" value="tls" <?php checked( 'tls', get_option( 'smtp_ssl' ) ); ?> />
									<label for="smtp_ssl_tls">
										<span><?php esc_html_e( 'Use TLS encryption.', 'wop-wordpress-smtp-mail' ); ?></span>
									</label>

									<p class="description"><?php esc_html_e( 'TLS is not the same as STARTTLS. For most servers SSL is the recommended option.', 'wop-wordpress-smtp-mail' ); ?></p>
								</fieldset>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"><?php esc_html_e( 'Authentication', 'wop-wordpress-smtp-mail' ); ?> </th>
							<td>
								<fieldset>
									<legend class="screen-reader-text">
										<span><?php esc_html_e( 'Authentication', 'wop-wordpress-smtp-mail' ); ?></span>
									</legend>

									<input id="smtp_auth_false" type="radio" name="smtp_auth" value="false" <?php checked( 'false', get_option( 'smtp_auth' ) ); ?> />
									<label for="smtp_auth_false">
										<span><?php esc_html_e( 'No: Do not use SMTP authentication.', 'wop-wordpress-smtp-mail' ); ?></span>
									</label><br/>

									<input id="smtp_auth_true" type="radio" name="smtp_auth" value="true" <?php checked( 'true', get_option( 'smtp_auth' ) ); ?> />
									<label for="smtp_auth_true">
										<span><?php esc_html_e( 'Yes: Use SMTP authentication.', 'wop-wordpress-smtp-mail' ); ?></span>
									</label><br/>

									<p class="description">
										<?php esc_html_e( 'If this is set to no, the values below are ignored.', 'wop-wordpress-smtp-mail' ); ?>
									</p>
								</fieldset>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">
								<label for="smtp_user"><?php esc_html_e( 'Username', 'wop-wordpress-smtp-mail' ); ?></label>
							</th>
							<td>
								<input name="smtp_user" type="text" id="smtp_user" value="<?php print( get_option( 'smtp_user' ) ); ?>" size="40" class="code" autocomplete="off"/>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">
								<label for="smtp_pass"><?php esc_html_e( 'Password', 'wop-wordpress-smtp-mail' ); ?></label>
							</th>
							<td>
								<input name="smtp_pass" type="password" id="smtp_pass" value="<?php print( get_option( 'smtp_pass' ) ); ?>" size="40" class="code" autocomplete="off"/>

								<p class="description">
									<?php esc_html_e( 'This is in plain text because it must not be stored encrypted.', 'wop-wordpress-smtp-mail' ); ?>
								</p>
							</td>
						</tr>
					</table>

					<p class="submit">
						<input type="submit" name="submit" id="submit" class="button-primary" value="<?php esc_attr_e( 'Save Changes', 'wop-wordpress-smtp-mail' ); ?>"/>
					</p>
				</div><!-- #wpms_section_smtp -->

				<?php if ( wop_mail_smtp_is_pepipost_active() ) : ?>
					<div id="wpms_section_pepipost" class="wpms_section">
						<h3>
							<?php esc_html_e( 'Pepipost SMTP Options', 'wop-wordpress-smtp-mail' ); ?>
						</h3>
						<p>
							<?php
							printf(
								/* translators: %s - Pepipost registration URL. */
								esc_html__( 'You need to signup on %s to get the SMTP username/password.', 'wop-wordpress-smtp-mail' ),

								''
							);
							?>
						</p>
						<table class="form-table">
							<tr valign="top">
								<th scope="row">
									<label for="pepipost_user"><?php esc_html_e( 'Username', 'wop-wordpress-smtp-mail' ); ?></label>
								</th>
								<td>
									<input name="pepipost_user" type="text" id="pepipost_user" value="<?php print( get_option( 'pepipost_user' ) ); ?>" size="40" class="code"/>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row">
									<label for="pepipost_pass"><?php esc_html_e( 'Password', 'wop-wordpress-smtp-mail' ); ?></label>
								</th>
								<td>
									<input name="pepipost_pass" type="text" id="pepipost_pass" value="<?php print( get_option( 'pepipost_pass' ) ); ?>" size="40" class="code"/>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row">
									<label for="pepipost_port"><?php esc_html_e( 'SMTP Port', 'wop-wordpress-smtp-mail' ); ?></label>
								</th>
								<td>
									<input name="pepipost_port" type="text" id="pepipost_port" value="<?php print( get_option( 'pepipost_port' ) ); ?>" size="6" class="regular-text"/>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row">
									<?php esc_html_e( 'Encryption', 'wop-wordpress-smtp-mail' ); ?>
								</th>
								<td>
									<fieldset>
										<legend class="screen-reader-text">
											<span>
												<?php esc_html_e( 'Encryption', 'wop-wordpress-smtp-mail' ); ?>
											</span>
										</legend>

										<input id="pepipost_ssl_none" type="radio" name="pepipost_ssl" value="none" <?php checked( 'none', get_option( 'pepipost_ssl' ) ); ?> />
										<label for="pepipost_ssl_none">
											<span><?php esc_html_e( 'No encryption.', 'wop-wordpress-smtp-mail' ); ?></span>
										</label><br/>

										<input id="pepipost_ssl_ssl" type="radio" name="pepipost_ssl" value="ssl" <?php checked( 'ssl', get_option( 'pepipost_ssl' ) ); ?> />
										<label for="pepipost_ssl_ssl">
											<span><?php esc_html_e( 'Use SSL encryption.', 'wop-wordpress-smtp-mail' ); ?></span>
										</label><br/>

										<input id="pepipost_ssl_tls" type="radio" name="pepipost_ssl" value="tls" <?php checked( 'tls', get_option( 'pepipost_ssl' ) ); ?> />
										<label for="pepipost_ssl_tls">
											<span><?php esc_html_e( 'Use TLS encryption.', 'wop-wordpress-smtp-mail' ); ?></span>
										</label>
									</fieldset>
								</td>
							</tr>
						</table>

						<p class="submit">
							<input type="submit" name="submit" id="submit" class="button-primary" value="<?php esc_attr_e( 'Save Changes', 'wop-wordpress-smtp-mail' ); ?>"/>
						</p>
					</div><!-- #wpms_section_pepipost -->
				<?php endif; ?>

				<input type="hidden" name="action" value="update"/>
				<input type="hidden" name="option_page" value="email">
			</form>

			<h3><?php esc_html_e( 'Send a Test Email', 'wop-wordpress-smtp-mail' ); ?></h3>

			<form method="POST" action="">
				<?php wp_nonce_field( 'test-email' ); ?>

				<table class="form-table">
					<tr valign="top">
						<th scope="row">
							<label for="to"><?php esc_html_e( 'To', 'wop-wordpress-smtp-mail' ); ?></label>
						</th>
						<td>
							<input name="to" type="email" id="to" value="" size="40" class="code"/>
							<p class="description"><?php esc_html_e( 'Type an email address here and then click Send Test to generate a test email.', 'wop-wordpress-smtp-mail' ); ?></p>
						</td>
					</tr>
				</table>

				<p class="submit">
					<input type="submit" name="wpms_action" id="wpms_action" class="button-primary" value="<?php esc_attr_e( 'Send Test', 'wop-wordpress-smtp-mail' ); ?>"/>
				</p>
			</form>

			<script type="text/javascript">
				/* globals jQuery */
				var wpmsOnMailerChange = function ( mailer ) {
					// Hide all the mailer forms.
					jQuery( '.wpms_section' ).hide();
					// Show the target mailer form.
					jQuery( '#wpms_section_' + mailer ).show();
				};
				jQuery( document ).ready( function () {
					// Call wpmsOnMailerChange() on startup with the current mailer.
					wpmsOnMailerChange( jQuery( 'input.wpms_mailer:checked' ).val() );

					// Watch the mailer for any changes
					jQuery( 'input.wpms_mailer' ).on( 'change', function ( e ) {
						// Call the wpmsOnMailerChange() handler, passing the value of the newly selected mailer.
						wpmsOnMailerChange( jQuery( e.target ).val() );
					} );
				} );
			</script>

		</div>
		<?php
	} // End of wp_mail_smtp_options_page() function definition.


if ( ! function_exists( 'wp_mail_smtp_menus' ) ) :
	/**
	 * This function adds the required page (only 1 at the moment).
	 */
	function wp_mail_smtp_menus() {

		if ( function_exists( 'add_submenu_page' ) ) {
			add_options_page( esc_html__( 'WOP Wordpress Mail SMTP Settings', 'wop-wordpress-smtp-mail' ), esc_html__( 'WOP Wordpress Mail SMTP', 'wop-wordpress-smtp-mail' ), 'manage_options', __FILE__, 'wp_mail_smtp_options_page' );
		}
	} // End of wp_mail_smtp_menus() function definition.
endif;

if ( ! function_exists( 'wp_mail_smtp_mail_from' ) ) :
	/**
	 * This function sets the from email value.
	 *
	 * @param string $orig
	 *
	 * @return string
	 */
	function wp_mail_smtp_mail_from( $orig ) {
		/*
		 * This is copied from pluggable.php lines 348-354 as at revision 10150
		 * http://trac.wordpress.org/browser/branches/2.7/wp-includes/pluggable.php#L348.
		 */

		// In case of CLI we don't have SERVER_NAME, so use host name instead, may be not a domain name.
		$server_name = ! empty( $_SERVER['SERVER_NAME'] ) ? $_SERVER['SERVER_NAME'] : wop_parse_url( get_home_url( get_current_blog_id() ), PHP_URL_HOST );

		// Get the site domain and get rid of www.
		$sitename = strtolower( $server_name );
		if ( substr( $sitename, 0, 4 ) === 'www.' ) {
			$sitename = substr( $sitename, 4 );
		}

		$default_from = 'wordpress@' . $sitename;

		/*
		 * End of copied code.
		 */

		// If the from email is not the default, return it unchanged.
		if ( $orig !== $default_from ) {
			return $orig;
		}

		if (
			defined( 'WPMS_ON' ) && WPMS_ON &&
			defined( 'WPMS_MAIL_FROM' )
		) {
			$mail_from_email = WPMS_MAIL_FROM;

			if ( ! empty( $mail_from_email ) ) {
				return $mail_from_email;
			}
		}

		if ( is_email( get_option( 'mail_from' ), false ) ) {
			return get_option( 'mail_from' );
		}

		// If in doubt, return the original value.
		return $orig;
	} // End of wp_mail_smtp_mail_from() function definition.
endif;

if ( ! function_exists( 'wp_mail_smtp_mail_from_name' ) ) :
	/**
	 * This function sets the from name value.
	 *
	 * @param string $orig
	 *
	 * @return string
	 */
	function wp_mail_smtp_mail_from_name( $orig ) {

		// Only filter if the from name is the default.
		if ( 'WordPress' === $orig ) {
			if (
				defined( 'WPMS_ON' ) && WPMS_ON &&
				defined( 'WPMS_MAIL_FROM_NAME' )
			) {
				$mail_from_name = WPMS_MAIL_FROM_NAME;

				if ( ! empty( $mail_from_name ) ) {
					return $mail_from_name;
				}
			}

			$from_name = get_option( 'mail_from_name' );
			if ( ! empty( $from_name ) && is_string( $from_name ) ) {
				return $from_name;
			}
		}

		return $orig;
	}
endif;

/**
 * Add a link to Settings page of a plugin on Plugins page.
 *
 * @param array $links
 * @param string $file
 *
 * @return mixed
 */
function wop_mail_plugin_action_linksone( $links, $file ) {

	if ( plugin_basename( __FILE__ ) !== $file ) {
		return $links;
	}

	$settings_link = '<a href="options-general.php?page=' . plugin_basename( __FILE__ ) . '">' . esc_html__( 'Settings', 'wop-wordpress-smtp-mail' ) . '</a>';

	array_unshift( $links, $settings_link );

	return $links;
}

/**
 * Awesome Motive Notifications.
 *
 * @since 0.11
 */
function wop_mail_smtp_am_notifications() {

	$is_hidden = get_option( 'wop_mail_smtp_am_notifications_hidden', '' );

	if ( 'true' === $is_hidden ) {
		return;
	}

	if ( ! class_exists( 'WPMS_AM_Notification' ) ) {
		require_once dirname( __FILE__ ) . '/class-wpms-am-notification.php';
	}

	new WPMS_AM_Notification( 'smtp', WPMS_PLUGIN_VER );
}

add_action( 'plugins_loaded', 'wop_mail_smtp_am_notifications' );

/**
 * Check whether the site is using Pepipost or not.
 *
 * @since 0.11
 *
 * @return bool
 */
function wop_mail_smtp_is_pepipost_active() {
	return apply_filters( 'wp_mail_smtp_options_is_pepipost_active', 'pepipost' === get_option( 'mailer' ) );
}

/**
 * Check the current PHP version and display a notice if on unsupported PHP.
 *
 * @since 0.11
 */
function wop_mail_smtp_check_php_version() {

	// Display for PHP below 5.3.
	if ( version_compare( PHP_VERSION, '5.3.0', '>=' ) ) {
		return;
	}

	// Display for admins only.
	if ( ! is_super_admin() ) {
		return;
	}

	// Display on Dashboard page only.
	if ( isset( $GLOBALS['pagenow'] ) && 'index.php' !== $GLOBALS['pagenow'] ) {
		return;
	}

	echo '<div class="notice notice-error">' .
		'<p>' .
		sprintf(
			/* translators: %1$s - WOP Wordpress Mail SMTP plugin name; %2$s - opening a link tag; %3$s - closing a link tag. */
			esc_html__(
				'Your site is running an outdated version of PHP. may cause issues with %1$s. %2$sRead more%3$s for additional information.',
				'wpforms'
			),
			'<strong>WOP Wordpress Mail SMTP</strong>',
			'<a href="#" target="_blank">',
			'</a>'
		) .
		'</p>' .
	'</div>';
}

add_action( 'admin_notices', 'wop_mail_smtp_check_php_version' );

// Add an action on phpmailer_init.
add_action( 'phpmailer_init', 'phpmailer_init_smtp' );

if ( ! defined( 'WPMS_ON' ) || ! WPMS_ON ) {
	// Whitelist our options.
	add_filter( 'whitelist_options', 'wp_mail_smtp_whitelist_options' );
	// Add the create pages options.
	add_action( 'admin_menu', 'wp_mail_smtp_menus' );
	// Add an activation hook for this plugin.
	register_activation_hook( __FILE__, 'wp_mail_smtp_activate' );
	// Adds "Settings" link to the Plugins page.
	add_filter( 'plugin_action_links', 'wop_mail_plugin_action_linksone', 10, 2 );
}

// Add filters to replace the mail from name and email address.
add_filter( 'wp_mail_from', 'wp_mail_smtp_mail_from' );
add_filter( 'wp_mail_from_name', 'wp_mail_smtp_mail_from_name' );

load_plugin_textdomain( 'wop-wordpress-smtp-mail', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
