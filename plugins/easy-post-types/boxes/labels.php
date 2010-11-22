
  <?php foreach($this->labels as $key=>$label) : ?>
      <?php
        if (isset($content['labels'][$key])) {
            if ($content['parent_child_relationships']==0)
                $label_text=$content['labels'][$key];
            else
                $page_text=$content['labels'][$key];
        }
        else {
            $label_text=$label[0];
            $page_text=$label[1];
        }
        $msg = $label[2];
      ?>
      <div class="form-field-row">
          <?php if ($content['parent_child_relationships']==0) : ?>
          <label for="post-label-<?php echo $key; ?>"><?php echo $msg; ?></label>
          <div class="field"><input type="text" value="<?php echo $label_text; ?>" id="post_label_<?php echo $key; ?>" name="post_label_<?php echo $key; ?>"></div>
          <?php else : ?>
          <label for="page-label-<?php echo $key; ?>"><?php echo $msg; ?></label>
          <div class="field"><input type="text" value="<?php echo $page_text; ?>" id="page_label_<?php echo $key; ?>" name="page_label_<?php echo $key; ?>"></div>
          <?php endif; ?>
      </div>
  <?php endforeach; ?>



<p class="clear">
  <a class="button-primary button" data-trigger-scope="contexflow" data-trigger-name="updateLabels" data-trigger-param-name="<?php echo $content['systemkey']?>" data-trigger-param-url="<?php $this->json_update_labels($content['systemkey']); ?>"><?php _e('Save labels', 'cct'); ?></a> <img src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" class="ajax-loading" alt="" />
</p>
