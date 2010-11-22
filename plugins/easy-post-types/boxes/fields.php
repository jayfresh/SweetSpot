
<div id="fields_content" class="rearrange-table-row-order">

  <p>
    <a class="button button" data-trigger-scope="contexflow" data-trigger-name="addField" data-trigger-param-title="<?php _e('Add New Field'); ?>" data-trigger-param-content-type="<?php echo $content['systemkey']?>" data-trigger-param-url="<?php echo $this->ajaxUrl; ?>">Add field</a>
    <img src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" class="ajax-loading" alt="" />
  </p>
  
  <table class="widefat tag fixed" cellspacing="0">
    <thead class="content-types-list">
      <tr>
        <th class="manage-column column-rearrange-table-row-order" scope="col"> </th>
        <th class="manage-column column-name" id="fields-name" scope="col"><?php _e('Name','cct'); ?></th>
        <th class="manage-column column-type" id="fields-type" scope="col"><?php _e('Type','cct'); ?></th>
        <th class="manage-column column-type" id="fields-fieldname" scope="col"><?php _e('System Name','cct'); ?></th>
        <th class="manage-column column-type" id="fields-used" scope="col"><?php _e('Used','cct'); ?></th>
      </tr>
    </thead>
    <tbody id="fields-list" class="rearrange-table-row-order--items">
        <?php include BOXES_TEMPLATES_DIR.'field-rows.php'; ?>
    </tbody>
    <tfoot>
      <tr>
        <th class="manage-column column-order column-rearrange-table-row-order" scope="col"> </th>
        <th class="manage-column column-name" id="fields-name" scope="col"><?php _e('Name','cct'); ?></th>
        <th class="manage-column column-type" id="fields-type" scope="col"><?php _e('Type','cct'); ?></th>
        <th class="manage-column column-type" id="fields-fieldname" scope="col"><?php _e('System Name','cct'); ?></th>
        <th class="manage-column column-type" id="fields-used" scope="col"><?php _e('Used','cct'); ?></th>
      </tr>
    </tfoot>
  </table>
  
 
  
  <div class="hidden">
    <?php 
      $fields_order_default = array();
      if ($content['fields']) {
        foreach($content['fields'] as $key=>$field){
          if($key=='admin_columns') continue;
          $fields_order_default[] = $key;
        }
      }
      $fields_order_default = implode(',', $fields_order_default);
      ?>
    <input type="text" id="fields_order" name="fields_order" value="<?php echo $fields_order_default; ?>" class="rearrange-table-row-order--input" />
  </div>
  
  <div class="hide-if-no-js">
    <h5><?php _e('Special formatting for fields','cct'); ?></h5>
    <p class="description"><?php _e('Drag the special formatting rows up to the fields table above.','cct'); ?></p>
    <table class=" rearrange-table-row-order--special-rows-table widefat" cellspacing="0">
     
      <tbody class="rearrange-table-row-order--special-rows">
        <tr class="rearrange-table-row-order--special-row" data-item="%fieldset%">
          <td class="column-rearrange-table-row-order"><div class="rearrange-table-row-order--handle"><span class="ui-icon ui-icon-triangle-2-n-s"></span></div></td>
          <td colspan="4" class="column-name">
            <strong class="row-title"><em><?php _e('Fieldset break','cct'); ?></em></strong>
            <div class="row-actions">
              <span class="delete"><a href="#" class="rearrange-table-row-order--remove"><?php _e('Remove'); ?></a></span>
            </div>
          </td>
        </tr>
      </tbody>
  
    </table>
   </div>
   <p>
    <a class="button-primary button" data-trigger-scope="contexflow" data-trigger-name="updateFields" data-trigger-param-name="<?php echo $content['systemkey']; ?>" data-trigger-param-url="<?php echo $this->ajaxUrl; ?>"><?php _e('Save Fields','cct'); ?></a>  <img src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" class="ajax-loading" alt="" />
  </p>
</div>

