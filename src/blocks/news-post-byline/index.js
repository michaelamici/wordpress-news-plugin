import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';

import metadata from './block.json';

/**
 * Edit component for the news/post-byline block
 */
function Edit({ context }) {
    const blockProps = useBlockProps();
    
    // Get post ID and post type from context (handle both formats)
    const postId = context?.['news/postId'] || context?.postId;
    const postType = context?.['news/postType'] || context?.postType;
    
    // Get the actual byline from post meta using the correct approach
    const byline = useSelect((select) => {
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
            return meta?._news_byline || '';
        } catch (error) {
            console.warn('Byline block: Error fetching data:', error);
            return '';
        }
    }, [postId, postType]);
    
    return (
        <div {...blockProps}>
            <div className="news-post-byline-editor">
                <strong>{__('Byline:', 'news')}</strong> 
                {byline ? (
                    <span className="news-post-byline-content">{byline}</span>
                ) : (
                    <span className="news-post-byline-empty">
                        {postId ? 
                            __('[No byline set for post ID: ', 'news') + postId + ']' :
                            __('[Article byline will appear here]', 'news')
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
