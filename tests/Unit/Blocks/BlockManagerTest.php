<?php

declare(strict_types=1);

namespace NewsPlugin\Tests\Unit\Blocks;

use NewsPlugin\Blocks\BlockManager;
use NewsPlugin\Core\Plugin;
use NewsPlugin\Assets\AssetManager;
use PHPUnit\Framework\TestCase;
use Brain\Monkey;
use Brain\Monkey\Functions;

/**
 * Unit tests for BlockManager class
 */
class BlockManagerTest extends TestCase
{
    private BlockManager $blockManager;
    private Plugin $plugin;
    private AssetManager $assetManager;

    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();

        // Mock WordPress functions
        Functions\when('__')->returnArg();
        Functions\when('esc_html__')->returnArg();
        Functions\when('esc_attr')->returnArg();
        Functions\when('esc_url')->returnArg();
        Functions\when('wp_kses_post')->returnArg();
        Functions\when('add_action')->returnValue(true);
        Functions\when('add_filter')->returnValue(true);
        Functions\when('register_block_type')->returnValue(true);
        Functions\when('get_post')->returnValue((object) [
            'ID' => 1,
            'post_title' => 'Test Article',
            'post_excerpt' => 'Test excerpt',
            'post_author' => 1,
            'post_type' => 'news',
            'post_status' => 'publish'
        ]);
        Functions\when('has_post_thumbnail')->returnValue(true);
        Functions\when('get_the_post_thumbnail')->returnValue('<img src="test.jpg" alt="Test">');
        Functions\when('get_permalink')->returnValue('https://example.com/test-article');
        Functions\when('get_the_date')->returnValue('2024-01-01');
        Functions\when('get_the_author_meta')->returnValue('Test Author');
        Functions\when('get_the_title')->returnValue('Test Article');
        Functions\when('get_the_excerpt')->returnValue('Test excerpt');
        Functions\when('get_the_author')->returnValue('Test Author');
        Functions\when('wp_reset_postdata')->returnValue(true);

        // Mock the Plugin and AssetManager
        $this->plugin = $this->createMock(Plugin::class);
        $this->assetManager = $this->createMock(AssetManager::class);
        
        $this->plugin->method('getAssetManager')->willReturn($this->assetManager);
        
