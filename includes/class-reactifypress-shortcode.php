<?php
/**
 * Shortcode class for ReactifyPress
 *
 * @package ReactifyPress
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Shortcode class
 */
class ReactifyPress_Shortcode {
    /**
     * Constructor
     */
    public function __construct() {
        // Register shortcode
        add_shortcode('reactifypress', array($this, 'shortcode_callback'));
        
        // Add Elementor widget (if Elementor is active)
        add_action('elementor/widgets/widgets_registered', array($this, 'register_elementor_widget'));
    }

    /**
     * Shortcode callback
     *
     * @param array $atts Shortcode attributes
     * @return string Shortcode output
     */
    public function shortcode_callback($atts) {
        $atts = shortcode_atts(
            array(
                'post_id' => get_the_ID(),
            ),
            $atts,
            'reactifypress'
        );
        
        $post_id = intval($atts['post_id']);
        
        if (!$post_id) {
            return '';
        }
        
        return reactifypress()->frontend->render_reactions_shortcode($post_id);
    }

    /**
     * Register Elementor widget
     *
     * @return void
     */
    public function register_elementor_widget() {
        // Check if Elementor is installed and activated
        if (!did_action('elementor/loaded')) {
            return;
        }
        
        // Include the Elementor widget class
        require_once REACTIFYPRESS_PLUGIN_DIR . 'includes/elementor/class-reactifypress-elementor-widget.php';
        
        // Register the widget
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new ReactifyPress_Elementor_Widget());
    }
}
