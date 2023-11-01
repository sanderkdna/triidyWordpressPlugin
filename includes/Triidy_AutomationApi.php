<?php


namespace Triidy_Automation;


class Triidy_AutomationApi {
    
    /**
     * define los procesos de ejecución de la API para actualización de los datos.
     * @return array
     */
    public static function loadApi() {
        $client_id = get_option(TRIIDY_AUTOMATION_SETTING_CLIENT_ID);
        $id = isset($_GET['id']) ? (int)$_GET['id'] : false;
        $key = isset($_GET['key']) ? addslashes($_GET['key']) : false;
        $notification_subject = isset($_POST['notification_subject']) ? $_POST['notification_subject'] : false;
        $notification_body = isset($_POST['notification_body']) ? $_POST['notification_body'] : false;
        
        unset($_POST['notification_body']);
        unset($_POST['notification_subject']);
        
        if ($id === false || $key === false) {
            return [
                'error' => true,
                'body' => 'Error de solicitud, no se enviaron los parámetros mínimos requeridos.'
            ];
        }
        
        if ($key !== $client_id) {
            return [
                'error' => true,
                'body' => 'La clave de acceso es incorrecta, el proceso no fue completado.'
            ];
        }
        
        global $wpdb;
        $_SQL = "SELECT * FROM `" . SMMC_TABLE_PRESTAMOS . "` WHERE `id` = '" . $id . "' LIMIT 1";
        $old_data = (array)$wpdb->get_row($_SQL);

        if ($old_data) {
            $new_data = array_replace($old_data, $_REQUEST);

            // eliminamos los valores que no puede ser remplazados...
            unset($new_data['id']);
            unset($new_data['order_id']);
            unset($new_data['cliente']);
            unset($new_data['forma_pago']);
            unset($new_data['key']);            
            unset($new_data['destinatario']);
            unset($new_data['direccion_destinatario']);
            unset($new_data['ciudad_destinatario']);
            unset($new_data['telefono_destinatario']);
            unset($new_data['email_destinatario']);
            
            $rows = $wpdb->update(SMMC_TABLE_PRESTAMOS, $new_data, ['id' => $id]);
            if ($rows) {
                
                if ($notification_body) {
                    $to = $new_data['email_destinatario'];
                    $subject = !empty($notification_subject) ? $notification_subject : 'Notificación de actualización de datos, Triidy_Automation Envío.';
                    $body = $notification_body;
                    $headers = ['Content-Type: text/html; charset=UTF-8'];
                    wp_mail($to, $subject, $body, $headers);
                }
                
                return [
                    'error' => false,
                    'body' => 'Información de envío actualizada con éxito.'
                ];
            } else {
                return [
                    'error' => true,
                    'body' => 'Error al intentar guardar los datos, por favor contactar con su administrador de aplicación2.'
                ];
            }
        } else {
            return [
                'error' => true,
                'body' => 'No se logro encontrar un registro con el ID solicitado.'
            ];
        }
        
    }
    
}