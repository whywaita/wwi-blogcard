<?php
/**
 * Unit tests for WP_Blogcard_Cache class.
 *
 * @package WP_Blogcard
 */

use Yoast\PHPUnitPolyfills\TestCases\TestCase;

/**
 * Test case for Cache.
 */
class CacheTest extends TestCase {

	/**
	 * Test cache key generation.
	 *
	 * @return void
	 */
	public function test_get_cache_key() {
		$url      = 'https://example.com/test';
		$expected = 'wp_blogcard_' . md5( $url );

		$this->assertEquals( $expected, WP_Blogcard_Cache::get_cache_key( $url ) );
	}

	/**
	 * Test that different URLs generate different cache keys.
	 *
	 * @return void
	 */
	public function test_cache_keys_are_unique() {
		$url1 = 'https://example.com/page1';
		$url2 = 'https://example.com/page2';

		$key1 = WP_Blogcard_Cache::get_cache_key( $url1 );
		$key2 = WP_Blogcard_Cache::get_cache_key( $url2 );

		$this->assertNotEquals( $key1, $key2 );
	}

	/**
	 * Test that same URLs generate same cache keys.
	 *
	 * @return void
	 */
	public function test_cache_keys_are_consistent() {
		$url = 'https://example.com/test';

		$key1 = WP_Blogcard_Cache::get_cache_key( $url );
		$key2 = WP_Blogcard_Cache::get_cache_key( $url );

		$this->assertEquals( $key1, $key2 );
	}
}
