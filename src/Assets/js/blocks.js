/**
 * News Plugin Blocks JavaScript
 * Minimal block editor functionality
 */

(function() {
    'use strict';
    
    const { registerBlockType } = wp.blocks;
    const { createElement: el } = wp.element;
    const { InspectorControls } = wp.blockEditor;
    const { PanelBody, TextControl, SelectControl, ColorPalette } = wp.components;
    const { __ } = wp.i18n;
    
    // Front Configuration Block
    registerBlockType('news/front-config', {
        title: __('Front Configuration', 'news'),
        description: __('Configure news front regions and queries', 'news'),
        category: 'news',
        icon: 'admin-site',
        supports: {
            html: false,
        },
        attributes: {
            frontId: {
                type: 'string',
                default: 'home',
            },
            regions: {
                type: 'object',
                default: {},
            },
            placements: {
                type: 'object',
                default: {},
            },
        },
        edit: function(props) {
            const { attributes, setAttributes } = props;
            const { frontId, regions, placements } = attributes;
            
            const fronts = newsBlocks?.fronts || {};
            const availableFronts = Object.keys(fronts);
            
            return el('div', { className: 'news-front-config-editor' },
                el(InspectorControls, {},
                    el(PanelBody, { title: __('Front Settings', 'news'), initialOpen: true },
                        el(SelectControl, {
                            label: __('Front', 'news'),
                            value: frontId,
                            options: availableFronts.map(id => ({
                                label: id.charAt(0).toUpperCase() + id.slice(1),
                                value: id,
                            })),
                            onChange: (value) => setAttributes({ frontId: value }),
                        })
                    )
                ),
                el('div', { className: 'news-front-config-preview' },
                    el('h3', {}, __('Front Configuration Preview', 'news')),
                    el('p', {}, __('This block will display the configured front regions and placements.', 'news')),
                    el('p', {}, __('Front ID: ', 'news') + frontId)
                )
            );
        },
        save: function() {
            return null; // Server-side rendering
        },
    });
    
    // Placement Block
    registerBlockType('news/placement', {
        title: __('News Placement', 'news'),
        description: __('Display a placement slot for ads or promos', 'news'),
        category: 'news',
        icon: 'money-alt',
        supports: {
            html: false,
        },
        attributes: {
            placementId: {
                type: 'string',
                default: '',
            },
            content: {
                type: 'string',
                default: '',
            },
            backgroundColor: {
                type: 'string',
                default: '#f0f0f0',
            },
            textColor: {
                type: 'string',
                default: '#333333',
            },
        },
        edit: function(props) {
            const { attributes, setAttributes } = props;
            const { placementId, content, backgroundColor, textColor } = attributes;
            
            const placements = newsBlocks?.placements || {};
            const availablePlacements = Object.keys(placements).map(id => ({
                label: placements[id].name || id,
                value: id,
            }));
            
            return el('div', { className: 'news-placement-editor' },
                el(InspectorControls, {},
                    el(PanelBody, { title: __('Placement Settings', 'news'), initialOpen: true },
                        el(SelectControl, {
                            label: __('Placement ID', 'news'),
                            value: placementId,
                            options: [
                                { label: __('Select a placement...', 'news'), value: '' },
                                ...availablePlacements,
                            ],
                            onChange: (value) => setAttributes({ placementId: value }),
                        }),
                        el(TextControl, {
                            label: __('Content', 'news'),
                            value: content,
                            onChange: (value) => setAttributes({ content: value }),
                            help: __('Optional custom content for this placement', 'news'),
                        })
                    ),
                    el(PanelBody, { title: __('Styling', 'news'), initialOpen: false },
                        el('div', { className: 'news-color-controls' },
                            el('label', {}, __('Background Color', 'news')),
                            el(ColorPalette, {
                                value: backgroundColor,
                                onChange: (value) => setAttributes({ backgroundColor: value }),
                            }),
                            el('label', {}, __('Text Color', 'news')),
                            el(ColorPalette, {
                                value: textColor,
                                onChange: (value) => setAttributes({ textColor: value }),
                            })
                        )
                    )
                ),
                el('div', {
                    className: 'news-placement-preview',
                    style: {
                        backgroundColor: backgroundColor,
                        color: textColor,
                        padding: '1rem',
                        border: '2px dashed #c3c4c7',
                        borderRadius: '4px',
                        textAlign: 'center',
                    }
                },
                    content ? el('div', {}, content) : el('p', {}, __('Placement Preview', 'news'))
                )
            );
        },
        save: function() {
            return null; // Server-side rendering
        },
    });
    
})();
