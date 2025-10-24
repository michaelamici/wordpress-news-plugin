<?php
/**
 * News Post Featured Block
 * 
 * Server-side render for the news post featured block.
 */

// Use FeaturedRenderer for clean server-side rendering
return \NewsPlugin\Blocks\Renderers\FeaturedRenderer::render($attributes, $content, $block);