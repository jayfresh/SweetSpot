<ul>
  <li><?php _e('Created by'); ?> <a href=""><?php echo $content['createdby']; ?></a> on <span class="status-display"><?php echo date('m/d/Y',$content['created']); ?></span></li>
  <li><?php _e('Updated last by'); ?> <a href=""><?php echo $content['updatedby']; ?></a> on <span class="status-display"><?php echo date('m/d/Y',$content['updated']); ?></span></li>
</ul>
<div id="major-publishing-actions" class="submitbox">
  <div id="delete-action"><a href="Javascript:custom_type_delete_content(<?php $this->json_delete_content($content['systemkey']); ?>);" class="submitdelete deletion"><?php _e('Delete'); ?></a></div>
    
  <div id="publishing-action">
    <input type="submit" value="<?php _e('Save','cct'); ?>" accesskey="s" tabindex="5" id="save-content-type" class="button-primary" name="save" />
  </div>
  <div class="clear"></div>
</div>