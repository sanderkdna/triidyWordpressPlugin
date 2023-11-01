<?php


// $addresses = \Triidy_Automation\Shipping\Addresses\AddressesFunctions::getAll();
$client_id = get_option(TRIIDY_AUTOMATION_SETTING_CLIENT_ID);

$_LIMIT_PERCENT = 75;

$order_id = isset($_GET['order_id']) ? $_GET['order_id'] : null;
$order = null;
$destinatario = '';
$direccion_destinatario = '';
$telefono_destinatario = '';
$email_destinatario = '';
$ciudad_destinatario = '';
$nombre_usuario = "";


if ($order_id) {
    $order = \Triidy_Automation\Shipping\ShippingFunctions::getOrders($order_id);
    if($order) {
    $destinatario = $order[0]['billing']['first_name'] . ' ' . $order[0]['billing']['last_name'];
        $direccion_destinatario = $order[0]['billing']['address_1'];
        $telefono_destinatario = $order[0]['billing']['phone'];
        $email_destinatario = $order[0]['billing']['email'];
        $ciudad_destinatario = $order[0]['billing']['city'];
        $nombre_usuario = $order[0]['nombre_usuario'];
    }
}
?>
<div class="wrap">
    <h1><?php _e('Enviar Orden Seleccioanda a Triidy', TRIIDY_AUTOMATION_SLUG); ?></h1>
    <p>Enviar Orden Seleccionada a Triidy.</p>
    <div class="widgets-holder-wrap" style="padding: 10px">
        <?php if (!empty($client_id)): ?>
            <form action="" method="post" onsubmit="onSubmit()">

                <table class="form-table">
                    <tbody>
                    <tr class="row-destinatario">
                        <th scope="row" align="right">
                            <label for="order_id"><?php _e('Orden', TRIIDY_AUTOMATION_SLUG); ?> <span
                                        class="required">*</span></label>
                        </th>
                        <td>
                            <select class="order_id" id="order_id" name="order_id" required></select>
                        </td>
                    </tr>
                    <tr class="row-destinatario">
                        <th scope="row">
                            <label for="destinatario"><?php _e('Destinatario', TRIIDY_AUTOMATION_SLUG); ?> <span
                                        class="required">*</span></label>
                        </th>
                        <td>
                            <input type="text" name="destinatario" id="destinatario" class="regular-text"
                                   placeholder="<?php echo esc_attr('', TRIIDY_AUTOMATION_SLUG); ?>"  required
                   value="<?php echo $destinatario;?>"/>
                        </td>
                    </tr>
                    <tr class="row-ciudad-destinatario">
                        <th scope="row">
                            <label for="ciudad_destinatario"><?php _e('Ciudad Destinatario', TRIIDY_AUTOMATION_SLUG); ?> <span class="required">*</span></label>
                        </th>
                        <td>
                            <input type="text" name="ciudad_destinatario" id="ciudad_destinatario"
                                   class="regular-text" placeholder="<?php echo esc_attr('', TRIIDY_AUTOMATION_SLUG); ?>" required value="<?php echo $ciudad_destinatario;?>"/>
                        </td>
                    </tr>
                    <tr class="row-direccion-destinatario">
                        <th scope="row">
                            <label for="direccion_destinatario"><?php _e('Dirección Destinatario', TRIIDY_AUTOMATION_SLUG); ?> <span
                                        class="required">*</span></label>
                        </th>
                        <td>
                            <input type="text" name="direccion_destinatario" id="direccion_destinatario"
                                   class="regular-text" placeholder="<?php echo esc_attr('', TRIIDY_AUTOMATION_SLUG); ?>" required value="<?php echo $direccion_destinatario;?>"/>
                        </td>
                    </tr>
                    <tr class="row-telefono-destinatario">
                        <th scope="row">
                            <label for="telefono_destinatario"><?php _e('Teléfono Destinatario', TRIIDY_AUTOMATION_SLUG); ?> <span
                                        class="required">*</span></label>
                        </th>
                        <td>
                            <input type="text" name="telefono_destinatario" id="telefono_destinatario"
                                   class="regular-text" placeholder="<?php echo esc_attr('', TRIIDY_AUTOMATION_SLUG); ?>" required value="<?php echo $telefono_destinatario;?>"/>
                        </td>
                    </tr>
                    <tr class="row-email-destinatario">
                        <th scope="row">
                            <label for="email_destinatario"><?php _e('Correo Destinatario', TRIIDY_AUTOMATION_SLUG); ?> <span
                                        class="required">*</span></label>
                        </th>
                        <td>
                            <input type="email" name="email_destinatario" id="email_destinatario" class="regular-text"
                                   placeholder="<?php echo esc_attr('', TRIIDY_AUTOMATION_SLUG); ?>" required value="<?php echo $email_destinatario;?>"/>
                            <input type="hidden" name="nombre_usuario" id="nombre_usuario" class="regular-text"
                                   placeholder="<?php echo esc_attr('', TRIIDY_AUTOMATION_SLUG); ?>" required value="<?php echo $nombre_usuario;?>"/>       
                        </td>
                    </tr>
                    <tr class="row-observaciones">
                        <th scope="row">
                            <label for="observaciones"><?php _e('Observaciones', TRIIDY_AUTOMATION_SLUG); ?></label>
                        </th>
                        <td>
                            <textarea name="observaciones" id="observaciones"
                                      placeholder="<?php echo esc_attr('', TRIIDY_AUTOMATION_SLUG); ?>" rows="5"
                                      cols="53"><?php echo($order ? $order[0]['observaciones'] : ''); ?></textarea>
                            <p class="description"><?php _e('Ingrese alguna observación que sea de ayuda para el proceso de administración del prestamo', TRIIDY_AUTOMATION_SLUG); ?></p>

                            <textarea name="order_detail" style="display:none" id="order_detail"
                                      placeholder="<?php echo esc_attr('', TRIIDY_AUTOMATION_SLUG); ?>" rows="5"
                                      cols="53"><?php echo($order ? $order[0]['order_detail'] : ''); ?></textarea>
                        </td>
                    </tr>
                    </tbody>
                </table>

                <input type="hidden" name="field_id" value="0">

                <?php wp_nonce_field('triidy_automation-create'); ?>
                <?php submit_button(__('Enviar a Triidy', TRIIDY_AUTOMATION_SLUG), 'primary', 'submit_triidy_automation_envios'); ?>

            </form>
        <?php else: ?>
            <div class="client-required">
                <p>Para hacer uso de esta funcionalidad, es necesario definir el valor del parámetro "<strong>Cliente
                        ID</strong>"</p>
                <p>Para definir este parámetro por favor diríjase al panel de configuración:</p>
                <p><a href="<?php echo admin_url('admin.php?page=triidy_automation-settings'); ?>"
                      class="add-new-h2"><?php _e('Configuración', TRIIDY_AUTOMATION_SLUG); ?></a></p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    jQuery(function ($) {

        $('#fecha_recogida').datepicker({
            dateFormat: 'yy-mm-dd'
        });
        $('#hora_recogida').timepicker({
            startHour: 0,
        });

        var options = {
            placeholder: "<?php _e('Selecciona una orden', TRIIDY_AUTOMATION_SLUG); ?>",
            width: '25em',
            ajax: {
                url: ajaxurl,
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term, // search query
                        action: 'triidy_automation_get_order_data' // AJAX action for admin-ajax.php
                    };
                },
                processResults: function (data) {
                    window['select2_options'] = [];
                    if (data) {
                        $.each(data, function (index, item) {
                            select2_options.push({id: item.id, text: item.text, observaciones: item.observaciones, shipping: item.shipping, billing: item.billing, order_detail:item.order_detail, nombre_usuario:item.nombre_usuario});
                        });
                    }
                    return {
                        results: select2_options
                    };
                },
                cache: true
            },
            cache: true,
            delay: 250,
            minimumInputLength: 1
        };

        if (<?php echo isset($order) ? 1 : 0; ?>) {
            options['data'] = <?php echo json_encode($order)?>;
        }

        $('#order_id').select2(options);
        $('#order_id').on('select2:select', function (e) {
            for (var i = 0; i < select2_options.length; i++) {
                if (select2_options[i].id === parseInt(e.target.value)) {
                    console.log(select2_options[i]);
                    $('#observaciones').val(select2_options[i].observaciones);
                    $('#order_detail').val(select2_options[i].order_detail);
                    $('#nombre_usuario').val(select2_options[i].nombre_usuario);
                    $('#destinatario').val(select2_options[i].billing.first_name + ' ' + select2_options[i].billing.last_name);
                    $('#direccion_destinatario').val(select2_options[i].billing.address_1);
                    $('#telefono_destinatario').val(select2_options[i].billing.phone);
                    $('#ciudad_destinatario').val(select2_options[i].billing.city);
                    $('#email_destinatario').val(select2_options[i].billing.email);

                }
            }
        });

    function similar_text(first, second, percent) { 
      if (first === null ||
        second === null ||
        typeof first === 'undefined' ||
        typeof second === 'undefined') {
        return 0
      }
      first = first.toLowerCase() + ''
      second = second.toLowerCase() + ''
      var pos1 = 0
      var pos2 = 0
      var max = 0
      var firstLength = first.length
      var secondLength = second.length
      var p
      var q
      var l
      var sum

      for (p = 0; p < firstLength; p++) {
        for (q = 0; q < secondLength; q++) {
          for (l = 0; (p + l < firstLength) && (q + l < secondLength) && (first.charAt(p + l) === second.charAt(q + l)); l++) { // eslint-disable-line max-len
        // @todo: ^-- break up this crazy for loop and put the logic in its body
          }
          if (l > max) {
        max = l
        pos1 = p
        pos2 = q
          }
        }
      }
      sum = max
      if (sum) {
        if (pos1 && pos2) {
          sum += similar_text(first.substr(0, pos1), second.substr(0, pos2))
        }
        if ((pos1 + max < firstLength) && (pos2 + max < secondLength)) {
          sum += similar_text(
        first.substr(pos1 + max, firstLength - pos1 - max),
        second.substr(pos2 + max,
        secondLength - pos2 - max))
        }
      }
      if (!percent) {
        return sum
      }
      return (sum * 200) / (firstLength + secondLength)
    }

    });

    function onSubmit(){
     document.body.insertAdjacentHTML('afterend', '<div class="client-required" style="opacity: .85;"><p style="font-size: 20px;margin: 0px;">Generando envio, por favor espere...</p><p><div class="lds-ellipsis"><div></div><div></div><div></div><div></div></div></p></div>');
    window.scrollTo(0,0);
    return true;
    };
</script>
