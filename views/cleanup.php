<div class="wrap">
    <h1>Configurações de Limpeza de Logs</h1>
    <form method="post">
        <?php wp_nonce_field('fc_logs_cleanup_action', 'fc_logs_cleanup_nonce'); ?>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="days_to_keep">Excluir logs com mais de X dias</label></th>
                <td><input type="number" name="days_to_keep" value="<?php echo esc_attr($days_to_keep); ?>" class="small-text"> dias</td>
            </tr>
            <tr>
                <th scope="row"><label for="max_records">Limite máximo de registros</label></th>
                <td><input type="number" name="max_records" value="<?php echo esc_attr($max_records); ?>" class="small-text"> registros</td>
            </tr>
        </table>
        <p>
            <input type="submit" name="save" class="button button-primary" value="Salvar Configurações">
            <input type="submit" name="run_now" class="button" value="Executar Limpeza Agora">
        </p>
    </form>
</div>
