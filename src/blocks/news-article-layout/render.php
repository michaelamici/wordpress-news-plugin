<?php
/**
 * News Front Layout Block
 * 
 * Server-side render for the news front layout block.
 * Displays articles in hero-grid-list layout with position-aware rendering.
 */

// Use FrontLayoutRenderer for clean server-side rendering
return \NewsPlugin\Blocks\Renderers\FrontLayoutRenderer::render($attributes, $content, $block);