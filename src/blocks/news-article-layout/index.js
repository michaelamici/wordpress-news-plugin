import { registerBlockVariation } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';

// Hero Grid List variation for News Articles
const HERO_GRID_LIST_VARIATION_NAME = 'news/article-layout-hero-grid-list';

registerBlockVariation('core/query', {
    name: HERO_GRID_LIST_VARIATION_NAME,
    title: __('News Article Layout - Hero Grid List', 'news'),
    description: __('Display news articles in a hero-grid-list layout with featured article as hero', 'news'),
    isActive: ({ namespace, query }) => {
        return (
            namespace === HERO_GRID_LIST_VARIATION_NAME
            && query.postType === 'news'
        );
    },
    icon: (
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M3 3H21C21.5523 3 22 3.44772 22 4V20C22 20.5523 21.5523 21 21 21H3C2.44772 21 2 20.5523 2 20V4C2 3.44772 2.44772 3 3 3Z" fill="currentColor"/>
            <path d="M4 5H20V7H4V5ZM4 9H12V19H4V9ZM14 9H20V11H14V9ZM14 13H20V15H14V13ZM14 17H20V19H14V17Z" fill="white"/>
        </svg>
    ),
    attributes: {
        namespace: HERO_GRID_LIST_VARIATION_NAME,
        query: {
            perPage: 6,
            pages: 0,
            offset: 0,
            postType: 'news',
            order: 'desc',
            orderBy: 'date',
            author: '',
            search: '',
            exclude: [],
            sticky: '',
            inherit: false,
        },
    },
    scope: ['inserter'],
    innerBlocks: [
        [
            'core/post-template',
            {
                layout: {
                    type: 'grid',
                    columnCount: 3,
                },
            },
            [
                ['core/post-featured-image'],
                ['core/post-title'],
                ['core/post-excerpt'],
                ['core/post-date'],
                ['news/news-post-byline'],
            ],
        ],
        ['core/query-pagination'],
        ['core/query-no-results'],
    ],
});

// Hero List variation for News Articles
const HERO_LIST_VARIATION_NAME = 'news/article-layout-hero-list';

registerBlockVariation('core/query', {
    name: HERO_LIST_VARIATION_NAME,
    title: __('News Article Layout - Hero List', 'news'),
    description: __('Display news articles in a hero-list layout with featured article as hero', 'news'),
    isActive: ({ namespace, query }) => {
    return (
            namespace === HERO_LIST_VARIATION_NAME
            && query.postType === 'news'
        );
    },
    icon: (
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M3 3H21C21.5523 3 22 3.44772 22 4V20C22 20.5523 21.5523 21 21 21H3C2.44772 21 2 20.5523 2 20V4C2 3.44772 2.44772 3 3 3Z" fill="currentColor"/>
            <path d="M4 5H20V7H4V5ZM4 9H20V11H4V9ZM4 13H20V15H4V13ZM4 17H20V19H4V17Z" fill="white"/>
        </svg>
    ),
    attributes: {
        namespace: HERO_LIST_VARIATION_NAME,
        query: {
            perPage: 8,
            pages: 0,
            offset: 0,
            postType: 'news',
            order: 'desc',
            orderBy: 'date',
            author: '',
            search: '',
            exclude: [],
            sticky: '',
            inherit: false,
        },
    },
    scope: ['inserter'],
    innerBlocks: [
        [
            'core/post-template',
            {
                layout: {
                    type: 'flex',
                    orientation: 'vertical',
                },
            },
            [
                ['core/post-featured-image'],
                ['core/post-title'],
                ['core/post-excerpt'],
                ['core/post-date'],
                ['news/news-post-byline'],
            ],
        ],
        ['core/query-pagination'],
        ['core/query-no-results'],
    ],
});

// Grid List variation for News Articles
const GRID_LIST_VARIATION_NAME = 'news/article-layout-grid-list';

registerBlockVariation('core/query', {
    name: GRID_LIST_VARIATION_NAME,
    title: __('News Article Layout - Grid List', 'news'),
    description: __('Display news articles in a grid-list layout', 'news'),
    isActive: ({ namespace, query }) => {
        return (
            namespace === GRID_LIST_VARIATION_NAME
            && query.postType === 'news'
        );
    },
    icon: (
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M3 3H21C21.5523 3 22 3.44772 22 4V20C22 20.5523 21.5523 21 21 21H3C2.44772 21 2 20.5523 2 20V4C2 3.44772 2.44772 3 3 3Z" fill="currentColor"/>
            <path d="M4 5H8V7H4V5ZM4 9H8V11H4V9ZM4 13H8V15H4V13ZM4 17H8V19H4V17ZM10 5H20V7H10V5ZM10 9H20V11H10V9ZM10 13H20V15H10V13ZM10 17H20V19H10V17Z" fill="white"/>
        </svg>
    ),
    attributes: {
        namespace: GRID_LIST_VARIATION_NAME,
        query: {
            perPage: 9,
            pages: 0,
            offset: 0,
            postType: 'news',
            order: 'desc',
            orderBy: 'date',
            author: '',
            search: '',
            exclude: [],
            sticky: '',
            inherit: false,
        },
    },
    scope: ['inserter'],
    innerBlocks: [
        [
            'core/post-template',
            {
                layout: {
                    type: 'grid',
                    columnCount: 3,
                },
            },
            [
                ['core/post-featured-image'],
                ['core/post-title'],
                ['core/post-excerpt'],
                ['core/post-date'],
                ['news/news-post-byline'],
            ],
        ],
        ['core/query-pagination'],
        ['core/query-no-results'],
    ],
});

// Featured Grid variation for News Articles
const FEATURED_GRID_VARIATION_NAME = 'news/article-layout-featured-grid';

registerBlockVariation('core/query', {
    name: FEATURED_GRID_VARIATION_NAME,
    title: __('News Article Layout - Featured Grid', 'news'),
    description: __('Display news articles in a featured grid layout highlighting important stories', 'news'),
    isActive: ({ namespace, query }) => {
        return (
            namespace === FEATURED_GRID_VARIATION_NAME
            && query.postType === 'news'
        );
    },
    icon: (
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M3 3H21C21.5523 3 22 3.44772 22 4V20C22 20.5523 21.5523 21 21 21H3C2.44772 21 2 20.5523 2 20V4C2 3.44772 2.44772 3 3 3Z" fill="currentColor"/>
            <path d="M4 5H20V7H4V5ZM4 9H12V19H4V9ZM14 9H20V11H14V9ZM14 13H20V15H14V13ZM14 17H20V19H14V17Z" fill="white"/>
        </svg>
    ),
    attributes: {
        namespace: FEATURED_GRID_VARIATION_NAME,
        query: {
            perPage: 7,
            pages: 0,
            offset: 0,
            postType: 'news',
            order: 'desc',
            orderBy: 'date',
            author: '',
            search: '',
            exclude: [],
            sticky: '',
            inherit: false,
        },
    },
    scope: ['inserter'],
    innerBlocks: [
        [
            'core/post-template',
            {
                layout: {
                    type: 'grid',
                    columnCount: 2,
                },
            },
            [
                ['news/news-post-featured'],
                ['core/post-featured-image'],
                ['core/post-title'],
                ['core/post-excerpt'],
                ['core/post-date'],
                ['news/news-post-byline'],
            ],
        ],
        ['core/query-pagination'],
        ['core/query-no-results'],
    ],
});