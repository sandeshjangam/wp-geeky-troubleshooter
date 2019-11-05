<?php
/**
 * Security Mode
 *
 * @package geeky-support
 */

namespace Sandesh\Geeky_Support;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gs_Security_Mode class.
 */
class Gs_Security_Mode {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {

		if ( Gs_Helper::is_security_mode() && current_user_can( 'manage_options' ) ) {

			$url = admin_url( 'index.php?page=wgs_security_mode' );

			if ( is_user_logged_in() ) {

				if ( $this->is_login_page() ) {
					wp_redirect( $url );
					exit();
				}

				if ( $this->is_editor_page() ) {
					return;
				}

				if ( empty( $_GET['page'] ) || 'wgs_security_mode' !== $_GET['page'] ) {

					$output = '';

					$output .= '<p>' . __( 'Sorry, you are not allowed to access this page.', 'geeky-support' ) . '</p>';
					$output .= '<p>' . __( 'You are in Security Mode. You need to disable Security Mode to access dashboard', 'geeky-support' ) . '</p>';
					$output .= '<p><a href="' . esc_url( $url ) . '">' . __( 'Take me to Security Page', 'geeky-support' ) . '</a>' . '</p>';

					wp_die( $output );
				} else {
					add_action( 'admin_menu', array( $this, 'admin_menus' ) );
					add_action( 'admin_init', array( $this, 'setup_wizard' ) );
				}
			}
		}
	}

	/**
	 * Check is login page.
	 */
	function is_login_page() {

		$is_login_page = false;

		$abspath_temp = str_replace( array( '\\', '/' ), DIRECTORY_SEPARATOR, ABSPATH );

		// Was wp-login.php or wp-register.php included during this execution?
		if (
			in_array( $abspath_temp . 'wp-login.php', get_included_files() ) ||
			in_array( $abspath_temp . 'wp-register.php', get_included_files() )
		) {
			$is_login_page = true;
		}

		// $GLOBALS['pagenow'] is equal to "wp-login.php"?
		if ( isset( $GLOBALS['pagenow'] ) && 'wp-login.php' === $GLOBALS['pagenow'] ) {
			$is_login_page = true;
		}

		// $_SERVER['PHP_SELF'] is equal to "/wp-login.php"?
		if ( '/wp-login.php' == $_SERVER['PHP_SELF'] ) {
			$is_login_page = true;
		}

		return $is_login_page;
	}

	/**
	 * Check is editor page.
	 */
	function is_editor_page() {

		$is_editor_page = false;

		if ( isset( $GLOBALS['pagenow'] ) && ( 'plugin-editor.php' === $GLOBALS['pagenow'] || 'theme-editor.php' === $GLOBALS['pagenow'] ) ) {
			$is_editor_page = true;
		}

		return $is_editor_page;
	}
	/**
	 * Add admin menus/screens.
	 */
	function admin_menus() {
		add_dashboard_page( '', '', 'manage_options', 'wgs_security_mode', '' );
	}

	/**
	 * Show the setup wizard.
	 */
	function setup_wizard() {

		if ( empty( $_GET['page'] ) || 'wgs_security_mode' !== $_GET['page'] ) {
			return;
		}

		$this->steps = array(
			'plugin-updates' => array(
				'name'    => __( 'Plugin Updates', 'geeky-support' ),
				'view'    => array( $this, 'plugin_updates_view' ),
				'handler' => array( $this, 'plugin_updates_save' ),
			),
			'setup-ready'    => array(
				'name'    => __( 'Ready!', 'geeky-support' ),
				'view'    => array( $this, 'ready_step_view' ),
				'handler' => array( $this, 'ready_step_save' ),
			),
		);

		$this->step = isset( $_GET['step'] ) ? sanitize_key( $_GET['step'] ) : current( array_keys( $this->steps ) );

		wp_enqueue_style( 'wgs-security-mode', WGS_URL . 'css/security-mode.css', array( 'dashicons' ), WGS_VER );
		wp_style_add_data( 'wgs-security-mode', 'rtl', 'replace' );
		wp_enqueue_script( 'wgs-security-mode', WGS_URL . 'js/security-mode.js', array( 'jquery', 'wp-util', 'updates' ), WGS_VER );

		wp_enqueue_media();

		if ( ! empty( $_POST['save_step'] ) && isset( $this->steps[ $this->step ]['handler'] ) ) {
			call_user_func( $this->steps[ $this->step ]['handler'] );
		}

		ob_start();
		$this->header();
		$this->steps();
		$this->content();
		$this->footer();
		exit;
	}

