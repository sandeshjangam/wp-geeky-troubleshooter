<?php
/**
 * Admin Data Class.
 *
 * @package geeky-support
 */

namespace Sandesh\Geeky_Support;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Gs_Admin_Data.
 */
class Gs_Admin_Data {

	/**
	 * Member Variable
	 *
	 * @var instance
	 */
	private static $instance = null;

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
	}


	/**
	 * Get size shorthand
	 *
	 * @param int $size Size.
	 * @return int
	 */
	function get_size_in_bytes( $size = '' ) {

		$channel = strtoupper( substr( $size, - 1 ) );
		$size    = (int) $size;

		// Capture the denomination and convert to uppercase, then do math to it.
		switch ( $channel ) {
			// Terabytes.
			case 'T':
				return $size * ( 1024 * 1024 * 1024 * 1024 );
			// Gigabytes.
			case 'G':
				return $size * ( 1024 * 1024 * 1024 );
			// Megabytes.
			case 'M':
				return $size * ( 1024 * 1024 );
			// Kilobytes.
			case 'K':
				return $size * 1024;
			default:
				return $size;
		}
	}

	/**
	 * Get size shorthand
	 *
	 * @param int $bytes Bytes.
	 * @param int $precision Precision.
	 */
	function get_size_in_shorthand( $bytes = 0, $precision = 2 ) {
		$units = array( ' bytes', 'KB', 'MB', 'GB', 'TB' );
		$i     = 0;

		while ( $bytes > 1024 ) {
			$bytes /= 1024;
			$i ++;
		}

		return round( $bytes, $precision ) . $units[ $i ];
	}

	/* Get System Info */

	/**
	 * Init Hooks.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	function system_config() {
		$system_config = array(
			array(
				'name'        => esc_attr__( 'PHP Version', 'geeky-support' ),
				'environment' => 'server',
				'minimum'     => null,
				'recommended' => '7.2 or higher',
				'actual'      => (float) phpversion(),
				'help_text'   => __( 'We recommend using the latest stable version of PHP.', 'geeky-support' ),
				'learn_more'  => 'http://php.net/releases/',
			),
			array(
				'name'        => esc_attr__( 'memory_limit', 'geeky-support' ),
				'environment' => 'server',
				'minimum'     => null,
				'recommended' => '128M',
				'actual'      => ini_get( 'memory_limit' ),
				'help_text'   => __( 'By default, memory limits set by your host or by WordPress may be too low. This will lead to applications crashing as PHP reaches the artificial limit. You can adjust your memory limit within your <a href="http://php.net/manual/en/ini.core.php#ini.memory-limit" target="_blank">php.ini file</a>, or by contacting your host for assistance. You may also need to define a memory limited in <a href="https://codex.wordpress.org/Editing_wp-config.php" target=_blank">wp-config.php</a>.', 'geeky-support' ),
				'learn_more'  => 'http://php.net/manual/en/ini.core.php#ini.memory-limit',
			),
			array(
				'name'        => esc_attr__( 'post_max_size', 'geeky-support' ),
				'environment' => 'server',
				'minimum'     => null,
				'recommended' => '64M',
				'actual'      => ini_get( 'post_max_size' ),
				'help_text'   => __( 'Post Max Size limits how large a page or file can be on your website. If your page is larger than the limit set in PHP, it will fail to load. Post sizes can become quite large when using the Divi Builder, so it is important to increase this limit. It also affects file size upload/download, which can prevent large layouts from being imported into the builder. You can adjust your max post size within your <a href="http://php.net/manual/en/ini.core.php#ini.post-max-size" target="_blank">php.ini file</a>, or by contacting your host for assistance.', 'geeky-support' ),
				'learn_more'  => 'http://php.net/manual/en/ini.core.php#ini.post-max-size',
			),
			array(
				'name'        => esc_attr__( 'max_execution_time', 'geeky-support' ),
				'environment' => 'server',
				'minimum'     => null,
				'recommended' => '180',
				'actual'      => ini_get( 'max_execution_time' ),
				'help_text'   => __( 'Max Execution Time affects how long a page is allowed to load before it times out. If the limit is too low, you may not be able to import large layouts and files into the builder. You can adjust your max execution time within your <a href="http://php.net/manual/en/info.configuration.php#ini.max-execution-time">php.ini file</a>, or by contacting your host for assistance.', 'geeky-support' ),
				'learn_more'  => 'http://php.net/manual/en/info.configuration.php#ini.max-execution-time',
			),
			array(
				'name'        => esc_attr__( 'upload_max_filesize', 'geeky-support' ),
				'environment' => 'server',
				'minimum'     => null,
				'recommended' => '64M',
				'actual'      => ini_get( 'upload_max_filesize' ),
				'help_text'   => __( 'Upload Max File Size determines that maximum file size that you are allowed to upload to your server. If the limit is too low, you may not be able to import large collections of layouts into the Divi Library. You can adjust your max file size within your <a href="http://php.net/manual/en/ini.core.php#ini.upload-max-filesize" target="_blank">php.ini file</a>, or by contacting your host for assistance.', 'geeky-support' ),
				'learn_more'  => 'http://php.net/manual/en/ini.core.php#ini.upload-max-filesize',
			),
			array(
				'name'        => esc_attr__( 'max_input_time', 'geeky-support' ),
				'environment' => 'server',
				'minimum'     => null,
				'recommended' => '180',
				'actual'      => ini_get( 'max_input_time' ),
				'help_text'   => __( 'This sets the maximum time in seconds a script is allowed to parse input data. If the limit is too low, the Divi Builder may time out before it is allowed to load. You can adjust your max input time within your <a href="http://php.net/manual/en/info.configuration.php#ini.max-input-time" target="_blank">php.ini file</a>, or by contacting your host for assistance.', 'geeky-support' ),
				'learn_more'  => 'http://php.net/manual/en/info.configuration.php#ini.max-input-time',
			),
			array(
				'name'        => esc_attr__( 'max_input_vars', 'geeky-support' ),
				'environment' => 'server',
				'minimum'     => null,
				'recommended' => '3000',
				'actual'      => ini_get( 'max_input_vars' ),
				'help_text'   => __( 'This setting affects how many input variables may be accepted. If the limit is too low, it may prevent the Divi Builder from loading. You can adjust your max input variables within your <a href="http://php.net/manual/en/info.configuration.php#ini.max-input-vars" target="_blank">php.ini file</a>, or by contacting your host for assistance.', 'geeky-support' ),
				'learn_more'  => 'http://php.net/manual/en/info.configuration.php#ini.max-input-vars',
			),
		);

		return $system_config;
	}

	/**
	 * System diagnosed data
	 */
	function system_diagnosed_data() {

		$system_config = $this->system_config();

		/* pass/fail test. Defaults to 'unknown'. */
		foreach ( $system_config as $i => $data ) {
			/**
			 * 'pass_fail': four-step process to set its value:
			 * - begin with `unknown` state;
			 * - if recommended value exists, change to `fail`;
			 * - if minimum value exists, compare against it & change to `minimal` if it passes;
			 * - compare against recommended value & change to `pass` if it passes.
			 */
			$system_config[ $i ]['pass_fail'] = 'unknown';

			if ( ! is_null( $data['recommended'] ) ) {
				$system_config[ $i ]['pass_fail'] = 'fail';
			}

			if ( ! is_null( $data['minimum'] ) && $this->get_size_in_bytes( $data['minimum'] ) <= $this->get_size_in_bytes( $data['actual'] ) ) {
				$system_config[ $i ]['pass_fail'] = 'minimal';
			}
			if ( ! is_null( $data['recommended'] ) && $this->get_size_in_bytes( $data['recommended'] ) <= $this->get_size_in_bytes( $data['actual'] ) ) {
				$system_config[ $i ]['pass_fail'] = 'pass';
			}

			/**
			 * Build messaging for minimum required values
			 */
			$message_minimum = '';
			if ( ! is_null( $data['minimum'] ) && 'fail' === $system_config[ $i ]['pass_fail'] ) {
				$message_minimum = sprintf(
					/* translators: %s: string */
					esc_html__( 'This fails to meet our minimum required value (%1$s). ', 'geeky-support' ),
					$data['minimum']
				);
			}
			if ( ! is_null( $data['minimum'] ) && 'minimal' === $system_config[ $i ]['pass_fail'] ) {
				$message_minimum = sprintf(
					/* translators: %s: string */
					esc_html__( 'This meets our minimum required value (%1$s). ', 'geeky-support' ),
					esc_html( $data['minimum'] )
				);
			}

			/**
			 * Build description messaging for results & recommendation
			 */
			$learn_more_link = '';
			if ( ! is_null( $data['learn_more'] ) ) {
				$learn_more_link = sprintf(
					' <a href="%1$s" target="_blank">%2$s</a>',
					esc_url( $data['learn_more'] ),
					esc_html__( 'Learn more.', 'geeky-support' )
				);
			}

			switch ( $system_config[ $i ]['pass_fail'] ) {
				case 'pass':
					$system_config[ $i ]['description'] = sprintf(
						'- %1$s %2$s',
						sprintf(
							/* translators: %s: string */
							esc_html__( 'Congratulations! This meets or exceeds our recommendation of %1$s.', 'geeky-support' ),
							esc_html( $data['recommended'] )
						),
						$learn_more_link
					);
					break;
				case 'minimal':
				case 'fail':
					$system_config[ $i ]['description'] = sprintf(
						'- %1$s%2$s %3$s',
						esc_html( $message_minimum ),
						sprintf(
							/* translators: %s: string */
							esc_html__( 'We recommend %1$s for the best experience.', 'geeky-support' ),
							esc_html( $data['recommended'] )
						),
						$learn_more_link
					);
					break;
				case 'unknown':
				default:
					$system_config[ $i ]['description'] = sprintf(
						/* translators: %s: link */
						esc_html__( '- We are unable to determine your setting. %1$s', 'geeky-support' ),
						$learn_more_link
					);
			}
		}

		return $system_config;
	}

	/**
	 * System Info
	 */
	function system_info() {

		return $this->prepare_html_data();
	}

	/**
	 * Prepare html data
	 */
	function prepare_html_data() {

		$system_config = $this->system_diagnosed_data();
		$report        = '';

		foreach ( $system_config as $item ) {

			$help_text = '';
			if ( ! is_null( $item['help_text'] ) ) {
				$help_text = $item['help_text'];
			}

			$report .= sprintf(
				'<div class="wgs-epanel-box wgs_system_status_row wgs_system_status_%1$s">
			<div class="wgs-box-title setting">
			    <h3>%2$s</h3>
			    <div class="wgs-box-descr"><p>%3$s</p></div>
			</div>
			<div class="wgs-box-content results">
			    <span class="actual">%4$s</span>
			    <span class="description">%5$s</span>
			</div>
			<span class="wgs-box-description"></span>
			</div>',
				esc_attr( $item['pass_fail'] ),
				esc_html( $item['name'] ),
				$help_text,
				esc_html( $item['actual'] ),
				$item['description']
			);
		}

		$report = '<div class="wgs-system-info-report">' . $report . '</div>';

		return $report;
	}

	/**
	 * Remote Access
	 */
	function remote_access() {

		$common_settings = Gs_Helper::get_common_settings();

		$remote_access = $common_settings['remote_access'];

		$login_link = Gs_Helper::get_login_url();

		$output = '';

		$output              = '<div class="form-wrap">';
			$output         .= '<div class="form-field" id="form-field-wgs_remote_access">';
				$output     .= '<label for="wgs_remote_access">';
					$output .= '<input type="hidden" id="wgs_hide_remote_access" name="_wgs_common[remote_access]" value="disable">';
					$output .= '<input type="checkbox" id="wgs_remote_access" name="_wgs_common[remote_access]" ' . checked( $remote_access, 'enable', false ) . ' value="enable">';
					$output .= __( 'Enable Remote Access', 'geeky-support' );
				$output     .= '</label>';
			$output         .= '</div>';

			$output         .= '<div class="form-field" id="form-field-wgs_login_link">';
				$output     .= '<label for="wgs_login_link">';
					$output .= __( 'Login Link', 'geeky-support' );
				$output     .= '</label>';
				$output     .= '<input type="text" readonly id="wgs_login_link" name="_wgs_common[login_link]" value="' . $login_link . '">';
				$output     .= '<button class="wgs-copy-login-link-btn button-primary button">' . __( 'Copy Link', 'geeky-support' ) . '</button>';
				$output     .= '<button class="wgs-regenerate-login-link-btn button-primary button wgs-button-spinner">' . __( 'Regenerate Link', 'geeky-support' ) . '</button>';
			$output         .= '</div>';

		$output .= '</div>';

		return $output;
	}

	/**
	 * Plugin Troubleshooting
	 */
	function plugin_troubleshooting() {

		$troubleshoot_mode = Gs_Helper::get_troubleshoot_mode_setting();

		$all_themes    = wp_get_themes();
		$current_theme = wp_get_theme();

		$all_plugins    = get_plugins();
		$active_plugins = get_option( 'active_plugins', array() );

		$passport = get_user_meta( get_current_user_id(), '_wgs_troubleshoot_mode_verify', true );

		$troubleshoot_mode_theme   = Gs_Helper::get_troubleshoot_mode_theme( $passport );
		$troubleshoot_mode_plugins = Gs_Helper::get_troubleshoot_mode_plugins( $passport );

		$output = '';

		$output              = '<div class="form-wrap">';
			$output         .= '<div class="form-field" id="form-field-wgs_troubleshoot_mode">';
				$output     .= '<label for="wgs_troubleshoot_mode">';
					$output .= '<input type="hidden" id="wgs_hide_troubleshoot_mode" name="_wgs_troubleshoot_mode" value="disable">';
					$output .= '<input type="checkbox" id="wgs_troubleshoot_mode" name="_wgs_troubleshoot_mode" ' . checked( $troubleshoot_mode, 'enable', false ) . ' value="enable">';
					$output .= __( 'Enable Troubleshooting', 'geeky-support' );
				$output     .= '</label>';
			$output         .= '</div>';

			/* Theme Troubleshooting */
			$output             .= '<div class="wgs_troubleshoot_mode_theme_plugin_wrap">';
				$output         .= '<div class="form-field" id="form-field-wgs_theme_section">';
					$output     .= '<h4>';
						$output .= __( 'Themes', 'geeky-support' );
					$output     .= '</h4>';
				$output         .= '</div>';

				$output .= '<div class="form-field" id="form-field-wgs_troubleshoot_mode_themes">';

		foreach ( $all_themes as $theme_slug => $theme ) {

			$checked = '';

			if ( $troubleshoot_mode_theme === $theme_slug ) {
				$checked = checked( 'enable', 'enable', false );
			}

			$output     .= '<label>';
				$output .= '<input type="radio" class="wgs_troubleshoot_mode_theme" name="_wgs_troubleshoot_mode_theme" ' . $checked . ' value="' . $theme_slug . '">';
				$output .= esc_html( $theme->get( 'Name' ) );

			$output .= '</label>';
		}
				$output .= '</div>';

				/* Plugin Troubleshooting */
				$output         .= '<div class="form-field" id="form-field-wgs_active_plugins_section">';
					$output     .= '<h4>';
						$output .= __( 'Currently Active Plugins', 'geeky-support' );
					$output     .= '</h4>';
				$output         .= '</div>';

				$output .= '<div class="form-field" id="form-field-wgs_troubleshoot_mode_plugins">';

		foreach ( $all_plugins as $plugin_slug => $plugin ) {

			if ( ! in_array( $plugin_slug, $active_plugins ) ) {
				continue;
			}

				$checked = '';

			if ( isset( $troubleshoot_mode_plugins[ $plugin_slug ] ) && 'enable' === $troubleshoot_mode_plugins[ $plugin_slug ] ) {
				$checked = checked( 'enable', 'enable', false );
			}

				$output     .= '<label>';
					$output .= '<input type="checkbox" class="wgs_troubleshoot_mode_plugins" name="_wgs_troubleshoot_mode_plugins[]" ' . $checked . ' value="' . $plugin_slug . '">';
					$output .= esc_html( $plugin['Name'] );

				$output .= '</label>';

				/*
				Later
				if ( ! in_array( $plugin, $this->troubleshoot_mode_plugins_whitelist ) ) {
				}
				*/
		}
				$output .= '</div>';
			$output     .= '</div>';

		$output .= '</div>';

		$output .= '<button class="wgs-troubleshoot-mode-save-btn button-primary button wgs-button-spinner">' . __( 'Save Changes', 'geeky-support' ) . '</button>';

		return $output;
	}

	/**
	 * Config Constants
	 */
	function config_constants() {

		$config_path        = Gs_Helper::get_config_path();
		$config_transformer = new WPConfigTransformer( $config_path );

		$defined_constants = wgs()->defined_constants;

		$actual_constants = array();

		foreach ( $defined_constants as $defined_constant ) {

			$actual_constants[ $defined_constant ] = 'false';

			if ( $config_transformer->exists( 'constant', strtoupper( $defined_constant ) ) ) {
				$value = $config_transformer->get_value( 'constant', strtoupper( $defined_constant ) );
				$value = trim( $value, '"\'' ); // Normalize quoted value.

				$actual_constants[ $defined_constant ] = $value;
			}
		}

		$output = '';

		$output = '<div class="form-wrap">';

		foreach ( $actual_constants as $constant => $value ) {
			$output         .= '<div class="form-field" id="form-field-wgs_config_constants_' . $constant . '">';
				$output     .= '<label for="wgs_config_constants_' . $constant . '">';
					$output .= '<input type="hidden" id="wgs_hide_config_constants_' . $constant . '" name="_wgs_config_constants[ ' . $constant . ' ]" value="disable">';
					$output .= '<input type="checkbox" id="wgs_config_constants_' . $constant . '" name="_wgs_config_constants[ ' . $constant . ' ]" ' . checked( $value, 'true', false ) . ' value="enable">';
					$output .= $constant;
				$output     .= '</label>';
			$output         .= '</div>';
		}

		$output .= '</div>';

		echo $output;
	}

	/**
	 * Get Debug Log
	 */
	function debug_log() {

		$wp_debug_log = $this->get_wp_debug_log();
		$card_title   = esc_html__( 'Logs', 'geeky-support' );

		$output = '<p>If you have <a href="https://codex.wordpress.org/Debugging_in_WordPress" target=_blank" >WP_DEBUG_LOG</a> enabled, WordPress related errors will be archived in a log file. For your convenience, we have aggregated the contents of this log file so that you and the support team can view it easily. The file cannot be edited here.</p>';

		$output .= '<div class="wgs_debug_log_preview">';

		if ( isset( $wp_debug_log['error'] ) ) {

			$output .= '<textarea disabled >' . $wp_debug_log['error'] . '</textarea>';
		} else {
			$output .= '<textarea disabled id="wgs_logs_display">' . $wp_debug_log['entries'] . '</textarea>'
							/*. '<textarea disabled id="wgs_logs_recent">' . $wp_debug_log['entries'] . '</textarea>'*/
							. '</div>'
							. '<div class="wgs_card_cta">'
							. '<a href="' . content_url( 'debug.log' ) . '" class="download_debug_log" download>'
							. esc_html__( 'Download Full Debug Log', 'geeky-support' )
							. ' (' . $wp_debug_log['size'] . ')'
							. '</a>';

							/*
							. '<a class="copy_debug_log">'
							. esc_html__( 'Copy Recent Log Entries', 'geeky-support' )
							. '</a>'
							*/
		}

		$output .= '</div>';

		return $output;
	}

	/**
	 * Get wp debug log
	 *
	 * @return string
	 */
	function get_wp_debug_log() {

		$lines_to_return = apply_filters( 'wgs_debug_log_lines', 200 );

		$log = array(
			'entries' => '',
			'size'    => 0,
		);

		// Early exit: internal PHP function `file_get_contents()` appears to be on lockdown.
		if ( ! function_exists( 'file_get_contents' ) ) {

			$log['error'] = esc_attr__( 'WordPress debug log cannot be read.', 'geeky-support' );

			return $log;
		}

		// Early exit: WP_DEBUG_LOG isn't defined in wp-config.php (or it's defined, but it's empty).
		if ( ! defined( 'WP_DEBUG_LOG' ) || ! WP_DEBUG_LOG ) {

			$log['error'] = esc_attr__( 'WP_DEBUG_LOG constant is not configured.', 'geeky-support' );

			return $log;
		}

		/**
		 * WordPress 5.1 introduces the option to define a custom path for the WP_DEBUG_LOG file.
		 *
		 * @see wp_debug_mode()
		 */
		if ( in_array( strtolower( (string) WP_DEBUG_LOG ), array( 'true', '1' ), true ) ) {
			$wp_debug_log_path = realpath( WP_CONTENT_DIR . '/debug.log' );
		} elseif ( is_string( WP_DEBUG_LOG ) ) {
			$wp_debug_log_path = realpath( WP_DEBUG_LOG );
		}

		// Early exit: `debug.log` doesn't exist or otherwise can't be read.
		if ( ! isset( $wp_debug_log_path ) || ! file_exists( $wp_debug_log_path ) || ! is_readable( $wp_debug_log_path ) ) {

			$log['error'] = esc_attr__( 'Geeky Support :: WordPress debug log cannot be found.', 'geeky-support' );

			return $log;
		}

		// Load the debug.log file.
		$file = new \SplFileObject( $wp_debug_log_path );

		// Get the filesize of debug.log.
		$log['size'] = $this->get_size_in_shorthand( 0 + $file->getSize() );

		// If $lines_to_return is a positive integer, fetch the last [$lines_to_return] lines of the log file.
		$lines_to_return = (int) $lines_to_return;

		if ( $lines_to_return > 0 ) {
			$file->seek( PHP_INT_MAX );
			$total_lines = $file->key();
			// If the file is smaller than the number of lines requested, return the entire file.
			$reader         = new \LimitIterator( $file, max( 0, $total_lines - $lines_to_return ) );
			$log['entries'] = '';
			foreach ( $reader as $line ) {
				$log['entries'] .= $line;
			}
		}
		// Unload the SplFileObject.
		$file = null;

		return $log;
	}

	/**
	 * Security Options
	 */
	function security_options() {

		$security_code = get_option( '_wgs_security_code', '' );

		$output              = '<div class="form-wrap">';
			$output         .= '<div class="form-field" id="form-field-wgs_security_code">';
				$output     .= '<label for="wgs_security_code">';
					$output .= __( 'Security Code', 'geeky-support' );
				$output     .= '</label>';
				$output     .= '<input type="text" id="wgs_security_code" name="_wgs_security_code" value="' . $security_code . '">';
			$output         .= '</div>';
		$output             .= '</div>';

		return $output;
	}
}

/**
 *  Prepare if class 'Gs_Admin_Data' exist.
 *  Kicking this off by calling 'get_instance()' method
 */
Gs_Admin_Data::get_instance();
