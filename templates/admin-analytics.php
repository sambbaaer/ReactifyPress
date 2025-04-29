<?php
/**
 * Admin analytics template
 *
 * @package ReactifyPress
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Get top posts with most reactions
global $wpdb;
$table_reactions = $wpdb->prefix . 'reactifypress_reactions';

$top_posts = $wpdb->get_results(
    "SELECT post_id, COUNT(*) as count 
    FROM $table_reactions 
    GROUP BY post_id 
    ORDER BY count DESC 
    LIMIT 10",
    ARRAY_A
);

// Get reaction distribution
$reaction_stats = $wpdb->get_results(
    "SELECT reaction_type, COUNT(*) as count 
    FROM $table_reactions 
    GROUP BY reaction_type 
    ORDER BY count DESC",
    ARRAY_A
);

// Get recent reactions
$recent_reactions = $wpdb->get_results(
    "SELECT r.*, p.post_title 
    FROM $table_reactions r 
    LEFT JOIN {$wpdb->posts} p ON r.post_id = p.ID 
    ORDER BY r.date_created DESC 
    LIMIT 20",
    ARRAY_A
);
?>

<div class="wrap reactifypress-settings-page">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <div class="reactifypress-analytics-container">
        <!-- Reactions Overview -->
        <div class="reactifypress-analytics-card">
            <h2 class="reactifypress-analytics-title"><?php esc_html_e('Reactions Overview', 'reactifypress'); ?></h2>
            
            <div class="reactifypress-overview-stats">
                <?php
                $total_reactions = $wpdb->get_var("SELECT COUNT(*) FROM $table_reactions");
                $unique_users = $wpdb->get_var("SELECT COUNT(DISTINCT user_id) FROM $table_reactions WHERE user_id > 0");
                $posts_with_reactions = $wpdb->get_var("SELECT COUNT(DISTINCT post_id) FROM $table_reactions");
                ?>
                
                <div class="reactifypress-stat-box">
                    <span class="reactifypress-stat-number"><?php echo esc_html(number_format_i18n($total_reactions)); ?></span>
                    <span class="reactifypress-stat-label"><?php esc_html_e('Total Reactions', 'reactifypress'); ?></span>
                </div>
                
                <div class="reactifypress-stat-box">
                    <span class="reactifypress-stat-number"><?php echo esc_html(number_format_i18n($unique_users)); ?></span>
                    <span class="reactifypress-stat-label"><?php esc_html_e('Unique Users', 'reactifypress'); ?></span>
                </div>
                
                <div class="reactifypress-stat-box">
                    <span class="reactifypress-stat-number"><?php echo esc_html(number_format_i18n($posts_with_reactions)); ?></span>
                    <span class="reactifypress-stat-label"><?php esc_html_e('Posts with Reactions', 'reactifypress'); ?></span>
                </div>
            </div>
        </div>
        
        <!-- Reaction Types Distribution -->
        <div class="reactifypress-analytics-card">
            <h2 class="reactifypress-analytics-title"><?php esc_html_e('Reaction Distribution', 'reactifypress'); ?></h2>
            
            <?php if ($reaction_stats) : ?>
                <table class="reactifypress-analytics-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Reaction', 'reactifypress'); ?></th>
                            <th><?php esc_html_e('Count', 'reactifypress'); ?></th>
                            <th><?php esc_html_e('Percentage', 'reactifypress'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        foreach ($reaction_stats as $stat) :
                            $reaction_type = $stat['reaction_type'];
                            $count = $stat['count'];
                            $percentage = ($total_reactions > 0) ? round(($count / $total_reactions) * 100, 1) : 0;
                            
                            // Get reaction details
                            $reaction_label = $reaction_type;
                            $reaction_icon = '';
                            
                            foreach ($active_reactions as $key => $reaction) {
                                if ($key === $reaction_type) {
                                    $reaction_label = $reaction['label'];
                                    $reaction_icon = $reaction['icon'];
                                    break;
                                }
                            }
                        ?>
                            <tr>
                                <td>
                                    <?php if ($reaction_icon) : ?>
                                        <span class="reactifypress-reaction-icon"><?php echo esc_html($reaction_icon); ?></span>
                                    <?php endif; ?>
                                    <?php echo esc_html($reaction_label); ?>
                                </td>
                                <td><?php echo esc_html(number_format_i18n($count)); ?></td>
                                <td>
                                    <div class="reactifypress-progress-bar">
                                        <div class="reactifypress-progress" style="width: <?php echo esc_attr($percentage); ?>%;"></div>
                                        <span><?php echo esc_html($percentage); ?>%</span>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p><?php esc_html_e('No reaction data available yet.', 'reactifypress'); ?></p>
            <?php endif; ?>
        </div>
        
        <!-- Top Posts -->
        <div class="reactifypress-analytics-card">
            <h2 class="reactifypress-analytics-title"><?php esc_html_e('Top Posts by Reactions', 'reactifypress'); ?></h2>
            
            <?php if ($top_posts) : ?>
                <table class="reactifypress-analytics-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Post', 'reactifypress'); ?></th>
                            <th><?php esc_html_e('Reactions', 'reactifypress'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($top_posts as $post) : 
                            $post_title = get_the_title($post['post_id']);
                            $post_link = get_permalink($post['post_id']);
                            $post_reaction_counts = reactifypress()->db->get_reactions_count($post['post_id']);
                        ?>
                            <tr>
                                <td>
                                    <a href="<?php echo esc_url($post_link); ?>" target="_blank">
                                        <?php echo esc_html($post_title); ?>
                                    </a>
                                </td>
                                <td>
                                    <div class="reactifypress-post-reactions">
                                        <span class="reactifypress-total-count"><?php echo esc_html(number_format_i18n($post['count'])); ?></span>
                                        
                                        <div class="reactifypress-reaction-breakdown">
                                            <?php foreach ($active_reactions as $key => $reaction) : 
                                                $count = isset($post_reaction_counts[$key]) ? $post_reaction_counts[$key] : 0;
                                                if ($count > 0) :
                                            ?>
                                                <span class="reactifypress-reaction-stat" title="<?php echo esc_attr($reaction['label']); ?>">
                                                    <?php echo esc_html($reaction['icon']); ?> <?php echo esc_html(number_format_i18n($count)); ?>
                                                </span>
                                            <?php endif; endforeach; ?>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p><?php esc_html_e('No reaction data available yet.', 'reactifypress'); ?></p>
            <?php endif; ?>
        </div>
        
        <!-- Recent Activity -->
        <div class="reactifypress-analytics-card">
            <h2 class="reactifypress-analytics-title"><?php esc_html_e('Recent Activity', 'reactifypress'); ?></h2>
            
            <?php if ($recent_reactions) : ?>
                <table class="reactifypress-analytics-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Post', 'reactifypress'); ?></th>
                            <th><?php esc_html_e('Reaction', 'reactifypress'); ?></th>
                            <th><?php esc_html_e('User', 'reactifypress'); ?></th>
                            <th><?php esc_html_e('Date', 'reactifypress'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_reactions as $reaction) : 
                            $post_title = $reaction['post_title'];
                            $post_link = get_permalink($reaction['post_id']);
                            $reaction_type = $reaction['reaction_type'];
                            $user_id = $reaction['user_id'];
                            $date = date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($reaction['date_created']));
                            
                            // Get reaction details
                            $reaction_label = $reaction_type;
                            $reaction_icon = '';
                            
                            foreach ($active_reactions as $key => $r) {
                                if ($key === $reaction_type) {
                                    $reaction_label = $r['label'];
                                    $reaction_icon = $r['icon'];
                                    break;
                                }
                            }
                            
                            // Get user info
                            $user_info = $user_id > 0 ? get_userdata($user_id) : null;
                            $user_display = $user_info ? $user_info->display_name : __('Guest', 'reactifypress');
                        ?>
                            <tr>
                                <td>
                                    <a href="<?php echo esc_url($post_link); ?>" target="_blank">
                                        <?php echo esc_html($post_title); ?>
                                    </a>
                                </td>
                                <td>
                                    <span class="reactifypress-reaction-icon"><?php echo esc_html($reaction_icon); ?></span>
                                    <?php echo esc_html($reaction_label); ?>
                                </td>
                                <td>
                                    <?php if ($user_id > 0) : ?>
                                        <a href="<?php echo esc_url(get_edit_user_link($user_id)); ?>">
                                            <?php echo esc_html($user_display); ?>
                                        </a>
                                    <?php else : ?>
                                        <?php echo esc_html($user_display); ?>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo esc_html($date); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p><?php esc_html_e('No recent reactions available.', 'reactifypress'); ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
/* Analytics specific styles */
.reactifypress-overview-stats {
    display: flex;
    justify-content: space-between;
    margin: 20px 0;
}

