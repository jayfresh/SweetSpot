<?php
/*
 * $values contains all info for the fields.
 */
$checked='checked="checked"';
?>
<input type="hidden" name="content_type" value="<?php echo $key; ?>" />
<fieldset class="inline-edit-col-left"><div class="inline-edit-col">
 <h4><?php _e('Quick Edit','cct'); ?></h4>
    <label>
      <span class="title"><?php _e('Name (plural)','cct'); ?></span>
      <span class="input-text-wrap"><input type="text" name="label" class="name" value="<?php echo $values['label']; ?>"></span>
    </label>
    <label>
      <span class="title"><?php _e('Singular name','cct'); ?></span>
      <span class="form-field-auto-fill" data-source="#<?php echo $key; ?>-name" data-filter="singular" data-auto="true"><input type="text" name="singular_label" value="<?php echo $values['singular_label']; ?>" class="name"> <a href="#" class="button edit-auto-fill hide-if-no-js" tabindex="0"><?php _e('edit'); ?></a><a href="#" class="button auto-auto-fill hide-if-no-js" tabindex="0"><?php _e('auto'); ?></a></span>
    </label>
</div></fieldset>
<fieldset class="inline-edit-col-right"><div class="inline-edit-col">
  <ul class="newsig--features-list">
    <li><input type="checkbox" name="title" id="features-title" <?php echo $values['title']==true?$checked:""; ?>><label for="features-title"><?php _e('title','cct'); ?></label></li>
    <li><input type="checkbox" name="editor" id="features-editor" <?php echo $values['editor']==true?$checked:""; ?>><label for="features-editor"><?php _e('editor','cct'); ?></label></li>
    <li><input type="checkbox" name="author" id="features-author" <?php echo $values['author']==true?$checked:""; ?>><label for="features-author"><?php _e('author','cct'); ?></label></li>
    <li><input type="checkbox" name="thumbnail" id="features-thumbnail" <?php echo $values['thumbnail']==true?$checked:""; ?>><label for="features-thumbnail"><?php _e('thumbnail','cct'); ?></label></li>
    <li><input type="checkbox" name="excerpt" id="features-excerpt" <?php echo $values['excerpt']==true?$checked:""; ?>><label for="features-excerpt"><?php _e('excerpt','cct'); ?></label></li>
    <li><input type="checkbox" name="trackbacks" id="features-trackbacks" <?php echo $values['trackbacks']==true?$checked:""; ?>><label for="features-trackbacks"><?php _e('trackbacks','cct'); ?></label></li>
    <li><input type="checkbox" name="custom_fields"  id="features-custom-fields" <?php echo $values['custom_fields']==true?$checked:""; ?>><label for="features-custom-fields"><?php _e('custom fields','cct'); ?></label></li>
    <li><input type="checkbox" name="comments" id="features-comments" <?php echo $values['comments']==true?$checked:""; ?>><label for="features-comments"><?php _e('comments','cct'); ?></label></li>
    <li><input type="checkbox" name="revisions" id="features-revisions" <?php echo $values['revisions']==true?$checked:""; ?>><label for="features-revisions"><?php _e('revisions','cct'); ?></label></li>
    <li><input type="checkbox" name="parent_child_relationships" <?php echo $values['parent_child_relationships']==true?$checked:""; ?> id="features-parent-child-relationships"><label for="features-parent-child-relationships"><?php _e('parent-child relationships','cct'); ?></label>
      <ul>
        <li><input type="checkbox" name="page_attributes" <?php echo $values['page attributes']==true?$checked:""; ?> id="features-page-attributes"><label for="features-page-attributes"><?php _e('page attributes','cct'); ?></label></li>
      </ul>
    </li>
  </ul>
</div></fieldset>
<p class="submit inline-edit-save">
  <a class="button-secondary cancel alignleft" title="Cancel" href="#" data-trigger-scope="contexflow" data-trigger-name="unloadQuickEdit" data-trigger-param-key="<?php echo $key; ?>"><?php _e('Cancel', 'cct'); ?></a>
  <input type="submit" class="button-primary save alignright" name="update_content" title="Update" value="<?php _e('Update','cct'); ?>" href="#inline-edit" />
    <img alt="" src="http://seamus.newsig.com/wordpress3/wp-admin/images/wpspin_light.gif" style="display: none;" class="waiting">
  <br class="clear">
</p>