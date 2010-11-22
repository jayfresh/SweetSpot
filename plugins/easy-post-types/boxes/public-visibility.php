<?php
    if (isset($content['exclude_from_search'])) {
        if ($content['exclude_from_search']==false)
            $checked='checked="checked"';
        else
            $checked="";
    }
    else
        $checked="";
?>
<div>  
  <input type="checkbox" name="" <?php echo $checked; ?> id="include-in-search"/>
  <label for="include-in-search"><?php _e('Include in search'); ?></label>
  <p class="description"><?php _e('Checking this will include the post from this post type in search results.'); ?></p>
</div>

<?php
    if (isset($content['query_var'])) {
        if ($content['query_var']==true)
            $checked='checked="checked"';
        else
            $checked="";
    }
    else
        $checked="";
?>

<div>  
  <input type="checkbox" name="" <?php echo $checked; ?> id="query-var"/>
  <label for="query-var"><?php _e('Query var'); ?></label>
  <p class="description"><?php _e('Checking this will include in the query vars.'); ?></p>
</div>

<p>
  <a class="button-primary button" href="Javascript:custom_type_update_visibility(<?php $this->json_update_visibility($content['systemkey']); ?>);"><?php _e('Update Public Visibility'); ?></a>
</p>
