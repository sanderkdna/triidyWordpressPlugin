<?php
namespace Triidy_Automation;


class Triidy_AutomationInstall {
    
    /**
     * Ejecuta los procesos de activación del plugin.
     */
    public static function up() {
        global $wpdb;
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    
        $SQL = "CREATE TABLE IF NOT EXISTS " . TRIIDY_AUTOMATION_TABLE_ORDENES . "  (
                    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
                    order_id INT NOT NULL,
                    cliente VARCHAR(50) NOT NULL,
                    destinatario VARCHAR(60) NOT NULL,
                    direccion_destinatario VARCHAR(40) NOT NULL,
                    ciudad_destinatario VARCHAR(6) NOT NULL,
                    telefono_destinatario VARCHAR(15) NOT NULL,
                    email_destinatario VARCHAR(40) NOT NULL,
                    nombre_usuario VARCHAR(20) NOT NULL,
                    observaciones  TEXT,
                    envio_guia  VARCHAR(30) NOT NULL,
                    valor_servicio  DECIMAL(12,2) NOT NULL,
                    forma_pago  VARCHAR(2),
                    estado_prestamo VARCHAR(200),
                    response TEXT
                );";
        dbDelta($SQL);

        $SQL = "CREATE TABLE IF NOT EXISTS " . TRIIDY_AUTOMATION_TABLE_PRODUCTOS . "  (
                    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
                    product_id INT NOT NULL,
                    nombre  TEXT,
                    precio  VARCHAR(30) NOT NULL,
                    idTriidy  VARCHAR(30) NOT NULL,
                    created_at  VARCHAR(15),
                    source VARCHAR(20),
                    response TEXT
                );";
        dbDelta($SQL);        

        
        $SQL = "CREATE TABLE IF NOT EXISTS " . TRIIDY_AUTOMATION_TABLE_ADDRESSES . " (
                    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
                    nombre VARCHAR(40) NOT NULL,
                    descripcion VARCHAR(400) NOT NULL,
                    plan_id VARCHAR(40) NOT NULL
                );";
        dbDelta($SQL);

    }
    
    public static function down() {
        // Desactivar plugin...
    }
}