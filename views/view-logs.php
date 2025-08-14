<?php
global $wpdb;
$table_name = $wpdb->prefix . 'user_logs_fc';

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
$date_filter = $_GET['date'] ?? '';
$paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$per_page = get_user_meta(get_current_user_id(), 'fc_user_logs_per_page', true);
$per_page = $per_page ? intval($per_page) : 10;
$offset = ($paged - 1) * $per_page;

$sql_where = "WHERE 1=1";
$params = [];

if ($user_id) {
    $sql_where .= " AND user_id = %d";
    $params[] = $user_id;
}

if ($date_filter) {
    $sql_where .= " AND DATE(date) = %s";
    $params[] = $date_filter;
}

// Total de registros
if (!empty($params)) {
    $total_query = $wpdb->prepare("SELECT COUNT(*) FROM $table_name $sql_where", ...$params);
} else {
    $total_query = "SELECT COUNT(*) FROM $table_name $sql_where";
}
$total_items = $wpdb->get_var($total_query);
$total_pages = ceil($total_items / $per_page);

// Query paginada
$sql_logs = "$sql_where ORDER BY date DESC LIMIT %d OFFSET %d";
$params[] = $per_page;
$params[] = $offset;

$query = $wpdb->prepare("SELECT * FROM $table_name $sql_logs", ...$params);
$logs = $wpdb->get_results($query);

// Gerar URL base para paginação
$base_url = admin_url('admin.php?page=fc_user_logs_view');
if ($user_id) $base_url .= '&user_id=' . urlencode($user_id);
if ($date_filter) $base_url .= '&date=' . urlencode($date_filter);
?>

<div class="wrap">
    <h1><?php _e('User Logs Details', 'fc-user-logs'); ?></h1>
    <h2>
    <?php if ($user_id): ?>
        <?php _e('Logs do Usuário', 'fc-user-logs'); ?> #<?php echo $user_id; ?>
    <?php else: ?>
        <?php _e('Todos os Logs de Usuário', 'fc-user-logs'); ?>
    <?php endif; ?>
    </h2>    

    <form method="get" class="fc-user-logs-filters">
        <input type="hidden" name="page" value="fc_user_logs_view">
        <?php if ($user_id): ?>
            <input type="hidden" name="user_id" value="<?php echo esc_attr($user_id); ?>">
        <?php endif; ?>
        <input type="date" name="date" value="<?php echo esc_attr($date_filter); ?>">
        <input type="submit" value="Filtrar" class="button">
    </form>

    <table class="widefat striped fc-user-logs-table">
        <thead>
            <tr>
                <th>Campo</th>
                <th>Valor Antigo</th>
                <th>Valor Novo</th>
                <th>Alterado por</th>
                <th>Data</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($logs)): ?>
                <tr><td colspan="5">Nenhum log encontrado.</td></tr>
            <?php else: ?>
                <?php foreach ($logs as $log): ?>
                    <tr>
                        <td data-label="Campo"><?php echo esc_html($log->field_name); ?></td>
                        <td data-label="Valor Antigo"><?php echo esc_html($log->old_value); ?></td>
                        <td data-label="Valor Novo"><?php echo esc_html($log->new_value); ?></td>
                        <td data-label="Alterado por"><?php echo esc_html($log->logged_user_name . " ({$log->logged_user_login})"); ?></td>
                        <td data-label="Data"><?php echo esc_html($log->date); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <?php if ($total_pages > 1): ?>
        <div class="tablenav">
            <?php
                $current_items = is_array($logs) ? count($logs) : 0;
                $start_item = $offset + 1;
                $end_item = $offset + $current_items;
            ?>
            <span>
                Mostrando <?php echo $start_item . '–' . $end_item; ?> de <?php echo $total_items; ?> registros.
            </span>            
            
            <div class="tablenav-pages">
                <span class="pagination-links">
                    <?php if ($paged > 1): ?>
                        <a class="prev-page" href="<?php echo $base_url . '&paged=' . ($paged - 1); ?>">&laquo;</a>
                    <?php endif; ?>

                    <span class="current-page">Página <?php echo $paged; ?> de <?php echo $total_pages; ?></span>

                    <?php if ($paged < $total_pages): ?>
                        <a class="next-page" href="<?php echo $base_url . '&paged=' . ($paged + 1); ?>">&raquo;</a>
                    <?php endif; ?>
                </span>
            </div>
        </div>
    <?php endif; ?>
</div>
