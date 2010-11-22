<div class="wrap content-type-edit-page">
  <div class="icon32"><img src="<?php echo CONTEXFLOW_PLUGIN_URL.'images/easy-post-type-large.png'; ?>" alt="{icon}" /></div>
  <h2><?php _e('Edit','cct'); ?> Post Type</h2>

  <?php if (!empty($content)) : ?>
  <form name="editform" action="" method="post">
      <input type="hidden" name="plugin_post_key" value="<?php echo PLUGIN_POST_KEY; ?>" />
      <input type="hidden" name="content_type" value="<?php echo $content['systemkey']; ?>" />
    <div class="metabox-holder has-right-sidebar">
      <div id="side-info-column" class="inner-sidebar">
      
      <?php 
          $checked=' checked="checked" ';
          // how to use meta boxes: http://www.code-styling.de/english/how-to-use-wordpress-metaboxes-at-own-plugins
          do_meta_boxes('content-type', 'side', $content);
        ?>
    </div>
      <div id="content-type-body">
        <div id="primary-options" class="form-wrap">
          <fieldset class="col-left"><div class="inside">
            <div class="form-field form-required">
              <label><?php _e('Name (plural)','cct'); ?></label>
              <input type="text" class="name" name="label" id="name" value="<?php echo $content['label']; ?>">
              <p><?php _e('The name of the content.','cct'); ?></p>
            </div>
            <div class="form-field form-required">
              <label><?php _e('Singular name','cct'); ?></label>
              <div class="form-field-auto-fill" data-source="#name" data-filter="singular" data-auto="true"><input type="text" name="singular_label" class="name" value="<?php echo $content['singular_label']; ?>"> <a href="#" class="button edit-auto-fill hide-if-no-js" tabindex="0"><?php _e('edit'); ?></a><a href="#" class="button auto-auto-fill hide-if-no-js" tabindex="0"><?php _e('auto'); ?></a></div>
              <p><?php _e('The singular version of the name.','cct'); ?></p>
            </div>
            <div class="form-field">
              <label><?php _e('System key','cct'); ?></label>
              <input type="text" disabled="disabled" name="content_type" value="<?php echo $content['systemkey']; ?>">
              <p><?php _e('The key is the system friendly version of the name.','cct'); ?></p>
            </div>
            <div class="pretty-urls">
              <label><?php _e('Use pretty URLs','cct'); ?></label>
              <input type="checkbox" name="pretty_urls" <?php echo $content['rewrite']==true?$checked:""; ?> id="pretty_urls" />
            </div>
            
          </div></fieldset>
          <fieldset class="col-right"><div class="inside">
            <h4><?php _e('Features','cct'); ?></h4>
            <ul class="newsig--features-list">
              <li><input type="checkbox" name="title" id="features-title" <?php echo $content['title']==true?$checked:""; ?>><label for="features-title">title</label></li>
              <li><input type="checkbox" name="editor" id="features-editor" <?php echo $content['editor']==true?$checked:""; ?>><label for="features-editor">editor</label></li>
              <li><input type="checkbox" name="author"  id="features-author" <?php echo $content['author']==true?$checked:""; ?>><label for="features-author">author</label></li>
              <li><input type="checkbox" name="thumbnail" id="features-thumbnail" <?php echo $content['thumbnail']==true?$checked:""; ?>><label for="features-thumbnail">thumbnail</label></li>
              <li><input type="checkbox" name="excerpt" id="features-excerpt" <?php echo $content['excerpt']==true?$checked:""; ?>><label for="features-excerpt">excerpt</label></li>
              <li><input type="checkbox" name="trackbacks" id="features-trackbacks" <?php echo $content['trackbacks']==true?$checked:""; ?>><label for="features-trackbacks">trackbacks</label></li>
              <li><input type="checkbox" name="custom_fields" id="features-custom-fields" <?php echo $content['custom_fields']==true?$checked:""; ?>><label for="features-custom-fields">custom fields</label></li>
              <li><input type="checkbox" name="comments" id="features-comments" <?php echo $content['comments']==true?$checked:""; ?>><label for="features-comments">comments</label></li>
              <li><input type="checkbox" name="revisions" id="features-revisions" <?php echo $content['revisions']==true?$checked:""; ?>><label for="features-revisions">revisions</label></li>
              <li><input type="checkbox" name="parent_child_relationships" id="features-parent-child-relationships" <?php echo $content['parent_child_relationships']==true?$checked:""; ?>><label for="features-parent-child-relationships">parent-child relationships</label>
                <ul>
                  <li><input type="checkbox" name="page_attributes" id="features-page-attributes" <?php echo $content['page_attributes']==true?$checked:""; ?>><label for="features-page-attributes">page attributes</label></li>
                </ul>
              </li>
            </ul>
          </div></fieldset>
        </div>
        
        
        <div id="content-type-metaboxes">
        <?php 
          // how to use meta boxes: http://www.code-styling.de/english/how-to-use-wordpress-metaboxes-at-own-plugins
          do_meta_boxes('content-type', 'normal', $content);
          do_meta_boxes('content-type', 'advance', $content);
        ?>
        </div>

      </div>
    </div>
  </form>
  <?php else : ?>
  <div class="nocontent">
      <?php $this->selectContentType(); ?>
  </div>
  <?php endif; ?>

</div>

<div id="delete_dialog" title="<?php _e('Delete Post Type?','cct'); ?>"></div>
