<?php


namespace Triidy_Automation\Shipping\Addresses;


if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class AddressesList extends \WP_List_Table {
    
    /**
     * AddressesList constructor.
     */
    function __construct() {
        parent::__construct([
            'singular' => 'Producto',
            'plural' => 'Productos',
            'ajax' => false
        ]);
    }
    
    
    /**
     * Agrega clases css a la tabla de la grid
     */
    function get_table_classes() {
        return ['widefat', 'fixed', 'striped', $this->_args['plural']];
    }
    
    /**
     * Mensaje mostrado cuando no se encuentran registros disponibles
     */
    function no_items() {
        _e('No se encontraron Productos Registrados ', TRIIDY_AUTOMATION_SLUG);
    }
    
    /**
     * Valores de columna predeterminados si no se encuentra devolución de llamada
     * @param $item
     * @param $column_name
     * @return string
     */
    function column_default($item, $column_name) {
        switch ($column_name) {
            case 'nombre':
                return $item->nombre;
            case 'precio':
                return $item->precio;
            case 'idTriidy':
                return $item->idTriidy;
            case 'response':
                $response = json_decode($item->response, true);
                return $response['message'];    
            case 'source':
                return $item->source;                
            default:
                return isset($item->$column_name) ? $item->$column_name : '';
        }
    }
    
    /**
     * Permite obtener el nombre de las columnas
     * @return array
     */
    function get_columns() {
        $columns = [
            'nombre' => __('nombre', TRIIDY_AUTOMATION_SLUG),
            'precio' => __('precio', TRIIDY_AUTOMATION_SLUG),
            'idTriidy' => __('idTriidy', TRIIDY_AUTOMATION_SLUG),
            'response' => __('response', TRIIDY_AUTOMATION_SLUG),
            'source' => __('source', TRIIDY_AUTOMATION_SLUG),
        ];
        return $columns;
    }
    
    /**
     * Agrega las opciones para acceso a edición y eliminación del registro.
     * @param object $item
     * @return string
     */
    // function column_nombre($item) {
    //     $actions = [];
    //     $url_edit = admin_url('admin.php?page=triidy_automation-addresses&action=edit&id=' . $item->id);
    //     $url_delete = admin_url('admin.php?page=triidy_automation-addresses&action=delete&id=' . $item->id);
        
    //     $actions['edit'] = sprintf('<a href="%s" data-id="%d" title="%s">%s</a>', $url_edit, $item->id, __('Actualizar dirección', 'triidy_automation'), __('Actualizar', 'triidy_automation'));
    //     $actions['delete'] = sprintf('<a href="%s" class="submitdelete" data-id="%d" title="%s">%s</a>', $url_delete, $item->id, __('Eliminar Dirección', 'triidy_automation'), __('Eliminar', 'triidy_automation'));
    //     return sprintf('<strong>%1$s</strong>%2$s', $item->nombre, $this->row_actions());
    // }
    
    /**
     * Define la lista de columnas que permiten el ordenamiento de datos.
     * @return array
     */
    function get_sortable_columns() {
        $sortable_columns = [
            'nombre' => ['nombre', true],
        ];
        return $sortable_columns;
    }
    
    /**
     * Agrega items a la lista de acciones por lotes
     * @return array
     */
    // function get_bulk_actions() {
    //     $actions = [
    //         'bulk-delete' => __('Eliminar Selección', TRIIDY_AUTOMATION_SLUG),
    //     ];
    //     return $actions;
    // }
    
    /**
     * Esta función premite procesar las acciones por lotes previamente registradas en get_bulk_actions()
     */
    public function process_bulk_action() {
        $action = $this->current_action();
        $ids = isset($_REQUEST['id']) ? wp_parse_id_list(wp_unslash($_REQUEST['id'])) : [];
        switch ($action) {
            case 'bulk-delete':
                global $wpdb;
                foreach ($ids as $id) {
                    $wpdb->delete(TRIIDY_AUTOMATION_TABLE_PRODUCTOS, ['id' => $id]);
                }
                $page_list = 'admin.php?page=' . TRIIDY_AUTOMATION_SLUG . '-addresses';
                $redirect_to = $page_list . '&' . http_build_query(['message' => __('success|Los registros han sido eliminados.', TRIIDY_AUTOMATION_SLUG)]);
                wp_safe_redirect($redirect_to);
                break;
        }
    }
    
    /**
     * Renderizar la columna de la casilla de verificación
     * @param object $item
     * @return string
     */
    function column_cb($item) {
        return sprintf(
            '<input type="checkbox" name="id[]" value="%d" />',
            $item->id
        );
    }
    
    /**
     * Prepara la clase para mostrar en forma de grid WordPress
     */
    function prepare_items() {
        
        $columns = $this->get_columns();
        $hidden = [];
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = [$columns, $hidden, $sortable];
        $per_page = 20;
        $current_page = $this->get_pagenum();
        $offset = ($current_page - 1) * $per_page;
        $this->page_status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '2';
        
        // only ncessary because we have sample data
        $args = [
            'offset' => $offset,
            'number' => $per_page,
        ];
        
        if (isset($_REQUEST['orderby']) && isset($_REQUEST['order'])) {
            $args['orderby'] = $_REQUEST['orderby'];
            $args['order'] = $_REQUEST['order'];
        }
        
        if (isset($_REQUEST['s']) && !empty($_REQUEST['s'])) {
            $args['search'] = trim($_REQUEST['s']);
        }
        
        $this->items = AddressesFunctions::getAll($args);
        $this->set_pagination_args([
            'total_items' => AddressesFunctions::count(),
            'per_page' => $per_page
        ]);
    }
}