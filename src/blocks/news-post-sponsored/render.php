<?php
/**
 * News Post Sponsored Block
 * 
 * Server-side render for the news post sponsored block.
 */

// Use SponsoredRenderer for clean server-side rendering
return \NewsPlugin\Blocks\Renderers\SponsoredRenderer::render($attributes, $content, $block);