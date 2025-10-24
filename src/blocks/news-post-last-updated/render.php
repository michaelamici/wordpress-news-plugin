<?php
/**
 * News Post Last Updated Block
 * 
 * Server-side render for the news post last updated block.
 */

// Use LastUpdatedRenderer for clean server-side rendering
return \NewsPlugin\Blocks\Renderers\LastUpdatedRenderer::render($attributes, $content, $block);