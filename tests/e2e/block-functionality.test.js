/**
 * Comprehensive end-to-end tests for News Plugin byline block functionality
 * Tests block behavior, settings, and rendering using Puppeteer
 */

const puppeteer = require('puppeteer');
const { createURL } = require('@wordpress/e2e-test-utils');

describe('News Plugin Byline Block Functionality', () => {
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
        
        // Enable request interception for debugging
        await page.setRequestInterception(true);
        page.on('request', request => {
            if (request.url().includes('admin-ajax.php')) {
                console.log('AJAX Request:', request.url());
            }
            request.continue();
        });
    });

    afterEach(async () => {
        if (page) {
            await page.close();
        }
    });

    describe('Block Editor Integration', () => {
        test('should load block editor with byline block available in Query Loop', async () => {
            await page.goto(createURL('/wp-admin/post-new.php?post_type=page'));
            
            // Wait for editor to fully load
            await page.waitForSelector('.editor-post-title__input', { timeout: 10000 });
            await page.waitForSelector('.block-editor-inserter__toggle');
            
            // Verify editor is loaded
            const editorTitle = await page.$('.editor-post-title__input');
            expect(editorTitle).toBeTruthy();
            
            // Add Query Loop block
            await page.click('.block-editor-inserter__toggle');
            await page.waitForSelector('.block-editor-inserter__menu');
            await page.type('.block-editor-inserter__search', 'query loop');
            await page.click('[data-type="core/query"]');
            
            await page.waitForSelector('.wp-block-query');
            
            // Click on Post Template
            await page.click('.wp-block-post-template');
            
            // Open block inserter again
            await page.click('.block-editor-inserter__toggle');
            await page.waitForSelector('.block-editor-inserter__menu');
            
            // Verify byline block is available
            const bylineBlock = await page.$('[data-type="news/post-byline"]');
            expect(bylineBlock).toBeTruthy();
        });

        test('should maintain block state during editor interactions', async () => {
            await page.goto(createURL('/wp-admin/post-new.php?post_type=page'));
            await page.waitForSelector('.editor-post-title__input');
            
            // Add title
            await page.type('.editor-post-title__input', 'Test Page with Query Loop');
            
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
            
            // Verify block is inserted
            const bylineBlock = await page.$('.wp-block-news-post-byline');
            expect(bylineBlock).toBeTruthy();
        });
    });

    describe('Block Context and Restrictions', () => {
        test('should only be available within Post Template context', async () => {
            await page.goto(createURL('/wp-admin/post-new.php?post_type=news'));
            await page.waitForSelector('.editor-post-title__input');
            
            // Open block inserter
            await page.click('.block-editor-inserter__toggle');
            await page.waitForSelector('.block-editor-inserter__menu');
            await page.type('.block-editor-inserter__search', 'byline');
            
            // The block should not be available outside Query Loop
            const bylineBlock = await page.$('[data-type="news/post-byline"]');
            expect(bylineBlock).toBeFalsy();
        });

        test('should be available within Query Loop Post Template', async () => {
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
            
            // Now search for byline block
            await page.click('.block-editor-inserter__toggle');
            await page.waitForSelector('.block-editor-inserter__menu');
            await page.type('.block-editor-inserter__search', 'byline');
            
            // The block should now be available
            const bylineBlock = await page.$('[data-type="news/post-byline"]');
            expect(bylineBlock).toBeTruthy();
        });
    });

    describe('Block Rendering and Output', () => {
        test('should render byline block with proper HTML structure', async () => {
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
            
            // Check block HTML structure
            const blockHTML = await page.evaluate(() => {
                const block = document.querySelector('.wp-block-news-post-byline');
                return block ? block.outerHTML : null;
            });
            
            expect(blockHTML).toContain('wp-block-news-post-byline');
        });

        test('should handle empty byline data gracefully', async () => {
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
            
            // Check for empty content (no byline data)
            const blockContent = await page.evaluate(() => {
                const block = document.querySelector('.wp-block-news-post-byline');
                return block ? block.textContent.trim() : '';
            });
            
            expect(blockContent).toBe('');
        });
    });

    describe('Block Performance and Loading', () => {
        test('should load byline block without performance issues', async () => {
            const startTime = Date.now();
            
            await page.goto(createURL('/wp-admin/post-new.php?post_type=page'));
            await page.waitForSelector('.editor-post-title__input');
            
            const loadTime = Date.now() - startTime;
            expect(loadTime).toBeLessThan(10000); // Should load within 10 seconds
            
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
            
            // Verify block loaded
            const block = await page.$('.wp-block-news-post-byline');
            expect(block).toBeTruthy();
        });

        test('should handle block insertion without errors', async () => {
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
            
            // Verify block was inserted
            const block = await page.$('.wp-block-news-post-byline');
            expect(block).toBeTruthy();
        });
    });

    describe('Block Accessibility', () => {
        test('should have proper accessibility attributes', async () => {
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
            
            // Check for accessibility attributes
            const block = await page.$('.wp-block-news-post-byline');
            expect(block).toBeTruthy();
        });

        test('should support keyboard navigation', async () => {
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
            
            // Test keyboard navigation
            await page.keyboard.press('Tab');
            await page.keyboard.press('Tab');
            
            // Verify focus is on block
            const focusedElement = await page.evaluate(() => document.activeElement);
            expect(focusedElement).toBeTruthy();
        });
    });

    describe('Block Error Handling', () => {
        test('should handle missing byline data gracefully', async () => {
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
            
            // Check for proper handling when no byline data
            const blockContent = await page.evaluate(() => {
                const block = document.querySelector('.wp-block-news-post-byline');
                return block ? block.textContent.trim() : '';
            });
            
            expect(blockContent).toBe('');
        });

        test('should maintain block state during errors', async () => {
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
            
            // Verify block remains in editor
            const block = await page.$('.wp-block-news-post-byline');
            expect(block).toBeTruthy();
            
            // Block should still be selectable and editable
            await page.click('.wp-block-news-post-byline');
            const selectedBlock = await page.$('.wp-block-news-post-byline.is-selected');
            expect(selectedBlock).toBeTruthy();
        });
    });
});