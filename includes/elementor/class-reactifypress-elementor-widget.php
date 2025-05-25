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
class ReactifyPress_Elementor_Widget extends \Elementor\Widget_Base
{
    /**
     * Get widget name
     *
     * @return string Widget name
     */
    public function get_name()
    {
        return 'reactifypress';
    }

    /**
     * Get widget title
     *
     * @return string Widget title
     */
    public function get_title()
    {
        return __('ReactifyPress', 'reactifypress');
    }

    /**
     * Get widget icon
     *
     * @return string Widget icon
     */
    public function get_icon()
    {
        return 'eicon-heart';
    }

    /**
     * Get widget categories
     *
     * @return array Widget categories
     */
    public function get_categories()
    {
        return ['general', 'pro-elements'];
    }

    /**
     * Get widget keywords
     *
     * @return array Widget keywords
     */
    public function get_keywords()
    {
        return ['reaction', 'engage', 'like', 'emoticon', 'feedback', 'social', 'emoji'];
    }

    /**
     * Get script depends
     *
     * @return array Script dependencies
     */
    public function get_script_depends()
    {
        // Ensure scripts are loaded in editor
        if (\Elementor\Plugin::$instance->editor->is_edit_mode()) {
            return ['jquery'];
        }
        return ['reactifypress-script'];
    }

    /**
     * Get style depends
     *
     * @return array Style dependencies
     */
    public function get_style_depends()
    {
        return ['reactifypress-style'];
    }

