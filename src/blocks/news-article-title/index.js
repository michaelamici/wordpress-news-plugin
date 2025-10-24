import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, RichText, InspectorControls } from '@wordpress/block-editor';
import { useSelect } from '@wordpress/data';
import { PanelBody, ToggleControl, SelectControl, TextControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

import metadata from './block.json';

/**
 * Edit component for the news/article-title block
 * Similar to core Post Title block but specifically for news articles
 */
function Edit({ attributes, setAttributes, context }) {
    const { level, textAlign, isLink, linkTarget, rel } = attributes;
    
    const blockProps = useBlockProps({
        className: 'wp-block-news-article-title',
        style: { textAlign }
    });

    // Get post context from parent block or attributes
    const postId = context?.postId || context?.['news/postId'];
    const postType = context?.postType || context?.['news/postType'] || 'news';
    
    // Fetch the post data
    const post = useSelect((select) => {
        if (!postId) return null;
        return select('core').getEntityRecord('postType', postType, postId);
    }, [postId, postType]);

    // Get the post title
    const title = post?.title?.rendered || '';

    // Handle title change (for editing)
    const onChangeTitle = (newTitle) => {
        // In a real implementation, this would update the post title
        // For now, we'll just show the current title as read-only
    };

    // Show loading state
    if (!post && postId) {
        return (
            <div {...blockProps}>
                <div className="news-article-title-loading">
                    <p>{__('Loading article title...', 'news')}</p>
                </div>
            </div>
        );
    }

    // Show error if no post found
    if (!post && postId) {
        return (
            <div {...blockProps}>
                <div className="news-article-title-error">
                    <p>{__('No article found.', 'news')}</p>
                </div>
            </div>
        );
    }

    // Show placeholder if no post context
    if (!postId) {
        return (
            <div {...blockProps}>
                <div className="news-article-title-placeholder">
                    <p>{__('News Article Title', 'news')}</p>
                </div>
            </div>
        );
    }

    const TagName = `h${level}`;
    const titleElement = isLink ? (
        <a
            href={post?.link}
            target={linkTarget}
            rel={rel}
        >
            {title}
        </a>
    ) : (
        title
    );

    return (
        <>
            <InspectorControls>
                <PanelBody title={__('Settings', 'news')}>
                    <SelectControl
                        label={__('Heading Level', 'news')}
                        value={level}
                        options={[
                            { label: __('H1', 'news'), value: 1 },
                            { label: __('H2', 'news'), value: 2 },
                            { label: __('H3', 'news'), value: 3 },
                            { label: __('H4', 'news'), value: 4 },
                            { label: __('H5', 'news'), value: 5 },
                            { label: __('H6', 'news'), value: 6 },
                        ]}
                        onChange={(value) => setAttributes({ level: parseInt(value) })}
                    />
                    <SelectControl
                        label={__('Text Alignment', 'news')}
                        value={textAlign}
                        options={[
                            { label: __('Left', 'news'), value: 'left' },
                            { label: __('Center', 'news'), value: 'center' },
                            { label: __('Right', 'news'), value: 'right' },
                        ]}
                        onChange={(value) => setAttributes({ textAlign: value })}
                    />
                    <ToggleControl
                        label={__('Make title a link', 'news')}
                        checked={isLink}
                        onChange={(value) => setAttributes({ isLink: value })}
                    />
                    {isLink && (
                        <>
                            <SelectControl
                                label={__('Link Target', 'news')}
                                value={linkTarget}
                                options={[
                                    { label: __('Same window', 'news'), value: '_self' },
                                    { label: __('New window', 'news'), value: '_blank' },
                                ]}
                                onChange={(value) => setAttributes({ linkTarget: value })}
                            />
                            <TextControl
                                label={__('Link Rel', 'news')}
                                value={rel}
                                onChange={(value) => setAttributes({ rel: value })}
                                help={__('Specify the relationship between the current document and the linked document.', 'news')}
                            />
                        </>
                    )}
                </PanelBody>
            </InspectorControls>
            <TagName {...blockProps}>
                {titleElement}
            </TagName>
        </>
    );
}

/**
 * Save component for the news/article-title block
 */
function Save() {
    return null; // Server-side rendered
}

/**
 * Register the block
 */
registerBlockType(metadata, {
    edit: Edit,
    save: Save,
});
