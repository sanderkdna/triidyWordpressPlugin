<?php
namespace Triidy_Automation\Shipping;
use Triidy_Automation\Shipping\Addresses\Addresses;
use Triidy_Automation\Shipping\Addresses\AddressesFunctions;


use HttpRequest;
use WC_Order;
use WC_Product;
use WP_Query;


class Shipping
{

    /**
     * Esta función permite cargar todos los recursos incluidos en este módulo.
     */
    public function load()
    {
        add_action('admin_menu', [$this, 'add_menu']);
        add_action('admin_enqueue_scripts', [$this, 'add_styles_files']);
        add_action('wp_ajax_triidy_automation_get_order_data', [$this, 'ajax_callback_orders']); // wp_ajax_{action}

        // Agregams el boton a la lista de ordenes
        add_filter('woocommerce_admin_order_actions', [$this, 'add_custom_order_actions_button'], 100, 2);
        add_action('admin_head', [$this, 'add_custom_order_status_actions_button_css']);

        // Incializamos el api para actualización de los datos.
        add_action('rest_api_init', [$this, 'add_endpoint_rest_api_init']);

        //DATOS DE NUEVO CAMPO DE KREDITO! => woocommerce_after_order_notes
        //add_action( 'woocommerce_after_order_notes', [$this, 'kreditmethod'] );
        add_action( 'woocommerce_before_single_product', [$this, 'EnviarProductoAPI'], 10 );
        
        add_action( 'woocommerce_checkout_update_order_meta', [$this, 'actualizar_info_kredito'] );
        add_action( 'woocommerce_admin_order_data_after_billing_address', [$this, 'mostrar_campo_kredito'], 10, 1 );

        add_filter('manage_edit-shop_order_columns', [$this, 'esl_change_product_columns']);
        add_action('manage_posts_custom_column', [$this, 'esl_product_columns_content']);

        //add_filter ( 'woocommerce_account_menu_items', [$this, 'CreateElementDesktopUser']);

        add_filter ( 'woocommerce_account_menu_items', [$this, 'misha_log_history_link'], 40 );
        add_action( 'init', [$this, 'misha_add_endpoint'] );
        add_action( 'woocommerce_account_log-history_endpoint', [$this, 'misha_my_account_endpoint_content'] );

        add_action('woocommerce_thankyou', [$this, 'INCon_event_saved_order'], 10, 1);
        add_action( 'send_headers', [$this, 'add_header_seguridad'], 40 );

        // add_action('woocommerce_saved_order_items', 'INCon_event_saved_order', 10, 3);
        // add_action('woocommerce_order_status_processing', 'INCon_event_saved_order', 10, 3);
        add_action( 'woocommerce_after_product_object_save', [$this, 'wp_kama_woocommerce_new_product_action'], 10, 2 );
        // add_action( 'woocommerce_update_product', [$this, 'wp_kama_woocommerce_new_product_action'], 10, 2 );

        add_filter( 'woocommerce_product_options_general_product_data', [$this, 'woo_new_product_tab'] );



    }

    /**
     * Agregamos el item de menú para administración del módulo.
     */
    public function add_menu()
    {

        $page = TRIIDY_AUTOMATION_SLUG . '-shipping';
        $sub_page1 = TRIIDY_AUTOMATION_SLUG . '-products';
        $sub_page2 = TRIIDY_AUTOMATION_SLUG . '-settings';

        /**
         * Cargamos el sub-módulo de direcciones.
         */
        $addresses = new Addresses($page);

        /**
         * Agerega el item de menú principal.
         */

        // add_menu_page(__('Ordenes', TRIIDY_AUTOMATION_SLUG), __('Ordenes', TRIIDY_AUTOMATION_SLUG), 'manage_options', $page, [$this, 'shipping_pages'], 'dashicons-groups', 81);
        

        add_menu_page(  __('Triidy Automations', TRIIDY_AUTOMATION_SLUG), 
                        __('Triidy Automations', TRIIDY_AUTOMATION_SLUG), 
                        'manage_options', 
                        $page, 
                        [$this, 'shipping_pages'], 
                        'dashicons-groups', 81);


        add_submenu_page(   $page, 
                            __('Ordenes', TRIIDY_AUTOMATION_SLUG), 
                            __('Ordenes', TRIIDY_AUTOMATION_SLUG), 
                            'manage_options', 
                            $page, 
                            [$this, 'shipping_pages']
                        );

        
        add_submenu_page(  $page,   
                            __('Productos', TRIIDY_AUTOMATION_SLUG), 
                            __('Productos', TRIIDY_AUTOMATION_SLUG), 
                            'manage_options', 
                            $sub_page1, 
                            [$addresses, 'addresses_pages']);

        

    }

