<?php
// needed only in the registration page
if ($cimy_uef_register_page) {
	$userid = isset($current_user) ? $current_user->ID : 0;
?>
	<script type='text/javascript'>
		var login_div = document.getElementById("login");
		login_div.style.width = "475px";
		login_div.style.margin = "7em auto";
	</script>

	<script type='text/javascript'>
		/* <![CDATA[ */
			userSettings = {
				url: "<?php echo SITECOOKIEPATH; ?>",
				uid: "<?php echo $userid; ?>",
				time: "<?php echo time(); ?>",
			}
		try{convertEntities(userSettings);}catch(e){};
		/* ]]> */
	</script>
<!-- 	<script type='text/javascript' src='http://localhost/wordpress27/wp-admin/js/common.js?ver=20081126'></script> -->
<?php
} else
	$userid = $get_user_id;

	// Set up init variables
	$mce_locale = ( '' == get_locale() ) ? 'en' : strtolower( substr(get_locale(), 0, 2) ); // only ISO 639-1
	$theme = "advanced";
	$language = isset($mce_locale) ? substr( $mce_locale, 0, 2 ) : 'en';
						
	$baseurl = get_option('siteurl') . '/wp-includes/js/tinymce';
						
	$https = ( isset($_SERVER['HTTPS']) && 'on' == strtolower($_SERVER['HTTPS']) ) ? true : false;
	
	if ( $https ) $baseurl = str_replace('http://', 'https://', $baseurl);
	
	$language . '", debug : false }, base : "' . $baseurl . '", suffix : "" };';
	
	$mce_spellchecker_languages = apply_filters('mce_spellchecker_languages', '+English=en,Danish=da,Dutch=nl,Finnish=fi,French=fr,German=de,Italian=it,Polish=pl,Portuguese=pt,Spanish=es,Swedish=sv');
	
	$mce_css = $baseurl . '/wordpress.css';
	$mce_css = apply_filters('mce_css', $mce_css);
	
	if ( $https ) $mce_css = str_replace('http://', 'https://', $mce_css);

	echo "\n\t<script type='text/javascript' src='".$baseurl."/tiny_mce.js'></script>\n";
	
	include_once(ABSPATH.'wp-includes/js/tinymce/langs/wp-langs.php' );

	// WordPress 2.7 kindly renamed var from $strings to $lang
	if (isset($strings))
		echo '<script type="text/javascript" language="javascript">'.$strings."</script>";
	else if (isset($lang))
		echo '<script type="text/javascript" language="javascript">'.$lang."</script>";
	
	$mce_buttons = apply_filters('mce_buttons', array('bold', 'italic', 'strikethrough', '|', 'bullist', 'numlist', 'blockquote', '|', 'justifyleft', 'justifycenter', 'justifyright', '|', 'link', 'unlink', 'image', 'wp_more', '|', 'spellchecker', 'fullscreen', 'wp_adv' ));
	$mce_buttons = implode($mce_buttons, ',');
	
	$mce_buttons_2 = apply_filters('mce_buttons_2', array('formatselect', 'underline', 'justifyfull', 'forecolor', '|', 'pastetext', 'pasteword', 'removeformat', '|', 'media', 'charmap', '|', 'outdent', 'indent', '|', 'undo', 'redo', 'wp_help' ));
	$mce_buttons_2 = implode($mce_buttons_2, ',');
	
	$mce_buttons_3 = apply_filters('mce_buttons_3', array());
	$mce_buttons_3 = implode($mce_buttons_3, ',');
	
	$mce_buttons_4 = apply_filters('mce_buttons_4', array());
	$mce_buttons_4 = implode($mce_buttons_4, ',');
	
	$plugins = array( 'safari', 'inlinepopups', 'autosave', 'spellchecker', 'paste', 'media', 'fullscreen' );

	// add 'wordpress' plug-in only if there is an user logged in, otherwise will produce issues on registration page
	if ($userid != 0)
		$plugins[] = 'wordpress';

	$plugins = implode($plugins, ',');
	
	echo "\n\t";
	echo '<script type="text/javascript" language="javascript">
		tinyMCE.init({
		mode : "exact",
		theme : "'.$theme.'",
		elements : "'.esc_attr($tiny_mce_objects).'",
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "left",
		theme_advanced_statusbar_location : "bottom",
		extended_valid_elements : "a[name|href|target|title|onclick],img[class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name],hr[class|width|size|noshade],font[face|size|color|style],span[class|align|style]",
		theme_advanced_buttons1: "'.$mce_buttons.'",
		theme_advanced_buttons2: "'.$mce_buttons_2.'",
		theme_advanced_buttons3: "'.$mce_buttons_3.'",
		theme_advanced_buttons4: "'.$mce_buttons_4.'",
		'.$mce_skin.'
		content_css : "'.$mce_css.'",
		language: "'.$mce_locale.'",
		spellchecker_languages : "'.$mce_spellchecker_languages.'",
		theme_advanced_resizin: "true",
		theme_advanced_resize_horizontal: "false",
		dialog_type: "modal",
		plugins: "'.$plugins.'",
	})';
	
	echo "\n\t";
	echo '</script>';
	echo "\n";
?>