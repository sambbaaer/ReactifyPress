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
        $active_reactions = reactifypress()->settings->get_active_reactions();
        if (!array_key_exists($reaction_type, $active_reactions)) {
            wp_send_json_error(array(
                'message' => __('Invalid reaction type.', 'reactifypress')
            ));
        }
        
        // Get user info
        $user_id = get_current_user_id();
        $user_ip = $this->get_anonymized_ip();
        
        // Save reaction
        $result = reactifypress()->db->add_reaction($post_id, $user_id, $user_ip, $reaction_type);
        
        if (!$result) {
            wp_send_json_error(array(
                'message' => __('Failed to save reaction.', 'reactifypress')
            ));
        }
        
        // Get updated counts and user reaction
        $counts = reactifypress()->db->get_reactions_count($post_id);
        $current_reaction = reactifypress()->db->get_user_reaction($post_id, $user_id, $user_ip);
        $current_reaction_type = $current_reaction ? $current_reaction['reaction_type'] : '';
        
        wp_send_json_success(array(
            'message' => __('Reaction saved successfully.', 'reactifypress'),
            'counts' => $counts,
            'current_reaction' => $current_reaction_type
        ));
    }

    /**
     * Handle getting reactions for a post
     *
     * @return void
     */
    public function handle_get_reactions() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'reactifypress_nonce')) {
            wp_send_json_error(array(
                'message' => __('Security check failed.', 'reactifypress')
            ));
        }
        
        // Check post ID
        if (!isset($_POST['post_id'])) {
            wp_send_json_error(array(
                'message' => __('Missing post ID.', 'reactifypress')
            ));
        }
        
        $post_id = intval($_POST['post_id']);
        
        // Validate post exists
        if (!get_post($post_id)) {
            wp_send_json_error(array(
                'message' => __('Invalid post.', 'reactifypress')
            ));
        }
        
        // Get counts
        $counts = reactifypress()->db->get_reactions_count($post_id);
        
        // Get user reaction
        $user_id = get_current_user_id();
        $user_ip = $this->get_anonymized_ip();
        $current_reaction = reactifypress()->db->get_user_reaction($post_id, $user_id, $user_ip);
        $current_reaction_type = $current_reaction ? $current_reaction['reaction_type'] : '';
        
        wp_send_json_success(array(
            'counts' => $counts,
            'current_reaction' => $current_reaction_type
        ));
    }

    /**
     * Get anonymized user IP address for GDPR compliance
     *
     * @return string Anonymized IP
     */
    private function get_anonymized_ip() {
        $ip = '';
        
        // Try to get IP from various sources
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            // HTTP_X_FORWARDED_FOR can contain multiple IPs, get the first one
            $ip_list = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($ip_list[0]);
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        
        // Sanitize IP
        $ip = filter_var($ip, FILTER_VALIDATE_IP);
        
        // Anonymize the IP (remove last octet for IPv4 or last 80 bits for IPv6)
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            // For IPv4, replace the last octet with '0'
            $anonymized_ip = preg_replace('/\.\d+$/', '.0', $ip);
        } elseif (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            // For IPv6, mask the last 80 bits (last 5 groups)
            $anonymized_ip = preg_replace('/:[0-9a-f]{1,4}(:[0-9a-f]{1,4}){4}$/i', ':0:0:0:0:0', $ip);
        } else {
            // Invalid IP, use a placeholder
            $anonymized_ip = '0.0.0.0';
        }
        
        return sanitize_text_field($anonymized_ip);
    }
}
