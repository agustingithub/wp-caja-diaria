<?php
// Función para registrar un ingreso cuando se completa un pedido de WooCommerce
function wp_caja_diaria_register_ingreso($order_id) {
    global $wpdb;

    // Sanitizar el ID del pedido
    $order_id = absint($order_id);

    // Obtener el pedido y verificar si está completado
    $order = wc_get_order($order_id);
    if (!$order || $order->get_status() !== 'completed') {
        return;
    }

    // Obtener el monto total del pedido
    $monto = $order->get_total();

    // Obtener la fecha de completado del pedido
    $fecha = $order->get_date_completed()->date('Y-m-d H:i:s');

    // Descripción del ingreso
    $descripcion = 'Venta Woocommerce (' . $order->get_payment_method_title() . ')';

    // Insertar el registro en la tabla de caja diaria
    $wpdb->insert(
        $wpdb->prefix . 'caja_diaria',
        array(
            'fecha' => $fecha,
            'tipo' => 'ingreso',
            'monto' => $monto,
            'descripcion' => $descripcion,
            'manual' => 0,
            'order_id' => $order_id
        )
    );
}

// Enganchar la función al evento de completar un pedido de WooCommerce
add_action('woocommerce_order_status_completed', 'wp_caja_diaria_register_ingreso');