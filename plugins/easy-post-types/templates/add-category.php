<div id="general_categorytype">
<form method="post" action="">
<input type="hidden" name="content_type" value="<?php echo $_POST['content_type']; ?>" />

<?php if(count($errors)>0): ?>
<div class="ui-state-error ui-corner-all">
  <?php if(in_array('name', $errors)): ?>
    <p><?php _e('You need to give a name for the category.', 'cct'); ?></p>
  <?php endif; ?>
</div>
<?php endif; ?>

<table><tbody>
  <tr class="<?php echo in_array('name', $errors)? 'error' : ''; ?>">
    <th>
      <span class="alignleft"><label for="add-category-name"><?php _e('Name:','cct'); ?></label></span>
      <span class="alignright"><abbr class="required" title="required">*</abbr></span>
    </th>
    <td><input type="text" id="add-category-name" name="name" value="<?php echo $category['filters']['label']; ?>" size="20" /></td>
  </tr>
  
  <tr>
    <th><span class="alignleft"><label for="add-category-internal-name"><?php echo _e('System key:','cct'); ?></label></span></th>
    <td>
    <?php if($_POST['new_category']): ?>
          <input type="text" name="internal_name" id="add-category-internal-name" value="<?php echo $category['internal_name']; ?>" size="20" />
    <?php else: ?>
          <input type="text" id="add-category-internal-name" value="<?php echo $category['internal_name']; ?>" size="20" disabled="disabled" />
          <input type="hidden" name="internal_name" value="<?php echo $category['internal_name']; ?>"  />
    <?php endif; ?>
    </td>
  </tr>
  
  
<?php
  $features_state = array( 'hierarchical' => true, 'public' => true, 'query_var' => true, 'rewrite' => true );
  if(count($errors)>0){
    foreach($features_state as $k => $v){
      $features_state[$k] = $_POST[$k];
    }
  } elseif($_POST['new_category']){
    // do nothing since they are already all true
  } else {
    foreach($features_state as $k => $v){
      $features_state[$k] = $category['filters'][$k];
    }
  }
  
?>
  
  <tr>
    <th><span class="alignleft"><label><?php _e('Features:'); ?></label></span></th>
    <td>
      <p>
          <input type="radio" id="add-category-hierarchical" <?php echo $features_state['hierarchical']? 'checked="checked"' : '';?> name="hierarchical" value="on" <?php echo !$_POST['new_category']? 'disabled="disabled"': '';?>/>
          <label for="add-category-hierarchical"><?php _e('Category (hierarchical)','cct'); ?></label>
          
          <input type="radio" id="add-category-tags" name="hierarchical" <?php echo !$features_state['hierarchical']? 'checked="checked"' : '';?> value="off" <?php echo !$_POST['new_category']? 'disabled="disabled"': '';?> />
          <label for="add-category-tags"><?php _e('Tags','cct'); ?></label>

      </p>
      
      <p>
        <input type="checkbox" id="add-category-public" <?php echo $features_state['public']? 'checked="checked"' : '';?> name="public" />
        <label for="add-category-public"><?php _e('Public','cct'); ?></label>
      </p>
      
      <p>
        <input type="checkbox" id="add-category-query_var" <?php echo $features_state['query_var']? 'checked="checked"' : '';?> name="query_var" />
        <label for="add-category-query_var"><?php _e('Searchable','cct'); ?></label>
      </p>
      
      <p>
        <input type="checkbox" id="add-category-rewrite" <?php echo $features_state['rewrite']? 'checked="checked"' : '';?> name="rewrite" />
        <label for="add-category-rewrite"><?php _e('Friendly URL','cct'); ?></label>
      </p>
    </td>
  </tr>

</tbody></table>

</form>
</div>