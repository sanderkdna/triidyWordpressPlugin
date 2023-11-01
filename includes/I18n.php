<?php


namespace Triidy_Automation;


/**
 * Define la funcionalidad de internacionalización.
 * @link       https://admhosti.site
 * @package    Triidy_Automation
 * @subpackage Triidy_Automation/I18n
 * @author     Sander Cadena <cadenasander@gmail.com>
 */
class I18n {
    
    /**
     * Cargue el dominio de texto del plugin para la traducción.
     * @param $plugin_name
     */
    public function load_plugin_textdomain() {
        $path_i18n = plugin_basename(dirname(TRIIDY_AUTOMATION_PLUGIN_FILE)) . '/i18n';
        load_plugin_textdomain(TRIIDY_AUTOMATION_SLUG, false, $path_i18n);
    }
}