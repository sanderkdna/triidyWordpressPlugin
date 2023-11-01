<?php
include_once dirname(dirname(dirname(dirname(__DIR__)))) . '/wp-config.php';
include_once dirname(dirname(dirname(dirname(__DIR__)))) . '/wp-load.php';
include_once dirname(dirname(dirname(dirname(__DIR__)))) . '/wp-includes/wp-db.php';

/**
 *  @author Sander Cadena 
 */
class syncProduct {

    /**
     * @var wpdb Acceso a la base de datos wordpress      
     */
    private $wpdb;

    function __construct()
    {
        global $wpdb;
        $this->wpdb = $wpdb;
    }

    public function execute($response) {

        $_JSON_DATA = $response;

        $CLIENT_ID = get_option(TRIIDY_AUTOMATION_SETTING_CLIENT_ID);
        $USERNAME = get_option(TRIIDY_AUTOMATION_SETTING_USERNAME);
        $CLAVE = get_option(TRIIDY_AUTOMATION_SETTING_CLAVE);

        $output = array();

        if ($USERNAME == $response['user'] &&  $CLAVE == $response['password']) {
                    
            $producto = $_JSON_DATA;

            $post_id = false;
            $code = trim($producto['invima']);


            $pathpostId = '';

            if (intval($producto['woocommerce_product_id']) > 1 ) {
                $pathpostId = ' and post_id = "'.$producto['woocommerce_product_id'].'"';
            }

            $_SQL = "SELECT * FROM `{$this->wpdb->prefix}postmeta` WHERE `meta_key` = '_INCon_Codigo_Procut' AND `meta_value` = '{$code}' $pathpostId ";
            $results = $this->wpdb->get_row($_SQL);                     // Guardar resultado

            if (empty($results)) {                                      // Si NO hay resultado, entonces...
            
                $str = "SELECT * FROM ".TRIIDY_AUTOMATION_TABLE_PRODUCTOS." 
                                                            WHERE idTriidy = '{$code}' ";
                                                            
                $_SQL = $str;
                $results = $this->wpdb->get_row($_SQL);                     // Guardar resultado
                
                if (empty($results)) {                                      // Si hay resultado, entonces...
                    // $output['Estado'] = 'Producto Nuevo';
                    // $output['Estado2'] = 'Producto Nuevo';
                    $post_id = wc_get_product_id_by_sku($code);             // Obtener el ID del producto por medio del SKU.
                    
                }else{
                    // $output['Estado'] = 'Producto Encontrado';
                    // $output['Estado2'] = 'Producto Encontrado por automations';
                    $post_id = $results->post_id;    
                }
                
                $post_id = wc_get_product_id_by_sku($code);             // Obtener el ID del producto por medio del SKU.
            } else {
                // $output['Estado'] = 'Producto Encontrado por codigoproducto';
                // $output['Estado2'] = 'Producto Encontrado por codigoproducto';
                $post_id = $results->post_id;
            }

            $disponibles = (int)$response['inventory'];
            $bodegas = [];
            $old_key = [];

            // Si existe disponibilidad de productos actualizamos la información.
            if ($post_id) {       

                wp_update_post(array('ID' => $post_id, 'post_title'  => $producto['name']));
                
                
                $my_post = array(
                  'ID'           => $post_id,
                  'post_title'   => $producto['name']
                );
                wp_update_post( $my_post );
                                             
    
                update_post_meta($post_id, '_sale_price', $producto['sale_price']);
                update_post_meta($post_id, '_weight', $producto['volume']);
                update_post_meta($post_id, '_length', $producto['height']);
                update_post_meta($post_id, '_width', $producto['width']);
                update_post_meta($post_id, '_INCon_Codigo_Procut', $code);
                // update_post_meta($post_id, '_sku', $code);
                update_post_meta($post_id, '_regular_price', $producto['sale_price'] );
                update_post_meta($post_id, '_price', $producto['sale_price'] );
                
                // Habilitamos el uso de invetnario 
                update_post_meta($post_id, '_manage_stock', 'yes');
                // Actualizamos el número de unidades disponibles 
                update_post_meta($post_id, '_stock', $disponibles);

      
                $date = date("Y-m-d H:i:s");
                global $wpdb;
                $str = "INSERT INTO ".TRIIDY_AUTOMATION_TABLE_PRODUCTOS." 
                                                            ( product_id, nombre, precio, idTriidy, created_at, source) 
                                                    VALUES  ( %d, %s, %s, %s, %s, %s) ";

                $wpdb->query( $wpdb->prepare(   $str, 
                                                $post_id,
                                                $producto['name'],
                                                $producto['sale_price'],
                                                $code,
                                                $date,
                                                'Triidy'
                                            ));

            } else {

                
                $user = wp_get_current_user();


                $estado_productos = array(true => "publish", false => "draft");

                $post_id = wp_insert_post(array(    'post_author' => 'user', 
                                                    'post_title' => $producto['name'], 
                                                    'post_title' => $producto['name'], 
                                                    'post_content' => $producto['description'], 
                                                    'post_status' => "publish", 
                                                    'post_type' => "product" ));

                wp_set_object_terms($post_id, 'simple', 'product_type');
                update_post_meta($post_id, '_visibility', 'visible');
                update_post_meta($post_id, 'total_sales', '0');
                update_post_meta($post_id, '_downloadable', 'no');
                update_post_meta($post_id, '_virtual', 'no');
                update_post_meta($post_id, '_sale_price', $producto['sale_price']);
                update_post_meta($post_id, '_purchase_note', '');
                update_post_meta($post_id, '_featured', 'no');
                update_post_meta($post_id, '_weight', $producto['volume']);
                update_post_meta($post_id, '_length', $producto['height']);
                update_post_meta($post_id, '_width', $producto['width']);
                update_post_meta($post_id, '_INCon_Codigo_Procut', $code);
                update_post_meta($post_id, '_height', '');
                update_post_meta($post_id, '_sku', $code);
                update_post_meta($post_id, '_product_attributes', array());
                update_post_meta($post_id, '_sale_price_dates_from', '');
                update_post_meta($post_id, '_sale_price_dates_to', '');
                update_post_meta($post_id, '_sold_individually', '');
                update_post_meta($post_id, '_regular_price', $producto['sale_price'] );
                update_post_meta($post_id, '_price', $producto['sale_price'] );
                
                // Habilitamos el uso de invetnario 
                update_post_meta($post_id, '_manage_stock', 'yes');
                // Actualizamos el número de unidades disponibles 
                update_post_meta($post_id, '_stock', $disponibles);
                // hactualizamos el estado de existencias 
                update_post_meta($post_id, '_stock_status', ((int)$disponibles) ? 'instock' : 'outofstock');



                $date = date("Y-m-d H:i:s");
                global $wpdb;
                $str = "INSERT INTO ".TRIIDY_AUTOMATION_TABLE_PRODUCTOS." 
                                                            ( product_id, nombre, precio, idTriidy, created_at, source) 
                                                    VALUES  ( %d, %s, %s, %s, %s, %s) ";

                $wpdb->query( $wpdb->prepare(   $str, 
                                                $post_id,
                                                $producto['name'],
                                                $producto['sale_price'],
                                                $code,
                                                $date,
                                                'Triidy'
                                            ) 
                                        );


                // Si cargaron la imagen temportal relacionamos la imagen al producto creado...
                $_PATH_IMG_TMP = ABSPATH . "wp-content/images_tmp/$code.jpg";
                if (file_exists($_PATH_IMG_TMP)) {
                    $this->Generate_Featured_Image($_PATH_IMG_TMP, $post_id);
                }
            }

            // Asignamos las categorias de los productos. 
            if (!empty($producto['clasificacion1'])) { // Categoria principal
                //$id_category_parent = wp_create_category_taxonomy($producto['linea'], 'product_cat'); // creamos la categoria si no existe.
                $id_category_parent = get_term_by('slug', strtolower($producto['clasificacion1']), 'product_cat');
                if ($id_category_parent)
                    $categories = array($id_category_parent->term_id);
                if (!empty($producto['clasificacion2'])) { // sub-categoria
                    //$id_category_child = wp_create_category_taxonomy($producto['subLinea'], 'product_cat', $id_category_parent); // creamos la sub-categoria si no existe.
                    $id_category_child = get_term_by('slug', strtolower($producto['clasificacion2']), 'product_cat');
                    if ($id_category_child)
                        array_push($categories, $id_category_child->term_id);
                }
                $affected_category = wp_set_post_terms($post_id, $categories, 'product_cat', ((int)get_option(INC_OVERWRITE_CATEGORIES) == 0)); // asignamos las categorias al producto
            }

            // Asociamos la clase de envio por producto 
            $list_class = array_map(function ($item) {
                return $item->slug;
            }, (new WC_Shipping())->get_shipping_classes());

            $type_send = strtolower($producto['grupo']);
            if (in_array($type_send, $list_class)) {
                wp_set_object_terms($post_id, $type_send, 'product_shipping_class');
            }   

            // $output['Estado'] = 'Producto Creado';
            $output['postId'] = $post_id;
            $output['platform'] = 'WOOCOMERCE';

            return $output;
        }else{
            print_r($response);
            return 'Credenciales Invalidas: '.$USERNAME.' == '.$response['user'].' &&  '.$CLAVE.' == '.$response['password'];

        }
           
    }

}
