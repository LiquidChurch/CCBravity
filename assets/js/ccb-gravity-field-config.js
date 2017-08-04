(function ($) {
    /**
     * not empty warning function
     *
     * @param e
     * @returns {boolean}
     */
    var removeCheckVal = function (e) {
        e.preventDefault();

        var inputVal = $(this).parents('.ccb-field-model').find('input').val();
        var confirmVal = true;

        if (inputVal != '') {
            confirmVal = confirm('Are you sure want to delete this value ?');
        }

        if (confirmVal == true) {
            return true;
        }

        return false;
    };

    /**
     * form element duplicate event
     */
    $('.ccb-field-model').duplicateElement({
        "class_remove": ".remove-this-field",
        "class_create": ".create-new-field",
        onCreate: function (el, btn, e) {
            el.find('input').val('');
            el.find('.remove-this-field').bind('click', removeCheckVal);
        },
        onRemove: function (el, btn, e) {
        }
    });

    /**
     * show warning when deleting not empty field
     */
    $('.remove-this-field').click(removeCheckVal);

    /*$("#ccb-gravity-field-config-form").on('submit', function(e){
     e.preventDefault();
     alert('form submitted');
     });*/

})(jQuery);