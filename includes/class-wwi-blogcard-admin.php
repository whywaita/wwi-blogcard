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
		add_action( 'admin_init', array( $this, 'handle_single_cache_delete' ) );
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
	 * Handle single cache entry deletion.
	 *
	 * @return void
	 */
	public function handle_single_cache_delete() {
		// Check if the form was submitted.
		if ( ! isset( $_POST['wwi_blogcard_delete_single_cache'] ) ) {
			return;
		}

		// Verify nonce.
		if ( ! isset( $_POST['wwi_blogcard_single_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wwi_blogcard_single_nonce'] ) ), 'wwi_blogcard_delete_single_cache' ) ) {
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

		// Get the URL to delete.
		if ( ! isset( $_POST['wwi_blogcard_cache_url'] ) ) {
			return;
		}

		$url = sanitize_url( wp_unslash( $_POST['wwi_blogcard_cache_url'] ) );

		if ( empty( $url ) ) {
			return;
		}

		// Delete the cache entry.
		$deleted = WWI_Blogcard_Cache::delete( $url );

		if ( $deleted ) {
			add_settings_error(
				'wwi_blogcard_messages',
				'wwi_blogcard_single_cache_deleted',
				__( 'Cache entry deleted successfully.', 'wwi-blogcard' ),
				'success'
			);
		} else {
			add_settings_error(
				'wwi_blogcard_messages',
				'wwi_blogcard_single_cache_not_found',
				__( 'Cache entry not found.', 'wwi-blogcard' ),
				'error'
			);
		}
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
		<style>
			.wwi-blogcard-admin .card {
				max-width: 100%;
			}
			.wwi-blogcard-admin .widefat .column-url {
				width: 45%;
				word-break: break-all;
			}
			.wwi-blogcard-admin .widefat .column-title {
				width: 45%;
			}
			.wwi-blogcard-admin .widefat .column-action {
				width: 10%;
			}
		</style>
		<div class="wrap wwi-blogcard-admin">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

			<?php settings_errors( 'wwi_blogcard_messages' ); ?>

			<div class="card">
				<h2><?php esc_html_e( 'Cache Management', 'wwi-blogcard' ); ?></h2>
				<p>
					<?php
					/* translators: %d: number of cached entries */
					$cache_message = _n(
						'Currently %d URL is cached.',
						'Currently %d URLs are cached.',
						$cache_count,
						'wwi-blogcard'
					);
					printf( esc_html( $cache_message ), (int) $cache_count );
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
							<?php echo 0 === $cache_count ? 'disabled' : ''; ?>
						/>
					</p>
				</form>
			</div>

			<?php if ( $cache_count > 0 ) : ?>
				<div class="card">
					<h2><?php esc_html_e( 'Cached URLs', 'wwi-blogcard' ); ?></h2>
					<table class="widefat striped">
						<thead>
							<tr>
								<th class="column-url"><?php esc_html_e( 'URL', 'wwi-blogcard' ); ?></th>
								<th class="column-title"><?php esc_html_e( 'Title', 'wwi-blogcard' ); ?></th>
								<th class="column-action"><?php esc_html_e( 'Action', 'wwi-blogcard' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							$cache_entries = WWI_Blogcard_Cache::get_all();
							foreach ( $cache_entries as $entry ) :
								?>
								<tr>
									<td>
										<a href="<?php echo esc_url( $entry['url'] ); ?>" target="_blank" rel="noopener noreferrer">
											<?php echo esc_html( $entry['url'] ); ?>
										</a>
									</td>
									<td><?php echo esc_html( $entry['title'] ); ?></td>
									<td>
										<form method="post" action="" style="display:inline;">
											<?php wp_nonce_field( 'wwi_blogcard_delete_single_cache', 'wwi_blogcard_single_nonce' ); ?>
											<input type="hidden" name="wwi_blogcard_cache_url" value="<?php echo esc_attr( $entry['url'] ); ?>" />
											<input type="submit"
												name="wwi_blogcard_delete_single_cache"
												class="button button-small"
												value="<?php esc_attr_e( 'Delete', 'wwi-blogcard' ); ?>"
											/>
										</form>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}
}
