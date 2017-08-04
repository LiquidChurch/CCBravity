<form id="ccb-gravity-field-config-form" method="post" action="">
    <?php
    wp_nonce_field('ccb-gravity-field-config-form');
    ?>
    <input type="hidden" name="action" value="ccb-gravity-field-config-form" />

    <div class="wrap ccb-gravity-field-config-wrap">

        <?php
        $fieldnames = $this->get('fieldnames');
        foreach ($fieldnames as $index => $fieldname) {
            ?>
            <fieldset class="ccb-field-model">
                <div class="form-group">
                    <div class="">
                        <label for="">CCB Field Name: </label>

                        <input name="fieldName[]" type="text" placeholder="Enter field name" value="<?php echo $fieldname ?>" class="field-name" required>

                        <a href="javascript:void(0);" class="btn btn-warning remove-this-field">
                            <span class="hidden-xs"> Delete </span>
                        </a>

                        <a href="javascript:void(0);" class="btn btn-success create-new-field">
                            <span class="hidden-xs"> Duplicate </span>
                        </a>
                    </div>
                </div>
            </fieldset>
        <?php
        }
        ?>

    </div>
    <input type="submit" value="Submit"/>
</form>
