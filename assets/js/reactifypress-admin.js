/**
 * ReactifyPress Admin JavaScript
 */

(function($) {
    'use strict';

    // Main ReactifyPress Admin class
    var ReactifyPressAdmin = {
        init: function() {
            // Initialize tabs
            this.initTabs();
            
            // Initialize color pickers
            this.initColorPickers();
            
            // Initialize sortable reactions
            this.initSortableReactions();
            
            // Initialize event handlers
            this.initEventHandlers();
        },

        /**
         * Initialize jQuery UI tabs
         */
        initTabs: function() {
            $('#reactifypress-tabs').tabs();
        },

        /**
         * Initialize color pickers
         */
        initColorPickers: function() {
            $('.reactifypress-color-picker').wpColorPicker();
        },

        /**
         * Initialize sortable reactions
         */
        initSortableReactions: function() {
            $('#reactifypress-reactions-list').sortable({
                items: '.reactifypress-reaction-item',
                handle: '.reactifypress-reaction-handle',
                update: function() {
                    // Re-index reactions when order changes
                    ReactifyPressAdmin.reindexReactions();
                }
            });
        },

        /**
         * Initialize event handlers
         */
        initEventHandlers: function() {
            // Add new reaction
            $('#reactifypress-add-reaction').on('click', this.handleAddReaction);
            
            // Delete reaction
            $(document).on('click', '.reactifypress-delete-reaction', this.handleDeleteReaction);
            
            // Reset settings
            $('#reactifypress-reset-settings').on('click', this.handleResetSettings);
            
            // Toggle reaction active state
            $(document).on('change', '.reactifypress-reaction-active', this.handleToggleReactionActive);
            
            // Preview reactions
            $('#reactifypress-preview-reactions').on('click', this.handlePreviewReactions);
        },

        /**
         * Handle adding a new reaction
         * 
         * @param {Event} e Click event
         * @returns {boolean} False to prevent default behavior
         */
        handleAddReaction: function(e) {
            e.preventDefault();
            
            // Clone template
            var $template = $('#reactifypress-reaction-template').html();
            var index = $('#reactifypress-reactions-list .reactifypress-reaction-item').length;
            
            // Replace placeholder index with actual index
            $template = $template.replace(/\{index\}/g, index);
            
            // Append to list
            $('#reactifypress-reactions-list').append($template);
            
            return false;
        },

        /**
         * Handle deleting a reaction
         * 
         * @param {Event} e Click event
         * @returns {boolean} False to prevent default behavior
         */
        handleDeleteReaction: function(e) {
            e.preventDefault();
            
            if (confirm(reactifypress_admin.i18n.confirm_delete)) {
                $(this).closest('.reactifypress-reaction-item').remove();
                
                // Re-index reactions
                ReactifyPressAdmin.reindexReactions();
            }
            
            return false;
        },

        /**
         * Handle resetting settings
         * 
         * @param {Event} e Click event
         * @returns {boolean} False to prevent default behavior
         */
        handleResetSettings: function(e) {
            e.preventDefault();
            
            if (confirm(reactifypress_admin.i18n.confirm_reset)) {
                // Set form action to include reset parameter
                var $form = $(this).closest('form');
                var action = $form.attr('action');
                
                if (action.indexOf('?') > -1) {
                    action += '&reset=1';
                } else {
                    action += '?reset=1';
                }
                
                $form.attr('action', action);
                $form.submit();
            }
            
            return false;
        },

        /**
         * Handle toggling a reaction's active state
         */
        handleToggleReactionActive: function() {
            var $checkbox = $(this);
            var $item = $checkbox.closest('.reactifypress-reaction-item');
            
            if ($checkbox.is(':checked')) {
                $item.removeClass('reactifypress-reaction-inactive');
            } else {
                $item.addClass('reactifypress-reaction-inactive');
            }
        },

        /**
         * Handle previewing reactions
         * 
         * @param {Event} e Click event
         * @returns {boolean} False to prevent default behavior
         */
        handlePreviewReactions: function(e) {
            e.preventDefault();
            
            // Get current settings from form
            var reactions = [];
            
            $('.reactifypress-reaction-item').each(function() {
                var $item = $(this);
                var isActive = $item.find('.reactifypress-reaction-active').is(':checked');
                
                if (isActive) {
                    reactions.push({
                        icon: $item.find('.reactifypress-reaction-icon').val(),
                        label: $item.find('.reactifypress-reaction-label').val()
                    });
                }
            });
            
            // Get display settings
            var backgroundColor = $('#reactifypress-background-color').val();
            var hoverColor = $('#reactifypress-hover-color').val();
            var activeColor = $('#reactifypress-active-color').val();
            var textColor = $('#reactifypress-text-color').val();
            var tooltipBgColor = $('#reactifypress-tooltip-background').val();
            var tooltipTextColor = $('#reactifypress-tooltip-text-color').val();
            
            // Create preview HTML
            var previewHtml = '<div class="reactifypress-preview-container">';
            previewHtml += '<div class="reactifypress-container">';
            previewHtml += '<div class="reactifypress-reactions">';
            
            // Add reaction buttons
            $.each(reactions, function(i, reaction) {
                previewHtml += '<div class="reactifypress-reaction" data-type="reaction-' + i + '">';
                previewHtml += '<div class="reactifypress-icon">' + reaction.icon + '</div>';
                previewHtml += '<span class="reactifypress-count">0</span>';
                previewHtml += '<span class="reactifypress-tooltip">' + reaction.label + '</span>';
                previewHtml += '</div>';
            });
            
            previewHtml += '</div></div>';
            
            // Add custom styles
            previewHtml += '<style>';
            previewHtml += '.reactifypress-preview-container .reactifypress-reaction {';
            previewHtml += '    background-color: ' + backgroundColor + ';';
            previewHtml += '    color: ' + textColor + ';';
            previewHtml += '}';
            previewHtml += '.reactifypress-preview-container .reactifypress-reaction:hover {';
            previewHtml += '    background-color: ' + hoverColor + ';';
            previewHtml += '}';
            previewHtml += '.reactifypress-preview-container .reactifypress-reaction.reactifypress-active {';
            previewHtml += '    background-color: ' + activeColor + ';';
            previewHtml += '}';
            previewHtml += '.reactifypress-preview-container .reactifypress-tooltip {';
            previewHtml += '    background-color: ' + tooltipBgColor + ';';
            previewHtml += '    color: ' + tooltipTextColor + ';';
            previewHtml += '}';
            previewHtml += '.reactifypress-preview-container .reactifypress-tooltip:after {';
            previewHtml += '    border-color: ' + tooltipBgColor + ' transparent transparent transparent;';
            previewHtml += '}';
            previewHtml += '</style>';
            
            // Show preview modal
            var $modal = $('#reactifypress-preview-modal');
            
            if ($modal.length === 0) {
                $('body').append('<div id="reactifypress-preview-modal" title="' + 'Preview Reactions' + '"></div>');
                $modal = $('#reactifypress-preview-modal');
                
                $modal.dialog({
                    autoOpen: false,
                    modal: true,
                    width: 600,
                    height: 300,
                    resizable: false,
                    closeOnEscape: true,
                    close: function() {
                        $(this).dialog('close');
                    }
                });
            }
            
            $modal.html(previewHtml);
            $modal.dialog('open');
            
            // Add click event to preview reactions
            $modal.find('.reactifypress-reaction').on('click', function() {
                $(this).toggleClass('reactifypress-active');
            });
            
            return false;
        },

        /**
         * Re-index reactions in the form
         */
        reindexReactions: function() {
            $('#reactifypress-reactions-list .reactifypress-reaction-item').each(function(index) {
                var $item = $(this);
                
                // Update input names
                $item.find('input, select, textarea').each(function() {
                    var name = $(this).attr('name');
                    if (name) {
                        var newName = name.replace(/reactions\[(\d+)\]/g, 'reactions[' + index + ']');
                        $(this).attr('name', newName);
                    }
                });
            });
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        ReactifyPressAdmin.init();
    });

})(jQuery);
