<?php
/**
 * Helper.
 *
 * @package geeky-support
 */

namespace Sandesh\Geeky_Support;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Gs_Helper.
 */
class Gs_Helper {

	/**
	 * Common global data
	 *
	 * @var zapier
	 */
	private static $common = null;

	/**
	 * Common global data
	 *
	 * @var zapier
	 */
	private static $troubleshoot_mode = null;

	/**
	 * Common global data
	 *
	 * @var zapier
	 */
	private static $troubleshoot_mode_theme = null;

	/**
	 * Common global data
	 *
	 * @var zapier
	 */
	private static $troubleshoot_mode_plugins = null;

	/**
	 * Returns an option from the database for
	 * the admin settings page.
	 *
	 * @param  string  $key     The option key.
	 * @param  mixed   $default Option default value if option is not available.
	 * @param  boolean $network_override Whether to allow the network admin setting to be overridden on subsites.
	 * @return string           Return the option value
	 */
	public static function get_admin_settings_option( $key, $default = false, $network_override = false ) {

		// Get the site-wide option if we're in the network admin.
		if ( $network_override && is_multisite() ) {
			$value = get_site_option( $key, $default );
		} else {
			$value = get_option( $key, $default );
		}

		return $value;
	}

	/**
	 * Updates an option from the admin settings page.
	 *
	 * @param string $key       The option key.
	 * @param mixed  $value     The value to update.
	 * @param bool   $network   Whether to allow the network admin setting to be overridden on subsites.
	 * @return mixed
	 */
	static public function update_admin_settings_option( $key, $value, $network = false ) {

		// Update the site-wide option since we're in the network admin.
		if ( $network && is_multisite() ) {
			update_site_option( $key, $value );
		} else {
			update_option( $key, $value );
		}

	}

	/**
	 * Get single setting
	 *
	 * @since 1.1.4
	 *
	 * @param  string $key Option key.
	 * @param  string $default Option default value if not exist.
	 * @return mixed
	 */
	static public function get_common_setting( $key = '', $default = '' ) {
		$settings = self::get_common_settings();

		if ( $settings && array_key_exists( $key, $settings ) ) {
			return $settings[ $key ];
		}

		return $default;
	}


	/**
	 * Get zapier settings.
	 *
	 * @return  array.
	 */
	static public function get_common_settings() {

		if ( null === self::$common ) {

			$common_default = array(
				'remote_access' => 'disable',
			);

			$common = Gs_Helper::get_admin_settings_option( '_wgs_common', false, true );

			self::$common = wp_parse_args( $common, $common_default );
		}

		return self::$common;
	}

	/**
	 * Get troubleshoot mode settings
	 *
	 * @since 1.0
	 *
	 * @return array
	 */
	static public function get_troubleshoot_mode_setting() {

		if ( null === self::$troubleshoot_mode ) {

			$troubleshoot_mode = 'disable';

			$meta_value = get_user_meta( get_current_user_id(), '_wgs_troubleshoot_mode', true );

			if ( '' !== $meta_value ) {
				$troubleshoot_mode = $meta_value;
			}

			self::$troubleshoot_mode = $troubleshoot_mode;
		}

		return self::$troubleshoot_mode;
	}

