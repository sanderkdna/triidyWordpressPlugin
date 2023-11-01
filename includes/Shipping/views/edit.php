<?php

$id = isset($_GET['id']) ? $_GET['id'] : false;
$cities = \Triidy_Automation\Shipping\ShippingFunctions::getAllCities();
$item = \Triidy_Automation\Shipping\ShippingFunctions::getById($id);
$addresses = \Triidy_Automation\Shipping\Addresses\AddressesFunctions::getAll();
$client_id = get_option(TRIIDY_AUTOMATION_SETTING_CLIENT_ID);

$order = null;
if ($item->order_id) {
    $order = \Triidy_Automation\Shipping\ShippingFunctions::getOrders($item->order_id);
}

?>
<div class="wrap">
    <h1><?php _e('Agregar nuevo envio', TRIIDY_AUTOMATION_SLUG); ?></h1>
    <p>Registre una nueva solicitud de envio de producos, para la orden seleccionada.</p>
    <div class="widgets-holder-wrap" style="padding: 10px">
        <?php if (!empty($client_id)): ?>
            <form action="" method="post">

                <table class="form-table">
                    <tbody>
                    <tr class="row-destinatario">
                        <th scope="row">
                            <label for="order_id"><?php _e('Orden', TRIIDY_AUTOMATION_SLUG); ?></label>
                        </th>
                        <td>
                            <select class="order_id" id="order_id" name="order_id"></select>
                        </td>
                    </tr>
                    <tr class="row-destinatario">
                        <th scope="row">
                            <label for="destinatario"><?php _e('Destinatario', TRIIDY_AUTOMATION_SLUG); ?></label>
                        </th>
                        <td>
                            <input type="text" name="destinatario" id="destinatario" class="regular-text" placeholder="<?php echo esc_attr('', TRIIDY_AUTOMATION_SLUG); ?>" value="<?php echo esc_attr($item->destinatario); ?>" required="required"/>
                        </td>
                    </tr>
                    <tr class="row-direccion-destinatario">
                        <th scope="row">
                            <label for="direccion_destinatario"><?php _e('Dirección Destinatario', TRIIDY_AUTOMATION_SLUG); ?></label>
                        </th>
                        <td>
                            <input type="text" name="direccion_destinatario" id="direccion_destinatario" class="regular-text" value="<?php echo esc_attr($item->direccion_destinatario); ?>"/>
                        </td>
                    </tr>
                    <tr class="row-ciudad-destinatario">
                        <th scope="row">
                            <label for="ciudad_destinatario"><?php _e('Ciudad Destinatario', TRIIDY_AUTOMATION_SLUG); ?></label>
                        </th>
                        <td>
                            <select name="ciudad_destinatario" id="ciudad_destinatario" required>
                                <option value=""><?php _e('- Seleccione una ciudad -', TRIIDY_AUTOMATION_SLUG); ?></option>
                                <?php foreach ($cities as $city) : ?>
                                    <option value="<?php echo $city->codigo ?>" <?php echo $city->codigo == $item->ciudad_destinatario ? 'selected' : '' ?>><?php echo $city->nombre ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr class="row-telefono-destinatario">
                        <th scope="row">
                            <label for="telefono_destinatario"><?php _e('Teléfono Destinatario', TRIIDY_AUTOMATION_SLUG); ?></label>
                        </th>
                        <td>
                            <input type="text" name="telefono_destinatario" id="telefono_destinatario" class="regular-text" value="<?php echo esc_attr($item->telefono_destinatario); ?>"/>
                        </td>
                    </tr>
                    <tr class="row-email-destinatario">
                        <th scope="row">
                            <label for="email_destinatario"><?php _e('Correo Destinatario', TRIIDY_AUTOMATION_SLUG); ?></label>
                        </th>
                        <td>
                            <input type="text" name="email_destinatario" id="email_destinatario" class="regular-text" value="<?php echo esc_attr($item->email_destinatario); ?>"/>
                        </td>
                    </tr>

                    <tr class="row-ciudad">
                        <th scope="row">
                            <label for="recogida_id"><?php _e('Dirección de Recogida', TRIIDY_AUTOMATION_SLUG); ?></label>
                        </th>
                        <td>
                            <select name="recogida_id" id="recogida_id" required style="width: 16.8em;">
                                <?php foreach ($addresses as $addres) : ?>
                                    <option value="<?php echo $addres->id ?>" <?php echo $addres->id == $item->recogida_id ? 'selected' : '' ?>><?php echo $addres->nombre ?></option>
                                <?php endforeach; ?>
                            </select>
                            <a href="<?php echo admin_url('admin.php?page=smmc-addresses&action=create'); ?>" class="add-new-h2" style="top: 0px;"><?php _e('Agregar Nueva', TRIIDY_AUTOMATION_SLUG); ?></a>
                        </td>
                    </tr>
                    <tr class="row-fecha-recogida">
                        <th scope="row">
                            <label for="fecha_recogida"><?php _e('Fecha de Recogida', TRIIDY_AUTOMATION_SLUG); ?></label>
                        </th>
                        <td>
                            <input type="text" name="fecha_recogida" id="fecha_recogida" value="<?php echo esc_attr($item->fecha_recogida); ?>" class="regular-text"/
                            >
                        </td>
                    </tr>
                    <tr class="row-ciudad-destinatario">
                        <th scope="row">
                            <label for="forma_pago"><?php _e('Forma de Pago', TRIIDY_AUTOMATION_SLUG); ?></label>
                        </th>
                        <td>
                            <select name="forma_pago" id="forma_pago" style="width: 25em;" required>
                                <option value="C" <?php echo $item->ciudad_destinatario = 'C' ? 'selected' : '' ?>><?php _e('Cobrar Valor', TRIIDY_AUTOMATION_SLUG); ?></option>
                                <option value="N" <?php echo $item->ciudad_destinatario = 'N' ? 'selected' : '' ?>><?php _e('Contra Entrega', TRIIDY_AUTOMATION_SLUG); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr class="row-observaciones">
                        <th scope="row">
                            <label for="observaciones"><?php _e('Observaciones', TRIIDY_AUTOMATION_SLUG); ?></label>
                        </th>
                        <td>
                            <textarea name="observaciones" id="observaciones" rows="5" cols="53">  <?php echo esc_attr($item->observaciones); ?> </textarea>
                            <p class="description"><?php _e('Ingrese alguna observación que sea de ayuda para el proceso de envío', TRIIDY_AUTOMATION_SLUG); ?></p>
                        </td>
                    </tr>
                    </tbody>
                </table>

                <input type="hidden" name="field_id" value="<?php echo $item->id; ?>">
                
                <?php wp_nonce_field('smmc-create'); ?>
                <?php submit_button(__('Actualizar Envío', TRIIDY_AUTOMATION_SLUG), 'primary', 'submit_smm_envios'); ?>

            </form>
        <?php else: ?>
            <div class="client-required">
                <p>Para hacer uso de esta funcionalidad, es necesario definir el valor del parámetro "<strong>Cliente
                        ID</strong>"</p>
                <p>Para definir este parámetro por favor diríjase al panel de configuración:</p>
                <p>
                    <a href="<?php echo admin_url('admin.php?page=smmc-settings'); ?>" class="add-new-h2"><?php _e('Configuración', TRIIDY_AUTOMATION_SLUG); ?></a>
                </p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    jQuery(function ($) {

        $('#fecha_recogida').datepicker({
            dateFormat: 'yy-mm-dd'
        });

        $('#ciudad_destinatario').select2({
            placeholder: "<?php _e('Selecciona una ciudad', TRIIDY_AUTOMATION_SLUG); ?>",
            width: '25em'
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
                    var options = [];
                    if (data) {
                        $.each(data, function (index, item) {
                            options.push({id: item.id, text: item.text});
                        });
                    }
                    return {
                        results: options
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

    });
</script>