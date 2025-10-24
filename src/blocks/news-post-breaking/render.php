<?php
/**
 * News Post Breaking Block
 * 
 * Server-side render for the news post breaking block.
 */

// Use BreakingRenderer for clean server-side rendering
return \NewsPlugin\Blocks\Renderers\BreakingRenderer::render($attributes, $content, $block);