	/**
	 * Get troubleshoot mode theme
	 *
	 * @since 1.0
	 *
	 * @param string $token Token.
	 * @return array
	 */
	static public function get_troubleshoot_mode_theme( $token = '' ) {

		if ( null === self::$troubleshoot_mode_theme ) {

			$troubleshoot_mode_theme_default = 'test';
			$troubleshoot_mode_theme         = 'test';

			$troubleshoot_mode_theme_opt = get_option( '_wgs_troubleshoot_mode_theme', array() );

			if ( isset( $troubleshoot_mode_theme_opt[ $token ] ) ) {
				$troubleshoot_mode_theme = $troubleshoot_mode_theme_opt[ $token ];
			} else {

				// $current_theme = wp_get_theme();
				// $troubleshoot_mode_theme = $current_theme->get('Name');
				$troubleshoot_mode_theme = get_option( 'stylesheet' );
			}

			self::$troubleshoot_mode_theme = $troubleshoot_mode_theme;
		}

		return self::$troubleshoot_mode_theme;
	}
	/**
	 * Get troubleshoot mode plugins
	 *
	 * @since 1.0
	 *
	 * @param string $token Token.
	 * @return array
	 */
	static public function get_troubleshoot_mode_plugins( $token = '' ) {

		if ( null === self::$troubleshoot_mode_plugins ) {

			$troubleshoot_mode_plugins_default = array();
			$troubleshoot_mode_plugins         = array();

			$troubleshoot_mode_plugins_opt = get_option( '_wgs_troubleshoot_mode_plugins', array() );

			if ( isset( $troubleshoot_mode_plugins_opt[ $token ] ) ) {
				$troubleshoot_mode_plugins = $troubleshoot_mode_plugins_opt[ $token ];
			} else {

				$active_plugins = get_option( 'active_plugins', array() );

				foreach ( $active_plugins as $i => $slug ) {
					$troubleshoot_mode_plugins[ $slug ] = 'enable';
				}
			}

			self::$troubleshoot_mode_plugins = $troubleshoot_mode_plugins;
		}

		return self::$troubleshoot_mode_plugins;
	}

	/**
	 * Get temporary login url
	 *
	 * @since 1.0
	 *
	 * @return string
	 */
	public static function get_login_url() {

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

		$user_id = '';

		if ( ! empty( $users_data ) && isset( $users_data[0] ) ) {

			$user_id = $users_data[0]->ID;
		}

		if ( empty( $user_id ) ) {
			return 'No Login URL ';
		}

		$is_valid_login = true;

		if ( ! $is_valid_login ) {
			return 'No Login URL ';
		}

		$login_token = get_user_meta( $user_id, '_wgs_token', true );

		if ( empty( $login_token ) ) {
			return 'No Login URL ';
		}

		$login_url = add_query_arg( 'wgs_token', $login_token, trailingslashit( admin_url() ) );

		return $login_url;
	}

	/**
	 * Get wp config path.
	 */
	public static function get_config_path() {

		$config_path = ABSPATH . 'wp-config.php';

		if ( ! file_exists( $config_path ) ) {

			if ( @file_exists( dirname( ABSPATH ) . '/wp-config.php' ) && ! @file_exists( dirname( ABSPATH ) . '/wp-settings.php' ) ) {
				$config_path = dirname( ABSPATH ) . '/wp-config.php';
			}
		}

		return apply_filters( 'wgs_wp_config_path', $config_path );
	}

	/**
	 * Current user can manage settings.
	 */
	public static function current_user_can_manage_settings() {

		// Only admins can save settings.
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		$check = get_user_meta( get_current_user_id(), '_wgs_user', true );

		return ! empty( $check ) ? false : true;

	}

	/**
	 * Is valid remote user.
	 *
	 * @param int $user_id User ID.
	 */
	public static function is_valid_remote_user( $user_id = false ) {

		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		$check = get_user_meta( get_current_user_id(), '_wgs_user', true );

		return empty( $check ) ? false : true;
	}

	/**
	 * Get restricted pages.
	 */
	public static function get_restricted_pages() {

		$blocked_pages = array( 'user-new.php', 'user-edit.php', 'profile.php' );
		$blocked_pages = apply_filters( 'wgs_restricted_pages_for_remote_user', $blocked_pages );

		return $blocked_pages;
	}

	/**
	 * Check security mode.
	 */
	public static function is_security_mode() {

		if ( isset( $_COOKIE['wgs-security-nonce'] ) ) {

			$security_nonce = sanitize_text_field( $_COOKIE['wgs-security-nonce'] );

			if ( get_option( '_wgs_security_nonce', '' ) === $security_nonce ) {

				return true;
			}
		}

		return false;
	}
}
