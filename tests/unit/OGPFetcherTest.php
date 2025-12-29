<?php
/**
 * Unit tests for WP_Blogcard_OGP_Fetcher class.
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
 * Test case for OGP Fetcher.
 *
 * These tests verify the OGP fetching and parsing functionality,
 * including SSRF protection and fallback behavior.
 */
class OGPFetcherTest extends TestCase {

	// =========================================================================
	// SSRF Protection: Private IP Detection Tests
	// =========================================================================

	/**
	 * @test
	 * localhostはプライベートIPとして検出される
	 */
	public function is_private_ip_returns_true_for_localhost() {
		// Arrange
		$localhost_url = 'http://localhost/test';

		// Act
		$result = WP_Blogcard_OGP_Fetcher::is_private_ip( $localhost_url );

		// Assert
		$this->assertTrue( $result );
	}

	/**
	 * @test
	 * 127.0.0.1はプライベートIPとして検出される
	 */
	public function is_private_ip_returns_true_for_loopback_ipv4() {
		// Arrange
		$loopback_url = 'http://127.0.0.1/test';

		// Act
		$result = WP_Blogcard_OGP_Fetcher::is_private_ip( $loopback_url );

		// Assert
		$this->assertTrue( $result );
	}

	/**
	 * @test
	 * パブリックドメインはプライベートIPとして検出されない
	 *
	 * Note: このテストはDNS解決を必要とするため、
	 * 隔離された環境では失敗する可能性がある
	 */
	public function is_private_ip_returns_false_for_public_domain() {
		// Arrange
		$public_url = 'https://example.com/';

		// Act
		$result = WP_Blogcard_OGP_Fetcher::is_private_ip( $public_url );

		// Assert
		$this->assertFalse( $result );
	}

	// =========================================================================
	// OGP Parsing: Complete Data Tests
	// =========================================================================

	/**
	 * @test
	 * OGPタグからタイトルを正しく抽出できる
	 */
	public function parse_ogp_extracts_title_from_og_title_tag() {
		// Arrange
		$html = $this->create_html_with_ogp( array( 'og:title' => 'Test Title' ) );
		$url  = 'https://example.com/page';

		// Act
		$result = WP_Blogcard_OGP_Fetcher::parse_ogp( $html, $url );

		// Assert
		$this->assertSame( 'Test Title', $result['title'] );
	}

	/**
	 * @test
	 * OGPタグから説明文を正しく抽出できる
	 */
	public function parse_ogp_extracts_description_from_og_description_tag() {
		// Arrange
		$html = $this->create_html_with_ogp( array( 'og:description' => 'Test Description' ) );
		$url  = 'https://example.com/page';

		// Act
		$result = WP_Blogcard_OGP_Fetcher::parse_ogp( $html, $url );

		// Assert
		$this->assertSame( 'Test Description', $result['description'] );
	}

	/**
	 * @test
	 * OGPタグから画像URLを正しく抽出できる
	 */
	public function parse_ogp_extracts_image_from_og_image_tag() {
		// Arrange
		$html = $this->create_html_with_ogp( array( 'og:image' => 'https://example.com/image.jpg' ) );
		$url  = 'https://example.com/page';

		// Act
		$result = WP_Blogcard_OGP_Fetcher::parse_ogp( $html, $url );

		// Assert
		$this->assertSame( 'https://example.com/image.jpg', $result['image'] );
	}

	/**
	 * @test
	 * OGPタグからサイト名を正しく抽出できる
	 */
	public function parse_ogp_extracts_site_name_from_og_site_name_tag() {
		// Arrange
		$html = $this->create_html_with_ogp( array( 'og:site_name' => 'Test Site' ) );
		$url  = 'https://example.com/page';

		// Act
		$result = WP_Blogcard_OGP_Fetcher::parse_ogp( $html, $url );

		// Assert
		$this->assertSame( 'Test Site', $result['site_name'] );
	}

	// =========================================================================
	// OGP Parsing: Fallback to Title Tag Tests
	// =========================================================================

	/**
	 * @test
	 * OGPタイトルがない場合はtitleタグにフォールバックする
	 */
	public function parse_ogp_falls_back_to_title_tag_when_og_title_missing() {
		// Arrange
		$html = '<!DOCTYPE html>
			<html>
			<head><title>Fallback Title</title></head>
			<body></body>
			</html>';
		$url  = 'https://example.com/page';

		// Act
		$result = WP_Blogcard_OGP_Fetcher::parse_ogp( $html, $url );

		// Assert
		$this->assertSame( 'Fallback Title', $result['title'] );
	}

