<?php
/**
 * Loader.
 *
 * @package geeky-support
 */

namespace Sandesh\Geeky_Support;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Gs_Loader' ) ) {

	/**
	 * Class Gs_Loader.
	 */
	final class Gs_Loader {

		/**
		 * Member Variable
		 *
		 * @var instance
		 */
		private static $instance = null;


		/**
		 * Member Variable
		 *
		 * @var defined_constants
		 */
		public $defined_constants = array( 'wp_debug', 'wp_debug_log', 'script_debug', 'wp_debug_display', 'savequeries', 'wp_disable_fatal_error_handler', 'disallow_file_edit' );

		/**
		 * Member Variable
		 *
		 * @var admin_data
		 */
		public $admin_data = null;

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
		public function __construct() {

			$this->define_constants();
			$this->core_files();

			add_action( 'plugins_loaded', array( $this, 'load_plugin' ), 99 );

			register_activation_hook( WGS_BASE_FILE, array( $this, 'activate' ) );
			register_deactivation_hook( WGS_BASE_FILE, array( $this, 'deactivate' ) );

		}

		/**
		 * Core Files.
		 */
		function core_files() {

			/* WPConfigTransformer */
			include_once WGS_DIR . 'wp-config-transformer/src/WPConfigTransformer.php';

			/* Helper */
			include_once WGS_DIR . 'class-gs-helper.php';
		}

		/**
		 * Activate.
		 */
		function activate() {

			/* Install MU File */
			if ( ! file_exists( WPMU_PLUGIN_DIR ) ) {
				@mkdir( WPMU_PLUGIN_DIR );
			}

			if ( file_exists( WPMU_PLUGIN_DIR . '/class-gs-mu-plugin.php' ) ) {
				@unlink( WPMU_PLUGIN_DIR . '/class-gs-mu-plugin.php' );
			}

			if ( file_exists( WGS_DIR . 'mu-plugins/class-gs-mu-plugin.php' ) ) {
				@copy( WGS_DIR . 'mu-plugins/class-gs-mu-plugin.php', WPMU_PLUGIN_DIR . '/class-gs-mu-plugin.php' );
			}

			/* Create Restore Point */
			$restore_points = get_site_option( 'wgs_restore_points', array() );

			$count = count( $restore_points );

			if ( $count >= 5 ) {
				array_shift( $restore_points );
			}

			$restore_points[] = array(
				'plugins' => get_option( 'active_plugins', array() ),
				'theme'   => array(
					'template'   => get_option( 'template' ),
					'stylesheet' => get_option( 'stylesheet' ),
				),
			);

			update_site_option( 'wgs_restore_points', $restore_points );

			/* Config Constants Backup */
			$config_path = Gs_Helper::get_config_path();

			$config_transformer = new WPConfigTransformer( $config_path );

			$this->backup_constants( $config_transformer );

			/*
			Change Constnt on Activation

			$constants          = [ 'wp_debug', 'wp_debug_log', 'script_debug', 'savequeries' ];
			$config_args        = [
				'raw'       => true,
				'normalize' => true,
			];
			foreach ( $constants as $constant ) {
				$config_transformer->update( 'constant', strtoupper( $constant ), 'true', $config_args );
			}
			*/
		}

		/**
		 * Backup Constants.
		 *
		 * @param object $config_transformer plugins array.
		 */
		function backup_constants( $config_transformer ) {

			$backup_constants = [];

			foreach ( $this->defined_constants as $defined_constant ) {

				if ( $config_transformer->exists( 'constant', strtoupper( $defined_constant ) ) ) {
					$value = $config_transformer->get_value( 'constant', strtoupper( $defined_constant ) );
					$value = trim( $value, '"\'' ); // Normalize quoted value.

					$backup_constants[ $defined_constant ] = $value;
				}
			}

			update_site_option( 'wgs_backup_constants', $backup_constants );
		}

		/**
		 * Deactivate.
		 */
		function deactivate() {

			$config_path = Gs_Helper::get_config_path();

			$config_transformer = new WPConfigTransformer( $config_path );

			foreach ( $this->defined_constants as $constant ) {
				$config_transformer->remove( 'constant', strtoupper( $constant ) );
			}

			$this->restore_constants( $config_transformer );

			/* Delete MU File */
			if ( file_exists( WPMU_PLUGIN_DIR . '/class-gs-mu-plugin.php' ) ) {
				@unlink( WPMU_PLUGIN_DIR . '/class-gs-mu-plugin.php' );
			}
		}

		/**
		 * Restore Constants.
		 *
		 * @param object $config_transformer plugins array.
		 */
		function restore_constants( $config_transformer ) {

			$restore_constants = get_site_option( 'wgs_backup_constants', array() );
			$config_args       = array(
				'raw'       => true,
				'normalize' => true,
			);

			foreach ( $restore_constants as $constant => $value ) {
				$config_transformer->update( 'constant', strtoupper( $constant ), $value, $config_args );
			}
		}


		/**
		 * Defines all constants
		 *
		 * @since 1.0.0
		 */
		function define_constants() {

			define( 'WGS_FILE', __FILE__ );
			define( 'WGS_BASE', plugin_basename( WGS_FILE ) );
			define( 'WGS_DIR', plugin_dir_path( WGS_FILE ) );
			define( 'WGS_URL', plugins_url( '/', WGS_FILE ) );
			define( 'WGS_VER', '1.0.0' );

			if ( ! defined( 'WGS_PLUGIN_SLUG' ) ) {
				define( 'WGS_PLUGIN_SLUG', 'wp-geeky-troubleshooter/wp-geeky-troubleshooter.php' );
			}
		}

		/**
		 * Loads plugin files.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		function load_plugin() {

			$this->load_core();

			$this->load_files();
		}

		/**
		 * Load Core
		 */
		function load_core() {

			/* Security Mode */
			include_once WGS_DIR . 'class-gs-security-mode.php';

			/* Admin data */
			include_once WGS_DIR . 'class-gs-admin-data.php';

			$this->admin_data = Gs_Admin_Data::get_instance();
		}

		/**
		 * Load Files.
		 */
		function load_files() {

			/* Admin Settings */
			include_once WGS_DIR . 'class-gs-admin.php';

			/* Front */
			include_once WGS_DIR . 'class-gs-front.php';
		}
	}

	/**
	 *  Prepare if class 'Gs_Loader' exist.
	 *  Kicking this off by calling 'get_instance()' method
	 */
	Gs_Loader::get_instance();
}

/**
 * Object Function wgs.
 */
function wgs() {
	return Gs_Loader::get_instance();
}
