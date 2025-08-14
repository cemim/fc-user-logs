<?php

class FC_UserLogs_Cleanup {
    private $option_days = 'fc_user_logs_days_to_keep';
    private $option_limit = 'fc_user_logs_max_records';
    private $table_name;

    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'user_logs_fc';

        add_action('admin_menu', [$this, 'add_submenu']);
        add_action('admin_init', [$this, 'handle_form_submission']);

        if (!wp_next_scheduled('fc_user_logs_cleanup_cron')) {
            wp_schedule_event(time(), 'daily', 'fc_user_logs_cleanup_cron');
        }

        add_action('fc_user_logs_cleanup_cron', [$this, 'run_cleanup_now']);
    }

    public function add_submenu() {
        add_submenu_page(
            'fc_user_logs_admin',
            'Limpeza Automática',
            'Limpeza Automática',
            'manage_options',
            'fc_user_logs_clean',
            [$this, 'render_page']
        );
    }

    public function render_page() {
        $days_to_keep = get_option($this->option_days, 30);
        $max_records = get_option($this->option_limit, 1000);
        include plugin_dir_path(__FILE__) . '../views/cleanup.php';
    }

    public function handle_form_submission() {
        if (!isset($_POST['fc_logs_cleanup_nonce']) || !wp_verify_nonce($_POST['fc_logs_cleanup_nonce'], 'fc_logs_cleanup_action')) {
            return;
        }

        if (isset($_POST['days_to_keep'])) {
            update_option($this->option_days, intval($_POST['days_to_keep']));
        }

        if (isset($_POST['max_records'])) {
            update_option($this->option_limit, intval($_POST['max_records']));
        }

        if (isset($_POST['run_now'])) {
            $this->run_cleanup_now();
            add_action('admin_notices', function () {
                echo '<div class="updated"><p>Limpeza manual executada.</p></div>';
            });
        }
    }

    public function run_cleanup_now() {
        global $wpdb;
        $days_to_keep = get_option($this->option_days, 30);
        $max_records = get_option($this->option_limit, 1000);

        // Limpar registros antigos
        $wpdb->query($wpdb->prepare(
            "DELETE FROM {$this->table_name} WHERE date < DATE_SUB(NOW(), INTERVAL %d DAY)",
            $days_to_keep
        ));

        // Garantir que não exceda o limite
        $count = $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name}");
        if ($count > $max_records) {
            $to_delete = $count - $max_records;
            $sql = $wpdb->prepare(
                "DELETE FROM {$this->table_name} WHERE id IN (
                    SELECT id FROM (
                        SELECT id FROM {$this->table_name} ORDER BY date ASC LIMIT %d
                    ) as temp
                )", $to_delete
            );

            $wpdb->query($sql);
        }
    }

    public static function deactivate() {
        wp_clear_scheduled_hook('fc_user_logs_cleanup_cron');
    }
}
