(function ($) {

    $(document).ready(function () {

        $(".sync-ccb").bind('click', function (e) {
            var self = $(this),
                form_id = self.data('form-id'),
                entry_id = self.data('entry-id');

            $.blockUI({message: '<h5> Please wait while we are syncing the data with CCB...</h5>'});
            $.ajax({
                method: 'post',
                url: ajaxurl,
                data: {
                    action: 'sync_entry_with_ccb',
                    'form_id': form_id,
                    'entry_id': entry_id
                },
                dataType: 'json'
            }).done(function (response) {
                console.log(response);

                if (typeof response.api_sync != 'undefined') {
                    if (response.api_sync == true) {
                        self.html('CCB Sync Done').removeClass('sync-ccb').addClass('sync-ccb-complete').unbind('click');
                    }
                } else {
                    alert('Failed');
                }

            }).always(function (response) {
                $.unblockUI();
            });
        });

    });

})(jQuery);