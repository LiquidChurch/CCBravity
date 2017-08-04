(function ($) {
    if(typeof CCB != 'undefined') {

        if(CCB.page == 'field_settings') {
            /**
             * form page
             */
            $.each(fieldSettings, function (i, v) {
                fieldSettings[i] += ', .ccb_field_settings';
            });

            $(document).ready(function () {
                $(".ccb_field_value").select2({dropdownAutoWidth: true});
            });

            $('.ccb_field_value').on("select2:select", function (e) {
                SetFieldProperty('ccbField', $(this).val());
            });

            $('.ccb_field_report').on("change", function (e) {
                var self = $(this);
                SetFieldProperty('ccbFieldReport', self.prop('checked'));
            });

            //binding to the load field settings event to initialize the CCB Field
            $(document).bind('gform_load_field_settings', function (event, field, form) {
                $(".ccb_field_value").val(field.ccbField).trigger("change");
                $(".ccb_field_report").prop('checked', field.ccbFieldReport == true);
            });
        }


        if(CCB.page == 'form_settings') {
            /**
             * settings page
             */
            $("#ccb_service_value").select2({dropdownAutoWidth: true});
            if (typeof CCB.ccb_api_settings != 'undefined') {
                $("#ccb_service_value").val(CCB.ccb_api_settings).trigger("change");
            }
        }

    }
})(jQuery);