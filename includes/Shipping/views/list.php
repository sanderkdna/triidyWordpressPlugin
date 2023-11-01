<div class="wrap">
    <h2>
        <?php _e('Triidy_Automation ', TRIIDY_AUTOMATION_SLUG); ?>
        <a href="<?php echo admin_url('admin.php?page=triidy_automation-shipping&action=create'); ?>" class="add-new-h2"><?php _e('Agregar Nuevo', TRIIDY_AUTOMATION_SLUG); ?></a>
    </h2>
    
    <?php if (isset($_REQUEST['message'])): ?>
        <?php list($type, $message) = explode('|', $_REQUEST['message']); ?>
        <div id="message" class="notice-<?php echo esc_attr($type) ?> notice is-dismissible">
            <p><strong><?php echo esc_html($message) ?></strong></p>
            <button type="button" class="notice-dismiss"><span class="screen-reader-text">Descartar este aviso.</span>
            </button>
        </div>
    <?php endif; ?>

    <form method="post">
        <?php

        $list_table = new \Triidy_Automation\Shipping\ShippingList();

        $page = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_STRIPPED);
        $paged = filter_input(INPUT_GET, 'paged', FILTER_SANITIZE_NUMBER_INT);

        printf('<input type="hidden" name="page" value="%s" />', $page);
        printf('<input type="hidden" name="paged" value="%d" />', $paged);

        $list_table->process_bulk_action();
        $list_table->prepare_items();
        $list_table->search_box('search', 'search_id');
        $list_table->display();

        ?>

        <?
            $user = wp_get_current_user();
            #print_r($user);
            $client_id = get_option(TRIIDY_AUTOMATION_SETTING_CLIENT_ID);
            $username = get_option(TRIIDY_AUTOMATION_SETTING_USERNAME);
            $password = get_option(TRIIDY_AUTOMATION_SETTING_CLAVE);

            // echo $username.' : '.$password;
            //$url =  TRIIDY_AUTOMATION_GLOBAL_URL.'?m=login&action=check&username='.$username.'&password='.$password.'&usuariowordpress=1';
            // echo $url;
            //echo 'listar ordenes enviadas aqui';
            //echo '<iframe id="inlineFrameExample" title="Detalle de Triidy_Automation.me" width="100%" height="800" src="'.$url.'"></iframe>';
        ?>
    </form>
</div>