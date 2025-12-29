<?php
/**
 * OGP Fetcher class for WP Blogcard.
 *
 * @package WP_Blogcard
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WP_Blogcard_OGP_Fetcher
 *
 * Fetches and parses OGP (Open Graph Protocol) data from URLs.
 */
class WP_Blogcard_OGP_Fetcher {

	/**
	 * Fetch OGP data from a URL.
	 *
	 * @param string $url The URL to fetch OGP data from.
	 * @return array|WP_Error The OGP data or WP_Error on failure.
	 */
	public static function fetch( $url ) {
		// Validate URL.
		if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
			return new WP_Error( 'invalid_url', __( 'Invalid URL provided.', 'wp-blogcard' ) );
		}

		// Check for SSRF - block private IPs.
		if ( self::is_private_ip( $url ) ) {
			return new WP_Error( 'blocked_url', __( 'Access to private IP addresses is not allowed.', 'wp-blogcard' ) );
		}

		// Check cache first.
		$cached = WP_Blogcard_Cache::get( $url );
		if ( false !== $cached ) {
			return $cached;
		}

		// Fetch the URL.
		$response = wp_remote_get(
			$url,
			array(
				'timeout'    => 10,
				'user-agent' => 'WP-Blogcard/' . WP_BLOGCARD_VERSION . ' (WordPress Plugin)',
				'sslverify'  => true,
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $status_code ) {
			return new WP_Error(
				'fetch_failed',
				sprintf(
					/* translators: %d: HTTP status code */
					__( 'Failed to fetch URL. Status code: %d', 'wp-blogcard' ),
					$status_code
				)
			);
		}

		$body = wp_remote_retrieve_body( $response );
		if ( empty( $body ) ) {
			return new WP_Error( 'empty_response', __( 'Empty response from URL.', 'wp-blogcard' ) );
		}

		// Parse OGP data.
		$ogp_data = self::parse_ogp( $body, $url );

		// Cache the result.
		WP_Blogcard_Cache::set( $url, $ogp_data );

		return $ogp_data;
	}

