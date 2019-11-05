<?php
/**
 * Plugin Name: MU Geeky Support Center
 * Plugin URI:
 * Description:
 * Author:
 * Author URI:
 * License: GPLv2 or later
 *
 * @package geeky-support
 */

namespace Sandesh\Geeky_Support;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Gs_Mu_Plugin.
 */
class Gs_Mu_Plugin {

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
	private static $troubleshoot_plugins = null;

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
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	function __construct() {

		define( 'WGS_PLUGIN_SLUG', 'wp-geeky-troubleshooter/wp-geeky-troubleshooter.php' );

		add_filter( 'option_active_plugins', array( $this, 'troubleshoot_mode_disable_plugins' ) );
		add_filter( 'option_active_sitewide_plugins', array( $this, 'troubleshoot_mode_disable_plugins' ) );
	}

	/**
	 * Disabled Plugin.
	 *
	 * @since 1.0.0
	 * @param array $plugins plugins array.
	 * @return array
	 */
	function troubleshoot_mode_disable_plugins( $plugins = array() ) {

		if ( isset( $_GET['wgs_security_code'] ) || isset( $_COOKIE['wgs-security-nonce'] ) ) {

			if ( isset( $_GET['wgs_security_code'] ) ) {

				$security_code = sanitize_text_field( $_GET['wgs_security_code'] );

				if ( get_option( '_wgs_security_code', '' ) === $security_code ) {

					$security_nonce = md5( rand() );

					setcookie( 'wgs-security-nonce', $security_nonce, time() + DAY_IN_SECONDS, SITECOOKIEPATH, false, is_ssl() );

					update_option( '_wgs_security_nonce', $security_nonce );

					header( 'Location: ' . wp_login_url( admin_url( 'index.php?page=wgs_security_mode' ) ) );
					exit();
				}
			}

			if ( isset( $_COOKIE['wgs-security-nonce'] ) ) {

				$security_nonce = sanitize_text_field( $_COOKIE['wgs-security-nonce'] );

				if ( get_option( '_wgs_security_nonce', '' ) === $security_nonce ) {

					foreach ( $plugins as $key => $slug ) {

						if ( WGS_PLUGIN_SLUG !== $slug ) {
							unset( $plugins[ $key ] );
						}
					}
				}
			}
		} else {
			if ( isset( $_COOKIE['wgs-troubleshoot-mode'] ) ) {

				if ( is_admin() ) {
					return $plugins;
				}

				if ( null === Gs_Mu_Plugin::$troubleshoot_plugins ) {

					$passport = $_COOKIE['wgs-troubleshoot-mode'];

					$troubleshoot_mode_plugins = get_option( '_wgs_troubleshoot_mode_plugins', array() );

					if ( ! isset( $troubleshoot_mode_plugins[ $passport ] ) ) {
						Gs_Mu_Plugin::$troubleshoot_plugins = $plugins;
					} else {

						$clean_plugins = $troubleshoot_mode_plugins[ $passport ];

						foreach ( $plugins as $key => $slug ) {

							if ( WGS_PLUGIN_SLUG === $slug ) {
								continue;
							}

							if ( isset( $clean_plugins[ $slug ] ) && 'enable' !== $clean_plugins[ $slug ] ) {

								unset( $plugins[ $key ] );
							}
						}

						Gs_Mu_Plugin::$troubleshoot_plugins = $plugins;
					}
				}

				return Gs_Mu_Plugin::$troubleshoot_plugins;
			}
		}

		return $plugins;
	}
}


/**
 *  Prepare if class 'Gs_Mu_Plugin' exist.
 *  Kicking this off by calling 'get_instance()' method
 */
Gs_Mu_Plugin::get_instance();


/**
 * Mu Plugin Object.
 *
 * @since 1.0.0
 * @return object
 */
function wgs_mu_plugin() {
	return Gs_Mu_Plugin::get_instance();
}
