import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, InnerBlocks } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

import metadata from './block.json';

/**
 * Edit component for the news/post-template block
 */
function Edit() {
    const blockProps = useBlockProps();
    
    return (
        <div {...blockProps}>
            <div className="news-post-template-editor">
                <h3>{__('News Post Template', 'news')}</h3>
                <p>{__('This template includes a byline and other news-specific elements.', 'news')}</p>
                <InnerBlocks 
                    allowedBlocks={['news/post-byline', 'core/paragraph', 'core/heading', 'core/image']}
                    template={[
                        ['news/post-byline'],
                        ['core/heading', { level: 2, placeholder: __('Article Title', 'news') }],
                        ['core/paragraph', { placeholder: __('Article content...', 'news') }]
                    ]}
                    templateLock={false}
                />
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
