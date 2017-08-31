<div class="ccb-gform">
    <div class="ccb-family-list-section">

    </div>

    <div class="ccb-group-list-section">

    </div>

    <div class="ccb-user-reg-form-section">
        <?php echo $this->output('gform'); ?>
    </div>

    <div class="ccb-user-reg-form-section-duplicate">
    </div>
</div>

<script type="text/javascript">

    var event_form_rendered = false;

    var user_profile_data = <?php echo json_encode($this->get('user_profile_data')); ?>;
    var user_group_data = <?php echo json_encode($this->get('user_group_data')); ?>;
    var login_authenticated = <?php echo json_encode($this->get('login_authenticated')); ?>;
    var all_ccb_form = <?php echo json_encode($this->get('all_ccb_form')) ?>;
    var gform_submitted_ccb_field = <?php echo json_encode($this->get('gform_submitted_ccb_field')) ?>;

    (function ($)
    {
        /**
         * gform post render event
         */
        jQuery(document).bind('gform_post_render', function (event, form_id, current_page)
        {
            if (all_ccb_form[form_id] == 'add_individual_to_event' && event_form_rendered == false)
            {
                event_form_rendered = true;

                var $gform_elem = $("#gform_" + form_id);

                if (typeof gfRepeater_getRepeaters != 'undefined')
                {
                    gfRepeater_start();

                    jQuery(window).trigger('gform_repeater_init_done');

                    var repeaterEnd = $gform_elem.find('.gf_repeater_add').parents('li');

                    repeaterEnd.hide();

                    if ($gform_elem.find('div.gform_page').length > 0)
                    {
                        repeaterEnd.appendTo($gform_elem.find(".gform_body > .gform_page:last > .gform_page_fields > ul"));
                    } else
                    {
                        repeaterEnd.appendTo($gform_elem.find(".gform_body > ul"));
                    }

                    fix_repeater_postion_after_validation_error();
                }

                if (typeof gfRepeater_repeaters != 'undefined')
                {
                    jQuery.each(gfRepeater_repeaters, function (key, repeater)
                    {
                        jQuery.each(repeater, function (key, value)
                        {
                            var repeaterId = key;
                            var repeater = gfRepeater_repeaters[form_id][repeaterId];
                            var repeatCount = repeater['settings']['start'];

                            gfRepeater_setRepeater(form_id, repeaterId, 1);
                            gfRepeater_updateRepeaterControls(form_id, repeaterId);
                            gfRepeater_updateDataElement(form_id, repeaterId);
                        });
                    });
                }

                /********** ccb field required validation ****************/
                $gform_elem.find('input[type="submit"]').click(function ()
                {
                    $gform_elem.find('[ccb-field]').each(function (i, v)
                    {

                        var required = $(v).parents('li').hasClass('gfield_contains_required');

                        if ((required == true))
                        {
                            if ($(v).is(':invalid'))
                            {
                                window["gf_submitting_3"] = false;
                                return false;
                            }
                        }
                    });
                });
                /********** ccb field required validation ****************/

                individual_family_profile();
                individual_group_profile();
            }

        });

        if (typeof fix_repeater_postion_after_validation_error == 'undefined')
        {
            function fix_repeater_postion_after_validation_error()
            {
                var register_type_prev_val = $("[ccb-field='event.register_user_type']").val();
                $("[ccb-field='event.register_user_type']").val('');
                $("[ccb-field='event.register_user_type']").trigger('change');
                $("[ccb-field='event.register_user_type']").val(register_type_prev_val);
                $("[ccb-field='event.register_user_type']").trigger('change');
            }
        }

        if (typeof individual_group_profile == 'undefined')
        {
            function individual_group_profile()
            {
                if (typeof user_group_data != 'undefined')
                {

                    if (typeof user_group_data.count != 'undefined' && user_group_data.group != 'undefined')
                    {

                        if (user_group_data.count > 0)
                        {
                            var groupElem = $(document.createElement('li')).addClass('ccb_group_lists').hide();
                            groupElem.append($(document.createElement('ul')).append($(document.createElement('label')).html('List of Group(s)')));

                            $(user_group_data.group).each(function (i, v)
                            {
                                if (v.status == 'leader')
                                {
                                    var groupElemText = $(document.createElement('a')).attr('href', 'javascript:void(0);').html(v.name);
                                    var groupRadio = $(document.createElement('input')).attr('type', 'radio').val(v.group_id);
                                    groupElem.find('ul').append($(document.createElement('li')).addClass('ccb_group_single').append(groupRadio).append(groupElemText));
                                }
                            });

                            $("[ccb-field='event.register_user_type']").parents('li').after(groupElem);

                            $('.ccb_group_single').find('input').bind('change', function (e)
                            {
                                var self = $(this);

                                get_group_members(self.val());
                            });

                            $('.ccb_group_single').find('a').bind('click', function (e)
                            {
                                var self = $(this);

                                var radio = self.siblings('input');
                                $('[ccb-field="individual.group.id"]').val(radio.val());
                                if (false == radio.prop("checked") || !$('.ccb_group_participant_lists').is(':visible'))
                                {
                                    radio.prop("checked", true);
                                    radio.trigger('change');
                                }
                            });
                        }
                    }
                }
            }
        }

        if (typeof individual_family_profile == 'undefined')
        {
            /**
             * family list populate if family members exists
             */
            function individual_family_profile()
            {
                if (typeof user_profile_data['individual.family_members'] != 'undefined')
                {
                    if (typeof user_profile_data['individual.family_members'].family_member != 'undefined')
                    {
                        if (user_profile_data['individual.family_members'].family_member.length > 0)
                        {

                            var familyElem = $(document.createElement('li')).addClass('ccb_family_members').hide();
                            familyElem.append($(document.createElement('ul')).append($(document.createElement('label')).html('List of family members')));

                            $(user_profile_data['individual.family_members'].family_member).each(function (i, v)
                            {
                                var familyMemElemText = $(document.createElement('a')).attr('href', 'javascript:void(0);').html(v.individual.value);
                                var familyMemElemCheckBox = $(document.createElement('input')).attr('type', 'checkbox').attr('individual_id', v.individual.id);
                                familyElem.find('ul').append($(document.createElement('li')).addClass('ccb_family_member').append(familyMemElemCheckBox).append(familyMemElemText));
                            });

                            $("[ccb-field='event.register_user_type']").parents('li').after(familyElem);

                            $('.ccb_family_member').find('input').bind('change', function (e)
                            {
                                var self = $(this);
                                add_existing_member_id(self.attr('individual_id'), self.prop("checked"));
                            });

                            $('.ccb_family_member').find('a').bind('click', function (e)
                            {
                                var self = $(this);
                                var checkBoxes = self.siblings('input');
                                checkBoxes.prop("checked", !checkBoxes.prop("checked"));
                                checkBoxes.trigger('change');
                            });

                        }
                    }
                }
            }
        }

        if (typeof inArray == 'undefined')
        {
            /**
             * php inarray similar javascript function
             */
            function inArray(needle, haystack)
            {
                var length = haystack.length;
                for (var i = 0; i < length; i++)
                {
                    if (haystack[i] == needle) return true;
                }
                return false;
            }
        }

        if (typeof removeA == 'undefined')
        {
            function removeA(arr)
            {
                var what, a = arguments, L = a.length, ax;
                while (L > 1 && arr.length)
                {
                    what = a[--L];
                    while ((ax = arr.indexOf(what)) !== -1)
                    {
                        arr.splice(ax, 1);
                    }
                }
                return arr;
            }
        }

        if (typeof get_individual_profile == 'undefined')
        {
            /**
             * get individual user profile details by id
             * @param event
             * @param form_id
             * @param individual_id
             */
            function get_individual_profile(event, form_id, individual_id)
            {
                var ajax_url = '<?php echo admin_url('admin-ajax.php') ?>';

                $.blockUI({message: '<h5> Please wait while we are fetching the member data...</h5>'});
                $.ajax({
                    method: 'post',
                    url: ajax_url,
                    data: {
                        action: 'get_individual_profile',
                        nonce: '<?php echo wp_create_nonce('ccb-gravity') ?>',
                        'individual_id': individual_id
                    },
                    dataType: 'json'
                }).done(function (response)
                {
                    if (response.api_error.length == 0)
                    {
                        autofill_func(event, form_id, response.user_profile)
                    }

                }).always(function (response)
                {
                    $.unblockUI();
                });
            }
        }

        if (typeof get_group_members == 'undefined')
        {
            function get_group_members(group_id)
            {
                var ajax_url = '<?php echo admin_url('admin-ajax.php') ?>';

                $.blockUI({message: '<h5> Please wait while we are fetching the member data...</h5>'});

                $.ajax({
                    method: 'post',
                    url: ajax_url,
                    data: {
                        action: 'get_group_participants',
                        nonce: '<?php echo wp_create_nonce('ccb-gravity') ?>',
                        'group_id': group_id
                    },
                    dataType: 'json'
                }).done(function (response)
                {

                    var participantElem = $('.ccb_group_participant_lists');

                    if (participantElem.length > 0)
                    {
                        participantElem.empty();
                        participantElem.hide();
                    }

                    if (response.error == false)
                    {

                        if (response.participants_data.count > 0)
                        {


                            if (typeof response.participants_data.participant != 'undefined')
                            {
                                var group_participant_data = response.participants_data.participant;
                                var elemCreated = false;

                                if (participantElem.length == 0)
                                {
                                    participantElem = $(document.createElement('li')).addClass('ccb_group_participant_lists').hide();
                                    elemCreated = true;
                                }

                                participantElem.append($(document.createElement('ul')).append($(document.createElement('label')).html('List of Participants(s)')));

                                $(group_participant_data).each(function (i, v)
                                {
                                    var ElemText = $(document.createElement('a')).attr('href', 'javascript:void(0);').html(v.name);
                                    var CheckBox = $(document.createElement('input')).attr('type', 'checkbox').val(v.id);
                                    participantElem.find('ul').append($(document.createElement('li')).addClass('ccb_group_participant_single').append(CheckBox).append(ElemText));
                                });

                                if (elemCreated)
                                {
                                    $(".ccb_group_lists").after(participantElem);
                                }

                                participantElem.show();

                                $('.ccb_group_participant_single').find('input').bind('change', function (e)
                                {
                                    var self = $(this);
                                    add_existing_member_id(self.val(), self.prop("checked"));
                                });

                                $('.ccb_group_participant_single').find('a').bind('click', function (e)
                                {
                                    var self = $(this);
                                    var CheckBox = self.siblings('input');
                                    CheckBox.prop("checked", !CheckBox.prop("checked"));
                                    CheckBox.trigger('change');
                                });
                            }

                        } else
                        {
                            alert('No group participants found!!!');
                        }

                    } else
                    {
                        if (typeof response.error_details.msg != 'undefined')
                        {
                            alert(response.error_details.msg);
                        } else
                        {
                            alert('Error fetching group participant(s) details');
                        }
                    }
                }).always(function (response)
                {
                    $.unblockUI();
                });
            }
        }

    })(jQuery);

</script>