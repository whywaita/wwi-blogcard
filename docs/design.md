# WP Blogcard - Design Document

## Overview

WP Blogcard is a WordPress Gutenberg block plugin that generates beautiful blog cards from URLs by fetching OGP (Open Graph Protocol) information.

## Architecture

### Components

1. **PHP Backend**
   - `WP_Blogcard_OGP_Fetcher`: Fetches and parses OGP data from URLs
   - `WP_Blogcard_Cache`: Manages caching using WordPress Transients API
   - `WP_Blogcard_REST_API`: Provides REST API endpoints for the block

2. **React Frontend**
   - `edit.js`: Editor component with URL input and preview
   - `save.js`: Frontend output component
   - `style.scss`: Frontend styles with dark mode support
   - `editor.scss`: Editor-specific styles

### Data Flow

1. User enters URL in block editor
2. Editor calls REST API (`/wp-blogcard/v1/fetch`)
3. Backend checks cache for existing data
4. If not cached, fetches URL and parses OGP tags
5. Returns data to editor and caches for future use
6. Block renders preview and saves output

## Security Measures

### SSRF Protection

The OGP fetcher includes protection against Server-Side Request Forgery (SSRF):

- Blocks requests to localhost (`127.0.0.1`, `::1`, `localhost`)
- Blocks requests to private IP ranges (`10.x.x.x`, `172.16-31.x.x`, `192.168.x.x`)
- Blocks requests to reserved IP ranges

### Input Validation

- URL validation using `filter_var()` with `FILTER_VALIDATE_URL`
- All output is sanitized using WordPress sanitization functions
- REST API requires `edit_posts` capability

## Caching Strategy

- Uses WordPress Transients API
- Cache duration: 24 hours
- Cache key: MD5 hash of URL with prefix `wp_blogcard_`
- Cache can be cleared via REST API (admin only)

## Styling

### CSS Custom Properties

The plugin uses CSS custom properties for easy theming:

- `--wp-blogcard-border-color`
- `--wp-blogcard-background`
- `--wp-blogcard-text-color`
- `--wp-blogcard-description-color`
- `--wp-blogcard-meta-color`
- `--wp-blogcard-hover-shadow`
- `--wp-blogcard-border-radius`
- `--wp-blogcard-image-size`

### Dark Mode

Automatic dark mode support using `prefers-color-scheme: dark` media query.

### Responsive Design

- Desktop: Horizontal layout with content on left, square image on bottom-right
- Mobile (< 600px): Vertical layout with image on top
