<?php
/**
 * Cache class for WWI Blogcard.
 *
 * @package WWI_Blogcard
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WWI_Blogcard_Cache
 *
 * Handles caching of OGP data using WordPress Transients API.
 */
class WWI_Blogcard_Cache {

	/**
	 * Cache expiration time in seconds (24 hours).
	 *
	 * @var int
	 */
	const CACHE_EXPIRATION = DAY_IN_SECONDS;

	/**
	 * Cache key prefix.
	 *
	 * @var string
	 */
	const CACHE_PREFIX = 'wwi_blogcard_';

	/**
	 * Generate a cache key for a URL.
	 *
	 * @param string $url The URL to generate a cache key for.
	 * @return string The cache key.
	 */
	public static function get_cache_key( $url ) {
		return self::CACHE_PREFIX . md5( $url );
	}

	/**
	 * Get cached OGP data for a URL.
	 *
	 * @param string $url The URL to get cached data for.
	 * @return array|false The cached data or false if not found.
	 */
	public static function get( $url ) {
		$cache_key = self::get_cache_key( $url );
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		return false;
	}

	/**
	 * Set cached OGP data for a URL.
	 *
	 * @param string $url  The URL to cache data for.
	 * @param array  $data The OGP data to cache.
	 * @return bool True if the value was set, false otherwise.
	 */
	public static function set( $url, $data ) {
		$cache_key = self::get_cache_key( $url );
		return set_transient( $cache_key, $data, self::CACHE_EXPIRATION );
	}

	/**
	 * Delete cached OGP data for a URL.
	 *
	 * @param string $url The URL to delete cached data for.
	 * @return bool True if the transient was deleted, false otherwise.
	 */
	public static function delete( $url ) {
		$cache_key = self::get_cache_key( $url );
		return delete_transient( $cache_key );
	}

	/**
	 * Clear all plugin cache.
	 *
	 * @return int Number of deleted cache entries.
	 */
	public static function clear_all() {
		global $wpdb;

		$count = 0;

		// Delete all transients with our prefix.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		$transients = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s",
				'_transient_' . self::CACHE_PREFIX . '%'
			)
		);

		foreach ( $transients as $transient ) {
			$transient_name = str_replace( '_transient_', '', $transient );
			if ( delete_transient( $transient_name ) ) {
				++$count;
			}
		}

		return $count;
	}
}
