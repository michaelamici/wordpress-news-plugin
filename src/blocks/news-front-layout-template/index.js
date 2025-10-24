import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, InnerBlocks } from '@wordpress/block-editor';
import { useSelect } from '@wordpress/data';
import { useEntityRecord } from '@wordpress/core-data';
import { __ } from '@wordpress/i18n';

import metadata from './block.json';

/**
 * Edit component for the news/article-post-template block
 * Based on WordPress core post-template implementation
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
    
    // Check if this block has any inner blocks
    const hasInnerBlocks = useSelect((select) => {
        const { getBlocks } = select('core/block-editor');
        const innerBlocks = getBlocks(clientId);
        return innerBlocks.length > 0;
    }, [clientId]);

    // Default template that only applies when block is empty
    const defaultTemplate = [
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
    
    return (
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
