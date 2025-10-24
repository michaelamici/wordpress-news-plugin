import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { useSelect } from '@wordpress/data';
import { PanelBody, ToggleControl, SelectControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

import metadata from './block.json';

/**
 * Edit component for the news/article-byline block
 * Displays the article byline from post meta
 */
function Edit({ attributes, setAttributes, context }) {
    const { textAlign, isLink, linkTarget } = attributes;
    
    const blockProps = useBlockProps({
        className: 'wp-block-news-article-byline',
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

    // Get the byline from post meta
    const byline = post?.meta?._news_byline || '';
    
    // Fallback to author display name if no byline
    const author = useSelect((select) => {
        if (!post?.author) return null;
        return select('core').getUser(post.author);
    }, [post?.author]);

    const displayByline = byline || author?.name || '';

    // Show loading state
    if (!post && postId) {
        return (
            <div {...blockProps}>
                <div className="news-article-byline-loading">
                    <p>{__('Loading article byline...', 'news')}</p>
                </div>
            </div>
        );
    }

    // Show error if no post found
    if (!post && postId) {
        return (
            <div {...blockProps}>
                <div className="news-article-byline-error">
                    <p>{__('No article found.', 'news')}</p>
                </div>
            </div>
        );
    }

    // Show placeholder if no post context
    if (!postId) {
        return (
            <div {...blockProps}>
                <div className="news-article-byline-placeholder">
                    <p>{__('News Article Byline', 'news')}</p>
                </div>
            </div>
        );
    }

    // Show empty state if no byline
    if (!displayByline) {
        return (
            <div {...blockProps}>
                <div className="news-article-byline-empty">
                    <p>{__('No byline available', 'news')}</p>
                </div>
            </div>
        );
    }

    const bylineElement = isLink ? (
        <a
            href={post?.link}
            target={linkTarget}
        >
            {displayByline}
        </a>
    ) : (
        displayByline
    );

    return (
        <>
            <InspectorControls>
                <PanelBody title={__('Settings', 'news')}>
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
                        label={__('Make byline a link', 'news')}
                        checked={isLink}
                        onChange={(value) => setAttributes({ isLink: value })}
                    />
                    {isLink && (
                        <SelectControl
                            label={__('Link Target', 'news')}
                            value={linkTarget}
                            options={[
                                { label: __('Same window', 'news'), value: '_self' },
                                { label: __('New window', 'news'), value: '_blank' },
                            ]}
                            onChange={(value) => setAttributes({ linkTarget: value })}
                        />
                    )}
                </PanelBody>
            </InspectorControls>
            <div {...blockProps}>
                {bylineElement}
            </div>
        </>
    );
}

/**
 * Save component for the news/article-byline block
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
