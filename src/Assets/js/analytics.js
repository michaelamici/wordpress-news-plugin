/**
 * News Analytics JavaScript
 * Track user interactions and events
 */

(function($) {
    'use strict';
    
    const NewsAnalytics = {
        init: function() {
            this.trackPageView();
            this.trackArticleViews();
            this.trackPlacementInteractions();
            this.trackBreakingNewsViews();
            this.trackScrollDepth();
        },
        
        trackPageView: function() {
            this.sendEvent('page_view', {
                url: window.location.href,
                title: document.title,
                referrer: document.referrer,
            });
        },
        
        trackArticleViews: function() {
            if (newsAnalytics.postId && newsAnalytics.postId > 0) {
                this.sendEvent('article_view', {
                    post_id: newsAnalytics.postId,
                    title: document.title,
                });
            }
        },
        
        trackPlacementInteractions: function() {
            // Track placement impressions
            $('.news-placement').each(function() {
                const $placement = $(this);
                const placementId = $placement.data('placement');
                
                if (placementId) {
                    NewsAnalytics.trackPlacementImpression(placementId);
                }
            });
            
            // Track placement clicks
            $(document).on('click', '.news-placement a, .news-placement button', function(e) {
                const $placement = $(this).closest('.news-placement');
                const placementId = $placement.data('placement');
                
                if (placementId) {
                    NewsAnalytics.trackPlacementClick(placementId);
                }
            });
        },
        
        trackPlacementImpression: function(placementId) {
            // Use Intersection Observer for better performance
            if ('IntersectionObserver' in window) {
                const observer = new IntersectionObserver(function(entries) {
                    entries.forEach(function(entry) {
                        if (entry.isIntersecting) {
                            NewsAnalytics.sendEvent('placement_impression', {
                                placement_id: placementId,
                                visibility: entry.intersectionRatio,
                            });
                            observer.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.5 });
                
                const $placement = $('.news-placement[data-placement="' + placementId + '"]');
                if ($placement.length) {
                    observer.observe($placement[0]);
                }
            } else {
                // Fallback for older browsers
                this.sendEvent('placement_impression', {
                    placement_id: placementId,
                });
            }
        },
        
        trackPlacementClick: function(placementId) {
            this.sendEvent('placement_click', {
                placement_id: placementId,
                timestamp: Date.now(),
            });
        },
        
        trackBreakingNewsViews: function() {
            $('.news-breaking-ticker-widget, .news-placement-breaking').each(function() {
                const $element = $(this);
                
                if ('IntersectionObserver' in window) {
                    const observer = new IntersectionObserver(function(entries) {
                        entries.forEach(function(entry) {
                            if (entry.isIntersecting) {
                                NewsAnalytics.sendEvent('breaking_news_view', {
                                    element: entry.target.className,
                                    visibility: entry.intersectionRatio,
                                });
                            }
                        });
                    }, { threshold: 0.1 });
                    
                    observer.observe($element[0]);
                }
            });
        },
        
        trackScrollDepth: function() {
            let maxScroll = 0;
            const milestones = [25, 50, 75, 90, 100];
            const reachedMilestones = [];
            
            $(window).on('scroll', function() {
                const scrollTop = $(window).scrollTop();
                const docHeight = $(document).height();
                const winHeight = $(window).height();
                const scrollPercent = Math.round((scrollTop / (docHeight - winHeight)) * 100);
                
                if (scrollPercent > maxScroll) {
                    maxScroll = scrollPercent;
                    
                    milestones.forEach(function(milestone) {
                        if (scrollPercent >= milestone && reachedMilestones.indexOf(milestone) === -1) {
                            reachedMilestones.push(milestone);
                            NewsAnalytics.sendEvent('scroll_depth', {
                                depth: milestone,
                                max_depth: maxScroll,
                            });
                        }
                    });
                }
            });
        },
        
        sendEvent: function(eventType, eventData) {
            if (!newsAnalytics.ajaxUrl || !newsAnalytics.nonce) {
                return;
            }
            
            $.ajax({
                url: newsAnalytics.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'news_track_event',
                    event_type: eventType,
                    event_data: eventData,
                    nonce: newsAnalytics.nonce,
                    post_id: newsAnalytics.postId || 0,
                },
                success: function(response) {
                    if (response.success) {
                        console.log('Analytics event tracked:', eventType);
                    }
                },
                error: function(xhr, status, error) {
                    console.warn('Analytics tracking failed:', error);
                }
            });
        }
    };
    
    // Initialize when document is ready
    $(document).ready(function() {
        NewsAnalytics.init();
    });
    
    // Expose to global scope for external use
    window.NewsAnalytics = NewsAnalytics;
    
})(jQuery);
