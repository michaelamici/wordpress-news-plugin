import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';

import metadata from './block.json';

/**
 * Edit component for the news/post-live block
 */
function Edit({ context }) {
    const blockProps = useBlockProps();
    
    // Get post ID and post type from Query Loop context
    const postId = context?.postId;
    const postType = context?.postType;
    
    // Get the live flag from post meta
    const isLive = useSelect((select) => {
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
            return meta?._news_is_live || false;
        } catch (error) {
            console.warn('Live block: Error fetching data:', error);
            return false;
        }
    }, [postId, postType]);
    
    return (
        <div {...blockProps}>
            <div className="news-post-live-editor">
                {isLive ? (
                    <span className="news-post-live-badge">
                        {__('queef', 'news')}
                    </span>
                ) : (
                    <span className="news-post-live-empty">
                        {postId ? 
                            __('[Not live - post ID: ', 'news') + postId + ']' :
                            __('[Live badge will appear here]', 'news')
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
