<?php

declare(strict_types=1);

namespace NewsPlugin\Tests\Integration\Blocks;

use NewsPlugin\Blocks\BlockManager;
use NewsPlugin\Core\Plugin;
use WP_UnitTestCase;

/**
 * Integration tests for BlockManager class
 */
class BlockManagerIntegrationTest extends WP_UnitTestCase
{
    private BlockManager $blockManager;

    public function setUp(): void
    {
        parent::setUp();
        
        // Create test posts and terms
        $this->createTestData();
        
        $this->blockManager = new BlockManager();
    }

    private function createTestData(): void
    {
        // Create test news posts
        $this->factory->post->create_many(5, [
            'post_type' => 'news',
            'post_status' => 'publish',
            'post_title' => 'Test News Article %d',
            'post_content' => 'This is test content for article %d',
            'post_excerpt' => 'Test excerpt for article %d',
        ]);

        // Create test news sections
        $this->factory->term->create([
            'taxonomy' => 'news_section',
            'name' => 'Politics',
            'slug' => 'politics',
        ]);

        $this->factory->term->create([
            'taxonomy' => 'news_section',
            'name' => 'Sports',
            'slug' => 'sports',
        ]);
    }

    public function testBlockRegistration(): void
    {
        // Test that blocks are registered
        $registered_blocks = \WP_Block_Type_Registry::get_instance()->get_all_registered();
        
        $this->assertArrayHasKey('news/article', $registered_blocks);
        $this->assertArrayHasKey('news/section', $registered_blocks);
        $this->assertArrayHasKey('news/breaking-news', $registered_blocks);
        $this->assertArrayHasKey('news/grid', $registered_blocks);
    }

    public function testNewsArticleBlockRendering(): void
    {
        $post_id = $this->factory->post->create([
            'post_type' => 'news',
            'post_title' => 'Test Article',
            'post_content' => 'Test content',
            'post_excerpt' => 'Test excerpt',
        ]);

        $attributes = [
            'articleId' => $post_id,
            'showExcerpt' => true,
            'showMeta' => true,
            'showImage' => false,
        ];

        $output = $this->blockManager->renderNewsArticleBlock($attributes);
        
        $this->assertStringContainsString('news-article-block', $output);
        $this->assertStringContainsString('Test Article', $output);
        $this->assertStringContainsString('Test excerpt', $output);
    }

    public function testNewsSectionBlockRendering(): void
    {
        $section_id = $this->factory->term->create([
            'taxonomy' => 'news_section',
            'name' => 'Test Section',
        ]);

        // Create posts in this section
        $post_ids = $this->factory->post->create_many(3, [
            'post_type' => 'news',
            'post_status' => 'publish',
        ]);

        foreach ($post_ids as $post_id) {
            wp_set_post_terms($post_id, [$section_id], 'news_section');
        }

        $attributes = [
            'sectionId' => $section_id,
            'postsPerPage' => 3,
            'layout' => 'list',
            'showExcerpt' => true,
            'showMeta' => true,
            'showImage' => false,
        ];

        $output = $this->blockManager->renderNewsSectionBlock($attributes);
        
        $this->assertStringContainsString('news-section-block', $output);
        $this->assertStringContainsString('news-layout-list', $output);
    }

    public function testBreakingNewsBlockRendering(): void
    {
        // Create a post with breaking news meta
        $post_id = $this->factory->post->create([
            'post_type' => 'news',
            'post_title' => 'Breaking News Test',
        ]);

        update_post_meta($post_id, '_news_article_meta', 'breaking');

        $attributes = [
            'count' => 1,
            'scroll' => true,
            'speed' => 30,
        ];

        $output = $this->blockManager->renderBreakingNewsBlock($attributes);
        
        $this->assertStringContainsString('news-breaking-block', $output);
        $this->assertStringContainsString('news-scrolling', $output);
        $this->assertStringContainsString('Breaking News Test', $output);
    }

