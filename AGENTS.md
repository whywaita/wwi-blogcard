# AGENTS.md - AI Agent Guidelines for WWI Blogcard

This document provides guidance for AI agents working on the WWI Blogcard project.

## Project Overview

WWI Blogcard is a WordPress Gutenberg block plugin that generates beautiful blog cards from URLs using OGP (Open Graph Protocol) information.

## Tech Stack

- **Backend**: PHP 7.4+, WordPress 6.0+
- **Frontend**: React (JSX), WordPress Block API v3
- **Build Tools**: @wordpress/scripts, webpack
- **Testing**: PHPUnit (PHP), Playwright (E2E) - t_wada style methodology
- **Linting**: ESLint, Stylelint, PHP_CodeSniffer (WordPress Coding Standards)
- **i18n**: Supports English and Japanese translations

## Project Structure

```
wwi-blogcard/
├── wwi-blogcard.php     # Main plugin file
├── includes/            # PHP classes
│   ├── class-wwi-blogcard-cache.php       # Cache management (Transients API)
│   ├── class-wwi-blogcard-ogp-fetcher.php # OGP data fetching and parsing
│   └── class-wwi-blogcard-rest-api.php    # REST API endpoints
├── src/                 # Source files (React/JS)
│   └── wwi-blogcard/
│       ├── block.json   # Block metadata
│       ├── index.js     # Block registration
│       ├── edit.js      # Editor component
│       ├── save.js      # Frontend output
│       ├── style.scss   # Frontend styles
│       └── editor.scss  # Editor-only styles
├── build/               # Compiled assets (generated)
├── languages/           # Translation files (.pot, .po, .mo, .json)
├── tests/               # PHPUnit tests
└── e2e/                 # Playwright E2E tests
```

## Development Commands

```bash
# Start development environment
make dev

# Stop development environment
make dev-down

# Destroy environment including volumes
make dev-destroy

# Build assets
make build
# or: npm run build

# Start watch mode
npm run start

# Run all linters
make lint

# Fix lint issues
make lint-fix

# Run PHP tests
make test

# Run E2E tests
make test-e2e

# Run WordPress Plugin Check
make plugin-check

# Create distribution package
make package

# Clean build artifacts
make clean
```

## Coding Standards

### PHP
- Follow WordPress Coding Standards
- Use PHP 7.4+ features
- Add PHPDoc comments for all functions and classes
- Text domain: `wwi-blogcard`

### JavaScript/React
- Follow WordPress JavaScript Coding Standards
- Use functional components with hooks
- Import from `@wordpress/*` packages
- Text domain: `wwi-blogcard`

### CSS/SCSS
- Use BEM naming convention (`.wwi-blogcard__element`)
- Use CSS Custom Properties for theming
- Support dark mode with `prefers-color-scheme`

## Security Considerations

- SSRF protection: Block private IP addresses in OGP fetcher
- Sanitize all user input
- Escape all output
- Use WordPress nonces for AJAX requests
- Validate permissions with `current_user_can()`

## REST API Endpoints

- `POST /wwi-blogcard/v1/fetch` - Fetch OGP data for a URL (requires `edit_posts` capability)
- `POST /wwi-blogcard/v1/clear-cache` - Clear cache (requires `manage_options` capability)

## Internationalization (i18n)

The plugin supports multiple languages:

- English (default)
- Japanese (ja)

Translation files are located in `languages/`:
- `wwi-blogcard.pot` - Translation template
- `wwi-blogcard-ja.po` - Japanese translations (source)
- `wwi-blogcard-ja.mo` - Japanese translations (compiled)
- `wwi-blogcard-ja-*.json` - JavaScript translations

To regenerate translation files:
```bash
# Generate .pot file
npm run wp-env run cli -- wp i18n make-pot . languages/wwi-blogcard.pot --domain=wwi-blogcard

# Generate .mo from .po
npm run wp-env run cli -- wp i18n make-mo languages/

# Generate JSON for JavaScript
npm run wp-env run cli -- wp i18n make-json languages/ --no-purge
```

## Testing Methodology: t_wada Style

This project follows the **t_wada style** testing methodology:

1. **Arrange-Act-Assert (AAA) pattern** - Every test has clear setup, execution, and verification phases
2. **One logical assertion per test** - Each test verifies a single behavior
3. **Descriptive test names** - Test names describe the behavior being tested
4. **Tests as documentation** - Tests serve as living documentation

Example:
```php
/**
 * @test
 * OGPタグからタイトルを正しく抽出できる
 */
public function parse_ogp_extracts_title_from_og_title_tag() {
    // Arrange
    $html = $this->create_html_with_ogp( array( 'og:title' => 'Test Title' ) );

    // Act
    $result = WWI_Blogcard_OGP_Fetcher::parse_ogp( $html, $url );

    // Assert
    $this->assertSame( 'Test Title', $result['title'] );
}
```

See [docs/testing.md](docs/testing.md) for full details.

## CI/CD

GitHub Actions runs the following checks in parallel:

- **Lint and Build** - ESLint, Stylelint, webpack build
- **Lint PHP** - PHP_CodeSniffer with WordPress standards
- **Plugin Check** - WordPress Plugin Check
- **E2E Tests** - Playwright browser tests

## Release Process

This project uses [tagpr](https://github.com/Songmu/tagpr) for automated releases:

1. Push changes to `main` branch
2. tagpr creates a release PR with version bump
3. Merge the PR to create a release
4. GitHub Actions builds and attaches the plugin ZIP to the release

## Plugin Distribution

The distribution package includes only production files:
- `wwi-blogcard.php`
- `includes/`
- `build/`
- `languages/` (excluding `.gitkeep`)
- `readme.txt`
