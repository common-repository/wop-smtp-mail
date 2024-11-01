<?php

namespace WOPMailSMTP\Providers;

use WOPMailSMTP\Options;

/**
 * Abstract Class ProviderAbstract to contain common providers functionality.
 *
 * @since 1.0.0
 */
abstract class OptionAbstract implements OptionInterface {

	/**
	 * @var string
	 */
	private $logo_url = '';
	/**
	 * @var string
	 */
	private $slug = '';
	/**
	 * @var string
	 */
	private $title = '';
	/**
	 * @var string
	 */
	private $description = '';
	/**
	 * @var string
	 */
	private $php = WPMS_PHP_VER;
	/**
	 * @var Options
	 */
	protected $options;

	/**
	 * ProviderAbstract constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param array $params
	 */
	public function __construct( $params ) {

		if (
			empty( $params['slug'] ) ||
			empty( $params['title'] )
		) {
			return;
		}

		$this->slug  = sanitize_key( $params['slug'] );
		$this->title = sanitize_text_field( $params['title'] );

		if ( ! empty( $params['description'] ) ) {
			$this->description = wp_kses( $params['description'],
				array(
					'br' => array(),
					'a'  => array(
						'href'   => array(),
						'rel'    => array(),
						'target' => array(),
					),
				)
			);
		}

		if ( ! empty( $params['php'] ) ) {
			$this->php = sanitize_text_field( $params['php'] );
		}

		if ( ! empty( $params['logo_url'] ) ) {
			$this->logo_url = esc_url_raw( $params['logo_url'] );
		}

		$this->options = new Options();
	}

	/**
	 * @inheritdoc
	 */
	public function get_logo_url() {
		return apply_filters( 'wp_mail_smtp_providers_provider_get_logo_url', $this->logo_url, $this );
	}

	/**
	 * @inheritdoc
	 */
	public function get_slug() {
		return apply_filters( 'wp_mail_smtp_providers_provider_get_slug', $this->slug, $this );
	}

	/**
	 * @inheritdoc
	 */
	public function get_title() {
		return apply_filters( 'wp_mail_smtp_providers_provider_get_title', $this->title, $this );
	}

	/**
	 * @inheritdoc
	 */
	public function get_description() {
		return apply_filters( 'wp_mail_smtp_providers_provider_get_description', $this->description, $this );
	}

	/**
	 * @inheritdoc
	 */
	public function get_php_version() {
		return apply_filters( 'wp_mail_smtp_providers_provider_get_php_version', $this->php, $this );
	}