	/**
	 * Get current step slug
	 */
	function get_current_step_slug() {
		$keys = array_keys( $this->steps );
		return $keys[ array_search( $this->step, array_keys( $this->steps ) ) ];
	}

	/**
	 * Get previous step link
	 */
	function get_prev_step_link() {
		$keys = array_keys( $this->steps );
		return add_query_arg( 'step', $keys[ array_search( $this->step, array_keys( $this->steps ) ) - 1 ] );
	}

	/**
	 * Get next step link
	 */
	function get_next_step_link() {
		$keys = array_keys( $this->steps );
		return add_query_arg( 'step', $keys[ array_search( $this->step, array_keys( $this->steps ) ) + 1 ] );
	}

	/**
	 * Get next step link
	 */
	function get_next_step_plain_link() {
		$keys = array_keys( $this->steps );
		$step = $keys[ array_search( $this->step, array_keys( $this->steps ) ) + 1 ];
		return admin_url( 'index.php?page=wgs_security_mode&step=' . $step );
	}

	/**
	 * Setup Wizard Header.
	 */
	public function header() {
		?>
		<!DOCTYPE html>
		<html>
		<head>
			<meta name="viewport" content="width=device-width" />
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
			<title><?php _e( 'Security Mode', 'geeky-support' ); ?></title>

			<script type="text/javascript">
				addLoadEvent = function(func){if(typeof jQuery!="undefined")jQuery(document).ready(func);else if(typeof wpOnload!='function'){wpOnload=func;}else{var oldonload=wpOnload;wpOnload=function(){oldonload();func();}}};
				var ajaxurl = '<?php echo admin_url( 'admin-ajax.php', 'relative' ); ?>';
				var pagenow = '';
			</script>
			<?php wp_print_scripts( array( 'security-mode' ) ); ?>
			<?php do_action( 'admin_print_styles' ); ?>
			<?php do_action( 'admin_head' ); ?>
		</head>
		<body class="wgs-security-setup wp-core-ui wgs-step-<?php echo esc_attr( $this->get_current_step_slug() ); ?>">
			<div id="wgs-logo">
				<h1><?php _e( 'Secure Troubleshoot Mode', 'geeky-support' ); ?></h1>
			</div>
		<?php
	}

	/**
	 * Setup Wizard Footer.
	 */
	public function footer() {

			$admin_url = admin_url( 'options-general.php?page=geeky_support_settings' );
		?>
			<div class="close-button-wrapper">
				<a href="<?php echo admin_url( 'plugin-editor.php' ); ?>" class="wizard-footer-link wizard-footer-left" ><?php _e( 'Plugin Editor', 'geeky-support' ); ?></a>
				
				<a href="<?php echo admin_url( 'theme-editor.php' ); ?>" class="wizard-footer-link wizard-footer-right" ><?php _e( 'Theme Editor', 'geeky-support' ); ?></a>
			</div>
			</body>
		</html>
		<?php
	}

	/**
	 * Output the steps.
	 */
	public function steps() {

		$ouput_steps = $this->steps;
		?>
		<ol class="wgs-security-steps">
			<?php
			foreach ( $ouput_steps as $step_key => $step ) :
				$classes   = '';
				$activated = false;
				if ( $step_key === $this->step ) {
					$classes   = 'active';
					$activated = true;
				} elseif ( array_search( $this->step, array_keys( $this->steps ) ) > array_search( $step_key, array_keys( $this->steps ) ) ) {
					$classes   = 'done';
					$activated = true;
				}
				?>
				<li class="<?php echo esc_attr( $classes ); ?>">
					<span><?php echo esc_html( $step['name'] ); ?></span>
				</li>
			<?php endforeach; ?>
		</ol>
		<?php
	}

	/**
	 * Output the content for the current step.
	 */
	function content() {
		echo '<div class="wgs-security-content">';
		call_user_func( $this->steps[ $this->step ]['view'] );
		echo '</div>';
	}

