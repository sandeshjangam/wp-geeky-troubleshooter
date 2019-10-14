<?php
/**
 * View Admin
 *
 * @package geeky-support
 */

namespace Sandesh\Geeky_Support;
?>

<div class="wrap wgs-clear" >
	<div id="poststuff">
		<div id="post-body" class="metabox-holder columns-2">
			<div id="postbox-container-2" class="postbox-container">
				<div id="normal-sortables" class="meta-box-sortables ui-sortable">
					<?php

					/*
					Later
					<div class="postbox ">
						<button type="button" class="handlediv button-link" aria-expanded="true"><span class="toggle-indicator" aria-hidden="true"></span></button>
						<h2 class="ui-sortable-handle"><span><?php _e( 'System Info', 'geeky-support' ); ?></span></h2>
						<div class="inside">
							<?php echo wgs()->admin_data->system_info(); ?>
						</div>
					</div>
					*/
					?>
					<?php if ( Gs_Helper::current_user_can_manage_settings() ) { ?>
						<div class="postbox ">
							<button type="button" class="handlediv button-link" aria-expanded="true"><span class="toggle-indicator" aria-hidden="true"></span></button>
							<h2 class="ui-sortable-handle"><span><?php _e( 'Remote Access', 'geeky-support' ); ?></span></h2>
							<div class="inside">

								<?php echo wgs()->admin_data->remote_access(); ?>
								<button class="wgs-remote-access-save-btn button-primary button wgs-button-spinner"><?php _e( 'Save Changes', 'geeky-support' ); ?></button>
							</div>
						</div>
					<?php } ?>

					<div class="postbox ">
						<button type="button" class="handlediv button-link" aria-expanded="true"><span class="toggle-indicator" aria-hidden="true"></span></button>
						<h2 class="ui-sortable-handle"><span><?php _e( 'Troubleshoot Theme & Plugins', 'geeky-support' ); ?></span></h2>
						<div class="inside">
							<?php echo wgs()->admin_data->plugin_troubleshooting(); ?>
						</div>
					</div>

					<div class="postbox ">
						<button type="button" class="handlediv button-link" aria-expanded="true"><span class="toggle-indicator" aria-hidden="true"></span></button>
						<h2 class="ui-sortable-handle"><span><?php _e( 'WP Config Constants', 'geeky-support' ); ?></span></h2>
						<div class="inside">
							<form method="post" class="wrap wgs-clear" action="" >
							<?php echo wgs()->admin_data->config_constants(); ?>
							<?php submit_button( __( 'Save Changes', 'geeky-support' ), 'wgs-config-constants-btn button-primary button', 'submit', false ); ?>
							<?php wp_nonce_field( 'wgs-config-constants', 'wgs-config-constants-nonce' ); ?>
							</form>
						</div>
					</div>
					<div class="postbox ">
						<button type="button" class="handlediv button-link" aria-expanded="true"><span class="toggle-indicator" aria-hidden="true"></span></button>
						<h2 class="ui-sortable-handle"><span><?php _e( 'Debug Log', 'geeky-support' ); ?></span></h2>
						<div class="inside">
							<?php if ( ! defined( 'WP_DEBUG_LOG' ) || ! WP_DEBUG_LOG ) {

								esc_html_e( 'WP_DEBUG_LOG constant is not configured.', 'geeky-support' );

							} else{

								echo wgs()->admin_data->debug_log();
							} ?>
						</div>
					</div>

					<div class="postbox ">
						<button type="button" class="handlediv button-link" aria-expanded="true"><span class="toggle-indicator" aria-hidden="true"></span></button>
						<h2 class="ui-sortable-handle"><span><?php _e( 'Security Code', 'geeky-support' ); ?></span></h2>
						<div class="inside">
							<?php echo wgs()->admin_data->security_options(); ?>
							<button class="wgs-security-code-save-btn button-primary button wgs-button-spinner"><?php _e( 'Save Changes', 'geeky-support' ); ?></button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
