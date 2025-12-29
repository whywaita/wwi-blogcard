import { test, expect } from '@playwright/test';

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

		// Wait for editor to load
		await page.waitForSelector( '.block-editor-writing-flow' );

		// Add block
		await page.click( '[aria-label="Add block"]' );
		await page.fill( '[placeholder="Search"]', 'Blogcard' );
		await page.click( '[class*="block-editor-block-types-list__item"]' );

		// Verify block is added
		await expect(
			page.locator( '.wp-block-wp-blogcard-blogcard' )
		).toBeVisible();
	} );

	test( 'can fetch OGP data', async ( { page } ) => {
		// Create new post
		await page.goto( '/wp-admin/post-new.php' );
		await page.waitForSelector( '.block-editor-writing-flow' );

		// Add block
		await page.click( '[aria-label="Add block"]' );
		await page.fill( '[placeholder="Search"]', 'Blogcard' );
		await page.click( '[class*="block-editor-block-types-list__item"]' );

		// Enter URL
		await page.fill( 'input[type="url"]', 'https://example.com' );
		await page.click( 'button:has-text("Fetch")' );

		// Wait for preview to load
		await expect( page.locator( '.wp-blogcard__title' ) ).toBeVisible( {
			timeout: 10000,
		} );
	} );
} );
