<?php
/**
 * Server-side rendering of the `kestrel-courier/juicy-headline-multi-query` block.
 *
 * @package WordPress
 */

/**
 * Finds all post template blocks within the query block.
 *
 * @param WP_Block_List $inner_blocks The inner blocks of the query block.
 * @return array Array of template blocks.
 */
function find_template_blocks( $inner_blocks ) {
	$templates = array();
	foreach ( $inner_blocks as $inner_block ) {
		if ( in_array(
			$inner_block->name,
			array(
				'kestrel-courier/saucy-story-template',
				'kestrel-courier/breaking-news-template',
				'kestrel-courier/featured-story-template',
			),
			true
		) ) {
			$templates[] = $inner_block;
		}
	}
	return $templates;
}

/**
 * Modifies the static `kestrel-courier/juicy-headline-multi-query` block on the server.
 *
 * @since 6.4.0
 *
 * @param array    $attributes Block attributes.
 * @param string   $content    Block default content.
 * @param WP_Block $block      The block instance.
 *
 * @return string Returns the modified output of the query block.
 */
function render_block_kestrel_courier_juicy_headline_multi_query( $attributes, $content, $block ) {
	$is_interactive = isset( $attributes['enhancedPagination'] )
		&& true === $attributes['enhancedPagination']
		&& isset( $attributes['queryId'] );

	// Check if we have multiple template blocks - if so, handle sequential rendering.
	$template_blocks = find_template_blocks( $block->inner_blocks );
	
	if ( count( $template_blocks ) > 1 ) {
		// Multiple templates: render with sequential assignment.
		// Identify template types and assign offset/limit via context.
		$template_configs = array();
		$current_offset = 0;
		
		foreach ( $template_blocks as $template_block ) {
			$template_name = $template_block->name;
			
			if ( 'kestrel-courier/featured-story-template' === $template_name ) {
				$template_configs[] = array(
					'block' => $template_block,
					'offset' => $current_offset,
					'posts_per_page' => 1,
				);
				$current_offset += 1;
			} elseif ( 'kestrel-courier/breaking-news-template' === $template_name ) {
				$template_configs[] = array(
					'block' => $template_block,
					'offset' => $current_offset,
					'posts_per_page' => 1,
				);
				$current_offset += 1;
			} elseif ( 'kestrel-courier/saucy-story-template' === $template_name ) {
				$template_configs[] = array(
					'block' => $template_block,
					'offset' => $current_offset,
					'posts_per_page' => -1, // Unlimited - render remaining posts
				);
				// Don't increment offset for saucy template as it handles the rest
			}
		}
		
		// Manually render each template with modified context
		$rendered_content = '';
		foreach ( $template_configs as $config ) {
			$template_block = $config['block'];
			
			// Get the parsed block array
			$template_block_parsed = $template_block->parsed_block;
			if ( ! isset( $template_block_parsed['blockName'] ) ) {
				// Fallback: construct parsed block from block object
				$template_block_parsed = array(
					'blockName' => $template_block->name,
					'attrs' => isset( $template_block->attributes ) ? $template_block->attributes : array(),
					'innerBlocks' => isset( $template_block->inner_blocks ) && $template_block->inner_blocks 
						? array_map( 
							function( $inner ) {
								return isset( $inner->parsed_block ) ? $inner->parsed_block : array();
							}, 
							iterator_to_array( $template_block->inner_blocks ) 
						) 
						: array(),
				);
			}
			
			// Create a new block instance with modified context
			// Copy context and add offset/posts_per_page to query context
			$modified_context = $block->context;
			
			// Build query context: start with context if available, then merge attributes
			$query_context = isset( $modified_context['query'] ) ? $modified_context['query'] : array();
			if ( isset( $attributes['query'] ) && is_array( $attributes['query'] ) ) {
				$query_context = array_merge( $attributes['query'], $query_context );
			}
			
			// Add offset and perPage to query context (perPage is the WordPress convention)
			$query_context['offset'] = $config['offset'];
			$query_context['perPage'] = $config['posts_per_page'];
			
			$modified_context['query'] = $query_context;
			
			// Render the template block with modified context
			$template_block_instance = new WP_Block( $template_block_parsed, $modified_context );
			$rendered_content .= $template_block_instance->render();
		}
		
		// Wrap the content in the query block wrapper
		$wrapper_attributes = get_block_wrapper_attributes();
		$content = '<' . ( isset( $attributes['tagName'] ) ? esc_attr( $attributes['tagName'] ) : 'div' ) . ' ' . $wrapper_attributes . '>' . $rendered_content . '</' . ( isset( $attributes['tagName'] ) ? esc_attr( $attributes['tagName'] ) : 'div' ) . '>';
	}

	// Enqueue the script module and add the necessary directives if the block is
	// interactive.
	if ( $is_interactive ) {
		wp_enqueue_script_module( '@wordpress/block-library/query/view' );

		$p = new WP_HTML_Tag_Processor( $content );
		if ( $p->next_tag() ) {
			// Add the necessary directives.
			$p->set_attribute( 'data-wp-interactive', 'kestrel-courier/juicy-headline-multi-query' );
			$p->set_attribute( 'data-wp-router-region', 'query-' . $attributes['queryId'] );
			$p->set_attribute( 'data-wp-context', '{}' );
			$p->set_attribute( 'data-wp-key', $attributes['queryId'] );
			$content = $p->get_updated_html();
		}
	}

	// Add the styles to the block type if the block is interactive and remove
	// them if it's not.
	$style_asset = 'wp-block-query';
	if ( ! wp_style_is( $style_asset ) ) {
		$style_handles = $block->block_type->style_handles;
		// If the styles are not needed, and they are still in the `style_handles`, remove them.
		if ( ! $is_interactive && in_array( $style_asset, $style_handles, true ) ) {
			$block->block_type->style_handles = array_diff( $style_handles, array( $style_asset ) );
		}
		// If the styles are needed, but they were previously removed, add them again.
		if ( $is_interactive && ! in_array( $style_asset, $style_handles, true ) ) {
			$block->block_type->style_handles = array_merge( $style_handles, array( $style_asset ) );
		}
	}

	return $content;
}

