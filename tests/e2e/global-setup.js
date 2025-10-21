/**
 * Global setup for Puppeteer e2e tests
 * Sets up WordPress test environment
 */

const { createURL } = require('@wordpress/e2e-test-utils');

module.exports = async () => {
    // Set up test environment variables
    process.env.WP_BASE_URL = process.env.WP_BASE_URL || 'https://wordpress.local';
    process.env.WP_ADMIN_USER = process.env.WP_ADMIN_USER || 'michaelamici';
    process.env.WP_ADMIN_PASSWORD = process.env.WP_ADMIN_PASSWORD || 'password';
    
    // Set up WordPress test environment
    console.log('Setting up WordPress test environment...');
    
    // Verify WordPress is accessible
    try {
        const response = await fetch(createURL('/'));
        if (!response.ok) {
            throw new Error(`WordPress not accessible: ${response.status}`);
        }
        console.log('WordPress test environment ready');
    } catch (error) {
        console.error('Failed to set up WordPress test environment:', error);
        throw error;
    }
};
