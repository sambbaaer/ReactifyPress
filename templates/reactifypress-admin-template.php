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
?>

<div class="wrap reactifypress-settings-page">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <?php settings_errors('reactifypress_settings'); ?>

    <form method="post" action="options.php">
        <?php
        settings_fields('reactifypress_settings');
        $settings = reactifypress()->settings->get_settings();
        ?>

        <div id="reactifypress-tabs" class="reactifypress-tabs">
            <ul>
                <li><a href="#tab-reactions"><?php esc_html_e('Reactions', 'reactifypress'); ?></a></li>
                <li><a href="#tab-appearance"><?php esc_html_e('Appearance', 'reactifypress'); ?></a></li>
                <li><a href="#tab-display"><?php esc_html_e('Display Settings', 'reactifypress'); ?></a></li>
                <li><a href="#tab-post-types"><?php esc_html_e('Post Types', 'reactifypress'); ?></a></li>
            </ul>

            <!-- Reactions Tab -->
            <div id="tab-reactions">
                <h2><?php esc_html_e('Manage Reactions', 'reactifypress'); ?></h2>
                <p><?php esc_html_e('Configure the reactions that will be available on your posts.', 'reactifypress'); ?></p>

                <div id="reactifypress-reactions-list" class="reactifypress-reactions-list">
                    <?php 
                    $i = 0;
                    foreach ($settings['reactions'] as $key => $reaction) : 
                        $inactive_class = !$reaction['active'] ? 'reactifypress-reaction-inactive' : '';
                    ?>
                        <div class="reactifypress-reaction-item <?php echo esc_attr($inactive_class); ?>">
                            <div class="reactifypress-reaction-handle dashicons dashicons-menu"></div>
                            
                            <div class="reactifypress-reaction-icon-wrapper">
                                <input type="text" name="reactifypress_settings[reactions][<?php echo esc_attr($i); ?>][icon]" value="<?php echo esc_attr($reaction['icon']); ?>" class="reactifypress-reaction-icon" placeholder="<?php esc_attr_e('Icon/Emoji', 'reactifypress'); ?>">
                            </div>
                            
                            <div class="reactifypress-reaction-controls">
                                <div class="reactifypress-reaction-field">
                                    <input type="text" name="reactifypress_settings[reactions][<?php echo esc_attr($i); ?>][label]" value="<?php echo esc_attr($reaction['label']); ?>" class="reactifypress-reaction-label" placeholder="<?php esc_attr_e('Reaction Name', 'reactifypress'); ?>">
                                </div>
                                
                                <div class="reactifypress-reaction-active-wrapper">
                                    <label>
                                        <input type="checkbox" name="reactifypress_settings[reactions][<?php echo esc_attr($i); ?>][active]" value="1" class="reactifypress-reaction-active" <?php checked($reaction['active'], true); ?>>
                                        <?php esc_html_e('Active', 'reactifypress'); ?>
                                    </label>
                                </div>
                                
                                <a href="#" class="reactifypress-delete-reaction" title="<?php esc_attr_e('Delete this reaction', 'reactifypress'); ?>">
                                    <span class="dashicons dashicons-trash"></span>
                                </a>
                            </div>
                        </div>
                    <?php 
                        $i++;
                    endforeach; 
                    ?>
                </div>

                <button type="button" id="reactifypress-add-reaction" class="button reactifypress-add-reaction">
                    <span class="dashicons dashicons-plus-alt"></span>
                    <?php esc_html_e('Add New Reaction', 'reactifypress'); ?>
                </button>

                <button type="button" id="reactifypress-preview-reactions" class="button button-secondary">
                    <?php esc_html_e('Preview Reactions', 'reactifypress'); ?>
                </button>
            </div>

            <!-- Appearance Tab -->
            <div id="tab-appearance">
                <h2><?php esc_html_e('Appearance Settings', 'reactifypress'); ?></h2>
                <p><?php esc_html_e('Customize the colors and visual appearance of your reactions.', 'reactifypress'); ?></p>

                <table class="form-table reactifypress-form-table">
                    <tr>
                        <th scope="row">
                            <label for="reactifypress-background-color"><?php esc_html_e('Background Color', 'reactifypress'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="reactifypress-background-color" name="reactifypress_settings[display][background_color]" value="<?php echo esc_attr($settings['display']['background_color']); ?>" class="reactifypress-color-picker">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="reactifypress-text-color"><?php esc_html_e('Text Color', 'reactifypress'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="reactifypress-text-color" name="reactifypress_settings[display][text_color]" value="<?php echo esc_attr($settings['display']['text_color']); ?>" class="reactifypress-color-picker">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="reactifypress-hover-color"><?php esc_html_e('Hover Color', 'reactifypress'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="reactifypress-hover-color" name="reactifypress_settings[display][hover_color]" value="<?php echo esc_attr($settings['display']['hover_color']); ?>" class="reactifypress-color-picker">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="reactifypress-active-color"><?php esc_html_e('Active Color', 'reactifypress'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="reactifypress-active-color" name="reactifypress_settings[display][active_color]" value="<?php echo esc_attr($settings['display']['active_color']); ?>" class="reactifypress-color-picker">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="reactifypress-tooltip-background"><?php esc_html_e('Tooltip Background', 'reactifypress'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="reactifypress-tooltip-background" name="reactifypress_settings[display][tooltip_background]" value="<?php echo esc_attr($settings['display']['tooltip_background']); ?>" class="reactifypress-color-picker">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="reactifypress-tooltip-text-color"><?php esc_html_e('Tooltip Text Color', 'reactifypress'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="reactifypress-tooltip-text-color" name="reactifypress_settings[display][tooltip_text_color]" value="<?php echo esc_attr($settings['display']['tooltip_text_color']); ?>" class="reactifypress-color-picker">
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Display Settings Tab -->
            <div id="tab-display">
                <h2><?php esc_html_e('Display Settings', 'reactifypress'); ?></h2>
                <p><?php esc_html_e('Configure how and where reactions are displayed on your site.', 'reactifypress'); ?></p>

                <table class="form-table reactifypress-form-table">
                    <tr>
                        <th scope="row">
                            <label for="reactifypress-display-position"><?php esc_html_e('Display Position', 'reactifypress'); ?></label>
                        </th>
                        <td>
                            <select id="reactifypress-display-position" name="reactifypress_settings[display][position]">
                                <option value="after_content" <?php selected($settings['display']['position'], 'after_content'); ?>><?php esc_html_e('After Content', 'reactifypress'); ?></option>
                                <option value="before_content" <?php selected($settings['display']['position'], 'before_content'); ?>><?php esc_html_e('Before Content', 'reactifypress'); ?></option>
                                <option value="both" <?php selected($settings['display']['position'], 'both'); ?>><?php esc_html_e('Both (Before and After)', 'reactifypress'); ?></option>
                                <option value="manual" <?php selected($settings['display']['position'], 'manual'); ?>><?php esc_html_e('Manual (Using Shortcode)', 'reactifypress'); ?></option>
                            </select>
                            <p class="description">
                                <?php esc_html_e('Select where to display the reactions. Choose "Manual" to use the shortcode: [reactifypress]', 'reactifypress'); ?>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label><?php esc_html_e('Display Counts', 'reactifypress'); ?></label>
                        </th>
                        <td>
                            <label>
                                <input type="checkbox" name="reactifypress_settings[display][display_count]" value="1" <?php checked($settings['display']['display_count'], true); ?>>
                                <?php esc_html_e('Show reaction counts', 'reactifypress'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label><?php esc_html_e('Display Labels', 'reactifypress'); ?></label>
                        </th>
                        <td>
                            <label>
                                <input type="checkbox" name="reactifypress_settings[display][display_labels]" value="1" <?php checked($settings['display']['display_labels'], true); ?>>
                                <?php esc_html_e('Show reaction labels (next to icons)', 'reactifypress'); ?>
                            </label>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Post Types Tab -->
            <div id="tab-post-types">
                <h2><?php esc_html_e('Post Types', 'reactifypress'); ?></h2>
                <p><?php esc_html_e('Select which post types should display reactions.', 'reactifypress'); ?></p>

                <table class="form-table reactifypress-form-table">
                    <?php foreach ($post_types as $post_type) : ?>
                        <tr>
                            <th scope="row">
                                <label><?php echo esc_html($post_type->labels->name); ?></label>
                            </th>
                            <td>
                                <label>
                                    <input type="checkbox" name="reactifypress_settings[post_types][<?php echo esc_attr($post_type->name); ?>]" value="1" <?php checked(isset($settings['post_types'][$post_type->name]) && $settings['post_types'][$post_type->name], true); ?>>
                                    <?php printf(esc_html__('Enable reactions for %s', 'reactifypress'), esc_html($post_type->labels->name)); ?>
                                </label>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>

        <p class="submit">
            <?php submit_button(null, 'primary', 'submit', false); ?>
            <a href="#" id="reactifypress-reset-settings" class="button reactifypress-reset-settings"><?php esc_html_e('Reset to Defaults', 'reactifypress'); ?></a>
        </p>
    </form>

    <!-- Template for new reactions (hidden) -->
    <script type="text/template" id="reactifypress-reaction-template">
        <div class="reactifypress-reaction-item">
            <div class="reactifypress-reaction-handle dashicons dashicons-menu"></div>
            
            <div class="reactifypress-reaction-icon-wrapper">
                <input type="text" name="reactifypress_settings[reactions][{index}][icon]" value="👍" class="reactifypress-reaction-icon" placeholder="<?php esc_attr_e('Icon/Emoji', 'reactifypress'); ?>">
            </div>
            
            <div class="reactifypress-reaction-controls">
                <div class="reactifypress-reaction-field">
                    <input type="text" name="reactifypress_settings[reactions][{index}][label]" value="<?php esc_attr_e('New Reaction', 'reactifypress'); ?>" class="reactifypress-reaction-label" placeholder="<?php esc_attr_e('Reaction Name', 'reactifypress'); ?>">
                </div>
                
                <div class="reactifypress-reaction-active-wrapper">
                    <label>
                        <input type="checkbox" name="reactifypress_settings[reactions][{index}][active]" value="1" class="reactifypress-reaction-active" checked>
                        <?php esc_html_e('Active', 'reactifypress'); ?>
                    </label>
                </div>
                
                <a href="#" class="reactifypress-delete-reaction" title="<?php esc_attr_e('Delete this reaction', 'reactifypress'); ?>">
                    <span class="dashicons dashicons-trash"></span>
                </a>
            </div>
        </div>
    </script>
</div>
