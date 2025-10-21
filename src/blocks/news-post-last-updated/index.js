import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';

import metadata from './block.json';

/**
 * Edit component for the news/post-last-updated block
 */
function Edit({ context }) {
    const blockProps = useBlockProps();
    
    // Get post ID and post type from Query Loop context
    const postId = context?.postId;
    const postType = context?.postType;
    
    // Get the last updated date from post meta
    const lastUpdated = useSelect((select) => {
        if (!postId || !postType) {
            return '';
        }
        
        try {
            // Get the post record using the correct post type from context
            const post = select('core').getEntityRecord('postType', postType, postId);
            if (!post) {
                return '';
            }
            
            // Get the meta field
            const meta = select('core').getEditedEntityRecord('postType', postType, postId)?.meta;
            return meta?._news_last_updated || '';
        } catch (error) {
            console.warn('Last updated block: Error fetching data:', error);
            return '';
        }
    }, [postId, postType]);
    
    return (
        <div {...blockProps}>
            <div className="news-post-last-updated-editor">
                {lastUpdated ? (
                    <span className="news-post-last-updated-content">
                        {__('Updated: ', 'news') + lastUpdated}
                    </span>
                ) : (
                    <span className="news-post-last-updated-empty">
                        {postId ? 
                            __('[No update date for post ID: ', 'news') + postId + ']' :
                            __('[Last updated date will appear here]', 'news')
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
