<?php

class FC_UserLogs_Monitor {
    private static $old_usermeta_cache = [];

    public function __construct() {
        // Hook antes da atualização: salva metadados antigos
        add_action('personal_options_update', [$this, 'capture_old_usermeta']);
        add_action('edit_user_profile_update', [$this, 'capture_old_usermeta']);

        // Hook após atualização: loga as mudanças
        add_action('profile_update', array($this, 'log_user_update'), 10, 2);

        // Hooks do plugin LearnDash
        $this->register_learndash_hooks();
    }

    /**
     * Registra os hooks do LearnDash
     */
    private function register_learndash_hooks() {
        add_action('ld_added_group_access', [$this, 'log_ld_added_group_access'], 10, 2);
        add_action('ld_removed_group_access', [$this, 'log_ld_removed_group_access'], 10, 2);
    }

    public function log_ld_added_group_access($user_id, $group_id) {
        $current_user = wp_get_current_user();
        $group_title  = get_the_title($group_id);

        $this->insert_log([
            'logged_user_id'    => $current_user->ID,
            'logged_user_login' => $current_user->user_login,
            'logged_user_name'  => $current_user->display_name,
            'user_id'           => $user_id,
            'user_login'        => get_userdata($user_id)->user_login,
            'user_name'         => get_userdata($user_id)->display_name,
            'field_name'        => 'learndash_group_access_added',
            'old_value'         => '',
            'new_value'         => "Group ID: {$group_id} - {$group_title}",
        ]);
    }

    public function log_ld_removed_group_access($user_id, $group_id) {
        $current_user = wp_get_current_user();
        $group_title  = get_the_title($group_id);

        $this->insert_log([
            'logged_user_id'    => $current_user->ID,
            'logged_user_login' => $current_user->user_login,
            'logged_user_name'  => $current_user->display_name,
            'user_id'           => $user_id,
            'user_login'        => get_userdata($user_id)->user_login,
            'user_name'         => get_userdata($user_id)->display_name,
            'field_name'        => 'learndash_group_access_removed',
            'old_value'         => "Group ID: {$group_id} - {$group_title}",
            'new_value'         => '',
        ]);
    }    

    public function capture_old_usermeta($user_id) {
        self::$old_usermeta_cache[$user_id] = $this->get_flat_usermeta($user_id);
    }

    public function log_user_update($user_id, $old_user_data) {
        if (!is_admin()) return;        

        $current_user = wp_get_current_user();
        $new_user_data = get_userdata($user_id);
        
        $old_fields = $this->object_to_array_recursive($old_user_data);
        $new_fields = $this->object_to_array_recursive($new_user_data);

        /** Start Capturar alterações na tabela User */

        foreach ($new_fields as $fieldsName => $fields) {
            // Ignora se for muito interno
            if (in_array($fieldsName, ['ID', 'caps'])) continue;
            
            // Garante que $fields seja array            
            if (!is_array($fields)) continue;

            error_log(print_r($fields, true));

            foreach ($fields as $field => $new_value) {
                $old_value = $old_fields[$fieldsName][$field] ?? null;

                // Compara e registra log
                if (isset($old_value) && isset($new_value) && $old_value !== $new_value) {
                    $this->insert_log([
                        'logged_user_id'    => $current_user->ID,
                        'logged_user_login' => $current_user->user_login,
                        'logged_user_name'  => $current_user->display_name,
                        'user_id'           => $user_id,
                        'user_login'        => $new_user_data->user_login,
                        'user_name'         => $new_user_data->display_name,
                        'field_name'        => $field,
                        'old_value'         => is_scalar($old_value) ? $old_value : json_encode($old_value),
                        'new_value'         => is_scalar($new_value) ? $new_value : json_encode($new_value),
                    ]);
                }
            }
        }

        /** End Capturar alterações na tabela User */



    
        /** Start Capturar alterações na tabela Usermeta */

        $old_usermeta = self::$old_usermeta_cache[$user_id] ?? [];
        $new_usermeta = $this->get_flat_usermeta($user_id);

        // Compara os METADADOS do usuário
        foreach ($new_usermeta as $meta_key => $new_value) {
            $old_value = $old_usermeta[$meta_key] ?? null;

            if ($old_value !== $new_value) {
                $this->insert_log([
                    'logged_user_id'    => $current_user->ID,
                    'logged_user_login' => $current_user->user_login,
                    'logged_user_name'  => $current_user->display_name,
                    'user_id'           => $user_id,
                    'user_login'        => $new_user_data->user_login,
                    'user_name'         => $new_user_data->display_name,
                    'field_name'        => "meta:{$meta_key}",
                    'old_value'         => is_scalar($old_value) ? $old_value : json_encode($old_value),
                    'new_value'         => is_scalar($new_value) ? $new_value : json_encode($new_value),
                ]);
            }
        }        

        /** End Capturar alterações na tabela Usermeta */

    }

    private function get_flat_usermeta($user_id) {
        $meta = get_user_meta($user_id);
        $flat = [];

        foreach ($meta as $key => $values) {
            $flat[$key] = $values[0] ?? null; // Pega só o primeiro valor
        }

        return $flat;
    }    

    private function insert_log($data) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'user_logs_fc';

        $wpdb->insert($table_name, [
            'logged_user_id'    => $data['logged_user_id'],
            'logged_user_login' => $data['logged_user_login'],
            'logged_user_name'  => $data['logged_user_name'],
            'user_id'           => $data['user_id'],
            'user_login'        => $data['user_login'],
            'user_name'         => $data['user_name'],
            'field_name'        => $data['field_name'],
            'old_value'         => $data['old_value'],
            'new_value'         => $data['new_value'],
            'date'              => current_time('mysql'),
        ]);
    }

    // Transforma objeto em array
    private function object_to_array_recursive($data) {
        if (is_object($data)) {
            $data = get_object_vars($data);
        }

        if (is_array($data)) {
            return array_map([$this, 'object_to_array_recursive'], $data);
        }

        return $data;
    }
}
