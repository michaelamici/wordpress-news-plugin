import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

import metadata from './block.json';

/**
 * Edit component for the news/post-byline block
 */
function Edit() {
    const blockProps = useBlockProps();
    
    return (
        <div {...blockProps}>
            <div className="news-post-byline-editor">
                <strong>{__('Byline:', 'news')}</strong> 
                <span className="news-post-byline-preview">
                    {__('[Will display the article byline on the frontend]', 'news')}
                </span>
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
