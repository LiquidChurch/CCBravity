<?php
$form_type = $this->get('form_type');
?>
<div class="ccb-gform">
    <?php
    if ($form_type == 'login_form')
    {
        ?>
        <div class="ccb-login-form-section">
            <?php echo $this->output('gform'); ?>
        </div>
        <?php
    }
    else
    {
        ?>
        <div class="ccb-family-list-section">

        </div>

        <div class="ccb-group-list-section">

        </div>

        <div class="ccb-user-reg-form-section">
            <?php echo $this->output('gform'); ?>
        </div>

        <div class="ccb-user-reg-form-section-duplicate">
        </div>
        <?php
    }
    ?>
</div>

<script type="text/javascript">

    var login_form_rendered = false;
    var event_form_rendered = false;

    (function ($)
    {

        var user_profile_data = <?php echo json_encode($this->get('user_profile_data')); ?>;
        var user_group_data = <?php echo json_encode($this->get('user_group_data')); ?>;

        var login_authenticated = <?php echo json_encode($this->get('login_authenticated')); ?>;
        var all_ccb_form = <?php echo json_encode($this->get('all_ccb_form')) ?>;
        var gform_submitted_ccb_field = <?php echo json_encode($this->get('gform_submitted_ccb_field')) ?>;

        /**
         * autofill form fields based on userdata
         */
        var autofill_func = function (e, form_id, user_profile)
        {
            var $gform_elem = $("#gform_" + form_id);
            var $elem_with_ccb_fld = $gform_elem.find('[ccb-field]');
            var done = [];

            $elem_with_ccb_fld.each(function (i, v)
            {
                var attr = $(v).attr('ccb-field'), elemType = $(v).attr('type');

                if (typeof user_profile[attr] != 'undefined' && !inArray(attr, done))
                {
                    if (elemType == 'radio' || elemType == 'checkbox')
                    {
                        if ($(v).val() == user_profile[attr])
                        {
                            $(v).attr('checked', true);
                            done.push(attr);
                        }
                    } else
                    {
                        done.push(attr);
                        $(v).val(user_profile[attr]);
                    }
                }
            });
        };

        /**
         * gform post render event
         */
        jQuery(document).bind('gform_post_render', function (event, form_id, current_page)
        {
            if (all_ccb_form[form_id] == 'individual_profile_from_login_password' && login_form_rendered == false)
            {
                /*this is currently not required*/

//                login_form_rendered = true;
//                var $formElem = $('#gform_' + form_id);
//
//                var title = $formElem.find('.gform_title').html();
//                var expand_title = title + ' <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAACXklEQVRYR+2WP0xTQRzHv6/vRXRoXShOdTOsRidJE6BKQpEAooiDREzbIKU6dpHXvj+4dNRSbNpGDA4iikCQkqAFkgYnjStxs9MDFh+DYvr6zF3qUBPqC1zSmPSWy13u7vf5fX/3+91xqHHjamwfdYC6AnUF/gsFBEmSlgSB95qmtarBcUCxaGQlSeoBUKy2y4oCdlVV9dHgmDXr5VVTiUmIougAsH9cAKcsyztjoftIpZ9BEHjg0PplEs8R8N/FZPwJotFoE4Dd4wI0ybKs+fwBvJp7A0EQwBGNAYSCI7SPJ5K0N00CUMTNgevIpFME4AyAHSYAt4eGsZLNguf/KGBidMRPz55KpsuqmDAMA11eL17MTLMDUBRFuzF4C7ncBsxSCSVyG03gQegeBXgcf0qjYuM4cDYbPJ42vJ59iUgkwkYBAtDb14/81kcEy14fJmsimYa75RIWF+bZAlzt7sGnz18Q8N2pmg2pzHNcvHAe75aX2AGoiqJ1dHZhe/srjJJBw0BKgm94iMJkpmdoXhD5eRuP5uZzWFtdgcgqBKqqaJ4rnSgUChXeDw700/Hs3HzFvMvlQu79KkSR0R0gAG3tHdjd26swdK23m47fLi5XzDsbG7GxvsYSQNXcre3Q9f1/fqFIaBwOO/Kb66QSssmCRxMTmrv1Mg5+HdD0q9o4oOFEA/KbH/BwfJwJgDMcDi+cdjhaaP5baKQefNf1rVgs1seiFNsBnAXgBEDKoJVmlA1/Y/EYCQBOAThZ5RX6G4pI9RPADxbPsRWPj7zGyn/gyIdb2VgHqCtQcwV+A2a71yHvLjbNAAAAAElFTkSuQmCC" />';
//                var collapse_title = title + ' <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAACIUlEQVRYR+2WPUxTURTHf6/vRXRoXShOdTOsRidJE6BKQtEg8XvQqKGNEXTtAq8f7+HSVcGYlqjRwe8gUUqCFkganDSuxM1OD1gsg2L6+swluNRaX+AmjUnPfO89v/s/5/zvVWhwKA3OTxOgqUBTgf9CAS2ZTE5rmhp2HHeuoShQLtu5ZDLZD5Tr7XKjgNc0zdL1oWF32bdW3Z0YR9d1H7C+UwB/KpVaGb5xk0z2Ppqmwl/9yxE3Jxq5yvid2yQSiTZgdacAbalUyhqMRHn2/CWapqEIjWuE4wiAMufOnmYymxEA+4AVKQAXL11hJpdDVX8rUN0QAsrBtm36wmEeP3ogD8AwDOvM+Qvk8ws4lQoV0Y018nsUBcXjIRTq4sXTJ8TjcTkKCICTA6coLH1g6FqkbjNO3MsS7DjC66lXcgGOn+jn46fPRAcv1wXITD7k8KGDvH0zLQ/ANAyrp7eP5eUv2BV7swy1OkDIr3pU2tsPMDc7gy6rBKZpWKFjvRSLRVdeEAgEyL+bRdcl9YAA6OruYXVtzRWAv7WVhfk5mQCmFezsplRa/+cXSpTG5/NSWJwXTihnCm6NjVnBzqNs/Nz4c/yqNVGgZVcLhcX3jIyOSgHwx2Kxqb0+X8fm/LsI4QffSqWldDo9IMOKvcB+wA8IG3QT9lbirzIeIw3YA+yu8wpVQwmpfgDfZTzHbm687TVu/gPbPtzNxiZAU4GGK/ALa1TFIW+JaFcAAAAASUVORK5CYII=" />';
//
//                $formElem.find('.gform_title').html(collapse_title);
//                $formElem.find(".gform_body").hide();
//                $formElem.find(".gform_footer").hide();
//
//                $formElem.find('.gform_title').click(function (e)
//                {
//                    $formElem.find(".gform_body").toggle("slow", function ()
//                    {
//
//                        if ($formElem.find(".gform_body").is(':visible'))
//                        {
//                            $formElem.find('.gform_title').html(expand_title);
//                        } else
//                        {
//                            $formElem.find('.gform_title').html(collapse_title);
//                        }
//
//                    });
//
//                    $formElem.find(".gform_footer").toggle("slow", function ()
//                    {
//                    });
//                });
            }
            else if (all_ccb_form[form_id] == 'add_individual_to_event' && event_form_rendered == false)
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

                if ($gform_elem.find('div.gform_page').length > 0)
                {
                    if (current_page == 1)
                    {

                        var $gform_page_elem = $("#gform_page_" + form_id + "_" + current_page);

                        if (typeof gform_submitted_ccb_field["individual.first_name"] != 'undefined')
                        {
                            if (typeof gform_submitted_ccb_field["individual.first_name"][0] != 'undefined')
                                $($gform_elem).find('[ccb-field="individual.first_name"]:first').val(gform_submitted_ccb_field["individual.first_name"][0]);
                        }

                        if (typeof gform_submitted_ccb_field["individual.last_name"] != 'undefined')
                        {
                            if (typeof gform_submitted_ccb_field["individual.last_name"][0] != 'undefined')
                                $($gform_elem).find('[ccb-field="individual.last_name"]:first').val(gform_submitted_ccb_field['individual.last_name'][0]);
                        }

                        if (typeof gform_submitted_ccb_field["individual.email"] != 'undefined')
                        {
                            if (typeof gform_submitted_ccb_field["individual.email"][0] != 'undefined')
                                $($gform_elem).find('[ccb-field="individual.email"]:first').val(gform_submitted_ccb_field['individual.email'][0]);
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

                        if (login_authenticated == true)
                        {
                            autofill_func({}, form_id, user_profile_data);
                        }

                        $('[ccb-field="individual.age"]:first').parents('li').remove();

                        show_event_for_selected_location($gform_elem);

                        $('[ccb-field="event.ids"]').bind('change', function (e)
                        {
                            show_event_for_selected_location($gform_elem);
                        });

                        $('[ccb-field="event.id"]').bind('change', function (e)
                        {
                            show_campus_after_single_event_selection($gform_elem);
                        });

                    } else if (current_page == 2)
                    {

                        var $gform_page2_elem = $("#gform_page_" + form_id + "_" + current_page);

                        $gform_elem.find('input[type="submit"]').click(function ()
                        {
                            $gform_page2_elem.find('[ccb-field]').each(function (i, v)
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

                        $gform_page2_elem.bind('DOMSubtreeModified', function (e)
                        {

                            $gform_page2_elem.find('[ccb-field]').each(function (i, v)
                            {

                                var requiredCCBField = ['individual.first_name', 'individual.last_name', 'individual.email'];

                                if (inArray($(v).attr('ccb-field'), requiredCCBField))
                                {
                                    $(v).prop('required', true);
                                }
                            });
                        });

                        $($gform_elem).find('[ccb-field="individual.first_name"]:first').val(gform_submitted_ccb_field["individual.first_name"][0]);
                        $($gform_elem).find('[ccb-field="individual.last_name"]:first').val(gform_submitted_ccb_field['individual.last_name'][0]);
                        $($gform_elem).find('[ccb-field="individual.email"]:first').val(gform_submitted_ccb_field['individual.email'][0]);

                        individual_family_profile();
                        individual_group_profile();

                        /**
                         * when user not logged in, omit some register as optiona
                         */
                        $("[ccb-field='event.register_user_type'] option").each(function ()
                        {
                            var option_val = $(this).val();

                            if (inArray(option_val, ['me', 'liquid_group']) && (login_authenticated == false))
                            {
                                $(this).remove();
                            }
                        });

                        /**
                         * events after changing register as dropdown
                         */
                        $("[ccb-field='event.register_user_type']").change(function (e)
                        {
                            var $form = $(this).parents('form')[0];
                            var removeAddedIndv = false;
                            repeaterEnd.hide();

                            reset_autofill_fields($form);

                            if ('' != $(this).val())
                            {
                                repeaterEnd.show();

                                if (!inArray($(this).val(), ['family', 'liquid_group']) || login_authenticated == false)
                                {
                                    var repeatCount = gfRepeater_repeaters[form_id][1]['data']['repeatCount'];
                                    if (repeatCount == '1')
                                    {
                                        $(".gf_repeater_add").trigger('click');
                                    }
                                } else
                                {
                                    removeAddedIndv = true;
                                }

                            } else
                            {

                                removeAddedIndv = true;
                            }

                            if (removeAddedIndv == true)
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

                            if ('me' == $(this).val())
                            {
                                autofill_func(e, form_id, user_profile_data);
                            } else if ('family' == $(this).val())
                            {
                                $('[ccb-field="individual.family.id"]').val(user_profile_data['individual.family.id']);
                                $(".ccb_family_members").show();
                            } else if ('liquid_group' == $(this).val())
                            {
                                if ($(".ccb_group_single").length > 0)
                                {
                                    $(".ccb_group_lists").show();
                                }
                            }

                        });
                    }
                }
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

        if (typeof show_event_for_selected_location == 'undefined')
        {
            function show_event_for_selected_location($gform_elem)
            {

                var $location = $gform_elem.find('[ccb-field="event.ids"]:checked');
                var $single_locations = $gform_elem.find('[ccb-field="event.id"]');

                var locationIds = [];
                if ($location.length > 0)
                {

                    locationIds = $location.val().split('|');
                    $single_locations.parents('li.gfield').show('slow');

                    $single_locations.prop('checked', false);
                } else
                {

                    $single_locations.parents('li.gfield').hide('slow');
                }

                show_campus_after_single_event_selection($gform_elem);

                $single_locations.each(function (i, v)
                {
                    var $el = $(v), $elVal = $el.val(), $elLi = $('.g' + $el.attr('id'));
                    if (!inArray($elVal, locationIds))
                    {
                        $elLi.hide();
                    } else
                    {
                        $elLi.show();
                    }
                });
            }
        }

        if (typeof show_campus_after_single_event_selection == 'undefined')
        {
            function show_campus_after_single_event_selection($gform_elem)
            {
                var $campus_locations = $gform_elem.find('[ccb-field="campus.id"]');
                var $single_location_checked = $gform_elem.find('[ccb-field="event.id"]:checked');

                if ($single_location_checked.length > 0)
                {

                    $campus_locations.parents('li.gfield').show('slow');
                } else
                {

                    $campus_locations.parents('li.gfield').hide('slow');
                    $campus_locations.prop('checked', false);
                }

            }
        }

        if (typeof reset_autofill_fields == 'undefined')
        {
            /**
             * reset fields when register as changed
             * @param $form
             */
            function reset_autofill_fields($form)
            {

                $(".ccb_family_members").hide();
                $(".ccb_group_lists").hide();
                $('.ccb_group_participant_lists').hide();

                $($form).find('[ccb-field="individual.member.ids"]').val('');
                $($form).find('[ccb-field="individual.family.id"]').val('');
                $($form).find('[ccb-field="individual.group.id"]').val('');

                $('.ccb_group_single').find('a').siblings('input').removeAttr('checked');
                $('.ccb_family_member').find('a').siblings('input').removeAttr('checked');

//            $($form).find('[ccb-field="individual.id"]').val('');
//            $($form).find('[ccb-field="individual.first_name"]').val('');
//            $($form).find('[ccb-field="individual.last_name"]').val('');
//            $($form).find('[ccb-field="individual.email"]').val('');
//            $($form).find('[ccb-field="individual.family.position"]').val('');
//            $($form).find('[ccb-field="individual.phone"]').val('');
//            $($form).find('[ccb-field="individual.address.line_1"]').val('');
//            $($form).find('[ccb-field="individual.address.line_2"]').val('');
//            $($form).find('[ccb-field="individual.address.city"]').val('');
//            $($form).find('[ccb-field="individual.address.state"]').val('');
//            $($form).find('[ccb-field="individual.address.zip"]').val('');

//            $($form).find('[ccb-field="campus.id"]').removeAttr('checked');
//            $($form).find('[ccb-field="campus.id"]:last').attr('checked', true);
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

        if (typeof add_existing_member_id == 'undefined')
        {
            function add_existing_member_id(mem_id, action)
            {
                if ($("[ccb-field='individual.member.ids']").length > 0)
                {
                    var $elem = $("[ccb-field='individual.member.ids']");
                    var prev_val = $elem.val();
                    var new_val = mem_id;
                    if (prev_val != '')
                    {
                        if (action == true)
                        {
                            new_val = prev_val + '|' + new_val;
                        } else
                        {
                            var explode = prev_val.split("|");
                            var mod_explode = removeA(explode, new_val);
                            new_val = mod_explode.join('|');
                        }
                    }
                    $elem.val(new_val);
                }
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
             * get individual user profile details ny id
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