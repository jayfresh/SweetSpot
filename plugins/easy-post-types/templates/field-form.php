<div id="general_fieldtype">
<?php if(count($errors)>0): ?>
<div class="ui-state-error ui-corner-all">
  <?php if(in_array('name', $errors)): ?>
    <p><?php _e('You need to name the field.', 'cct'); ?></p>
  <?php endif; ?>
</div>
<?php endif; ?>


<?php
if (count($this->types)<=0): ?>
    <div class="ui-state-highlight ui-corner-all"><p><?php _e('No field plugins defined. Please install some field plugins.','cct'); ?></p></div>
    
<?php else: ?>
<form method="post" action="">
<input type="hidden" name="plugin_post_key" value="<?php echo PLUGIN_POST_KEY; ?>" />


<?php
$choices='';
$type="";
foreach($this->types as $obj){
  if (empty($type)) {
      if (isset($_POST['ct_name']))
          $type=$_POST['ct_name'];

      if (isset($values['type']) && $values['type']===$obj->getId())
          $type=$obj->getId();
  }

  if ($type==$obj->getId())
      $selected="selected";
  else
      $selected="";


  $choices.='<option '.$selected.' value="'.$obj->getId().'">'.$obj->getName().'</option>';
}

if (empty($type)) {
    $type=$this->types[0]->getId();
}

?>


<?php

  $fieldType=$this->getFieldType($type);
  if ($fieldType!=null): ?>
  <input type="hidden" name="content_type" value="<?php echo $_POST['content_type']; ?>" />
    <table><tbody>
      <tr class="<?php echo in_array('name', $errors)? 'error' : ''; ?>" >
        <th><span class="alignleft"><label for="field-edit-name"><?php _e('Name:','cct'); ?></label></span><span class="alignright"><abbr title="required" class="required">*</abbr></span></th> 
        <td><input type="text" name="name" value="<?php echo empty($values['name']) ? '' : $values['name']; ?>" id="field-edit-name"/></td>
      </tr>
      <tr>
        <th><span class="alignleft"><label for="field-edit-key"><?php _e('System key:','cct'); ?></label></span></th> 
        <?php if($is_new): ?>
          <td><input type="text" name="field_name" value="<?php echo empty($values['field_name']) ? '' : $values['field_name']; ?>" id="field-edit-key" /></td>
        <?php else: ?>
          <td><input type="text" value="<?php echo $values['field_name']; ?>" id="field-edit-key" disabled="disabled"/>
          <input type="hidden" name="field_name" value="<?php echo $values['field_name']; ?>" />
          
          </td>
        <?php endif; ?>
      </tr>
     <?php /* <tr>
        <th><span class="alignleft"><label for="field-edit-table-column"><?php _e('Add as table column:','cct'); ?></label></th> 
        <td><input type="checkbox" name="show_list" <?php echo $values['show_list']=='on'?'checked="checked"':''; ?> id="field-edit-table-column" /></td>
      </tr> */ ?>
      <tr>
        <th><span class="alignleft"><label for="field-edit-field-type"><?php _e('Field type:', 'cct'); ?></label></th>
        <td>
          <?php if($is_new): ?>
            <select id="field-edit-field-type" name="ct_name" data-trigger-scope="contexflow" data-trigger-name="updateFieldTypeSettings" data-trigger-param-content-type="<?php echo $_POST['content_type']; ?>" data-trigger-param-url="<?php echo $this->ajaxUrl; ?>"><?php echo $choices; ?></select>
            <img src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" class="ajax-loading" alt="" />
          <?php else: ?>
            <select id="field-edit-field-type" disabled="disabled" ><?php echo $choices; ?></select>
            <input type="hidden" name="ct_name" value="<?php echo $values['type']; ?>" ?>
          <?php endif; ?>
        </td>
      </tr>
    </tbody></table>
    <fieldset id="field-type-settings">
      <?php 
      $fieldSettings = !empty($values) ? $values : array();
      $this->edit_field_form_type_fields($type, $content_type, $fieldSettings); ?>
    </fieldset>
      <?php /*_e '<input type="submit" name="save_field" value="Save Field" />';*/ ?>
  
<?php else: ?>
      <?php _e('Please select a Custom Type First','cct'); ?>
<?php endif; ?>




  </form>
  <?php /* $this->display_fields($_POST['content_type']); */ ?>
<?php endif; ?>
</div>