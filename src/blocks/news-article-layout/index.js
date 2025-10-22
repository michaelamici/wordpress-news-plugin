import { registerBlockType } from '@wordpress/blocks';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, RangeControl, SelectControl, ToggleControl, Spinner } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { useState, useEffect } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';

import metadata from './block.json';

// Article Layout Component
function ArticleLayout({ articles, showExcerpt, showDate }) {
    if (!articles || articles.total === 0) {
        return (
            <div className="news-article-layout-empty">
                <p>{__('No articles found.', 'news')}</p>
            </div>
        );
    }

    const { hero, grid, list } = articles;

    return (
        <div className="news-article-layout">
            {/* Hero Section */}
            {hero && (
                <div className="news-hero">
                    <article className="news-hero-article">
                        {hero.image && (
                            <div className="news-hero-image">
                                <a href={hero.url}>
                                    <img src={hero.image} alt={hero.title} />
                                </a>
                            </div>
                        )}
                        <div className="news-hero-content">
                            <h2 className="news-hero-title">
                                <a href={hero.url}>{hero.title}</a>
                            </h2>
                            {showExcerpt && hero.excerpt && (
                                <div className="news-hero-excerpt">{hero.excerpt}</div>
                            )}
                            {showDate && hero.date && (
                                <div className="news-hero-date">{hero.date}</div>
                            )}
                        </div>
                    </article>
                </div>
            )}

            {/* Grid Section */}
            {grid && grid.length > 0 && (
                <div className="news-grid">
                    {grid.map((post) => (
                        <article key={post.id} className="news-grid-item">
                            {post.image_medium && (
                                <div className="news-grid-image">
                                    <a href={post.url}>
                                        <img src={post.image_medium} alt={post.title} />
                                    </a>
                                </div>
                            )}
                            <div className="news-grid-content">
                                <h3 className="news-grid-title">
                                    <a href={post.url}>{post.title}</a>
                                </h3>
                                {showExcerpt && post.excerpt && (
                                    <div className="news-grid-excerpt">{post.excerpt}</div>
                                )}
                                {showDate && post.date && (
                                    <div className="news-grid-date">{post.date}</div>
                                )}
                            </div>
                        </article>
                    ))}
                </div>
            )}

            {/* List Section */}
            {list && list.length > 0 && (
                <div className="news-list">
                    {list.map((post) => (
                        <article key={post.id} className="news-list-item">
                            <h4 className="news-list-title">
                                <a href={post.url}>{post.title}</a>
                            </h4>
                            {showExcerpt && post.excerpt && (
                                <div className="news-list-excerpt">{post.excerpt}</div>
                            )}
                            {showDate && post.date && (
                                <div className="news-list-date">{post.date}</div>
                            )}
                        </article>
                    ))}
                </div>
            )}
        </div>
    );
}

registerBlockType(metadata, {
    edit: function Edit({ attributes, setAttributes }) {
        const blockProps = useBlockProps();
        const { gridCount, sectionFilter, showExcerpt, showDate } = attributes;
        const [articles, setArticles] = useState(null);
        const [loading, setLoading] = useState(true);
        const [error, setError] = useState(null);

        // Get available sections for filter
        const sections = useSelect((select) => {
            const { getEntityRecords } = select('core');
            return getEntityRecords('taxonomy', 'news_section', {
                per_page: -1,
                orderby: 'name',
                order: 'asc'
            });
        }, []);

        const sectionOptions = [
            { label: __('All Sections', 'news'), value: '' },
            ...(sections || []).map(section => ({
                label: section.name,
                value: section.slug
            }))
        ];

        // Fetch articles when attributes change
        useEffect(() => {
            const fetchArticles = async () => {
                setLoading(true);
                setError(null);
                
                try {
                    const response = await apiFetch({
                        path: `/index.php?rest_route=/news/v1/layout&grid_count=${gridCount}&section_filter=${sectionFilter}&show_excerpt=${showExcerpt}&show_date=${showDate}`,
                    });
                    setArticles(response);
                } catch (err) {
                    console.error('News Plugin: API error:', err);
                    setError(err.message || __('Failed to load articles', 'news'));
                } finally {
                    setLoading(false);
                }
            };

            fetchArticles();
        }, [gridCount, sectionFilter, showExcerpt, showDate]);

        return (
            <>
                <InspectorControls>
                    <PanelBody title={__('Layout Settings', 'news')}>
                        <RangeControl
                            label={__('Grid Items Count', 'news')}
                            value={gridCount}
                            onChange={(value) => setAttributes({ gridCount: value })}
                            min={1}
                            max={6}
                        />
                    </PanelBody>
                    
                    <PanelBody title={__('Filter Settings', 'news')}>
                        <SelectControl
                            label={__('Section Filter', 'news')}
                            value={sectionFilter}
                            options={sectionOptions}
                            onChange={(value) => setAttributes({ sectionFilter: value })}
                        />
                    </PanelBody>
                    
                    <PanelBody title={__('Display Settings', 'news')}>
                        <ToggleControl
                            label={__('Show Excerpt', 'news')}
                            checked={showExcerpt}
                            onChange={(value) => setAttributes({ showExcerpt: value })}
                        />
                        <ToggleControl
                            label={__('Show Date', 'news')}
                            checked={showDate}
                            onChange={(value) => setAttributes({ showDate: value })}
                        />
                    </PanelBody>
                </InspectorControls>

                <div {...blockProps}>
                    {loading && (
                        <div className="news-article-layout-loading">
                            <Spinner />
                            <p>{__('Loading articles...', 'news')}</p>
                        </div>
                    )}
                    
                    {error && (
                        <div className="news-article-layout-error">
                            <p>{__('Error:', 'news')} {error}</p>
                        </div>
                    )}
                    
                    {!loading && !error && (
                        <ArticleLayout 
                            articles={articles} 
                            showExcerpt={showExcerpt}
                            showDate={showDate}
                        />
                    )}
                </div>
            </>
        );
    },

    save: function Save() {
        return null; // Dynamic block
    }
});