/**
 * ReactifyPress Frontend JavaScript
 */

(function ($) {
    'use strict';

    // Main ReactifyPress class
    var ReactifyPress = {
        // Cache DOM elements
        cache: {},

        // Track processing state
        isProcessing: false,

        init: function () {
            // Cache commonly used elements
            this.cacheElements();

            // Initialize event handlers
            this.initEventHandlers();

            // Initialize keyboard navigation
            this.initKeyboardNavigation();

            // Load initial reactions if needed
            this.loadInitialReactions();
        },

        /**
         * Cache DOM elements
         */
        cacheElements: function () {
            this.cache.$body = $('body');
            this.cache.$containers = $('.reactifypress-container');
        },

        /**
         * Initialize event handlers
         */
        initEventHandlers: function () {
            // Handle reaction click
            $(document).on('click', '.reactifypress-reaction', this.handleReactionClick.bind(this));

            // Handle reaction keyboard activation
            $(document).on('keypress', '.reactifypress-reaction', this.handleReactionKeypress.bind(this));

            // Prevent double clicks
            $(document).on('dblclick', '.reactifypress-reaction', function (e) {
                e.preventDefault();
            });

            // Add hover effects for touch devices
            if ('ontouchstart' in window) {
                $(document).on('touchstart', '.reactifypress-reaction', this.handleTouchStart);
                $(document).on('touchend', '.reactifypress-reaction', this.handleTouchEnd);
            }
        },

        /**
         * Initialize keyboard navigation
         */
        initKeyboardNavigation: function () {
            $(document).on('keydown', '.reactifypress-reaction', function (e) {
                var $this = $(this);
                var $reactions = $this.parent().find('.reactifypress-reaction');
                var currentIndex = $reactions.index($this);

                switch (e.keyCode) {
                    case 37: // Arrow left
                        if (currentIndex > 0) {
                            $reactions.eq(currentIndex - 1).focus();
                        }
                        break;
                    case 39: // Arrow right
                        if (currentIndex < $reactions.length - 1) {
                            $reactions.eq(currentIndex + 1).focus();
                        }
                        break;
                }
            });
        },

        /**
         * Load initial reactions for visible containers
         */
        loadInitialReactions: function () {
            // This can be used to lazy-load reaction counts
            // Currently reactions are loaded server-side
        },

        /**
         * Handle reaction click
         * 
         * @param {Event} e Click event
         * @returns {boolean} False to prevent default behavior
         */
        handleReactionClick: function (e) {
            e.preventDefault();

            // Check if login is required
            if (reactifypress.require_login && !reactifypress.user_logged_in) {
                this.showLoginMessage();
                return false;
            }

            var $reaction = $(e.currentTarget);
            this.processReaction($reaction);

            return false;
        },

        /**
         * Handle reaction keypress (for accessibility)
         * 
         * @param {Event} e Keypress event
         * @returns {boolean} False to prevent default behavior
         */
        handleReactionKeypress: function (e) {
            if (e.keyCode === 13 || e.keyCode === 32) { // Enter or Space
                e.preventDefault();

                var $reaction = $(e.currentTarget);
                this.processReaction($reaction);

                return false;
            }
        },

        /**
         * Process reaction
         * 
         * @param {jQuery} $reaction Reaction element
         */
        processReaction: function ($reaction) {
            var $container = $reaction.closest('.reactifypress-container');
            var postId = $container.data('post-id');
            var reactionType = $reaction.data('type');

            // Don't process if already processing
            if (this.isProcessing || $container.hasClass('reactifypress-processing')) {
                return;
            }

            // Set processing state
            this.isProcessing = true;
            $container.addClass('reactifypress-processing');

            // Show loading indicator
            $container.find('.reactifypress-loading').fadeIn(200);

            // Add optimistic UI update
            this.optimisticUpdate($reaction, $container);

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
                success: function (response) {
                    if (response.success) {
                        // Update reaction counts
                        ReactifyPress.updateReactionCounts($container, response.data.counts);

                        // Update active reaction
                        ReactifyPress.updateActiveReaction($container, response.data.current_reaction);

                        // Show success feedback
                        ReactifyPress.showSuccessFeedback($reaction);

                        // Update total count if exists
                        ReactifyPress.updateTotalCount($container, response.data.counts);
                    } else {
                        // Revert optimistic update
                        ReactifyPress.revertOptimisticUpdate($reaction, $container);

                        // Show error message
                        ReactifyPress.showErrorMessage(response.data.message);
                    }
                },
                error: function () {
                    // Revert optimistic update
                    ReactifyPress.revertOptimisticUpdate($reaction, $container);

                    // Show error message
                    ReactifyPress.showErrorMessage(reactifypress.i18n.error);
                },
                complete: function () {
                    // Remove processing state
                    ReactifyPress.isProcessing = false;
                    $container.removeClass('reactifypress-processing');

                    // Hide loading indicator
                    $container.find('.reactifypress-loading').fadeOut(200);
                }
            });
        },

        /**
         * Optimistic UI update
         * 
         * @param {jQuery} $reaction Reaction element
         * @param {jQuery} $container Container element
         */
        optimisticUpdate: function ($reaction, $container) {
            var wasActive = $reaction.hasClass('reactifypress-active');
            var $count = $reaction.find('.reactifypress-count');
            var currentCount = parseInt($count.text()) || 0;

            // Store original state for revert
            $reaction.data('original-state', {
                active: wasActive,
                count: currentCount
            });

            if (wasActive) {
                // Remove reaction
                $reaction.removeClass('reactifypress-active');
                $count.text(Math.max(0, currentCount - 1));
            } else {
                // Add reaction
                $container.find('.reactifypress-reaction').removeClass('reactifypress-active');
                $reaction.addClass('reactifypress-active');
                $count.text(currentCount + 1);
            }

            // Add animation class
            $reaction.addClass('reactifypress-animating');
        },

        /**
         * Revert optimistic update
         * 
         * @param {jQuery} $reaction Reaction element
         * @param {jQuery} $container Container element
         */
        revertOptimisticUpdate: function ($reaction, $container) {
            var originalState = $reaction.data('original-state');

            if (originalState) {
                var $count = $reaction.find('.reactifypress-count');

                // Restore original state
                if (originalState.active) {
                    $reaction.addClass('reactifypress-active');
                } else {
                    $reaction.removeClass('reactifypress-active');
                }

                $count.text(originalState.count);

                // Remove stored state
                $reaction.removeData('original-state');
            }
        },

        /**
         * Update reaction counts
         * 
         * @param {jQuery} $container The reaction container
         * @param {Object} counts The updated counts
         */
        updateReactionCounts: function ($container, counts) {
            // Loop through each reaction and update count
            $container.find('.reactifypress-reaction').each(function () {
                var $reaction = $(this);
                var type = $reaction.data('type');
                var count = counts[type] || 0;
                var $count = $reaction.find('.reactifypress-count');

                // Animate count change
                var currentCount = parseInt($count.text()) || 0;

                if (currentCount !== count) {
                    $count.prop('Counter', currentCount).animate({
                        Counter: count
                    }, {
                        duration: 300,
                        easing: 'swing',
                        step: function (now) {
                            $count.text(Math.ceil(now));
                        }
                    });
                }

                // Update has-reactions class
                if (count > 0) {
                    $reaction.addClass('reactifypress-has-reactions');
                } else {
                    $reaction.removeClass('reactifypress-has-reactions');
                }
            });
        },

        /**
         * Update active reaction
         * 
         * @param {jQuery} $container The reaction container
         * @param {string} activeType The active reaction type
         */
        updateActiveReaction: function ($container, activeType) {
            // Remove active class from all reactions
            $container.find('.reactifypress-reaction').removeClass('reactifypress-active');

            // Add active class to current reaction
            if (activeType) {
                $container.find('.reactifypress-reaction[data-type="' + activeType + '"]').addClass('reactifypress-active');
            }
        },

        /**
         * Update total count
         * 
         * @param {jQuery} $container The reaction container
         * @param {Object} counts The reaction counts
         */
        updateTotalCount: function ($container, counts) {
            var $total = $container.find('.reactifypress-total strong');

            if ($total.length) {
                var totalCount = 0;
                for (var type in counts) {
                    totalCount += counts[type];
                }

                $total.text(totalCount.toLocaleString());
            }
        },

        /**
         * Show success feedback
         * 
         * @param {jQuery} $reaction Reaction element
         */
        showSuccessFeedback: function ($reaction) {
            // Remove animation class
            $reaction.removeClass('reactifypress-animating');

            // Add success animation
            $reaction.addClass('reactifypress-success');

            setTimeout(function () {
                $reaction.removeClass('reactifypress-success');
            }, 500);

            // Trigger custom event
            $reaction.trigger('reactifypress:success');
        },

        /**
         * Show error message
         * 
         * @param {string} message Error message
         */
        showErrorMessage: function (message) {
            // Create toast notification
            var $toast = $('<div class="reactifypress-toast reactifypress-toast-error">' + message + '</div>');

            $('body').append($toast);

            // Animate in
            setTimeout(function () {
                $toast.addClass('reactifypress-toast-visible');
            }, 10);

            // Remove after delay
            setTimeout(function () {
                $toast.removeClass('reactifypress-toast-visible');
                setTimeout(function () {
                    $toast.remove();
                }, 300);
            }, 3000);
        },

        /**
         * Show login message
         */
        showLoginMessage: function () {
            var loginUrl = reactifypress.login_url || '/wp-login.php';
            var message = reactifypress.i18n.login_required;

            // Create login prompt
            var $prompt = $('<div class="reactifypress-login-prompt">' +
                '<p>' + message + '</p>' +
                '<a href="' + loginUrl + '" class="reactifypress-login-button">' +
                'Login' +
                '</a>' +
                '</div>');

            // Show as modal or redirect
            if (reactifypress.login_modal) {
                // Show modal (implement modal functionality)
                this.showModal($prompt);
            } else {
                // Redirect to login
                window.location.href = loginUrl + '?redirect_to=' + encodeURIComponent(window.location.href);
            }
        },

        /**
         * Handle touch start
         * 
         * @param {Event} e Touch event
         */
        handleTouchStart: function (e) {
            $(this).addClass('reactifypress-touch');
        },

        /**
         * Handle touch end
         * 
         * @param {Event} e Touch event
         */
        handleTouchEnd: function (e) {
            $(this).removeClass('reactifypress-touch');
        }
    };

    // Initialize on document ready
    $(document).ready(function () {
        ReactifyPress.init();
    });

    // Expose for external use
    window.ReactifyPress = ReactifyPress;

})(jQuery);