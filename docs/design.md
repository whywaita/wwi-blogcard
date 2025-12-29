# WP Blogcard - Design Document

**Author:** whywaita
**Status:** Approved
**Last Updated:** 2024-12-29

## Overview

WP Blogcard is a WordPress Gutenberg block plugin that generates visually appealing blog cards from URLs by fetching OGP (Open Graph Protocol) information.

### Objective

Provide WordPress users with an easy way to embed rich link previews (blog cards) in their posts, similar to how social media platforms display link previews.

## Background

When sharing links in blog posts, plain URLs or simple text links don't provide readers with context about the linked content. Blog cards solve this by displaying:
- Title of the linked page
- Description/excerpt
- Featured image (OGP image)
- Site name and favicon

Many Japanese blogs use blog cards extensively, but existing WordPress solutions are either:
- Outdated (not compatible with Gutenberg block editor)
- Require external services
- Lack proper caching
- Have security vulnerabilities (SSRF)

## Goals and Non-Goals

### Goals

1. **Easy to use**: Users can create blog cards by simply pasting a URL
2. **Native Gutenberg integration**: Built as a modern WordPress block
3. **Performance**: Implement caching to reduce external requests
4. **Security**: Protect against SSRF attacks
5. **Responsive design**: Cards look good on all devices
6. **Accessibility**: Support dark mode and screen readers
7. **Minimal dependencies**: Use WordPress core APIs where possible

### Non-Goals

1. ~~Support for Classic Editor~~ (Gutenberg only)
2. ~~Custom card templates~~ (Single design, CSS customizable)
3. ~~Analytics/click tracking~~ (Privacy concerns)
4. ~~Shortcode support~~ (Block-only)

## Detailed Design

### Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                        WordPress Admin                          │
│  ┌───────────────────────────────────────────────────────────┐  │
│  │                    Gutenberg Editor                        │  │
│  │  ┌─────────────────────────────────────────────────────┐  │  │
│  │  │              Blogcard Block (React)                  │  │  │
│  │  │  - edit.js: Editor UI with URL input                │  │  │
│  │  │  - save.js: Frontend HTML output                    │  │  │
│  │  └─────────────────────────────────────────────────────┘  │  │
│  └───────────────────────────────────────────────────────────┘  │
│                              │                                   │
│                              ▼                                   │
│  ┌───────────────────────────────────────────────────────────┐  │
│  │                    REST API (PHP)                          │  │
│  │  POST /wp-blogcard/v1/fetch                               │  │
│  │  POST /wp-blogcard/v1/clear-cache                         │  │
│  └───────────────────────────────────────────────────────────┘  │
│                              │                                   │
│                              ▼                                   │
│  ┌───────────────────────────────────────────────────────────┐  │
│  │                   OGP Fetcher (PHP)                        │  │
│  │  - URL validation                                          │  │
│  │  - SSRF protection                                         │  │
│  │  - HTML fetching (wp_remote_get)                          │  │
│  │  - OGP parsing (DOMDocument + XPath)                      │  │
│  └───────────────────────────────────────────────────────────┘  │
│                              │                                   │
│                              ▼                                   │
│  ┌───────────────────────────────────────────────────────────┐  │
│  │                    Cache (PHP)                             │  │
│  │  - WordPress Transients API                               │  │
│  │  - 24-hour expiration                                      │  │
│  │  - Key: wp_blogcard_{md5(url)}                            │  │
│  └───────────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────┘
```

### Component Details

#### 1. PHP Backend

**File Structure:**
```
includes/
├── class-cache.php        # Cache management
├── class-ogp-fetcher.php  # OGP fetching and parsing
└── class-rest-api.php     # REST API endpoints
```

**WP_Blogcard_OGP_Fetcher:**
- Validates URL format using `filter_var()`
- Checks for private IPs (SSRF protection)
- Fetches HTML using `wp_remote_get()` with 10-second timeout
- Parses OGP meta tags using DOMDocument and XPath
- Falls back to Twitter Card meta tags, then standard meta tags
- Resolves relative URLs to absolute
- Provides fallback favicon via Google's favicon service

**WP_Blogcard_Cache:**
- Uses WordPress Transients API for storage
- Cache key format: `wp_blogcard_{md5(url)}`
- Default expiration: 24 hours (DAY_IN_SECONDS)
- Provides methods: `get()`, `set()`, `delete()`, `clear_all()`

**WP_Blogcard_REST_API:**
- Namespace: `wp-blogcard/v1`
- Endpoints:
  - `POST /fetch` - Fetch OGP data (requires `edit_posts` capability)
  - `POST /clear-cache` - Clear cache (requires `manage_options` capability)

#### 2. React Frontend

**File Structure:**
```
src/wp-blogcard/
├── block.json    # Block metadata (API v3)
├── index.js      # Block registration
├── edit.js       # Editor component
├── save.js       # Save component (frontend output)
├── style.scss    # Frontend styles
└── editor.scss   # Editor-only styles
```

**Block Attributes:**
```json
{
  "url": { "type": "string", "default": "" },
  "title": { "type": "string", "default": "" },
  "description": { "type": "string", "default": "" },
  "image": { "type": "string", "default": "" },
  "siteName": { "type": "string", "default": "" },
  "favicon": { "type": "string", "default": "" }
}
```

**Editor Component (edit.js):**
1. Shows placeholder with URL input when no data
2. Calls REST API on "Fetch" button click
3. Shows loading spinner during fetch
4. Displays error messages on failure
5. Shows preview when data is loaded
6. Provides "Change URL" button to reset

**Save Component (save.js):**
- Outputs static HTML (no JavaScript on frontend)
- Uses `loading="lazy"` for images
- Opens links in new tab with `rel="noopener noreferrer"`

#### 3. Styling

**CSS Custom Properties:**
```css
:root {
  --wp-blogcard-border-color: #e0e0e0;
  --wp-blogcard-background: #fff;
  --wp-blogcard-text-color: #333;
  --wp-blogcard-description-color: #666;
  --wp-blogcard-meta-color: #999;
  --wp-blogcard-hover-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  --wp-blogcard-border-radius: 8px;
  --wp-blogcard-image-size: 120px;
}
```

**Layout (Desktop):**
```
┌─────────────────────────────────────┐
│ Title                               │
│ Description...                      │
│ Description...                ┌────┐│
│ 🌐 site-name                  │IMG ││
└───────────────────────────────┴────┘
```
- Content on left, title at top
- Square OGP image (120x120px) at bottom-right
- Uses `flex-direction: row-reverse` with `align-items: flex-end`

**Layout (Mobile < 600px):**
```
┌─────────────────┐
│      IMAGE      │
├─────────────────┤
│ Title           │
│ Description...  │
│ 🌐 site-name    │
└─────────────────┘
```
- Vertical stack layout
- Full-width image on top

**Dark Mode:**
- Automatic via `@media (prefers-color-scheme: dark)`
- Adjusted colors for readability

### Data Flow

```
User enters URL
       │
       ▼