    /**
     * Esta función controla la impresión de las vistas de este módulo
     */
    public function shipping_pages()
    {
        $action = isset($_GET['action']) ? $_GET['action'] : 'list';

        switch ($action) {
            case 'create':
            case 'edit':
                ShippingFunctions::handlerRequest();
                break;
            case 'delete':
                $id = isset($_GET['id']) ? $_GET['id'] : false;
                ShippingFunctions::delete($id);
                break;
        }

        $template = dirname(__FILE__) . '/views/' . $action . '.php';
        if (file_exists($template)) {
            include $template;
        }
    }

    /**
     * Agrega la función que permite obtener los datos via ajax de las ordenes
     */
    public function ajax_callback_orders()
    {
        echo json_encode(ShippingFunctions::getOrders((int)$_GET['q']));


        exit;
    }

    /**
     * Esta función permite agregar el icono a la lista de ordenes que permite registrar un nuevo envio.
     * @param $actions
     * @param $order
     * @return mixed
     */
    public function add_custom_order_actions_button($actions, $order)
    {
        if ($order->has_status(['processing'])) {
            $order_id = method_exists($order, 'get_id') ? $order->get_id() : $order->id;
            $actions['parcial'] = [
                'url' => wp_nonce_url(admin_url('admin.php?page=triidy_automation-shipping&action=create&order_id=' . $order_id), 'woocommerce-mark-order-status'),
                'name' => __('Triidy_Automation Envios', TRIIDY_AUTOMATION_SLUG),
                'action' => "view parcial", // keep "view" class for a clean button CSS
            ];
        }
        return $actions;
    }

    /**
     * Esta función define el icono usado para la acción en la lista de ordenes.
     */
    function add_custom_order_status_actions_button_css()
    {
        echo '<style>.view.parcial::after { font-family: WooCommerce !important; content: "\e006" !important; }</style>';
    }

