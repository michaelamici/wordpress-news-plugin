/**
 * End-to-end tests for News Plugin byline block using Puppeteer
 * Based on WordPress Gutenberg testing practices
 */

const puppeteer = require('puppeteer');
const { createURL } = require('@wordpress/e2e-test-utils');

describe('News Plugin Byline Block', () => {
    let browser;
    let page;

    beforeAll(async () => {
        browser = await puppeteer.launch({
            headless: process.env.CI === 'true',
            slowMo: 50,
            args: [
                '--no-sandbox',
                '--disable-setuid-sandbox',
                '--disable-dev-shm-usage',
                '--disable-accelerated-2d-canvas',
                '--no-first-run',
                '--no-zygote',
                '--single-process',
                '--disable-gpu'
            ]
        });
    });

    afterAll(async () => {
        if (browser) {
            await browser.close();
        }
    });

    beforeEach(async () => {
        page = await browser.newPage();
        await page.setViewport({ width: 1280, height: 720 });
        
        // Set up console logging for debugging
        page.on('console', msg => {
            if (msg.type() === 'error') {
                console.error('Browser console error:', msg.text());
            }
        });
    });

    afterEach(async () => {
        if (page) {
            await page.close();
        }
    });

    describe('Block Registration', () => {
        test('should register news byline block in the editor', async () => {
            await page.goto(createURL('/wp-admin/post-new.php?post_type=news'));
            
            // Wait for editor to load
            await page.waitForSelector('.editor-post-title__input');
            
            // Open block inserter
            await page.click('.block-editor-inserter__toggle');
            await page.waitForSelector('.block-editor-inserter__menu');
            
            // Check for news category
            const newsCategory = await page.$('[data-category="news"]');
            expect(newsCategory).toBeTruthy();
            
            // Check for byline block
            const bylineBlock = await page.$('[data-type="news/post-byline"]');
            expect(bylineBlock).toBeTruthy();
        });

        test('should display byline block title correctly', async () => {
            await page.goto(createURL('/wp-admin/post-new.php?post_type=news'));
            await page.waitForSelector('.editor-post-title__input');
            
            await page.click('.block-editor-inserter__toggle');
            await page.waitForSelector('.block-editor-inserter__menu');
            
            // Check block title
            const bylineBlock = await page.$('[data-type="news/post-byline"]');
            const blockTitle = await page.evaluate(el => el.textContent, bylineBlock);
            expect(blockTitle).toContain('Post Byline');
        });
    });

    describe('Query Loop Context', () => {
        test('should only be available within Query Loop', async () => {
            await page.goto(createURL('/wp-admin/post-new.php?post_type=news'));
            await page.waitForSelector('.editor-post-title__input');
            
            // Open block inserter
            await page.click('.block-editor-inserter__toggle');
            await page.waitForSelector('.block-editor-inserter__menu');
            
            // Search for byline block
            await page.type('.block-editor-inserter__search', 'byline');
            
            // The block should not be available outside Query Loop
            const bylineBlock = await page.$('[data-type="news/post-byline"]');
            expect(bylineBlock).toBeFalsy();
        });

        test('should be available within Query Loop', async () => {
            await page.goto(createURL('/wp-admin/post-new.php?post_type=page'));
            await page.waitForSelector('.editor-post-title__input');
            
            // Add Query Loop block
            await page.click('.block-editor-inserter__toggle');
            await page.waitForSelector('.block-editor-inserter__menu');
            await page.type('.block-editor-inserter__search', 'query loop');
            await page.click('[data-type="core/query"]');
            
            await page.waitForSelector('.wp-block-query');
            
            // Click on Post Template within Query Loop
            await page.click('.wp-block-post-template');
            
            // Now search for byline block
            await page.click('.block-editor-inserter__toggle');
            await page.waitForSelector('.block-editor-inserter__menu');
            await page.type('.block-editor-inserter__search', 'byline');
            
            // The block should now be available
            const bylineBlock = await page.$('[data-type="news/post-byline"]');
            expect(bylineBlock).toBeTruthy();
        });
    });

    describe('Byline Block Functionality', () => {
        test('should insert byline block within Query Loop', async () => {
            await page.goto(createURL('/wp-admin/post-new.php?post_type=page'));
            await page.waitForSelector('.editor-post-title__input');
            
            // Add Query Loop block
            await page.click('.block-editor-inserter__toggle');
            await page.waitForSelector('.block-editor-inserter__menu');
            await page.type('.block-editor-inserter__search', 'query loop');
            await page.click('[data-type="core/query"]');
            
            await page.waitForSelector('.wp-block-query');
            
            // Click on Post Template
            await page.click('.wp-block-post-template');
            
            // Insert byline block
            await page.click('.block-editor-inserter__toggle');
            await page.waitForSelector('.block-editor-inserter__menu');
            await page.type('.block-editor-inserter__search', 'byline');
            await page.click('[data-type="news/post-byline"]');
            
            // Wait for block to be inserted
            await page.waitForSelector('.wp-block-news-post-byline');
            
            // Check block is present
            const bylineBlock = await page.$('.wp-block-news-post-byline');
            expect(bylineBlock).toBeTruthy();
        });

        test('should show placeholder when no byline data', async () => {
            await page.goto(createURL('/wp-admin/post-new.php?post_type=page'));
            await page.waitForSelector('.editor-post-title__input');
            
            // Add Query Loop block
            await page.click('.block-editor-inserter__toggle');
            await page.waitForSelector('.block-editor-inserter__menu');
            await page.type('.block-editor-inserter__search', 'query loop');
            await page.click('[data-type="core/query"]');
            
            await page.waitForSelector('.wp-block-query');
            
            // Click on Post Template
            await page.click('.wp-block-post-template');
            
            // Insert byline block
            await page.click('.block-editor-inserter__toggle');
            await page.waitForSelector('.block-editor-inserter__menu');
            await page.type('.block-editor-inserter__search', 'byline');
            await page.click('[data-type="news/post-byline"]');
            
            await page.waitForSelector('.wp-block-news-post-byline');
            
            // Check for placeholder (should be empty since no byline data)
            const blockContent = await page.evaluate(() => {
                const block = document.querySelector('.wp-block-news-post-byline');
                return block ? block.textContent.trim() : '';
            });
            
            expect(blockContent).toBe('');
        });
    });

    describe('Block Rendering', () => {
        test('should render byline block correctly on frontend', async () => {
            // First create a news post with byline data
            await page.goto(createURL('/wp-admin/post-new.php?post_type=news'));
            await page.waitForSelector('.editor-post-title__input');
            
            // Add title
            await page.type('.editor-post-title__input', 'Test News Post');
            
            // Add byline meta field (this would be done via custom fields or meta box)
            // For testing, we'll simulate this by adding the meta field directly
            
            // Save the post
            await page.click('.editor-post-publish-button');
            await page.waitForSelector('.editor-post-publish-panel__toggle');
            await page.click('.editor-post-publish-panel__toggle');
            await page.click('.editor-post-publish-button');
            
            // Wait for post to be published
            await page.waitForSelector('.editor-post-publish-panel__content');
            
            // Now create a page with Query Loop
            await page.goto(createURL('/wp-admin/post-new.php?post_type=page'));
            await page.waitForSelector('.editor-post-title__input');
            
            // Add Query Loop block
            await page.click('.block-editor-inserter__toggle');
            await page.waitForSelector('.block-editor-inserter__menu');
            await page.type('.block-editor-inserter__search', 'query loop');
            await page.click('[data-type="core/query"]');
            
            await page.waitForSelector('.wp-block-query');
            
            // Click on Post Template
            await page.click('.wp-block-post-template');
            
            // Insert byline block
            await page.click('.block-editor-inserter__toggle');
            await page.waitForSelector('.block-editor-inserter__menu');
            await page.type('.block-editor-inserter__search', 'byline');
            await page.click('[data-type="news/post-byline"]');
            
            await page.waitForSelector('.wp-block-news-post-byline');
            
            // Save the page
            await page.click('.editor-post-publish-button');
            await page.waitForSelector('.editor-post-publish-panel__toggle');
            await page.click('.editor-post-publish-panel__toggle');
            await page.click('.editor-post-publish-button');
            
            // Wait for page to be published
            await page.waitForSelector('.editor-post-publish-panel__content');
            
            // Preview the page
            await page.click('.editor-post-preview');
            await page.waitForSelector('.wp-block');
            
            // Check if byline block is rendered
            const bylineBlocks = await page.$$('.wp-block-news-post-byline');
            expect(bylineBlocks.length).toBeGreaterThan(0);
        });
    });

    describe('Block Interactions', () => {
        test('should allow block selection and editing', async () => {
            await page.goto(createURL('/wp-admin/post-new.php?post_type=page'));
            await page.waitForSelector('.editor-post-title__input');
            
            // Add Query Loop block
            await page.click('.block-editor-inserter__toggle');
            await page.waitForSelector('.block-editor-inserter__menu');
            await page.type('.block-editor-inserter__search', 'query loop');
            await page.click('[data-type="core/query"]');
            
            await page.waitForSelector('.wp-block-query');
            
            // Click on Post Template
            await page.click('.wp-block-post-template');
            
            // Insert byline block
            await page.click('.block-editor-inserter__toggle');
            await page.waitForSelector('.block-editor-inserter__menu');
            await page.type('.block-editor-inserter__search', 'byline');
            await page.click('[data-type="news/post-byline"]');
            
            await page.waitForSelector('.wp-block-news-post-byline');
            
            // Click on the block to select it
            await page.click('.wp-block-news-post-byline');
            
            // Check if block is selected
            const selectedBlock = await page.$('.wp-block-news-post-byline.is-selected');
            expect(selectedBlock).toBeTruthy();
        });

        test('should show block toolbar when selected', async () => {
            await page.goto(createURL('/wp-admin/post-new.php?post_type=page'));
            await page.waitForSelector('.editor-post-title__input');
            
            // Add Query Loop block
            await page.click('.block-editor-inserter__toggle');
            await page.waitForSelector('.block-editor-inserter__menu');
            await page.type('.block-editor-inserter__search', 'query loop');
            await page.click('[data-type="core/query"]');
            
            await page.waitForSelector('.wp-block-query');
            
            // Click on Post Template
            await page.click('.wp-block-post-template');
            
            // Insert byline block
            await page.click('.block-editor-inserter__toggle');
            await page.waitForSelector('.block-editor-inserter__menu');
            await page.type('.block-editor-inserter__search', 'byline');
            await page.click('[data-type="news/post-byline"]');
            
            await page.waitForSelector('.wp-block-news-post-byline');
            
            // Click on the block to select it
            await page.click('.wp-block-news-post-byline');
            
            // Check for block toolbar
            const blockToolbar = await page.$('.block-editor-block-toolbar');
            expect(blockToolbar).toBeTruthy();
        });
    });
});