	/**
	 * Introduction step.
	 */
	function plugin_updates_view() {
		?>
		<h1><?php _e( 'Troubleshoot your plugins!', 'geeky-support' ); ?></h1>
		<p>This is secuirty mode. You just need to select the required plugins. It will automaticaly activate those required plugins and rest plugins will be deactivated. It will help you to regain your site access in normal mode.</p>
		<form method="post">				
			<?php

			/* To Get Actual Active Plugins */
			remove_filter( 'option_active_plugins', array( Gs_Mu_Plugin::get_instance(), 'troubleshoot_mode_disable_plugins' ) );
			remove_filter( 'option_active_sitewide_plugins', array( Gs_Mu_Plugin::get_instance(), 'troubleshoot_mode_disable_plugins' ) );
			$active_plugins = get_option( 'active_plugins', array() );
			add_filter( 'option_active_plugins', array( Gs_Mu_Plugin::get_instance(), 'troubleshoot_mode_disable_plugins' ) );
			add_filter( 'option_active_sitewide_plugins', array( Gs_Mu_Plugin::get_instance(), 'troubleshoot_mode_disable_plugins' ) );

			$all_plugins = get_plugins();

			$output  = '';
			$output .= '<div class="form-field" id="form-field-wgs_security_plugins">';

			foreach ( $all_plugins as $plugin_slug => $plugin ) {

				if ( WGS_PLUGIN_SLUG === $plugin_slug ) {
					continue;
				}

				if ( ! in_array( $plugin_slug, $active_plugins ) ) {
					continue;
				}

				$checked = '';

				$output     .= '<label>';
					$output .= '<input type="checkbox" class="wgs_security_plugins" name="_wgs_security_plugins[]" ' . $checked . ' value="' . $plugin_slug . '">';
					$output .= esc_html( $plugin['Name'] );

				$output .= '</label>';
			}

			$output .= '</div>';

			echo $output;
			?>

			<div class="wgs-security-actions step">
				<div class="button-prev-wrap">
				</div>
				<div class="button-next-wrap">
					<input type="submit" class="button-primary button button-large button-next" value="<?php _e( 'Update Plugins »', 'geeky-support' ); ?>" name="save_step" />
				</div>
				<?php wp_nonce_field( 'wgs-security-mode' ); ?>
			</div>
		</form>
		<?php
	}

	/**
	 * Save Locale Settings.
	 */
	function plugin_updates_save() {

		check_admin_referer( 'wgs-security-mode' );

		if ( isset( $_POST['_wgs_security_plugins'] ) && ! empty( $_POST['_wgs_security_plugins'] ) ) {

			$active_plugins = array_map(
				function( $slug ) {
						return sanitize_text_field( $slug );
				},
				$_POST['_wgs_security_plugins']
			);

			$active_plugins[] = WGS_PLUGIN_SLUG;

			update_option( 'active_plugins', $active_plugins );

		}

		$redirect_url = $this->get_next_step_link();
		wp_redirect( esc_url_raw( $redirect_url ) );
		exit;
	}

	/**
	 * Ready Step View.
	 */
	function ready_step_view() {

		?>
		<h1><?php _e( 'Congratulations!', 'geeky-support' ); ?></h1>

		<div class="wgs-security-setup-next-steps">
			<div class="wgs-security-setup-next-steps-last">

				<p class="success">
					<?php
					_e( 'Troubleshooting is completed.', 'geeky-support' )
					?>
				</p>

				<form method="post">
					<div class="wgs-security-actions step">
						<div class="button-prev-wrap">
						</div>
						<div class="button-next-wrap">
							<input type="submit" class="button-primary button button-large button-next" value="<?php _e( 'Exit Security Mode »', 'geeky-support' ); ?>" name="save_step" />
						</div>
						<?php wp_nonce_field( 'wgs-security-exit-mode' ); ?>
					</div>
				</form>
			</div>
		</div>
		<?php
	}

	/**
	 * Ready Step Save.
	 */
	function ready_step_save() {

		check_admin_referer( 'wgs-security-exit-mode' );

		setcookie( 'wgs-security-nonce', '', 1, SITECOOKIEPATH, false, is_ssl() );

		$admin_url = admin_url( 'options-general.php?page=geeky_support_settings' );

		wp_redirect( esc_url_raw( $admin_url ) );
		exit();
	}
}

new Gs_Security_Mode();
