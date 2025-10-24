import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, InnerBlocks, InspectorControls, BlockContextProvider } from '@wordpress/block-editor';
import { useEntityRecords } from '@wordpress/core-data';
import { __ } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';
import { PanelBody, SelectControl, RangeControl, TextControl } from '@wordpress/components';

import metadata from './block.json';


/**
 * Edit component for the news/front-layout block
 * Enhanced with InspectorControls and simplified hero + list layout
 */
function Edit({ attributes, setAttributes, clientId }) {
    const blockProps = useBlockProps();
    
    // Get query parameters from attributes
    const { postsPerPage, orderBy, order, sectionFilter } = attributes;
    
    // Fetch news articles for preview
    const { records: posts, hasResolved } = useEntityRecords('postType', 'news', {
        per_page: postsPerPage,
        orderby: orderBy,
        order: order,
        status: 'publish'
    });

    const heroPost = posts?.[0];
    const listPosts = posts?.slice(1) || []; // All remaining posts as list

    // Update block attributes with current hero post context
    if (heroPost && (!attributes.postId || attributes.postId !== heroPost.id)) {
        setAttributes({
            postId: heroPost.id,
            postType: 'news',
            position: 'hero'
        });
    }

    // Get the template block for the hero position
    const heroTemplateBlock = useSelect((select) => {
        if (!clientId) return null;
        const { getBlocks } = select('core/block-editor');
        const innerBlocks = getBlocks(clientId);
        return innerBlocks.find(block => block.name === 'news/article-hero-post-template');
    }, [clientId]);

    // Get the template block for the list position
    const listTemplateBlock = useSelect((select) => {
        if (!clientId) return null;
        const { getBlocks } = select('core/block-editor');
        const innerBlocks = getBlocks(clientId);
        return innerBlocks.find(block => block.name === 'news/article-list-post-template');
    }, [clientId]);

    const hasHeroTemplate = !!heroTemplateBlock;
    const hasListTemplate = !!listTemplateBlock;

    const renderListPreview = () => {
        if (!hasResolved) {
            return <p>{__('Loading articles...', 'news')}</p>;
        }
        
        if (!posts || posts.length === 0) {
            return <p>{__('No articles found. Create some news articles to see the preview.', 'news')}</p>;
        }

        // Only show list items as preview (hero is handled by InnerBlocks)
        if (listPosts.length === 0) {
            return null;
        }

        // Show simple preview for list posts
        return (
            <div className="news-front-layout__list">
                <div className="news-list">
                    {listPosts.map((post) => (
                        <div key={post.id} className="news-list-item">
                            <h6>{post.title.rendered}</h6>
                            <div className="news-list-excerpt" dangerouslySetInnerHTML={{ __html: post.excerpt.rendered }} />
                        </div>
                    ))}
                </div>
            </div>
        );
    };

    return (
        <>
            <InspectorControls>
                <PanelBody title={__('Query Settings', 'news')} initialOpen={true}>
                    <RangeControl
                        label={__('Number of posts', 'news')}
                        value={postsPerPage}
                        onChange={(value) => setAttributes({ postsPerPage: value })}
                        min={1}
                        max={20}
                    />
                    <SelectControl
                        label={__('Order by', 'news')}
                        value={orderBy}
                        options={[
                            { label: __('Date', 'news'), value: 'date' },
                            { label: __('Title', 'news'), value: 'title' },
                            { label: __('Modified', 'news'), value: 'modified' },
                            { label: __('Random', 'news'), value: 'rand' }
                        ]}
                        onChange={(value) => setAttributes({ orderBy: value })}
                    />
                    <SelectControl
                        label={__('Order', 'news')}
                        value={order}
                        options={[
                            { label: __('Descending', 'news'), value: 'desc' },
                            { label: __('Ascending', 'news'), value: 'asc' }
                        ]}
                        onChange={(value) => setAttributes({ order: value })}
                    />
                    <TextControl
                        label={__('Section Filter', 'news')}
                        value={sectionFilter}
                        onChange={(value) => setAttributes({ sectionFilter: value })}
                        help={__('Enter section slug to filter posts (optional)', 'news')}
                    />
                </PanelBody>
            </InspectorControls>
            
            <div {...blockProps}>
                <div className="news-front-layout">
                    <InnerBlocks 
                        allowedBlocks={['news/article-hero-post-template', 'news/article-list-post-template']}
                        template={[
                            ['news/article-hero-post-template', {}],
                            ['news/article-list-post-template', {}]
                        ]}
                        templateLock={false}
                        renderAppender={InnerBlocks.ButtonBlockAppender}
                    />
                    
                    {/* List Articles Preview - handled by InnerBlocks */}
                </div>
            </div>
        </>
    );
}

/**
 * Save component for the news/front-layout block
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