┌──────────────┐
│ edit.js      │ ──▶ POST /wp-blogcard/v1/fetch { url: "..." }
└──────────────┘
       │
       ▼
┌──────────────┐
│ REST API     │ ──▶ Permission check (edit_posts)
└──────────────┘
       │
       ▼
┌──────────────┐
│ Cache        │ ──▶ Check for cached data
└──────────────┘
       │ (cache miss)
       ▼
┌──────────────┐
│ OGP Fetcher  │ ──▶ Validate URL, check SSRF
└──────────────┘
       │
       ▼
┌──────────────┐
│ wp_remote_get│ ──▶ Fetch HTML from URL
└──────────────┘
       │
       ▼
┌──────────────┐
│ DOMDocument  │ ──▶ Parse OGP meta tags
└──────────────┘
       │
       ▼
┌──────────────┐
│ Cache        │ ──▶ Store result (24h TTL)
└──────────────┘
       │
       ▼
Return OGP data to editor
       │
       ▼
┌──────────────┐
│ edit.js      │ ──▶ Update block attributes
└──────────────┘
       │
       ▼
┌──────────────┐
│ save.js      │ ──▶ Generate static HTML
└──────────────┘
```

## Security Considerations

### SSRF Protection

Server-Side Request Forgery (SSRF) is prevented by:

1. **URL Validation**: Only valid URLs pass `filter_var($url, FILTER_VALIDATE_URL)`

2. **Private IP Blocking**: The following are blocked:
   - `localhost`, `127.0.0.1`, `::1`
   - Private ranges: `10.0.0.0/8`, `172.16.0.0/12`, `192.168.0.0/16`
   - Reserved ranges: Link-local, loopback, etc.

3. **DNS Resolution Check**: Hostname is resolved to IP before checking

```php
// Simplified SSRF check
public static function is_private_ip($url) {
    $host = parse_url($url, PHP_URL_HOST);
    $ip = gethostbyname($host);
    return filter_var($ip, FILTER_VALIDATE_IP,
        FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false;
}
```

### Input Validation & Output Escaping

- All user input is sanitized using WordPress functions
- Output is escaped with `esc_url()`, `esc_html()`, `esc_attr()`
- REST API parameters use `sanitize_callback` and `validate_callback`

### Permission Checks

- Fetch endpoint requires `edit_posts` capability
- Cache clear endpoint requires `manage_options` capability
- Uses WordPress REST API permission callbacks

## Alternatives Considered

### 1. Client-side OGP Fetching

**Rejected**: CORS restrictions prevent direct fetching from browser. Would require a proxy service.

### 2. oEmbed Integration

**Rejected**: oEmbed requires the target site to support it. OGP is more universally available.

### 3. External Service (Embedly, Iframely)

**Rejected**:
- Privacy concerns (third-party tracking)
- Cost for high-volume sites
- Dependency on external service availability

### 4. Store Full HTML in Block

**Rejected**: Would bloat post content. Current approach stores only essential attributes.

## Testing Plan

### Unit Tests (PHPUnit)

- `OGPFetcherTest`: OGP parsing, SSRF detection, fallback handling
- `CacheTest`: Cache key generation, get/set/delete operations

### Integration Tests

- REST API endpoint responses
- Permission checks
- Error handling

### E2E Tests (Playwright)

- Block can be added to post
- URL input and fetch workflow
- Preview display
- Published post rendering

### Manual Testing

- Various URLs (with/without OGP, various encodings)
- Dark mode appearance
- Mobile responsiveness
- RTL language support

## Open Questions

1. **Image proxy**: Should we proxy OGP images to avoid mixed content issues?
   - **Decision**: No, keep it simple. Let WordPress handle via HTTPS.

2. **Cache invalidation UI**: Should we add admin UI to clear cache?
   - **Decision**: Defer to future version. REST API endpoint is sufficient for now.

3. **Multiple card styles**: Should we support different visual styles?
   - **Decision**: Defer. Single style with CSS custom properties for theming.

## References

- [Open Graph Protocol](https://ogp.me/)
- [WordPress Block Editor Handbook](https://developer.wordpress.org/block-editor/)
- [WordPress REST API Handbook](https://developer.wordpress.org/rest-api/)
- [WordPress Transients API](https://developer.wordpress.org/plugins/transients/)
