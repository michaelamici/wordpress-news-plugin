import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';

import metadata from './block.json';

/**
 * Edit component for the news/post-sponsored block
 */
function Edit({ context }) {
    const blockProps = useBlockProps();
    
    // Get post ID and post type from Query Loop context
    const postId = context?.postId;
    const postType = context?.postType;
    
    // Get the sponsored flag from post meta
    const isSponsored = useSelect((select) => {
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
            return meta?._news_sponsored || false;
        } catch (error) {
            console.warn('Sponsored block: Error fetching data:', error);
            return false;
        }
    }, [postId, postType]);
    
    return (
        <div {...blockProps}>
            <div className="news-post-sponsored-editor">
                {isSponsored ? (
                    <span className="news-post-sponsored-badge">
                        {__('Sponsored', 'news')}
                    </span>
                ) : (
                    <span className="news-post-sponsored-empty">
                        {postId ? 
                            __('[Not sponsored - post ID: ', 'news') + postId + ']' :
                            __('[Sponsored badge will appear here]', 'news')
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
