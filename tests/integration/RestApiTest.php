<?php
/**
 * Integration tests for WP_Blogcard_REST_API class.
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
 * Test case for REST API integration.
 *
 * These tests verify the REST API endpoints behave correctly
 * under various conditions including permission checks,
 * input validation, and response formatting.
 */
class RestApiTest extends TestCase {

	/**
	 * REST API instance.
	 *
	 * @var WP_Blogcard_REST_API
	 */
	private $rest_api;

	/**
	 * Set up test fixtures.
	 *
	 * @return void
	 */
	protected function set_up() {
		parent::set_up();
		$this->rest_api = new WP_Blogcard_REST_API();
	}

	// =========================================================================
	// URL Validation Tests
	// =========================================================================

	/**
	 * @test
	 * 有効なURLは検証を通過する
	 */
	public function validate_url_returns_true_for_valid_https_url() {
		// Arrange
		$valid_url = 'https://example.com/page';

		// Act
		$result = $this->rest_api->validate_url( $valid_url );

		// Assert
		$this->assertTrue( $result );
	}

	/**
	 * @test
	 * 有効なHTTP URLは検証を通過する
	 */
	public function validate_url_returns_true_for_valid_http_url() {
		// Arrange
		$valid_url = 'http://example.com/page';

		// Act
		$result = $this->rest_api->validate_url( $valid_url );

		// Assert
		$this->assertTrue( $result );
	}

	/**
	 * @test
	 * クエリパラメータ付きURLは検証を通過する
	 */
	public function validate_url_returns_true_for_url_with_query_params() {
		// Arrange
		$url_with_params = 'https://example.com/search?q=test&page=1';

		// Act
		$result = $this->rest_api->validate_url( $url_with_params );

		// Assert
		$this->assertTrue( $result );
	}

	/**
	 * @test
	 * 空文字列は検証に失敗する
	 */
	public function validate_url_returns_false_for_empty_string() {
		// Arrange
		$empty_url = '';

		// Act
		$result = $this->rest_api->validate_url( $empty_url );

		// Assert
		$this->assertFalse( $result );
	}

	/**
	 * @test
	 * スキームなしのURLは検証に失敗する
	 */
	public function validate_url_returns_false_for_url_without_scheme() {
		// Arrange
		$url_without_scheme = 'example.com/page';

		// Act
		$result = $this->rest_api->validate_url( $url_without_scheme );

		// Assert
		$this->assertFalse( $result );
	}

	/**
	 * @test
	 * 不正な形式のURLは検証に失敗する
	 */
	public function validate_url_returns_false_for_malformed_url() {
		// Arrange
		$malformed_url = 'not a valid url';

		// Act
		$result = $this->rest_api->validate_url( $malformed_url );

		// Assert
		$this->assertFalse( $result );
	}

	/**
	 * @test
	 * JavaScriptスキームは検証に失敗する
	 */
	public function validate_url_returns_false_for_javascript_scheme() {
		// Arrange
		$javascript_url = 'javascript:alert(1)';

		// Act
		$result = $this->rest_api->validate_url( $javascript_url );

		// Assert
		$this->assertFalse( $result );
	}

	/**
	 * @test
	 * data:スキームは検証に失敗する
	 */
	public function validate_url_returns_false_for_data_scheme() {
		// Arrange
		$data_url = 'data:text/html,<script>alert(1)</script>';

		// Act
		$result = $this->rest_api->validate_url( $data_url );

		// Assert
		$this->assertFalse( $result );
	}

	// =========================================================================
	// Permission Check Tests
	// =========================================================================

	/**
	 * @test
	 * check_permissionメソッドが存在し呼び出し可能である
	 */
	public function check_permission_method_is_callable() {
		// Arrange & Act & Assert
		$this->assertTrue(
			method_exists( $this->rest_api, 'check_permission' ),
			'check_permission method should exist'
		);
		$this->assertTrue(
			is_callable( array( $this->rest_api, 'check_permission' ) ),
			'check_permission method should be callable'
		);
	}

	/**
	 * @test
	 * check_admin_permissionメソッドが存在し呼び出し可能である
	 */
	public function check_admin_permission_method_is_callable() {
		// Arrange & Act & Assert
		$this->assertTrue(
			method_exists( $this->rest_api, 'check_admin_permission' ),
			'check_admin_permission method should exist'
		);
		$this->assertTrue(
			is_callable( array( $this->rest_api, 'check_admin_permission' ) ),
			'check_admin_permission method should be callable'
		);
	}

	// =========================================================================
	// REST API Namespace Tests
	// =========================================================================

	/**
	 * @test
	 * REST API名前空間が正しく定義されている
	 */
	public function namespace_is_correctly_defined() {
		// Arrange
		$expected_namespace = 'wp-blogcard/v1';

		// Act
		$actual_namespace = WP_Blogcard_REST_API::NAMESPACE;

		// Assert
		$this->assertSame( $expected_namespace, $actual_namespace );
	}

	// =========================================================================
	// API Initialization Tests
	// =========================================================================

	/**
	 * @test
	 * initメソッドが存在し呼び出し可能である
	 */
	public function init_method_is_callable() {
		// Arrange & Act & Assert
		$this->assertTrue(
			method_exists( $this->rest_api, 'init' ),
			'init method should exist'
		);
		$this->assertTrue(
			is_callable( array( $this->rest_api, 'init' ) ),
			'init method should be callable'
		);
	}

	/**
	 * @test
	 * register_routesメソッドが存在し呼び出し可能である
	 */
	public function register_routes_method_is_callable() {
		// Arrange & Act & Assert
		$this->assertTrue(
			method_exists( $this->rest_api, 'register_routes' ),
			'register_routes method should exist'
		);
		$this->assertTrue(
			is_callable( array( $this->rest_api, 'register_routes' ) ),
			'register_routes method should be callable'
		);
	}

	// =========================================================================
	// Fetch OGP Method Tests
	// =========================================================================

	/**
	 * @test
	 * fetch_ogpメソッドが存在し呼び出し可能である
	 */
	public function fetch_ogp_method_is_callable() {
		// Arrange & Act & Assert
		$this->assertTrue(
			method_exists( $this->rest_api, 'fetch_ogp' ),
			'fetch_ogp method should exist'
		);
		$this->assertTrue(
			is_callable( array( $this->rest_api, 'fetch_ogp' ) ),
			'fetch_ogp method should be callable'
		);
	}

	// =========================================================================
	// Clear Cache Method Tests
	// =========================================================================

	/**
	 * @test
	 * clear_cacheメソッドが存在し呼び出し可能である
	 */
	public function clear_cache_method_is_callable() {
		// Arrange & Act & Assert
		$this->assertTrue(
			method_exists( $this->rest_api, 'clear_cache' ),
			'clear_cache method should exist'
		);
		$this->assertTrue(
			is_callable( array( $this->rest_api, 'clear_cache' ) ),
			'clear_cache method should be callable'
		);
	}
}
