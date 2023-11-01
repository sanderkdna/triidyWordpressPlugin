<?php
namespace Triidy_Automation;


use Triidy_Automation\Settings\Settings;
use Triidy_Automation\Shipping\Shipping;

class Triidy_Automation
{

    /**
     * Esta es la función princiál encargada de cargar todos los elementos que incluye este plugin.
     */
    public function run()
    {

        register_activation_hook(TRIIDY_AUTOMATION_PLUGIN_FILE, [$this, 'activation_plugin']);
        register_deactivation_hook(TRIIDY_AUTOMATION_PLUGIN_FILE, [$this, 'deactivation_plugin']);
        add_action('rest_api_init', [$this, 'register_api_functions']);

        /**
         * Cargamos la traducción del actual plugin.
         */
        $i18n = new I18n();
        $i18n->load_plugin_textdomain();

        /**
         * Cargamos el módulo para la administración de envios de ordenes.
         */
        $shipping = new Shipping();
        $shipping->load();

        $settings = new Settings();
        $settings->load();

    }

    /**
     * Esta función permite registrar las condiciones de acceso a la API
     */
    public function register_api_functions()
    {

        /**
         * Path: [HTTP_SERVER]/wp-json/Triidy_Automation/shipping/?id=[ID]&key=[KEY]
         */
        register_rest_route(TRIIDY_AUTOMATION_SLUG, '/shipping', [
            'methods' => 'POST',
            'callback' => [Triidy_AutomationApi::class, 'loadApi']
        ]);
    }

    public function activation_plugin()
    {
        Triidy_AutomationInstall::up();
    }

    public function deactivation_plugin()
    {
        Triidy_AutomationInstall::down();
    }

}

 