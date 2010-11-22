<?php 
if ($content['categories']) : 
  $checked=' checked="checked" ';
  foreach($content['categories'] as $key=>$category): 
    $comma='';
  ?>
    <tr id="content-type-" class="<?php echo $alternate? 'alternate' : ''; ?>">
      <td class="use column-use check-column"><input type="checkbox" name="categories" value="<?php echo $category['internal_name']; ?>" <?php if (is_array($category) && in_array($content['systemkey'], $category['object_type'])) echo $checked; ?> data-original-state="<?php echo (is_array($category) && in_array($content['systemkey'], $category['object_type']))? 'checked' : ''; ?>" title="<?php _e('Use','cct'); ?>" /></td>
      <td class="name column-name">
        <strong><a class="row-title" title="Edit &ldquo;<?php print $content['label']; ?>&rdquo;" href=""><?php echo $category['filters']['label']; ?></a></strong><br />
      </td>
      <td class="fields column-fields"><?php if ($category['filters']['hierarchical']==true) _e('Category','cct'); else _e('Tag','cct'); ?></td>
      <td class="categories column-content-types">
        <?php if (is_array($category['object_type'])) 
        foreach($category['object_type'] as $type) { 
          echo $comma.($this->fields_info['types'][$type]['label']); 
          $comma=','; 
        }?>
      </td>
      <td class="column-edit"><span class="edit">
        <a data-trigger-scope="contexflow" data-trigger-name="editCategory" data-trigger-param-category="<?php echo $category['internal_name']; ?>" data-trigger-param-name="<?php echo $content['systemkey']; ?>" data-trigger-param-url="<?php echo $this->ajaxUrl; ?>" href="#"><?php _e('Edit','cct'); ?></a></span></td>
    </tr>
  <?php
endforeach;
endif;