.reactifypress-stat-box {
    background: #f5f5f5;
    border-radius: 4px;
    padding: 20px;
    text-align: center;
    width: 30%;
}

.reactifypress-stat-number {
    display: block;
    font-size: 24px;
    font-weight: bold;
    margin-bottom: 5px;
    color: #0073aa;
}

.reactifypress-stat-label {
    color: #666;
}

.reactifypress-progress-bar {
    background: #f1f1f1;
    border-radius: 10px;
    height: 20px;
    overflow: hidden;
    position: relative;
    width: 100%;
}

.reactifypress-progress {
    background: #0073aa;
    height: 100%;
    position: absolute;
    left: 0;
    top: 0;
}

.reactifypress-progress-bar span {
    position: absolute;
    right: 10px;
    top: 0;
    line-height: 20px;
    font-size: 12px;
    color: #fff;
    text-shadow: 0 0 2px rgba(0, 0, 0, 0.5);
}

.reactifypress-reaction-breakdown {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 5px;
}

.reactifypress-reaction-stat {
    background: #f5f5f5;
    border-radius: 20px;
    padding: 2px 8px;
    font-size: 12px;
}

.reactifypress-post-reactions {
    display: flex;
    align-items: center;
    gap: 10px;
}

.reactifypress-total-count {
    font-weight: bold;
}

.reactifypress-reaction-icon {
    margin-right: 5px;
}
</style>