        // Mock the Plugin::instance() static method
        Plugin::setInstance($this->plugin);
    }

    protected function tearDown(): void
    {
        Monkey\tearDown();
        parent::tearDown();
    }

    public function testConstructor(): void
    {
        $blockManager = new BlockManager();
        $this->assertInstanceOf(BlockManager::class, $blockManager);
    }

    public function testRegisterBlocks(): void
    {
        $blockManager = new BlockManager();
        
        // Test that register_block_type is called for each block
        Functions\expect('register_block_type')
            ->with('news/article', \Mockery::type('array'))
            ->once();
            
        Functions\expect('register_block_type')
            ->with('news/section', \Mockery::type('array'))
            ->once();
            
        Functions\expect('register_block_type')
            ->with('news/breaking-news', \Mockery::type('array'))
            ->once();
            
        Functions\expect('register_block_type')
            ->with('news/grid', \Mockery::type('array'))
            ->once();

        $blockManager->registerBlocks();
    }

    public function testRenderNewsArticleBlockWithValidArticle(): void
    {
        $blockManager = new BlockManager();
        $attributes = [
            'articleId' => 1,
            'showExcerpt' => true,
            'showMeta' => true,
            'showImage' => true
        ];

        $output = $blockManager->renderNewsArticleBlock($attributes);
        
        $this->assertStringContainsString('news-article-block', $output);
        $this->assertStringContainsString('news-article-title', $output);
        $this->assertStringContainsString('Test Article', $output);
        $this->assertStringContainsString('news-article-excerpt', $output);
        $this->assertStringContainsString('news-article-meta', $output);
    }

    public function testRenderNewsArticleBlockWithNoArticleId(): void
    {
        $blockManager = new BlockManager();
        $attributes = ['articleId' => 0];

        $output = $blockManager->renderNewsArticleBlock($attributes);
        
        $this->assertStringContainsString('Please select an article', $output);
    }

    public function testRenderNewsArticleBlockWithInvalidArticle(): void
    {
        Functions\when('get_post')->returnValue(null);
        
        $blockManager = new BlockManager();
        $attributes = ['articleId' => 999];

        $output = $blockManager->renderNewsArticleBlock($attributes);
        
        $this->assertStringContainsString('Article not found', $output);
    }

    public function testRenderNewsSectionBlock(): void
    {
        // Mock WP_Query
        $mockQuery = $this->createMock(\WP_Query::class);
        $mockQuery->method('have_posts')->willReturn(true);
        $mockQuery->method('the_post')->willReturn(true);
        
        // Mock the global $wp_query
        global $wp_query;
        $wp_query = $mockQuery;
        
        $blockManager = new BlockManager();
        $attributes = [
            'sectionId' => 1,
            'postsPerPage' => 5,
            'layout' => 'list',
            'showExcerpt' => true,
            'showMeta' => true,
            'showImage' => true
        ];

        $output = $blockManager->renderNewsSectionBlock($attributes);
        
        $this->assertStringContainsString('news-section-block', $output);
        $this->assertStringContainsString('news-layout-list', $output);
    }

    public function testRenderNewsSectionBlockWithNoPosts(): void
    {
        // Mock WP_Query with no posts
        $mockQuery = $this->createMock(\WP_Query::class);
        $mockQuery->method('have_posts')->willReturn(false);
        
        $blockManager = new BlockManager();
        $attributes = ['sectionId' => 1];

        $output = $blockManager->renderNewsSectionBlock($attributes);
        
        $this->assertStringContainsString('No articles found', $output);
    }

    public function testRenderBreakingNewsBlock(): void
    {
        // Mock WP_Query
        $mockQuery = $this->createMock(\WP_Query::class);
        $mockQuery->method('have_posts')->willReturn(true);
        $mockQuery->method('the_post')->willReturn(true);
        
        $blockManager = new BlockManager();
        $attributes = [
            'count' => 3,
            'scroll' => true,
            'speed' => 50
        ];

        $output = $blockManager->renderBreakingNewsBlock($attributes);
        
        $this->assertStringContainsString('news-breaking-block', $output);
        $this->assertStringContainsString('news-scrolling', $output);
        $this->assertStringContainsString('data-speed="50"', $output);
    }

    public function testRenderBreakingNewsBlockWithNoPosts(): void
    {
        // Mock WP_Query with no posts
        $mockQuery = $this->createMock(\WP_Query::class);
        $mockQuery->method('have_posts')->willReturn(false);
        
        $blockManager = new BlockManager();
        $attributes = ['count' => 3];

        $output = $blockManager->renderBreakingNewsBlock($attributes);
        
        $this->assertEmpty($output);
    }

    public function testRenderNewsGridBlock(): void
    {
        // Mock WP_Query
        $mockQuery = $this->createMock(\WP_Query::class);
        $mockQuery->method('have_posts')->willReturn(true);
        $mockQuery->method('the_post')->willReturn(true);
        
        $blockManager = new BlockManager();
        $attributes = [
            'postsPerPage' => 6,
            'columns' => 3,
            'sectionId' => 1,
            'featured' => false,
            'showExcerpt' => true,
            'showMeta' => true,
            'showImage' => true
        ];

        $output = $blockManager->renderNewsGridBlock($attributes);
        
        $this->assertStringContainsString('news-grid-block', $output);
        $this->assertStringContainsString('news-columns-3', $output);
    }

    public function testRenderNewsGridBlockWithNoPosts(): void
    {
        // Mock WP_Query with no posts
        $mockQuery = $this->createMock(\WP_Query::class);
        $mockQuery->method('have_posts')->willReturn(false);
        
        $blockManager = new BlockManager();
        $attributes = ['postsPerPage' => 6];

        $output = $blockManager->renderNewsGridBlock($attributes);
        
        $this->assertStringContainsString('No articles found', $output);
    }

    public function testEnqueueBlockAssets(): void
    {
        $this->assetManager->expects($this->once())
            ->method('enqueueBlockAssets');

        $blockManager = new BlockManager();
        $blockManager->enqueueBlockAssets();
    }

    public function testEnqueueBlockStyles(): void
    {
        $this->assetManager->expects($this->once())
            ->method('enqueueStyle')
            ->with('news-blocks', 'css/blocks.css');

        $blockManager = new BlockManager();
        $blockManager->enqueueBlockStyles();
    }

    public function testAddBlockCategory(): void
    {
        $blockManager = new BlockManager();
        $categories = [];
        $post = (object) ['ID' => 1];

        $result = $blockManager->addBlockCategory($categories, $post);
        
        $this->assertCount(1, $result);
        $this->assertEquals('news', $result[0]['slug']);
        $this->assertEquals('News', $result[0]['title']);
        $this->assertEquals('megaphone', $result[0]['icon']);
    }

    public function testGetBlocks(): void
    {
        $blockManager = new BlockManager();
        $blocks = $blockManager->getBlocks();
        
        $this->assertIsArray($blocks);
        $this->assertArrayHasKey('news-article', $blocks);
        $this->assertArrayHasKey('news-section', $blocks);
        $this->assertArrayHasKey('breaking-news', $blocks);
        $this->assertArrayHasKey('news-grid', $blocks);
    }

    public function testGetBlock(): void
    {
        $blockManager = new BlockManager();
        
        $block = $blockManager->getBlock('news-article');
        $this->assertIsArray($block);
        $this->assertEquals('news-article', $block['name']);
        
        $nonExistentBlock = $blockManager->getBlock('non-existent');
        $this->assertNull($nonExistentBlock);
    }
}
