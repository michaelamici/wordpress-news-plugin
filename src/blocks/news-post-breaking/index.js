import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';

import metadata from './block.json';

/**
 * Edit component for the news/post-breaking block
 */
function Edit({ context }) {
    const blockProps = useBlockProps();
    
    // Get post ID and post type from context (handle both formats)
    const postId = context?.['news/postId'] || context?.postId;
    const postType = context?.['news/postType'] || context?.postType;
    
    // Get the breaking flag from post meta
    const isBreaking = useSelect((select) => {
        if (!postId || !postType) {
            return false;
        }
        
        try {
            // Get the post record using the correct post type from context
            const post = select('core').getEntityRecord('postType', postType, postId);
            if (!post) {
                return false;
            }
            
            // Get the meta field
            const meta = select('core').getEditedEntityRecord('postType', postType, postId)?.meta;
            return meta?._news_breaking || false;
        } catch (error) {
            console.warn('Breaking block: Error fetching data:', error);
            return false;
        }
    }, [postId, postType]);
    
    return (
        <div {...blockProps}>
            <div className="news-post-breaking-editor">
                {isBreaking ? (
                    <span className="news-post-breaking-badge">
                        {__('Breaking', 'news')}
                    </span>
                ) : (
                    <span className="news-post-breaking-empty">
                        {postId ? 
                            __('[Not breaking - post ID: ', 'news') + postId + ']' :
                            __('[Breaking badge will appear here]', 'news')
                        }
                    </span>
                )}
            </div>
        </div>
    );
}

/**
 * Register the block
 */
registerBlockType(metadata, {
    edit: Edit,
});