	/**
	 * @inheritdoc
	 */
	public function display_options() {
		?>

		<!-- SMTP Host -->
		<div id="wop-wordpress-smtp-mail-setting-row-<?php echo esc_attr( $this->get_slug() ); ?>-host" class="wop-wordpress-smtp-mail-setting-row wop-wordpress-smtp-mail-setting-row-text wop-wordpress-smtp-mail-clear">
			<div class="wop-wordpress-smtp-mail-setting-label">
				<label for="wop-wordpress-smtp-mail-setting-<?php echo esc_attr( $this->get_slug() ); ?>-host"><?php esc_html_e( 'Enter Your SMTP Host', 'wop-wordpress-smtp-mail' ); ?></label>
			</div>
			<div class="wop-wordpress-smtp-mail-setting-field">
				<input name="wop-wordpress-smtp-mail[<?php echo esc_attr( $this->get_slug() ); ?>][host]" type="text"
					value="<?php echo esc_attr( $this->options->get( $this->get_slug(), 'host' ) ); ?>"
					<?php echo $this->options->is_const_defined( $this->get_slug(), 'host' ) ? 'disabled' : ''; ?>
					id="wop-wordpress-smtp-mail-setting-<?php echo esc_attr( $this->get_slug() ); ?>-host" spellcheck="false"/>
			<p class="desc">You can request your hosting provider for the SMTP details of your site.</p>
			</div>
		</div>

		<!-- SMTP Port -->
		<div id="wop-wordpress-smtp-mail-setting-row-<?php echo esc_attr( $this->get_slug() ); ?>-port" class="wop-wordpress-smtp-mail-setting-row wop-wordpress-smtp-mail-setting-row-number wop-wordpress-smtp-mail-clear">
			<div class="wop-wordpress-smtp-mail-setting-label">
				<label for="wop-wordpress-smtp-mail-setting-<?php echo esc_attr( $this->get_slug() ); ?>-port"><?php esc_html_e( 'Enter SMTP Port No', 'wop-wordpress-smtp-mail' ); ?></label>
			</div>
			<div class="wop-wordpress-smtp-mail-setting-field">
				<input name="wop-wordpress-smtp-mail[<?php echo esc_attr( $this->get_slug() ); ?>][port]" type="number"
					value="<?php echo esc_attr( $this->options->get( $this->get_slug(), 'port' ) ); ?>"
					<?php echo $this->options->is_const_defined( $this->get_slug(), 'port' ) ? 'disabled' : ''; ?>
					id="wop-wordpress-smtp-mail-setting-<?php echo esc_attr( $this->get_slug() ); ?>-port" class="small-text" spellcheck="false"
				/>
				<p class="desc">You can request your hosting provider for the SMTP details of your site.</p>
			</div>
		</div>

		<!-- SMTP Encryption -->
		<div id="wop-wordpress-smtp-mail-setting-row-<?php echo esc_attr( $this->get_slug() ); ?>-encryption" class="wop-wordpress-smtp-mail-setting-row wop-wordpress-smtp-mail-setting-row-radio wop-wordpress-smtp-mail-clear">
			<div class="wop-wordpress-smtp-mail-setting-label">
				<label><?php esc_html_e( 'Type of Encryption', 'wop-wordpress-smtp-mail' ); ?></label>
			</div>
			<div class="wop-wordpress-smtp-mail-setting-field">

				<label for="wop-wordpress-smtp-mail-setting-<?php echo esc_attr( $this->get_slug() ); ?>-enc-none">
					<input type="radio" id="wop-wordpress-smtp-mail-setting-<?php echo esc_attr( $this->get_slug() ); ?>-enc-none"
						name="wop-wordpress-smtp-mail[<?php echo esc_attr( $this->get_slug() ); ?>][encryption]" value="none"
						<?php echo $this->options->is_const_defined( $this->get_slug(), 'encryption' ) ? 'disabled' : ''; ?>
						<?php checked( 'none', $this->options->get( $this->get_slug(), 'encryption' ) ); ?>
					/>
					<?php esc_html_e( 'None', 'wop-wordpress-smtp-mail' ); ?>
				</label>

				<label for="wop-wordpress-smtp-mail-setting-<?php echo esc_attr( $this->get_slug() ); ?>-enc-ssl">
					<input type="radio" id="wop-wordpress-smtp-mail-setting-<?php echo esc_attr( $this->get_slug() ); ?>-enc-ssl"
						name="wop-wordpress-smtp-mail[<?php echo esc_attr( $this->get_slug() ); ?>][encryption]" value="ssl"
						<?php echo $this->options->is_const_defined( $this->get_slug(), 'encryption' ) ? 'disabled' : ''; ?>
						<?php checked( 'ssl', $this->options->get( $this->get_slug(), 'encryption' ) ); ?>
					/>
					<?php esc_html_e( 'SSL', 'wop-wordpress-smtp-mail' ); ?>
				</label>

				<label for="wop-wordpress-smtp-mail-setting-<?php echo esc_attr( $this->get_slug() ); ?>-enc-tls">
					<input type="radio" id="wop-wordpress-smtp-mail-setting-<?php echo esc_attr( $this->get_slug() ); ?>-enc-tls"
						name="wop-wordpress-smtp-mail[<?php echo esc_attr( $this->get_slug() ); ?>][encryption]" value="tls"
						<?php echo $this->options->is_const_defined( $this->get_slug(), 'encryption' ) ? 'disabled' : ''; ?>
						<?php checked( 'tls', $this->options->get( $this->get_slug(), 'encryption' ) ); ?>
					/>
					<?php esc_html_e( 'TLS', 'wop-wordpress-smtp-mail' ); ?>
				</label>

				<p class="desc">
					<?php esc_html_e( 'TLS is not the same as STARTTLS. For most servers SSL is the recommended option.', 'wop-wordpress-smtp-mail' ); ?>
				</p>
			</div>
		</div>


		<!-- SMTP Authentication -->
		<div id="wop-wordpress-smtp-mail-setting-row-<?php echo esc_attr( $this->get_slug() ); ?>-auth" class="wop-wordpress-smtp-mail-setting-row wop-wordpress-smtp-mail-setting-row-checkbox-toggle wop-wordpress-smtp-mail-clear">
			<div class="wop-wordpress-smtp-mail-setting-label">
				<label for="wop-wordpress-smtp-mail-setting-<?php echo esc_attr( $this->get_slug() ); ?>-auth"><?php esc_html_e( 'Authentication', 'wop-wordpress-smtp-mail' ); ?></label>
			</div>
			<div class="wop-wordpress-smtp-mail-setting-field">
				<label for="wop-wordpress-smtp-mail-setting-<?php echo esc_attr( $this->get_slug() ); ?>-auth">
				 <input type="checkbox" id="wop-wordpress-smtp-mail-setting-<?php echo esc_attr( $this->get_slug() ); ?>-auth" name="wop-wordpress-smtp-mail[<?php echo esc_attr( $this->get_slug() ); ?>][auth]" value="yes"
						<?php echo $this->options->is_const_defined( $this->get_slug(), 'auth' ) ? 'disabled' : ''; ?>
						<?php checked( true, $this->options->get( $this->get_slug(), 'auth' ) ); ?>
					/>
					 
					
				</label>
			</div>
		</div>

		<!-- SMTP Username -->
		<div id="wop-wordpress-smtp-mail-setting-row-<?php echo esc_attr( $this->get_slug() ); ?>-user" class="wop-wordpress-smtp-mail-setting-row wop-wordpress-smtp-mail-setting-row-text wop-wordpress-smtp-mail-clear <?php echo ! $this->options->is_const_defined( $this->get_slug(), 'auth' ) && ! $this->options->get( $this->get_slug(), 'auth' ) ? 'inactive' : ''; ?>">
			<div class="wop-wordpress-smtp-mail-setting-label">
				<label for="wop-wordpress-smtp-mail-setting-<?php echo esc_attr( $this->get_slug() ); ?>-user"><?php esc_html_e( 'SMTP Username', 'wop-wordpress-smtp-mail' ); ?></label>
			</div>
			<div class="wop-wordpress-smtp-mail-setting-field">
				<input name="wop-wordpress-smtp-mail[<?php echo esc_attr( $this->get_slug() ); ?>][user]" type="text"
					value="<?php echo esc_attr( $this->options->get( $this->get_slug(), 'user' ) ); ?>"
					<?php echo $this->options->is_const_defined( $this->get_slug(), 'user' ) ? 'disabled' : ''; ?>
					id="wop-wordpress-smtp-mail-setting-<?php echo esc_attr( $this->get_slug() ); ?>-user" spellcheck="false" autocomplete="off"
				/>
				<p class="desc">
					<?php esc_html_e( 'Enter Your Username', 'wop-wordpress-smtp-mail' ); ?>
				</p>
			</div>
		</div>

		<!-- SMTP Password -->
		<div id="wop-wordpress-smtp-mail-setting-row-<?php echo esc_attr( $this->get_slug() ); ?>-pass" class="wop-wordpress-smtp-mail-setting-row wop-wordpress-smtp-mail-setting-row-password wop-wordpress-smtp-mail-clear <?php echo ! $this->options->is_const_defined( $this->get_slug(), 'auth' ) && ! $this->options->get( $this->get_slug(), 'auth' ) ? 'inactive' : ''; ?>">
			<div class="wop-wordpress-smtp-mail-setting-label">
				<label for="wop-wordpress-smtp-mail-setting-<?php echo esc_attr( $this->get_slug() ); ?>-pass"><?php esc_html_e( 'SMTP Password', 'wop-wordpress-smtp-mail' ); ?></label>
			</div>
			<div class="wop-wordpress-smtp-mail-setting-field">
				<?php if ( $this->options->is_const_defined( $this->get_slug(), 'pass' ) ) : ?>
					<input type="text" value="*************" disabled id="wop-wordpress-smtp-mail-setting-<?php echo esc_attr( $this->get_slug() ); ?>-pass"/>
				<?php else : ?>
					<input name="wop-wordpress-smtp-mail[<?php echo esc_attr( $this->get_slug() ); ?>][pass]" type="password"
						value="<?php echo esc_attr( $this->options->get( $this->get_slug(), 'pass' ) ); ?>"
						id="wop-wordpress-smtp-mail-setting-<?php echo esc_attr( $this->get_slug() ); ?>-pass" spellcheck="false" autocomplete="off"
					/>
					
						<p class="desc">
					<?php esc_html_e( 'Enter your SMTP Password.', 'wop-wordpress-smtp-mail' ); ?>
				</p>
					
					
				<?php endif; ?>
			</div>
		</div>

		<?php
	}

	/**
	 * Check whether we can use this provider based on the PHP version.
	 * Valid for those, that use SDK.
	 *
	 * @return bool
	 */
	protected function is_php_correct() {
		return version_compare( phpversion(), $this->php, '>=' );
	}

	/**
	 * Display a helpful message to those users, that are using an outdated version of PHP,
	 * which is not supported by the currently selected Provider.
	 */
	protected function display_php_warning() {
		?>

		<blockquote>
			<?php
			printf(
				/* translators: %1$s - Provider name; %2$s - PHP version required by Provider; %3$s - current PHP version. */
				esc_html__( '%1$s requires PHP %2$s to work and does not support your current PHP version %3$s. Please contact your host and request a PHP upgrade to the latest one.', 'wop-wordpress-smtp-mail' ),
				$this->title,
				$this->php,
				phpversion()
			)
			?>
			<br>
			<?php esc_html_e( 'Meanwhile you can switch to the "Other SMTP" Mailer option.', 'wop-wordpress-smtp-mail' ); ?>
		</blockquote>

		<?php
	}
}
