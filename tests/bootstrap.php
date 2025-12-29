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

// Mock WordPress functions for testing.
if ( ! function_exists( 'wp_parse_url' ) ) {
	/**
	 * Mock wp_parse_url function.
	 *
	 * @param string $url       The URL to parse.
	 * @param int    $component The specific component to retrieve.
	 * @return mixed Parsed URL or specific component.
	 */
	function wp_parse_url( $url, $component = -1 ) {
		return parse_url( $url, $component );
	}
}

if ( ! function_exists( 'esc_attr' ) ) {
	/**
	 * Mock esc_attr function.
	 *
	 * @param string $text Text to escape.
	 * @return string Escaped text.
	 */
	function esc_attr( $text ) {
		return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
	}
}

if ( ! function_exists( 'esc_html' ) ) {
	/**
	 * Mock esc_html function.
	 *
	 * @param string $text Text to escape.
	 * @return string Escaped text.
	 */
	function esc_html( $text ) {
		return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
	}
}

if ( ! function_exists( 'esc_url' ) ) {
	/**
	 * Mock esc_url function.
	 *
	 * @param string $url URL to escape.
	 * @return string Escaped URL.
	 */
	function esc_url( $url ) {
		return filter_var( $url, FILTER_SANITIZE_URL );
	}
}

if ( ! function_exists( 'esc_url_raw' ) ) {
	/**
	 * Mock esc_url_raw function.
	 *
	 * @param string $url URL to sanitize.
	 * @return string Sanitized URL.
	 */
	function esc_url_raw( $url ) {
		return filter_var( $url, FILTER_SANITIZE_URL );
	}
}

if ( ! function_exists( 'sanitize_text_field' ) ) {
	/**
	 * Mock sanitize_text_field function.
	 *
	 * @param string $str String to sanitize.
	 * @return string Sanitized string.
	 */
	function sanitize_text_field( $str ) {
		return trim( strip_tags( $str ) );
	}
}

if ( ! function_exists( 'wp_kses_post' ) ) {
	/**
	 * Mock wp_kses_post function.
	 *
	 * @param string $data Content to filter.
	 * @return string Filtered content.
	 */
	function wp_kses_post( $data ) {
		return strip_tags( $data, '<a><br><p><strong><em><ul><ol><li>' );
	}
}

if ( ! function_exists( '__' ) ) {
	/**
	 * Mock translation function.
	 *
	 * @param string $text   Text to translate.
	 * @param string $domain Text domain.
	 * @return string Original text.
	 */
	function __( $text, $domain = 'default' ) {
		return $text;
	}
}

if ( ! function_exists( '_e' ) ) {
	/**
	 * Mock echo translation function.
	 *
	 * @param string $text   Text to translate.
	 * @param string $domain Text domain.
	 */
	function _e( $text, $domain = 'default' ) {
		echo $text;
	}
}

if ( ! function_exists( 'esc_html__' ) ) {
	/**
	 * Mock esc_html__ function.
	 *
	 * @param string $text   Text to translate and escape.
	 * @param string $domain Text domain.
	 * @return string Escaped text.
	 */
	function esc_html__( $text, $domain = 'default' ) {
		return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
	}
}

// Transient storage for testing.
global $wp_test_transients;
$wp_test_transients = array();

if ( ! function_exists( 'set_transient' ) ) {
	/**
	 * Mock set_transient function.
	 *
	 * @param string $transient  Transient name.
	 * @param mixed  $value      Transient value.
	 * @param int    $expiration Time until expiration in seconds.
	 * @return bool True on success.
	 */
	function set_transient( $transient, $value, $expiration = 0 ) {
		global $wp_test_transients;
		$wp_test_transients[ $transient ] = array(
			'value'      => $value,
			'expiration' => $expiration > 0 ? time() + $expiration : 0,
		);
		return true;
	}
}

if ( ! function_exists( 'get_transient' ) ) {
	/**
	 * Mock get_transient function.
	 *
	 * @param string $transient Transient name.
	 * @return mixed Transient value or false.
	 */
	function get_transient( $transient ) {
		global $wp_test_transients;
		if ( ! isset( $wp_test_transients[ $transient ] ) ) {
			return false;
		}
		$data = $wp_test_transients[ $transient ];
		if ( $data['expiration'] > 0 && $data['expiration'] < time() ) {
			unset( $wp_test_transients[ $transient ] );
			return false;
		}
		return $data['value'];
	}
}

if ( ! function_exists( 'delete_transient' ) ) {
	/**
	 * Mock delete_transient function.
	 *
	 * @param string $transient Transient name.
	 * @return bool True on success.
	 */
	function delete_transient( $transient ) {
		global $wp_test_transients;
		unset( $wp_test_transients[ $transient ] );
		return true;
	}
}

// Load plugin files.
require_once WP_BLOGCARD_PLUGIN_DIR . 'includes/class-cache.php';
require_once WP_BLOGCARD_PLUGIN_DIR . 'includes/class-ogp-fetcher.php';
require_once WP_BLOGCARD_PLUGIN_DIR . 'includes/class-rest-api.php';
