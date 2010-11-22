<?php
    if (isset($content['capabilities'])) {
        $capabilities=$content['capabilities'];
    }
    else {
        $capabilities[0]='edit_post';
        $capabilities[1]='edit_posts';
        $capabilities[2]='edit_others_posts';
        $capabilities[3]='publish_posts';
        $capabilities[4]='read_post';
        $capabilities[5]='read_private_posts';
        $capabilities[6]='delete_post';
    }
?>
<p class="description"><?php _e('Long help text to explain permission goes here... <a href="http://codex.wordpress.org/Roles_and_Capabilities">Roles and Capabilities</a>'); ?> </p>

<!-- <div class="form-field-row">
  <label for="permission-type"><?php _e('Permission type'); ?></label>
  <select name="" id="admin-menu-position">
    <option value=""><?php _e('Post'); ?></option>
    <option value=""><?php _e('Page'); ?></option>
    <option value=""><?php _e('this type???'); ?></option>
  </select>
</div>
-->


    <div class="form-field-row">
      <label for="permission-edit-object"><?php _e('edit object'); ?></label>
      <div class="field"><input value="<?php echo $capabilities[0]; ?>" type="text" class="auto-fill" id="permission_edit_object"  name=""> </div>
      <div class="description"><p class="description"><a href="http://codex.wordpress.org/Roles_and_Capabilities#edit_posts"><?php _e('Learn more') ?></a><p></div>
    </div>
    
    <div class="form-field-row">
      <label for="permission-edit-type"><?php _e('edit type'); ?></label>
      <div class="field"><input value="<?php echo $capabilities[1]; ?>" type="text" class="auto-fill" id="permission_edit_type" name=""> </div>
      <div class="description"><p class="description"><a href="http://codex.wordpress.org/Roles_and_Capabilities#edit_posts"><?php _e('Learn more') ?></a><p></div>
    </div>
    
    <div class="form-field-row">
      <label for="permission-edit-others-objects"><?php _e('edit others&rsquo; objects'); ?></label>
      <div class="field"><input value="<?php echo $capabilities[2]; ?>" type="text" class="auto-fill" id="permission_edit_others_objects" name=""> </div>
      <div class="description"><p class="description"><a href="http://codex.wordpress.org/Roles_and_Capabilities#edit_others_posts"><?php _e('Learn more') ?></a><p></div>
    </div>
    
    <div class="form-field-row">
      <label for="permission-publish-objects"><?php _e('publish objects'); ?></label>
      <div class="field"><input value="<?php echo $capabilities[3]; ?>" type="text" class="auto-fill" id="permission_publish_objects" name=""> </div>
      <div class="description"><p class="description"><a href="http://codex.wordpress.org/Roles_and_Capabilities#edit_published_posts"><?php _e('Learn more') ?></a><p></div>
    </div>
    
    <div class="form-field-row">
      <label for="permission-read-object"><?php _e('read object'); ?></label>
      <div class="field"><input value="<?php echo $capabilities[4]; ?>" type="text" class="auto-fill" id="permission_read_object" name=""> </div>
      <div class="description"><p class="description"><a href="http://codex.wordpress.org/Roles_and_Capabilities#read"><?php _e('Learn more') ?></a><p></div>
    </div>

      <div class="form-field-row">
      <label for="permission-read-private-object"><?php _e('read private object'); ?></label>
      <div class="field"><input value="<?php echo $capabilities[5]; ?>" type="text" class="auto-fill" id="permission_read_private_object" name=""> </div>
      <div class="description"><p class="description"><a href="http://codex.wordpress.org/Roles_and_Capabilities#read_private_posts"><?php _e('Learn more') ?></a><p></div>
    </div>
    
    <div class="form-field-row">
      <label for="permission-delete-object"><?php _e('delete object'); ?></label>
      <div class="field"><input value="<?php echo $capabilities[6]; ?>" type="text" class="auto-fill" id="permission_delete_object" name=""> </div>
      <div class="description"><p class="description"><a href="http://codex.wordpress.org/Roles_and_Capabilities#delete_posts"><?php _e('Learn more') ?></a><p></div>
    </div>
    
    <div class="clear-left"></div>


<p>
  <a class="button-primary button" href="Javascript:custom_type_update_permissions(<?php $this->json_update_permissions($content['systemkey']); ?>);"><?php _e('Update Permissions'); ?></a>
</p>
