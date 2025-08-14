<?php
/**
* Plugin Name: FC User Logs
* Plugin URI: https://www.wordpress.org/fc-user-logs
* Description: Registra logs de alteração nos usuários, compatível com o LMS Learndash
* Version: 1.0
* Requires at least: 6.5
* Requires PHP: 7.4
* Author: Filipe Cemim
* License: GPL v2 or later
* License URI: https://www.gnu.org/licenses/gpl-2.0.html
* Text Domain: fc-user-logs
* Domain Path: /languages
*/
/*
FC User Logs is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
 
FC User Logs is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with FC User Logs. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if( !class_exists( 'FC_UserLogs' ) ){

    class FC_UserLogs {
        public static $instance;

        public function __construct() {
            self::$instance = $this;

            // Define constants used througout the plugin
            $this->define_constants();
            $this->load_textdomain();

            // Adicionar menu
            add_action('admin_menu',  array($this, 'add_menu'));
            add_filter('set-screen-option', [__CLASS__, 'set_screen_option'], 10, 3);

            require_once FC_USER_LOGS_PATH . 'includes/class-fc-user-logs-monitor.php';
            new FC_UserLogs_Monitor();

            add_action('admin_enqueue_scripts', array($this, 'fc_user_logs_enqueue_admin_styles'));

            require_once FC_USER_LOGS_PATH . 'includes/class-fc-user-logs-cleanup.php';
            new FC_UserLogs_Cleanup();
        }

        public function add_menu()
        {
            $hookMenu = add_menu_page(
                esc_html__('FC User Logs', 'fc-user-logs'),
                'FC User Logs',
                'manage_options',
                'fc_user_logs_admin',
                array($this, 'fc_user_logs_admin_logs_page'),
                'dashicons-visibility'
            );

            $hookSubmenu = add_submenu_page(
                'fc_user_logs_admin',
                'View Logs',
                'View Logs',
                'manage_options',
                'fc_user_logs_view',
                [$this, 'render_view_logs']
            );

            add_action("load-$hookSubmenu", [$this, 'add_screen_options']);
            add_action("load-$hookMenu", [$this, 'add_screen_options']);
        }

        public function render_view_logs() {
            require FC_USER_LOGS_PATH . 'views/view-logs.php';
        }

        public function add_screen_options() {
            add_screen_option('per_page', [
                'label' => 'Registros por página',
                'default' => 10,
                'option' => 'fc_user_logs_per_page',
            ]);
        }

        public static function set_screen_option($status, $option, $value) {
            if ($option === 'fc_user_logs_per_page') {
                return (int) $value;
            }
            return $status;
        }                

        public static function create_logs_table() {
            global $wpdb;
            $table_name = $wpdb->prefix . 'user_logs_fc';

            $charset_collate = $wpdb->get_charset_collate();

            $sql = "CREATE TABLE IF NOT EXISTS $table_name (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                logged_user_id BIGINT UNSIGNED,
                logged_user_login VARCHAR(60),
                logged_user_name VARCHAR(100),
                user_id BIGINT UNSIGNED,
                user_login VARCHAR(60),
                user_name VARCHAR(100),
                field_name VARCHAR(100),
                old_value LONGTEXT,
                new_value LONGTEXT,
                date DATETIME DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id)
            ) $charset_collate;";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }

        public function fc_user_logs_enqueue_admin_styles($hook)
        {            
            // Aplica o estilo apenas na página principal ou submenu do plugin
            if ($hook === 'toplevel_page_fc_user_logs_admin' || $hook === 'fc-user-logs_page_fc_user_logs_view') {
                wp_enqueue_style(
                    'fc-user-logs-admin-style',
                    FC_USER_LOGS_URL .'assets/css/admin-style.css',
                    array(),
                    '1.0.0'
                );
            }
        }

        public function fc_user_logs_admin_logs_page()
        {
            if (!current_user_can('manage_options')) {
                return;
            }

            require(FC_USER_LOGS_PATH . 'views/logs-page.php');            
        }        

         /**
         * Define Constants
         */
        public function define_constants(){
            // Path/URL to root of this plugin, with trailing slash.
            define ( 'FC_USER_LOGS_PATH', plugin_dir_path( __FILE__ ) );            
            define ( 'FC_USER_LOGS_URL', plugin_dir_url( __FILE__ ) );
            define ( 'FC_USER_LOGS_VERSION', '1.0.0' );
        }

        public function load_textdomain()
        {
            load_plugin_textdomain(
                'fc-user-logs',
                false,
                dirname(plugin_basename(__FILE__)) .'/languages/'
            );
        }

        /**
         * Activate the plugin
         */
        public static function activate(){
            self::create_logs_table();
            update_option('rewrite_rules', '' );
        }

        /**
         * Deactivate the plugin
         */
        public static function deactivate(){   
            register_deactivation_hook(__FILE__, ['FC_UserLogs_Cleanup', 'deactivate']);         
            flush_rewrite_rules();
        }

        /**
         * Uninstall the plugin
         */
        public static function uninstall()
        {

        }

    }
}

if( class_exists( 'FC_UserLogs' ) ){
    // Installation and uninstallation hooks
    register_activation_hook( __FILE__, array( 'FC_UserLogs', 'activate'));
    register_deactivation_hook( __FILE__, array( 'FC_UserLogs', 'deactivate'));
    register_uninstall_hook( __FILE__, array( 'FC_UserLogs', 'uninstall' ) );

    $fcUserLogs = new FC_UserLogs();
}