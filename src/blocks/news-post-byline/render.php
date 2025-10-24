<?php
/**
 * News Post Byline Block
 * 
 * Server-side render for the news post byline block.
 */

// Use BylineRenderer for clean server-side rendering
return \NewsPlugin\Blocks\Renderers\BylineRenderer::render($attributes, $content, $block);