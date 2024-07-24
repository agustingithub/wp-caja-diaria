<?php
/*
Plugin Name: WP Caja Diaria
Description: Plugin para gestionar ingresos y gastos diarios integrado con Woocommerce.
Version: 1.1
Author: Tu Nombre
*/

if (!defined('ABSPATH')) {
    exit; // Salir si se accede directamente
}

// Hook de activación para crear la tabla de la base de datos
function wp_caja_diaria_activate() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'caja_diaria';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        fecha datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        tipo varchar(10) NOT NULL,
        monto decimal(10,2) NOT NULL,
        descripcion text NOT NULL,
        manual boolean DEFAULT 0 NOT NULL,
        order_id bigint(20) DEFAULT NULL,
        PRIMARY KEY (id),
        UNIQUE KEY order_id (order_id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'wp_caja_diaria_activate');

// Includes
require_once plugin_dir_path(__FILE__) . 'includes/wpcajadiaria-functions.php';
require_once plugin_dir_path(__FILE__) . 'includes/wpcajadiaria-admin.php';
require_once plugin_dir_path(__FILE__) . 'includes/wpcajadiaria-ajax.php';
require_once plugin_dir_path(__FILE__) . 'includes/wpcajadiaria-woocommerce.php';