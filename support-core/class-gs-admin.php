<?php
/**
 * Admin Class.
 *
 * @package geeky-support
 */

namespace Sandesh\Geeky_Support;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Gs_Admin.
 */
class Gs_Admin {

	/**
	 * Member Variable
	 *
	 * @var instance
	 */
	private static $instance = null;

	/**
	 * Initiator
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

		if ( ! is_admin() ) {
			return;
		}

		add_action( 'admin_menu', array( $this, 'submenu' ), 999 );

		add_action( 'init', array( $this, 'save_settings_and_scripts' ), 20 );

		add_action( 'wp_ajax_wgs_save_remote_access', array( $this, 'save_remote_access' ) );
		add_action( 'wp_ajax_wgs_regenerate_login_link', array( $this, 'regenerate_login_link' ) );

		add_action( 'wp_ajax_wgs_save_troubleshoot_mode', array( $this, 'save_troubleshoot_mode' ) );

		add_action( 'wp_ajax_wgs_save_security_code', array( $this, 'save_security_code' ) );
	}

	/**
	 * Add submenu to admin menu.
	 *
	 * @since 1.0.0
	 */
	function submenu() {

		$parent_slug = 'options-general.php';
		$page_title  = __( 'Geeky Troubleshooter', 'geeky-support' );
		$menu_title  = __( 'Geeky Troubleshooter', 'geeky-support' );
		$capability  = 'manage_options';
		$menu_slug   = 'geeky_support_settings';
		$callback    = __CLASS__ . '::render';

		add_submenu_page(
			$parent_slug,
			$page_title,
			$menu_title,
			$capability,
			$menu_slug,
			$callback
		);
	}

	/**
	 * Renders the admin settings.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	static public function render() {

		include_once WGS_DIR . 'admin/view/support-data.php';
	}

	/**
	 * Save settings and load scripts
	 */
	function save_settings_and_scripts() {

		// Enqueue admin scripts.
		if ( isset( $_REQUEST['page'] ) && 'geeky_support_settings' == $_REQUEST['page'] ) {

			add_action( 'admin_enqueue_scripts', array( $this, 'styles_scripts' ) );

			$this->save_settings();
		}
	}

	/**
	 * Enqueues the needed CSS/JS for the builder's admin settings page.
	 *
	 * @since 1.0.0
	 */
	function styles_scripts() {

		// Styles.
		wp_enqueue_style( 'geeky-support-admin', WGS_URL . 'css/support-center.css', array(), WGS_VER );

		// Script.
		wp_enqueue_script( 'geeky-support-admin', WGS_URL . 'js/support-center.js', array( 'jquery' ), WGS_VER );

		$localize = array(
			'ajaxurl'                     => admin_url( 'admin-ajax.php' ),
			'remote_access_nonce'         => wp_create_nonce( 'wgs-remote-access' ),
			'regenerate_login_link_nonce' => wp_create_nonce( 'wgs-regenerate-login-link' ),
			'troubleshoot_mode_nonce'     => wp_create_nonce( 'wgs-troubleshoot-mode' ),
			'security_code_nonce'         => wp_create_nonce( 'wgs-security-code' ),

		);

		wp_localize_script( 'geeky-support-admin', 'geeky_support', $localize );
	}

	/**
	 * Save All admin settings here
	 */
	function save_settings() {

		// Only admins can save settings.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$this->save_config_constants();

		// Let extensions hook into saving.
		do_action( 'wgs_admin_settings_save' );
	}

	/**
	 * Save config constants
	 */
	function save_config_constants() {

		if ( isset( $_POST['wgs-config-constants-nonce'] ) && wp_verify_nonce( $_POST['wgs-config-constants-nonce'], 'wgs-config-constants' ) ) {

			$constants = isset( $_POST['_wgs_config_constants'] ) ? $_POST['_wgs_config_constants'] : array();

			if ( ! empty( $constants ) ) {

				$config_path        = Gs_Helper::get_config_path();
				$config_transformer = new WPConfigTransformer( $config_path );
				$config_args        = [
					'raw'       => true,
					'normalize' => true,
				];

				// Loop through the input and sanitize each of the values.
				foreach ( $constants as $constant => $val ) {

					$value = ( 'enable' === $val ) ? 'true' : 'false';

					$constant = sanitize_text_field( $constant );

					$config_transformer->update( 'constant', strtoupper( $constant ), $value, $config_args );
				}
			}

			$url   = $_SERVER['REQUEST_URI'];
			$query = array(
				'message' => 'saved',
			);

			$redirect_to = add_query_arg( $query, $url );

			wp_redirect( $redirect_to );
			exit;
		} // End if statement.
	}

