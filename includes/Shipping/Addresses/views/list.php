<div class="wrap">
    <h2>
        <?php _e('Triidy_Automation  - Productos Enviados y Recibidos', TRIIDY_AUTOMATION_SLUG); ?>
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
        $list_table = new \Triidy_Automation\Shipping\Addresses\AddressesList();
        
        $page  = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_STRIPPED );
        $paged = filter_input( INPUT_GET, 'paged', FILTER_SANITIZE_NUMBER_INT );

        printf( '<input type="hidden" name="page" value="%s" />', $page );
        printf( '<input type="hidden" name="paged" value="%d" />', $paged );

        $list_table->process_bulk_action();
        $list_table->prepare_items();
        $list_table->search_box('search', 'search_id');
        $list_table->display();
        ?>
    </form>
</div>