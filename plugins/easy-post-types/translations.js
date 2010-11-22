    function ct_check_actions() {
        return true;
        //return confirm(contexflow.__('confmsg'));
    }

    function custom_type_update_admin(params) {
        var data = {
            action: 'update_admin',
            content_type: params['name'],
            'public': jQuery('#public').attr('checked'),
            show_ui: jQuery('#show_ui').attr('checked'),
            fields_to_show_in_table: jQuery('#fields_to_show_in_table').val(),
            admin_menu_position: jQuery('#admin_menu_position').val(),
            admin_menu_icon: jQuery('#admin_menu_icon').val()
            };

        jQuery.post(params['url'], data, function(response) {
            alert(contexflow.__('aupd'));
            });
    }

    function custom_type_update_permissions(params) {
        var data = {
            action: 'update_permissions',
            content_type: params['name']
            };
        contexflow.permissions(data);
        jQuery.post(params['url'], data, function(response) {
            alert(contexflow.__('pupd'));
            });
    }


    function custom_type_update_visibility(params) {
        var data = {
            action: 'update_visibility',
            content_type: params['name'],
            query_var: jQuery('#query-var').attr('checked'),
            exclude_from_search: jQuery('#include-in-search').attr('checked')
            };
        jQuery.post(params['url'], data, function(response) {
            alert(contexflow.__('vupd'));
            });
    }

    function custom_type_update_labels(params) {
        var data = {
            action: 'update_labels',
            content_type: params['name']
            };
        contexflow.labels_list(data);
        jQuery.post(params['url'], data, function(response) {
            alert(contexflow.__('lupd'));
            });
    }

    function custom_type_update_category(params) {
        if (confirm(contexflow.__('updmsg'))) {
            var data = {
                action: 'update_category',
                content_type: params['type'],
                category: params['item'].value,
                checked: params['item'].checked
                };

            jQuery.post(params['url'], data, function(response) {
                jQuery('#category-list').html(response);
            });
        }
        else
            item.checked=!item.checked;
    }

    function custom_type_edit_category(params) {
            var data = {
                action: 'edit_category',
                content_type: params['name'],
                category: params['category']
                };
        var msg={};
        msg[contexflow.__('cmsg')]=function() {
                           jQuery(this).dialog('close');
        }

        jQuery.post(params['url'], data, function(response) {
            jQuery("#addcategory_dialog").dialog('destroy');
            jQuery('#addcategory_dialog').html(response);
            jQuery("#addcategory_dialog").dialog({
                resizable: false,
                modal: true,
                buttons: msg
                });
            });
    }


    function custom_type_edit_field(params) {
    var data = {
        action: 'edit_field',
	content_type: params['name'],
        field_name: params['field_name']
        };
    var msg={};
    msg[contexflow.__('cmsg')]=function() {
                       jQuery(this).dialog('close');
    }

    jQuery.post(params['url'], data, function(response) {
        jQuery('#addfield_dialog').html(response);
        jQuery("#addfield_dialog").dialog({
            resizable: false,
            modal: true,
            buttons: msg
            });
        });
    }


    function custom_type_update_field(params) {
        var data = {
            action: 'update_field',
            content_type: params['name'],
            order: jQuery('#fields_order').val()
        };

        jQuery.post(params['url'], data, function(response) {
            jQuery('#fields_content').html(response);
            alert(contexflow.__('fupd'));
        });
    }


    function custom_type_add_field(params) {
    var data = {
        action: 'edit_field',
	content_type: params['name']
        };
    var msg={};
    msg[contexflow.__('cmsg')]=function() {
                       jQuery(this).dialog('close');
    }

    jQuery.post(params['url'], data, function(response) {
        jQuery('#addfield_dialog').html(response);
        jQuery("#addfield_dialog").dialog({
            resizable: false,
            modal: true,
            buttons: msg
            });
        });
    }

    function custom_type_add_category(params) {
    var data = {
        action: 'edit_category',
	content_type: params['name']
        };

    var msg={};
    msg[contexflow.__('cmsg')]=function() {
                       jQuery(this).dialog('close');
    }

    jQuery.post(params['url'], data, function(response) {
        jQuery("#addcategory_dialog").dialog('destroy');
        jQuery('#addcategory_dialog').html(response);
        jQuery("#addcategory_dialog").dialog({
            resizable: false,
            modal: true,
            buttons: msg
            });
        });
    }


    function custom_type_delete_content(params) {
    var data = {
        action: 'delete_content',
	content_type: params['name'],
        passthru: params['passthru']
        };

    jQuery.post(params['url'], data, function(response) {
        jQuery('#delete_dialog').html(response);
        if (data['passthru']==1) {
            reload_page_content(params['url'])
            jQuery("#menu-post a[href*='"+params['name']+"']").parent("li#menu-post").remove();
            return;
        }
        var msg={};
        msg[contexflow.__('dmsg')]=function() {
                            jQuery(this).dialog('close');
                            params['passthru']=1;
                            custom_type_delete_content(params);
        }
        msg[contexflow.__('mmsg')]=function() {
                            jQuery(this).dialog('close');
                            custom_type_move_content(params);
        }
        msg[contexflow.__('cmsg')]=function() {
                            jQuery(this).dialog('close');
        }

        jQuery("#delete_dialog").dialog({
            resizable: false,
            modal: true,
            buttons: msg
            });
        });
}