	/**
	 * Save remote access
	 */
	function save_remote_access() {

		check_ajax_referer( 'wgs-remote-access', 'security' );

		$checked = isset( $_POST['checked'] ) ? sanitize_text_field( $_POST['checked'] ) : 'disable';

		$error = true;

		$result = array(
			'status' => 'error',
		);

		if ( ! Gs_Helper::current_user_can_manage_settings() ) {
			$result['message'] = 'unathorised_access';
		} else {
			$error = false;
		}

		if ( ! $error ) {

			$this->update_user_status( $checked );
			$this->save_remote_access_setting( $checked );
		}

		/**
		 * End
		 */
		wp_send_json_success( $result );
	}

	/**
	 * Regenerate login link
	 */
	function regenerate_login_link() {

		check_ajax_referer( 'wgs-regenerate-login-link', 'security' );

		$checked = isset( $_POST['checked'] ) ? sanitize_text_field( $_POST['checked'] ) : 'disable';

		$error = true;

		$result = array(
			'status' => 'error',
		);

		if ( ! Gs_Helper::current_user_can_manage_settings() ) {
			$result['message'] = 'unathorised_access';
		} else {
			$error = false;
		}

		if ( ! $error ) {

			$this->delete_user();

			$user = $this->create_new_user();

			if ( true === $user['error'] ) {
				$result = array(
					'status'  => 'error',
					'message' => 'user_creation_failed',
				);
			} else {
				$result = array(
					'status'      => 'success',
					'message'     => 'user_created',
					'login_token' => $user['login_token'],
				);
			}

			$this->update_user_status( $checked );
		}

		/**
		 * End
		 */
		wp_send_json_success( $result );
	}

	/**
	 * Save troubleshoot mode
	 */
	function save_troubleshoot_mode() {

		check_ajax_referer( 'wgs-troubleshoot-mode', 'security' );

		$checked = isset( $_POST['checked'] ) ? sanitize_text_field( $_POST['checked'] ) : 'disable';
		$theme   = isset( $_POST['theme'] ) ? sanitize_text_field( trim( $_POST['theme'] ) ) : 'test';
		$plugins = isset( $_POST['plugins'] ) ? $_POST['plugins'] : array();

		$new_plugins = array();

		$result = array(
			'status' => 'success',
		);

		/*
		Later
		if ( false == $this->can_manage_settings() ) {
			$result['message'] = 'unathorised_access';
		} else {
			$error = false;
		}
		*/

		foreach ( $plugins as $slug => $value ) {

			$new_slug  = sanitize_text_field( $slug );
			$new_value = sanitize_text_field( $value );

			$new_plugins[ $new_slug ] = $new_value;
		}

		$passport = '';

		if ( 'enable' === $checked ) {

			$passport = md5( rand() );

			/* Delet Old Options of the user */
			$old_passport = get_user_meta( get_current_user_id(), '_wgs_troubleshoot_mode_verify', true );

			/* Themes */
			$passport_theme = get_option( '_wgs_troubleshoot_mode_theme', array() );

			if ( isset( $passport_theme[ $old_passport ] ) ) {
				unset( $passport_theme[ $old_passport ] );
			}

			$passport_theme[ $passport ] = $theme;

			update_option( '_wgs_troubleshoot_mode_theme', $passport_theme );

			/* Plugins */
			$passport_plugins = get_option( '_wgs_troubleshoot_mode_plugins', array() );

			if ( isset( $passport_plugins[ $old_passport ] ) ) {
				unset( $passport_plugins[ $old_passport ] );
			}

			$passport_plugins[ $passport ] = $new_plugins;

			update_option( '_wgs_troubleshoot_mode_plugins', $passport_plugins );

			/* Rest Operation */
			update_user_meta( get_current_user_id(), '_wgs_troubleshoot_mode_verify', $passport );

			setcookie( 'wgs-troubleshoot-mode', $passport, time() + DAY_IN_SECONDS, SITECOOKIEPATH, false, is_ssl() );

		} else {

			$passport = get_user_meta( get_current_user_id(), '_wgs_troubleshoot_mode_verify', true );

			/* Theme */
			$passport_theme = get_option( '_wgs_troubleshoot_mode_theme', array() );

			if ( isset( $passport_theme[ $passport ] ) ) {
				unset( $passport_theme[ $passport ] );
			}

			update_option( '_wgs_troubleshoot_mode_theme', $passport_theme );

			/* Plugins */
			$passport_plugins = get_option( '_wgs_troubleshoot_mode_plugins', array() );

			if ( isset( $passport_plugins[ $passport ] ) ) {
				unset( $passport_plugins[ $passport ] );
			}

			update_option( '_wgs_troubleshoot_mode_plugins', $passport_plugins );

			// update_user_meta( get_current_user_id(), '_wgs_troubleshoot_mode_verify', $passport );.
			// delete_option( 'wgs_troubleshoot_mode_verify' );.
			// Expire the cookie.
			setcookie( 'wgs-troubleshoot-mode', '', 1, SITECOOKIEPATH, false, is_ssl() );
		}

		update_user_meta( get_current_user_id(), '_wgs_troubleshoot_mode', $checked );

		/**
		 * End
		 */
		wp_send_json_success( $result );
	}

