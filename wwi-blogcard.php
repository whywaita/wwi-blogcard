<?php
/**
 * Plugin Name:       WWI Blogcard
 * Plugin URI:        https://github.com/whywaita/wwi-blogcard
 * Description:       A WordPress block plugin that generates beautiful blog cards from URLs using OGP information.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            whywaita
 * Author URI:        https://github.com/whywaita
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wwi-blogcard
 * Domain Path:       /languages
 *
 * @package WWI_Blogcard
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin constants.
define( 'WWI_BLOGCARD_VERSION', '1.0.0' );
define( 'WWI_BLOGCARD_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WWI_BLOGCARD_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Load plugin classes.
 */
require_once WWI_BLOGCARD_PLUGIN_DIR . 'includes/class-wwi-blogcard-cache.php';
require_once WWI_BLOGCARD_PLUGIN_DIR . 'includes/class-wwi-blogcard-ogp-fetcher.php';
require_once WWI_BLOGCARD_PLUGIN_DIR . 'includes/class-wwi-blogcard-rest-api.php';

/**
 * Initialize the plugin.
 *
 * @return void
 */
function wwi_blogcard_init() {
	// Initialize REST API.
	$rest_api = new WWI_Blogcard_REST_API();
	$rest_api->init();
}
add_action( 'init', 'wwi_blogcard_init' );

/**
 * Register the block.
 *
 * @return void
 */
function wwi_blogcard_register_block() {
	$block = register_block_type( WWI_BLOGCARD_PLUGIN_DIR . 'build/wwi-blogcard' );

	// Set up JavaScript translations.
	if ( $block && ! empty( $block->editor_script_handles ) ) {
		foreach ( $block->editor_script_handles as $handle ) {
			wp_set_script_translations( $handle, 'wwi-blogcard', WWI_BLOGCARD_PLUGIN_DIR . 'languages' );
		}
	}
}
add_action( 'init', 'wwi_blogcard_register_block' );
