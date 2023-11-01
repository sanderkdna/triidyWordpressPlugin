<div class="wrap">
    <h1><?php _e('Agregar Nuevo Plan', TRIIDY_AUTOMATION_SLUG); ?></h1>
    <div class="widgets-holder-wrap" style="padding: 10px">
        <form action="" method="post">
            <table class="form-table">
                <tbody>
                <tr class="row-nombre">
                    <th scope="row">
                        <label for="nombre"><?php _e('Nombre', TRIIDY_AUTOMATION_SLUG); ?> <span class="required">*</span></label>
                    </th>
                    <td>
                        <input type="text" name="nombre" id="nombre" class="regular-text"   required/>
                    </td>
                </tr>
                <tr class="row-descripcion">
                    <th scope="row">
                        <label for="descripcion"><?php _e('Descripcion', TRIIDY_AUTOMATION_SLUG); ?> <span class="required">*</span></label>
                    </th>
                    <td>
                        <input type="text" name="descripcion" id="descripcion" class="regular-text"  required/>
                    </td>
                </tr>
                <tr class="row-plan_id">
                    <th scope="row">
                        <label for="plan_id"><?php _e('PlanID', TRIIDY_AUTOMATION_SLUG); ?> <span class="required">*</span></label>
                    </th>
                    <td>
                        <input type="text" name="plan_id" id="plan_id" class="regular-text" required/>
                    </td>
                </tr>
                </tbody>
            </table>

            <input type="hidden" name="field_id" value="0">
            
            <?php wp_nonce_field('triidy_automation-create'); ?>
            <?php submit_button(__('Agregar Plan', TRIIDY_AUTOMATION_SLUG), 'primary', 'submit_triidy_automation_addresses'); ?>

        </form>
    </div>
</div>