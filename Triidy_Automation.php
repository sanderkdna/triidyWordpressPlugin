<?php
/*
 * Plugin Name: Triidy_Automation
 * Description: Plugin de sincronización de datos
 * Version: 1.0.0
 * Author: Triidy SAS
 * Author URI: http://www.triidy.com
 * License: GPLv2
 */



/* Definimos las constantes de plugin */


ob_clean();
ob_start();

/**
 * Si este archivo se llama directamente, aborta el sistema
 */
if (!defined('WPINC')) {
    die;
}

/**
 * Definimos la ruta base del archivo principal para su uso en futuras referencias.
 */
if (!defined('TRIIDY_AUTOMATION_PLUGIN_FILE')) {
    define('TRIIDY_AUTOMATION_PLUGIN_FILE', __FILE__);
}

/**
 * Cargamos un archivos que contiene la definición de todas las constanstes usadas en este pugin.
 */
require_once __DIR__ . '/constants.php';

/**
 * Cargamos el autoload de composer para cargar todos los namespaces definidos.
 */
require_once __DIR__ . '/vendor/autoload.php';
/**
 * Instanciamos el objeto principal del plugin y ejecutamos la carga de elementos.
 */
$triidy_automation = new \Triidy_Automation\Triidy_Automation();
$triidy_automation->run();