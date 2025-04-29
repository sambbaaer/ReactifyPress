<?php
/**
 * Database class for ReactifyPress
 *
 * @package ReactifyPress
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Database class
 */
class ReactifyPress_DB {
    /**
     * Table name for reactions
     *
     * @var string
     */
    private $table_reactions;

    /**
     * Constructor
     */
    public function __construct() {
        global $wpdb;
        $this->table_reactions = $wpdb->prefix . 'reactifypress_reactions';
    }

    /**
     * Create database tables
     *
     * @return void
     */
    public function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $this->table_reactions (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            post_id bigint(20) NOT NULL,
            user_id bigint(20) NOT NULL DEFAULT 0,
            user_ip varchar(100) NOT NULL,
            reaction_type varchar(50) NOT NULL,
            date_created datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY post_id (post_id),
            KEY user_id (user_id),
            KEY reaction_type (reaction_type)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Get reactions count for a post
     *
     * @param int $post_id Post ID
     * @return array Reaction counts by type
     */
    public function get_reactions_count($post_id) {
        global $wpdb;
        
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT reaction_type, COUNT(*) as count 
                FROM $this->table_reactions 
                WHERE post_id = %d 
                GROUP BY reaction_type",
                $post_id
            ),
            ARRAY_A
        );
        
        $counts = array();
        if ($results) {
            foreach ($results as $result) {
                $counts[$result['reaction_type']] = (int) $result['count'];
            }
        }
        
        return $counts;
    }

    /**
     * Add or update a reaction
     *
     * @param int    $post_id       Post ID
     * @param int    $user_id       User ID (0 for guests)
     * @param string $user_ip       User IP address
     * @param string $reaction_type Reaction type
     * @return bool|int False on failure, reaction ID on success
     */
    public function add_reaction($post_id, $user_id, $user_ip, $reaction_type) {
        global $wpdb;
        
        // Check if user already reacted to this post
        $existing_reaction = $this->get_user_reaction($post_id, $user_id, $user_ip);
        
        if ($existing_reaction) {
            // If user is reacting with the same type, remove the reaction (toggle)
            if ($existing_reaction['reaction_type'] === $reaction_type) {
                return $this->delete_reaction($existing_reaction['id']);
            }
            
            // Update existing reaction
            $updated = $wpdb->update(
                $this->table_reactions,
                array('reaction_type' => $reaction_type),
                array('id' => $existing_reaction['id']),
                array('%s'),
                array('%d')
            );
            
            return $updated ? $existing_reaction['id'] : false;
        }
        
        // Insert new reaction
        $inserted = $wpdb->insert(
            $this->table_reactions,
            array(
                'post_id' => $post_id,
                'user_id' => $user_id,
                'user_ip' => $user_ip,
                'reaction_type' => $reaction_type,
                'date_created' => current_time('mysql')
            ),
            array('%d', '%d', '%s', '%s', '%s')
        );
        
        return $inserted ? $wpdb->insert_id : false;
    }

    /**
     * Delete a reaction
     *
     * @param int $reaction_id Reaction ID
     * @return bool True on success, false on failure
     */
    public function delete_reaction($reaction_id) {
        global $wpdb;
        
        return $wpdb->delete(
            $this->table_reactions,
            array('id' => $reaction_id),
            array('%d')
        );
    }

    /**
     * Get user reaction for a post
     *
     * @param int    $post_id Post ID
     * @param int    $user_id User ID
     * @param string $user_ip User IP address
     * @return array|false User reaction data or false if not found
     */
    public function get_user_reaction($post_id, $user_id, $user_ip) {
        global $wpdb;
        
        $query = "SELECT * FROM $this->table_reactions WHERE post_id = %d";
        $params = array($post_id);
        
        if ($user_id > 0) {
            $query .= " AND user_id = %d";
            $params[] = $user_id;
        } else {
            $query .= " AND user_ip = %s AND user_id = 0";
            $params[] = $user_ip;
        }
        
        return $wpdb->get_row($wpdb->prepare($query, $params), ARRAY_A);
    }

    /**
     * Get users who reacted with a specific reaction type
     *
     * @param int    $post_id       Post ID
     * @param string $reaction_type Reaction type
     * @param int    $limit         Limit (default 10)
     * @return array User IDs
     */
    public function get_users_by_reaction($post_id, $reaction_type, $limit = 10) {
        global $wpdb;
        
        return $wpdb->get_col(
            $wpdb->prepare(
                "SELECT user_id 
                FROM $this->table_reactions 
                WHERE post_id = %d AND reaction_type = %s AND user_id > 0
                ORDER BY date_created DESC
                LIMIT %d",
                $post_id, $reaction_type, $limit
            )
        );
    }
}
