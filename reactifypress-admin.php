<?php
/**
 * Admin class for ReactifyPress
 *
 * @package ReactifyPress
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Admin class
 */
class ReactifyPress_Admin {
    /**
     * Constructor
     */
    public function __construct() {
        // Add admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Register settings
        add_action('admin_init', array($this, 'register_settings'));
        
        // Enqueue admin scripts and styles
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }

    /**
     * Add admin menu
     *
     * @return void
     */
    public function add_admin_menu() {
        add_menu_page(
            __('ReactifyPress', 'reactifypress'),
            __('ReactifyPress', 'reactifypress'),
            'manage_options',
            'reactifypress',
            array($this, 'render_settings_page'),
            'dashicons-smiley',
            30
        );
        
        add_submenu_page(
            'reactifypress',
            __('Settings', 'reactifypress'),
            __('Settings', 'reactifypress'),
            'manage_options',
            'reactifypress',
            array($this, 'render_settings_page')
        );
        
        add_submenu_page(
            'reactifypress',
            __('Analytics', 'reactifypress'),
            __('Analytics', 'reactifypress'),
            'manage_options',
            'reactifypress-analytics',
            array($this, 'render_analytics_page')
        );
    }

    /**
     * Register settings
     *
     * @return void
     */
    public function register_settings() {
        register_setting(
            'reactifypress_settings',
            'reactifypress_settings',
            array($this, 'sanitize_settings')
        );
    }

    /**
     * Enqueue admin scripts and styles
     *
     * @param string $hook Current admin page
     * @return void
     */
    public function enqueue_admin_scripts($hook) {
        // Only enqueue on plugin admin pages
        if (strpos($hook, 'reactifypress') === false) {
            return;
        }
        
        // Enqueue color picker
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
        
        // Enqueue jQuery UI
        wp_enqueue_style('jquery-ui-style', '//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');
        wp_enqueue_script('jquery-ui-tabs');
        wp_enqueue_script('jquery-ui-sortable');
        
        // Enqueue admin scripts and styles
        wp_enqueue_style(
            'reactifypress-admin-style',
            REACTIFYPRESS_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            REACTIFYPRESS_VERSION
        );
        
        wp_enqueue_script(
            'reactifypress-admin-script',
            REACTIFYPRESS_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery', 'wp-color-picker', 'jquery-ui-tabs', 'jquery-ui-sortable'),
            REACTIFYPRESS_VERSION,
            true
        );
        
        wp_localize_script('reactifypress-admin-script', 'reactifypress_admin', array(
            'nonce' => wp_create_nonce('reactifypress_admin_nonce'),
            'i18n' => array(
                'confirm_reset' => __('Are you sure you want to reset all settings to defaults?', 'reactifypress'),
                'confirm_delete' => __('Are you sure you want to delete this reaction?', 'reactifypress'),
                'save_success' => __('Settings saved successfully.', 'reactifypress'),
                'save_error' => __('Error saving settings.', 'reactifypress'),
            ),
        ));
    }

    /**
     * Render the settings page
     *
     * @return void
     */
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        $settings = reactifypress()->settings->get_settings();
        $active_reactions = reactifypress()->settings->get_active_reactions();
        $post_types = get_post_types(array('public' => true), 'objects');
        
        include REACTIFYPRESS_PLUGIN_DIR . 'templates/admin-settings.php';
    }

    /**
     * Render the analytics page
     *
     * @return void
     */
    public function render_analytics_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        $active_reactions = reactifypress()->settings->get_active_reactions();
        
        include REACTIFYPRESS_PLUGIN_DIR . 'templates/admin-analytics.php';
    }

    /**
     * Sanitize settings
     *
     * @param array $input Input settings
     * @return array Sanitized settings
     */
    public function sanitize_settings($input) {
        $sanitized = array();
        
        // Reactions
        if (isset($input['reactions']) && is_array($input['reactions'])) {
            $sanitized['reactions'] = array();
            
            foreach ($input['reactions'] as $key => $reaction) {
                $key = sanitize_key($key);
                $sanitized['reactions'][$key] = array(
                    'icon' => sanitize_text_field($reaction['icon']),
                    'label' => sanitize_text_field($reaction['label']),
                    'active' => isset($reaction['active']) && $reaction['active'] ? true : false,
                );
            }
        }
        
        // Display settings
        if (isset($input['display']) && is_array($input['display'])) {
            $sanitized['display'] = array(
                'primary_color' => sanitize_hex_color($input['display']['primary_color']),
                'secondary_color' => sanitize_hex_color($input['display']['secondary_color']),
                'hover_color' => sanitize_hex_color($input['display']['hover_color']),
                'active_color' => sanitize_hex_color($input['display']['active_color']),
                'background_color' => sanitize_hex_color($input['display']['background_color']),
                'text_color' => sanitize_hex_color($input['display']['text_color']),
                'tooltip_background' => sanitize_hex_color($input['display']['tooltip_background']),
                'tooltip_text_color' => sanitize_hex_color($input['display']['tooltip_text_color']),
                'display_count' => isset($input['display']['display_count']) && $input['display']['display_count'] ? true : false,
                'display_labels' => isset($input['display']['display_labels']) && $input['display']['display_labels'] ? true : false,
                'position' => sanitize_key($input['display']['position']),
            );
        }
        
        // Post types
        if (isset($input['post_types']) && is_array($input['post_types'])) {
            $sanitized['post_types'] = array();
            
            foreach ($input['post_types'] as $post_type => $enabled) {
                $post_type = sanitize_key($post_type);
                $sanitized['post_types'][$post_type] = !empty($enabled);
            }
        }
        
        return $sanitized;
    }
}
