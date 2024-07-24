<?php
// Función auxiliar para sanitizar la entrada
function wpcd_sanitize_input($input) {
    if (is_numeric($input)) {
        return floatval($input); // Para entradas numéricas
    } else {
        return sanitize_text_field($input); // Para entradas de texto
    }
}