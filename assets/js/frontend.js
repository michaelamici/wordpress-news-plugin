/**
 * News Plugin Frontend JavaScript
 */

(function($) {
    'use strict';

    // Initialize when document is ready
    $(document).ready(function() {
        NewsFrontend.init();
    });

    // News Frontend object
    window.NewsFrontend = {
        
        /**
         * Initialize frontend functionality
         */
        init: function() {
            this.initBreakingNews();
            this.initSearch();
            this.initLazyLoading();
        },

        /**
         * Initialize breaking news ticker
         */
        initBreakingNews: function() {
            $('.news-breaking-shortcode.news-scrolling').each(function() {
                var $container = $(this);
                var $content = $container.find('.news-breaking-content');
                var speed = parseInt($container.data('speed')) || 50;
                
                if ($content.children().length > 1) {
                    $content.css({
                        'animation': 'news-scroll ' + (speed * 10) + 's linear infinite',
                        'white-space': 'nowrap'
                    });
                }
            });
        },

        /**
         * Initialize search functionality
         */
        initSearch: function() {
            $('.news-search-form').on('submit', function(e) {
                var $form = $(this);
                var query = $form.find('input[name="s"]').val().trim();
                
                if (!query) {
                    e.preventDefault();
                    alert(newsFrontend.strings.error || 'Please enter a search term');
                    return false;
                }
            });
        },

        /**
         * Initialize lazy loading for images
         */
        initLazyLoading: function() {
            if ('IntersectionObserver' in window) {
                var imageObserver = new IntersectionObserver(function(entries, observer) {
                    entries.forEach(function(entry) {
                        if (entry.isIntersecting) {
                            var img = entry.target;
                            img.src = img.dataset.src;
                            img.classList.remove('lazy');
                            imageObserver.unobserve(img);
                        }
                    });
                });

                $('.news-article-thumbnail img.lazy').each(function() {
                    imageObserver.observe(this);
                });
            }
        },

        /**
         * Load more articles via AJAX
         */
        loadMoreArticles: function(container, page, callback) {
            var data = {
                action: 'news_load_more',
                page: page,
                nonce: newsFrontend.nonce
            };

            $.ajax({
                url: newsFrontend.ajaxUrl,
                type: 'POST',
                data: data,
                beforeSend: function() {
                    container.find('.news-loading').show();
                },
                success: function(response) {
                    if (response.success) {
                        container.find('.news-articles-list').append(response.data.html);
                        container.find('.news-loading').hide();
                        
                        if (callback) {
                            callback(response.data);
                        }
                    } else {
                        container.find('.news-loading').hide();
                        alert(response.data.message || 'Error loading articles');
                    }
                },
                error: function() {
                    container.find('.news-loading').hide();
                    alert('Error loading articles');
                }
            });
        },

        /**
         * Filter articles by section
         */
        filterBySection: function(section, container) {
            var data = {
                action: 'news_filter_section',
                section: section,
                nonce: newsFrontend.nonce
            };

            $.ajax({
                url: newsFrontend.ajaxUrl,
                type: 'POST',
                data: data,
                beforeSend: function() {
                    container.find('.news-loading').show();
                },
                success: function(response) {
                    if (response.success) {
                        container.find('.news-articles-list').html(response.data.html);
                        container.find('.news-loading').hide();
                    } else {
                        container.find('.news-loading').hide();
                        alert(response.data.message || 'Error filtering articles');
                    }
                },
                error: function() {
                    container.find('.news-loading').hide();
                    alert('Error filtering articles');
                }
            });
        },

        /**
         * Toggle article meta
         */
        toggleArticleMeta: function(articleId, metaKey, callback) {
            var data = {
                action: 'news_toggle_meta',
                article_id: articleId,
                meta_key: metaKey,
                nonce: newsFrontend.nonce
            };

            $.ajax({
                url: newsFrontend.ajaxUrl,
                type: 'POST',
                data: data,
                success: function(response) {
                    if (response.success && callback) {
                        callback(response.data);
                    }
                },
                error: function() {
                    alert('Error updating article');
                }
            });
        }
    };

    // Add CSS animation for breaking news scroll
    if (!document.getElementById('news-scroll-animation')) {
        var style = document.createElement('style');
        style.id = 'news-scroll-animation';
        style.textContent = `
            @keyframes news-scroll {
                0% { transform: translateX(100%); }
                100% { transform: translateX(-100%); }
            }
        `;
        document.head.appendChild(style);
    }

})(jQuery);
