import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, InnerBlocks } from '@wordpress/block-editor';
import { useEntityRecords } from '@wordpress/core-data';
import { __ } from '@wordpress/i18n';
import { createContext, useContext } from '@wordpress/element';
import { useSelect } from '@wordpress/data';

import metadata from './block.json';

// Create context for passing layout data to child blocks
const LayoutContext = createContext({});

/**
 * Hero Template Renderer Component
 * Renders the actual Gutenberg template block in the hero position
 */
function HeroTemplateRenderer({ heroPost, templateBlock, clientId }) {
    if (!templateBlock || !heroPost) {
        return null;
    }

    // Get the template block's edit component from the block registry
    const templateEditComponent = useSelect((select) => {
        const { getBlockType } = select('core/blocks');
        const blockType = getBlockType('news/article-post-template');
        return blockType?.edit;
    }, []);

    if (!templateEditComponent) {
        return (
            <div className="news-hero-template-placeholder">
                <div className="news-hero-template-indicator">
                    <span className="news-hero-template-badge">
                        {__('Template Active', 'news')}
                    </span>
                    <p>{__('Template block will render here with real article data', 'news')}</p>
                </div>
            </div>
        );
    }

    // Create context for the template block
    const templateContext = {
        'news/postId': heroPost.id,
        'news/postType': 'news',
        'news/position': 'hero'
    };

    // Create attributes for the template block
    const templateAttributes = {
        postId: heroPost.id,
        postType: 'news',
        position: 'hero'
    };

    // Render the actual template block component
    return (
        <div className="news-hero-template-rendered">
            {templateEditComponent({
                context: templateContext,
                attributes: templateAttributes,
                clientId: `${clientId}-hero-template`,
                setAttributes: () => {}, // No-op for preview
                isSelected: false,
                className: 'news-article-post-template news-article-post-template--hero'
            })}
        </div>
    );
}

/**
 * Edit component for the news/front-layout block
 * Enhanced to provide better context and preview
 */
function Edit({ attributes, setAttributes, clientId }) {
    const blockProps = useBlockProps();
    
    // Fetch news articles for preview
    const { records: posts, hasResolved } = useEntityRecords('postType', 'news', {
        per_page: 10,
        orderby: 'date',
        order: 'desc',
        status: 'publish'
    });

    const heroPost = posts?.[0];
    const gridPosts = posts?.slice(1, 5) || []; // Show 4 grid posts
    const listPosts = posts?.slice(5) || []; // Show remaining as list

    // Update block attributes with current hero post context
    if (heroPost && (!attributes.postId || attributes.postId !== heroPost.id)) {
        setAttributes({
            postId: heroPost.id,
            postType: 'news',
            position: 'hero'
        });
    }

    // Create layout context for child blocks
    const layoutContext = {
        heroPost: heroPost,
        gridPosts: gridPosts,
        listPosts: listPosts,
        totalPosts: posts?.length || 0,
        layoutType: 'front-layout'
    };

    // Get the template block for the hero position
    const heroTemplateBlock = useSelect((select) => {
        if (!clientId) return null;
        const { getBlocks } = select('core/block-editor');
        const innerBlocks = getBlocks(clientId);
        return innerBlocks.find(block => block.name === 'news/article-post-template');
    }, [clientId]);

    const hasHeroTemplate = !!heroTemplateBlock;

    const renderPreview = () => {
        if (!hasResolved) {
            return <p>{__('Loading articles...', 'news')}</p>;
        }
        
        if (!posts || posts.length === 0) {
            return <p>{__('No articles found. Create some news articles to see the preview.', 'news')}</p>;
        }

        return (
            <div className="news-front-layout-preview">
                {/* Hero Article - Show template if available, otherwise default */}
                {heroPost && (
                    <div className="news-front-layout__hero news-front-layout__hero--preview">
                        <h3>{__('Hero Article', 'news')}</h3>
                        {hasHeroTemplate ? (
                            <HeroTemplateRenderer 
                                heroPost={heroPost} 
                                templateBlock={heroTemplateBlock}
                                clientId={clientId}
                            />
                        ) : (
                        <div className="news-hero-article">
                            <h4>{heroPost.title.rendered}</h4>
                            <div className="news-hero-excerpt" dangerouslySetInnerHTML={{ __html: heroPost.excerpt.rendered }} />
                        </div>
                        )}
                    </div>
                )}

                {/* Grid Articles */}
                {gridPosts.length > 0 && (
                    <div className="news-front-layout__grid news-front-layout__grid--preview">
                        <h3>{__('Grid Articles', 'news')}</h3>
                        <div className="news-grid">
                            {gridPosts.map((post) => (
                                <div key={post.id} className="news-grid-item">
                                    <h5>{post.title.rendered}</h5>
                                    <div dangerouslySetInnerHTML={{ __html: post.excerpt.rendered }} />
                                </div>
                            ))}
                        </div>
                    </div>
                )}

                {/* List Articles */}
                {listPosts.length > 0 && (
                    <div className="news-front-layout__list news-front-layout__list--preview">
                        <h3>{__('List Articles', 'news')}</h3>
                        <div className="news-list">
                            {listPosts.map((post) => (
                                <div key={post.id} className="news-list-item">
                                    <h6>{post.title.rendered}</h6>
                                </div>
                            ))}
                        </div>
                    </div>
                )}
            </div>
        );
    };

    return (
        <LayoutContext.Provider value={layoutContext}>
        <div {...blockProps}>
            <div className="news-front-layout-editor">
                <h4>{__('News Front Layout', 'news')}</h4>
                
                {renderPreview()}
                
                <div className="news-front-layout-template-section" onMouseEnter={() => {
                    const container = document.querySelector('.news-template-blocks-container');
                    if (container) container.style.display = 'block';
                }} onMouseLeave={() => {
                    const container = document.querySelector('.news-template-blocks-container');
                    if (container) container.style.display = 'none';
                }}>
                    <h5>{__('Hero Article Template:', 'news')}</h5>
                    <p>{__('Add a template block to customize how the hero article is displayed. The template will be rendered in the hero position above.', 'news')}</p>
                    <div className="news-template-blocks-container" style={{ display: 'none' }}>
                        <InnerBlocks 
                            allowedBlocks={['news/article-post-template']}
                            template={[
                                ['news/article-post-template', {
                                    postId: heroPost?.id || 0,
                                    postType: 'news',
                                    position: 'hero'
                                }]
                            ]}
                            templateLock={false}
                            renderAppender={InnerBlocks.ButtonBlockAppender}
                        />
                    </div>
                </div>
            </div>
        </div>
        </LayoutContext.Provider>
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