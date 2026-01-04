<?php
/**
 * Admin settings page for WWI Blogcard.
 *
 * @package WWI_Blogcard
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WWI_Blogcard_Admin
 *
 * Handles the admin settings page for the plugin.
 */
class WWI_Blogcard_Admin {

	/**
	 * Initialize the admin page.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'handle_cache_clear' ) );
	}

	/**
	 * Add the admin menu page.
	 *
	 * @return void
	 */
	public function add_admin_menu() {
		add_options_page(
			__( 'WWI Blogcard Settings', 'wwi-blogcard' ),
			__( 'WWI Blogcard', 'wwi-blogcard' ),
			'manage_options',
			'wwi-blogcard-settings',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Handle cache clear form submission.
	 *
	 * @return void
	 */
	public function handle_cache_clear() {
		// Check if the form was submitted.
		if ( ! isset( $_POST['wwi_blogcard_clear_cache'] ) ) {
			return;
		}

		// Verify nonce.
		if ( ! isset( $_POST['wwi_blogcard_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wwi_blogcard_nonce'] ) ), 'wwi_blogcard_clear_cache' ) ) {
			add_settings_error(
				'wwi_blogcard_messages',
				'wwi_blogcard_nonce_error',
				__( 'Security check failed. Please try again.', 'wwi-blogcard' ),
				'error'
			);
			return;
		}

		// Check user capabilities.
		if ( ! current_user_can( 'manage_options' ) ) {
			add_settings_error(
				'wwi_blogcard_messages',
				'wwi_blogcard_permission_error',
				__( 'You do not have permission to perform this action.', 'wwi-blogcard' ),
				'error'
			);
			return;
		}

		// Clear the cache.
		$count = WWI_Blogcard_Cache::clear_all();

		// Add success message.
		add_settings_error(
			'wwi_blogcard_messages',
			'wwi_blogcard_cache_cleared',
			sprintf(
				/* translators: %d: number of cache entries cleared */
				_n(
					'Cache cleared successfully. %d entry was deleted.',
					'Cache cleared successfully. %d entries were deleted.',
					$count,
					'wwi-blogcard'
				),
				$count
			),
			'success'
		);
	}

	/**
	 * Render the settings page.
	 *
	 * @return void
	 */
	public function render_settings_page() {
		// Check user capabilities.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$cache_count = WWI_Blogcard_Cache::get_count();
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

			<?php settings_errors( 'wwi_blogcard_messages' ); ?>

			<div class="card">
				<h2><?php esc_html_e( 'Cache Management', 'wwi-blogcard' ); ?></h2>
				<p>
					<?php
					printf(
						/* translators: %d: number of cached entries */
						esc_html(
							_n(
								'Currently %d URL is cached.',
								'Currently %d URLs are cached.',
								$cache_count,
								'wwi-blogcard'
							)
						),
						esc_html( $cache_count )
					);
					?>
				</p>
				<p class="description">
					<?php esc_html_e( 'Cache is automatically cleared after 24 hours. Use the button below to manually clear all cached OGP data.', 'wwi-blogcard' ); ?>
				</p>

				<form method="post" action="">
					<?php wp_nonce_field( 'wwi_blogcard_clear_cache', 'wwi_blogcard_nonce' ); ?>
					<p>
						<input type="submit"
							name="wwi_blogcard_clear_cache"
							class="button button-secondary"
							value="<?php esc_attr_e( 'Clear All Cache', 'wwi-blogcard' ); ?>"
							<?php echo $cache_count === 0 ? 'disabled' : ''; ?>
						/>
					</p>
				</form>
			</div>

			<div class="card">
				<h2><?php esc_html_e( 'About WWI Blogcard', 'wwi-blogcard' ); ?></h2>
				<p>
					<?php esc_html_e( 'WWI Blogcard is a WordPress block plugin that generates beautiful blog cards from URLs using OGP (Open Graph Protocol) information.', 'wwi-blogcard' ); ?>
				</p>
				<p>
					<strong><?php esc_html_e( 'Version:', 'wwi-blogcard' ); ?></strong> <?php echo esc_html( WWI_BLOGCARD_VERSION ); ?>
				</p>
			</div>
		</div>
		<?php
	}
}
