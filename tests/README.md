# News Plugin Testing Suite

This directory contains comprehensive tests for the News Plugin, including unit tests, integration tests, and end-to-end tests using Puppeteer as recommended by WordPress.

## Test Structure

```
tests/
├── Unit/                    # PHPUnit unit tests
│   └── Blocks/
│       └── BlockManagerTest.php
├── Integration/             # PHPUnit integration tests
│   └── Blocks/
│       └── BlockManagerIntegrationTest.php
├── e2e/                    # End-to-end tests with Puppeteer
│   ├── blocks.test.js
│   ├── block-functionality.test.js
│   ├── setup.js
│   ├── global-setup.js
│   ├── global-teardown.js
│   └── puppeteer.config.js
├── bootstrap.php           # PHPUnit bootstrap
├── run-tests.sh            # Test runner script
└── README.md              # This file
```

## Test Types

### 1. Unit Tests (PHPUnit)
- **Location**: `tests/Unit/`
- **Purpose**: Test individual methods and classes in isolation
- **Framework**: PHPUnit with Brain Monkey for WordPress function mocking
- **Coverage**: BlockManager class methods, block registration, rendering functions

### 2. Integration Tests (PHPUnit)
- **Location**: `tests/Integration/`
- **Purpose**: Test component interactions with WordPress core
- **Framework**: PHPUnit with WP_UnitTestCase
- **Coverage**: Block registration with WordPress, database interactions, post/term creation

### 3. End-to-End Tests (Puppeteer)
- **Location**: `tests/e2e/`
- **Purpose**: Test complete user workflows in the browser
- **Framework**: Puppeteer with Jest
- **Coverage**: Block editor interactions, block insertion, settings panels, frontend rendering

## Running Tests

### Prerequisites

1. **WordPress Test Environment**: Ensure WordPress is running and accessible
2. **Dependencies**: Install PHP and Node.js dependencies
3. **Test Database**: Set up a test database for WordPress

### Quick Start

```bash
# Run all tests
./tests/run-tests.sh

# Or run individual test suites
npm run test:puppeteer    # Puppeteer e2e tests
npm run test:e2e          # Playwright e2e tests
vendor/bin/phpunit        # PHPUnit tests
```

### Individual Test Commands

#### PHPUnit Tests
```bash
# Run unit tests only
vendor/bin/phpunit tests/Unit/

# Run integration tests only
vendor/bin/phpunit tests/Integration/

# Run with coverage
vendor/bin/phpunit --coverage-html coverage/
```

#### Puppeteer Tests
```bash
# Run Puppeteer tests
npm run test:puppeteer

# Run with debug mode (shows browser)
HEADLESS=false npm run test:puppeteer

# Run specific test file
npm run test:puppeteer -- tests/e2e/blocks.test.js
```

#### Playwright Tests
```bash
# Run Playwright tests
npm run test:e2e

# Run with UI mode
npm run test:e2e:ui

# Run specific test file
npm run test:e2e -- tests/e2e/blocks.test.js
```

## Test Configuration

### PHPUnit Configuration
- **File**: `phpunit.xml`
- **Bootstrap**: `tests/bootstrap.php`
- **Coverage**: HTML and text reports in `coverage/` directory

### Puppeteer Configuration
- **File**: `tests/e2e/puppeteer.config.js`
- **Setup**: `tests/e2e/setup.js`
- **Global Setup**: `tests/e2e/global-setup.js`

### Environment Variables
```bash
# WordPress test environment
WP_BASE_URL=http://localhost:8888
WP_ADMIN_USER=admin
WP_ADMIN_PASSWORD=password

# Test configuration
CI=true                    # Run in headless mode
HEADLESS=false            # Show browser during tests
```

## Test Coverage

### BlockManager Class
- ✅ Constructor and initialization
- ✅ Block registration methods
- ✅ Block rendering methods
- ✅ Asset enqueuing
- ✅ Block category registration
- ✅ Error handling

### Block Functionality
- ✅ News Article Block
- ✅ News Section Block
- ✅ Breaking News Block
- ✅ News Grid Block
- ✅ Block settings and attributes
- ✅ Frontend rendering

### Editor Integration
- ✅ Block insertion
- ✅ Block selection and editing
- ✅ Settings panels
- ✅ Block toolbar
- ✅ Keyboard navigation
- ✅ Accessibility

## Writing New Tests

### Unit Tests
```php
<?php
namespace NewsPlugin\Tests\Unit\Blocks;

use NewsPlugin\Blocks\BlockManager;
use PHPUnit\Framework\TestCase;
use Brain\Monkey;

class MyBlockTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();
    }

    public function testMyMethod(): void
    {
        // Test implementation
    }
}
```

### Integration Tests
```php
<?php
namespace NewsPlugin\Tests\Integration\Blocks;

use NewsPlugin\Blocks\BlockManager;
use WP_UnitTestCase;

class MyIntegrationTest extends WP_UnitTestCase
{
    public function testMyIntegration(): void
    {
        // Test with WordPress
    }
}
```

### E2E Tests
```javascript
describe('My Block Test', () => {
    let browser;
    let page;

    beforeAll(async () => {
        browser = await puppeteer.launch({
            headless: process.env.CI === 'true'
        });
    });

    test('should do something', async () => {
        page = await browser.newPage();
        await page.goto(createURL('/wp-admin/post-new.php'));
        // Test implementation
    });
});
```

## Debugging Tests

### PHPUnit Debugging
```bash
# Run with verbose output
vendor/bin/phpunit --verbose

# Run specific test
vendor/bin/phpunit --filter testMyMethod

# Run with debug output
vendor/bin/phpunit --debug
```

### Puppeteer Debugging
```bash
# Run with browser visible
HEADLESS=false npm run test:puppeteer

# Run with slow motion
SLOW_MO=100 npm run test:puppeteer

# Run specific test
npm run test:puppeteer -- --testNamePattern="should do something"
```

### Common Issues

1. **WordPress not accessible**: Check `WP_BASE_URL` environment variable
2. **Database connection**: Ensure test database is properly configured
3. **Dependencies**: Run `composer install` and `npm install`
4. **Browser issues**: Check Puppeteer installation and Chrome/Chromium

## Continuous Integration

The test suite is designed to work with CI/CD pipelines:

```yaml
# Example GitHub Actions workflow
- name: Run Tests
  run: |
    composer install
    npm install
    ./tests/run-tests.sh
```

## Best Practices

1. **Test Isolation**: Each test should be independent
2. **Mock External Dependencies**: Use mocks for WordPress functions
3. **Realistic Test Data**: Create meaningful test content
4. **Error Scenarios**: Test both success and failure cases
5. **Performance**: Keep tests fast and efficient
6. **Documentation**: Document complex test scenarios

## Contributing

When adding new tests:

1. Follow the existing test structure
2. Use descriptive test names
3. Add appropriate assertions
4. Update this README if needed
5. Ensure tests pass in CI environment

## Resources

- [WordPress Testing Documentation](https://developer.wordpress.org/block-editor/explanations/architecture/automated-testing/)
- [PHPUnit Documentation](https://phpunit.readthedocs.io/)
- [Puppeteer Documentation](https://pptr.dev/)
- [WordPress E2E Test Utils](https://github.com/WordPress/gutenberg/tree/trunk/packages/e2e-test-utils)
