import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, InnerBlocks, BlockContextProvider } from '@wordpress/block-editor';
import { useSelect } from '@wordpress/data';
import { useEntityRecord, useEntityRecords } from '@wordpress/core-data';
import { __ } from '@wordpress/i18n';

import metadata from './block.json';

/**
 * Edit component for the news/article-list-post-template block
 * Simplified for list position
 */
function Edit({ context, clientId, attributes, setAttributes }) {
    const blockProps = useBlockProps({
        className: 'news-article-list-post-template'
    });
    
    // Get post context from parent block or attributes
    // Prioritize standard WordPress context keys that core blocks expect
    const postId = context?.postId || context?.['news/postId'] || attributes?.postId;
    const postType = context?.postType || context?.['news/postType'] || attributes?.postType || 'news';
    const position = context?.['news/position'] || attributes?.position || 'list';
    
    // Fetch the post data for preview (single article)
    const { record: post, hasResolved } = useEntityRecord('postType', postType, postId);
    
    // For list position, we need to fetch multiple posts (posts 2+ from the parent query)
    // This is a workaround since we can't get the parent's query results directly
    const { records: allPosts, hasResolved: allPostsResolved } = useEntityRecords('postType', 'news', {
        per_page: 10,
        orderby: 'date',
        order: 'desc',
        status: 'publish'
    });
    
    // Get list posts (skip first post, which is the hero)
    const listPosts = allPosts?.slice(1) || [];
    
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

    // List-specific template (compact)
    const defaultTemplate = [
        ['core/group', { className: 'news-article-list-item' }, [
            ['core/post-featured-image', { sizeSlug: 'thumbnail', align: 'left' }],
            ['core/group', { className: 'news-article-list-content' }, [
                ['core/post-title', { level: 4, textAlign: 'left' }],
                ['core/post-excerpt', { moreText: __('Read more', 'news'), excerptLength: 20 }],
                ['core/post-date', { format: 'M j, Y' }]
            ]]
        ]]
    ];
    
    // Show loading state
    if (!hasResolved && !allPostsResolved) {
        return (
            <div {...blockProps}>
                <div className="news-article-template-loading">
                    <p>{__('Loading list articles...', 'news')}</p>
                </div>
            </div>
        );
    }
    
    // If we're in list position and have multiple posts, render them all
    if (position === 'list' && listPosts.length > 0) {
        return (
            <div {...blockProps}>
                <div className="news-list">
                    {listPosts.map((listPost) => (
                        <BlockContextProvider
                            key={listPost.id}
                            value={{
                                postId: listPost.id,
                                postType: 'news',
                                'news/postId': listPost.id,
                                'news/postType': 'news',
                                'news/position': 'list'
                            }}
                        >
                            <div className="news-list-item-wrapper">
                                <InnerBlocks 
                                    template={hasInnerBlocks ? undefined : defaultTemplate}
                                    allowedBlocks={[
                                        'core/group',
                                        'core/post-title',
                                        'news/article-title',
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
                        </BlockContextProvider>
                    ))}
                </div>
            </div>
        );
    }
    
    // Show error if no post found for single post mode
    if (!post && position !== 'list') {
        return (
            <div {...blockProps}>
                <div className="news-article-template-error">
                    <p>{__('No list article found. Please add a News Front Layout block first.', 'news')}</p>
                </div>
            </div>
        );
    }
    
    // Single post mode (fallback)
    return (
        <div {...blockProps}>
            <InnerBlocks 
                template={hasInnerBlocks ? undefined : defaultTemplate}
                allowedBlocks={[
                    'core/group',
                    'core/post-title',
                    'news/article-title',
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
    );
}

/**
 * Save component for the news/article-list-post-template block
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
