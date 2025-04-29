/**
 * ReactifyPress Frontend JavaScript
 */

(function($) {
    'use strict';

    // Main ReactifyPress class
    var ReactifyPress = {
        init: function() {
            // Initialize event handlers
            this.initEventHandlers();
        },

        /**
         * Initialize event handlers
         */
        initEventHandlers: function() {
            // Handle reaction click
            $(document).on('click', '.reactifypress-reaction', this.handleReactionClick);
        },

        /**
         * Handle reaction click
         * 
         * @param {Event} e Click event
         * @returns {boolean} False to prevent default behavior
         */
        handleReactionClick: function(e) {
            e.preventDefault();

            var $reaction = $(this);
            var $container = $reaction.closest('.reactifypress-container');
            var postId = $container.data('post-id');
            var reactionType = $reaction.data('type');

            // Don't process if already processing
            if ($container.hasClass('reactifypress-processing')) {
                return false;
            }

            // Add processing class
            $container.addClass('reactifypress-processing');

            // Send AJAX request
            $.ajax({
                url: reactifypress.ajax_url,
                type: 'POST',
                data: {
                    action: 'reactifypress_add_reaction',
                    nonce: reactifypress.nonce,
                    post_id: postId,
                    reaction_type: reactionType
                },
                success: function(response) {
                    // Remove processing class
                    $container.removeClass('reactifypress-processing');

                    if (response.success) {
                        // Update reaction counts
                        ReactifyPress.updateReactionCounts($container, response.data.counts);
                        
                        // Update active reaction
                        ReactifyPress.updateActiveReaction($container, response.data.current_reaction);
                    } else {
                        // Show error message
                        console.error('ReactifyPress:', response.data.message);
                        alert(reactifypress.i18n.error);
                    }
                },
                error: function() {
                    // Remove processing class
                    $container.removeClass('reactifypress-processing');
                    
                    // Show error message
                    alert(reactifypress.i18n.error);
                }
            });

            return false;
        },

        /**
         * Update reaction counts
         * 
         * @param {jQuery} $container The reaction container
         * @param {Object} counts The updated counts
         */
        updateReactionCounts: function($container, counts) {
            // Loop through each reaction and update count
            $container.find('.reactifypress-reaction').each(function() {
                var $reaction = $(this);
                var type = $reaction.data('type');
                var count = counts[type] || 0;
                
                // Update count
                $reaction.find('.reactifypress-count').text(count);
            });
        },

        /**
         * Update active reaction
         * 
         * @param {jQuery} $container The reaction container
         * @param {string} activeType The active reaction type
         */
        updateActiveReaction: function($container, activeType) {
            // Remove active class from all reactions
            $container.find('.reactifypress-reaction').removeClass('reactifypress-active');
            
            // Add active class to current reaction
            if (activeType) {
                $container.find('.reactifypress-reaction[data-type="' + activeType + '"]').addClass('reactifypress-active');
            }
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        ReactifyPress.init();
    });

})(jQuery);
