
function closeDiv(divId) {
    jQuery('#'+divId).empty().addClass('hidden');
}

function load_quick_edit(url, key) {
    var data = {
        action: 'load_quick_edit',
        content_type: key
        };
    jQuery.post(url, data, function(response) {
        jQuery('#form-quick-edit-'+key).html(response).parent().eq(0).removeClass('hidden');
        contexflow.createAutoFillFields(jQuery('#form-quick-edit-'+key));
    });
}


function reload_page_content(url) {
    var data = {
        action: 'reload_page_content'
        };
    jQuery.post(url, data, function(response) {
        jQuery('#page_content').html(response);
    });
}

function reloadContentType(url, id) {
    var type = jQuery('#'+id).val();
    var data = {
        action: 'reload_content',
	content_type: type
        };
    jQuery.post(url, data, function(response) {
        jQuery('#fill_options').html(response);
        jQuery("ul.tabs").tabs("div.panes > div");
    });
    
}
function custom_type_import(url) {
    var data = {
        action: 'import_content',
        text: jQuery('#export_content').val()
        };

    jQuery.post(url, data, function(response) {
        alert(response);
    });

}


function custom_type_export(url, id) {
    var type = jQuery('#'+id).val();
    var data = {
        action: 'export_content',
	content_type: type
        };
    jQuery.post(url, data, function(response) {
        jQuery('#export_content').val(response);
    });

}

function reloadFieldType(url, id, contentType) {
    var type = jQuery('#'+id).val();
    var data = {
        action: 'reload_fieldtype',
	ct_name: type,
        content_type: contentType
        };
    jQuery.post(url, data, function(response) {
        jQuery('#general_fieldtype').html(response);
    });

}

function custom_type_move_content(params) {
    var data = {
        action: 'move_content',
	content_type: params['name']
        };

    jQuery.post(params['url'], data, function(response) {
       reload_page_content(params['url']);
    });
}


function custom_type_delete_field(url, type, name, msg) {
    var data = {
        action: 'delete_field',
	content_type: type,
        name: name
    };
    if (confirm(msg)) {
    jQuery.post(url, data, function(response) {
        jQuery('#fields-list').html(response);
    });
    }
}

function custom_type_edit_field(url, data) {
   
    jQuery.post(url, data, function(response) {
        jQuery('#general_fieldtype').html(response);
    });
}

function ct_check_form_edit(form) {
    var label = jQuery('#form_edit input[name=content_type]').val();
    var name = jQuery('#form_edit input[name=label]').val();
    var sing = jQuery('#form_edit input[name=singular_label]').val();

    if (label=='' || name=='') {
        jQuery('#form_edit input[name=content_type]').addClass('error');
        jQuery('#form_edit input[name=label]').addClass('error');
        return false;
    }
    
    return true;
}

function ct_check_submit(form, message_change, message_check) {
    /*var label = jQuery('#form_create input[name=content_type]').val();
    var name = jQuery('#form_create input[name=label]').val();
    var sing = jQuery('#form_create input[name=singular_label]').val();

    if (label=='' && name=='') {
        jQuery('#form_create input[name=content_type]').parents('.form-field:first').addClass('error');
        jQuery('#form_create input[name=label]').parents('.form-field:first').addClass('error');
        return false;
    }

    if (name=='') {
        jQuery('#form_create input[name=label]').parents('.form-field:first').addClass('error');
        return false;
    }

    if (label=='') {
        var newname = name.replace(/^\s+|\s+$/g,"");
        newname = newname.toLowerCase();
        newname = newname.replace(/ /g, '_');
        if (/^[a-zA-Z_][a-zA-Z0-9_]+$/i.test(newname)) {
            jQuery('#form_create input[name=content_type]').val(newname);
            if (sing=='')
                jQuery('#form_create input[name=singular_label]').val(name);
            message_change = message_change.replace(/%VAR%/, newname);
            if (confirm(message_change)) {
                jQuery('#form_create input[name=singular_label]').removeAttr('disabled');
                return true;
            }
            else {
                jQuery('#form_create input[name=content_type]').val('');
                return false;
            }
        }
        else {
            alert(message_check);
            return false;
        }

    }

    jQuery('#form_create input[name=singular_label]').removeAttr('disabled');
*/
    return true;
}





