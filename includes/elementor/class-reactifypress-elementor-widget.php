<?php
/**
 * Elementor widget for ReactifyPress
 *
 * @package ReactifyPress
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * ReactifyPress Elementor Widget
 */
class ReactifyPress_Elementor_Widget extends \Elementor\Widget_Base {
    /**
     * Get widget name
     *
     * @return string Widget name
     */
    public function get_name() {
        return 'reactifypress';
    }

    /**
     * Get widget title
     *
     * @return string Widget title
     */
    public function get_title() {
        return __('ReactifyPress', 'reactifypress');
    }

    /**
     * Get widget icon
     *
     * @return string Widget icon
     */
    public function get_icon() {
        return 'eicon-heart';
    }

    /**
     * Get widget categories
     *
     * @return array Widget categories
     */
    public function get_categories() {
        return ['general'];
    }

    /**
     * Get widget keywords
     *
     * @return array Widget keywords
     */
    public function get_keywords() {
        return ['reaction', 'engage', 'like', 'emoticon', 'feedback'];
    }

    /**
     * Register widget controls
     *
     * @return void
     */
    protected function register_controls() {
        $this->start_controls_section(
            'section_content',
            [
                'label' => __('Content', 'reactifypress'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'post_id',
            [
                'label' => __('Post ID', 'reactifypress'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => '',
                'description' => __('Leave empty to use current post ID. Only change this if you want to show reactions from a specific post.', 'reactifypress'),
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_style',
            [
                'label' => __('Style', 'reactifypress'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'alignment',
            [
                'label' => __('Alignment', 'reactifypress'),
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'flex-start' => [
                        'title' => __('Left', 'reactifypress'),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => __('Center', 'reactifypress'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'flex-end' => [
                        'title' => __('Right', 'reactifypress'),
                        'icon' => 'eicon-text-align-right',
                    ],
                ],
                'default' => 'flex-start',
                'selectors' => [
                    '{{WRAPPER}} .reactifypress-reactions' => 'justify-content: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'spacing',
            [
                'label' => __('Spacing', 'reactifypress'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 50,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 10,
                ],
                'selectors' => [
                    '{{WRAPPER}} .reactifypress-reactions' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_control(
            'margin',
            [
                'label' => __('Margin', 'reactifypress'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .reactifypress-container' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Render widget output
     *
     * @return void
     */
    protected function render() {
        $settings = $this->get_settings_for_display();
        
        $post_id = !empty($settings['post_id']) ? intval($settings['post_id']) : get_the_ID();
        
        if (!$post_id) {
            return;
        }
        
        // Render reactions
        echo reactifypress()->frontend->render_reactions_shortcode($post_id);
    }

    /**
     * Render widget output in the editor
     *
     * @return void
     */
    protected function content_template() {
        ?>
        <div class="reactifypress-container elementor-reactifypress-placeholder">
            <div class="reactifypress-reactions">
                <div class="reactifypress-reaction">
                    <div class="reactifypress-icon">👍</div>
                    <span class="reactifypress-count">0</span>
                    <span class="reactifypress-tooltip">Like</span>
                </div>
                <div class="reactifypress-reaction">
                    <div class="reactifypress-icon">❤️</div>
                    <span class="reactifypress-count">0</span>
                    <span class="reactifypress-tooltip">Love</span>
                </div>
                <div class="reactifypress-reaction">
                    <div class="reactifypress-icon">😂</div>
                    <span class="reactifypress-count">0</span>
                    <span class="reactifypress-tooltip">Haha</span>
                </div>
            </div>
            <small><?php echo esc_html__('ReactifyPress reactions will be displayed here.', 'reactifypress'); ?></small>
        </div>
        <?php
    }
}
