<?php
/**
 * News Post Live Block
 * 
 * Server-side render for the news post live block.
 */

// Use LiveRenderer for clean server-side rendering
return \NewsPlugin\Blocks\Renderers\LiveRenderer::render($attributes, $content, $block);