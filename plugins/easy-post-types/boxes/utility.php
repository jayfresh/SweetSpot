<div class="wrap nosubsub" id="page_content">
  <div class="icon32"><img src="<?php echo CONTEXFLOW_PLUGIN_URL.'images/easy-post-type-large.png'; ?>" alt="{icon}" /></div>

    <h2><?php _e('Utilities','cct'); ?></h2>
    <div class="export">
        <select name="export_content_type" id="export_content_type">
        <option value="all"><?php _e('All Post Types','cct'); ?></option>
        <?php
            foreach ($this->fields_info['types'] as $key => $type) {
            if (!isset($_POST['content_type']))
              $_POST['content_type']=$key;
            if ($_POST['content_type']==$key)
              $selected=" selected ";
            else
              $selected = " ";
            echo '<option '.$selected.'value="'.$key.'">'.$type['label'].'</option>';
            }
        ?>
        </select>
        <span class="message"><?php _e('Select either a single post type, or all post types to export.','cct'); ?></span>
    </div>
    <div class="import">
        <div class="export">
            <a href="javascript:custom_type_export('<?php echo $this->ajaxUrl; ?>','export_content_type');"><?php _e('Export','cct'); ?></a>
            <?php _e('Click here, and save the output text below.', 'cct'); ?>
        </div>
        <div class="import">
            <a href="javascript:custom_type_import('<?php echo $this->ajaxUrl ?>');"><?php _e('Import','cct'); ?></a>
            <?php _e('Paste a previous export in the box below, and click import. Notice that if it is a full import all your post types defined will be lost.', 'cct'); ?>
        </div>
    </div>

    <div class="import-export">
        <textarea rows="20" cols="120" id="export_content" ></textarea>
    </div>
</div>