	/**
	 * Save secuirtyconfig constants
	 */
	function save_security_code() {

		check_ajax_referer( 'wgs-security-code', 'security' );

		$security_code = isset( $_POST['security_code'] ) ? sanitize_text_field( $_POST['security_code'] ) : '';

		$result = array(
			'status' => 'success',
		);

		/*
		Later
		if ( false == $this->can_manage_settings() ) {
			$result['message'] = 'unathorised_access';
		} else {
			$error = false;
		}
		*/

		update_option( '_wgs_security_code', $security_code );

		/**
		 * End
		 */
		wp_send_json_success( $result );
	}

	/**
	 * Save remote acsess
	 *
	 * @since 1.0.0
	 * @param string $remote_access Access.
	 */
	function save_remote_access_setting( $remote_access ) {

		$new_settings['remote_access'] = $remote_access;

		Gs_Helper::update_admin_settings_option( '_wgs_common', $new_settings, true );
	}

	/**
	 * Save plugin troubleshooting
	 *
	 * @param string $troubleshoot_mode Mode.
	 */
	function save_troubleshoot_mode_setting( $troubleshoot_mode ) {

		update_user_meta( get_current_user_id(), '_wgs_troubleshoot_mode', $troubleshoot_mode );
	}
	/**
	 * Create a new user
	 *
	 * @return array|int|WP_Error
	 */
	function create_new_user() {

		$password   = $this->generate_password();
		$username   = 'geeky_support_team';
		$first_name = 'Geeky';
		$last_name  = 'Support';
		$role       = 'administrator';

		$user_args = array(
			'first_name' => $first_name,
			'last_name'  => $last_name,
			'user_login' => $username,
			'user_pass'  => $password,
			'role'       => $role,
		);

		$result = array(
			'error' => true,
		);

		$user_id = wp_insert_user( $user_args );

		if ( is_wp_error( $user_id ) ) {

			$code = $user_id->get_error_code();

			$result['errcode'] = $code;
			$result['message'] = $user_id->get_error_message( $code );

		} else {

			if ( is_multisite() ) {
				grant_super_admin( $user_id );
			}

			$login_token = $this->generate_login_token( $user_id );

			update_user_meta( $user_id, '_wgs_user', true );
			update_user_meta( $user_id, '_wgs_created', strtotime( gmdate( 'Y-m-d H:i:s', time() ) ) );
			update_user_meta( $user_id, '_wgs_token', $login_token );
			update_user_meta( $user_id, 'show_welcome_panel', 0 );

			$result['error']       = false;
			$result['user_id']     = $user_id;
			$result['login_token'] = $login_token;
		}

		return $result;
	}

	/**
	 * Generate login token
	 *
	 * @since 1.0.0
	 * @param int $user_id User id.
	 */
	function generate_login_token( $user_id ) {

		$str = $user_id . time() . uniqid( '', true );

		return md5( $str );
	}

	/**
	 * Generate password for new user.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	function generate_password() {
		return wp_generate_password( absint( 15 ), true, false );

	}

	/**
	 * Delete remote user
	 *
	 * @since 1.0
	 */
	function delete_user() {

		$args = array(
			'fields'     => 'all',
			'order'      => 'DESC',
			'orderby'    => 'meta_value',
			'meta_query' => array(
				0 => array(
					'key'   => '_wgs_user',
					'value' => 1,
				),
			),
		);

		$users = new \WP_User_Query( $args );

		$users_data = $users->get_results();

		if ( ! empty( $users_data ) ) {

			foreach ( $users_data as $user ) {

				if ( $user instanceof \WP_User ) {
					wp_delete_user( $user->ID ); // Delete User.
				}
			}
		}
	}

	/**
	 * Update user status
	 *
	 * @since 1.0.0
	 * @param string $status User status.
	 */
	function update_user_status( $status = 'disable' ) {

		$args = array(
			'fields'     => 'all',
			'order'      => 'DESC',
			'orderby'    => 'meta_value',
			'meta_query' => array(
				0 => array(
					'key'   => '_wgs_user',
					'value' => 1,
				),
			),
		);

		$users = new \WP_User_Query( $args );

		$users_data = $users->get_results();

		if ( ! empty( $users_data ) ) {

			foreach ( $users_data as $user ) {

				if ( $user instanceof \WP_User ) {
					update_user_meta( $user->ID, '_wgs_user_status', $status );
				}
			}
		}
	}
}

/**
 *  Prepare if class 'Gs_Admin' exist.
 *  Kicking this off by calling 'get_instance()' method
 */
Gs_Admin::get_instance();