    public function testNewsGridBlockRendering(): void
    {
        $section_id = $this->factory->term->create([
            'taxonomy' => 'news_section',
            'name' => 'Grid Test Section',
        ]);

        // Create posts in this section
        $post_ids = $this->factory->post->create_many(4, [
            'post_type' => 'news',
            'post_status' => 'publish',
        ]);

        foreach ($post_ids as $post_id) {
            wp_set_post_terms($post_id, [$section_id], 'news_section');
        }

        $attributes = [
            'postsPerPage' => 4,
            'columns' => 2,
            'sectionId' => $section_id,
            'featured' => false,
            'showExcerpt' => true,
            'showMeta' => true,
            'showImage' => false,
        ];

        $output = $this->blockManager->renderNewsGridBlock($attributes);
        
        $this->assertStringContainsString('news-grid-block', $output);
        $this->assertStringContainsString('news-columns-2', $output);
    }

    public function testBlockCategoryRegistration(): void
    {
        $categories = [];
        $post = $this->factory->post->create_and_get();
        
        $result = $this->blockManager->addBlockCategory($categories, $post);
        
        $this->assertCount(1, $result);
        $this->assertEquals('news', $result[0]['slug']);
    }

    public function testBlockAttributes(): void
    {
        $blocks = $this->blockManager->getBlocks();
        
        // Test news-article block attributes
        $article_block = $blocks['news-article'];
        $this->assertArrayHasKey('attributes', $article_block);
        $this->assertArrayHasKey('articleId', $article_block['attributes']);
        $this->assertArrayHasKey('showExcerpt', $article_block['attributes']);
        $this->assertArrayHasKey('showMeta', $article_block['attributes']);
        $this->assertArrayHasKey('showImage', $article_block['attributes']);

        // Test news-section block attributes
        $section_block = $blocks['news-section'];
        $this->assertArrayHasKey('attributes', $section_block);
        $this->assertArrayHasKey('sectionId', $section_block['attributes']);
        $this->assertArrayHasKey('postsPerPage', $section_block['attributes']);
        $this->assertArrayHasKey('layout', $section_block['attributes']);

        // Test breaking-news block attributes
        $breaking_block = $blocks['breaking-news'];
        $this->assertArrayHasKey('attributes', $breaking_block);
        $this->assertArrayHasKey('count', $breaking_block['attributes']);
        $this->assertArrayHasKey('scroll', $breaking_block['attributes']);
        $this->assertArrayHasKey('speed', $breaking_block['attributes']);

        // Test news-grid block attributes
        $grid_block = $blocks['news-grid'];
        $this->assertArrayHasKey('attributes', $grid_block);
        $this->assertArrayHasKey('postsPerPage', $grid_block['attributes']);
        $this->assertArrayHasKey('columns', $grid_block['attributes']);
        $this->assertArrayHasKey('sectionId', $grid_block['attributes']);
        $this->assertArrayHasKey('featured', $grid_block['attributes']);
    }

    public function testBlockSupports(): void
    {
        $blocks = $this->blockManager->getBlocks();
        
        foreach ($blocks as $block) {
            $this->assertArrayHasKey('supports', $block);
            $this->assertArrayHasKey('align', $block['supports']);
            $this->assertArrayHasKey('html', $block['supports']);
            $this->assertContains('wide', $block['supports']['align']);
            $this->assertContains('full', $block['supports']['align']);
            $this->assertFalse($block['supports']['html']);
        }
    }

    public function testBlockKeywords(): void
    {
        $blocks = $this->blockManager->getBlocks();
        
        foreach ($blocks as $block) {
            $this->assertArrayHasKey('keywords', $block);
            $this->assertIsArray($block['keywords']);
            $this->assertContains('news', $block['keywords']);
        }
    }

    public function testBlockIcons(): void
    {
        $blocks = $this->blockManager->getBlocks();
        
        $expected_icons = [
            'news-article' => 'megaphone',
            'news-section' => 'category',
            'breaking-news' => 'warning',
            'news-grid' => 'grid-view',
        ];

        foreach ($expected_icons as $block_name => $expected_icon) {
            $this->assertEquals($expected_icon, $blocks[$block_name]['icon']);
        }
    }
}
