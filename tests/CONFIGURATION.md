# Test Environment Configuration

## Environment Variables Configured

Based on your WordPress installation at `/usr/share/webapps/dev/wordpress`, I've configured the following values:

### WordPress Configuration
- **Base URL**: `https://wordpress.local`
- **Admin User**: `michaelamici` (first admin user found)
- **Admin Password**: `password` (you may need to update this)

### Database Configuration
- **Database Name**: `wordpress_dev`
- **Database User**: `wordpress`
- **Database Password**: `wordpress123`
- **Database Host**: `localhost`

### PHPUnit Configuration
- **Tests Directory**: `/tmp/wordpress-tests-lib` (confirmed to exist)
- **Domain**: `wordpress.local`
- **Email**: `michaelamici@gmail.com`
- **Title**: `WordPress Dev`
- **PHP Binary**: `/usr/bin/php`

## Files Created/Updated

1. **`tests/.env`** - Environment variables file (copied from env.example)
2. **`tests/env.example`** - Template with correct values from your WordPress installation
3. **`tests/load-env.sh`** - Script to load environment variables
4. **`tests/run-tests.sh`** - Updated to load environment variables
5. **`tests/e2e/setup.js`** - Updated with correct default values
6. **`tests/e2e/global-setup.js`** - Updated with correct default values

## Usage

### Load Environment Variables
```bash
# From plugin root directory
source tests/load-env.sh
```

### Run Tests
```bash
# Run all tests with environment variables
./tests/run-tests.sh

# Or run individual test suites
npm run test:puppeteer    # Puppeteer e2e tests
npm run test:e2e          # Playwright e2e tests
vendor/bin/phpunit        # PHPUnit tests
```

## Important Notes

1. **Admin Password**: You may need to update the `WP_ADMIN_PASSWORD` in `tests/.env` with the actual password for the `michaelamici` user.

2. **SSL Certificate**: Since you're using `https://wordpress.local`, make sure your local SSL certificate is properly configured for the e2e tests.

3. **Database Access**: The tests will use the same database as your WordPress installation. Consider using a separate test database for production testing.

4. **WordPress Tests Library**: The tests library is already installed at `/tmp/wordpress-tests-lib` and ready to use.

## Verification

To verify the configuration is working:

```bash
# Test environment loading
source tests/load-env.sh
echo "WordPress URL: $WP_BASE_URL"
echo "Admin User: $WP_ADMIN_USER"
echo "Database: $DB_NAME"

# Test WordPress accessibility
curl -k $WP_BASE_URL

# Test database connection
mysql -u $DB_USER -p$DB_PASSWORD -D $DB_NAME -e "SELECT 1;"
```

## Next Steps

1. Update the admin password in `tests/.env` if needed
2. Run the test suite to verify everything works
3. Customize any additional test settings as needed
