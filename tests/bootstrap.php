<?php
/**
 * PHPUnit bootstrap file.
 *
 * @package WP_Blogcard
 */

// Composer autoloader.
require_once dirname( __DIR__ ) . '/vendor/autoload.php';

// Load Yoast PHPUnit Polyfills.
require_once dirname( __DIR__ ) . '/vendor/yoast/phpunit-polyfills/phpunitpolyfills-autoload.php';

// Define WordPress constants for tests.
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', '/tmp/wordpress/' );
}

if ( ! defined( 'WP_BLOGCARD_VERSION' ) ) {
	define( 'WP_BLOGCARD_VERSION', '1.0.0' );
}

if ( ! defined( 'WP_BLOGCARD_PLUGIN_DIR' ) ) {
	define( 'WP_BLOGCARD_PLUGIN_DIR', dirname( __DIR__ ) . '/' );
}

if ( ! defined( 'DAY_IN_SECONDS' ) ) {
	define( 'DAY_IN_SECONDS', 86400 );
}

// Load plugin files.
require_once WP_BLOGCARD_PLUGIN_DIR . 'includes/class-cache.php';
require_once WP_BLOGCARD_PLUGIN_DIR . 'includes/class-ogp-fetcher.php';
require_once WP_BLOGCARD_PLUGIN_DIR . 'includes/class-rest-api.php';
