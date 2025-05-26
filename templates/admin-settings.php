<?php

/**
 * Admin settings template
 *
 * @package ReactifyPress
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Handle form submission feedback
$saved = isset($_GET['settings-updated']) && $_GET['settings-updated'];
$reset = isset($_GET['reset']) && $_GET['reset'];
?>

<div class="wrap reactifypress-settings-page">
    <h1>
        <?php echo esc_html(get_admin_page_title()); ?>
        <span class="reactifypress-version">v<?php echo REACTIFYPRESS_VERSION; ?></span>
    </h1>

    <?php if ($saved) : ?>
        <div class="notice notice-success is-dismissible">
            <p><?php esc_html_e('Settings saved successfully.', 'reactifypress'); ?></p>
        </div>
    <?php endif; ?>

    <?php if ($reset) : ?>
        <div class="notice notice-warning is-dismissible">
            <p><?php esc_html_e('Settings have been reset to defaults.', 'reactifypress'); ?></p>
        </div>
    <?php endif; ?>

    <?php settings_errors('reactifypress_settings'); ?>

    <form method="post" action="options.php" id="reactifypress-settings-form">
        <?php
        settings_fields('reactifypress_settings');
        $settings = reactifypress()->settings->get_settings();
        ?>

        <div id="reactifypress-tabs" class="reactifypress-tabs">
            <ul>
                <li><a href="#tab-reactions"><span class="dashicons dashicons-smiley"></span> <?php esc_html_e('Reaktionen', 'reactifypress'); ?></a></li>
                <li><a href="#tab-appearance"><span class="dashicons dashicons-admin-appearance"></span> <?php esc_html_e('Aussehen', 'reactifypress'); ?></a></li>
                <li><a href="#tab-display"><span class="dashicons dashicons-visibility"></span> <?php esc_html_e('Anzeigeeinstellungen', 'reactifypress'); ?></a></li>
                <li><a href="#tab-post-types"><span class="dashicons dashicons-admin-post"></span> <?php esc_html_e('Beitragstypen', 'reactifypress'); ?></a></li>
                <li><a href="#tab-advanced"><span class="dashicons dashicons-admin-tools"></span> <?php esc_html_e('Erweitert', 'reactifypress'); ?></a></li>
            </ul>

            <!-- Reactions Tab -->
            <div id="tab-reactions">
                <div class="reactifypress-section-header">
                    <h2><?php esc_html_e('Manage Reactions', 'reactifypress'); ?></h2>
                    <p><?php esc_html_e('Configure the reactions that will be available on your posts. Drag to reorder.', 'reactifypress'); ?></p>
                </div>

                <div class="reactifypress-reactions-toolbar">
                    <button type="button" id="reactifypress-add-reaction" class="button button-primary">
                        <span class="dashicons dashicons-plus-alt"></span>
                        <?php esc_html_e('Neue Reaktion hinzufügen', 'reactifypress'); ?>
                    </button>

                    <button type="button" id="reactifypress-preview-reactions" class="button">
                        <span class="dashicons dashicons-visibility"></span>
                        <?php esc_html_e('Vorschau', 'reactifypress'); ?>
                    </button>

                    <div class="reactifypress-emoji-picker-wrapper">
                        <button type="button" id="reactifypress-emoji-picker" class="button">
                            <span class="dashicons dashicons-editor-help"></span>
                            <?php esc_html_e('Emoji Reference', 'reactifypress'); ?>
                        </button>
                    </div>
                </div>

                <div id="reactifypress-reactions-list" class="reactifypress-reactions-list">
                    <?php
                    $i = 0;
                    foreach ($settings['reactions'] as $key => $reaction) :
                        $inactive_class = !$reaction['active'] ? 'reactifypress-reaction-inactive' : '';
                    ?>
                        <div class="reactifypress-reaction-item <?php echo esc_attr($inactive_class); ?>" data-index="<?php echo esc_attr($i); ?>">
                            <div class="reactifypress-reaction-handle" title="<?php esc_attr_e('Drag to reorder', 'reactifypress'); ?>">
                                <span class="dashicons dashicons-menu"></span>
                            </div>

                            <div class="reactifypress-reaction-icon-wrapper">
                                <input type="text"
                                    name="reactifypress_settings[reactions][<?php echo esc_attr($i); ?>][icon]"
                                    value="<?php echo esc_attr($reaction['icon']); ?>"
                                    class="reactifypress-reaction-icon"
                                    placeholder="<?php esc_attr_e('Icon/Emoji', 'reactifypress'); ?>"
                                    maxlength="4">
                                <span class="reactifypress-icon-preview"><?php echo esc_html($reaction['icon']); ?></span>
                            </div>

                            <div class="reactifypress-reaction-controls">
                                <div class="reactifypress-reaction-field">
                                    <input type="text"
                                        name="reactifypress_settings[reactions][<?php echo esc_attr($i); ?>][label]"
                                        value="<?php echo esc_attr($reaction['label']); ?>"
                                        class="reactifypress-reaction-label regular-text"
                                        placeholder="<?php esc_attr_e('Reaction Name', 'reactifypress'); ?>"
                                        required>
                                </div>

                                <div class="reactifypress-reaction-active-wrapper">
                                    <label class="reactifypress-switch">
                                        <input type="checkbox"
                                            name="reactifypress_settings[reactions][<?php echo esc_attr($i); ?>][active]"
                                            value="1"
                                            class="reactifypress-reaction-active"
                                            <?php checked($reaction['active'], true); ?>>
                                        <span class="reactifypress-switch-slider"></span>
                                        <span class="screen-reader-text"><?php esc_html_e('Active', 'reactifypress'); ?></span>
                                    </label>
                                </div>

                                <button type="button" class="reactifypress-delete-reaction" title="<?php esc_attr_e('Delete this reaction', 'reactifypress'); ?>">
                                    <span class="dashicons dashicons-trash"></span>
                                </button>
                            </div>
                        </div>
                    <?php
                        $i++;
                    endforeach;
                    ?>
                </div>

                <div class="reactifypress-reactions-help">
                    <p><span class="dashicons dashicons-info"></span> <?php esc_html_e('Tip: Use emojis from your system emoji picker or copy from Emojipedia.', 'reactifypress'); ?></p>
                </div>
            </div>

            <!-- Appearance Tab -->
            <div id="tab-appearance">
                <div class="reactifypress-section-header">
                    <h2><?php esc_html_e('Appearance Settings', 'reactifypress'); ?></h2>
                    <p><?php esc_html_e('Customize the colors and visual appearance of your reactions.', 'reactifypress'); ?></p>
                </div>

                <div class="reactifypress-appearance-preview">
                    <h3><?php esc_html_e('Live Preview', 'reactifypress'); ?></h3>
                    <div id="reactifypress-live-preview"></div>
                </div>

                <table class="form-table reactifypress-form-table">
                    <tr>
                        <th scope="row">
                            <label for="reactifypress-background-color"><?php esc_html_e('Hintergrundfarbe', 'reactifypress'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="reactifypress-background-color" name="reactifypress_settings[display][background_color]" value="<?php echo esc_attr($settings['display']['background_color']); ?>" class="reactifypress-color-picker" data-default-color="#ffffff">
                            <p class="description"><?php esc_html_e('Default background color for reaction buttons.', 'reactifypress'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="reactifypress-text-color"><?php esc_html_e('Textfarbe', 'reactifypress'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="reactifypress-text-color" name="reactifypress_settings[display][text_color]" value="<?php echo esc_attr($settings['display']['text_color']); ?>" class="reactifypress-color-picker" data-default-color="#333333">
                            <p class="description"><?php esc_html_e('Color for reaction counts and labels.', 'reactifypress'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="reactifypress-hover-color"><?php esc_html_e('Hover-Farbe', 'reactifypress'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="reactifypress-hover-color" name="reactifypress_settings[display][hover_color]" value="<?php echo esc_attr($settings['display']['hover_color']); ?>" class="reactifypress-color-picker" data-default-color="#e74c3c">
                            <p class="description"><?php esc_html_e('Background color when hovering over reactions.', 'reactifypress'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="reactifypress-active-color"><?php esc_html_e('Aktive Farbe', 'reactifypress'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="reactifypress-active-color" name="reactifypress_settings[display][active_color]" value="<?php echo esc_attr($settings['display']['active_color']); ?>" class="reactifypress-color-picker" data-default-color="#f1c40f">
                            <p class="description"><?php esc_html_e('Background color for selected reactions.', 'reactifypress'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="reactifypress-tooltip-background"><?php esc_html_e('Tooltip Background', 'reactifypress'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="reactifypress-tooltip-background" name="reactifypress_settings[display][tooltip_background]" value="<?php echo esc_attr($settings['display']['tooltip_background']); ?>" class="reactifypress-color-picker" data-default-color="#333333">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="reactifypress-tooltip-text-color"><?php esc_html_e('Tooltip Text Color', 'reactifypress'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="reactifypress-tooltip-text-color" name="reactifypress_settings[display][tooltip_text_color]" value="<?php echo esc_attr($settings['display']['tooltip_text_color']); ?>" class="reactifypress-color-picker" data-default-color="#ffffff">
                        </td>
                    </tr>
                </table>

                <div class="reactifypress-preset-colors">
                    <h3><?php esc_html_e('Color Presets', 'reactifypress'); ?></h3>
                    <div class="reactifypress-presets">
                        <button type="button" class="button reactifypress-preset" data-preset="default">
                            <span class="reactifypress-preset-preview" style="background: linear-gradient(45deg, #fff 50%, #f1c40f 50%);"></span>
                            <?php esc_html_e('Default', 'reactifypress'); ?>
                        </button>
                        <button type="button" class="button reactifypress-preset" data-preset="dark">
                            <span class="reactifypress-preset-preview" style="background: linear-gradient(45deg, #2c3e50 50%, #3498db 50%);"></span>
                            <?php esc_html_e('Dark', 'reactifypress'); ?>
                        </button>
                        <button type="button" class="button reactifypress-preset" data-preset="minimal">
                            <span class="reactifypress-preset-preview" style="background: linear-gradient(45deg, #f8f8f8 50%, #333 50%);"></span>
                            <?php esc_html_e('Minimal', 'reactifypress'); ?>
                        </button>
                        <button type="button" class="button reactifypress-preset" data-preset="colorful">
                            <span class="reactifypress-preset-preview" style="background: linear-gradient(45deg, #e74c3c 25%, #3498db 25%, #3498db 50%, #2ecc71 50%, #2ecc71 75%, #f1c40f 75%);"></span>
                            <?php esc_html_e('Colorful', 'reactifypress'); ?>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Display Settings Tab -->
            <div id="tab-display">
                <div class="reactifypress-section-header">
                    <h2><?php esc_html_e('Display Settings', 'reactifypress'); ?></h2>
                    <p><?php esc_html_e('Configure how and where reactions are displayed on your site.', 'reactifypress'); ?></p>
                </div>

                <table class="form-table reactifypress-form-table">
                    <tr>
                        <th scope="row">
                            <label for="reactifypress-display-position"><?php esc_html_e('Display Position', 'reactifypress'); ?></label>
                        </th>
                        <td>
                            <select id="reactifypress-display-position" name="reactifypress_settings[display][position]" class="regular-text">
                                <option value="after_content" <?php selected($settings['display']['position'], 'after_content'); ?>><?php esc_html_e('Nach dem Inhalt', 'reactifypress'); ?></option>
                                <option value="before_content" <?php selected($settings['display']['position'], 'before_content'); ?>><?php esc_html_e('Vor dem Inhalt', 'reactifypress'); ?></option>
                                <option value="both" <?php selected($settings['display']['position'], 'both'); ?>><?php esc_html_e('Beides (Vor und Nach)', 'reactifypress'); ?></option>
                                <option value="manual" <?php selected($settings['display']['position'], 'manual'); ?>><?php esc_html_e('Manuell (mit Shortcode)', 'reactifypress'); ?></option>
                            </select>
                            <p class="description">
                                <?php esc_html_e('Select where to display the reactions.', 'reactifypress'); ?>
                                <code>[reactifypress]</code> <?php esc_html_e('or', 'reactifypress'); ?> <code>[reactifypress post_id="123"]</code>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <?php esc_html_e('Display Options', 'reactifypress'); ?>
                        </th>
                        <td>
                            <fieldset>
                                <label>
                                    <input type="checkbox" name="reactifypress_settings[display][display_count]" value="1" <?php checked($settings['display']['display_count'], true); ?>>
                                    <?php esc_html_e('Reaktionszahlen anzeigen', 'reactifypress'); ?>
                                </label>
                                <br>
                                <label>
                                    <input type="checkbox" name="reactifypress_settings[display][display_labels]" value="1" <?php checked($settings['display']['display_labels'], true); ?>>
                                    <?php esc_html_e('Reaktionsnamen anzeigen (neben den Symbolen)', 'reactifypress'); ?>
                                </label>
                                <br>
                                <label>
                                    <input type="checkbox" name="reactifypress_settings[display][show_total]" value="1" <?php checked(isset($settings['display']['show_total']) ? $settings['display']['show_total'] : true, true); ?>>
                                    <?php esc_html_e('Gesamtzahl der Reaktionen anzeigen', 'reactifypress'); ?>
                                </label>
                                <br>
                                <label>
                                    <input type="checkbox" name="reactifypress_settings[display][animate]" value="1" <?php checked(isset($settings['display']['animate']) ? $settings['display']['animate'] : true, true); ?>>
                                    <?php esc_html_e('Animationen aktivieren', 'reactifypress'); ?>
                                </label>
                            </fieldset>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="reactifypress-alignment"><?php esc_html_e('Alignment', 'reactifypress'); ?></label>
                        </th>
                        <td>
                            <select id="reactifypress-alignment" name="reactifypress_settings[display][alignment]" class="regular-text">
                                <option value="left" <?php selected(isset($settings['display']['alignment']) ? $settings['display']['alignment'] : 'left', 'left'); ?>><?php esc_html_e('Left', 'reactifypress'); ?></option>
                                <option value="center" <?php selected(isset($settings['display']['alignment']) ? $settings['display']['alignment'] : 'left', 'center'); ?>><?php esc_html_e('Center', 'reactifypress'); ?></option>
                                <option value="right" <?php selected(isset($settings['display']['alignment']) ? $settings['display']['alignment'] : 'left', 'right'); ?>><?php esc_html_e('Right', 'reactifypress'); ?></option>
                            </select>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Post Types Tab -->
            <div id="tab-post-types">
                <div class="reactifypress-section-header">
                    <h2><?php esc_html_e('Post Types', 'reactifypress'); ?></h2>
                    <p><?php esc_html_e('Select which post types should display reactions.', 'reactifypress'); ?></p>
                </div>

                <table class="form-table reactifypress-form-table">
                    <?php foreach ($post_types as $post_type) : ?>
                        <tr>
                            <th scope="row">
                                <label for="reactifypress-post-type-<?php echo esc_attr($post_type->name); ?>">
                                    <?php echo esc_html($post_type->labels->name); ?>
                                </label>
                            </th>
                            <td>
                                <label class="reactifypress-switch">
                                    <input type="checkbox"
                                        id="reactifypress-post-type-<?php echo esc_attr($post_type->name); ?>"
                                        name="reactifypress_settings[post_types][<?php echo esc_attr($post_type->name); ?>]"
                                        value="1"
                                        <?php checked(isset($settings['post_types'][$post_type->name]) && $settings['post_types'][$post_type->name], true); ?>>
                                    <span class="reactifypress-switch-slider"></span>
                                </label>
                                <?php if ($post_type->description) : ?>
                                    <p class="description"><?php echo esc_html($post_type->description); ?></p>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>

            <!-- Advanced Tab -->
            <div id="tab-advanced">
                <div class="reactifypress-section-header">
                    <h2><?php esc_html_e('Reaktionen verwalten', 'reactifypress'); ?></h2>
                    <p><?php esc_html_e('Konfigurieren Sie die Reaktionen, die für Ihre Beiträge verfügbar sein sollen. Ziehen Sie sie, um die Reihenfolge zu ändern.', 'reactifypress'); ?></p>
                </div>

                <table class="form-table reactifypress-form-table">
                    <tr>
                        <th scope="row">
                            <?php esc_html_e('User Restrictions', 'reactifypress'); ?>
                        </th>
                        <td>
                            <fieldset>
                                <label>
                                    <input type="checkbox" name="reactifypress_settings[advanced][require_login]" value="1" <?php checked(isset($settings['advanced']['require_login']) ? $settings['advanced']['require_login'] : false, true); ?>>
                                    <?php esc_html_e('Require users to be logged in to react', 'reactifypress'); ?>
                                </label>
                                <p class="description"><?php esc_html_e('When enabled, only logged-in users can add reactions.', 'reactifypress'); ?></p>
                            </fieldset>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <?php esc_html_e('Performance', 'reactifypress'); ?>
                        </th>
                        <td>
                            <fieldset>
                                <label>
                                    <input type="checkbox" name="reactifypress_settings[advanced][lazy_load]" value="1" <?php checked(isset($settings['advanced']['lazy_load']) ? $settings['advanced']['lazy_load'] : false, true); ?>>
                                    <?php esc_html_e('Enable lazy loading of reactions', 'reactifypress'); ?>
                                </label>
                                <p class="description"><?php esc_html_e('Load reaction counts via AJAX for better page performance.', 'reactifypress'); ?></p>
                            </fieldset>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="reactifypress-custom-css"><?php esc_html_e('Custom CSS', 'reactifypress'); ?></label>
                        </th>
                        <td>
                            <textarea id="reactifypress-custom-css" name="reactifypress_settings[advanced][custom_css]" rows="10" cols="50" class="large-text code"><?php echo esc_textarea(isset($settings['advanced']['custom_css']) ? $settings['advanced']['custom_css'] : ''); ?></textarea>
                            <p class="description"><?php esc_html_e('Add custom CSS to style reactions. No need to include <style> tags.', 'reactifypress'); ?></p>
                        </td>
                    </tr>
                </table>

                <div class="reactifypress-import-export">
                    <h3><?php esc_html_e('Import/Export Settings', 'reactifypress'); ?></h3>
                    <p><?php esc_html_e('Export your settings for backup or import settings from another site.', 'reactifypress'); ?></p>

                    <div class="reactifypress-export">
                        <h4><?php esc_html_e('Export', 'reactifypress'); ?></h4>
                        <button type="button" id="reactifypress-export-settings" class="button">
                            <span class="dashicons dashicons-download"></span>
                            <?php esc_html_e('Export Settings', 'reactifypress'); ?>
                        </button>
                    </div>

                    <div class="reactifypress-import">
                        <h4><?php esc_html_e('Import', 'reactifypress'); ?></h4>
                        <input type="file" id="reactifypress-import-file" accept=".json">
                        <button type="button" id="reactifypress-import-settings" class="button" disabled>
                            <span class="dashicons dashicons-upload"></span>
                            <?php esc_html_e('Import Settings', 'reactifypress'); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="reactifypress-submit-wrapper">
            <p class="submit">
                <?php submit_button(null, 'primary', 'submit', false); ?>
                <button type="button" id="reactifypress-reset-settings" class="button reactifypress-reset-settings">
                    <?php esc_html_e('Reset to Defaults', 'reactifypress'); ?>
                </button>
            </p>
        </div>
    </form>

    <!-- Template for new reactions (hidden) -->
    <script type="text/template" id="reactifypress-reaction-template">
        <div class="reactifypress-reaction-item" data-index="{index}">
            <div class="reactifypress-reaction-handle" title="<?php esc_attr_e('Drag to reorder', 'reactifypress'); ?>">
                <span class="dashicons dashicons-menu"></span>
            </div>
            
            <div class="reactifypress-reaction-icon-wrapper">
                <input type="text" 
                       name="reactifypress_settings[reactions][{index}][icon]" 
                       value="👍" 
                       class="reactifypress-reaction-icon" 
                       placeholder="<?php esc_attr_e('Icon/Emoji', 'reactifypress'); ?>"
                       maxlength="4">
                <span class="reactifypress-icon-preview">👍</span>
            </div>
            
            <div class="reactifypress-reaction-controls">
                <div class="reactifypress-reaction-field">
                    <input type="text" 
                           name="reactifypress_settings[reactions][{index}][label]" 
                           value="<?php esc_attr_e('New Reaction', 'reactifypress'); ?>" 
                           class="reactifypress-reaction-label regular-text" 
                           placeholder="<?php esc_attr_e('Reaction Name', 'reactifypress'); ?>"
                           required>
                </div>
                
                <div class="reactifypress-reaction-active-wrapper">
                    <label class="reactifypress-switch">
                        <input type="checkbox" 
                               name="reactifypress_settings[reactions][{index}][active]" 
                               value="1" 
                               class="reactifypress-reaction-active" 
                               checked>
                        <span class="reactifypress-switch-slider"></span>
                        <span class="screen-reader-text"><?php esc_html_e('Active', 'reactifypress'); ?></span>
                    </label>
                </div>
                
                <button type="button" class="reactifypress-delete-reaction" title="<?php esc_attr_e('Delete this reaction', 'reactifypress'); ?>">
                    <span class="dashicons dashicons-trash"></span>
                </button>
            </div>
        </div>
    </script>

    <!-- Emoji Picker Modal -->
    <div id="reactifypress-emoji-modal" class="reactifypress-modal" style="display: none;">
        <div class="reactifypress-modal-content">
            <div class="reactifypress-modal-header">
                <h3><?php esc_html_e('Common Reaction Emojis', 'reactifypress'); ?></h3>
                <button type="button" class="reactifypress-modal-close">&times;</button>
            </div>
            <div class="reactifypress-modal-body">
                <div class="reactifypress-emoji-grid">
                    <button type="button" class="reactifypress-emoji-button" data-emoji="👍">👍</button>
                    <button type="button" class="reactifypress-emoji-button" data-emoji="❤️">❤️</button>
                    <button type="button" class="reactifypress-emoji-button" data-emoji="😂">😂</button>
                    <button type="button" class="reactifypress-emoji-button" data-emoji="😮">😮</button>
                    <button type="button" class="reactifypress-emoji-button" data-emoji="😢">😢</button>
                    <button type="button" class="reactifypress-emoji-button" data-emoji="😡">😡</button>
                    <button type="button" class="reactifypress-emoji-button" data-emoji="👏">👏</button>
                    <button type="button" class="reactifypress-emoji-button" data-emoji="🔥">🔥</button>
                    <button type="button" class="reactifypress-emoji-button" data-emoji="🎉">🎉</button>
                    <button type="button" class="reactifypress-emoji-button" data-emoji="💯">💯</button>
                    <button type="button" class="reactifypress-emoji-button" data-emoji="🤔">🤔</button>
                    <button type="button" class="reactifypress-emoji-button" data-emoji="🙏">🙏</button>
                    <button type="button" class="reactifypress-emoji-button" data-emoji="😍">😍</button>
                    <button type="button" class="reactifypress-emoji-button" data-emoji="🤗">🤗</button>
                    <button type="button" class="reactifypress-emoji-button" data-emoji="😎">😎</button>
                    <button type="button" class="reactifypress-emoji-button" data-emoji="🚀">🚀</button>
                    <button type="button" class="reactifypress-emoji-button" data-emoji="💪">💪</button>
                    <button type="button" class="reactifypress-emoji-button" data-emoji="✨">✨</button>
                    <button type="button" class="reactifypress-emoji-button" data-emoji="🙌">🙌</button>
                    <button type="button" class="reactifypress-emoji-button" data-emoji="💔">💔</button>
                </div>
                <p class="description"><?php esc_html_e('Click an emoji to copy it to your clipboard.', 'reactifypress'); ?></p>
            </div>
        </div>
    </div>
</div>

<style>
    /* Admin page improvements */
    .reactifypress-settings-page {
        position: relative;
    }

    .reactifypress-version {
        font-size: 12px;
        color: #666;
        font-weight: normal;
        margin-left: 10px;
    }

    .reactifypress-section-header {
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 1px solid #eee;
    }

    .reactifypress-section-header h2 {
        margin-bottom: 10px;
    }

    .reactifypress-section-header p {
        color: #666;
        font-size: 14px;
    }

    /* Toolbar */
    .reactifypress-reactions-toolbar {
        margin-bottom: 20px;
        display: flex;
        gap: 10px;
        align-items: center;
    }

    /* Switch styles */
    .reactifypress-switch {
        position: relative;
        display: inline-block;
        width: 44px;
        height: 24px;
    }

    .reactifypress-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .reactifypress-switch-slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: .3s;
        border-radius: 24px;
    }

    .reactifypress-switch-slider:before {
        position: absolute;
        content: "";
        height: 16px;
        width: 16px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        transition: .3s;
        border-radius: 50%;
    }

    .reactifypress-switch input:checked+.reactifypress-switch-slider {
        background-color: #2271b1;
    }

    .reactifypress-switch input:checked+.reactifypress-switch-slider:before {
        transform: translateX(20px);
    }

    /* Icon preview */
    .reactifypress-icon-preview {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 20px;
        pointer-events: none;
    }

    .reactifypress-reaction-icon-wrapper {
        position: relative;
    }

    .reactifypress-reaction-icon {
        padding-right: 40px;
    }

    /* Delete button */
    .reactifypress-delete-reaction {
        background: none;
        border: none;
        color: #b32d2e;
        cursor: pointer;
        padding: 5px;
        transition: color 0.2s;
    }

    .reactifypress-delete-reaction:hover {
        color: #dc3232;
    }

    /* Live preview */
    .reactifypress-appearance-preview {
        background: #f5f5f5;
        padding: 20px;
        border-radius: 5px;
        margin-bottom: 30px;
    }

    #reactifypress-live-preview {
        background: #fff;
        padding: 20px;
        border-radius: 3px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    /* Preset buttons */
    .reactifypress-presets {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .reactifypress-preset {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .reactifypress-preset-preview {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        display: inline-block;
        border: 2px solid #ddd;
    }

    /* Modal styles */
    .reactifypress-modal {
        position: fixed;
        z-index: 9999;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .reactifypress-modal-content {
        background: #fff;
        border-radius: 5px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        max-width: 500px;
        width: 90%;
        max-height: 80vh;
        overflow-y: auto;
    }

    .reactifypress-modal-header {
        padding: 20px;
        border-bottom: 1px solid #eee;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .reactifypress-modal-header h3 {
        margin: 0;
    }

    .reactifypress-modal-close {
        background: none;
        border: none;
        font-size: 24px;
        cursor: pointer;
        color: #666;
        padding: 0;
        width: 30px;
        height: 30px;
    }

    .reactifypress-modal-body {
        padding: 20px;
    }

    .reactifypress-emoji-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(50px, 1fr));
        gap: 10px;
        margin-bottom: 15px;
    }

    .reactifypress-emoji-button {
        background: #f5f5f5;
        border: 1px solid #ddd;
        border-radius: 5px;
        padding: 10px;
        font-size: 24px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .reactifypress-emoji-button:hover {
        background: #e5e5e5;
        transform: scale(1.1);
    }

    /* Import/Export section */
    .reactifypress-import-export {
        margin-top: 40px;
        padding: 20px;
        background: #f9f9f9;
        border-radius: 5px;
    }

    .reactifypress-import-export h3 {
        margin-top: 0;
    }

    .reactifypress-export,
    .reactifypress-import {
        margin: 20px 0;
    }

    /* Submit wrapper */
    .reactifypress-submit-wrapper {
        margin-top: 40px;
        padding-top: 20px;
        border-top: 1px solid #eee;
    }

    /* Help text */
    .reactifypress-reactions-help {
        margin-top: 20px;
        padding: 15px;
        background: #f0f8ff;
        border-left: 4px solid #2271b1;
        border-radius: 3px;
    }

    .reactifypress-reactions-help .dashicons {
        color: #2271b1;
        margin-right: 5px;
    }

    /* Improved reaction item styles */
    .reactifypress-reaction-item {
        background: #fff;
        border: 1px solid #ddd;
        margin-bottom: 10px;
        padding: 15px;
        border-radius: 5px;
        display: flex;
        align-items: center;
        gap: 15px;
        transition: all 0.2s;
    }

    .reactifypress-reaction-item:hover {
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .reactifypress-reaction-item.ui-sortable-helper {
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    }

    .reactifypress-reaction-handle {
        cursor: move;
        color: #999;
    }

    .reactifypress-reaction-controls {
        flex: 1;
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .reactifypress-reaction-field {
        flex: 1;
    }

    /* Inactive reaction styles */
    .reactifypress-reaction-inactive {
        opacity: 0.6;
        background: #f9f9f9;
    }

    /* Responsive improvements */
    @media (max-width: 782px) {
        .reactifypress-reactions-toolbar {
            flex-wrap: wrap;
        }

        .reactifypress-reaction-item {
            flex-wrap: wrap;
        }

        .reactifypress-reaction-controls {
            width: 100%;
            margin-top: 10px;
        }
    }
</style>