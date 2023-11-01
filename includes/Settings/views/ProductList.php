<?php


namespace Triidy_Automation\Shipping;


if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class ShippingList extends \WP_List_Table {
    
    /**
     * AddressesList constructor.
     */
    function __construct() {
        parent::__construct([
            'singular' => 'Orden',
            'plural' => 'Ordenes',
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
        _e('No se encontraron Prestamos registrados', TRIIDY_AUTOMATION_SLUG);
    }
    
    /**
     * Valores de columna predeterminados si no se encuentra devolución de llamada
     * @param $item
     * @param $column_name
     * @return string
     */
    function column_default($item, $column_name) {
        switch ($column_name) {
            case 'order_id':
                return $item->order_id;
            case 'destinatario':
                return $item->destinatario;
            case 'telefono_destinatario':
                return $item->telefono_destinatario;
            case 'email_destinatario':
                return $item->email_destinatario;
            case 'ciudad_destinatario':
                return $item->ciudad_destinatario;
            case 'estado_prestamo':
                return $item->estado_prestamo;
            case 'envio_guia':
                return $item->envio_guia;
            case 'valor_servicio':
                return $item->valor_servicio;
            case 'response':
                $response = json_decode($item->response, true);
                return $response['message'];    
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
            'cb' => '<input type="checkbox" />',
            'order_id' => __('# Orden', 'Envios'),
            'destinatario' => __('Destinatario', 'Envios'),
            'telefono_destinatario' => __('Teléfono Destinatario', 'Envios'),
            'email_destinatario' => __('Correo Electrónico (Destinatario)', 'Envios'),
            'ciudad_destinatario' => __('Ciudad Destinatario', 'Envios'),
            'envio_guia' => __('TriidyId', 'Envios'),
            'valor_servicio' => __('Valor Servicio', 'Envios'),
            'response' => __('Respuesta', 'Envios'),
        
        ];
        return $columns;
    }
    
    /**
     * Agrega las opciones para acceso a edición y eliminación del registro.
     * @param object $item
     * @return string
     */
    function column_destinatario($item) {
        $actions = [];
        $url_edit = admin_url('admin.php?page=triidy_automation-shipping&action=edit&id=' . $item->id);
        $url_delete = admin_url('admin.php?page=triidy_automation-shipping&action=delete&id=' . $item->id);
        
        if(!$item->envio_guia) {
            $actions['edit'] = sprintf('<a href="%s" data-id="%d" title="%s">%s</a>', $url_edit, $item->id, __('Actualizar dirección', TRIIDY_AUTOMATION_SLUG), __('Actualizar', TRIIDY_AUTOMATION_SLUG));
            $actions['delete'] = sprintf('<a href="%s" class="submitdelete" data-id="%d" title="%s">%s</a>', $url_delete, $item->id, __('Eliminar Dirección', TRIIDY_AUTOMATION_SLUG), __('Eliminar', TRIIDY_AUTOMATION_SLUG));
        }

        return sprintf('<strong>%1$s</strong>%2$s', $item->destinatario, $this->row_actions($actions));
    }
    
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
    function get_bulk_actions() {
        $actions = [
            'bulk-delete' => __('Eliminar Selección', TRIIDY_AUTOMATION_SLUG),
        ];
        return $actions;
    }
    
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
                    $old_data = $wpdb->get_row($wpdb->prepare('SELECT * FROM ' . TRIIDY_AUTOMATION_TABLE_ORDENES . ' WHERE id = %d', $id));
                    $res = $wpdb->delete(TRIIDY_AUTOMATION_TABLE_ORDENES, ['id' => $id]);
                    if($res) {
                        ShippingFunctions::apiTriidy_AutomationDelete($old_data->envio_guia);
                    }
                }
                $page_list = 'admin.php?page=' . TRIIDY_AUTOMATION_SLUG . '-shipping';
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
        
        $this->items = ShippingFunctions::getAll($args);
        $this->set_pagination_args([
            'total_items' => ShippingFunctions::count(),
            'per_page' => $per_page
        ]);
    }
}