/**
 * Registers the `kestrel-courier/juicy-headline-multi-query` block on the server.
 *
 * @since 5.8.0
 */
function register_block_kestrel_courier_juicy_headline_multi_query() {
	register_block_type_from_metadata(
		dirname( __DIR__, 3 ) . '/build/blocks/juicy-headline-multi-query',
		array(
			'render_callback' => 'render_block_kestrel_courier_juicy_headline_multi_query',
		)
	);
}
add_action( 'init', 'register_block_kestrel_courier_juicy_headline_multi_query' );

/**
 * Ensure the block is available in all post types that support the block editor.
 *
 * @param array $allowed_block_types Array of allowed block types. Default is all block types.
 * @param object $post The post object.
 * @return array Modified array of allowed block types.
 */
function kestrel_courier_allow_query_block_in_posts( $allowed_block_types, $post ) {
	if ( ! $post ) {
		return $allowed_block_types;
	}
	
	// Ensure the block is always available if post type supports editor
	if ( post_type_supports( $post->post_type, 'editor' ) ) {
		if ( ! is_array( $allowed_block_types ) ) {
			// If all blocks are allowed, return null to keep that behavior
			return $allowed_block_types;
		}
		// Add our block to the allowed list if a filter is restricting blocks
		if ( ! in_array( 'kestrel-courier/juicy-headline-multi-query', $allowed_block_types, true ) ) {
			$allowed_block_types[] = 'kestrel-courier/juicy-headline-multi-query';
		}
	}
	
	return $allowed_block_types;
}
add_filter( 'allowed_block_types_all', 'kestrel_courier_allow_query_block_in_posts', 10, 2 );

/**
 * Traverse the tree of blocks looking for any plugin block (i.e., a block from
 * an installed plugin) inside a Query block with the enhanced pagination
 * enabled. If at least one is found, the enhanced pagination is effectively
 * disabled to prevent any potential incompatibilities.
 *
 * @since 6.4.0
 *
 * @param array $parsed_block The block being rendered.
 * @return array Returns the parsed block, unmodified.
 */
function kestrel_courier_block_core_query_disable_enhanced_pagination( $parsed_block ) {
	static $enhanced_query_stack   = array();
	static $dirty_enhanced_queries = array();
	static $render_query_callback  = null;

	$block_name              = $parsed_block['blockName'];
	$block_type              = WP_Block_Type_Registry::get_instance()->get_registered( $block_name );
	$has_enhanced_pagination = isset( $parsed_block['attrs']['enhancedPagination'] ) && true === $parsed_block['attrs']['enhancedPagination'] && isset( $parsed_block['attrs']['queryId'] );
	/*
	 * Client side navigation can be true in two states:
	 *  - supports.interactivity = true;
	 *  - supports.interactivity.clientNavigation = true;
	 */
	$supports_client_navigation = ( isset( $block_type->supports['interactivity']['clientNavigation'] ) && true === $block_type->supports['interactivity']['clientNavigation'] )
		|| ( isset( $block_type->supports['interactivity'] ) && true === $block_type->supports['interactivity'] );

	if ( 'kestrel-courier/juicy-headline-multi-query' === $block_name && $has_enhanced_pagination ) {
		$enhanced_query_stack[] = $parsed_block['attrs']['queryId'];

		if ( ! isset( $render_query_callback ) ) {
			/**
			 * Filter that disables the enhanced pagination feature during block
			 * rendering when a plugin block has been found inside. It does so
			 * by adding an attribute called `data-wp-navigation-disabled` which
			 * is later handled by the front-end logic.
			 *
			 * @param string   $content  The block content.
			 * @param array    $block    The full block, including name and attributes.
			 * @return string Returns the modified output of the query block.
			 */
			$render_query_callback = static function ( $content, $block ) use ( &$enhanced_query_stack, &$dirty_enhanced_queries, &$render_query_callback ) {
				$has_enhanced_pagination = isset( $block['attrs']['enhancedPagination'] ) && true === $block['attrs']['enhancedPagination'] && isset( $block['attrs']['queryId'] );

				if ( ! $has_enhanced_pagination ) {
					return $content;
				}

				if ( isset( $dirty_enhanced_queries[ $block['attrs']['queryId'] ] ) ) {
					// Disable navigation in the router store config.
					wp_interactivity_config( 'core/router', array( 'clientNavigationDisabled' => true ) );
					$dirty_enhanced_queries[ $block['attrs']['queryId'] ] = null;
				}

				array_pop( $enhanced_query_stack );

				if ( empty( $enhanced_query_stack ) ) {
					remove_filter( 'render_block_kestrel-courier/juicy-headline-multi-query', $render_query_callback );
					$render_query_callback = null;
				}

				return $content;
			};

			add_filter( 'render_block_kestrel-courier/juicy-headline-multi-query', $render_query_callback, 10, 2 );
		}
	} elseif (
		! empty( $enhanced_query_stack ) &&
		isset( $block_name ) &&
		( ! $supports_client_navigation )
	) {
		foreach ( $enhanced_query_stack as $query_id ) {
			$dirty_enhanced_queries[ $query_id ] = true;
		}
	}

	return $parsed_block;
}

add_filter( 'render_block_data', 'kestrel_courier_block_core_query_disable_enhanced_pagination', 10, 1 );
