<?php
/**
 * Frontend class for ReactifyPress
 *
 * @package ReactifyPress
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Frontend class
 */
class ReactifyPress_Frontend {
    /**
     * Constructor
     */
    public function __construct() {
        // Add reactions to content
        add_filter('the_content', array($this, 'add_reactions_to_content'));
        
        // Register scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'register_scripts_styles'));
    }

    /**
     * Register frontend scripts and styles
     *
     * @return void
     */
    public function register_scripts_styles() {
        // Only register (not enqueue) - we'll enqueue only when needed
        wp_register_style(
            'reactifypress-style',
            REACTIFYPRESS_PLUGIN_URL . 'assets/css/reactifypress.css',
            array(),
            REACTIFYPRESS_VERSION
        );
        
        wp_register_script(
            'reactifypress-script',
            REACTIFYPRESS_PLUGIN_URL . 'assets/js/reactifypress.js',
            array('jquery'),
            REACTIFYPRESS_VERSION,
            true
        );
        
        // Localize script
        wp_localize_script('reactifypress-script', 'reactifypress', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('reactifypress_nonce'),
            'i18n' => array(
                'error' => __('An error occurred. Please try again.', 'reactifypress'),
                'thanks' => __('Thanks for your reaction!', 'reactifypress'),
            ),
        ));
    }

    /**
     * Check if reactions should be displayed for current post
     *
     * @return bool True if should display, false otherwise
     */
    private function should_display_reactions() {
        // Check if we're on a singular post/page
        if (!is_singular()) {
            return false;
        }
        
        $post_type = get_post_type();
        $settings = reactifypress()->settings;
        
        // Check if reactions are enabled for this post type
        return $settings->is_enabled_for_post_type($post_type);
    }

    /**
     * Add reactions to post content
     *
     * @param string $content Post content
     * @return string Modified content
     */
    public function add_reactions_to_content($content) {
        if (!$this->should_display_reactions()) {
            return $content;
        }
        
        // Get display position
        $position = reactifypress()->settings->get_display_position();
        
        // Generate reactions HTML
        $reactions_html = $this->get_reactions_html();
        
        // Add reactions based on position
        if ('before_content' === $position || 'both' === $position) {
            $content = $reactions_html . $content;
        }
        
        if ('after_content' === $position || 'both' === $position) {
            $content = $content . $reactions_html;
        }
        
        // Enqueue scripts and styles
        wp_enqueue_style('reactifypress-style');
        wp_enqueue_script('reactifypress-script');
        
        // Add inline dynamic CSS
        $this->add_inline_styles();
        
        return $content;
    }

    /**
     * Get reactions HTML
     *
     * @param int|null $post_id Optional post ID (uses current post if not provided)
     * @return string Reactions HTML
     */
    public function get_reactions_html($post_id = null) {
        if (null === $post_id) {
            $post_id = get_the_ID();
        }
        
        if (!$post_id) {
            return '';
        }
        
        // Get active reactions
        $active_reactions = reactifypress()->settings->get_active_reactions();
        
        if (empty($active_reactions)) {
            return '';
        }
        
        // Get reaction counts
        $reaction_counts = reactifypress()->db->get_reactions_count($post_id);
        
        // Get current user reaction
        $user_id = get_current_user_id();
        $user_ip = $this->get_user_ip();
        $current_reaction = reactifypress()->db->get_user_reaction($post_id, $user_id, $user_ip);
        $current_reaction_type = $current_reaction ? $current_reaction['reaction_type'] : '';
        
        // Generate HTML
        $output = '<div class="reactifypress-container" data-post-id="' . esc_attr($post_id) . '">';
        $output .= '<div class="reactifypress-reactions">';
        
        foreach ($active_reactions as $type => $reaction) {
            $count = isset($reaction_counts[$type]) ? $reaction_counts[$type] : 0;
            $active_class = ($type === $current_reaction_type) ? 'reactifypress-active' : '';
            
            $output .= '<div class="reactifypress-reaction ' . esc_attr($active_class) . '" data-type="' . esc_attr($type) . '">';
            $output .= '<div class="reactifypress-icon">' . esc_html($reaction['icon']) . '</div>';
            
            // Display count if enabled
            if (reactifypress()->settings->get_setting('display', 'display_count', true)) {
                $output .= '<span class="reactifypress-count">' . esc_html($count) . '</span>';
            }
            
            // Display label if enabled
            if (reactifypress()->settings->get_setting('display', 'display_labels', false)) {
                $output .= '<span class="reactifypress-label">' . esc_html($reaction['label']) . '</span>';
            }
            
            // Add tooltip
            $output .= '<span class="reactifypress-tooltip">' . esc_html($reaction['label']) . '</span>';
            
            $output .= '</div>';
        }
        
        $output .= '</div>';
        $output .= '</div>';
        
        return $output;
    }

    /**
     * Add inline styles based on settings
     *
     * @return void
     */
    private function add_inline_styles() {
        $settings = reactifypress()->settings;
        $display_settings = $settings->get_setting('display');
        
        $css = "
            .reactifypress-reaction {
                background-color: {$display_settings['background_color']};
                color: {$display_settings['text_color']};
            }
            .reactifypress-reaction:hover {
                background-color: {$display_settings['hover_color']};
            }
            .reactifypress-reaction.reactifypress-active {
                background-color: {$display_settings['active_color']};
            }
            .reactifypress-tooltip {
                background-color: {$display_settings['tooltip_background']};
                color: {$display_settings['tooltip_text_color']};
            }
        ";
        
        wp_add_inline_style('reactifypress-style', $css);
    }

    /**
     * Get user IP address
     *
     * @return string User IP
     */
    private function get_user_ip() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        
        return sanitize_text_field($ip);
    }

    /**
     * Shortcode to display reactions
     *
     * @param int $post_id Post ID
     * @return string Reactions HTML
     */
    public function render_reactions_shortcode($post_id = null) {
        if (!$post_id) {
            $post_id = get_the_ID();
        }
        
        if (!$post_id) {
            return '';
        }
        
        // Enqueue scripts and styles
        wp_enqueue_style('reactifypress-style');
        wp_enqueue_script('reactifypress-script');
        
        // Add inline dynamic CSS
        $this->add_inline_styles();
        
        return $this->get_reactions_html($post_id);
    }
}
