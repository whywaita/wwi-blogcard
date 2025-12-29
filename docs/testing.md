# WP Blogcard - Testing Guide

## Testing Methodology: t_wada Style

This project adopts the **t_wada style** testing methodology, named after Takuto Wada (和田卓人), a renowned Japanese software engineer and testing advocate.

### Core Principles

#### 1. Arrange-Act-Assert (AAA) Pattern

Every test follows the AAA pattern (also known as Given-When-Then):

```php
/**
 * @test
 * OGPタグからタイトルを正しく抽出できる
 */
public function parse_ogp_extracts_title_from_og_title_tag() {
    // Arrange (Given): Set up test data and preconditions
    $html = $this->create_html_with_ogp( array( 'og:title' => 'Test Title' ) );
    $url  = 'https://example.com/page';

    // Act (When): Execute the code under test
    $result = WP_Blogcard_OGP_Fetcher::parse_ogp( $html, $url );

    // Assert (Then): Verify the expected outcome
    $this->assertSame( 'Test Title', $result['title'] );
}
```

#### 2. One Logical Assertion Per Test

Each test focuses on verifying a single behavior. This makes tests:
- Easier to understand
- Easier to maintain
- More precise in failure diagnosis

```php
// Good: Single focused assertion
public function get_cache_key_includes_prefix() {
    $cache_key = WP_Blogcard_Cache::get_cache_key( 'https://example.com' );
    $this->assertStringStartsWith( 'wp_blogcard_', $cache_key );
}

// Avoid: Multiple unrelated assertions
public function test_cache_key() {
    $key = WP_Blogcard_Cache::get_cache_key( 'https://example.com' );
    $this->assertStringStartsWith( 'wp_blogcard_', $key );  // Different concern
    $this->assertEquals( 44, strlen( $key ) );              // Different concern
    $this->assertNotEmpty( $key );                          // Different concern
}
```

#### 3. Descriptive Test Names

Test names should describe the behavior being tested in natural language:

```php
// Good: Describes behavior
public function is_private_ip_returns_true_for_localhost()
public function parse_ogp_falls_back_to_title_tag_when_og_title_missing()
public function validate_url_returns_false_for_javascript_scheme()

// Avoid: Vague names
public function test_private_ip()
public function test_parse_ogp()
public function test_validation()
```

#### 4. Tests as Documentation

Tests serve as living documentation of the system's behavior. Reading tests should explain:
- What the code does
- How it handles edge cases
- What the expected behavior is

### Test Structure

```
tests/
├── bootstrap.php           # Test bootstrap file
├── unit/                   # Unit tests (isolated, no external dependencies)
│   ├── CacheTest.php       # Cache class tests
│   └── OGPFetcherTest.php  # OGP fetcher tests
└── integration/            # Integration tests (component interactions)
    └── RestApiTest.php     # REST API tests
```

### Test Categories

#### Unit Tests

- Test individual classes/methods in isolation
- No external dependencies (database, network, etc.)
- Fast execution
- Located in `tests/unit/`

```bash
# Run unit tests only
./vendor/bin/phpunit --testsuite unit
```

#### Integration Tests

- Test interactions between components
- May use mocked WordPress functions
- Located in `tests/integration/`

```bash
# Run integration tests only
./vendor/bin/phpunit --testsuite integration
```

#### E2E Tests

- Test the full application flow
- Use Playwright for browser automation
- Located in `e2e/`

```bash
# Run E2E tests
npm run test:e2e
```

### Running Tests

```bash
# Run all PHP tests
make test

# Run all tests with coverage
./vendor/bin/phpunit --coverage-html coverage/

# Run specific test file
./vendor/bin/phpunit tests/unit/CacheTest.php

# Run tests matching a pattern
./vendor/bin/phpunit --filter "cache"
```

### Writing New Tests

1. **Identify the behavior** you want to test
2. **Write the test name** as a sentence describing the behavior
3. **Write the test** following AAA pattern
4. **Run the test** to ensure it fails first (TDD red phase)
5. **Implement the code** to make the test pass (TDD green phase)
6. **Refactor** if needed while keeping tests green

### Test Annotations

Use `@test` annotation for better readability:

```php
/**
 * @test
 * 日本語でテストの目的を説明する
 */
public function descriptive_method_name_in_english() {
    // ...
}
```

### References

- [t-wada/clean-code-and-testing](https://github.com/t-wada/clean-code-and-testing)
- [The Art of Unit Testing](https://www.manning.com/books/the-art-of-unit-testing-second-edition)
- [xUnit Test Patterns](http://xunitpatterns.com/)
