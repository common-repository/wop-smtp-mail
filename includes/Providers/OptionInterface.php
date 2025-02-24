<?php

namespace WOPMailSMTP\Providers;

/**
 * Interface ProviderInterface, shared between all current and future providers.
 * Defines required methods across all providers.
 *
 * @since 1.0.0
 */
interface OptionInterface {

	/**
	 * Get the mailer provider slug.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_slug();

	/**
	 * Get the mailer provider title (or name).
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_title();

	/**
	 * Get the mailer provider description.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_description();

	/**
	 * Get the mailer provider minimum PHP version.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_php_version();

	/**
	 * Get the mailer provider logo URL.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_logo_url();

	/**
	 * Output the mailer provider options.
	 *
	 * @since 1.0.0
	 */
	public function display_options();
}
