<?php
/**
 * REST API class for WP Blogcard.
 *
 * @package WP_Blogcard
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WP_Blogcard_REST_API
 *
 * Handles REST API endpoints for the plugin.
 */
class WP_Blogcard_REST_API {

	/**
	 * REST API namespace.
	 *
	 * @var string
	 */
	const NAMESPACE = 'wp-blogcard/v1';

	/**
	 * Initialize the REST API.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Register REST API routes.
	 *
	 * @return void
	 */
	public function register_routes() {
		register_rest_route(
			self::NAMESPACE,
			'/fetch',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'fetch_ogp' ),
				'permission_callback' => array( $this, 'check_permission' ),
				'args'                => array(
					'url' => array(
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'esc_url_raw',
						'validate_callback' => array( $this, 'validate_url' ),
					),
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/clear-cache',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'clear_cache' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
				'args'                => array(
					'url' => array(
						'required'          => false,
						'type'              => 'string',
						'sanitize_callback' => 'esc_url_raw',
					),
				),
			)
		);
	}

	/**
	 * Check if the user has permission to use the API.
	 *
	 * @return bool True if the user has permission, false otherwise.
	 */
	public function check_permission() {
		return current_user_can( 'edit_posts' );
	}

	/**
	 * Check if the user has admin permission.
	 *
	 * @return bool True if the user has admin permission, false otherwise.
	 */
	public function check_admin_permission() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Validate URL parameter.
	 *
	 * @param string $url The URL to validate.
	 * @return bool True if the URL is valid, false otherwise.
	 */
	public function validate_url( $url ) {
		return filter_var( $url, FILTER_VALIDATE_URL ) !== false;
	}

	/**
	 * Fetch OGP data for a URL.
	 *
	 * @param WP_REST_Request $request The request object.
	 * @return WP_REST_Response|WP_Error The response object.
	 */
	public function fetch_ogp( $request ) {
		$url = $request->get_param( 'url' );

		$result = WP_Blogcard_OGP_Fetcher::fetch( $url );

		if ( is_wp_error( $result ) ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'message' => $result->get_error_message(),
				),
				400
			);
		}

		return new WP_REST_Response(
			array(
				'success' => true,
				'data'    => $result,
			),
			200
		);
	}

	/**
	 * Clear cache for a specific URL or all cache.
	 *
	 * @param WP_REST_Request $request The request object.
	 * @return WP_REST_Response The response object.
	 */
	public function clear_cache( $request ) {
		$url = $request->get_param( 'url' );

		if ( ! empty( $url ) ) {
			// Clear cache for specific URL.
			$deleted = WP_Blogcard_Cache::delete( $url );
			return new WP_REST_Response(
				array(
					'success' => $deleted,
					'message' => $deleted
						? __( 'Cache cleared for the specified URL.', 'wp-blogcard' )
						: __( 'No cache found for the specified URL.', 'wp-blogcard' ),
				),
				200
			);
		}

		// Clear all cache.
		$count = WP_Blogcard_Cache::clear_all();
		return new WP_REST_Response(
			array(
				'success' => true,
				'message' => sprintf(
					/* translators: %d: number of cache entries cleared */
					__( 'Cleared %d cache entries.', 'wp-blogcard' ),
					$count
				),
				'count'   => $count,
			),
			200
		);
	}
}