    /**
     * Esta función permite agregar las hojas de estilos css al proyecto.
     */
    public function add_styles_files()
    {
        wp_register_style('triidy_automation_admin_styles', plugin_dir_url(TRIIDY_AUTOMATION_PLUGIN_FILE) . 'assets/css/triidy_automation_shipping.css', false, TRIIDY_AUTOMATION_VERSION, false);
        wp_enqueue_style('triidy_automation_admin_styles');

        wp_enqueue_script('jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js', false, null, true);
        wp_enqueue_script('jquery-ui-datepicker');

        wp_enqueue_style('e2b-admin-ui-css', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.9.0/themes/base/jquery-ui.css', false, '1.9.0', false);
        wp_enqueue_style('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.8/css/select2.min.css');
        wp_enqueue_script('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.8/js/select2.min.js', ['jquery']);

        wp_enqueue_style('jquery_timepicker', 'https://cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.css');
        wp_enqueue_script('jquery_timepicker', 'https://cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js', ['jquery']);
    }

    public function add_endpoint_rest_api_init()
    {
        register_rest_route('triidy_automation/v1', '/a', [
            'methods' => 'GET',
            'callback' => [$this, 'capture_request_callback_api']
        ]);
    }

    public function capture_request_callback_api()
    {
        print_r('<pre>');
        print_r("OK");
        exit();
    }

    function kreditmethod( $checkout ) {
     
        echo '' . __('<hr style="margin-top:20px"/>') . '';

        $plan = new AddressesFunctions();

        $addresses = $plan::getAll();

        $array_opciones = array('PAGO DE CONTADO' => __('Seleccione una Opción (por defecto: Pago de contado)'));

        foreach ($addresses as $addres){
            $array_opciones[$addres->nombre] = $addres->nombre;
        }

        woocommerce_form_field( 'plan_pago', array(
            'type'          => 'select',
            'class'         => array('my-field-class form-row-wide'),
            'label'         => __('Si desea pagar su pedido a cuotas, seleccione el plan de pagos que prefiera y seleccione "contra reembolso" en su metodo de pago'),
            'placeholder'   => __('Ej: 99999999D'),
            'options'       => $array_opciones,
                'default' => 'N/A',
            ), $checkout->get_value( 'plan_pago' ));
    }
 
    function actualizar_info_kredito( $order_id ) {
        if ( ! empty( $_POST['plan_pago'] ) ) {
            update_post_meta( $order_id, 'plan_pago', sanitize_text_field( $_POST['plan_pago'] ) );
        }
    }
 
    function mostrar_campo_kredito($order){
        echo '<p><strong>'.__('FORMA DE PAGO').':</strong> ' . get_post_meta( $order->id, 'plan_pago', true ) . '</p>';
    }

    function esl_change_product_columns( $columns ) {

        // añadimos una nueva columna personalizada
        $columns['plan_pago'] = 'Forma de Pago'; 

        return $columns;

    }
    function esl_product_columns_content($column_name) {
        if ($column_name == 'plan_pago') {
            global $id;
            global $wpdb;
            
            //Buscamos el valor del campo personalizado 'plan_pago' y los mostramos.
            $results = $wpdb->get_results("SELECT meta_value FROM ".$wpdb->postmeta." WHERE post_id = '".$id."' AND meta_key = 'plan_pago'");
            
            $plan = array();
            foreach ($results as $result){
                $plan['plan'] = $result->meta_value;
            }
            echo $plan['plan'];
        }
    }

    function CreateElementDesktopUser($menu_links){
        
    $new = array( 'historialTriidy_Automation' => 'Historial de Prestamos Activos' );

        // Colocamos el nuevo elemento en la posición que nos interese (cambiando el 1 por el orden que queramos). 
    $menu_links = array_slice( $menu_links, 0, 1, true )
    + $new
    + array_slice( $menu_links, 1, NULL, true );

    return $menu_links;

    }


    /*
 * Step 1. Add Link (Tab) to My Account menu
 */
    function misha_log_history_link( $menu_links ){
        
        $menu_links = array_slice( $menu_links, 0, 5, true ) 
        + array( 'log-history' => 'Historial de Abonos' )
        + array_slice( $menu_links, 5, NULL, true );
        
        return $menu_links;

    }
    /*
     * Step 2. Register Permalink Endpoint
     */
    function misha_add_endpoint() {
        // WP_Rewrite is my Achilles' heel, so please do not ask me for detailed explanation
        add_rewrite_endpoint( 'log-history', EP_PAGES );

    }
    /*
     * Step 3. Content for the new page in My Account, woocommerce_account_{ENDPOINT NAME}_endpoint
     */
    function misha_my_account_endpoint_content() {
        // of course you can print dynamic content here, one of the most useful functions here is get_current_user_id()
        include('KreditDetail.php');
    }
    /*
     * Step 4
     */
    // Go to Settings > Permalinks and just push "Save Changes" button.

    function INCon_event_saved_order($order_id){
        // global $wpdb;
        // echo "<script>console.log('Consultado información de la orden...');</script>";
        $automate = get_option(TRIIDY_AUTOMATION_SETTING_ORDENES_AUTOMATICAS);
        //echo '---'.$automate;

        if ($automate == 'SI') {
            // code...
            $orden = new WC_Order($order_id); // Obtener toda la información de la orden

            $datos =  json_encode($orden->meta_data);
            $array = json_decode($datos, true);
            $ordernArray = json_decode($orden, true);


            // print_r($ordernArray);
            $senddata = false;
            $forma_pago = '';
            foreach ($array as $value) {
               $cadena = "Key: '". $value['key'] ."', Value: ". $value['value'] ."},<br>";
                $forma_pago = $value['value'];
                $senddata = true;

            }
            $message = '';
            $break = false;

            if ($senddata) {
                echo '<h4>Consultado información de la orden... '.$order_id.'</h4>';
                 $user_info = get_userdata($orden->get_customer_id()); 
                

              //  $orderdetail = self::getOrders($order_id);

                $fields['order_id'] = $order_id;
                $fields['destinatario'] = $ordernArray['billing']['first_name'].' '.$ordernArray['billing']['last_name'] ;
                $fields['direccion_destinatario'] = $ordernArray['billing']['address_1'];
                $fields['ciudad_destinatario'] =    $ordernArray['billing']['city'];
                $fields['telefono_destinatario'] =  $ordernArray['billing']['phone'];
                $fields['email_destinatario'] =     $ordernArray['billing']['email'];


                $current_user = wp_get_current_user();
                $fields['nombre_usuario'] = $current_user->display_name;
                
                $fields['observaciones'] = '';
                $fields['order_detail'] =  '';
                $fields['nombre_usuario'] = $current_user->display_name;
                $fields['envio_guia'] = "0";

                $order = new WC_Order($fields['order_id']);
                $fields['valor_servicio'] = $order->get_data()['total'];
                #echo 'fuera del if';


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
                        echo "{$item->get_name()} x{$item->get_quantity()}";

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
                                $message .= $value[0].', ';
                                $break = true;
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
                
                echo '<hr>';

                print_r($ordernArray);
                if (!$field_id) {
                    $insert_id = self::insert($fields);
                    $fields['departamento_destinatario'] =   $ordernArray['billing']['state'];
                    $fields['payment_method'] =   $ordernArray['payment_method'];
                    
                    if($insert_id) {
                        $data = $fields;
                        $data['cliente'] = get_option(TRIIDY_AUTOMATION_SETTING_CLIENT_ID);
                        self::apiTriidy_AutomationInsert($insert_id, $data, $page_create, $productosTriidy, $message, $break);
                        $order->add_order_note("Se crea un nuevo registro para envio de productos por medio de Triidy_Automation.");
                    }
                } else {
                    $fields['id'] = $field_id;
                    $insert_id = self::insert($fields);
                }

                if (is_wp_error($insert_id)) {
                    $redirect_to = $page_list . '&' . http_build_query(['message' => __('error|Error: No se lograron guardar los datos del pedido.', TRIIDY_AUTOMATION_SLUG)]);
                } else {
                    $redirect_to = $page_list . '&' . http_build_query(['message' => __('success|Los datos del pedido han sido guardados con éxito!', TRIIDY_AUTOMATION_SLUG)]);
                }
/*
*/
                #wp_safe_redirect($redirect_to);
                // exit;




            }

            echo '<h5>Envio la orden a Triidy</h5>';

        }else{ 

            echo '<h5>No envio la Orden porque el envio automatico esta deshabilitado</h5>';
            
        }
    }

    static function getByPlanByName($name)
    {
        global $wpdb;
        //  echo 'SELECT * FROM ' . TRIIDY_AUTOMATION_TABLE_PLANES . ' WHERE nombre = %s';
        // $query = ;
        // echo "SELECT * FROM ".TRIIDY_AUTOMATION_TABLE_ADDRESSES." WHERE nombre = '$name'";
        $results = $wpdb->get_results( "SELECT * FROM ".TRIIDY_AUTOMATION_TABLE_ADDRESSES." WHERE nombre = '$name'", OBJECT );
        return $results;
    }

    static function getOrders($id){
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
        while ($order_query->have_posts()) :
            $order_query->the_post();
            $id = $order_query->post->ID;
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
            $orders[] = ['id' => $id, 'text' => $text, 'observaciones' => implode($observaciones, ', '), 'order_detail' => implode($order_detail, '@@@'), 'billing'=> $data['billing'], 'shipping'=>$data['shipping'], 'nombre_usuario'=>$data['customer_id']];
        endwhile;
        return $orders;
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
     * Realiza el envio de los datos regisrados a la api de AM Mensajes
     * @param $data
     */
    static function apiTriidy_AutomationInsert($insert_id, $data, $page_create, $productList, $message, $break)
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
        // echo $responseText;
        
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
            
            $responseText = $err;
            $wpdb->query($wpdb->prepare('UPDATE ' . TRIIDY_AUTOMATION_TABLE_ORDENES . ' SET response=%s WHERE id = %d', $responseText, $insert_id));

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


                #echo 'UPDATE ' . TRIIDY_AUTOMATION_TABLE_ORDENES . ' SET envio_guia=%s, estado_prestamo=%s WHERE id = %d';
                #echo $response['cupon_code']."----".$response['estado']."----".$response['valor'], $insert_id
                #echo 'debo ejecutar la consulta de actualizacion';
                #exit;
            } else {

                global $wpdb;
                $wpdb->query($wpdb->prepare('UPDATE ' . TRIIDY_AUTOMATION_TABLE_ORDENES . ' SET envio_guia=%s, estado_prestamo=%s, response=%s WHERE id = %d', $respuesta, $response['message'], $responseText, $insert_id));


                echo 'error al insertar via API';
                $redirect_to = $page_create . '&' . http_build_query(['message' => 'error|'.$response['mensaje']]);
               // wp_safe_redirect($redirect_to);
            }
        }
    }
    
    static function add_header_seguridad() {
     
        header( 'X-Frame-Options: SAMEORIGIN' );
    }

    static function EnviarProductoAPI(){

        

    }


    /**
     * Esta función permite agregar el icono a la lista de ordenes que permite registrar un nuevo envio.
     * @param $actions
     * @param $order
     * @return mixed
     */
    public function add_custom_product_actions_button($actions, $order)
    {
        if ($order->has_status(['processing'])) {
            $order_id = method_exists($order, 'get_id') ? $order->get_id() : $order->id;
            $actions['parcial'] = [
                'url' => wp_nonce_url(admin_url('admin.php?page=triidy_automation-shipping&action=create&order_id=' . $order_id), 'woocommerce-mark-order-status'),
                'name' => __('Triidy_Automation Envios', TRIIDY_AUTOMATION_SLUG),
                'action' => "view parcial", // keep "view" class for a clean button CSS
            ];
        }
        return $actions;
    }

     /**
     * Function for `woocommerce_new_product` action-hook.
     * 
     * @param  $id      
     * @param  $product 
     *
     * @return void
     */

    function sample_admin_notice__success() {
        $class = 'notice notice-success';
        $message = __( 'El producto no fue enviado a Triidy porque no tiene información en el campo inventario, por favor agrega el inventario y envialo nuevamente. <br/> <br/>Producto actualizado correctamente.', 'sample-text-domain' );

        printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
    }


    function personalizar_mensaje_notificacion_actualizar_producto() {
        global $pagenow;

        // Verifica si estamos en la página de edición de productos
        if ($pagenow === 'post.php' && isset($_GET['post']) && get_post_type($_GET['post']) === 'product' && isset($_GET['message'])) {
            $product_id = $_GET['post'];
            $message = $_GET['message'];
            
            // Verifica si el mensaje es de actualización exitosa
            if ($message === 'updated') {
                // Personaliza aquí tu mensaje de notificación
                $custom_message = '¡El producto se ha actualizado correctamente!';
                
                // Muestra el mensaje personalizado en una notificación
                echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($custom_message) . '</p></div>';
                exit;
            }
        }
    }

    function sample_admin_notice__error() {
        $class = 'notice notice-error';
        $message = __( 'Irks! An error has occurred.', 'sample-text-domain' );

        printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
    }


    function wp_kama_woocommerce_new_product_action( $id, $product){

            //$redirect_to = $page_list . '&' . http_build_query(['message' => __('success|Los datos del envio han sido guardados con éxito!', TRIIDY_AUTOMATION_SLUG)]);
        $enviarAutomaticamente = get_option(TRIIDY_AUTOMATION_SETTING_PRODUCTOS_AUTOMATICOS);
        $height = ($id->height >= 1)?$id->height:0;
        $width  = ($id->width >= 1)?$id->width:0;
        $length = ($id->length >= 1)?$id->length:0;
        $weight = ($id->weight >= 1)?$id->weight:0;


       // echo $id;

        $invima = ($id->sku == '')?$id->id:$id->sku;

        $params_array['user'] =  get_option(TRIIDY_AUTOMATION_SETTING_USERNAME);
        $params_array['password'] =  get_option(TRIIDY_AUTOMATION_SETTING_CLAVE);
        
        $params_array['name'] = $id->name;
        $params_array['cost'] = $id->price;
        $params_array['sale_price'] = $id->price;
        $params_array['product_type'] = "1";
        $params_array['height'] = $height;
        $params_array['width'] =  $width;
        $params_array['depth'] =  $length;
        $params_array['volume'] = $weight;
        $params_array['color'] = "N/A";
        $params_array['packaging'] = "N/A";
        $params_array['content'] = $id->description;
        $params_array['invima'] = "$invima";
        $params_array['net_weight'] = "N/A";
        $params_array['country_phone_code'] = "57";
        $params_array['shopify_product_id'] = "0";
        $params_array['woocommerce_product_id'] = "$id->id";
        $params_array['description'] = $id->description;
        $params_array['inventory'] = "".$id->stock_quantity;
        $params_array['platform'] = 'WOOCOMERCE';

        $post_id = $id->id; // Reemplaza 123 con el ID de tu publicación

        if ($enviarAutomaticamente == 'SI') {

            if ($id->stock_quantity <= 0) {

                // add_action( 'admin_notices', self::sample_admin_notice__error() );

                // $page_list = admin_url('edit.php?post_type=product');

                // $redirect_to = $page_list . '&' . http_build_query(['message' => __('error| El producto no se puede actualizar, inventario: 0', TRIIDY_AUTOMATION_SLUG)]);
                // wp_safe_redirect($redirect_to);
                // exit;

                //add_action( 'admin_notices', 'sample_admin_notice__success' );


            }else{

                $params = json_encode($params_array);  
                //echo $params;
                $message .= '<h4>Body del Producto</h4>';
                $message .= $params;
                $message .= 'Antes de enviar, como debo actualizar inventario, entonces actualizo el producto, por tanto entro a esta funcion de enviar producto';
                $message .= '<br/>';


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
                $responseText = $response;


                curl_close($curl);

                $response = json_decode($response, true);

                $message .= '<h4>Respuesta de la API:</h4>';

                if ($response['errors'] != '') {
                    $message .= '<ul>';
                    foreach ($response['errors'] as $key => $value) {
                        if ($value[0] == "El campo: 'inventory' es requerido") {
                            $message .= '<li>'.'Error: Debe tener productos en el inventario para poder sincronizar con Triidy'.'</li>';
                            // code...
                        }else{
                            $message .= '<li>'.$value.'</li>';
                        }
                        // code...
                    }
                    $message .= '</ul>';

                    $url = esc_url_raw(add_query_arg(array(
                            'post_type' => 'product'
                        ), 'edit.php'));

                }else{
                    $message .= 'El producto se envió correctamente a triidy. triidyId: '.$response['result']['product_id'];
                    $date = date("Y-m-d H:i:s");
                    global $wpdb;
                    $str = "INSERT INTO ".TRIIDY_AUTOMATION_TABLE_PRODUCTOS." 
                                                                ( product_id, nombre, precio, idTriidy, created_at, response, source) 
                                                        VALUES  ( %d, %s, %s, %s, %s, %s, %s)";
    
                    $wpdb->query( $wpdb->prepare(   $str, 
                                                    $post_id,
                                                    $id->name,
                                                    $id->price,
                                                    $response['result']['product_id'],
                                                    $date,
                                                    $responseText,
                                                    'WooCommerce'
                                                ) 
                                            );
                    
                    // echo 'debo actualizar iconn value ProductId: '.$id->id.' _sku Nuevo: '.$response['result']['product_id'];
                    update_post_meta($id->id, '_sku', $response['result']['product_id']);
                    update_post_meta($id->id, '_INCon_Codigo_Procut', $response['result']['product_id']);
                    

                    $url = esc_url_raw(add_query_arg(array(
                        'post_type' => 'product'
                    ), 'edit.php'));
                }
                


                if ($_SERVER['REQUEST_URI'] != '/?wc-ajax=checkout') {
                     wp_die($message, "Editar publicación", array(
                        'response' => 200,
                        'link_url' => $url
                    ));
                }
            }
            
        }else{

            if ($_POST['sentToTriidy'] == 'yes') {
                //echo $params;

                if ($id->stock_quantity <= 0) {

                    $message = 'El producto no fue enviado a Triidy porque no tiene información en el campo inventario, por favor agrega el inventario y envialo nuevamente. <br/> <br/>Producto actualizado correctamente';

                    // Obtén el enlace de la página post.php
                    $url = esc_url_raw(add_query_arg(array(
                        'action' => 'edit'
                    ), 'post.php'));

                }else{

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

                    $response = json_decode($response, true);
                    $message .= '<h4>Respuesta de la API:</h4>'.$response;

                    if ($response['errors'] != '') {
                        $message .= '<ul>';
                        foreach ($response['errors'] as $key => $value) {
                            if ($value[0] == "El campo: 'inventory' es requerido") {
                                $message .= '<li>'.'Error: Debe tener productos en el inventario para poder sincronizar con Triidy'.'</li>';
                                // code...
                            }else{
                                $message .= '<li>'.$value.'</li>';
                            }
                            // code...
                        }
                        $message .= '</ul>';

                         // Obtén el enlace de la página post.php
                        $url = esc_url_raw(add_query_arg(array(
                            'post_type' => 'product'
                        ), 'edit.php'));

                    }else{
                        $message .= 'El producto se envió correctamente a triidy. triidyId: '.$response['result']['product_id'];
        
                        $url = esc_url_raw(add_query_arg(array(
                            'post_type' => 'product'
                        ), 'edit.php'));
                    }
                }


                // $message = "¡Redireccionando a la página de edición!";

                if ($_SERVER['REQUEST_URI'] != '/?wc-ajax=checkout') {
                     wp_die($message, "Editar publicación", array(
                        'response' => 200,
                        'link_url' => $url
                    ));
                }

            }else{
                echo 'no envio nada';
                
            }
        }
    }

    function sentProduct($productObject){

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

    function woo_new_product_tab( $tabs ) {
    
        global $post;

            echo '<div class="product_custom_field">';

            // Custom Product Checkbox Field
            woocommerce_wp_checkbox( array(
                'id'        => 'sentToTriidy',
                'name'        => 'sentToTriidy',
                'label'     => __('Enviar Producto a Triidy', 'woocommerce'),
                'description' => __('Enviar este producto a la plataforma de  Triidy manualmente', 'woocommerce'),
                'desc_tip'  => 'true',
            ) );

            echo '</div>';

        }
    function woo_new_product_tab_content() {

            // The new tab content

            echo '<h2>New Product Tab</h2>';
            echo '<p>Here\'s your new product tab.</p>';
            
    }


}