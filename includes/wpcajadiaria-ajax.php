<?php

// Verificar nonce para seguridad
function wpcd_verify_nonce() {
    // Verificar si el nonce está configurado y es válido
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wp_caja_diaria_nonce')) {
        wp_die('Error de verificación de nonce.'); 
    }
}

// Controlador AJAX para obtener movimientos
function wp_caja_diaria_get_movements() {
    wpcd_verify_nonce();
    global $wpdb;
    $table_name = $wpdb->prefix . 'caja_diaria';
    $results = $wpdb->get_results("SELECT * FROM $table_name WHERE DATE(fecha) = CURDATE()", ARRAY_A);

    foreach ($results as &$result) {
        $result['tipo'] = ucfirst($result['tipo']);
        $result['acciones'] = ($result['manual'] == 1) 
            ? '<button class="delete-movement" data-id="' . $result['id'] . '" data-tipo="' . strtolower($result['tipo']) . '" data-manual="' . $result['manual'] . '">Eliminar</button>' 
            : '';
    }

    wp_send_json($results);
}

// Controlador AJAX para agregar movimiento
function wp_caja_diaria_add_movement() {
    wpcd_verify_nonce();
    global $wpdb;
    $descripcion = wpcd_sanitize_input($_POST['descripcion']);
    $monto = wpcd_sanitize_input($_POST['monto']);
    $categoria = wpcd_sanitize_input($_POST['categoria']);

    $wpdb->insert(
        $wpdb->prefix . 'caja_diaria',
        array(
            'tipo' => 'egreso',
            'monto' => $monto,
            'descripcion' => $descripcion . ' (' . $categoria . ')',
            'manual' => 1
        )
    );

    wp_send_json_success(); 
}

// Controlador AJAX para agregar ingreso
function wp_caja_diaria_add_ingreso() {
    wpcd_verify_nonce();
    global $wpdb;
    $descripcion = wpcd_sanitize_input($_POST['descripcion']);
    $monto = wpcd_sanitize_input($_POST['monto']);
    $categoria = wpcd_sanitize_input($_POST['categoria']);

    $wpdb->insert(
        $wpdb->prefix . 'caja_diaria',
        array(
            'tipo' => 'ingreso',
            'monto' => $monto,
            'descripcion' => $descripcion . ' (' . $categoria . ')',
            'manual' => 1
        )
    );

    wp_send_json_success(); 
}

// Controlador AJAX para eliminar movimiento
function wp_caja_diaria_delete_movement() {
    wpcd_verify_nonce();
    global $wpdb;
    $id = intval($_POST['id']);
    $tipo = sanitize_text_field($_POST['tipo']);
    $manual = intval($_POST['manual']);

    if (($tipo == 'egreso' || $tipo == 'ingreso') && $manual == 1) {
        $wpdb->delete(
            $wpdb->prefix . 'caja_diaria',
            array('id' => $id)
        );
    }

    wp_send_json_success(); 
}

// Controlador AJAX para actualizar la caja registradora
function wp_caja_diaria_update_caja() {
    wpcd_verify_nonce();
    global $wpdb;
    $table_name = $wpdb->prefix . 'caja_diaria';

    // Obtener pedidos completados del día actual con el método de pago "custom_payment"
    $args = array(
        'status' => 'completed',
        'date_completed' => date('Y-m-d'),
        'payment_method' => 'custom_payment' 
    );
    $orders = wc_get_orders($args);

    if (!empty($orders)) {
        foreach ($orders as $order) {
            $order_id = $order->get_id();

            // Verificar si el pedido ya existe en la tabla
            $exists = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $table_name WHERE order_id = %d",
                $order_id
            ));

            if (!$exists) {
                $monto = $order->get_total();
                $fecha = $order->get_date_completed()->date('Y-m-d H:i:s');
                $descripcion = 'Venta Woocommerce (' . $order->get_payment_method_title() . ')';

                $wpdb->insert(
                    $table_name,
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
        }
    }

    wp_send_json_success(); // Enviar respuesta de éxito
}

// Controlador AJAX para resetear la caja registradora
function wp_caja_diaria_reset() {
    wpcd_verify_nonce();
    global $wpdb;
    $table_name = $wpdb->prefix . 'caja_diaria';

    $wpdb->query("TRUNCATE TABLE $table_name"); 

    wp_send_json_success(); 
}

// Enganchar las acciones AJAX
add_action('wp_ajax_wp_caja_diaria_get_movements', 'wp_caja_diaria_get_movements');
add_action('wp_ajax_wp_caja_diaria_add_movement', 'wp_caja_diaria_add_movement');
add_action('wp_ajax_wp_caja_diaria_add_ingreso', 'wp_caja_diaria_add_ingreso');
add_action('wp_ajax_wp_caja_diaria_delete_movement', 'wp_caja_diaria_delete_movement');
add_action('wp_ajax_wp_caja_diaria_update_caja', 'wp_caja_diaria_update_caja');
add_action('wp_ajax_wp_caja_diaria_reset', 'wp_caja_diaria_reset');