<?php
/**
 * Ajax Handler class for ReactifyPress
 *
 * @package ReactifyPress
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Ajax Handler class
 */
class ReactifyPress_Ajax_Handler {
    /**
     * Constructor
     */
    public function __construct() {
        // Register Ajax actions
        add_action('wp_ajax_reactifypress_add_reaction', array($this, 'handle_add_reaction'));
        add_action('wp_ajax_nopriv_reactifypress_add_reaction', array($this, 'handle_add_reaction'));
        
        add_action('wp_ajax_reactifypress_get_reactions', array($this, 'handle_get_reactions'));
        add_action('wp_ajax_nopriv_reactifypress_get_reactions', array($this, 'handle_get_reactions'));
    }

    /**
     * Handle adding/updating a reaction
     *
     * @return void
     */
    public function handle_add_reaction() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'reactifypress_nonce')) {
            wp_send_json_error(array(
                'message' => __('Security check failed.', 'reactifypress')
            ));
        }

        // Check required fields
        if (!isset($_POST['post_id']) || !isset($_POST['reaction_type'])) {
            wp_send_json_error(array(
                'message' => __('Missing required fields.', 'reactifypress')
            ));
        }

        $post_id = intval($_POST['post_id']);
        $reaction_type = sanitize_key($_POST['reaction_type']);
        
        // Validate post exists
        if (!get_post($post_id)) {
            wp_send_json_error(array(
                'message' => __('Invalid post.', 'reactifypress')
            ));
        }
        
        // Validate reaction type
        $active_reactions = reactifypress()->settings->get_