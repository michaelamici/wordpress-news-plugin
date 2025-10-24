import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, InnerBlocks } from '@wordpress/block-editor';
import { useEntityRecords } from '@wordpress/core-data';
import { __ } from '@wordpress/i18n';
import { createContext } from '@wordpress/element';

import metadata from './block.json';

/**
 * Edit component for the news/front-layout block
 */
function Edit({ attributes, setAttributes }) {
    const blockProps = useBlockProps();
    
    // Fetch news articles for preview
    const { records: posts, hasResolved } = useEntityRecords('postType', 'news', {
        per_page: 10,
        orderby: 'date',
        order: 'desc',
        status: 'publish'
    });

    const heroPost = posts?.[0];

    // Update block attributes with current hero post context
    if (heroPost && (!attributes.postId || attributes.postId !== heroPost.id)) {
        setAttributes({
            postId: heroPost.id,
            postType: 'news',
            position: 'hero'
        });
    }

    // Debug: Log current attributes
    console.log('Front layout attributes:', attributes);
    console.log('Hero post:', heroPost);

    const renderPreview = () => {
        if (!hasResolved) {
            return <p>{__('Loading articles...', 'news')}</p>;
        }
        
        if (!posts || posts.length === 0) {
            return <p>{__('No articles found. Create some news articles to see the preview.', 'news')}</p>;
        }

        const gridPosts = posts.slice(1, 5); // Show 4 grid posts
        const listPosts = posts.slice(5); // Show remaining as list

        return (
            <div className="news-front-layout-preview">
                {/* Hero Article - This will be replaced by template block if present */}
                {heroPost && (
                    <div className="news-front-layout__hero news-front-layout__hero--preview">
                        <h3>{__('Hero Article', 'news')}</h3>
                        <div className="news-hero-article">
                            <h4>{heroPost.title.rendered}</h4>
                            <div className="news-hero-excerpt" dangerouslySetInnerHTML={{ __html: heroPost.excerpt.rendered }} />
                        </div>
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
        <div {...blockProps}>
            <div className="news-front-layout-editor">
                <h4>{__('News Front Layout', 'news')}</h4>
                
                {renderPreview()}
                
                <div className="news-front-layout-template-section">
                    <h5>{__('Hero Article Template:', 'news')}</h5>
                    <p>{__('Add a template block to customize how the hero article is displayed. If no template is present, the default hero layout will be used.', 'news')}</p>
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