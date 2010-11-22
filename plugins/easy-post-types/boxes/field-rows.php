<?php
if ($content['fields']) :
  foreach($content['fields'] as $key=>$field) : ?>
  <?php if($key=='admin_columns') continue; ?>
  <?php if ($field['type']=='_fieldset') : ?>
        <tr class="rearrange-table-row-order--special-row" data-item="%fieldset%">
          <td class="column-rearrange-table-row-order"><div class="rearrange-table-row-order--handle"><span class="ui-icon ui-icon-triangle-2-n-s"></span></div></td>
          <td colspan="4" class="column-name">
            <strong class="row-title"><em><?php _e('Fieldset break','cct'); ?></em></strong>
            <div class="row-actions">
              <span class="delete"><a href="#" class="rearrange-table-row-order--remove"><?php _e('Remove','cct'); ?></a></span>
            </div>
          </td>
        </tr>

  <?php else : ?>
  <tr id="content-type-<?php echo $key; ?>" class="<?php echo $alternate? 'alternate' : ''; ?>" data-item="<?php echo $key; ?>">
    <td class="column-rearrange-table-row-order"><div class="hide-if-no-js rearrange-table-row-order--handle"><span class="ui-icon ui-icon-triangle-2-n-s"></span></div></td>
    <td class="name column-name">
      <strong><a class="row-title" title="Edit &ldquo;<?php print $field['name']; ?>&rdquo;" href="#" data-trigger-scope="contexflow" data-trigger-name="editField" data-trigger-param-url="<?php echo $this->ajaxUrl; ?>"  data-trigger-param-content-type="<?php echo $content['systemkey']; ?>" data-trigger-param-field-name="<?php echo $field['field_name']; ?>" data-trigger-param-title="<?php echo __('Edit field: ') . $field['name']; ?>"><?php echo $field['name']; ?></a></strong><br />
      <div class="row-actions">
        <span class="edit"><a href="#" data-trigger-scope="contexflow" data-trigger-name="editField" data-trigger-param-url="<?php echo $this->ajaxUrl; ?>"  data-trigger-param-content-type="<?php echo $content['systemkey']; ?>" data-trigger-param-field-name="<?php echo $field['field_name']; ?>" data-trigger-param-title="<?php echo __('Edit field: ') . $field['name']; ?>"><?php _e('Edit'); ?></a> | </span>
        <span class="delete"><a href="#" data-trigger-scope="contexflow" data-trigger-name="deleteField" data-trigger-param-url="<?php echo $this->ajaxUrl; ?>"  data-trigger-param-content-type="<?php echo $content['systemkey']; ?>" data-trigger-param-field-name="<?php echo $field['field_name']; ?>"><?php _e('Delete'); ?></a></span>
      </div>
    </td>
    <td class="fields column-fields"><?php echo $field['type']; ?></td>
    <td class="fields column-fields"><?php echo $field['field_name']; ?></td>
    <td class="fields column-fields"><?php echo $this->dbclass->getPostsPerContentField($content['systemkey'], $field['field_name']); ?></td>
  </tr>
  <?php
  endif;
  endforeach;
endif;