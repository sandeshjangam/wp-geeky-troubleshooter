<?php
/**
 * Front Class.
 *
 * @package geeky-support
 */

namespace Sandesh\Geeky_Support;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Gs_Front.
 */
class Gs_Front {

	/**
	 * Member Variable
	 *
	 * @var instance
	 */
	private static $instance = null;

	/**
	 * Member Variable
	 *
	 * @var instance
	 */
	private static $troubleshoot_template = null;

	/**
	 * Member Variable
	 *
	 * @var instance
	 */
	private static $troubleshoot_stylesheet = null;

	/**
	 *  Initiator
	 */
	public static function get_instance() {

		if ( is_null( self::$instance ) ) {

			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Constructor
	 */
	function __construct() {

		$this->init_hooks();
	}

	/**
	 * Init Hooks.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	function init_hooks() {

		/* Troubleshoot Theme */
		$this->setup_troubleshoot_theme();

		add_action( 'init', array( $this, 'init_login' ) );
		// $this->loader->add_filter( 'wp_authenticate_user', $plugin_public, 'disable_temporary_user_login', 10, 2 );
		// $this->loader->add_filter( 'allow_password_reset', $plugin_public, 'disable_password_reset', 10, 2 );
	}

	/**
	 * Add submenu to admin menu.
	 *
	 * @since 1.0.0
	 */
	function init_login() {

		/* Check here if remote access id enable */
		if ( isset( $_GET['wgs_token'] ) && ! empty( $_GET['wgs_token'] ) ) {

			$login_token = sanitize_key( $_GET['wgs_token'] );  // Input var okay.

			$users = $this->get_valid_user_based_on_login_token( $login_token );

			if ( $users && isset( $users[0] ) ) {

				$temporary_user = $users[0];

				$temporary_user_id = $temporary_user->ID;

				$do_login = true;

				if ( is_user_logged_in() ) {

					$current_user_id = get_current_user_id();

					if ( $temporary_user_id === $current_user_id ) {
						$do_login = false;
					} else {
						wp_logout();
					}
				}

				if ( $do_login ) {

					$temporary_user_login = $temporary_user->login;

					update_user_meta( $temporary_user_id, '_wgs_last_login', $this->get_current_gmt_timestamp() ); // phpcs:ignore
					wp_set_current_user( $temporary_user_id, $temporary_user_login );
					wp_set_auth_cookie( $temporary_user_id );

					do_action( 'wp_login', $temporary_user_login, $temporary_user );
				}

				$redirect_to_url = ( isset( $_REQUEST['redirect_to'] ) ) ? $_REQUEST['redirect_to'] : apply_filters( 'login_redirect', get_admin_url(), false, $temporary_user ); // phpcs:ignore

				// If empty redirect user to admin page.
				if ( ! empty( $redirect_to_url ) ) {
					$redirect_to = $redirect_to_url;
				}
			} else {
				// Remote user not found??? Redirect to home page.
				$redirect_to = home_url();
			}

			wp_safe_redirect( $redirect_to ); // Redirect to given url after successfull login.
			exit();
		}

		// Restrict unauthorized page access for remote users.
		if ( is_user_logged_in() ) {

			$user_id = get_current_user_id();

			if ( ! empty( $user_id ) && Gs_Helper::is_valid_remote_user( $user_id ) ) {

				global $pagenow;

				$blocked_pages = Gs_Helper::get_restricted_pages();
				$page          = ! empty( $_GET['page'] ) ? $_GET['page'] : '';

				if ( ! empty( $page ) && in_array( $page, $blocked_pages )
					|| ( ! empty( $pagenow ) && ( in_array( $pagenow, $blocked_pages ) ) )
					|| ( ! empty( $pagenow ) && ( 'users.php' === $pagenow && isset( $_GET['action'] ) && ( 'deleteuser' === $_GET['action'] || 'delete' === $_GET['action'] ) ) ) ) {
					wp_die( esc_attr__( 'You don\'t have permission to access this page', 'geeky-support' ) );
				}
			}
		}
	}

	/**
	 * Current timestamp
	 *
	 * @return string
	 */
	function get_current_gmt_timestamp() {
		return strtotime( gmdate( 'Y-m-d H:i:s', time() ) );
	}

	/**
	 * Get valid remote user based on token
	 *
	 * @since 1.0
	 *
	 * @param string $token Token.
	 * @param string $fields Fields.
	 *
	 * @return array|bool
	 */
	function get_valid_user_based_on_login_token( $token = '', $fields = 'all' ) {

		if ( empty( $token ) ) {
			return false;
		}

		$args = array(
			'fields'     => $fields,
			'order'      => 'DESC',
			'orderby'    => 'meta_value',
			'meta_query' => array(
				0 => array(
					'key'     => '_wgs_token',
					'value'   => sanitize_text_field( $token ),
					'compare' => '=',
				),
			),
		);

		$users = new \WP_User_Query( $args );

		$users_data = $users->get_results();

		if ( empty( $users_data ) ) {
			return false;
		}

		return $users_data;
	}

	/**
	 * Setup troubleshoot theme.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function setup_troubleshoot_theme() {

		if ( isset( $_COOKIE['wgs-troubleshoot-mode'] ) ) {

			if ( is_admin() ) {
				return;
			}

			if ( null === self::$troubleshoot_stylesheet ) {

				$passport = $_COOKIE['wgs-troubleshoot-mode'];

				$troubleshoot_mode_theme = get_option( '_wgs_troubleshoot_mode_theme', array() );

				if ( ! isset( $troubleshoot_mode_theme[ $passport ] ) ) {
					self::$troubleshoot_stylesheet = 'error';
				} else {

					$theme = $troubleshoot_mode_theme[ $passport ];

					/* Set Stylesheet */
					self::$troubleshoot_stylesheet = $theme;

					/* Set Template */
					$new_theme = wp_get_theme( $theme );

					self::$troubleshoot_template = $new_theme->get_template();
				}

				add_filter( 'template', array( $this, 'troubleshoot_template' ), 999 );
				add_filter( 'stylesheet', array( $this, 'troubleshoot_stylesheet' ), 999 );
			}
		}
	}

	/**
	 * Troubleshoot Template
	 *
	 * @since 1.0.0
	 *
	 * @param string $template Template.
	 *
	 * @return string
	 */
	function troubleshoot_template( $template ) {

		if ( null !== self::$troubleshoot_template && 'error' !== self::$troubleshoot_template ) {
			return self::$troubleshoot_template;
		}

		return $stylesheet;
	}

	/**
	 * Troubleshoot Template
	 *
	 * @since 1.0.0
	 *
	 * @param string $stylesheet Stylesheet.
	 *
	 * @return string
	 */
	function troubleshoot_stylesheet( $stylesheet ) {

		if ( null !== self::$troubleshoot_stylesheet && 'error' !== self::$troubleshoot_stylesheet ) {
			return self::$troubleshoot_stylesheet;
		}

		return $stylesheet;
	}
}

/**
 *  Prepare if class 'Gs_Front' exist.
 *  Kicking this off by calling 'get_instance()' method
 */
Gs_Front::get_instance();