    /**
     * Register widget controls
     *
     * @return void
     */
    protected function register_controls()
    {
        // Content Section
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

        $this->add_control(
            'preview_mode',
            [
                'label' => __('Preview Mode', 'reactifypress'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('On', 'reactifypress'),
                'label_off' => __('Off', 'reactifypress'),
                'return_value' => 'yes',
                'default' => 'yes',
                'description' => __('Show sample reactions in editor preview.', 'reactifypress'),
                'condition' => [
                    'post_id' => '',
                ],
            ]
        );

        $this->end_controls_section();

        // Style Section
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
                    'space-between' => [
                        'title' => __('Justify', 'reactifypress'),
                        'icon' => 'eicon-text-align-justify',
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
                'size_units' => ['px', 'em'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 50,
                        'step' => 1,
                    ],
                    'em' => [
                        'min' => 0,
                        'max' => 3,
                        'step' => 0.1,
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

        $this->add_responsive_control(
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

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'typography',
                'label' => __('Typography', 'reactifypress'),
                'selector' => '{{WRAPPER}} .reactifypress-reaction',
            ]
        );

        $this->add_control(
            'icon_size',
            [
                'label' => __('Icon Size', 'reactifypress'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', 'em'],
                'range' => [
                    'px' => [
                        'min' => 10,
                        'max' => 50,
                        'step' => 1,
                    ],
                    'em' => [
                        'min' => 0.5,
                        'max' => 3,
                        'step' => 0.1,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 18,
                ],
                'selectors' => [
                    '{{WRAPPER}} .reactifypress-icon' => 'font-size: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Advanced Styling
        $this->start_controls_section(
            'section_advanced_style',
            [
                'label' => __('Advanced Styling', 'reactifypress'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'override_colors',
            [
                'label' => __('Override Plugin Colors', 'reactifypress'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'reactifypress'),
                'label_off' => __('No', 'reactifypress'),
                'return_value' => 'yes',
                'default' => '',
            ]
        );

        $this->add_control(
            'button_background',
            [
                'label' => __('Button Background', 'reactifypress'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .reactifypress-reaction' => 'background-color: {{VALUE}} !important;',
                ],
                'condition' => [
                    'override_colors' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'button_text_color',
            [
                'label' => __('Button Text Color', 'reactifypress'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .reactifypress-reaction' => 'color: {{VALUE}} !important;',
                ],
                'condition' => [
                    'override_colors' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'button_hover_background',
            [
                'label' => __('Button Hover Background', 'reactifypress'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .reactifypress-reaction:hover' => 'background-color: {{VALUE}} !important;',
                ],
                'condition' => [
                    'override_colors' => 'yes',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'button_box_shadow',
                'label' => __('Box Shadow', 'reactifypress'),
                'selector' => '{{WRAPPER}} .reactifypress-reaction',
            ]
        );

        $this->add_control(
            'button_border_radius',
            [
                'label' => __('Border Radius', 'reactifypress'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .reactifypress-reaction' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
    protected function render()
    {
        $settings = $this->get_settings_for_display();

        $post_id = !empty($settings['post_id']) ? intval($settings['post_id']) : get_the_ID();

        // In editor mode without a post ID, show preview
        if (\Elementor\Plugin::$instance->editor->is_edit_mode() && !$post_id && $settings['preview_mode'] === 'yes') {
            $this->render_preview();
            return;
        }

        if (!$post_id) {
            if (\Elementor\Plugin::$instance->editor->is_edit_mode()) {
                echo '<div class="elementor-alert elementor-alert-info">';
                echo esc_html__('Please select a post ID or save the page to see reactions.', 'reactifypress');
                echo '</div>';
            }
            return;
        }

        // Ensure styles and scripts are enqueued
        wp_enqueue_style('reactifypress-style');
        wp_enqueue_script('reactifypress-script');

        // Add inline styles if needed
        reactifypress()->frontend->add_inline_styles();

        // Render reactions
        echo reactifypress()->frontend->get_reactions_html($post_id);
    }

    /**
     * Render preview for editor
     *
     * @return void
     */
    private function render_preview()
    {
        $settings = reactifypress()->settings->get_settings();
        $active_reactions = reactifypress()->settings->get_active_reactions();

        if (empty($active_reactions)) {
            echo '<div class="elementor-alert elementor-alert-warning">';
            echo esc_html__('No active reactions configured. Please configure reactions in ReactifyPress settings.', 'reactifypress');
            echo '</div>';
            return;
        }

?>
        <div class="reactifypress-container reactifypress-preview" data-post-id="0">
            <div class="reactifypress-reactions">
                <?php
                $sample_counts = [12, 8, 5, 3, 2, 1];
                $i = 0;
                foreach ($active_reactions as $type => $reaction) :
                    $count = isset($sample_counts[$i]) ? $sample_counts[$i] : 0;
                ?>
                    <div class="reactifypress-reaction" data-type="<?php echo esc_attr($type); ?>">
                        <div class="reactifypress-icon"><?php echo esc_html($reaction['icon']); ?></div>
                        <?php if ($settings['display']['display_count']) : ?>
                            <span class="reactifypress-count"><?php echo esc_html($count); ?></span>
                        <?php endif; ?>
                        <?php if ($settings['display']['display_labels']) : ?>
                            <span class="reactifypress-label"><?php echo esc_html($reaction['label']); ?></span>
                        <?php endif; ?>
                        <span class="reactifypress-tooltip"><?php echo esc_html($reaction['label']); ?></span>
                    </div>
                <?php
                    $i++;
                endforeach;
                ?>
            </div>
        </div>
        <style>
            .reactifypress-preview .reactifypress-reaction {
                background-color: <?php echo esc_attr($settings['display']['background_color']); ?>;
                color: <?php echo esc_attr($settings['display']['text_color']); ?>;
            }

            .reactifypress-preview .reactifypress-reaction:hover {
                background-color: <?php echo esc_attr($settings['display']['hover_color']); ?>;
            }

            .reactifypress-preview .reactifypress-tooltip {
                background-color: <?php echo esc_attr($settings['display']['tooltip_background']); ?>;
                color: <?php echo esc_attr($settings['display']['tooltip_text_color']); ?>;
            }

            .reactifypress-preview .reactifypress-tooltip:after {
                border-color: <?php echo esc_attr($settings['display']['tooltip_background']); ?> transparent transparent transparent;
            }
        </style>
    <?php
    }

    /**
     * Render widget output in the editor (JavaScript)
     *
     * @return void
     */
    protected function content_template()
    {
    ?>
        <#
            var postId=settings.post_id || '' ;
            var previewMode=settings.preview_mode || 'yes' ;

            if (!postId && previewMode==='yes' ) {
            // Get reactions from PHP
            var reactions=<?php echo json_encode(reactifypress()->settings->get_active_reactions()); ?>;
            var displaySettings=<?php echo json_encode(reactifypress()->settings->get_setting('display')); ?>;

            if (Object.keys(reactions).length===0) {
            #>
            <div class="elementor-alert elementor-alert-warning">
                <?php echo esc_html__('No active reactions configured. Please configure reactions in ReactifyPress settings.', 'reactifypress'); ?>
            </div>
            <#
                } else {
                #>
                <div class="reactifypress-container reactifypress-preview-elementor" data-post-id="0">
                    <div class="reactifypress-reactions">
                        <#
                            var sampleCounts=[12, 8, 5, 3, 2, 1];
                            var i=0;
                            _.each(reactions, function(reaction, type) {
                            var count=sampleCounts[i] || 0;
                            #>
                            <div class="reactifypress-reaction" data-type="{{ type }}">
                                <div class="reactifypress-icon">{{{ reaction.icon }}}</div>
                                <# if (displaySettings.display_count) { #>
                                    <span class="reactifypress-count">{{ count }}</span>
                                    <# } #>
                                        <# if (displaySettings.display_labels) { #>
                                            <span class="reactifypress-label">{{ reaction.label }}</span>
                                            <# } #>
                                                <span class="reactifypress-tooltip">{{ reaction.label }}</span>
                            </div>
                            <#
                                i++;
                                });
                                #>
                    </div>
                    <small style="display: block; text-align: center; margin-top: 10px; color: #999;">
                        <?php echo esc_html__('ReactifyPress - Preview Mode', 'reactifypress'); ?>
                    </small>
                </div>
                <style>
                    .reactifypress-preview-elementor .reactifypress-reaction {
                        background-color: {
                                {
                                displaySettings.background_color
                            }
                        }

                        ;

                        color: {
                                {
                                displaySettings.text_color
                            }
                        }

                        ;
                    }

                    .reactifypress-preview-elementor .reactifypress-reaction:hover {
                        background-color: {
                                {
                                displaySettings.hover_color
                            }
                        }

                        ;
                    }

                    .reactifypress-preview-elementor .reactifypress-tooltip {
                        background-color: {
                                {
                                displaySettings.tooltip_background
                            }
                        }

                        ;

                        color: {
                                {
                                displaySettings.tooltip_text_color
                            }
                        }

                        ;
                    }

                    .reactifypress-preview-elementor .reactifypress-tooltip:after {
                        border-color: {
                                {
                                displaySettings.tooltip_background
                            }
                        }

                        transparent transparent transparent;
                    }
                </style>
                <#
                    }
                    } else if (!postId) {
                    #>
                    <div class="elementor-alert elementor-alert-info">
                        <?php echo esc_html__('Please select a post ID or save the page to see reactions.', 'reactifypress'); ?>
                    </div>
                    <#
                        } else {
                        #>
                        <div class="elementor-alert elementor-alert-info">
                            <?php echo esc_html__('Reactions will be displayed for post ID: ', 'reactifypress'); ?>{{ postId }}
                        </div>
                        <#
                            }
                            #>
                    <?php
                }
            }
