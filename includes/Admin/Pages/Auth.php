<?php

namespace WOPMailSMTP\Admin\Pages;

use WOPMailSMTP\Options;

/**
 * Class Auth.
 *
 * @since 1.0.0
 */
class Auth {

	/**
	 * @var string Slug of a tab.
	 */
	protected $slug = 'auth';

	/**
	 * Launch mailer specific Auth logic.
	 *
	 * @since 1.0.0
	 */
	public function process_auth() {

		$auth = wop_wordpress_mail_smtp()->get_providers()->get_auth( Options::init()->get( 'mail', 'mailer' ) );

		$auth->process();
	}

	/**
	 * Return nothing, as we don't need this functionality.
	 *
	 * @since 1.0.0
	 */
	public function get_label() {
		return '';
	}

	/**
	 * Return nothing, as we don't need this functionality.
	 *
	 * @since 1.0.0
	 */
	public function get_title() {
		return '';
	}

	/**
	 * Do nothing, as we don't need this functionality.
	 *
	 * @since 1.0.0
	 */
	public function display() {
	}
}
