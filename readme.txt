=== WWI Blogcard ===
Contributors: whywaita
Tags: blogcard, ogp, embed, link, card
Requires at least: 6.0
Tested up to: 6.9
Stable tag: 1.0.7
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A WordPress block plugin that generates beautiful blog cards from URLs using OGP information.

== Description ==

WWI Blogcard is a Gutenberg block plugin that creates visually appealing blog cards from any URL. Simply paste a URL, and the plugin automatically fetches OGP (Open Graph Protocol) information to generate a rich preview card.

= Features =

* **Easy to use**: Just paste a URL and the blog card is automatically generated
* **OGP support**: Fetches title, description, and images from OGP meta tags
* **Responsive design**: Cards look great on any device
* **Dark mode support**: Automatic dark mode based on system preferences
* **Caching**: OGP data is cached for improved performance
* **Security**: SSRF protection blocks requests to private IP addresses

= Usage =

1. Add a new "Blogcard" block in the Gutenberg editor
2. Paste the URL you want to create a card for
3. Click "Fetch" to retrieve OGP information
4. The blog card is automatically generated

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/wwi-blogcard` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Use the "Blogcard" block in the Gutenberg editor.

== Source Code ==

The source code for this plugin, including uncompiled JavaScript and CSS files, is available on GitHub:

https://github.com/whywaita/wwi-blogcard

= Building from Source =

To build the plugin from source, you need Node.js and npm installed. Then run the following commands:

1. Clone the repository: `git clone https://github.com/whywaita/wwi-blogcard.git`
2. Navigate to the plugin directory: `cd wwi-blogcard`
3. Install dependencies: `npm install`
4. Build the plugin: `npm run build`

The build process uses `@wordpress/scripts` to compile and minify the JavaScript and CSS files from the `src/` directory to the `build/` directory.

== Frequently Asked Questions ==

= What is OGP? =

OGP (Open Graph Protocol) is a protocol that allows web pages to become rich objects in a social graph. It's commonly used by social media platforms to display link previews.

= How long is OGP data cached? =

OGP data is cached for 24 hours by default using WordPress Transients API.

= Does this plugin support private/internal URLs? =

No, for security reasons (SSRF protection), the plugin blocks requests to private IP addresses and localhost.

== Screenshots ==

1. Blog card example in the editor
2. Blog card displayed on the frontend
3. Block settings panel

== Changelog ==
See: https://github.com/whywaita/wwi-blogcard/blob/main/CHANGELOG.md
== Upgrade Notice ==

= 1.0.0 =
Initial release of WWI Blogcard.
