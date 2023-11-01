<?php


namespace Triidy_Automation\Shipping\Addresses;


class AddressesFunctions {
    
    /**
     * Permite obtener los datos de un registro de dirección, dado su ID unico.
     * @param $id
     * @return bool
     */
    static function getById($id) {
        if (!$id)
            return false;
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare('SELECT * FROM ' . TRIIDY_AUTOMATION_TABLE_PRODUCTOS . ' WHERE id = %d', $id));
    }
    
    /**
     * Permite obtener todos los registros de plan_ides o aplicar filtros segun los parametros enviados.
     * @param array $args
     * @return mixed
     */
    static function getAll($args = []) {
        global $wpdb;
        $defaults = [
            'number' => 20,
            'offset' => 0,
            'orderby' => 'id',
            'order' => 'ASC',
        ];
        $args = wp_parse_args($args, $defaults);
        $cache_key = 'addresses-all';
        $items = wp_cache_get($cache_key, 'triidy_automation');
        if (false === $items) {
            $WHERE = "";
            if (isset($args['search'])) {
                $WHERE .= "WHERE LOWER(A.nombre) LIKE '%" . $args['search'] . "%' OR
                        LOWER(A.precio) LIKE '%" . $args['search'] . "%' OR
                        LOWER(C.idTriidy) LIKE '%" . $args['search'] . "%'";
            }
            
             $SQL = "SELECT * FROM
                    " . TRIIDY_AUTOMATION_TABLE_PRODUCTOS . " as A
                    ORDER BY " . $args['orderby'] . " " . $args['order'] . "
                    LIMIT " . $args['offset'] . ", " . $args['number'];
            $items = $wpdb->get_results($SQL);
            wp_cache_set($cache_key, $items, 'triidy_automation');
        }
        return $items;
    }
    
    /**
     * Retorna el valor total de registros en la tabla de plan_ides.
     * @return int
     */
    static function count() {
        global $wpdb;
        return (int)$wpdb->get_var('SELECT COUNT(*) FROM ' . TRIIDY_AUTOMATION_TABLE_PRODUCTOS);
    }
    
    /**
     * este handler detecta las acciones enviadas por el formulario de creación o actualización y procesa la data.
     */
    static function handlerRequest() {
        if (!isset($_POST['submit_triidy_automation_addresses'])) {
            return;
        }
        
        if (!wp_verify_nonce($_POST['_wpnonce'], 'triidy_automation-create')) {
            die(__('¿Estás haciendo trampa?', 'triidy_automation'));
        }
        
        if (!current_user_can('read')) {
            wp_die(__('¡Permiso denegado!', 'triidy_automation'));
        }
        
        $errors = [];
        $page_create = admin_url('admin.php?page=' . TRIIDY_AUTOMATION_SLUG . '-addresses&action=create');
        $page_list = admin_url('admin.php?page=' . TRIIDY_AUTOMATION_SLUG . '-addresses');
        $field_id = isset($_POST['field_id']) ? intval($_POST['field_id']) : 0;
        
        $nombre = isset($_POST['nombre']) ? sanitize_text_field($_POST['nombre']) : '';
        $descripcion = isset($_POST['descripcion']) ? sanitize_text_field($_POST['descripcion']) : '';
        $plan_id = isset($_POST['plan_id']) ? sanitize_text_field($_POST['plan_id']) : '';
        
        if (!$nombre) {
            $errors[] = __('Error: Se requiere un nombre', TRIIDY_AUTOMATION_SLUG);
        }
        if (!$descripcion) {
            $errors[] = __('Error: Se requiere un descripcion', TRIIDY_AUTOMATION_SLUG);
        }
        if (!$plan_id) {
            $errors[] = __('Error: Se requiere una dirección', TRIIDY_AUTOMATION_SLUG);
        }
        
        if ($errors) {
            $first_error = reset($errors);
            $redirect_to = $page_create . '&' . http_build_query(['message' => 'error|' . $first_error, 'type' => 'error']);
            wp_safe_redirect($redirect_to);
            exit;
        }
        
        $fields = [
            'nombre' => $nombre,
            'descripcion' => $descripcion,
            'plan_id' => $plan_id
        ];
        
        if (!$field_id) {
            $insert_id = self::insert($fields);
        } else {
            $fields['id'] = $field_id;
            $insert_id = self::insert($fields);
        }
        
        if (is_wp_error($insert_id)) {
            $redirect_to = $page_list . '&' . http_build_query(['message' => __('error|Error: No se lograron guardar los datos de la dirección', TRIIDY_AUTOMATION_SLUG)]);
        } else {
            $redirect_to = $page_list . '&' . http_build_query(['message' => __('success|Los datos de la dirección han sido guardados con éxito!', TRIIDY_AUTOMATION_SLUG)]);
        }
        
        wp_safe_redirect($redirect_to);
        exit;
    }
    
    /**
     * Realiza la insersion o actualización de los datos de plan_ides en la base de datos
     * @param array $args
     * @return bool|int
     */
    static function insert($args = []) {
        global $wpdb;
        $defaults = [
            'nombre' => '',
            'precio' => '',
            'triidyId' => '',
        ];
        $args = wp_parse_args($args, $defaults);
        $row_id = (int)$args['id'];
        unset($args['id']);
        if (!$row_id) {
            if ($wpdb->insert(TRIIDY_AUTOMATION_TABLE_PRODUCTOS, $args)) {
                return $wpdb->insert_id;
            }
        } else {
            if ($wpdb->update(TRIIDY_AUTOMATION_TABLE_PRODUCTOS, $args, ['id' => $row_id])) {
                return $row_id;
            }
        }
        echo 'hola! por acá llego';
        exit;
        return false;
    }
    
    /**
     * Permite eliminar un registro de dirección dado su ID de identificación.
     * @param $id
     */
    static function delete($id) {
        $page_list = 'admin.php?page=' . TRIIDY_AUTOMATION_SLUG . '-addresses';
        
        if ($id) {
            global $wpdb;
            $response = $wpdb->delete(TRIIDY_AUTOMATION_TABLE_PRODUCTOS, ['id' => $id]);
        } else
            $response = false;
        
        if ($response) {
            $redirect_to = $page_list . '&' . http_build_query(['message' => __('success|La dirección ha sido eliminada correctamente.', TRIIDY_AUTOMATION_SLUG)]);
        } else {
            $redirect_to = $page_list . '&' . http_build_query(['message' => __('error|Error: No se logro eliminar la dirección seleccionada.', TRIIDY_AUTOMATION_SLUG)]);
        }
        
        wp_safe_redirect($redirect_to);
    }
}