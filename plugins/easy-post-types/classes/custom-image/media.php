<?php

	error_reporting(0);
	require( dirname(__FILE__) . '/../../../../../wp-config.php' );

	global $wpdb;
	global $userdata;

	// set the user info in case we need to limit to the current author
	get_currentuserinfo();

        $attachments_sql = "SELECT * FROM $wpdb->posts WHERE post_type = 'attachment' ORDER BY post_modified DESC";

	$attachment_files = $wpdb->get_results( $attachments_sql );

?>
<script language="Javascript">
var id='#<?php echo $_REQUEST['ref']; ?>';
jQuery(document).ready(function () {
    jQuery('.attachments-apply').bind('click', function(){
        tb_remove();
    });
    jQuery('.attachments-images a').bind('click', function(){
        eci_selected=jQuery(this).find('.attachment-file-name').val();
        jQuery(id).val(eci_selected);
    });
});
</script>

<div id="file-list">
    <p class="actions"><a href="#" class="attachments-apply button button-highlighted">Apply</a></p>

    <div id="file-details">

            <?php
            echo '<div class="attachments-images">';
            echo '<h2>'.__('Images Avaliable','cct').'</h2>';
            echo '<ul class="image-list">';
            foreach ($attachment_files as $post)
            {
                    if ( strpos($post->post_mime_type, 'image') !== false )
                    {
                            echo '<li style="float: left; padding: 5px;">';
                            echo '<a href="#">';
                            echo '<span class="attachments-data">';
                            echo '<input type="hidden" class="attachment-file-name" value="' . $post->guid . '" />';
                            echo '<input type="hidden" class="attachment-file-id" value=">' . $post->ID . '" />';
                            echo '</span>';
                            echo '<span class="attachments-thumbnail">';
                            echo wp_get_attachment_image( $post->ID, array(80, 60), true );
                            echo '</span>';
                            echo '</a>';
                            echo '</li>';
                    }
            }
            echo '</ul>';
            echo '</div>';
    ?>

</div>
<div class="clear"></div>
<p class="actions"><a href="#" class="attachments-apply button button-highlighted">Apply</a></p>

</div>
