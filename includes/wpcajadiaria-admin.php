<?php
// Agregar el menú de administración
function wp_caja_diaria_menu() {
    add_menu_page(
        'Caja Diaria',
        'Caja Diaria',
        'manage_options',
        'wp-caja-diaria',
        'wp_caja_diaria_page',
        'dashicons-analytics',
        6
    );
}
add_action('admin_menu', 'wp_caja_diaria_menu');

// Encolar scripts y estilos
function wp_caja_diaria_enqueue_scripts($hook) {
    if ($hook !== 'toplevel_page_wp-caja-diaria') {
        return;
    }
    wp_enqueue_style('datatables-css', 'https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css');
    wp_enqueue_script('jquery');
    wp_enqueue_script('datatables-js', 'https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js', array('jquery'), null, true);
    wp_enqueue_script('wp-caja-diaria-js', plugins_url('/js/wpcajadiaria.js', __FILE__), array('jquery', 'datatables-js'), null, true);

    // Localizar script con nonce para seguridad AJAX
    wp_localize_script('wp-caja-diaria-js', 'wp_caja_diaria', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('wp_caja_diaria_nonce') // Agregar nonce
    ));
}
add_action('admin_enqueue_scripts', 'wp_caja_diaria_enqueue_scripts');

// Estilos personalizados
function wp_caja_diaria_custom_styles() {
    echo '<style>
        .loading-message {
            text-align: center;
            font-size: 18px;
            color: #333;
            padding: 10px;
        }
    </style>';
}
add_action('admin_head', 'wp_caja_diaria_custom_styles');

// Contenido de la página de administración
function wp_caja_diaria_page() {
    ?>
    <div class="wrap">
        <h1>Caja Diaria</h1>

        <div style="display: flex; margin-bottom: 20px;">
            <form id="wp-caja-diaria-form-ingreso" method="post" action="">
                <h2>Registrar Ingreso</h2>
                <input type="hidden" name="action" value="wp_caja_diaria_add_ingreso">
                <label for="descripcion-ingreso">Descripción:</label><br>
                <input type="text" id="descripcion-ingreso" name="descripcion" required><br><br>
                <label for="monto-ingreso">Monto:</label><br>
                <input type="number" step="0.01" id="monto-ingreso" name="monto" required><br><br>
                <label for="categoria-ingreso">Categoría:</label><br>
                <select id="categoria-ingreso" name="categoria">
                    <option value="ventas">Ventas</option>
                    <option value="servicios">Servicios</option>
                    <option value="otros">Otros</option>
                </select><br><br>
                <input type="submit" name="submit_ingreso" value="Registrar Ingreso">
            </form>

            <form id="wp-caja-diaria-form-egreso" method="post" action="">
                <h2>Registrar Egreso</h2>
                <input type="hidden" name="action" value="wp_caja_diaria_add_movement">
                <label for="descripcion-egreso">Descripción:</label><br>
                <input type="text" id="descripcion-egreso" name="descripcion" required><br><br>
                <label for="monto-egreso">Monto:</label><br>
                <input type="number" step="0.01" id="monto-egreso" name="monto" required><br><br>
                <label for="categoria-egreso">Categoría:</label><br>
                <select id="categoria-egreso" name="categoria">
                    <option value="compras">Compras</option>
                    <option value="gastos">Gastos</option>
                    <option value="otros">Otros</option>
                </select><br><br>
                <input type="submit" name="submit_egreso" value="Registrar Egreso">
            </form>
        </div>

        <div style="margin-bottom: 20px;">
            <button id="reset-caja">Resetear Caja Diaria</button>
            <button id="refresh-table">Actualizar Caja Diaria</button>
            <span id="error-message" style="color: red; margin-left: 10px;"></span>
        </div>

        <table id="caja-diaria-table" class="display" style="width:100%">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Fecha</th>
                    <th>Tipo</th>
                    <th>Monto</th>
                    <th>Descripción</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th colspan="3">Total:</th>
                    <th></th>
                    <th></th>
                    <th></th>
                </tr>
            </tfoot>
        </table>
    </div>
    <?php
}