<?php
/**
 * Unit tests for WP_Blogcard_OGP_Fetcher class.
 *
 * @package WP_Blogcard
 */

use Yoast\PHPUnitPolyfills\TestCases\TestCase;

/**
 * Test case for OGP Fetcher.
 */
class OGPFetcherTest extends TestCase {

	/**
	 * Test that private IPs are correctly identified.
	 *
	 * @return void
	 */
	public function test_is_private_ip_localhost() {
		$this->assertTrue( WP_Blogcard_OGP_Fetcher::is_private_ip( 'http://localhost/test' ) );
		$this->assertTrue( WP_Blogcard_OGP_Fetcher::is_private_ip( 'http://127.0.0.1/test' ) );
	}

	/**
	 * Test that public IPs are not marked as private.
	 *
	 * @return void
	 */
	public function test_is_private_ip_public() {
		// Note: This test requires DNS resolution, so it may fail in isolated environments.
		$this->assertFalse( WP_Blogcard_OGP_Fetcher::is_private_ip( 'https://example.com/' ) );
	}

	/**
	 * Test OGP parsing with complete data.
	 *
	 * @return void
	 */
	public function test_parse_ogp_complete() {
		$html = '<!DOCTYPE html>
		<html>
		<head>
			<meta property="og:title" content="Test Title">
			<meta property="og:description" content="Test Description">
			<meta property="og:image" content="https://example.com/image.jpg">
			<meta property="og:site_name" content="Test Site">
			<link rel="icon" href="/favicon.ico">
		</head>
		<body></body>
		</html>';

		$result = WP_Blogcard_OGP_Fetcher::parse_ogp( $html, 'https://example.com/page' );

		$this->assertEquals( 'Test Title', $result['title'] );
		$this->assertEquals( 'Test Description', $result['description'] );
		$this->assertEquals( 'https://example.com/image.jpg', $result['image'] );
		$this->assertEquals( 'Test Site', $result['site_name'] );
	}

	/**
	 * Test OGP parsing with fallback to title tag.
	 *
	 * @return void
	 */
	public function test_parse_ogp_fallback_title() {
		$html = '<!DOCTYPE html>
		<html>
		<head>
			<title>Fallback Title</title>
		</head>
		<body></body>
		</html>';

		$result = WP_Blogcard_OGP_Fetcher::parse_ogp( $html, 'https://example.com/page' );

		$this->assertEquals( 'Fallback Title', $result['title'] );
	}

	/**
	 * Test OGP parsing with Twitter card fallback.
	 *
	 * @return void
	 */
	public function test_parse_ogp_twitter_fallback() {
		$html = '<!DOCTYPE html>
		<html>
		<head>
			<meta name="twitter:title" content="Twitter Title">
			<meta name="twitter:description" content="Twitter Description">
		</head>
		<body></body>
		</html>';

		$result = WP_Blogcard_OGP_Fetcher::parse_ogp( $html, 'https://example.com/page' );

		$this->assertEquals( 'Twitter Title', $result['title'] );
		$this->assertEquals( 'Twitter Description', $result['description'] );
	}
}
