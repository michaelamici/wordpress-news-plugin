#!/bin/bash

# Load environment variables from .env file
# Usage: source tests/load-env.sh

if [ -f "tests/.env" ]; then
    echo "Loading environment variables from tests/.env"
    export $(grep -v '^#' tests/.env | xargs)
    echo "Environment variables loaded:"
    echo "  WP_BASE_URL: $WP_BASE_URL"
    echo "  WP_ADMIN_USER: $WP_ADMIN_USER"
    echo "  DB_NAME: $DB_NAME"
    echo "  WP_TESTS_DIR: $WP_TESTS_DIR"
else
    echo "No .env file found. Please copy tests/env.example to tests/.env and configure it."
    exit 1
fi
