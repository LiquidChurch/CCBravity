<form id="ccb-gravity-service-config-form" method="post" action="">
    <?php
    wp_nonce_field('ccb-gravity-service-config-form');
    ?>
    <input type="hidden" name="action" value="ccb-gravity-service-config-form" />

    <div class="wrap ccb-gravity-field-config-wrap">

        <?php
        $serviceNames = $this->get('serviceNames');
        if(!empty($serviceNames)) {
            foreach ($serviceNames as $index => $serviceName) {
                ?>
                <fieldset class="ccb-field-model">
                    <div class="form-group">
                        <div class="">
                            <label for="">CCB Service Name: </label>

                            <input name="serviceName[]" type="text" placeholder="Enter service name"
                                   value="<?php echo $serviceName ?>" class="field-name" required>

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
        } else {
            ?>
            <fieldset class="ccb-field-model">
                <div class="form-group">
                    <div class="">
                        <label for="">CCB Service Name: </label>

                        <input name="serviceName[]" type="text" placeholder="Enter service name" class="field-name" required>

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
