<?php
/**
 * Plugin Name:       WP Blogcard
 * Plugin URI:        https://github.com/whywaita/wp-blogcard
 * Description:       A WordPress block plugin that generates beautiful blog cards from URLs using OGP information.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            whywaita
 * Author URI:        https://github.com/whywaita
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wp-blogcard
 * Domain Path:       /languages
 *
 * @package WP_Blogcard
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin constants.
define( 'WP_BLOGCARD_VERSION', '1.0.0' );
define( 'WP_BLOGCARD_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WP_BLOGCARD_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Load plugin classes.
 */
require_once WP_BLOGCARD_PLUGIN_DIR . 'includes/class-wp-blogcard-cache.php';
require_once WP_BLOGCARD_PLUGIN_DIR . 'includes/class-wp-blogcard-ogp-fetcher.php';
require_once WP_BLOGCARD_PLUGIN_DIR . 'includes/class-wp-blogcard-rest-api.php';

/**
 * Initialize the plugin.
 *
 * @return void
 */
function wp_blogcard_init() {
	// Initialize REST API.
	$rest_api = new WP_Blogcard_REST_API();
	$rest_api->init();
}
add_action( 'init', 'wp_blogcard_init' );

/**
 * Register the block.
 *
 * @return void
 */
function wp_blogcard_register_block() {
	register_block_type( WP_BLOGCARD_PLUGIN_DIR . 'build/wp-blogcard' );
}
add_action( 'init', 'wp_blogcard_register_block' );