	// =========================================================================
	// OGP Parsing: Fallback to Twitter Card Tests
	// =========================================================================

	/**
	 * @test
	 * OGPタイトルがない場合はTwitter Cardにフォールバックする
	 */
	public function parse_ogp_falls_back_to_twitter_title_when_og_title_missing() {
		// Arrange
		$html = '<!DOCTYPE html>
			<html>
			<head>
				<meta name="twitter:title" content="Twitter Title">
			</head>
			<body></body>
			</html>';
		$url  = 'https://example.com/page';

		// Act
		$result = WP_Blogcard_OGP_Fetcher::parse_ogp( $html, $url );

		// Assert
		$this->assertSame( 'Twitter Title', $result['title'] );
	}

	/**
	 * @test
	 * OGP説明文がない場合はTwitter Card説明文にフォールバックする
	 */
	public function parse_ogp_falls_back_to_twitter_description_when_og_description_missing() {
		// Arrange
		$html = '<!DOCTYPE html>
			<html>
			<head>
				<meta name="twitter:description" content="Twitter Description">
			</head>
			<body></body>
			</html>';
		$url  = 'https://example.com/page';

		// Act
		$result = WP_Blogcard_OGP_Fetcher::parse_ogp( $html, $url );

		// Assert
		$this->assertSame( 'Twitter Description', $result['description'] );
	}

	/**
	 * @test
	 * OGP画像がない場合はTwitter Card画像にフォールバックする
	 */
	public function parse_ogp_falls_back_to_twitter_image_when_og_image_missing() {
		// Arrange
		$html = '<!DOCTYPE html>
			<html>
			<head>
				<meta name="twitter:image" content="https://example.com/twitter-image.jpg">
			</head>
			<body></body>
			</html>';
		$url  = 'https://example.com/page';

		// Act
		$result = WP_Blogcard_OGP_Fetcher::parse_ogp( $html, $url );

		// Assert
		$this->assertSame( 'https://example.com/twitter-image.jpg', $result['image'] );
	}

	// =========================================================================
	// OGP Parsing: Site Name Fallback Tests
	// =========================================================================

	/**
	 * @test
	 * サイト名がない場合はホスト名にフォールバックする
	 */
	public function parse_ogp_falls_back_to_hostname_when_site_name_missing() {
		// Arrange
		$html = '<!DOCTYPE html><html><head></head><body></body></html>';
		$url  = 'https://example.com/page';

		// Act
		$result = WP_Blogcard_OGP_Fetcher::parse_ogp( $html, $url );

		// Assert
		$this->assertSame( 'example.com', $result['site_name'] );
	}

	// =========================================================================
	// OGP Parsing: Favicon Tests
	// =========================================================================

	/**
	 * @test
	 * faviconがない場合はGoogleのfaviconサービスにフォールバックする
	 */
	public function parse_ogp_falls_back_to_google_favicon_service_when_favicon_missing() {
		// Arrange
		$html = '<!DOCTYPE html><html><head></head><body></body></html>';
		$url  = 'https://example.com/page';

		// Act
		$result = WP_Blogcard_OGP_Fetcher::parse_ogp( $html, $url );

		// Assert
		$this->assertStringContainsString( 'google.com/s2/favicons', $result['favicon'] );
		$this->assertStringContainsString( 'example.com', $result['favicon'] );
	}

	// =========================================================================
	// OGP Parsing: Relative URL Resolution Tests
	// =========================================================================

	/**
	 * @test
	 * 相対パスの画像URLは絶対URLに変換される
	 */
	public function parse_ogp_converts_relative_image_url_to_absolute() {
		// Arrange
		$html = $this->create_html_with_ogp( array( 'og:image' => '/images/photo.jpg' ) );
		$url  = 'https://example.com/page';

		// Act
		$result = WP_Blogcard_OGP_Fetcher::parse_ogp( $html, $url );

		// Assert
		$this->assertSame( 'https://example.com/images/photo.jpg', $result['image'] );
	}

	// =========================================================================
	// Helper Methods
	// =========================================================================

	/**
	 * OGPメタタグを含むHTMLを生成するヘルパーメソッド
	 *
	 * @param array $og_tags OGPタグの配列 (property => content)
	 * @return string 生成されたHTML
	 */
	private function create_html_with_ogp( array $og_tags ): string {
		$meta_tags = '';
		foreach ( $og_tags as $property => $content ) {
			$meta_tags .= sprintf(
				'<meta property="%s" content="%s">',
				esc_attr( $property ),
				esc_attr( $content )
			);
		}

		return sprintf(
			'<!DOCTYPE html><html><head>%s</head><body></body></html>',
			$meta_tags
		);
	}
}
