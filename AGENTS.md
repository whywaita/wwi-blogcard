# AGENTS.md - AI Agent Guidelines for WP Blogcard

This document provides guidance for AI agents working on the WP Blogcard project.

## Project Overview

WP Blogcard is a WordPress Gutenberg block plugin that generates beautiful blog cards from URLs using OGP (Open Graph Protocol) information.

## Tech Stack

- **Backend**: PHP 7.4+, WordPress 6.0+
- **Frontend**: React (JSX), WordPress Block API v3
- **Build Tools**: @wordpress/scripts, webpack
- **Testing**: PHPUnit (PHP), Playwright (E2E)
- **Linting**: ESLint, Stylelint, PHP_CodeSniffer (WordPress Coding Standards)

## Project Structure

```
wp-blogcard/
├── wp-blogcard.php      # Main plugin file
├── includes/            # PHP classes
│   ├── class-cache.php      # Cache management (Transients API)
│   ├── class-ogp-fetcher.php # OGP data fetching and parsing
│   └── class-rest-api.php    # REST API endpoints
├── src/                 # Source files (React/JS)
│   └── wp-blogcard/
│       ├── block.json   # Block metadata
│       ├── index.js     # Block registration
│       ├── edit.js      # Editor component
│       ├── save.js      # Frontend output
│       ├── style.scss   # Frontend styles
│       └── editor.scss  # Editor-only styles
├── build/               # Compiled assets (generated)
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
npm run build

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
```

## Coding Standards

### PHP
- Follow WordPress Coding Standards
- Use PHP 7.4+ features
- Add PHPDoc comments for all functions and classes
- Text domain: `wp-blogcard`

### JavaScript/React
- Follow WordPress JavaScript Coding Standards
- Use functional components with hooks
- Import from `@wordpress/*` packages

### CSS/SCSS
- Use BEM naming convention (`.wp-blogcard__element`)
- Use CSS Custom Properties for theming
- Support dark mode with `prefers-color-scheme`

## Security Considerations

- SSRF protection: Block private IP addresses in OGP fetcher
- Sanitize all user input
- Escape all output
- Use WordPress nonces for AJAX requests
- Validate permissions with `current_user_can()`

## REST API Endpoints

- `POST /wp-blogcard/v1/fetch` - Fetch OGP data for a URL (requires `edit_posts` capability)
- `POST /wp-blogcard/v1/clear-cache` - Clear cache (requires `manage_options` capability)

## Release Process

This project uses [tagpr](https://github.com/Songmu/tagpr) for automated releases:

1. Push changes to `main` branch
2. tagpr creates a release PR with version bump
3. Merge the PR to create a release
4. GitHub Actions builds and attaches the plugin ZIP to the release
