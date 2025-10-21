#!/bin/bash

# News Plugin Test Runner
# Runs all tests: unit, integration, and e2e

set -e

echo "🚀 Starting News Plugin Test Suite"
echo "=================================="

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if we're in the right directory
if [ ! -f "news.php" ]; then
    print_error "Please run this script from the plugin root directory"
    exit 1
fi

# Load environment variables
if [ -f "tests/.env" ]; then
    print_status "Loading environment variables from tests/.env"
    source tests/load-env.sh
else
    print_warning "No .env file found. Using default values."
fi

# Check if dependencies are installed
if [ ! -d "vendor" ]; then
    print_warning "Composer dependencies not found. Installing..."
    composer install
fi

if [ ! -d "node_modules" ]; then
    print_warning "NPM dependencies not found. Installing..."
    npm install
fi

# Run PHPUnit tests
print_status "Running PHPUnit unit and integration tests..."
if command -v phpunit &> /dev/null; then
    phpunit --configuration phpunit.xml
else
    vendor/bin/phpunit --configuration phpunit.xml
fi

if [ $? -eq 0 ]; then
    print_status "✅ PHPUnit tests passed"
else
    print_error "❌ PHPUnit tests failed"
    exit 1
fi

# Run Jest/Puppeteer tests
print_status "Running Jest/Puppeteer e2e tests..."
npm run test:puppeteer

if [ $? -eq 0 ]; then
    print_status "✅ Jest/Puppeteer tests passed"
else
    print_error "❌ Jest/Puppeteer tests failed"
    exit 1
fi

# Run Playwright tests (if available)
if [ -f "playwright.config.js" ]; then
    print_status "Running Playwright e2e tests..."
    npm run test:e2e
    
    if [ $? -eq 0 ]; then
        print_status "✅ Playwright tests passed"
    else
        print_error "❌ Playwright tests failed"
        exit 1
    fi
fi

print_status "🎉 All tests completed successfully!"
echo "=================================="
