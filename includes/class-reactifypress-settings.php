<?php
/**
 * Settings class for ReactifyPress
 *
 * @package ReactifyPress
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Settings class
 */
class ReactifyPress_Settings {
    /**
     * Option name for settings
     *
     * @var string
     */
    private $option_name = 'reactifypress_settings';

    /**
     * Default settings
     *
     * @var array
     */
    private $defaults = array(
        'reactions' => array(
            'like' => array(
                'icon' => '👍',
                'label' => 'Like',
                'active' => true,
            ),
            'love' => array(
                'icon' => '❤️',
                'label' => 'Liebe ich',
                'active' => true,
            ),
            'haha' => array(
                'icon' => '😂',
                'label' => 'Haha',
                'active' => true,
            ),
            'wow' => array(
                'icon' => '😮',
                'label' => 'Wow',
                'active' => true,
            ),
            'sad' => array(
                'icon' => '😢',
                'label' => 'Oh man',
                'active' => true,
            ),
            'angry' => array(
                'icon' => '😡',
                'label' => 'macht mich wütend',
                'active' => true,
            ),
        ),
        'display' => array(
            'primary_color' => '#3498db',
            'secondary_color' => '#2ecc71',
            'hover_color' => '#e74c3c',
            'active_color' => '#f1c40f',
            'background_color' => '#ffffff',
            'text_color' => '#333333',
            'tooltip_background' => '#333333',
            'tooltip_text_color' => '#ffffff',
            'display_count' => true,
            'display_labels' => false,
            'position' => 'after_content', // Options: after_content, before_content, both, manual
        ),
        'post_types' => array(
            'post' => true,
            'page' => false,
        ),
    );

    /**
     * Constructor
     */
    public function __construct() {
        // Fetch settings when instance is created
        $this->get_settings();
    }

    /**
     * Add default settings if not exists
     *
     * @return void
     */
    public function add_default_settings() {
        if (false === get_option($this->option_name)) {
            update_option($this->option_name, $this->defaults);
        }
    }

    /**
     * Get all plugin settings
     *
     * @return array Plugin settings
     */
    public function get_settings() {
        $settings = get_option($this->option_name, array());
        
        // Merge with defaults to ensure all settings exist
        return wp_parse_args($settings, $this->defaults);
    }

    /**
     * Get a specific setting
     *
     * @param string $section Setting section
     * @param string $key     Setting key
     * @param mixed  $default Default value
     * @return mixed Setting value
     */
    public function get_setting($section, $key = '', $default = null) {
        $settings = $this->get_settings();
        
        if (empty($key)) {
            return isset($settings[$section]) ? $settings[$section] : $default;
        }
        
        return isset($settings[$section][$key]) ? $settings[$section][$key] : $default;
    }

    /**
     * Get active reactions
     *
     * @return array Active reactions
     */
    public function get_active_reactions() {
        $reactions = $this->get_setting('reactions');
        $active_reactions = array();
        
        foreach ($reactions as $key => $reaction) {
            if (isset($reaction['active']) && $reaction['active']) {
                $active_reactions[$key] = $reaction;
            }
        }
        
        return $active_reactions;
    }

    /**
     * Update a setting
     *
     * @param string $section Setting section
     * @param string $key     Setting key
     * @param mixed  $value   Setting value
     * @return bool True if updated, false otherwise
     */
    public function update_setting($section, $key, $value) {
        $settings = $this->get_settings();
        
        if (!isset($settings[$section])) {
            $settings[$section] = array();
        }
        
        $settings[$section][$key] = $value;
        
        return update_option($this->option_name, $settings);
    }

    /**
     * Update an entire section
     *
     * @param string $section Section name
     * @param array  $values  Section values
     * @return bool True if updated, false otherwise
     */
    public function update_section($section, $values) {
        $settings = $this->get_settings();
        $settings[$section] = $values;
        
        return update_option($this->option_name, $settings);
    }

    /**
     * Update all settings
     *
     * @param array $settings New settings
     * @return bool True if updated, false otherwise
     */
    public function update_settings($settings) {
        return update_option($this->option_name, $settings);
    }

    /**
     * Check if reactions are enabled for a post type
     *
     * @param string $post_type Post type
     * @return bool True if enabled, false otherwise
     */
    public function is_enabled_for_post_type($post_type) {
        $post_types = $this->get_setting('post_types');
        
        return isset($post_types[$post_type]) && $post_types[$post_type];
    }

    /**
     * Get the position where reactions should be displayed
     *
     * @return string Position (after_content, before_content, both, or manual)
     */
    public function get_display_position() {
        return $this->get_setting('display', 'position', 'after_content');
    }

    /**
     * Reset settings to defaults
     *
     * @return bool True on success, false on failure
     */
    public function reset_to_defaults() {
        return update_option($this->option_name, $this->defaults);
    }
}
