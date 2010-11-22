<?php
    if (is_array($content['fields'])) {
        $comma='';
        foreach($content['fields'] as $key=>$field) {
            if ($field['show_list']=='on') {
                $field_to_show.=$comma.$key;
                $comma=',';
            }
        }
        if (is_array($content['fields']['admin_columns'])) {
            foreach($content['fields']['admin_columns'] as $key=>$field) {
                $field_to_show.=$comma.'%<'.strtoupper($field).'>%';
                $comma=',';
            }
        }
    }
    
    if ($content['public']==true)
        $checked=' checked="checked" ';
    else
        $checked='';
?>
<p><label for="show-in-admin">Show in admin</label> <input type="checkbox" name="public" <?php print $checked; ?> id="public" class="toggle-sub-area" rel="#admin-ui-sub-area" /></p>

<fieldset class="sub-area" id="admin-ui-sub-area"><div class="inside">
  <fieldset class="sub-area">
    <h4><?php _e('Use default UI'); ?></h4>
  <div class="inside">
     <?php
        if ($content['show_ui']==true)
            $checked=' checked="checked" ';
        else
            $checked='';
     ?>
    <p><label for=""><?php _e('Use the default WordPress post edit UI'); ?></label> <input type="checkbox" name="show_ui" <?php echo $checked; ?> id="show_ui" /></p>
  </div></fieldset>
  
  
  <fieldset class="sub-area table-column-rearrange" >
    <h4><?php _e('Columns in the table'); ?></h4>
    <div class="inside">
    <p><label for="fields-to-show-in-table"><?php _e('Columns to show in the table items:'); ?></label></p>
    <table class="widefat" cellspacing="0">
      <thead>
        <tr>
          <th class="columns-column">
            <input type="text" name="" value="<?php echo $field_to_show; ?>" id="fields_to_show_in_table" class="table-column-rearrange--input hide-if-js"/>
            <ul class="table-column-rearrange--used hide-if-no-js table-column-rearrange--columns"></ul>
            <div class="clear hide-if-js"></div>
          </th>
        </tr>
      </thead>
    </table>
    <div class="hide-if-no-js drop-area">
      <h5><?php _e('Unused columns'); ?></h5>
      <ul class="table-column-rearrange--unused table-column-rearrange--columns"></ul>
      <div class="clear"></div>
    </div>
    <p class="description hide-if-js"><?php _e('Add the field keys, separated by commas, to the input in order to show in the posts table for the content-type.'); ?></p>
    <p class="description hide-if-no-js"><?php _e('Drag and drop the fields to rearrange the columns'); ?></p>
    <p class="hide-if-js">Available fields:</p>
    <dl class="available-fields hide-if-js">
    <?php if (is_array($content['fields'])) : ?>
        <?php foreach($content['fields'] as $key=>$fieldname) : ?>
            <?php
            switch($key){
              case '_fieldset':
              case 'admin_columns':
                break;
              default:
                ?>
              <dt class="table-column-rearrange--col-key"><?php print $fieldname['field_name']; ?></dt>
              <dd class="table-column-rearrange--col-name"><?php print $fieldname['name']; ?></dd>
              <?php
            } ?>
            
        <?php endforeach; ?>
    <?php endif; ?>
      <dt class="table-column-rearrange--col-key">%&lt;CB&gt;%</dt>
      <dd class="table-column-rearrange--col-name"><input title="<?php _e('Bulk checkbox'); ?>" type="checkbox" onchange="this.checked=false" /></dd>
      <dt class="table-column-rearrange--col-key">%&lt;TITLE&gt;%</dt>
      <dd class="table-column-rearrange--col-name"><?php _e('Title'); ?></dd>
      <dt class="table-column-rearrange--col-key">%&lt;AUTHOR&gt;%</dt>
      <dd class="table-column-rearrange--col-name"><?php _e('Author'); ?></dd>
      <dt class="table-column-rearrange--col-key">%&lt;DATE&gt;%</dt>
      <dd class="table-column-rearrange--col-name"><?php _e('Date'); ?></dd>
    </dl>
  </div></fieldset>
  
  <fieldset class="sub-area">
    <h4><?php _e('Admin menu'); ?></h4>
    <div class="inside">
    <div class="form-field-row">
      <label for="admin_menu_position"><?php _e('Position'); ?></label>
      <select name="" id="admin_menu_position">
        <option <?php echo $content['menu_position']=='5'?" selected ":""; ?> value="5"><?php _e('Below Posts'); ?></option>
        <option <?php echo $content['menu_position']=='10'?" selected ":""; ?> value="10"><?php _e('Below Media'); ?></option>
        <option <?php echo $content['menu_position']=='20'?" selected ":""; ?> value="20"><?php _e('Below Pages'); ?></option>
      </select>
    </div>
    
    <div class="form-field-row">
      <label for="admin_menu_icon"><?php _e('Icon'); ?></label>
      <select name="" id="admin_menu_icon">
        <option <?php echo empty($content['menu_icon'])?" selected ":""; ?> value=""><?php _e('Post'); ?></option>
        <option <?php echo $content['menu_icon']==CONTEXFLOW_ICONS_URL.'photo.png'?" selected ":""; ?> value="<?php echo CONTEXFLOW_ICONS_URL ?>photo.png"><?php _e('Photo'); ?></option>
        <option <?php echo $content['menu_icon']==CONTEXFLOW_ICONS_URL.'podcast.png'?" selected ":""; ?> value="<?php echo CONTEXFLOW_ICONS_URL ?>podcast.png"><?php _e('Podcast'); ?></option>
      </select>
    </div>
  </div></fieldset>
  
</div></fieldset>

<p>
  <a class="button-primary button" href="Javascript:custom_type_update_admin(<?php $this->json_add_field($content['systemkey']); ?>);"><?php _e('Update Admin Interface'); ?></a>
</p>