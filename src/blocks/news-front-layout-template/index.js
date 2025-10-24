import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, InnerBlocks } from '@wordpress/block-editor';
import { useSelect } from '@wordpress/data';
import { useEntityRecord } from '@wordpress/core-data';
import { __ } from '@wordpress/i18n';
import { createContext, useContext } from '@wordpress/element';

import metadata from './block.json';

// Create context for passing post data to child blocks
const PostContext = createContext({});

/**
 * Edit component for the news/article-post-template block
 * Enhanced to show real article data in editor preview
 */
function Edit({ context, clientId, attributes }) {
    const blockProps = useBlockProps({
        className: 'news-article-post-template'
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

    // Position-specific templates
    const getTemplateForPosition = (position) => {
        switch (position) {
            case 'hero':
                return [
                    ['core/group', { className: 'news-article-header news-article-header--hero' }, [
                        ['news/post-featured', {}],
                        ['news/post-breaking', {}],
                        ['core/post-title', { level: 1, textAlign: 'left' }],
                        ['news/post-byline', {}]
                    ]],
                    ['core/group', { className: 'news-article-meta news-article-meta--hero' }, [
                        ['news/post-last-updated', {}],
                        ['news/post-exclusive', {}],
                        ['news/post-sponsored', {}],
                        ['news/post-live', {}]
                    ]],
                    ['core/post-featured-image', { sizeSlug: 'large', align: 'wide' }],
                    ['core/post-excerpt', { moreText: __('Read more', 'news') }],
                    ['core/read-more', {}]
                ];
            case 'grid':
                return [
                    ['core/group', { className: 'news-article-header news-article-header--grid' }, [
                        ['news/post-featured', {}],
                        ['news/post-breaking', {}],
                        ['core/post-title', { level: 3, textAlign: 'left' }],
                        ['news/post-byline', {}]
                    ]],
                    ['core/post-featured-image', { sizeSlug: 'medium' }],
                    ['core/post-excerpt', { moreText: __('Read more', 'news') }],
                    ['core/read-more', {}]
                ];
            case 'list':
                return [
                    ['core/group', { className: 'news-article-header news-article-header--list' }, [
                        ['news/post-featured', {}],
                        ['news/post-breaking', {}],
                        ['core/post-title', { level: 4, textAlign: 'left' }],
                        ['news/post-byline', {}]
                    ]],
                    ['core/post-excerpt', { moreText: __('Read more', 'news') }],
                    ['core/read-more', {}]
                ];
            default:
                return [
        ['core/group', { className: 'news-article-header' }, [
            ['news/post-featured', {}],
            ['core/post-title', { level: 1, textAlign: 'left' }],
            ['news/post-byline', {}]
        ]],
        ['core/group', { className: 'news-article-meta' }, [
            ['news/post-last-updated', {}],
            ['news/post-breaking', {}],
            ['news/post-exclusive', {}],
            ['news/post-sponsored', {}],
            ['news/post-live', {}]
        ]],
        ['core/post-featured-image', { sizeSlug: 'large', align: 'wide' }],
        ['core/post-excerpt', {}],
        ['core/read-more', {}]
    ];
        }
    };

    const defaultTemplate = getTemplateForPosition(position);
    
    // Show loading state
    if (!hasResolved) {
        return (
            <div {...blockProps}>
                <div className="news-article-template-loading">
                    <p>{__('Loading article...', 'news')}</p>
                </div>
            </div>
        );
    }
    
    // Show error if no post found
    if (!post) {
        return (
            <div {...blockProps}>
                <div className="news-article-template-error">
                    <p>{__('No article found. Please select a valid article.', 'news')}</p>
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
            <div className="news-article-template-preview">
                <div className="news-article-template-header">
                    <h4>{__('Article Template Preview', 'news')}</h4>
                    <p>{__('Position:', 'news')} <strong>{position}</strong> | {__('Post:', 'news')} <strong>{post.title?.rendered || post.title}</strong></p>
                </div>
                <InnerBlocks 
                    template={hasInnerBlocks ? undefined : defaultTemplate}
                    allowedBlocks={[
                        'core/group',
                        'core/post-title',
                        'core/post-featured-image',
                        'core/post-excerpt',
                        'core/read-more',
                        'news/post-featured',
                        'news/post-byline',
                        'news/post-last-updated',
                        'news/post-breaking',
                        'news/post-exclusive',
                        'news/post-sponsored',
                        'news/post-live'
                    ]}
                    templateLock={false}
                    renderAppender={InnerBlocks.ButtonBlockAppender}
                />
            </div>
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
