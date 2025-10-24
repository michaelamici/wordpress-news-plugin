<?php
/**
 * News Article Post Template Block
 * 
 * Server-side render for the news article post template block.
 */

// Use ArticleTemplateRenderer for clean server-side rendering
return \NewsPlugin\Blocks\Renderers\ArticleTemplateRenderer::render($attributes, $content, $block);