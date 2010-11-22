<div class="wrap nosubsub" id="page_content">
  <div class="icon32"><img src="<?php echo CONTEXFLOW_PLUGIN_URL.'images/easy-post-type-large.png'; ?>" alt="{icon}" /></div>
  <h2>Easy Post Types</h2>
  <br class="clear" />
  <div id="col-container">
    <div id="col-right">
      <div class="form-wrap">
          <form id="form_edit" action="" method="post" onsubmit="return ct_check_actions();" >
          <input type="hidden" name="plugin_post_key" value="<?php echo PLUGIN_POST_KEY; ?>" />
          <div class="tablenav">
            <select name="action">
              <option selected="selected" value=""><?php _e('Bulk Actions','cct'); ?></option>
              <option value="delete"><?php _e('Delete','cct'); ?></option>
            </select>
            <input type="submit" class="button-secondary action"  id="doaction" name="doaction" value="Apply"/>
          </div>
          <table class="widefat tag fixed" cellspacing="0">
            <thead class="content-types-list">
              <tr>
                <th style="" class="manage-column column-cb check-column" id="cb" scope="col"><input type="checkbox"></th>
                <th style="" class="manage-column column-name" id="name" scope="col"><?php _e('Post Type','cct'); ?></th>
                <th style="" class="manage-column column-fields" id="fields" scope="col"><?php _e('Fields','cct'); ?></th>
                <th style="" class="manage-column column-categories" id="categories" scope="col"><?php _e('Categories','cct'); ?></th>
              </tr>
            </thead>
            <tbody id="the-list">
             <?php if ($contentTypes) : ?>
             <?php $alternate = true; ?>
             <?php foreach($contentTypes as $key => $content) : ?>
             <tr id="content-type-<?php echo $key; ?>" class="<?php echo $alternate? 'alternate' : ''; ?>">
                <th class="check-column" scope="row">
                  <input type="checkbox" value="<?php echo $key; ?>" name="delete_content_type[]"/>
                </th>
                <td class="name column-name">
                  <strong><a class="row-title" title="Edit &ldquo;<?php print $content['label']; ?>&rdquo;" href="<?php echo get_bloginfo('wpurl'); ?>/wp-admin/admin.php?page=ct_editcontent&type=<?php echo $key; ?>"><?php print $content['label']; ?></a></strong><br />
                  <div class="row-actions">
                    <span class="edit"><a href="<?php echo get_bloginfo('wpurl'); ?>/wp-admin/admin.php?page=ct_editcontent&type=<?php echo $key; ?>"><?php _e('Edit', 'cct'); ?></a> | </span>
                    <span class="inline hide-if-no-js"><a  href="#" class="editinline" data-trigger-scope="contexflow" data-trigger-name="loadQuickEdit" data-trigger-param-url="<?php echo $this->ajaxUrl; ?>" data-trigger-param-key="<?php echo $key; ?>"><?php _e('Quick Edit', 'cct'); ?></a> | </span>
                    <span class="delete"><a href="Javascript:custom_type_delete_content(<?php $this->json_delete_content($key); ?>);"><?php _e('Delete', 'cct'); ?></a></span>
                  </div>
                </td>
                <td class="fields column-fields">
                  <?php if(is_array($contentFields['fields'][$key])) {
                      $comma='';
                      foreach($contentFields['fields'][$key] as $keyf=>$field) {
                          if (!empty($field['type']) && $field['type']=='_fieldset') continue;
                          echo $comma.'<a href="'.get_bloginfo('wpurl').'/wp-admin/admin.php?page=ct_editcontent&type='.$key.'#content-type-fields">'.(empty($field['name'])? '' : $field['name']).'</a>';
                          $comma=',';
                      }
                    
                  } else {
                    _e('(none)','cct');
                  } ?>
                </td>
                <td class="categories column-categories">
                  
                  <?php 
                  if(!empty($contentFields['categories']) && is_array($contentFields['categories'])) {
                      foreach($contentFields['categories'] as $keyc=>$cate) {
                          if (is_array($cate['object_type'])) {
                            $comma='';
                            foreach($cate['object_type'] as $keycontent => $valuecat) {
                                if ($key==$valuecat){
                                    echo $comma.'<a href="'.get_bloginfo('wpurl').'/wp-admin/admin.php?page=ct_editcontent&type='.$key.'#content-type-labels">'.$cate['filters']['label'].'</a>';
                                    $comma=',';
                                  }
                            }
                         }
                      }
                  }
                  else {
                    _e('(none)','cct');
                  } ?>
                </td>
              </tr>
              <tr class="<?php echo $alternate? 'alternate' : ''; ?> hidden inline-edit-row inline-edit-row-content-type" id="form-quick-edit-<?php echo $key; ?>">
                  <td colspan="4" ></td>
              </tr>
              <?php $alternate = !$alternate; ?>
            <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
            <tfoot>
              <tr>
                <th style="" class="manage-column column-cb check-column"  scope="col"><input type="checkbox"></th>
                <th style="" class="manage-column column-name" scope="col"><?php _e('Post Type','cct'); ?></th>
                <th style="" class="manage-column column-fields"  scope="col"><?php _e('Fields','cct'); ?></th>
                <th style="" class="manage-column column-categories"  scope="col"><?php _e('Categories','cct'); ?></th>
              </tr>
            </tfoot>
          </table>
        </form>
      </div>
    </div>

    <div id="col-left"><div class="col-wrap">
        <div class="form-wrap">
        <h3><?php _e('Add a New Post Type','cct'); ?></h3>
        <form id="form_create" id="add-content-types" action="" method="post">
            <input type="hidden" name="plugin_post_key" value="<?php echo PLUGIN_POST_KEY; ?>" />
          <div class="form-field form-required">
            <label><?php _e('Name (plural)','cct'); ?></label>
            <input type="text" name="label" class="name" id="new-content-type-name">
            <p><?php _e('The name of the content.','cct'); ?></p>
          </div>
          <div class="form-field form-required">
            <label><?php _e('Singular name','cct'); ?></label>
            <div class="form-field-auto-fill" data-source="#new-content-type-name" data-filter="singular" data-auto="true"><input type="text" name="singular_label" class="name"> <a href="#" class="button edit-auto-fill hide-if-no-js" tabindex="0"><?php _e('edit','cct'); ?></a><a href="#" class="button auto-auto-fill hide-if-no-js" tabindex="0"><?php _e('auto','cct'); ?></a></div>
            <p><?php _e('The singular version of the name.','cct'); ?></p>
          </div>
          <div class="form-field">
            <label><?php _e('System key','cct'); ?></label>
            <input type="text" name="content_type" class="name">
            <p><?php _e('The key is the system friendly version of the name.','cct'); ?></p>
          </div>

          <div class="form-field">
            <label><?php _e('Features','cct'); ?></label>

            <ul class="newsig--features-list">
              <li><input type="checkbox" name="title" id="features-title" checked="checked"><label for="features-title"><?php _e('title','cct'); ?></label></li>
              <li><input type="checkbox" name="editor" id="features-editor" checked="checked"><label for="features-editor"><?php _e('editor','cct'); ?></label></li>
              <li><input type="checkbox" name="author" id="features-author" checked="checked"><label for="features-author"><?php _e('author','cct'); ?></label></li>
              <li><input type="checkbox" name="thumbnail" id="features-thumbnail" checked="checked"><label for="features-thumbnail"><?php _e('thumbnail','cct'); ?></label></li>
              <li><input type="checkbox" name="excerpt" id="features-excerpt" checked="checked"><label for="features-excerpt"><?php _e('excerpt','cct'); ?></label></li>
              <li><input type="checkbox" name="trackbacks" id="features-trackbacks" checked="checked"><label for="features-trackbacks"><?php _e('trackbacks','cct'); ?></label></li>
              <li><input type="checkbox" name="custom_fields" id="features-custom-fields" checked="checked"><label for="features-custom-fields"><?php _e('custom fields','cct'); ?></label></li>
              <li><input type="checkbox" name="comments" id="features-comments" checked="checked"><label for="features-comments"><?php _e('comments','cct'); ?></label></li>
              <li><input type="checkbox" name="revisions" id="features-revisions" checked="checked"><label for="features-revisions"><?php _e('revisions','cct'); ?></label></li>
              <li><input type="checkbox" name="parent_child_relationships" id="features-parent-child-relationships"><label for="features-parent-child-relationships"><?php _e('parent-child relationships','cct'); ?></label>
                <ul>
                  <li><input type="checkbox" name="page_attributes" id="features-page-attributes"><label for="features-page-attributes"><?php _e('page attributes','cct'); ?></label></li>
                </ul>
              </li>
            </ul>
          </div>

          <p class="submit">
            <input type="submit"  name="add_content" id="add-content" class="button" value="<?php _e('Add Post Type','cct'); ?>">
          </p>
        </form>
      </div>
    </div></div>
  </div>
</div>

<div id="delete_dialog" title="<?php _e('Delete Post Type?','cct'); ?>"></div>
