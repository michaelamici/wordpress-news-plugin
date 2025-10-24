<?php
/**
 * News Post Exclusive Block
 * 
 * Server-side render for the news post exclusive block.
 */

// Use ExclusiveRenderer for clean server-side rendering
return \NewsPlugin\Blocks\Renderers\ExclusiveRenderer::render($attributes, $content, $block);