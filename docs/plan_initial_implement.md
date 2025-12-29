# WP Blogcard - Initial Implementation Plan

## Phase 1: Project Setup

- [x] Initialize npm project with @wordpress/scripts
- [x] Set up composer for PHP dependencies
- [x] Configure linting (ESLint, Stylelint, PHPCS)
- [x] Set up Docker development environment
- [x] Create basic plugin structure

## Phase 2: Backend Implementation

- [x] Create OGP fetcher class
  - [x] URL validation
  - [x] SSRF protection
  - [x] HTML parsing with DOMDocument
  - [x] OGP tag extraction
  - [x] Fallback to standard meta tags
- [x] Create cache class
  - [x] Transients API integration
  - [x] Cache key generation
  - [x] Cache expiration (24 hours)
- [x] Create REST API class
  - [x] Fetch endpoint
  - [x] Clear cache endpoint
  - [x] Permission checks

## Phase 3: Frontend Implementation

- [x] Create block.json metadata
- [x] Create editor component
  - [x] URL input placeholder
  - [x] Fetch button with loading state
  - [x] Error handling
  - [x] Preview display
- [x] Create save component
  - [x] HTML output structure
  - [x] Lazy loading for images
- [x] Create styles
  - [x] BEM naming convention
  - [x] CSS custom properties
  - [x] Dark mode support
  - [x] Responsive design

## Phase 4: Testing

- [x] PHPUnit setup
- [ ] Unit tests for OGP fetcher
- [ ] Unit tests for cache class
- [ ] Integration tests for REST API
- [x] Playwright E2E setup
- [ ] E2E tests for block editor

## Phase 5: Documentation & Release

- [x] Create readme.txt
- [x] Create design documentation
- [x] Set up GitHub Actions CI
- [x] Set up tagpr for releases
- [x] Create AGENTS.md
- [ ] Submit to WordPress.org plugin directory

## Future Enhancements

- [ ] Block settings sidebar
  - [ ] Custom image size
  - [ ] Show/hide description
  - [ ] Custom link target
- [ ] Multiple card styles
- [ ] Bulk URL import
- [ ] Cache management UI in admin