(function($, window, document, undefined){ 


contexflow = window.contexflow || {};


// Options for general settings
contexflow.options = {};

// Options for the jQuery UI
contexflow.options.dialog = {};

contexflow.options.dialog.large = {
  width: 550,
  height: 450,
  resizable: false,
  modal: true
};


/**
 * Setup the meta toggle boxes
 *
 * This setups up the WordPress toggle boxes that seen on the post edit page.
 */
contexflow.setupToggleBoxes = function(){
  // close postboxes that should be closed
  $('.if-js-closed').removeClass('if-js-closed').addClass('closed');
  // postboxes setup
  postboxes.add_postbox_toggles();
};




/**
 * Handle the child checkboxes for the features.
 * 
 * This makes sure the state of the parent and child of features stay valid. This means if a child
 * checkbox is checked then the parent must be checked, and if the parent is unchecked then the child
 * is unchecked.
 */
contexflow.setupChildParentFeaturesRelationship = function(){

  $('ul.newsig--features-list li:has(ul) > :checkbox').live( 'change', function(){
    if(!this.checked) {
      $(this).parent().find('ul :checkbox').attr('checked', false);
    }
  });
  
  $('ul.newsig--features-list li ul :checkbox').live( 'change', function(){
    if(this.checked) {
      $(this).parentsUntil('ul.newsig--features-list').children(':checkbox').attr('checked', true);
    }
  });  
}




/**
 * Handles the disabled/enabled sub-area
 *
 * This will disable/enable a sub-area based on the state of a check-box.
 *
 * Example: 
 *
 *  <input type="checkbox" class="toggle-sub-area" rel="#my-sub-area"/>
 *  <div id="my-sub-area">
 *      The sub area
 *  </div>
 */
contexflow.setupCheckboxToSubArea = function(){
  $(':checkbox.toggle-sub-area').each(function(){
    var $this = $(this);
    var subArea = $($this.attr('rel')).eq(0);
    subArea.append('<div class="sub-area-disable-screen"> </div>');
    
    subArea.toggleClass('sub-area-disabled', !this.checked);
    
    
    $this.change(function(){
      subArea.toggleClass('sub-area-disabled', !this.checked);
    });
  });
}


/**
 * Table Column Rearrange Class
 *
 * This is a UI class for rearranging the columns to show in a table.
 */
contexflow.TableColumnRearrange = function(element){
  // the elements
  this.element = $(element);
  this.input = this.element.find(':input.table-column-rearrange--input').eq(0);
  this.unused = this.element.find('.table-column-rearrange--unused').eq(0);
  this.used = this.element.find('.table-column-rearrange--used').eq(0);
  
  // columns
  this.columns = {};
  var self = this;
  
  var keys = this.element.find('.table-column-rearrange--col-key');
  var names = this.element.find('.table-column-rearrange--col-name');
  for(var i=0; i<keys.length; ++i){
    this.columns[keys.eq(i).text()] = names.eq(i).html();
  }
  
  this.selected_columns = this.input.val().split(',');
  
  // create the list of fields
  // used fields
  var unused_fields = $.extend({}, this.columns);
  if(this.selected_columns[0]){
    for(var i=0; i<this.selected_columns.length; ++i){
      $('<li>'+this.columns[this.selected_columns[i]]+'</li>')
        .data('key', this.selected_columns[i])
        .appendTo(this.used);
      delete unused_fields[this.selected_columns[i]];
    }
  }
 
  
  for(var key in unused_fields){
    $('<li>'+this.columns[key]+'</li>')
      .data('key', key)
      .appendTo(this.unused);
  }
  
  // drag and drop
  var connect_class = contexflow.TableColumnRearrange.connectClass();
  this.used
    .addClass(connect_class)
    .sortable({
      connectWith: '.'+connect_class,
      update: $.proxy(this, 'change'),
      cancel: ':radio'
    })
    .disableSelection();
  this.unused
    .addClass(connect_class)
    .sortable({
      connectWith: '.'+connect_class,
      cancel: ':radio'
    })
    .disableSelection();

};

// uid_counter used for TableColumnRearrange objects
contexflow.TableColumnRearrange.uid_counter = 0;

// Static method to create a class for the connectClass for the jQuery UI 
contexflow.TableColumnRearrange.connectClass = function(){
  var id = ++contexflow.TableColumnRearrange.uid_counter;
  return 'table-column-rearrange--connected-sortable-'+id;
};

contexflow.TableColumnRearrange.prototype = {
  
  /**
   * Change
   * 
   * The callback for when the columns are changed by drag-and-drop
   */
  change: function(event, ui){
    var sc = [];
    var cols = this.used.children();
    for(var i=0; i<cols.length; ++i){
      var c = cols.eq(i);
      sc.push(c.data('key'));
    } 
    
    this.selected_columns = sc;
    this.input.val(this.selected_columns.join(','));
  }

}


/**
 * Setup Table Column Rearrange
 *
 * Call this to setup all the table column rearrange.
 */
contexflow.setupTableColumnRearrange = function(){
  
  $('.table-column-rearrange').each(function(){
    new contexflow.TableColumnRearrange(this);
  });
};




/**
 * Throbber Class
 *
 * A simple object to control the throbber
 */
contexflow.Throbber = function(throbberElement) {

};

contexflow.Throbber.prototype = {
  show: function(){
  
  },
  
  hide: function(){
  
  }
};





/**
 * Rearrange Order Class
 *
 * This is a UI class for reordering the items (such as rows in a table) and save it.
 */
contexflow.RearrangeTableRowOrder = function(element){
  this.element = $(element);
  this.input = this.element.find(':input.rearrange-table-row-order--input').eq(0);
  this.tbody = this.element.find('tbody.rearrange-table-row-order--items').eq(0);
  this.specialRows = this.element.find('.rearrange-table-row-order--special-rows > tr').eq(0);
  
  this.numberOfColumns = this.tbody.parent().find('thead:first th').size();
  
  this.tbody.sortable({
    handle: '.rearrange-table-row-order--handle',
    helper: $.proxy(this, 'helper'),
    update: $.proxy(this, 'change'),
    placeholder: {
      element: $.proxy(this, 'createPlaceholder'),
      update: $.proxy(this, 'updatePlaceholder')
    },
    start: $.proxy(this, 'start')
  });
  
  this.specialRows.draggable({
    handle: '.rearrange-table-row-order--handle',
    connectToSortable: this.tbody,
    helper: $.proxy(this, 'helper'),
    revert: 'invalid'
  });
  
  $('a.rearrange-table-row-order--remove', this.tbody.get(0)).live('click', $.proxy(this, 'removeRowHandler') );
  
  this.tbody.children().data('RearrangeTableRowOrder', this);
  this.element.data('RearrangeTableRowOrder', this);
};


contexflow.RearrangeTableRowOrder.prototype = {
  
  /**
   *
   */
  change: function(){
    var keys = [];
    var c = this.tbody.children();
    for(var i=0; i<c.length; ++i){
      keys.push(c.eq(i).attr('data-item'));
    }
    
    keys = keys.join(',');
    this.input.val(keys);
  },
  
  update: function(){
    this.change();
  },
  
  helper: function(event, element) {
    element = element || $(event.currentTarget);
    var h = element.clone();
    h.find('td:not(.column-rearrange-table-row-order, .column-name)').hide();
    h.find('.row-actions').hide();
    h.addClass('rearrange-table-row-order--hover');
    return h;
  },
  
  createPlaceholder: function(container, el){
    return $('<tr class="ui-sortable-placeholder"><td colspan="'+this.numberOfColumns+'"> </td></tr>');
  },
  
  updatePlaceholder: function(container, placeholder){
    placeholder.height(container.currentItem.height());    
  },
  
  
  start: function(event, ui){
  },
  
  removeRowHandler: function(event) {
    $(event.target).parents('tr').eq(0).remove();
    this.change();
    event.stopPropagation();
    return false;
  },
  
  removeRow: function(rowElement) {
    rowElement.remove();
    this.change();
  }
  
};


/**
 * Setup Order Rearrange
 *
 * Call this to setup all the order rearrange.
 */
contexflow.setupRearrangeTableRowOrder = function(context){
  context = context || document;
  $('.rearrange-table-row-order', context).each(function(){
    new contexflow.RearrangeTableRowOrder(this);
  });
}


/**
 * Object for the autofilled
 *
 * @param element - the container element
 */
contexflow.AutoFill = function (element) {
  // the HTML elements
  this.element = jQuery(element).eq(0);
  this.input = this.element.find(':text').eq(0);
  this.inputHidden = $('<input type="hidden" />').insertAfter(this.input);
  this.edit_button = this.element.find('a.edit-auto-fill').eq(0);
  this.auto_button = this.element.find('a.auto-auto-fill').eq(0);
  
  // flag
  this.is_auto = this.element.attr('data-auto') != 'false';
  
  // the input element that is the source
  this.source = jQuery(this.element.attr('data-source')).eq(0);
  if(this.source.size()==0) {
    this.abort();
    return;
  }
  
  // the type of filter to apply to the value from the source
  this.filter_type = this.element.attr('data-filter');
  if(!this.filter_type || !(this.filter_type in contexflow.AutoFill.filters)){
    this.abort();
    return;
  }
  
  // extra settings for the filter, if any
  this.filter_extra = this.element.attr('data-filter-extra');
  
  // set is_auto to false if the input has a value that does not the filtered results
  if(this.is_auto && this.input.val()){
    this.is_auto = this.input.val() == contexflow.AutoFill.filters[this.filter_type](this.source.val(), this.filter_extra);
  }
  
  
  if(this.is_auto) {
    this.auto();
  } else {
    this.edit();
  }
  
  this.source.focus(jQuery.proxy(this, 'onWatch'));
  this.source.blur(jQuery.proxy(this, 'offWatch'));
  
  this.edit_button.click(jQuery.proxy(this, 'editEvent'));
  this.auto_button.click(jQuery.proxy(this, 'autoEvent'));
  
  this.element.data('contexflow_AutoFill', this);
};

contexflow.AutoFill.prototype = {
  /**
   * Aborts the creation of the AutoFill
   */
  abort: function(){
      this.element.find('a.button').hide();
    },
  /**
   * Switches to auto mode.
   */
  auto: function(){
    this.is_auto = true;
    this.edit_button.show();
    this.auto_button.hide();
    this.input.addClass('auto-fill').attr('disabled', true);
    this.inputHidden.attr('name', this.input.attr('name'));
    this.watch();
  },
  
  /**
   * Switches to edit mode.
   */
  edit: function(){
    this.is_auto = false;
    this.edit_button.hide();
    this.auto_button.show();
    this.input.removeClass('auto-fill').attr('disabled', false);
    this.inputHidden.attr('name', '');
    clearInterval(this.watchTimer);
  },
  
  /**
   * The event handler for switching to auto mode.
   */
  autoEvent: function(event){
    event.stopPropagation();
    this.auto();
    return false;
  },
  
  /**
   * The event handler for switching to edit mode.
   */
  editEvent: function(event){
    event.stopPropagation();
    this.edit();
    return false;
  },
  
  /**
   * The callback to update the elements value with the source value.
   */
  watch: function(){
    var v = this.source.val();
    v = contexflow.AutoFill.filters[this.filter_type](v, this.filter_extra);
    this.input.val(v);
    this.inputHidden.val(v);
  },
  
  /**
   * Starts watching the source element for changes to update the element.
   */
  onWatch: function(){
    if(this.is_auto){
      this.watch();
      this.watchTimer = setInterval(jQuery.proxy(this, 'watch'), 30);
    }
  },
  
  /**
   * Stops watching the source element to change.
   */
  offWatch: function(){
    clearInterval(this.watchTimer);
    if(this.is_auto){
      this.watch();
    }
  }

};



// The filters for AutoFill.

contexflow.AutoFill.filters = {
  'singular': function(value){
      switch(value){
        case 'People':
          return 'Person';
        case 'people':
          return 'person';
        case 'Movies':
          return 'Movie';
        case 'movies':
          return 'movie';
        case 'Ties':
          return 'Tie';
        case 'ties':
          return 'tie';
        case 'News':
        case 'news':
          return value;
        
      }
      
      if(value.substring(value.length-3) == 'ies'){
        value = value.substring(0,value.length-3) + 'y';
        return value;
      }
      
      if(value.substring(value.length-3) == 'oes'){
        value = value.substring(0,value.length-2);
        return value;
      }
      
      if(value.substring(value.length-1) == 's'){
        value = value.substring(0,value.length-1);
        return value;
      }
      return value;
    }
};



/**
 * Creates autofill inputs.
 *
 * @param context (optional) - the context to restrict the search of autofills within.
 */
contexflow.createAutoFillFields = function (context){
  context = context ? context : document;
  $('div.form-field-auto-fill, span.form-field-auto-fill', context).each(function(){
    new contexflow.AutoFill(this);
  });
};



// Triggers
// The namespace for trigger functions.
contexflow.triggers = {};



/**
 * Watches links that are triggers for javascript events. 
 */
contexflow.watchForActionTriggers = function(){
  var action = function(event){
    var $this = $(this);
    var name = $this.attr('data-trigger-name');
    
    if(name){
      if(!contexflow.triggers[name]){
        return;
      }
      
      var paramName = '';
      var params = {};
      var attrs = this.attributes;
      var minNameLength = 'data-trigger-param-'.length;
      for(var i=0; i<attrs.length; ++i){
        if(attrs[i].nodeName.indexOf('data-trigger-param-')==0 && attrs[i].nodeName.length > minNameLength){
          paramName = attrs[i].nodeName.substring(minNameLength).replace(/(\-[a-z])/g, function($1){return $1.toUpperCase().replace('-','');});
          params[paramName] = attrs[i].nodeValue;
        }
      }
      
      if(contexflow.triggers[name](params, this)){
        event.stopPropagation();
        return false;
      }
    }
  };
  
  $('a[data-trigger-scope=contexflow]').live( 'click', action);
  $('select[data-trigger-scope=contexflow]').live( 'change', action);
};



// Quick Edit
var openedQuickEdit; // keeps track of the currently opened quick edit



/**
 * Load a custom content type quick edit form.
 * 
 * @param attrs.key - the content type key
 * @param attrs.url - the URL to call to get the quick edit form
 */
contexflow.triggers.loadQuickEdit = function(attrs) {
  if(!attrs.key || !attrs.url){
    return false;
  }
  
  var data = {
    action: 'load_quick_edit',
    content_type: attrs.key
  };
  jQuery.post(attrs.url, data, function(response) {
      if(openedQuickEdit){
        contexflow.triggers.unloadQuickEdit({'key': openedQuickEdit});
      }
      
      openedQuickEdit = attrs.key;
      $('#content-type-'+data.content_type).addClass('hidden');
      $('#form-quick-edit-'+data.content_type).removeClass('hidden').children('td').eq(0).html($(response));
      contexflow.createAutoFillFields(jQuery('#form-quick-edit-'+data.content_type));
  });
  return true;
};



/**
 * Unload a custom content type quick edit form.
 *
 * @param attrs.key - the content type key
 */
contexflow.triggers.unloadQuickEdit = function(attrs) {
  
  if(attrs.key){ 
    openedQuickEdit = '';
    $('#content-type-'+attrs.key).removeClass('hidden');
    $('#form-quick-edit-'+attrs.key).addClass('hidden').children('td').eq(0).empty();
    return true;
  }
}



/**
 * Opens a dialog to add a field.
 * 
 * @param params.name - the content type
 * @param params.url - the URL for the AJAX call
 * @param params.title - the title for the dialog
 */
contexflow.triggers.addField = function(params, el) {
  var throbber = $(el).parent().children('.ajax-loading');
  if(throbber.length){
    throbber.css('visibility', 'visible');
  }
  
  // Package data to send to server
  var data = {
    action: 'edit_field',
    content_type: params.contentType,
    new_field: true
  };
  
  
  // Create the actions for the dialog buttons
  var buttons={};
  // Cancel button: close the dialog
  buttons[contexflow.__('cmsg')]=function() {
    $(this).dialog('close');
  };
  
  // Add Field: Submit the form
  buttons[contexflow.__('Add Field')] = function(){
    var content = $(this);
    
    // Show the throbber next to the buttons
    content.parent().find('.ui-dialog-buttonpane img.ui-throbber').css('visibility', 'visible');
    
    content.find('form').submit(function(){
      var data = $(this).serialize();
      data += '&action=add_field&new_field=true&content_type='+params.contentType;
      
      
      $.post(params.url, data, function(response){
        if(response.status=='error' && response.contents) {
          content.html($(response.contents));
        } else if(response.status=='success'){
          $('#fields-list').append(response.contents);
          content.dialog('close');
        } else {
          content.dialog('close');
        }
        content.parent().find('.ui-dialog-buttonpane img.ui-throbber').css('visibility', 'hidden');
      }, 'json');      
      
      return false;
    }).submit();
  }
  
  
  
  // create dialog if needed
  if($('#field-edit-form').size()==0){
    $('<div id="field-edit-form"></div>').appendTo('body');
  }
  
  // Call up the dialog
  $.post(params.url, data, function(response) {
    $('#field-edit-form')
      .dialog('destroy')
      .html(response).dialog($.extend(
      contexflow.options.dialog.large,
      {
        buttons: buttons,
        title: params.title
      }))
      .next()
        .append('<img title="'+contexflow.__('Waiting')+'" class="ui-throbber" style="visibility: hidden" src="'+contexflow.wpUrls.throbber+'"/>');
    
    if(throbber.length){
      throbber.css('visibility', 'hidden');
    }
  });
  return true;
};



/**
 * Edit a field
 *
 * @param params.content_type - the content type
 * @param params.field_name - the name of the fields
 * @param params.url - the URL for the AJAX call
 * @param params.title - the title for the dialog
 *
 */
contexflow.triggers.editField = function(params, el) {
  
  // Package data to send to server
  var data = {
    action: 'edit_field',
    content_type: params.contentType,
    field_name: params.fieldName
  };

  // Create the actions for the dialog buttons
  var buttons={};
  // Cancel button: close the dialog
  buttons[contexflow.__('cmsg')]=function() {
    $(this).dialog('close');
  };
  
  // Add Field: Submit the form
  buttons[contexflow.__('Save')] = function(){
    var content = $(this);
    
    // Show the throbber next to the buttons
    content.parent().find('.ui-dialog-buttonpane img.ui-throbber').css('visibility', 'visible');
    
    content.find('form').submit(function(){
      var data = $(this).serialize();
      data += '&action=save_field&new_field=false&content_type='+params.contentType;
      
      
      $.post(params.url, data, function(response){
        if(response.status=='error' && response.contents) {
          content.html($(response.contents));
        } else if(response.status=='success'){
          $('#fields-list').html(response.contents);
          content.dialog('close');
        } else {
          content.dialog('close');
        }
        content.parent().find('.ui-dialog-buttonpane img.ui-throbber').css('visibility', 'hidden');
      }, 'json');      
      
      return false;
    }).submit();
  }
  
  
  
  // create dialog if needed
  if($('#field-edit-form').size()==0){
    $('<div id="field-edit-form"></div>').appendTo('body');
  }
  
  // Call up the dialog
  $.post(params.url, data, function(response) {
    $('#field-edit-form')
      .dialog('destroy')
      .html(response).dialog($.extend(
      contexflow.options.dialog.large,
      {
        buttons: buttons,
        title: params.title
      }))
      .next()
        .append('<img title="'+contexflow.__('Waiting')+'" class="ui-throbber" style="visibility: hidden" src="'+contexflow.wpUrls.throbber+'"/>');
  });
  return true;
}



/**
 * Saves the labels box.
 *
 * @param params.name - the content type
 * @param params.url - the URL for the AJAX call
 */
contexflow.triggers.updateLabels = function(params, el) {
  var data = {
    action: 'update_labels',
    content_type: params['name']
  };
  
  contexflow.label_list(data);
  var throbber = $(el).parent().children('.ajax-loading');
  throbber.css('visibility', 'visible');
  jQuery.post(params['url'], data, function(response) {
    throbber.css('visibility', 'hidden');
  });
  return true;
};


/**
 * Saves the fields
 *
 * @param params.name
 * @param params.url - the URL for the AJAX call
 */
contexflow.triggers.updateFields = function(params, el) {
  // throbber
  var throbber = $(el).parent().children('.ajax-loading');
  throbber.css('visibility', 'visible');
  
  // update the value
  var ro = $('#fields_content').data('RearrangeTableRowOrder');
  if(ro){
    ro.update();
  }
  
  // data for the AJAX call
  var data = {
    action: 'update_field',
    content_type: params.name,
    order: $('#fields_order').val()
  };
  
  // send the results to the server and then update the HTML
  jQuery.post(params['url'], data, function(response) {
    jQuery('#fields_content').html(response);
    contexflow.setupRearrangeTableRowOrder();
    throbber.css('visibility', 'hidden');
  });
  return true;
};




/**
 * Add a category set
 *  
 * @param params.name
 * @param params.url - the URL for the AJAX call
 */
contexflow.triggers.addCategory = function(params, el) {
  var throbber = $(el).parent().children('.ajax-loading');
  if(throbber.length){
    throbber.css('visibility', 'visible');
  }
  
  var data = {
    action: 'edit_category',
    content_type: params['name'],
    new_category: true
  };
  
  var msg={};
  msg[contexflow.__('cmsg')]=function() {
    jQuery(this).dialog('close');
  }
  
  msg[contexflow.__('Add Category')] = function(){
    var content = $(this);
    content.parent().find('.ui-dialog-buttonpane img.ui-throbber').css('visibility', 'visible');
    content.find('form').submit(function(){
      var data = $(this).serialize();
      data += '&action=add_category&new_category=true&content_type='+params.name;
      
      
      $.post(params.url, data, function(response){
        if(response.status=='error' && response.form) {
          content.html($(response.form));
        } else if(response.status=='success'){
          $('#category-list').html(response.categories);
          content.dialog('close');
        } else {
          content.dialog('close');
        }
        content.parent().find('.ui-dialog-buttonpane img.ui-throbber').css('visibility', 'hidden');
      }, 'json');      
      
      return false;
    }).submit();
  }
  

  jQuery.post(params.url, data, function(response) {
    var dialog = $('#addcategory_dialog');
    dialog.dialog('destroy');
    dialog.html($(response)); // Wrap in $() to make sure the form element shows up
    jQuery("#addcategory_dialog").dialog($.extend(
      contexflow.options.dialog.large,
      {
        buttons: msg
      }))
      .next()
        .append('<img title="'+contexflow.__('Waiting')+'" class="ui-throbber" style="visibility: hidden" src="'+contexflow.wpUrls.throbber+'"/>');
    
    if(throbber.length){
      throbber.css('visibility', 'hidden');
    }
  });
  return true;
};


/**
 * Edit a category
 *
 * @param params.url
 * @param params.name
 * @param params.category
 */
contexflow.triggers.editCategory = function(params){
  var data = {
    action: 'edit_category',
    content_type: params['name'],
    category: params['category']
  };
  
  var msg={};
  msg[contexflow.__('cmsg')]=function() {
             jQuery(this).dialog('close');
  };
  
  msg[contexflow.__('Save')] = function(){
    var content = $(this);
    content.find('form').submit(function(){
      var data = $(this).serialize();
      data += '&action=update_category&content_type='+params.name;
      
      
      $.post(params.url, data, function(response){
        if(response.status=='error' && response.form) {
          content.html($(response.form));
        } else if(response.status=='success'){
          $('#category-list').html(response.categories);
          content.dialog('close');
        } else {
          content.dialog('close');
        }
      }, 'json');      
      
      return false;
    }).submit();
  }
  
  
  jQuery.post(params['url'], data, function(response) {
    var dialog = $("#editcategory_dialog");
    dialog.dialog('destroy')
      .html($(response))
      .dialog($.extend(
        contexflow.options.dialog.large,
        {
          buttons: msg
        }));
  });
  return true;
};



/**
 * Saves all of the 
 *
 */
contexflow.triggers.saveCategoriesUsedByContentType = function(params, el){
  
  var throbber = $(el).parent().children('.ajax-loading');
  throbber.css('visibility', 'visible');
  
  var cats_to_update = {};
  $('#category-list :checkbox[name=categories]').each(function(){
    var $this = $(this);
    if($this.attr('checked') != ($this.attr('data-original-state') == 'checked')){
      cats_to_update[$this.val()] = $this.attr('checked');
    }
  });
  
  if(!$.isEmptyObject(cats_to_update)){
    // make AJAX call to update
    var data = {
      action: 'save_categories_used_by_content_type',
      content_type: params.name,
      categories_to_update: cats_to_update
    };
    
    $.post(params.url, data, function(response) {
      
      if(response.status == 'success'){
        $('#category-list').html(response.contents);
      } else {
        // error
      }
      throbber.css('visibility', 'hidden');
    }, 'json');
    
  } else {
    // nothing to save
    throbber.css('visibility', 'hidden');
  }
  return true;
};




/**
 * Deletes a fields from a content type
 *
 * url, contentType, fieldName, msg
 */
contexflow.triggers.deleteField = function(params, el){
  var data = {
    action: 'delete_field',
    content_type: params.contentType,
    field_name: params.fieldName
  };
  if (confirm(contexflow.__('cnfmsgfield'))) {
      $.post(params.url, data, function(response) {
        if(response.status == 'success'){
          $('#fields-list').html(response.contents);
          var tr = $(el).parents('tr').eq(0);
          var ro = tr.data('RearrangeTableRowOrder');
          if(ro){
            ro.removeRow(tr);
          }
        }
      }, 'json');
  }
  return true;
};



/**
 * Updates the area for the field type specific settings
 *
 * @param params.url,
 * @param params.id,
 * @param params.contentType
 *
 */
contexflow.triggers.updateFieldTypeSettings = function(params, el) {
  var field_type = $(el).val();
  var data = {
    action: 'update_field_type_settings',
    field_type: field_type,
    content_type: params.contentType
  };
  
  var throbber = $(el).parent().children('.ajax-loading');
  throbber.css('visibility', 'visible');
  $.post(params.url, data, function(response) {
    $('#field-type-settings').html($(response.contents));
    throbber.css('visibility', 'hidden');
    return;
  }, 'json');
};




$(document).ready(function(){
  
  contexflow.setupChildParentFeaturesRelationship();
  contexflow.createAutoFillFields();
  contexflow.setupToggleBoxes();
  contexflow.watchForActionTriggers();
  contexflow.setupCheckboxToSubArea();
  contexflow.setupTableColumnRearrange();
  contexflow.setupRearrangeTableRowOrder();

});

})(jQuery, window, window.document);