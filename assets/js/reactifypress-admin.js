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
                    // Color picker change handler
                },
                clear: function () {
                    // Color picker clear handler
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

            // Icon input change
            $(document).on('input', '.reactifypress-reaction-icon', this.handleIconChange);

            // Color presets
            $('.reactifypress-preset').on('click', this.handlePresetClick);

            // Export settings
            $('#reactifypress-export-settings').on('click', this.handleExportSettings);

            // Import settings
            $('#reactifypress-import-file').on('change', this.handleImportFileSelect);
            $('#reactifypress-import-settings').on('click', this.handleImportSettings);

            // Emoji picker
            $('#reactifypress-emoji-picker').on('click', this.showEmojiModal);

            // Prevent form submission on enter in reaction fields
            $('.reactifypress-reaction-item input').on('keypress', function (e) {
                if (e.which === 13) {
                    e.preventDefault();
                    return false;
                }
            });
        },

        /**
         * Initialize emoji modal
         */
        initEmojiModal: function () {
            var self = this;

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
                var $button = $(this);

                // Copy to clipboard
                if (navigator.clipboard) {
                    navigator.clipboard.writeText(emoji).then(function () {
                        // Show success feedback
                        $button.css('background', '#2ecc71');
                        setTimeout(function () {
                            $button.css('background', '');
                        }, 300);
                    });
                } else {
                    // Fallback for older browsers
                    var $temp = $('<input>');
                    $('body').append($temp);
                    $temp.val(emoji).select();
                    document.execCommand('copy');
                    $temp.remove();

                    // Show success feedback
                    $button.css('background', '#2ecc71');
                    setTimeout(function () {
                        $button.css('background', '');
                    }, 300);
                }
            });
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
        },

        /**
         * Handle icon change
         */
        handleIconChange: function () {
            var $input = $(this);
            var $preview = $input.siblings('.reactifypress-icon-preview');

            $preview.text($input.val());
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
                    if (confirm(reactifypress_admin.i18n.confirm_import)) {
                        // Populate form with imported settings
                        ReactifyPressAdmin.importSettings(settings);
                        alert(reactifypress_admin.i18n.import_success);
                    }
                } catch (error) {
                    alert(reactifypress_admin.i18n.import_error);
                }
            };
            reader.readAsText(file);
        },

        /**
         * Import settings into the form
         */
        importSettings: function (settings) {
            // Import reactions
            if (settings.reactifypress_settings && settings.reactifypress_settings.reactions) {
                // Clear existing reactions
                $('#reactifypress-reactions-list').empty();

                // Add imported reactions
                var index = 0;
                $.each(settings.reactifypress_settings.reactions, function (key, reaction) {
                    var $template = $('#reactifypress-reaction-template').html();
                    $template = $template.replace(/\{index\}/g, index);

                    var $item = $($template);
                    $item.find('.reactifypress-reaction-icon').val(reaction.icon);
                    $item.find('.reactifypress-icon-preview').text(reaction.icon);
                    $item.find('.reactifypress-reaction-label').val(reaction.label);

                    if (!reaction.active) {
                        $item.find('.reactifypress-reaction-active').prop('checked', false);
                        $item.addClass('reactifypress-reaction-inactive');
                    }

                    $('#reactifypress-reactions-list').append($item);
                    index++;
                });
            }

            // Import display settings
            if (settings.reactifypress_settings && settings.reactifypress_settings.display) {
                var display = settings.reactifypress_settings.display;

                // Update color pickers
                if (display.background_color) $('#reactifypress-background-color').wpColorPicker('color', display.background_color);
                if (display.text_color) $('#reactifypress-text-color').wpColorPicker('color', display.text_color);
                if (display.hover_color) $('#reactifypress-hover-color').wpColorPicker('color', display.hover_color);
                if (display.active_color) $('#reactifypress-active-color').wpColorPicker('color', display.active_color);
                if (display.tooltip_background) $('#reactifypress-tooltip-background').wpColorPicker('color', display.tooltip_background);
                if (display.tooltip_text_color) $('#reactifypress-tooltip-text-color').wpColorPicker('color', display.tooltip_text_color);

                // Update other display settings
                if (display.position) $('#reactifypress-display-position').val(display.position);
                if (display.alignment) $('#reactifypress-alignment').val(display.alignment);

                $('input[name="reactifypress_settings[display][display_count]"]').prop('checked', display.display_count);
                $('input[name="reactifypress_settings[display][display_labels]"]').prop('checked', display.display_labels);
                $('input[name="reactifypress_settings[display][show_total]"]').prop('checked', display.show_total);
                $('input[name="reactifypress_settings[display][animate]"]').prop('checked', display.animate);
            }
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