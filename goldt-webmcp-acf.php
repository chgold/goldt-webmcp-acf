<?php
/**
 * Plugin Name: Goldnat for ACF
 * Plugin URI: https://goldnat.ai/plugins/goldt-webmcp-acf
 * Description: Advanced Custom Fields integration for Goldnat — exposes ACF field groups, field definitions and per-post field values as AI tools via the Goldnat WebMCP Bridge.
 * Version: 1.0.0
 * Author: chgold
 * Author URI: https://goldnat.ai/
 * License: Proprietary
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Requires Plugins: goldt-webmcp-bridge, advanced-custom-fields
 * Text Domain: goldt-webmcp-acf
 * @package GoldtWebMCP\ACF
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'GOLDTWMCP_ACF_VERSION', '1.0.0' );
define( 'GOLDTWMCP_ACF_PATH', plugin_dir_path( __FILE__ ) );

/**
 * Verify the free Bridge core + ACF are present.
 *
 * @return bool
 */
function goldtwmcp_acf_check_dependencies() {
	if ( ! defined( 'GOLDTWMCP_VERSION' ) ) {
		add_action( 'admin_notices', function () {
			echo '<div class="notice notice-error"><p><strong>Goldnat for ACF:</strong> Requires the free Goldnat WebMCP Bridge plugin.</p></div>';
		} );
		return false;
	}
	if ( ! function_exists( 'acf_get_field_groups' ) ) {
		add_action( 'admin_notices', function () {
			echo '<div class="notice notice-error"><p><strong>Goldnat for ACF:</strong> Requires the Advanced Custom Fields plugin.</p></div>';
		} );
		return false;
	}
	return true;
}

add_action( 'plugins_loaded', function () {
	if ( ! goldtwmcp_acf_check_dependencies() ) {
		return;
	}
	add_action( 'goldtwmcp_register_modules', 'goldtwmcp_acf_register_modules' );
}, 30 );

/**
 * Instantiate + register the ACF module.
 *
 * @param \GoldtWebMCP\Core\GoldtWebMCP $goldtwmcp Bridge instance.
 * @return void
 */
function goldtwmcp_acf_register_modules( $goldtwmcp ) {
	require_once GOLDTWMCP_ACF_PATH . 'includes/modules/class-acf-module.php';
	$manifest = $goldtwmcp->get_manifest_instance();
	$module   = new \GoldtWebMCP\Modules\ACF_Module( $manifest );
	$goldtwmcp->register_external_module( 'acf', $module );
}
