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
     * Static variable to track if styles were already added
     *
     * @var bool
     */
    private static $styles_added = false;

    /**
     * Constructor
     */
    public function __construct() {
        // Add reactions to content
        add_filter('the_content', array($this, 'add_reactions_to_content'), 15);
        
        // Register scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'register_scripts_styles'));
        
        // Add preload for performance
        add_action('wp_head', array($this, 'add_preload_hints'), 1);
    }

    /**
     * Add preload hints for better performance
     *
     * @return void
     */
    public function add_preload_hints() {
        if (!$this->should_load_assets()) {
            return;
        }
        
        ?>
        <link rel="preload" href="<?php echo esc_url(REACTIFYPRESS_PLUGIN_URL . 'assets/css/reactifypress.css'); ?>" as="style">
        <link rel="preload" href="<?php echo esc_url(REACTIFYPRESS_PLUGIN_URL . 'assets/js/reactifypress.js'); ?>" as="script">
        <?php
    }

    /**
     * Check if assets should be loaded
     *
     * @return bool
     */
    private function should_load_assets() {
        // Load on singular posts/pages that have reactions enabled
        if (is_singular()) {
            $post_type = get_post_type();
            return reactifypress()->settings->is_enabled_for_post_type($post_type);
        }
        
        // Also load if shortcode is used
        global $post;
        if ($post && has_shortcode($post->post_content, 'reactifypress')) {
            return true;
        }
        
        return false;
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
                'loading' => __('Loading...', 'reactifypress'),
                'login_required' => __('Please log in to react.', 'reactifypress'),
            ),
            'user_logged_in' => is_user_logged_in(),
            'require_login' => apply_filters('reactifypress_require_login', false),
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
        $enabled = $settings->is_enabled_for_post_type($post_type);
        
        // Allow filtering
        return apply_filters('reactifypress_should_display', $enabled, $post_type);
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
        
        // Check if we're in the main query
        if (!in_the_loop() || !is_main_query()) {
            return $content;
        }
        
        // Get display position
        $position = reactifypress()->settings->get_display_position();
        
        // Don't add if position is manual
        if ('manual' === $position) {
            return $content;
        }
        
        // Generate reactions HTML
        $reactions_html = $this->get_reactions_html();
        
        // Add wrapper for better positioning
        $reactions_html = '<div class="reactifypress-auto-insert">' . $reactions_html . '</div>';
        
        // Add reactions based on position
        if ('before_content' === $position || 'both' === $position) {
            $content = $reactions_html . $content;
        }
        
        if ('after_content' === $position || 'both' === $position) {
            $content = $content . $reactions_html;
        }
        
        // Enqueue scripts and styles
        $this->enqueue_assets();
        
        return $content;
    }

    /**
     * Enqueue assets when needed
     *
     * @return void
     */
    public function enqueue_assets() {
        wp_enqueue_style('reactifypress-style');
        wp_enqueue_script('reactifypress-script');
        
        // Add inline dynamic CSS only once
        if (!self::$styles_added) {
            $this->add_inline_styles();
            self::$styles_added = true;
        }
    }

    /**
     * Get reactions HTML
     *
     * @param int|null $post_id Optional post ID (uses current post if not provided)
     * @param array    $args    Optional arguments
     * @return string Reactions HTML
     */
    public function get_reactions_html($post_id = null, $args = array()) {
        if (null === $post_id) {
            $post_id = get_the_ID();
        }
        
        if (!$post_id) {
            return '';
        }
        
        // Parse arguments
        $defaults = array(
            'class' => '',
            'show_total' => null, // Will use setting if null
            'animate' => true,
        );
        $args = wp_parse_args($args, $defaults);
        
        // Get active reactions
        $active_reactions = reactifypress()->settings->get_active_reactions();
        
        if (empty($active_reactions)) {
            return '';
        }
        
        // Get reaction counts
        $reaction_counts = reactifypress()->db->get_reactions_count($post_id);
        $total_count = array_sum($reaction_counts);
        
        // Get current user reaction
        $user_id = get_current_user_id();
        $user_ip = $this->get_user_ip();
        $current_reaction = reactifypress()->db->get_user_reaction($post_id, $user_id, $user_ip);
        $current_reaction_type = $current_reaction ? $current_reaction['reaction_type'] : '';
        
        // Build classes
        $container_classes = array('reactifypress-container');
        if ($args['animate']) {
            $container_classes[] = 'reactifypress-animate';
        }
        if ($args['class']) {
            $container_classes[] = esc_attr($args['class']);
        }
        
        // Check if we should show total - use setting if not explicitly set in args
        $show_total = $args['show_total'];
        if ($show_total === null) {
            $show_total = reactifypress()->settings->get_setting('display', 'show_total', true);
        }
        
        // Generate HTML
        $output = '<div class="' . implode(' ', $container_classes) . '" data-post-id="' . esc_attr($post_id) . '">';
        
        // Add header with total count if enabled
        if ($show_total && $total_count > 0) {
            $output .= '<div class="reactifypress-header">';
            $output .= '<span class="reactifypress-total">';
            $output .= sprintf(
                _n('%s reaction', '%s reactions', $total_count, 'reactifypress'),
                '<strong>' . number_format_i18n($total_count) . '</strong>'
            );
            $output .= '</span>';
            $output .= '</div>';
        }
        
        $output .= '<div class="reactifypress-reactions">';
        
        foreach ($active_reactions as $type => $reaction) {
            $count = isset($reaction_counts[$type]) ? $reaction_counts[$type] : 0;
            $classes = array('reactifypress-reaction');
            
            if ($type === $current_reaction_type) {
                $classes[] = 'reactifypress-active';
            }
            
            if ($count > 0) {
                $classes[] = 'reactifypress-has-reactions';
            }
            
            $output .= '<div class="' . implode(' ', $classes) . '" data-type="' . esc_attr($type) . '" role="button" tabindex="0" aria-label="' . esc_attr($reaction['label']) . '">';
            $output .= '<div class="reactifypress-icon">' . esc_html($reaction['icon']) . '</div>';
            
            // Display count if enabled
            if (reactifypress()->settings->get_setting('display', 'display_count', true)) {
                $output .= '<span class="reactifypress-count">' . esc_html(number_format_i18n($count)) . '</span>';
            }
            
            // Display label if enabled
            if (reactifypress()->settings->get_setting('display', 'display_labels', false)) {
                $output .= '<span class="reactifypress-label">' . esc_html($reaction['label']) . '</span>';
            }
            
            // Add tooltip
            $output .= '<span class="reactifypress-tooltip" role="tooltip">' . esc_html($reaction['label']) . '</span>';
            
            $output .= '</div>';
        }
        
        $output .= '</div>';
        
        // Add loading indicator
        $output .= '<div class="reactifypress-loading" aria-hidden="true">';
        $output .= '<span class="reactifypress-spinner"></span>';
        $output .= '</div>';
        
        $output .= '</div>';
        
        return apply_filters('reactifypress_reactions_html', $output, $post_id, $args);
    }

    /**
     * Add inline styles based on settings
     *
     * @return void
     */
    public function add_inline_styles() {
        $settings = reactifypress()->settings;
        $display_settings = $settings->get_setting('display');
        
        // Add alignment styles
        $alignment = isset($display_settings['alignment']) ? $display_settings['alignment'] : 'left';
        $alignment_css = '';
        
        switch ($alignment) {
            case 'center':
                $alignment_css = 'justify-content: center;';
                break;
            case 'right':
                $alignment_css = 'justify-content: flex-end;';
                break;
            default:
                $alignment_css = 'justify-content: flex-start;';
        }
        
        $css = "
            :root {
                --reactifypress-bg: {$display_settings['background_color']};
                --reactifypress-text: {$display_settings['text_color']};
                --reactifypress-hover: {$display_settings['hover_color']};
                --reactifypress-active: {$display_settings['active_color']};
                --reactifypress-tooltip-bg: {$display_settings['tooltip_background']};
                --reactifypress-tooltip-text: {$display_settings['tooltip_text_color']};
            }
            
            .reactifypress-reactions {
                {$alignment_css}
            }
            
            .reactifypress-reaction {
                background-color: var(--reactifypress-bg);
                color: var(--reactifypress-text);
            }
            .reactifypress-reaction:hover {
                background-color: var(--reactifypress-hover);
            }
            .reactifypress-reaction.reactifypress-active {
                background-color: var(--reactifypress-active);
            }
            .reactifypress-tooltip {
                background-color: var(--reactifypress-tooltip-bg);
                color: var(--reactifypress-tooltip-text);
            }
            .reactifypress-tooltip:after {
                border-color: var(--reactifypress-tooltip-bg) transparent transparent transparent;
            }
        ";
        
        // Add custom CSS if provided
        if (isset($display_settings['custom_css']) && !empty($display_settings['custom_css'])) {
            $css .= "\n" . $display_settings['custom_css'];
        }
        
        // Add advanced settings custom CSS
        $advanced_settings = $settings->get_setting('advanced');
        if (isset($advanced_settings['custom_css']) && !empty($advanced_settings['custom_css'])) {
            $css .= "\n" . $advanced_settings['custom_css'];
        }
        
        // Add custom CSS hook
        $css = apply_filters('reactifypress_inline_styles', $css, $display_settings);
        
        wp_add_inline_style('reactifypress-style', $css);
    } = "
            :root {
                --reactifypress-bg: {$display_settings['background_color']};
                --reactifypress-text: {$display_settings['text_color']};
                --reactifypress-hover: {$display_settings['hover_color']};
                --reactifypress-active: {$display_settings['active_color']};
                --reactifypress-tooltip-bg: {$display_settings['tooltip_background']};
                --reactifypress-tooltip-text: {$display_settings['tooltip_text_color']};
            }
            
            .reactifypress-reaction {
                background-color: var(--reactifypress-bg);
                color: var(--reactifypress-text);
            }
            .reactifypress-reaction:hover {
                background-color: var(--reactifypress-hover);
            }
            .reactifypress-reaction.reactifypress-active {
                background-color: var(--reactifypress-active);
            }
            .reactifypress-tooltip {
                background-color: var(--reactifypress-tooltip-bg);
                color: var(--reactifypress-tooltip-text);
            }
            .reactifypress-tooltip:after {
                border-color: var(--reactifypress-tooltip-bg) transparent transparent transparent;
            }
        ";
        
        // Add custom CSS hook
        $css = apply_filters('reactifypress_inline_styles', $css, $display_settings);
        
        wp_add_inline_style('reactifypress-style', $css);
    }

    /**
     * Get user IP address
     *
     * @return string User IP
     */
    private function get_user_ip() {
        $ip = '';
        
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip_list = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($ip_list[0]);
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        
        return sanitize_text_field($ip);
    }

    /**
     * Shortcode to display reactions
     *
     * @param int $post_id Post ID
     * @param array $args Arguments
     * @return string Reactions HTML
     */
    public function render_reactions_shortcode($post_id = null, $args = array()) {
        if (!$post_id) {
            $post_id = get_the_ID();
        }
        
        if (!$post_id) {
            return '';
        }
        
        // Enqueue scripts and styles
        $this->enqueue_assets();
        
        return $this->get_reactions_html($post_id, $args);
    }
}