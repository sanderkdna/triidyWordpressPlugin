<?php


namespace Triidy_Automation\Shipping\Addresses;


class Addresses {
    
    /**
     * Define el nombre del item de menu padre para este elemento
     * @var String
     */
    protected $parent_menu;
    
    /**
     * Addresses constructor.
     * El contructor con la sobrecarga de las variables de configuración global.
     * @param $parent_menu
     */
    public function __construct($parent_menu) {
        $this->parent_menu = $parent_menu;
    }
    
    /**
     * Esta función controla la impresión de las vistas de este módulo
     */
    public function addresses_pages() {
        $action = isset($_GET['action']) ? $_GET['action'] : 'list';
        switch ($action) {
            case 'create':
            case 'edit':
                AddressesFunctions::handlerRequest();
                break;
            case 'delete':
                $id = isset($_GET['id']) ? $_GET['id'] : false;
                AddressesFunctions::delete($id);
                break;
        }
        
        $template = dirname(__FILE__) . '/views/' . $action . '.php';
        if (file_exists($template)) {
            include $template;
        }
    }
    
}