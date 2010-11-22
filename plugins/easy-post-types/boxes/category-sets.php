<p>
  <a class="button" data-trigger-scope="contexflow" data-trigger-name="addCategory" data-trigger-param-name="<?php echo $content['systemkey']; ?>" data-trigger-param-url="<?php echo $this->ajaxUrl; ?>"><?php _e('Add category set', 'cct'); ?></a>
  <img src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" class="ajax-loading" alt="" />
</p>


<table class="widefat tag fixed fields-list" cellspacing="0">
  <thead>
    <tr>
      <th class="manage-column check-column" id="category-sets-use" scope="col"><?php _e('Use', 'cct'); ?></th>
      <th class="manage-column column-name" id="category-sets-name" scope="col"><?php _e('Name', 'cct'); ?></th>
      <th class="manage-column column-type" id="category-sets-type" scope="col"><?php _e('Type', 'cct'); ?></th>
      <th class="manage-column column-content-types" id="category-sets-content-types" scope="col"><?php _e('Post Types', 'cct'); ?></th>
      <th class="manage-column column-edit" id="fields-edit" scope="col" title="<?php _e('Edit', 'cct'); ?>"></th>
    </tr>
  </thead>
  <tbody id="category-list">
    
  <?php $this->list_categories($content); ?>
    
  </tbody>
  <tfoot>
    <tr>
      <th class="manage-column column-name check-column" scope="col"><?php _e('Use', 'cct'); ?></th>
      <th class="manage-column column-name" scope="col"><?php _e('Name', 'cct'); ?></th>
      <th class="manage-column column-type" scope="col"><?php _e('Type', 'cct'); ?></th>
      <th class="manage-column column-content-types" scope="col"><?php _e('Post Types', 'cct'); ?></th>
      <th class="manage-column column-edit" scope="col" title="<?php _e('Edit', 'cct'); ?>"></th>
    </tr>
  </tfoot>
</table>

<p>
  <!-- onchange="javascript:custom_type_update_category({'url':'<?php echo $this->ajaxUrl; ?>', 'item': this, 'type': '<?php echo $content['systemkey']; ?>'});" -->
  <a class="button-primary button" data-trigger-scope="contexflow" data-trigger-name="saveCategoriesUsedByContentType" data-trigger-param-name="<?php echo $content['systemkey']; ?>" data-trigger-param-url="<?php echo $this->ajaxUrl; ?>"><?php _e('Save categories','cct'); ?></a>
  <img src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" class="ajax-loading" alt="" />
</p>

<div id="addcategory_dialog" title="<?php _e('Add New Category'); ?>"></div>
<div id="editcategory_dialog" title="<?php _e('Edit Category'); ?>"></div>