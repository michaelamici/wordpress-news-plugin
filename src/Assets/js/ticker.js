/**
 * Breaking News Ticker JavaScript
 * Smooth scrolling ticker animation
 */

(function($) {
    'use strict';
    
    $.fn.newsTicker = function(options) {
        const settings = $.extend({
            speed: 50,
            direction: 'left',
            pauseOnHover: true,
            pauseOnFocus: true,
        }, options);
        
        return this.each(function() {
            const $ticker = $(this);
            const $wrapper = $ticker.find('.news-ticker-wrapper');
            const $content = $ticker.find('.news-ticker-content');
            const $items = $content.find('.news-ticker-item');
            
            if ($items.length === 0) {
                return;
            }
            
            let isPaused = false;
            let animationId;
            
            // Clone items for seamless loop
            $content.append($content.html());
            
            function animate() {
                if (isPaused) {
                    animationId = requestAnimationFrame(animate);
                    return;
                }
                
                const currentPosition = $wrapper.scrollLeft();
                const maxScroll = $content.width() / 2;
                
                if (settings.direction === 'left') {
                    $wrapper.scrollLeft(currentPosition + 1);
                    
                    if (currentPosition >= maxScroll) {
                        $wrapper.scrollLeft(0);
                    }
                } else {
                    $wrapper.scrollLeft(currentPosition - 1);
                    
                    if (currentPosition <= 0) {
                        $wrapper.scrollLeft(maxScroll);
                    }
                }
                
                animationId = requestAnimationFrame(animate);
            }
            
            // Pause on hover
            if (settings.pauseOnHover) {
                $ticker.on('mouseenter', function() {
                    isPaused = true;
                }).on('mouseleave', function() {
                    isPaused = false;
                });
            }
            
            // Pause on focus
            if (settings.pauseOnFocus) {
                $ticker.on('focusin', function() {
                    isPaused = true;
                }).on('focusout', function() {
                    isPaused = false;
                });
            }
            
            // Start animation
            animate();
            
            // Cleanup on destroy
            $ticker.data('newsTicker', {
                destroy: function() {
                    if (animationId) {
                        cancelAnimationFrame(animationId);
                    }
                    $ticker.off('mouseenter mouseleave focusin focusout');
                }
            });
        });
    };
    
    // Auto-initialize tickers on page load
    $(document).ready(function() {
        $('.news-ticker-container').each(function() {
            const $ticker = $(this);
            const speed = parseInt($ticker.find('.news-ticker-wrapper').data('speed')) || 50;
            const direction = $ticker.find('.news-ticker-wrapper').data('direction') || 'left';
            
            $ticker.newsTicker({
                speed: speed,
                direction: direction,
            });
        });
    });
    
})(jQuery);
