# News Plugin Testing

This directory contains comprehensive tests for the News Plugin, following WordPress testing standards and best practices.

## Test Structure

### Unit Tests (`tests/unit/`)
- **Purpose**: Test individual components in isolation
- **Scope**: Classes, methods, and functions
- **Dependencies**: Minimal, mocked where necessary
- **Examples**: 
  - `test-news-post-type.php` - Tests NewsPostType class
  - `test-news-section.php` - Tests NewsSection taxonomy

### Integration Tests (`tests/integration/`)
- **Purpose**: Test component interactions and WordPress integration
- **Scope**: WordPress hooks, filters, REST API, database operations
- **Dependencies**: WordPress environment, database
- **Examples**:
  - `test-fronts.php` - Tests Fronts system integration
  - `test-rest-api.php` - Tests REST API endpoints

### Smoke Tests (`tests/smoke/`)
- **Purpose**: Test critical user flows end-to-end
- **Scope**: Complete workflows, user scenarios
- **Dependencies**: Full WordPress environment
- **Examples**:
  - `test-critical-flows.php` - Tests complete user workflows

## Test Utilities

### `test-utils.php`
Provides helper functions for test setup:
- `create_news_article()` - Create test news articles
- `create_news_section()` - Create test sections
- `create_test_user()` - Create test users with capabilities
- `cleanup_test_data()` - Clean up test data
- `assert_post_meta()` - Assert post meta values
- `assert_term_meta()` - Assert term meta values

## Running Tests

### Prerequisites
1. Install WordPress test environment:
   ```bash
   bash tests/bin/install-wp-tests.sh wordpress_test root '' localhost latest
   ```

2. Install dependencies:
   ```bash
   composer install
   ```

### Test Commands
```bash
# Run all tests
composer test

# Run specific test suites
composer test-unit
composer test-integration
composer test-smoke

# Run with coverage
composer test-coverage

# Run specific test file
vendor/bin/phpunit tests/unit/test-news-post-type.php
```

### Environment Variables
- `WP_TESTS_DIR` - WordPress test directory
- `WP_CORE_DIR` - WordPress core directory
- `WP_TESTS_PHPUNIT_POLYFILLS_PATH` - PHPUnit polyfills path

## Test Configuration

### `phpunit.xml`
- Test suite definitions
- Coverage configuration
- Logging configuration
- PHP settings

### `composer.json`
- Test dependencies
- Scripts for running tests
- Autoloading configuration

## Coverage Reports

Coverage reports are generated in `tests/coverage/`:
- HTML: `tests/coverage/html/index.html`
- Text: `tests/coverage/coverage.txt`
- Clover: `tests/coverage/clover.xml`

## Continuous Integration

GitHub Actions workflow (`.github/workflows/tests.yml`):
- Tests on PHP 8.1, 8.2, 8.3
- Tests on WordPress latest, 6.4, 6.5
- Generates coverage reports
- Uploads to Codecov

## Test Standards

### WordPress Standards
- Follow WordPress coding standards
- Use WordPress test functions
- Proper cleanup of test data
- Test WordPress hooks and filters

### PHPUnit Standards
- Use descriptive test method names
- One assertion per test when possible
- Proper setup and teardown
- Mock external dependencies

### Coverage Goals
- **Unit Tests**: 80%+ coverage
- **Integration Tests**: Critical paths covered
- **Smoke Tests**: All user flows covered

## Best Practices

### Test Data
- Use factory methods for test data creation
- Clean up test data after each test
- Use unique identifiers to avoid conflicts
- Test with realistic data

### Assertions
- Use specific assertions (`assertEquals` vs `assertTrue`)
- Test both positive and negative cases
- Test edge cases and error conditions
- Verify side effects

### Performance
- Keep tests fast and focused
- Use database transactions where possible
- Mock expensive operations
- Test performance-critical code

## Troubleshooting

### Common Issues
1. **Database connection errors**: Check test database setup
2. **WordPress not loaded**: Verify `WP_TESTS_DIR` and `WP_CORE_DIR`
3. **Plugin not loaded**: Check plugin activation in bootstrap
4. **Memory issues**: Increase PHP memory limit in phpunit.xml

### Debug Mode
Enable debug mode in `phpunit.xml`:
```xml
<php>
    <ini name="error_reporting" value="E_ALL"/>
    <ini name="display_errors" value="1"/>
    <ini name="log_errors" value="1"/>
</php>
```

### Verbose Output
Run tests with verbose output:
```bash
vendor/bin/phpunit --verbose
```

## Contributing

When adding new tests:
1. Follow existing naming conventions
2. Add appropriate test categories
3. Update this README if needed
4. Ensure tests pass in CI
5. Add coverage for new code
