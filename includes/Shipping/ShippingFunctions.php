<?php


namespace Triidy_Automation\Shipping;


use HttpRequest;
use Triidy_Automation\Shipping\Addresses\AddressesFunctions;
use WC_Order;
use WP_Query;

class ShippingFunctions
{

    /**
     * Permite obtener los datos de un registro del envio, dado su ID unico.
     * @param $id
     * @return bool
     */
    static function getById($id)
    {
        if (!$id)
            return false;
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare('SELECT * FROM ' . TRIIDY_AUTOMATION_TABLE_ORDENES . ' WHERE id = %d', $id));
    }

    /**
     * Permite obtener todos los registros de direcciones o aplicar filtros segun los parametros enviados.
     * @param array $args
     * @return mixed
     */
    static function getAll($args = [])
    {
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
                $WHERE .= "WHERE LOWER(S.destinatario) LIKE '%" . $args['search'] . "%' OR
                        LOWER(S.telefono_destinatario) LIKE '%" . $args['search'] . "%' OR
                        LOWER(S.email_destinatario) LIKE '%" . $args['search'] . "%' OR
                        LOWER(S.ciudad_destinatario) LIKE '%" . $args['search'] . "%'";
            }

            $SQL = "SELECT
                    DISTINCT S.id,
                    S.order_id,
                    S.destinatario,
                    S.telefono_destinatario,
                    S.email_destinatario,
                    S.ciudad_destinatario,
                    S.estado_prestamo,
                    S.envio_guia,
                    S.valor_servicio,
                    S.response
                FROM
                    " . TRIIDY_AUTOMATION_TABLE_ORDENES . " S
                    " . $WHERE . "
                    ORDER BY " . $args['orderby'] . " " . $args['order'] . "
                    LIMIT " . $args['offset'] . ", " . $args['number'];

            $items = $wpdb->get_results($SQL);
            wp_cache_set($cache_key, $items, 'triidy_automation');
        }
        return $items;
    }

    /**
     * Retorna el valor total de registros en la tabla de direcciones.
     * @return int
     */
    static function count()
    {
        global $wpdb;
        return (int)$wpdb->get_var('SELECT COUNT(*) FROM ' . TRIIDY_AUTOMATION_TABLE_ORDENES);
    }


    /**
     * Permite obtener la lista de ordenes para ser agregadas al formato ajax del formaro de edicion y creación, esta linea fue escrita a las 3:18 am si no la entiendes, yo tampoco..
     * @param $id
     * @return array
     */
    static function getOrders($id)
    {
        $orders = [];
        $args = [
            'post_type' => 'shop_order',
            'posts_per_page' => -1,
            'post_status' => 'any'
        ];
        if ($id) {
            $args['post__in'] = [(int)$id];
        }
        $order_query = new WP_Query($args);
        if($order_query->have_posts()){
            $order_query->the_post();
            //$id = $order_query->post->ID;
            // echo $id;
            $order = new WC_Order($id);
            $observaciones = [];
            $order_detail = [];

            if ($order) {
                foreach ($order->get_items() as $item) {
                    $observaciones[] = "{$item->get_name()} x{$item->get_quantity()}";
                    $order_detail[] = "{$item->get_name()}||{$item->get_quantity()}||{$item->get_product_id()}||{$item->get_total()}";
                }
            }

            $text = "#" . $id . " : " . html_entity_decode($order_query->post->post_title);
	        $data = $order->get_data();
            $impOB = implode(', ', $observaciones);
            $impDT = implode('@@@', $order_detail);
            $orders[] = ['id' => $id, 'text' => $text, 'observaciones' => $impOB, 'order_detail' => $impDT, 'billing'=> $data['billing'], 'shipping'=>$data['shipping'], 'nombre_usuario'=>$data['customer_id']];
        }

        return $orders;
    }


    /**
     * este handler detecta las acciones enviadas por el formulario de creación o actualización y procesa la data.
     */
    static function handlerRequest()
    {
        if (!isset($_POST['submit_triidy_automation_envios'])) {
            return;
        }

        if (!wp_verify_nonce($_POST['_wpnonce'], 'triidy_automation-create')) {
            die(__('Are you cheating?', 'triidy_automation'));
        }

        if (!current_user_can('read')) {
            wp_die(__('Permission Denied!', 'triidy_automation'));
        }

        $errors = [];
        $page_create = admin_url('admin.php?page=triidy_automation-shipping&action=create');
        $page_list = admin_url('admin.php?page=triidy_automation-shipping');
        $field_id = isset($_POST['field_id']) ? intval($_POST['field_id']) : 0;

        $fields['order_id'] = isset($_POST['order_id']) ? sanitize_text_field($_POST['order_id']) : '';
        $fields['destinatario'] = isset($_POST['destinatario']) ? sanitize_text_field($_POST['destinatario']) : '';
        $fields['direccion_destinatario'] = isset($_POST['direccion_destinatario']) ? sanitize_text_field($_POST['direccion_destinatario']) : '';
        $fields['ciudad_destinatario'] = isset($_POST['ciudad_destinatario']) ? sanitize_text_field($_POST['ciudad_destinatario']) : '';
        $fields['telefono_destinatario'] = isset($_POST['telefono_destinatario']) ? sanitize_text_field($_POST['telefono_destinatario']) : '';
        $fields['email_destinatario'] = isset($_POST['email_destinatario']) ? sanitize_text_field($_POST['email_destinatario']) : '';

        $current_user = wp_get_current_user();
        $fields['nombre_usuario'] = $current_user->display_name;
        
        $fields['observaciones'] = isset($_POST['observaciones'])?sanitize_text_field($_POST['observaciones']):'';
        $fields['order_detail'] = isset($_POST['order_detail'])?sanitize_text_field($_POST['order_detail']):'';
        $fields['nombre_usuario'] = isset($_POST['nombre_usuario'])?sanitize_text_field($_POST['nombre_usuario']):'';
        $fields['envio_guia'] = "0";

        $order = new WC_Order($fields['order_id']);
        $ordernArray = json_decode($order, true);

        $fields['valor_servicio'] = $order->get_data()['total'];

            
                if ($order) {
                    $productDetail = array();
                    $i = 0;
                    $productosTriidy = array();
                    #echo 'entro a if';
                    #print_r($order->get_items());
                    foreach ($order->get_items() as $item) {
                        
                        $product = wc_get_product($item->get_product_id());
    
                        $productDetail['name'] = "".$product->get_name();
                        $productDetail['cost'] = $product->get_price();
                        $productDetail['sale_price'] = ($product->get_price() > 0)?$product->get_price():$product->get_price();
                        $productDetail['product_type'] = "1";
                        $productDetail['height'] = '1';
                        $productDetail['width'] =  '1';
                        $productDetail['depth'] =  '1';
                        $productDetail['volume'] = '1';
                        $productDetail['color'] =  '1';
                        $productDetail['packaging'] = 'N/A';
                        $productDetail['content'] = "".$product->get_description();
                        $productDetail['invima'] = "".$product->get_id();
                        $productDetail['net_weight'] = '1';
                        $productDetail['country_phone_code'] = '57';
                        $productDetail['shopify_product_id'] = '0';
                        $productDetail['woocommerce_product_id'] = "".$product->get_id();
                        $productDetail['description'] = "".$product->get_description();
                        $productDetail['inventory'] = "".($product->get_stock_quantity() > 0)?"".$product->get_stock_quantity():'1';
                        $productDetail['platform'] = 'WOOCOMERCE';
                        echo 'voy a buscar producto';
                       // echo "{$item->get_name()} x{$item->get_quantity()}";

                        $x = self::sentProduct($productDetail);


                        $response = json_decode($x, true);

                        echo '<h4>Respuesta de la API:</h4>';

                        if ($response['errors'] != '') {
                            echo '<ul>';
                            foreach ($response['errors'] as $key => $value) {
                                if ($value[0] == "El campo: 'inventory' es requerido") {
                                    echo '<li>'.'Error: Debe tener productos en el inventario para poder sincronizar con Triidy'.'</li>';
                                    // code...
                                }else{
                                    echo '<li>'.$value[0].'</li>';
                                }
                                // code...
                            }
                            echo '</ul>';
                        }else{
                            echo 'El producto se envió correctamente a triidy. triidyId: '.$response['result']['product_id'];
                            $productosTriidy[$i]['triidyId'] = "".$response['result']['product_id'];
                            $productosTriidy[$i]['cantidad'] = "".$item->get_quantity();
                            $productosTriidy[$i]['nombre'] = $product->get_name();
                        }
                        $i++;
                    }
                }
                
/*
                */
        /* Validación de campos */
        // if (!$fields['observaciones']) {
        //     $errors[] = __('Error: Se requiere una observacion.', 'triidy_automation');
        // }

        // if ($errors) {
        //     $first_error = reset($errors);
        //     $redirect_to = add_query_arg(['error' => $first_error], $page_create);
        //     wp_safe_redirect($redirect_to);
        //     exit;
        // }

        if (!$field_id) {
            $insert_id = self::insert($fields);
            $fields['departamento_destinatario'] =   $ordernArray['billing']['state'];
            $fields['payment_method'] =   $ordernArray['payment_method'];


            if($insert_id) {
                $data = $fields;
                $data['cliente'] = get_option(TRIIDY_AUTOMATION_SETTING_CLIENT_ID);
                self::apiTriidy_AutomationInsert($insert_id, $data, $page_create, $productosTriidy);
                $order->add_order_note("Se crea un nuevo registro para envio de productos por medio de Triidy_Automation.");
            }
        } else {
            $fields['id'] = $field_id;
            $insert_id = self::insert($fields);
        }

        // echo 'hola!';

        if (is_wp_error($insert_id)) {
            $redirect_to = $page_list . '&' . http_build_query(['message' => __('error|Error: No se lograron guardar los datos del envio.', TRIIDY_AUTOMATION_SLUG)]);
        } else {
            $redirect_to = $page_list . '&' . http_build_query(['message' => __('success|Los datos del envio han sido guardados con éxito!', TRIIDY_AUTOMATION_SLUG)]);
        }

        wp_safe_redirect($redirect_to);
        exit;
    }

    static function sentProduct($productObject){

        global $wpdb;
        // $product = $wpdb->get_row($wpdb->prepare('select * from '.TRIIDY_AUTOMATION_TABLE_PRODUCTOS.' where product_id = %s ', $productObject['woocommerce_product_id']));

            echo 'Enviando producto a triidy desde funcion SentProduct';
            print_r($productObject);

            $enviarAutomaticamente = get_option(TRIIDY_AUTOMATION_SETTING_PRODUCTOS_AUTOMATICOS);
            $height = ($id->height >= 1)?$id->height:0;
            $width  = ($id->width >= 1)?$id->width:0;
            $length = ($id->length >= 1)?$id->length:0;
            $weight = ($id->weight >= 1)?$id->weight:0;

           // echo $id;

            $invima = ($id->sku == '')?$id->id:$id->sku;
            $params_array = $productObject;
                
            $params_array['user'] =  get_option(TRIIDY_AUTOMATION_SETTING_USERNAME);
            $params_array['password'] =  get_option(TRIIDY_AUTOMATION_SETTING_CLAVE);
            
            $params = json_encode($params_array);            
            $curl = curl_init();
            curl_setopt_array($curl, array(
              CURLOPT_URL => 'https://devopstriidy.com/Automations/api/Triidy/product',
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => '',
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 0,
              CURLOPT_FOLLOWLOCATION => true,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => 'POST',
              CURLOPT_POSTFIELDS => $params,
              CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
              ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);

            return $response;
        // }
    }

    /**
     * Realiza la insersion o actualización de los datos de direcciones en la base de datos
     * @param array $args
     * @return bool|int
     */
    static function insert($args = [])
    {
        global $wpdb;
        $defaults = [
            'id' => null,
            'cliente' => get_option(TRIIDY_AUTOMATION_SETTING_CLIENT_ID)
        ];

        $args = wp_parse_args($args, $defaults);
        $row_id = (int)$args['id'];

        $wpdb->show_errors();
        unset($args['id']);
        if (!$row_id) {

            $metakey   = 'Funny Phrases';
            $metavalue = "WordPress' database interface is like Sunday Morning: Easy.";
 
            $wpdb->query( $wpdb->prepare(" INSERT INTO ".TRIIDY_AUTOMATION_TABLE_ORDENES." ( cliente,order_id,destinatario,direccion_destinatario,ciudad_destinatario,telefono_destinatario,email_destinatario,nombre_usuario,observaciones,envio_guia,valor_servicio,forma_pago,estado_prestamo ) 
                                                                            VALUES ( %s, %d, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s ) ", $args['cliente'], $args['order_id'], $args['destinatario'], $args['direccion_destinatario'], $args['ciudad_destinatario'], $args['telefono_destinatario'], $args['email_destinatario'], $args['nombre_usuario'], $args['observaciones'], $args['envio_guia'], $args['valor_servicio'], $args['forma_pago'], $args['estado_prestamo'] ) );
   
            return $wpdb->insert_id;
            
        } else {
            if ($wpdb->update(TRIIDY_AUTOMATION_TABLE_ORDENES, $args, ['id' => $row_id])) {
                self::apiTriidy_AutomationUpdate($row_id, $args);
                return $row_id;
            } else {
                $wpdb->print_error();
                exit();
            }
        }
        return false;
    }

    /**
     * Permite eliminar un registro de dirección dado su ID de identificación.
     * @param $id
     */
    static function delete($id)
    {
        $page_list = 'admin.php?page=' . TRIIDY_AUTOMATION_SLUG . '-shipping';

        if ($id) {
            global $wpdb;
            $old_data = $wpdb->get_row($wpdb->prepare('SELECT * FROM ' . TRIIDY_AUTOMATION_TABLE_ORDENES . ' WHERE id = %d', $id));
            $response = $wpdb->delete(TRIIDY_AUTOMATION_TABLE_ORDENES, ['id' => $id]);
            if($response) {
                self::apiTriidy_AutomationDelete($old_data->envio_guia);
            }
            
        } else
            $response = false;
        if ($response) {
            $redirect_to = $page_list . '&' . http_build_query(['message' => __('success|El registro de envio ha sido eliminado correctamente.', TRIIDY_AUTOMATION_SLUG)]);
        } else {
            $redirect_to = $page_list . '&' . http_build_query(['message' => __('error|Error: No se logro eliminar el envio seleccionado.', TRIIDY_AUTOMATION_SLUG)]);
        }

        wp_safe_redirect($redirect_to);
    }

    /**
     * Realiza el envio de los datos regisrados a la api de AM Mensajes
     * @param $data
     */
    static function apiTriidy_AutomationInsert($insert_id, $data, $page_create,  $productList)
    {

       $params_array = array(
            'fn' => 'insert',
            'cliente' => $data['cliente'],
            'order_id' => $data['order_id'],
            'envio_id' => $insert_id,
            'destinatario' => $data['destinatario'],
            'direccion_destinatario' => $data['direccion_destinatario'],
            'ciudad_destinatario' => $data['ciudad_destinatario'],
            'telefono_destinatario' => $data['telefono_destinatario'],
            'email_destinatario' => $data['email_destinatario'],
            'nombre_usuario' => $data['nombre_usuario'],
            'observaciones' =>  $data['observaciones'],
            'order_detail' =>  $data['order_detail'],
            'valor_servicio' => $data['valor_servicio'],
            'forma_pago' => $data['forma_pago']
        );

        $params_array = array();

        $paymentMethod = ($data['payment_method'] == 'cod')?'Contraentrega':'Pago Anticipado';
        $isAgainstDelivery = ($data['payment_method'] == 'cod')?true:false;

        $params_array['user'] =     get_option(TRIIDY_AUTOMATION_SETTING_USERNAME);
        $params_array['password'] =  get_option(TRIIDY_AUTOMATION_SETTING_CLAVE);
        $params_array['customer']['country_phone_code'] = "57";
        $params_array['customer']['neighborhood'] = "Barrio";
        $params_array['customer']['document'] = "0000";
        $params_array['customer']['email'] = $data['email_destinatario'];
        $params_array['customer']['gender'] = "M";
        $params_array['customer']['name'] = $data['destinatario'];
        $params_array['customer']['phone'] = $data['telefono_destinatario'];
        $params_array['customer']['address'] = $data['direccion_destinatario'];
        $params_array['sale']['height'] = "1";
        $params_array['sale']['width'] = "1";
        $params_array['sale']['length'] = "1";
        $params_array['sale']['payment_method'] = $paymentMethod;
        $params_array['sale']['phone'] = $data['telefono_destinatario'];
        $params_array['sale']['declared_value'] = $data['valor_servicio'];
        // $params_array['sale']['declared_value'] = 400000;
        $params_array['sale']['is_against_delivery'] = $isAgainstDelivery;
        $params_array['sale']['collected_value'] = "".$data['valor_servicio'];
        // $params_array['sale']['collected_value'] = "500000";
        $params_array['sale']['sale_id'] = "".$data['order_id'];
        $params_array['sale']['platform'] = 'WOOCOMERCE';


        $curl = curl_init();
        $arrayCity = array();
        $arrayCity['user']      =   get_option(TRIIDY_AUTOMATION_SETTING_USERNAME);;
        $arrayCity['password']  =   get_option(TRIIDY_AUTOMATION_SETTING_CLAVE);;
        $arrayCity['city']      =   $data['ciudad_destinatario'];
        $arrayCity['region']    =   $data['departamento_destinatario'];

        $paramsCity = json_encode($arrayCity);

        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://devopstriidy.com/Automations/api/Triidy/check-product-existence-by-city-and-region',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS => $paramsCity,
          CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json'
          ),
        ));
        
        $responseCity = curl_exec($curl);

        curl_close($curl);

        $responseText = $responseCity;
        echo 'location exit: '.$responseText;
        
        $responseCityVector = json_decode($responseCity, true);

    
        $params_array['customer']['location_id'] = $responseCityVector['result'];
    

        $listaProductos = '';
        for ($i=0; $i < count($productList) ; $i++) { 
            $listaProductos .= $productList[$i]['nombre'].' X'.$productList[$i]['cantidad'];
            $params_array['sale']['details'][$i]['product_id'] = "".$productList[$i]['triidyId'];
            $params_array['sale']['details'][$i]['quantity'] =   "".$productList[$i]['cantidad'];
        }
        
        //echo $params;
        $curl = curl_init();
        $params = json_encode($params_array);

        echo $params;   
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => TRIIDY_AUTOMATION_API_URL."Triidy/sale",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS => $params,
          CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json'
          ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);
        


        if ($err) {
            print_r('<pre>');
            print_r( "cURL Error #:" . $err);
            exit();
        } else {

            $responseText = $response;
            $response = json_decode($response, true);

                
            echo '<h4>Respuesta del servidor</h4>';
            echo print_r($response);
            $respuesta = trim(str_replace('Venta realizada con exito:', '', $response['result']['respuesta'])) ;
            $respuesta = trim(str_replace('Venta ya existente:', '', $respuesta)) ;
            $guiaid = $respuesta;
            if($response['is_success']) {
                global $wpdb;
                $wpdb->query($wpdb->prepare('UPDATE ' . TRIIDY_AUTOMATION_TABLE_ORDENES . ' SET envio_guia=%s, estado_prestamo=%s, response=%s WHERE id = %d', $respuesta, $response['message'], $responseText, $insert_id));


                $postfields = array('triidyPass' => get_option(TRIIDY_AUTOMATION_SETTING_CLAVE),
                                    'triidyUser' => get_option(TRIIDY_AUTOMATION_SETTING_USERNAME),
                                    'currentStatus' => '0',
                                    'nombreProducto' => $listaProductos,
                                    'precioProducto' => $data['valor_servicio'],
                                    'nombreComprador' => $data['destinatario'],
                                    'departamentoComprador' => $data['departamento_destinatario'],
                                    'municipioComprador' => $data['ciudad_destinatario'],
                                    'direccionComprador' => $data['direccion_destinatario'],
                                    'telefonoComprador' => $data['telefono_destinatario'],
                                    'triidyOrdenId' => $respuesta,
                                );
                echo '<hr>';
                echo '<h5>Enviando datos hacia Automation / whatsapp: '.$guiaid.'</h5>';
                print_r($postfields);
                $curlx = curl_init();

                curl_setopt_array($curlx, array(
                  CURLOPT_URL => 'https://triidy.admhost.site/api/v1/userconnect',
                  CURLOPT_RETURNTRANSFER => true,
                  CURLOPT_ENCODING => '',
                  CURLOPT_MAXREDIRS => 10,
                  CURLOPT_TIMEOUT => 0,
                  CURLOPT_FOLLOWLOCATION => true,
                  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                  CURLOPT_CUSTOMREQUEST => 'POST',
                  CURLOPT_POSTFIELDS => $postfields,
                ));

                
                $response = curl_exec($curlx);
                $err = curl_error($curlx);
                
                curl_close($curlx);
                if ($err) {
                    print_r('<pre>');
                    print_r( "cURL Error #:" . $err);
                    exit();
                } else {
                    $response = json_decode($response, true);
                    echo '<h4>Respuesta del servidor Automations</h4>';
                    echo $response;
                    echo 'hola enviada la notificacion';
                }
        /*
*/
            }else{
                global $wpdb;
                $wpdb->query($wpdb->prepare('UPDATE ' . TRIIDY_AUTOMATION_TABLE_ORDENES . ' SET envio_guia=%s, estado_prestamo=%s, response=%s WHERE id = %d', $respuesta, $response['message'], $responseText, $insert_id));

            }
        }


                #echo 'UPDATE ' . TRIIDY_AUTOMATION_TABLE_ORDENES . ' SET envio_guia=%s, estado_prestamo=%s WHERE id = %d';
                #echo $response['cupon_code']."----".$response['estado']."----".$response['valor'], $insert_id
                #echo 'debo ejecutar la consulta de actualizacion';
                #exit;

    }


    static function apiTriidy_AutomationDelete($envio_guia) {
        if(!empty($envio_guia)) {
            $curl = curl_init();
            $params = http_build_query(array(
                'fn' => 'delete',
                'cliente' => get_option(TRIIDY_AUTOMATION_SETTING_CLIENT_ID),
                'guia' => $envio_guia
            ));
            curl_setopt_array($curl, array(
                CURLOPT_URL => TRIIDY_AUTOMATION_API_URL."api.php?".$params,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET"
            ));
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
        }
    }
    
    /**
     * Realiza la actualización de los datos regisrados a la api de AM Mensajes
     * @param $id
     * @param $data
     */
    static function apiTriidy_AutomationUpdate($id, $data)
    {
        //print_r('<pre>');
        //print_r($id);
        //print_r('<br>');
        //print_r($data);
        //exit();
    }


}
