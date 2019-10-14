<?php
/**
 * Plugin Name: WP Geeky Troubleshooter
 * Plugin URI: https://www.techiesandesh.com/
 * Description: Easily Troubleshoot Your WordPress Site wihout Affecting Live Site.
 * Version: 1.0.0
 * Author: Sandesh
 * Author URI: https://www.sandeshjangam.com/
 *
 * @package wp-geeky-troubleshooter
 */

namespace Sandesh\Geeky_Support;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WGS_BASE_FILE', __FILE__ );

require_once 'support-core/class-gs-loader.php';
