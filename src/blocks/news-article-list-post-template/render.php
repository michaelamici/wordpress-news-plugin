<?php
/**
 * News Article List Post Template Block
 * 
 * Server-side render for the news article list post template block.
 */

// Use ArticleTemplateRenderer for clean server-side rendering
return \NewsPlugin\Blocks\Renderers\ArticleTemplateRenderer::render($attributes, $content, $block);
