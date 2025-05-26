<?php
/**
 * Plugin Name: ReactifyPress
 * Plugin URI: https://samuelbaer.ch/reactifypress
 * Description: Ermöglicht Besuchern, auf Beiträge mit verschiedenen Emoticons zu reagieren, ähnlich wie bei sozialen Netzwerken.
 * Version: 2.1.3
 * Requires at least: 5.6
 * Requires PHP: 7.4
 * Author: sambbaer
 * Author URI: https://samuelbaer.ch
 * Text Domain: reactifypress
 * Domain Path: /languages
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('REACTIFYPRESS_VERSION', '2.1.3');
define('REACTIFYPRESS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('REACTIFYPRESS_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Main plugin class
 */
class ReactifyPress {
    /**
     * Instance of the plugin
     *
     * @var ReactifyPress
     */
    private static $instance = null;

    /**
     * Settings instance
     *
     * @var ReactifyPress_Settings
     */
    public $settings;

    /**
     * Frontend instance
     *
     * @var ReactifyPress_Frontend
     */
    public $frontend;

    /**
     * Admin instance
     *
     * @var ReactifyPress_Admin
     */
    public $admin;

    /**
     * Database instance
     *
     * @var ReactifyPress_DB
     */
    public $db;

    /**
     * Ajax Handler instance
     *
     * @var ReactifyPress_Ajax_Handler
     */
    public $ajax_handler;

    /**
     * Shortcode instance
     *
     * @var ReactifyPress_Shortcode
     */
    public $shortcode;

    /**
     * Get plugin instance
     *
     * @return ReactifyPress
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Class constructor
     */
    private function __construct() {
        // Load plugin textdomain
        add_action('plugins_loaded', array($this, 'load_textdomain'));

        // Include required files
        $this->includes();

        // Initialize classes
        $this->init_classes();

        // Register activation and deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }

    /**
     * Load plugin textdomain
     *
     * @return void
     */
    public function load_textdomain()
    {
        // Diese Zeile ändern
        load_plugin_textdomain('reactifypress', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    /**
     * Include required files
     *
     * @return void
     */
    private function includes() {
        require_once REACTIFYPRESS_PLUGIN_DIR . 'includes/class-reactifypress-db.php';
        require_once REACTIFYPRESS_PLUGIN_DIR . 'includes/class-reactifypress-settings.php';
        require_once REACTIFYPRESS_PLUGIN_DIR . 'includes/class-reactifypress-frontend.php';
        require_once REACTIFYPRESS_PLUGIN_DIR . 'includes/class-reactifypress-admin.php';
        require_once REACTIFYPRESS_PLUGIN_DIR . 'includes/class-reactifypress-ajax-handler.php';
        require_once REACTIFYPRESS_PLUGIN_DIR . 'includes/class-reactifypress-shortcode.php';
    }

    /**
     * Initialize plugin classes
     *
     * @return void
     */
    private function init_classes() {
        $this->db = new ReactifyPress_DB();
        $this->settings = new ReactifyPress_Settings();
        $this->frontend = new ReactifyPress_Frontend();
        $this->admin = new ReactifyPress_Admin();
        $this->ajax_handler = new ReactifyPress_Ajax_Handler();
        $this->shortcode = new ReactifyPress_Shortcode();
    }

    /**
     * Plugin activation hook
     *
     * @return void
     */
    public function activate() {
        // Create database tables
        $this->db->create_tables();

        // Add default settings
        $this->settings->add_default_settings();

        // Clear permalinks
        flush_rewrite_rules();
    }

    /**
     * Plugin deactivation hook
     *
     * @return void
     */
    public function deactivate() {
        // Clear permalinks
        flush_rewrite_rules();
    }
}

// Initialize the plugin
function reactifypress() {
    return ReactifyPress::get_instance();
}

// Kick off the plugin
reactifypress();
