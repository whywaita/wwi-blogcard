<?php
/**
 * Unit tests for WP_Blogcard_Cache class.
 *
 * Testing methodology: t_wada style
 * - Arrange-Act-Assert (Given-When-Then) pattern
 * - One logical assertion per test
 * - Descriptive test names that explain behavior
 * - Tests as documentation
 *
 * @package WP_Blogcard
 */

use Yoast\PHPUnitPolyfills\TestCases\TestCase;

/**
 * Test case for Cache.
 *
 * These tests verify the cache key generation and
 * cache management functionality.
 */
class CacheTest extends TestCase {

	// =========================================================================
	// Cache Key Generation Tests
	// =========================================================================

	/**
	 * @test
	 * キャッシュキーにプレフィックスが含まれる
	 */
	public function get_cache_key_includes_prefix() {
		// Arrange
		$url = 'https://example.com/test';

		// Act
		$cache_key = WP_Blogcard_Cache::get_cache_key( $url );

		// Assert
		$this->assertStringStartsWith( 'wp_blogcard_', $cache_key );
	}

	/**
	 * @test
	 * キャッシュキーにURLのMD5ハッシュが含まれる
	 */
	public function get_cache_key_includes_md5_hash_of_url() {
		// Arrange
		$url           = 'https://example.com/test';
		$expected_hash = md5( $url );

		// Act
		$cache_key = WP_Blogcard_Cache::get_cache_key( $url );

		// Assert
		$this->assertStringContainsString( $expected_hash, $cache_key );
	}

	/**
	 * @test
	 * キャッシュキーの形式が正しい（プレフィックス + MD5）
	 */
	public function get_cache_key_has_correct_format() {
		// Arrange
		$url      = 'https://example.com/test';
		$expected = 'wp_blogcard_' . md5( $url );

		// Act
		$cache_key = WP_Blogcard_Cache::get_cache_key( $url );

		// Assert
		$this->assertSame( $expected, $cache_key );
	}

	// =========================================================================
	// Cache Key Uniqueness Tests
	// =========================================================================

	/**
	 * @test
	 * 異なるURLは異なるキャッシュキーを生成する
	 */
	public function get_cache_key_generates_different_keys_for_different_urls() {
		// Arrange
		$url1 = 'https://example.com/page1';
		$url2 = 'https://example.com/page2';

		// Act
		$key1 = WP_Blogcard_Cache::get_cache_key( $url1 );
		$key2 = WP_Blogcard_Cache::get_cache_key( $url2 );

		// Assert
		$this->assertNotSame( $key1, $key2 );
	}

	/**
	 * @test
	 * クエリパラメータが異なるURLは異なるキャッシュキーを生成する
	 */
	public function get_cache_key_generates_different_keys_for_urls_with_different_query_params() {
		// Arrange
		$url1 = 'https://example.com/search?q=foo';
		$url2 = 'https://example.com/search?q=bar';

		// Act
		$key1 = WP_Blogcard_Cache::get_cache_key( $url1 );
		$key2 = WP_Blogcard_Cache::get_cache_key( $url2 );

		// Assert
		$this->assertNotSame( $key1, $key2 );
	}

	// =========================================================================
	// Cache Key Consistency Tests
	// =========================================================================

	/**
	 * @test
	 * 同じURLは常に同じキャッシュキーを生成する
	 */
	public function get_cache_key_generates_same_key_for_same_url() {
		// Arrange
		$url = 'https://example.com/test';

		// Act
		$key1 = WP_Blogcard_Cache::get_cache_key( $url );
		$key2 = WP_Blogcard_Cache::get_cache_key( $url );

		// Assert
		$this->assertSame( $key1, $key2 );
	}

	/**
	 * @test
	 * キャッシュキー生成は冪等である（何度呼んでも同じ結果）
	 */
	public function get_cache_key_is_idempotent() {
		// Arrange
		$url  = 'https://example.com/test';
		$keys = array();

		// Act
		for ( $i = 0; $i < 5; $i++ ) {
			$keys[] = WP_Blogcard_Cache::get_cache_key( $url );
		}

		// Assert
		$unique_keys = array_unique( $keys );
		$this->assertCount( 1, $unique_keys, 'All generated keys should be identical' );
	}

	// =========================================================================
	// Cache Constants Tests
	// =========================================================================

	/**
	 * @test
	 * キャッシュ有効期限が24時間（86400秒）である
	 */
	public function cache_expiration_is_24_hours() {
		// Arrange
		$expected_seconds = 86400; // 24 * 60 * 60

		// Act
		$actual_expiration = WP_Blogcard_Cache::CACHE_EXPIRATION;

		// Assert
		$this->assertSame( $expected_seconds, $actual_expiration );
	}

	/**
	 * @test
	 * キャッシュプレフィックスが正しく定義されている
	 */
	public function cache_prefix_is_correctly_defined() {
		// Arrange
		$expected_prefix = 'wp_blogcard_';

		// Act
		$actual_prefix = WP_Blogcard_Cache::CACHE_PREFIX;

		// Assert
		$this->assertSame( $expected_prefix, $actual_prefix );
	}

	// =========================================================================
	// Method Existence Tests
	// =========================================================================

	/**
	 * @test
	 * getメソッドが存在し呼び出し可能である
	 */
	public function get_method_is_callable() {
		// Assert
		$this->assertTrue(
			method_exists( 'WP_Blogcard_Cache', 'get' ),
			'get method should exist'
		);
		$this->assertTrue(
			is_callable( array( 'WP_Blogcard_Cache', 'get' ) ),
			'get method should be callable'
		);
	}

	/**
	 * @test
	 * setメソッドが存在し呼び出し可能である
	 */
	public function set_method_is_callable() {
		// Assert
		$this->assertTrue(
			method_exists( 'WP_Blogcard_Cache', 'set' ),
			'set method should exist'
		);
		$this->assertTrue(
			is_callable( array( 'WP_Blogcard_Cache', 'set' ) ),
			'set method should be callable'
		);
	}

	/**
	 * @test
	 * deleteメソッドが存在し呼び出し可能である
	 */
	public function delete_method_is_callable() {
		// Assert
		$this->assertTrue(
			method_exists( 'WP_Blogcard_Cache', 'delete' ),
			'delete method should exist'
		);
		$this->assertTrue(
			is_callable( array( 'WP_Blogcard_Cache', 'delete' ) ),
			'delete method should be callable'
		);
	}

	/**
	 * @test
	 * clear_allメソッドが存在し呼び出し可能である
	 */
	public function clear_all_method_is_callable() {
		// Assert
		$this->assertTrue(
			method_exists( 'WP_Blogcard_Cache', 'clear_all' ),
			'clear_all method should exist'
		);
		$this->assertTrue(
			is_callable( array( 'WP_Blogcard_Cache', 'clear_all' ) ),
			'clear_all method should be callable'
		);
	}
}
