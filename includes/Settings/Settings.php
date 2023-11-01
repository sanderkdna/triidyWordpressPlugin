<?php


namespace Triidy_Automation\Settings;


class Settings {

    /**
     * Esta función permite cargar todos los recursos incluidos en este módulo.
     */
    public function load() {
        add_action('admin_menu', [$this, 'add_menu']);
    }

    /**
     * Esta función permite registrar los parametros generales de confiruación.
     */
    public function register_triidy_automation_setting() {
        register_setting(TRIIDY_AUTOMATION_SLUG, TRIIDY_AUTOMATION_SETTING_CLIENT_ID);
        register_setting(TRIIDY_AUTOMATION_SLUG, TRIIDY_AUTOMATION_SETTING_USERNAME);
        register_setting(TRIIDY_AUTOMATION_SLUG, TRIIDY_AUTOMATION_SETTING_CLAVE);
        register_setting(TRIIDY_AUTOMATION_SLUG, TRIIDY_AUTOMATION_SETTING_ORDENES_AUTOMATICAS);
        register_setting(TRIIDY_AUTOMATION_SLUG, TRIIDY_AUTOMATION_SETTING_PRODUCTOS_AUTOMATICOS);
    }

    /**
     * Agregamos el item de menú para administración del módulo.
     */
    public function add_menu() {

        $page = TRIIDY_AUTOMATION_SLUG . '-shipping';
        $sub_page = TRIIDY_AUTOMATION_SLUG . '-settings';
        $sub_page1 = TRIIDY_AUTOMATION_SLUG . '-products';


        add_submenu_page(   $page, 
                            __('Configuración', TRIIDY_AUTOMATION_SLUG), 
                            __('Configuración', TRIIDY_AUTOMATION_SLUG), 
                            'manage_options', 
                            $sub_page, 
                            [$this, 'setting_pages']
                        );

    }

    public function setting_pages() {
        $template = dirname(__FILE__) . '/views/settings.php';
        $updated = false;

        if (isset($_POST[TRIIDY_AUTOMATION_SETTING_CLIENT_ID])) { // Guardamos la configuración de ID cliente.
            update_option(TRIIDY_AUTOMATION_SETTING_CLIENT_ID, trim($_POST[TRIIDY_AUTOMATION_SETTING_CLIENT_ID]));
            $updated = true;
        }

        if (isset($_POST[TRIIDY_AUTOMATION_SETTING_USERNAME])) { // Guardamos la configuración de ID cliente.
            update_option(TRIIDY_AUTOMATION_SETTING_USERNAME, trim($_POST[TRIIDY_AUTOMATION_SETTING_USERNAME]));
            $updated = true;
        }

        if (isset($_POST[TRIIDY_AUTOMATION_SETTING_CLAVE])) { // Guardamos la configuración de ID cliente.
            update_option(TRIIDY_AUTOMATION_SETTING_CLAVE, trim($_POST[TRIIDY_AUTOMATION_SETTING_CLAVE]));
            $updated = true;
        }

        if (isset($_POST[TRIIDY_AUTOMATION_SETTING_ORDENES_AUTOMATICAS])) { // Guardamos la configuración de ID cliente.
            update_option(TRIIDY_AUTOMATION_SETTING_ORDENES_AUTOMATICAS, trim($_POST[TRIIDY_AUTOMATION_SETTING_ORDENES_AUTOMATICAS]));
            $updated = true;
        }

        if (isset($_POST[TRIIDY_AUTOMATION_SETTING_PRODUCTOS_AUTOMATICOS])) { // Guardamos la configuración de ID cliente.
            update_option(TRIIDY_AUTOMATION_SETTING_PRODUCTOS_AUTOMATICOS, trim($_POST[TRIIDY_AUTOMATION_SETTING_PRODUCTOS_AUTOMATICOS]));
            $updated = true;
        }

        if($updated) {
            $_REQUEST['message'] = __('success|Información de configuración actualizada con éxito!', TRIIDY_AUTOMATION_SLUG);
        }

        if (file_exists($template)) {
            include $template;
        }
    }

    public function product_page() {
        $template = dirname(__FILE__) . '/views/ProductList.php';
        $updated = false;

        
        if (file_exists($template)) {
            include $template;
        }else{
            echo 'el archivo '.$template.'no existe';
        }
    }
}