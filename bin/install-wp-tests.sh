#!/usr/bin/env bash

if [ $# -lt 3 ]; then
    echo "usage: $0 <db-name> <db-user> <db-pass> [db-host] [wp-version]"
    exit 1
fi

DB_NAME=$1
DB_USER=$2
DB_PASS=$3
DB_HOST=${4-localhost}
WP_VERSION=${5-latest}

WP_TESTS_DIR=${WP_TESTS_DIR-/tmp/wordpress-tests-lib}
WP_CORE_DIR=${WP_CORE_DIR-/tmp/wordpress/}

set -ex

# Create directories
mkdir -p $WP_CORE_DIR
mkdir -p $WP_TESTS_DIR

# Download WordPress
if [[ $WP_VERSION == 'latest' ]]; then
    wget -O /tmp/wordpress.tar.gz https://wordpress.org/latest.tar.gz
else
    wget -O /tmp/wordpress.tar.gz https://wordpress.org/wordpress-$WP_VERSION.tar.gz
fi

# Extract WordPress
tar --strip-components=1 -zxf /tmp/wordpress.tar.gz -C $WP_CORE_DIR

# Download WordPress test suite
svn co --quiet https://develop.svn.wordpress.org/trunk/tests/phpunit/includes/ $WP_TESTS_DIR/includes
svn co --quiet https://develop.svn.wordpress.org/trunk/tests/phpunit/data/ $WP_TESTS_DIR/data

# Create wp-tests-config.php
cat > $WP_TESTS_DIR/wp-tests-config.php << 'CONFIG'
<?php
define("DB_NAME", "$DB_NAME");
define("DB_USER", "$DB_USER");
define("DB_PASS", "$DB_PASS");
define("DB_HOST", "$DB_HOST");
define("WP_TESTS_DOMAIN", "example.org");
define("WP_TESTS_EMAIL", "admin@example.org");
define("WP_TESTS_TITLE", "Test Blog");
define("WP_PHP_BINARY", "php");
define("WP_TESTS_FORCE_KNOWN_BUGS", true);
define("WP_TESTS_DIR", "$WP_TESTS_DIR");
define("WP_CORE_DIR", "$WP_CORE_DIR");
CONFIG

# Create test database
mysqladmin create $DB_NAME --user="$DB_USER" --password="$DB_PASS" --host="$DB_HOST"

echo "WordPress test suite installed successfully!"
