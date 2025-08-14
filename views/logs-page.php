<?php
global $wpdb;
$table_name = $wpdb->prefix . 'user_logs_fc';

// Filtros
$filters = [
    'user_id' => $_GET['user_id'] ?? '',
    'user_name' => $_GET['user_name'] ?? '',
    'user_login' => $_GET['user_login'] ?? ''
];

$paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$per_page = get_user_meta(get_current_user_id(), 'fc_user_logs_per_page', true);
$per_page = $per_page ? intval($per_page) : 10;
$offset = ($paged - 1) * $per_page;

// SQL base
$sql_where = "WHERE 1=1";
$params = [];

if ($filters['user_id']) {
    $sql_where .= " AND user_id = %d";
    $params[] = $filters['user_id'];
}

if ($filters['user_name']) {
    $sql_where .= " AND user_name LIKE %s";
    $params[] = '%' . $wpdb->esc_like($filters['user_name']) . '%';
}

if ($filters['user_login']) {
    $sql_where .= " AND user_login LIKE %s";
    $params[] = '%' . $wpdb->esc_like($filters['user_login']) . '%';
}

// Contagem total de usuários distintos
if (!empty($params)) {
    $count_query = $wpdb->prepare(
        "SELECT COUNT(DISTINCT user_id) FROM $table_name $sql_where",
        ...$params
    );
} else {
    $count_query = "SELECT COUNT(DISTINCT user_id) FROM $table_name $sql_where";
}

$total_items = $wpdb->get_var($count_query);
$total_pages = ceil($total_items / $per_page);

// Consulta paginada
$sql = "SELECT user_id, user_name, user_login, COUNT(*) as changes
        FROM $table_name
        $sql_where
        GROUP BY user_id
        ORDER BY MAX(date) DESC
        LIMIT %d OFFSET %d";

$params[] = $per_page;
$params[] = $offset;

$query = $wpdb->prepare($sql, ...$params);
$results = $wpdb->get_results($query);

// URL base para paginação
$base_url = admin_url('admin.php?page=fc_user_logs_admin');
foreach ($filters as $key => $val) {
    if (!empty($val)) {
        $base_url .= "&$key=" . urlencode($val);
    }
}
?>

<div class="wrap">
    <h1><?php esc_html_e('User Logs', 'fc-user-logs'); ?></h1>

    <form method="get" class="fc-user-logs-filters">
        <input type="hidden" name="page" value="fc_user_logs_admin">
        <input type="text" name="user_id" placeholder="ID" value="<?= esc_attr($filters['user_id']) ?>">
        <input type="text" name="user_name" placeholder="Nome" value="<?= esc_attr($filters['user_name']) ?>">
        <input type="text" name="user_login" placeholder="Login" value="<?= esc_attr($filters['user_login']) ?>">
        <input type="submit" value="Filtrar" class="button button-primary">
    </form>

    
    <table class="widefat striped fc-user-logs-table">
        <thead>
            <tr>
                <th><?php _e('Usuário', 'fc-user-logs'); ?></th>
                <th><?php _e('Nome', 'fc-user-logs'); ?></th>
                <th><?php _e('Login', 'fc-user-logs'); ?></th>
                <th><?php _e('Ações', 'fc-user-logs'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($results)): ?>
                <tr><td colspan="4">Nenhum usuário encontrado.</td></tr>
            <?php else: ?>
                <?php foreach ($results as $row): ?>
                    <tr>
                        <td data-label="Usuário"><?php echo esc_html($row->user_id); ?></td>
                        <td data-label="Nome"><?php echo esc_html($row->user_name); ?></td>
                        <td data-label="Login"><?php echo esc_html($row->user_login); ?></td>
                        <td data-label="Ações">
                            <a href="<?php echo admin_url('admin.php?page=fc_user_logs_view&user_id=' . $row->user_id); ?>">
                                <?php _e('View Logs', 'fc-user-logs'); ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    

    <?php if ($total_pages > 1): ?>
        <div class="tablenav">
            <?php
                $current_items = is_array($results) ? count($results) : 0;
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
