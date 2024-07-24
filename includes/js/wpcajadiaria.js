jQuery(document).ready(function($) {
    // Aumentar el tiempo de espera de AJAX a 60 segundos
    $.ajaxSetup({
        timeout: 60000 
    });

    var table = $('#caja-diaria-table').DataTable({
        "ajax": {
            "url": wp_caja_diaria.ajax_url,
            "type": "POST",
            "data": { 
                action: "wp_caja_diaria_get_movements",
                nonce: wp_caja_diaria.nonce  // Asegúrate de enviar el nonce
            },
            "dataSrc": function (json) {
                console.log("Respuesta AJAX:", json); // Registro de depuración
                $('.loading-message').remove(); 
                return json;
            },
            "error": function (xhr, error, thrown) {
                console.error("Error en la llamada AJAX:", error, thrown);
                $('#error-message').text("Error al cargar los datos. Por favor, inténtalo de nuevo.");
            }
        },
        "columns": [
            { "data": "id" },
            { "data": "fecha" },
            { "data": "tipo" },
            { "data": "monto" },
            { "data": "descripcion" },
            { "data": "acciones" }
        ],
        "language": {
            "url": "https://cdn.datatables.net/plug-ins/1.11.3/i18n/es_es.json"
        },
        "footerCallback": function(row, data, start, end, display) {
            var api = this.api();
            var total = 0;

            api.column(3, { page: 'current' }).data().each(function(value, index) {
                var tipo = data[index].tipo.toLowerCase();
                if (tipo === 'egreso') {
                    total -= parseFloat(value);
                } else {
                    total += parseFloat(value);
                }
            });

            $(api.column(3).footer()).html(total.toFixed(2));
        }
    });

    function handleFormSubmit(form, action) {
        form.submit(function(event) {
            event.preventDefault();
            var formData = $(this).serialize();

            $.post(wp_caja_diaria.ajax_url, formData, function(response) {
                if (response.success) {
                    table.ajax.reload(null, false);
                    form[0].reset();
                    $('#error-message').text(""); 
                } else {
                    $('#error-message').text(response.data); 
                }
            }).fail(function(xhr, status, error) {
                console.error("Error en la llamada AJAX (" + action + "):", error);
                $('#error-message').text("Error al agregar el " + action + ". Por favor, inténtalo de nuevo.");
            });
        });
    }

    handleFormSubmit($('#wp-caja-diaria-form-ingreso'), 'ingreso');
    handleFormSubmit($('#wp-caja-diaria-form-egreso'), 'egreso');

    $('#caja-diaria-table').on('click', '.delete-movement', function() {
        if (!confirm('¿Estás seguro de que deseas eliminar este movimiento?')) {
            return;
        }

        var id = $(this).data('id');
        var tipo = $(this).data('tipo');
        var manual = $(this).data('manual');

        $.post(wp_caja_diaria.ajax_url, {
            action: 'wp_caja_diaria_delete_movement',
            id: id,
            tipo: tipo,
            manual: manual,
            nonce: wp_caja_diaria.nonce 
        }, function(response) {
            if (response.success) {
                table.ajax.reload(null, false);
                $('#error-message').text(""); 
            } else {
                $('#error-message').text(response.data); 
            }
        }).fail(function(xhr, status, error) {
            console.error("Error en la llamada AJAX (eliminar):", error);
            $('#error-message').text("Error al eliminar el movimiento. Por favor, inténtalo de nuevo.");
        });
    });

    $('#reset-caja').click(function() {
        if (!confirm('¿Estás seguro de que deseas resetear la caja diaria?')) {
            return;
        }

        $.post(wp_caja_diaria.ajax_url, {
            action: 'wp_caja_diaria_reset',
            nonce: wp_caja_diaria.nonce 
        }, function(response) {
            if (response.success) {
                table.ajax.reload(null, false);
                $('#error-message').text(""); 
            } else {
                $('#error-message').text(response.data); 
            }
        }).fail(function(xhr, status, error) {
            console.error("Error en la llamada AJAX (reset):", error);
            $('#error-message').text("Error al resetear la caja. Por favor, inténtalo de nuevo.");
        });
    });

    $('#refresh-table').click(function() {
        $('.loading-message').remove(); 
        table.clear().draw(); 
        $('#caja-diaria-table tbody').html('<tr><td colspan="6" class="loading-message">Cargando...</td></tr>'); 

        $.post(wp_caja_diaria.ajax_url, {
            action: 'wp_caja_diaria_update_caja',
            nonce: wp_caja_diaria.nonce 
        }, function(response) {
            if (response.success) {
                table.ajax.reload(null, false);
                $('#error-message').text(""); 
            } else {
                $('#error-message').text(response.data); 
            }
        }).fail(function(xhr, status, error) {
            console.error("Error en la llamada AJAX (actualizar):", error);
            $('#error-message').text("Error al actualizar la caja. Por favor, inténtalo de nuevo.");
        });
    });
});