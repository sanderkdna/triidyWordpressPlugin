<div class="wrap">
    <h1><?php _e('Configuración Triidy_Automation', TRIIDY_AUTOMATION_SLUG); ?></h1>
    <div class="widgets-holder-wrap" style="padding: 10px">
        <?php if (isset($_REQUEST['message'])): ?>
            <?php list($type, $message) = explode('|', $_REQUEST['message']); ?>
            <div id="message" class="notice-<?php echo esc_attr($type) ?> notice is-dismissible">
                <p><strong><?php echo esc_html($message) ?></strong></p>
                <button type="button" class="notice-dismiss"><span class="screen-reader-text">Descartar este aviso.</span>
                </button>
            </div>
        <?php endif; ?>
        <form method="post">
            <table class="form-table">
                <tbody>
                <tr class="row-nombre">
                    <th scope="row">
                        <label for="nombre"><?php _e('Token Code ', TRIIDY_AUTOMATION_SLUG); ?></label>
                    </th>
                    <td>
                        <input class="regular-text"  type="text" name="<?php echo TRIIDY_AUTOMATION_SETTING_CLIENT_ID; ?>" value="<?= get_option(TRIIDY_AUTOMATION_SETTING_CLIENT_ID); ?>" required/>
                        <p class="description"><?php _e('Ingrese el Token Code generado por la plataforma Automations', TRIIDY_AUTOMATION_SLUG); ?></p>
                    </td>
                </tr>
                <tr class="row-nombre">
                    <th scope="row">
                        <label for="nombre"><?php _e('Nombre de Usuario de Triidy', TRIIDY_AUTOMATION_SLUG); ?></label>
                    </th>
                    <td>
                        <input class="regular-text"  type="text" name="<?php echo TRIIDY_AUTOMATION_SETTING_USERNAME; ?>" value="<?= get_option(TRIIDY_AUTOMATION_SETTING_USERNAME); ?>" required/>
                        <p class="description"><?php _e('Ingrese el nombre de usuario de la plataforma de administracion', TRIIDY_AUTOMATION_SLUG); ?></p>
                    </td>
                </tr>
                <tr class="row-nombre">
                    <th scope="row">
                        <label for="nombre"><?php _e('Código Token Triidy', TRIIDY_AUTOMATION_SLUG); ?></label>
                    </th>
                    <td>
                        <input class="regular-text"  type="text" name="<?php echo TRIIDY_AUTOMATION_SETTING_CLAVE; ?>" value="<?= get_option(TRIIDY_AUTOMATION_SETTING_CLAVE); ?>" required/>
                        <p class="description"><?php _e('Ingrese la contraseña', TRIIDY_AUTOMATION_SLUG); ?></p>
                    </td>
                </tr>

                <tr class="row-nombre">
                    <th scope="row">
                        <label for="nombre"><?php _e('Seleccione si desea que las Ordenes sean enviadas automáticamente despues de creadas', TRIIDY_AUTOMATION_SLUG); ?></label>
                    </th>
                    <td>
                        <select  class="regular-text"  name="<?php echo TRIIDY_AUTOMATION_SETTING_ORDENES_AUTOMATICAS; ?>" id="<?php echo TRIIDY_AUTOMATION_SETTING_ORDENES_AUTOMATICAS; ?>" required>
                                <option value="">Seleccione una Opción</option> 
                                <option value="SI" <?php echo (get_option(TRIIDY_AUTOMATION_SETTING_ORDENES_AUTOMATICAS) == 'SI')?'selected="selected"':'' ?> >SI</option>
                                <option value="NO" <?php echo (get_option(TRIIDY_AUTOMATION_SETTING_ORDENES_AUTOMATICAS) == 'NO')?'selected="selected"':'' ?> >NO</option>
                        </select>
                        <p class="description"><?php _e('Seleccione una Opción', TRIIDY_AUTOMATION_SLUG); ?></p>
                    </td>
                </tr>

                <tr class="row-nombre">
                    <th scope="row">
                        <label for="nombre"><?php _e('Seleccione si desea que los Productos sean enviadas automáticamente despues de creados', TRIIDY_AUTOMATION_SLUG); ?></label>
                    </th>
                    <td>
                        <select  class="regular-text"  name="<?php echo TRIIDY_AUTOMATION_SETTING_PRODUCTOS_AUTOMATICOS; ?>" id="<?php echo TRIIDY_AUTOMATION_SETTING_PRODUCTOS_AUTOMATICOS; ?>" required>
                                <option value="">Seleccione una Opción</option> 
                                <option value="SI" <?php echo (get_option(TRIIDY_AUTOMATION_SETTING_PRODUCTOS_AUTOMATICOS) == 'SI')?'selected="selected"':'' ?> >SI</option>
                                <option value="NO" <?php echo (get_option(TRIIDY_AUTOMATION_SETTING_PRODUCTOS_AUTOMATICOS) == 'NO')?'selected="selected"':'' ?> >NO</option>
                        </select>
                        
                        <p class="description"><?php _e('Seleccione una Opción', TRIIDY_AUTOMATION_SLUG); ?></p>
                    </td>
                </tr>
                </tbody>
            </table>
            <?php submit_button(__('Guardar Cambios', TRIIDY_AUTOMATION_SLUG), 'primary', TRIIDY_AUTOMATION_SLUG); ?>
        </form>
    </div>
</div>