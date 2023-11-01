<?php

$id = isset($_GET['id']) ? $_GET['id'] : false;
$item = \Triidy_Automation\Shipping\Addresses\AddressesFunctions::getById($id);

?>
<div class="wrap">
    <h1><?php _e('Editar Plan', TRIIDY_AUTOMATION_SLUG); ?></h1>
    <form action="" method="post">
        <table class="form-table">
            <tbody>
            <tr class="row-nombre">
                <th scope="row">
                    <label for="nombre"><?php _e('Nombre', TRIIDY_AUTOMATION_SLUG); ?></label>
                </th>
                <td>
                    <input type="text" name="nombre" id="nombre" class="regular-text" value="<?php echo esc_attr($item->nombre); ?>" required/>
                </td>
            </tr>
            <tr class="row-descripcion">
                <th scope="row">
                    <label for="descripcion"><?php _e('Descripcion', TRIIDY_AUTOMATION_SLUG); ?></label>
                </th>
                <td>
                    <input type="text" name="descripcion" id="descripcion" class="regular-text" value="<?php echo esc_attr($item->descripcion); ?>" required/>
                </td>
            </tr>
            <tr class="row-plan_id">
                <th scope="row">
                    <label for="plan_id"><?php _e('Plan_id', TRIIDY_AUTOMATION_SLUG); ?></label>
                </th>
                <td>
                    <input type="text" name="plan_id" id="plan_id" class="regular-text" value="<?php echo esc_attr($item->plan_id); ?>" required/>
                </td>
            </tr>
            
            </tbody>
        </table>

        <input type="hidden" name="field_id" value="<?php echo $item->id; ?>">
        
        <?php wp_nonce_field('triidy_automation-create'); ?>
        <?php submit_button(__('Actualizar Plan de Pago', TRIIDY_AUTOMATION_SLUG), 'primary', 'submit_triidy_automation_addresses'); ?>

    </form>
</div>