	/**
	 * Check if a URL points to a private IP address (SSRF protection).
	 *
	 * @param string $url The URL to check.
	 * @return bool True if the URL points to a private IP, false otherwise.
	 */
	public static function is_private_ip( $url ) {
		$parsed = wp_parse_url( $url );

		if ( ! isset( $parsed['host'] ) ) {
			return true; // Invalid URL, block it.
		}

		$host = $parsed['host'];

		// Check for localhost.
		if ( 'localhost' === $host || '127.0.0.1' === $host || '::1' === $host ) {
			return true;
		}

		// Resolve hostname to IP.
		$ip = gethostbyname( $host );

		// If gethostbyname returns the hostname, resolution failed.
		if ( $ip === $host ) {
			// Could be an IPv6 address or unresolvable host.
			$ip = $host;
		}

		// Check if IP is in private ranges.
		if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) === false ) {
			return true;
		}

		return false;
	}

	/**
	 * Parse OGP data from HTML content.
	 *
	 * @param string $html The HTML content to parse.
	 * @param string $url  The original URL for fallback data.
	 * @return array The parsed OGP data.
	 */
	public static function parse_ogp( $html, $url ) {
		$data = array(
			'title'       => '',
			'description' => '',
			'image'       => '',
			'site_name'   => '',
			'favicon'     => '',
			'url'         => $url,
		);

		// Suppress libxml errors.
		$previous_errors = libxml_use_internal_errors( true );

		$doc = new DOMDocument();

		// Ensure proper UTF-8 encoding for DOMDocument.
		// Add UTF-8 meta tag if not present to handle multibyte characters correctly.
		if ( stripos( $html, 'charset=' ) === false ) {
			$html = '<?xml encoding="UTF-8">' . $html;
		}
		$doc->loadHTML( $html, LIBXML_NOWARNING | LIBXML_NOERROR );

		// Restore error handling.
		libxml_clear_errors();
		libxml_use_internal_errors( $previous_errors );

		$xpath = new DOMXPath( $doc );

		// Get OGP meta tags.
		$og_properties = array(
			'og:title'       => 'title',
			'og:description' => 'description',
			'og:image'       => 'image',
			'og:site_name'   => 'site_name',
		);

		foreach ( $og_properties as $property => $key ) {
			$nodes = $xpath->query( sprintf( '//meta[@property="%s"]/@content', $property ) );
			if ( $nodes && $nodes->length > 0 ) {
				$data[ $key ] = $nodes->item( 0 )->nodeValue;
			}
		}

		// Fallback to Twitter cards.
		if ( empty( $data['title'] ) ) {
			$nodes = $xpath->query( '//meta[@name="twitter:title"]/@content' );
			if ( $nodes && $nodes->length > 0 ) {
				$data['title'] = $nodes->item( 0 )->nodeValue;
			}
		}

		if ( empty( $data['description'] ) ) {
			$nodes = $xpath->query( '//meta[@name="twitter:description"]/@content' );
			if ( $nodes && $nodes->length > 0 ) {
				$data['description'] = $nodes->item( 0 )->nodeValue;
			}
		}

		if ( empty( $data['image'] ) ) {
			$nodes = $xpath->query( '//meta[@name="twitter:image"]/@content' );
			if ( $nodes && $nodes->length > 0 ) {
				$data['image'] = $nodes->item( 0 )->nodeValue;
			}
		}

		// Fallback to standard meta tags.
		if ( empty( $data['title'] ) ) {
			$nodes = $xpath->query( '//title' );
			if ( $nodes && $nodes->length > 0 ) {
				$data['title'] = $nodes->item( 0 )->nodeValue;
			}
		}

		if ( empty( $data['description'] ) ) {
			$nodes = $xpath->query( '//meta[@name="description"]/@content' );
			if ( $nodes && $nodes->length > 0 ) {
				$data['description'] = $nodes->item( 0 )->nodeValue;
			}
		}

		// Get favicon.
		$favicon_queries = array(
			'//link[@rel="icon"]/@href',
			'//link[@rel="shortcut icon"]/@href',
			'//link[@rel="apple-touch-icon"]/@href',
		);

		foreach ( $favicon_queries as $query ) {
			$nodes = $xpath->query( $query );
			if ( $nodes && $nodes->length > 0 ) {
				$data['favicon'] = $nodes->item( 0 )->nodeValue;
				break;
			}
		}

		// Make relative URLs absolute.
		$parsed_url = wp_parse_url( $url );
		$base_url   = $parsed_url['scheme'] . '://' . $parsed_url['host'];

		if ( ! empty( $data['image'] ) && strpos( $data['image'], 'http' ) !== 0 ) {
			$data['image'] = $base_url . '/' . ltrim( $data['image'], '/' );
		}

		if ( ! empty( $data['favicon'] ) && strpos( $data['favicon'], 'http' ) !== 0 ) {
			$data['favicon'] = $base_url . '/' . ltrim( $data['favicon'], '/' );
		}

		// Fallback favicon to Google's service.
		if ( empty( $data['favicon'] ) ) {
			$data['favicon'] = 'https://www.google.com/s2/favicons?domain=' . rawurlencode( $parsed_url['host'] );
		}

		// Fallback site_name to hostname.
		if ( empty( $data['site_name'] ) ) {
			$data['site_name'] = $parsed_url['host'];
		}

		// Sanitize output.
		$data['title']       = sanitize_text_field( $data['title'] );
		$data['description'] = sanitize_text_field( $data['description'] );
		$data['image']       = esc_url_raw( $data['image'] );
		$data['site_name']   = sanitize_text_field( $data['site_name'] );
		$data['favicon']     = esc_url_raw( $data['favicon'] );

		return $data;
	}
}
