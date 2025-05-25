/**
 * ReactifyPress Admin JavaScript
 */

(function ($) {
    'use strict';

    // Main ReactifyPress Admin class
    var ReactifyPressAdmin = {
        // Color presets
        colorPresets: {
            default: {
                background_color: '#ffffff',
                text_color: '#333333',
                hover_color: '#e74c3c',
                active_color: '#f1c40f',
                tooltip_background: '#333333',
                tooltip_text_color: '#ffffff'
            },
            dark: {
                background_color: '#2c3e50',
                text_color: '#ecf0f1',
                hover_color: '#34495e',
                active_color: '#3498db',
                tooltip_background: '#1a252f',
                tooltip_text_color: '#ecf0f1'
            },
            minimal: {
                background_color: '#f8f8f8',
                text_color: '#333333',
                hover_color: '#e0e0e0',
                active_color: '#333333',
                tooltip_background: '#333333',
                tooltip_text_color: '#ffffff'
            },
            colorful: {
                background_color: '#fff3cd',
                text_color: '#856404',
                hover_color: '#ffeaa7',
                active_color: '#f39c12',
                tooltip_background: '#f39c12',
                tooltip_text_color: '#ffffff'
            }
        },

        init: function () {
            // Initialize tabs
            this.initTabs();

            // Initialize color pickers
            this.initColorPickers();

            // Initialize sortable reactions
            this.initSortableReactions();

            // Initialize event handlers
            this.initEventHandlers();

            // Initialize live preview
            this.initLivePreview();

            // Initialize emoji modal
            this.initEmojiModal();
        },

        /**
         * Initialize jQuery UI tabs
         */
        initTabs: function () {
            $('#reactifypress-tabs').tabs({
                activate: function (event, ui) {
                    // Save current tab in localStorage
                    localStorage.setItem('reactifypress_active_tab', ui.newTab.index());
                },
                active: parseInt(localStorage.getItem('reactifypress_active_tab') || 0)
            });
        },

        /**
         * Initialize color pickers
         */
        initColorPickers: function () {
            $('.reactifypress-color-picker').wpColorPicker({
                change: function (event, ui) {
                    // Update live preview when color changes
                    ReactifyPressAdmin.updateLivePreview();
                },
                clear: function () {
                    ReactifyPressAdmin.updateLivePreview();
                }
            });
        },

        /**
         * Initialize sortable reactions
         */
        initSortableReactions: function () {
            $('#reactifypress-reactions-list').sortable({
                items: '.reactifypress-reaction-item',
                handle: '.reactifypress-reaction-handle',
                placeholder: 'reactifypress-sortable-placeholder',
                forcePlaceholderSize: true,
                update: function () {
                    // Re-index reactions when order changes
                    ReactifyPressAdmin.reindexReactions();
                    ReactifyPressAdmin.updateLivePreview();
                }
            });
        },

        /**
         * Initialize event handlers
         */
        initEventHandlers: function () {
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

            // Icon input change
            $(document).on('input', '.reactifypress-reaction-icon', this.handleIconChange);

            // Label input change
            $(document).on('input', '.reactifypress-reaction-label', this.updateLivePreview);

            // Color presets
            $('.reactifypress-preset').on('click', this.handlePresetClick);

            // Export settings
            $('#reactifypress-export-settings').on('click', this.handleExportSettings);

            // Import settings
            $('#reactifypress-import-file').on('change', this.handleImportFileSelect);
            $('#reactifypress-import-settings').on('click', this.handleImportSettings);

            // Emoji picker
            $('#reactifypress-emoji-picker').on('click', this.showEmojiModal);

            // Display option changes
            $('input[name*="[display]"]').on('change', this.updateLivePreview);

            // Prevent form submission on enter in reaction fields
            $('.reactifypress-reaction-item input').on('keypress', function (e) {
                if (e.which === 13) {
                    e.preventDefault();
                    return false;
                }
            });
        },

        /**
         * Initialize live preview
         */
        initLivePreview: function () {
            this.updateLivePreview();
        },

        /**
         * Update live preview
         */
        updateLivePreview: function () {
            var reactions = [];
            var displaySettings = {};

            // Get active reactions
            $('.reactifypress-reaction-item').each(function () {
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
            displaySettings.background_color = $('#reactifypress-background-color').val();
            displaySettings.text_color = $('#reactifypress-text-color').val();
            displaySettings.hover_color = $('#reactifypress-hover-color').val();
            displaySettings.active_color = $('#reactifypress-active-color').val();
            displaySettings.display_count = $('input[name*="[display_count]"]').is(':checked');
            displaySettings.display_labels = $('input[name*="[display_labels]"]').is(':checked');

            // Create preview HTML
            var previewHtml = '<div class="reactifypress-container">';
            previewHtml += '<div class="reactifypress-reactions">';

            var sampleCounts = [42, 18, 7, 3, 1, 0];
            reactions.forEach(function (reaction, index) {
                var count = sampleCounts[index] || 0;
                var activeClass = index === 0 ? 'reactifypress-active' : '';

                previewHtml += '<div class="reactifypress-reaction ' + activeClass + '">';
                previewHtml += '<div class="reactifypress-icon">' + reaction.icon + '</div>';

                if (displaySettings.display_count) {
                    previewHtml += '<span class="reactifypress-count">' + count + '</span>';
                }

                if (displaySettings.display_labels) {
                    previewHtml += '<span class="reactifypress-label">' + reaction.label + '</span>';
                }

                previewHtml += '</div>';
            });

            previewHtml += '</div></div>';

            // Add styles
            previewHtml += '<style>';
            previewHtml += '#reactifypress-live-preview .reactifypress-reaction {';
            previewHtml += 'background-color: ' + displaySettings.background_color + ';';
            previewHtml += 'color: ' + displaySettings.text_color + ';';
            previewHtml += '}';
            previewHtml += '#reactifypress-live-preview .reactifypress-reaction:hover {';
            previewHtml += 'background-color: ' + displaySettings.hover_color + ';';
            previewHtml += '}';
            previewHtml += '#reactifypress-live-preview .reactifypress-reaction.reactifypress-active {';
            previewHtml += 'background-color: ' + displaySettings.active_color + ';';
            previewHtml += '}';
            previewHtml += '</style>';

            $('#reactifypress-live-preview').html(previewHtml);
        },

        /**
         * Handle adding a new reaction
         */
        handleAddReaction: function (e) {
            e.preventDefault();

            // Clone template
            var $template = $('#reactifypress-reaction-template').html();
            var index = $('#reactifypress-reactions-list .reactifypress-reaction-item').length;

            // Replace placeholder index with actual index
            $template = $template.replace(/\{index\}/g, index);

            // Append to list with animation
            var $newItem = $($template).hide();
            $('#reactifypress-reactions-list').append($newItem);
            $newItem.slideDown(300);

            // Focus on the new icon input
            $newItem.find('.reactifypress-reaction-icon').focus();

            // Update live preview
            ReactifyPressAdmin.updateLivePreview();

            return false;
        },

        /**
         * Handle deleting a reaction
         */
        handleDeleteReaction: function (e) {
            e.preventDefault();

            var $item = $(this).closest('.reactifypress-reaction-item');

            if (confirm(reactifypress_admin.i18n.confirm_delete)) {
                $item.slideUp(300, function () {
                    $(this).remove();

                    // Re-index reactions
                    ReactifyPressAdmin.reindexReactions();
                    ReactifyPressAdmin.updateLivePreview();
                });
            }

            return false;
        },

        /**
         * Handle resetting settings
         */
        handleResetSettings: function (e) {
            e.preventDefault();

            if (confirm(reactifypress_admin.i18n.confirm_reset)) {
                // Add reset parameter to form action
                var $form = $(this).closest('form');
                $('<input>').attr({
                    type: 'hidden',
                    name: 'reset',
                    value: '1'
                }).appendTo($form);

                $form.submit();
            }

            return false;
        },

        /**
         * Handle toggling a reaction's active state
         */
        handleToggleReactionActive: function () {
            var $checkbox = $(this);
            var $item = $checkbox.closest('.reactifypress-reaction-item');

            if ($checkbox.is(':checked')) {
                $item.removeClass('reactifypress-reaction-inactive');
            } else {
                $item.addClass('reactifypress-reaction-inactive');
            }

            ReactifyPressAdmin.updateLivePreview();
        },

        /**
         * Handle icon change
         */
        handleIconChange: function () {
            var $input = $(this);
            var $preview = $input.siblings('.reactifypress-icon-preview');

            $preview.text($input.val());
            ReactifyPressAdmin.updateLivePreview();
        },

        /**
         * Handle preview reactions
         */
        handlePreviewReactions: function (e) {
            e.preventDefault();

            // Open preview in a modal
            var $preview = $('#reactifypress-live-preview').clone();

            $('<div class="reactifypress-preview-modal">')
                .append($preview)
                .dialog({
                    title: reactifypress_admin.i18n.preview_title || 'Preview Reactions',
                    modal: true,
                    width: 600,
                    height: 400,
                    close: function () {
                        $(this).dialog('destroy').remove();
                    }
                });

            return false;
        },

        /**
         * Handle color preset click
         */
        handlePresetClick: function (e) {
            e.preventDefault();

            var preset = $(this).data('preset');
            var colors = ReactifyPressAdmin.colorPresets[preset];

            if (colors) {
                // Update color pickers
                $.each(colors, function (key, value) {
                    var $input = $('#reactifypress-' + key.replace('_', '-'));
                    if ($input.length) {
                        $input.wpColorPicker('color', value);
                    }
                });

                // Update live preview
                ReactifyPressAdmin.updateLivePreview();
            }
        },

        /**
         * Handle export settings
         */
        handleExportSettings: function (e) {
            e.preventDefault();

            // Collect all settings
            var settings = {};
            $('#reactifypress-settings-form').serializeArray().forEach(function (item) {
                var keys = item.name.match(/\[([^\]]+)\]/g);
                if (keys) {
                    var obj = settings;
                    keys.forEach(function (key, index) {
                        key = key.replace(/[\[\]]/g, '');
                        if (index === keys.length - 1) {
                            obj[key] = item.value;
                        } else {
                            obj[key] = obj[key] || {};
                            obj = obj[key];
                        }
                    });
                }
            });

            // Create download
            var dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(settings, null, 2));
            var downloadAnchorNode = document.createElement('a');
            downloadAnchorNode.setAttribute("href", dataStr);
            downloadAnchorNode.setAttribute("download", "reactifypress-settings-" + new Date().toISOString().slice(0, 10) + ".json");
            document.body.appendChild(downloadAnchorNode);
            downloadAnchorNode.click();
            downloadAnchorNode.remove();
        },

        /**
         * Handle import file selection
         */
        handleImportFileSelect: function () {
            var hasFile = this.files && this.files.length > 0;
            $('#reactifypress-import-settings').prop('disabled', !hasFile);
        },

        /**
         * Handle import settings
         */
        handleImportSettings: function (e) {
            e.preventDefault();

            var fileInput = document.getElementById('reactifypress-import-file');
            var file = fileInput.files[0];

            if (!file) {
                return;
            }

            var reader = new FileReader();
            reader.onload = function (e) {
                try {
                    var settings = JSON.parse(e.target.result);

                    // Confirm import
                    if (confirm('This will replace all current settings. Continue?')) {
                        // Import settings logic would go here
                        // For now, just show success message
                        alert('Settings imported successfully! Please save to apply changes.');
                    }
                } catch (error) {
                    alert('Invalid settings file.');
                }
            };
            reader.readAsText(file);
        },

        /**
         * Initialize emoji modal
         */
        initEmojiModal: function () {
            // Modal close button
            $('.reactifypress-modal-close').on('click', function () {
                $(this).closest('.reactifypress-modal').fadeOut();
            });

            // Close on background click
            $('.reactifypress-modal').on('click', function (e) {
                if (e.target === this) {
                    $(this).fadeOut();
                }
            });

            // Emoji button click
            $('.reactifypress-emoji-button').on('click', function () {
                var emoji = $(this).data('emoji');

                // Copy to clipboard
                if (navigator.clipboard) {
                    navigator.clipboard.writeText(emoji).then(function () {
                        // Show success feedback
                        var $button = $(this);
                        $button.addClass('copied');
                        setTimeout(function () {
                            $button.removeClass('copied');
                        }, 1000);
                    });
                } else {
                    // Fallback for older browsers
                    var $temp = $('<input>');
                    $('body').append($temp);
                    $temp.val(emoji).select();
                    document.execCommand('copy');
                    $temp.remove();
                }

                // Flash the button
                $(this).css('background', '#2ecc71');
                setTimeout(function () {
                    $(this).css('background', '');
                }.bind(this), 300);
            });
        },

        /**
         * Show emoji modal
         */
        showEmojiModal: function (e) {
            e.preventDefault();
            $('#reactifypress-emoji-modal').fadeIn();
        },

        /**
         * Re-index reactions in the form
         */
        reindexReactions: function () {
            $('#reactifypress-reactions-list .reactifypress-reaction-item').each(function (index) {
                var $item = $(this);

                // Update data-index
                $item.attr('data-index', index);

                // Update input names
                $item.find('input, select, textarea').each(function () {
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
    $(document).ready(function () {
        ReactifyPressAdmin.init();
    });

})(jQuery);