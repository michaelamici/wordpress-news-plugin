/**
 * Setup file for Puppeteer e2e tests
 * Based on WordPress Gutenberg testing practices
 */

const { createURL } = require('@wordpress/e2e-test-utils');

// Set up test environment
beforeAll(async () => {
    // Set up WordPress test environment
    process.env.WP_BASE_URL = process.env.WP_BASE_URL || 'https://wordpress.local';
    process.env.WP_ADMIN_USER = process.env.WP_ADMIN_USER || 'michaelamici';
    process.env.WP_ADMIN_PASSWORD = process.env.WP_ADMIN_PASSWORD || 'password';
});

// Global test utilities
global.createURL = createURL;

// Helper function to wait for blocks to load
global.waitForBlocks = async (page) => {
    await page.waitForSelector('.wp-block', { timeout: 10000 });
};

// Helper function to create a new post with blocks
global.createPostWithBlocks = async (page, blocks = []) => {
    await page.goto(createURL('/wp-admin/post-new.php?post_type=news'));
    
    // Wait for editor to load
    await page.waitForSelector('.editor-post-title__input');
    
    // Add title
    await page.type('.editor-post-title__input', 'Test News Article');
    
    // Add blocks
    for (const block of blocks) {
        await page.click('.block-editor-inserter__toggle');
        await page.waitForSelector('.block-editor-inserter__menu');
        await page.type('.block-editor-inserter__search', block);
        await page.click(`[data-type="news/${block}"]`);
    }
    
    return page;
};

// Helper function to save post
global.savePost = async (page) => {
    await page.click('.editor-post-publish-button');
    await page.waitForSelector('.editor-post-publish-panel__toggle');
    await page.click('.editor-post-publish-panel__toggle');
    await page.click('.editor-post-publish-button');
    await page.waitForSelector('.editor-post-publish-panel__content');
};

// Helper function to preview post
global.previewPost = async (page) => {
    await page.click('.editor-post-preview');
    await page.waitForSelector('.wp-block');
};
