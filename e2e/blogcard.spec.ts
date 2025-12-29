import { test, expect, type Page, type FrameLocator } from '@playwright/test';

/**
 * Helper function to close the welcome dialog if present.
 */
async function closeWelcomeDialog( page: Page ): Promise< void > {
	const closeButton = page.locator(
		'button[aria-label="Close"], .components-modal__header button'
	);
	if ( await closeButton.isVisible( { timeout: 3000 } ).catch( () => false ) ) {
		await closeButton.click();
	}
}

/**
 * Helper function to get the editor frame (handles iframe-based editor).
 */
async function getEditorFrame( page: Page ): Promise< FrameLocator | Page > {
	// Wait for iframe to appear
	await page.waitForTimeout( 1000 );

	// Try the most common iframe selector first
	const iframe = page.frameLocator( 'iframe' ).first();
	const iframeExists = await iframe
		.locator( 'body' )
		.isVisible( { timeout: 2000 } )
		.catch( () => false );

	if ( iframeExists ) {
		return iframe;
	}
	return page;
}

test.describe( 'Blogcard Block', () => {
	test.beforeEach( async ( { page } ) => {
		// Login to WordPress admin
		await page.goto( '/wp-admin' );
		await page.fill( '#user_login', 'admin' );
		await page.fill( '#user_pass', 'password' );
		await page.click( '#wp-submit' );
		await page.waitForURL( /wp-admin/ );
	} );

	test( 'can add a blogcard block', async ( { page } ) => {
		// Create new post
		await page.goto( '/wp-admin/post-new.php' );

		// Close welcome dialog if present
		await closeWelcomeDialog( page );

		// Get editor frame (handles iframe-based editor in WP 6.x)
		const editor = await getEditorFrame( page );

		// Wait for editor to be ready - look for the "Add title" placeholder or default block button
		await editor.locator( 'text="Add title"' ).or(
			editor.locator( 'text="Type / to choose a block"' )
		).waitFor( { timeout: 10000 } );

		// Add block using the inserter - button in the main page, not iframe
		await page.click( 'button[aria-label="Block Inserter"]' );
		await page.fill( '[placeholder="Search"]', 'Blogcard' );
		// Click on the Blogcard option in the block list (not the external "Simple Blog Card")
		await page.click( '[role="listbox"][aria-label="Blocks"] [role="option"]:has-text("Blogcard")' );

		// Verify block is added
		await expect(
			editor.locator( '.wp-block-wp-blogcard-blogcard' )
		).toBeVisible( { timeout: 10000 } );
	} );

	test( 'can fetch OGP data', async ( { page } ) => {
		// Create new post
		await page.goto( '/wp-admin/post-new.php' );

		// Close welcome dialog if present
		await closeWelcomeDialog( page );

		// Get editor frame
		const editor = await getEditorFrame( page );

		// Wait for editor to be ready
		await editor.locator( 'text="Add title"' ).or(
			editor.locator( 'text="Type / to choose a block"' )
		).waitFor( { timeout: 10000 } );

		// Add block using the inserter
		await page.click( 'button[aria-label="Block Inserter"]' );
		await page.fill( '[placeholder="Search"]', 'Blogcard' );
		// Click on the Blogcard option in the block list (not the external "Simple Blog Card")
		await page.click( '[role="listbox"][aria-label="Blocks"] [role="option"]:has-text("Blogcard")' );

		// Wait for block to be added
		await editor.locator( '.wp-block-wp-blogcard-blogcard' ).waitFor( { timeout: 10000 } );

		// Enter URL - try main page first, then iframe
		let urlInput = page.locator( 'input[type="url"]' );
		if ( ! ( await urlInput.isVisible( { timeout: 2000 } ).catch( () => false ) ) ) {
			urlInput = editor.locator( 'input[type="url"]' );
		}
		await urlInput.fill( 'https://example.com' );

		// Click fetch button - try main page first, then iframe
		let fetchButton = page.locator( 'button:has-text("Fetch")' );
		if ( ! ( await fetchButton.isVisible( { timeout: 2000 } ).catch( () => false ) ) ) {
			fetchButton = editor.locator( 'button:has-text("Fetch")' );
		}
		await fetchButton.click();

		// Wait for preview to load - try iframe first (most common), then main page
		const previewTitle = editor.locator( '.wp-blogcard__title' );
		await expect( previewTitle ).toBeVisible( { timeout: 15000 } );
	} );
} );
