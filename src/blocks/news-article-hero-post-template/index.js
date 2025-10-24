import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, InnerBlocks } from '@wordpress/block-editor';
import { useSelect } from '@wordpress/data';
import { useEntityRecord } from '@wordpress/core-data';
import { __ } from '@wordpress/i18n';
import { createContext } from '@wordpress/element';

import metadata from './block.json';

// Create context for passing post data to child blocks
const PostContext = createContext({});

/**
 * Edit component for the news/article-hero-post-template block
 * Simplified for hero position only
 */
function Edit({ context, clientId, attributes }) {
    const blockProps = useBlockProps({
        className: 'news-article-hero-post-template'
    });
    
    // Get post context from parent block or attributes
    const postId = context?.['news/postId'] || attributes?.postId || context?.postId;
    const postType = context?.['news/postType'] || attributes?.postType || context?.postType || 'news';
    const position = context?.['news/position'] || attributes?.position || 'hero';
    
    // Fetch the post data for preview
    const { record: post, hasResolved } = useEntityRecord('postType', postType, postId);
    
    // Get post meta for badges and flags
    const postMeta = useSelect((select) => {
        if (!postId) return {};
        return select('core').getEntityRecord('postType', postType, postId, { _embed: true });
    }, [postId, postType]);
    
    // Check if this block has any inner blocks
    const hasInnerBlocks = useSelect((select) => {
        const { getBlocks } = select('core/block-editor');
        const innerBlocks = getBlocks(clientId);
        return innerBlocks.length > 0;
    }, [clientId]);

    // Hero-specific template (simplified)
    const defaultTemplate = [
        ['core/group', { className: 'news-article-header news-article-header--hero' }, [
            ['core/post-title', { level: 1, textAlign: 'left' }],
            ['core/post-date', {}],
            ['core/post-author', {}]
        ]],
        ['core/post-featured-image', { sizeSlug: 'large', align: 'wide' }],
        ['core/post-excerpt', { moreText: __('Read more', 'news') }],
        ['core/read-more', {}]
    ];
    
    // Show loading state
    if (!hasResolved) {
        return (
            <div {...blockProps}>
                <div className="news-article-template-loading">
                    <p>{__('Loading hero article...', 'news')}</p>
                </div>
            </div>
        );
    }
    
    // Show error if no post found
    if (!post) {
        return (
            <div {...blockProps}>
                <div className="news-article-template-error">
                    <p>{__('No hero article found. Please add a News Front Layout block first.', 'news')}</p>
                </div>
            </div>
        );
    }
    
    // Create context value for child blocks
    const contextValue = {
        postId: post.id,
        postType: postType,
        position: position,
        post: post,
        meta: postMeta
    };
    
    return (
        <PostContext.Provider value={contextValue}>
        <div {...blockProps}>
            <InnerBlocks 
                template={hasInnerBlocks ? undefined : defaultTemplate}
                allowedBlocks={[
                    'core/group',
                    'core/post-title',
                    'core/post-featured-image',
                    'core/post-excerpt',
                    'core/read-more',
                    'core/post-content',
                    'core/post-date',
                    'core/post-author',
                    'core/post-terms'
                ]}
                templateLock={false}
                renderAppender={InnerBlocks.ButtonBlockAppender}
            />
        </div>
        </PostContext.Provider>
    );
}

/**
 * Save component for the news/article-post-template block
 */
function Save() {
    return <InnerBlocks.Content />;
}

/**
 * Register the block
 */
registerBlockType(metadata, {
    edit: Edit,
    save: Save,
});
