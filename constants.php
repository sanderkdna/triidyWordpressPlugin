<?php

global $wpdb;

/**
 * Definimos un slug para clasificar todos los elementos dentro del plugin
 */
define('TRIIDY_AUTOMATION_SLUG', 'triidy_automation');
define('TRIIDY_AUTOMATION_API_URL', 'https://devopstriidy.com/Automations/api/');
define('TRIIDY_AUTOMATION_WEB_URL', 'https://devopstriidy.com/Automations/api/');
define('TRIIDY_AUTOMATION_GLOBAL_URL', 'https://devopstriidy.com/Automations/api/');

/**
 * Definimos la versión del plugin en una constante global.
 */
define('TRIIDY_AUTOMATION_VERSION', '1.0.0');

/**
 * Definimos los nombres de las tablas usadas por el plugin.
 */
define('TRIIDY_AUTOMATION_TABLE_PLANES', $wpdb->prefix . 'triidy_automation_planes');
define('TRIIDY_AUTOMATION_TABLE_ORDENES', $wpdb->prefix . 'triidy_automation_ordenes');
define('TRIIDY_AUTOMATION_TABLE_PRODUCTOS', $wpdb->prefix . 'triidy_automation_productos');
define('TRIIDY_AUTOMATION_TABLE_ADDRESSES', $wpdb->prefix . 'triidy_automation_addresses');


/**
 * Definimos las constantes para los parametros de configuración
 */
define('TRIIDY_AUTOMATION_SETTING_CLIENT_ID', 'TRIIDY_AUTOMATION_SETTING_CLIENT_ID');
define('TRIIDY_AUTOMATION_SETTING_USERNAME', 'TRIIDY_AUTOMATION_SETTING_USERNAME');
define('TRIIDY_AUTOMATION_SETTING_CLAVE', 'TRIIDY_AUTOMATION_SETTING_CLAVE');
define('TRIIDY_AUTOMATION_SETTING_ORDENES_AUTOMATICAS', 'TRIIDY_AUTOMATION_SETTING_ORDENES_AUTOMATICAS');
define('TRIIDY_AUTOMATION_SETTING_PRODUCTOS_AUTOMATICOS', 'TRIIDY_AUTOMATION_SETTING_PRODUCTOS_AUTOMATICOS');