<?php
/*
Plugin Name: SB Welcome Email Editor
Plugin URI: http://www.sean-barton.co.uk
Description: Allows you to change the wordpress welcome email for both admin and standard members. Also allows for custom headers.
Version: 1.4
Author: Sean Barton
Author URI: http://www.sean-barton.co.uk
*/

$sb_we_file = trailingslashit(str_replace('\\', '/', __FILE__));
$sb_we_dir = trailingslashit(str_replace('\\', '/', dirname(__FILE__)));
$sb_we_home = trailingslashit(str_replace('\\', '/', get_bloginfo('wpurl')));
$sb_we_active = true;

define('SB_WE_PRODUCT_NAME', 'SB Welcome Email Editor');
define('SB_WE_PLUGIN_DIR_PATH', $sb_we_dir);
define('SB_WE_PLUGIN_DIR_URL', trailingslashit(str_replace(str_replace('\\', '/', ABSPATH), $sb_we_home, $sb_we_dir)));
define('SB_WE_PLUGIN_DIRNAME', str_replace('/plugins/','',strstr(SB_WE_PLUGIN_DIR_URL, '/plugins/')));

$sb_we_admin_start = '<div id="poststuff" class="wrap"><h2>' . SB_WE_PRODUCT_NAME . '</h2>';
$sb_we_admin_end = '</div>';

$sb_we_pages = array(
__('Settings','sb_we')=>'sb_we_settings'
);

function sb_we_loaded() {
	add_action('init', 'sb_we_init');
	add_action('admin_menu', 'sb_we_admin_page');
	global $sb_we_active;
	
	if (is_admin()) {
		if (!$sb_we_active) {
			$msg = '<div class="error"><p>' . SB_WE_PRODUCT_NAME . ' can not function because another plugin is conflicting. Please disable other plugins until this message disappears to fix the problem.</p></div>';
			add_action('admin_notices', create_function( '', 'echo \'' . $msg . '\';' ));
		}
	}
}

function sb_we_init() {
	if (!get_option('sb_we_settings')) {
		$sb_we_settings = new stdClass();
		$sb_we_settings->user_subject = '[[blog_name]] Your username and password';
		$sb_we_settings->user_body = 'Username: [user_login]<br />Password: [user_password]<br />[login_url]';
		$sb_we_settings->admin_subject = '[[blog_name]] New User Registration';
		$sb_we_settings->admin_body = 'New user registration on your blog ' . $blog_name . '<br /><br />Username: [user_login]<br />Email: [user_email]';
		$sb_we_settings->admin_notify_user_id = 1;
		$sb_we_settings->header_from_email = '[admin_email]';
		$sb_we_settings->header_reply_to = '[admin_email]';
		$sb_we_settings->header_send_as = 'html';
		$sb_we_settings->header_additional = '';
		
		add_option('sb_we_settings', $sb_we_settings);
	}
}
if (!function_exists('wp_new_user_notification')) {
	function wp_new_user_notification($user_id, $plaintext_pass = '') {
		global $sb_we_home;
		
		if ($user = new WP_User($user_id)) {
			$blog_name = get_option('blogname');
			$settings = get_option('sb_we_settings');
			$admin_email = get_option('admin_email');
			
			$user_login = stripslashes($user->user_login);
			$user_email = stripslashes($user->user_email);
			
			$user_subject = $settings->user_subject;
			$user_message = $settings->user_body;
			$admin_subject = $settings->admin_subject;
			$admin_message = $settings->admin_body;
			
			//Headers
			$headers = '';
			if ($reply_to = $settings->header_reply_to) {
				$headers .= 'Reply-To: ' . $reply_to . "\r\n";
			}
			if ($from_email = $settings->header_from_email) {
				$headers .= 'From: ' . $from_email . "\r\n";
			}
			if ($send_as = $settings->header_send_as) {
				if ($send_as == 'html') {
					$charset = get_bloginfo('charset');
					if (!$charset) {
						$charset = 'iso-8859-1';
					}
					$headers .= 'Content-type: text/html; charset=' . $charset . "\r\n";
				}
			}
			if ($additional = $settings->header_additional) {
				$headers .= $additional;
			}
			
			$headers = str_replace('[admin_email]', $admin_email, $headers);
			$headers = str_replace('[blog_name]', $blog_name, $headers);
			$headers = str_replace('[site_url]', $sb_we_home, $headers);
			//End Headers
			
			//Don't notify if the admin object doesn't exist;
			if ($settings->admin_notify_user_id) {
				//Allows single or multiple admins to be notified. Admin ID 1 OR 1,3,2,5,6,etc...
				$admins = explode(',', $settings->admin_notify_user_id);
				
				if (!is_array($admins)) {
					$admins = array($admins);
				}
				
				$admin_message = str_replace('[blog_name]', $blog_name, $admin_message);
				$admin_message = str_replace('[site_url]', $sb_we_home, $admin_message);
				$admin_message = str_replace('[login_url]', $sb_we_home . 'wp-login.php', $admin_message);
				$admin_message = str_replace('[user_email]', $user_email, $admin_message);
				$admin_message = str_replace('[user_login]', $user_login, $admin_message);
				$admin_message = str_replace('[plaintext_password]', $plaintext_pass, $admin_message);
				$admin_message = str_replace('[user_password]', $plaintext_pass, $admin_message);
			
				$admin_subject = str_replace('[blog_name]', $blog_name, $admin_subject);
				$admin_subject = str_replace('[site_url]', $sb_we_home, $admin_subject);
				$admin_subject = str_replace('[user_email]', $user_email, $admin_subject);
				$admin_subject = str_replace('[user_login]', $user_login, $admin_subject);				
				
				foreach ($admins as $admin_id) {
					if ($admin = new WP_User($admin_id)) {
						@mail($admin->user_email, $admin_subject, $admin_message, $headers);
					}
				}
			}
		
			if (!empty($plaintext_pass)) {
				$user_message = str_replace('[site_url]', $sb_we_home, $user_message);
				$user_message = str_replace('[login_url]', $sb_we_home . 'wp-login.php', $user_message);
				$user_message = str_replace('[user_email]', $user_email, $user_message);
				$user_message = str_replace('[user_login]', $user_login, $user_message);
				$user_message = str_replace('[plaintext_password]', $plaintext_pass, $user_message);
				$user_message = str_replace('[user_password]', $plaintext_pass, $user_message);
				$user_message = str_replace('[blog_name]', $blog_name, $user_message);
				
				$user_subject = str_replace('[blog_name]', $blog_name, $user_subject);
				$user_subject = str_replace('[site_url]', $sb_we_home, $user_subject);
				$user_subject = str_replace('[user_email]', $user_email, $user_subject);
				$user_subject = str_replace('[user_login]', $user_login, $user_subject);			
			
				wp_mail($user_email, $user_subject, $user_message, $headers);
			}
		}
	}
} else {
	$sb_we_active = false;
}

function sb_we_update_settings() {
	$old_settings = get_option('sb_we_settings');

	$settings = new stdClass();
	foreach ($old_settings as $key=>$value) {
		$settings->$key = stripcslashes(sb_we_post($key, $value));
	}

	if (update_option('sb_we_settings', $settings)) {
		sb_we_display_message(__('Settings have been successfully saved', 'sb_we'));
	}
}

function sb_we_display_message($msg, $error=false, $return=false) {
    $class = 'updated fade';
    
    if ($error) {
        $class = 'error';
    }
	
    $html = '<div id="message" class="' . $class . '" style="margin-top: 5px; padding: 7px;">' . $msg . '</div>';

    if ($return) {
            return $html;
    } else {
            echo $html;
    }
}

function sb_we_settings() {
	if (sb_we_post('submit')) {
		sb_we_update_settings();
	}
	
	$html = '';
	$settings = get_option('sb_we_settings');
	
	$page_options = array(
	'user_subject'=>array(
		'title'=>'User Email Subject'
		, 'type'=>'text'
		, 'style'=>'width: 500px;'
		, 'description'=>'Subject line for the email sent to the user.'
	)
	, 'user_body'=>array(
		'title'=>'User Email Body'
		, 'type'=>'textarea'
		, 'style'=>'width: 550px; height: 200px;'
		, 'description'=>'Body content for the email sent to the user.'
	)
	, 'admin_subject'=>array(
		'title'=>'Admin Email Subject'
		, 'type'=>'text'
		, 'style'=>'width: 500px;'
		, 'description'=>'Subject Line for the email sent to the admin user(s).'
	)
	, 'admin_body'=>array(
		'title'=>'Admin Email Body'
		, 'type'=>'textarea'
		, 'style'=>'width: 550px; height: 200px;'
		, 'description'=>'Body content for the email sent to the admin user(s).'
	)
	, 'header_from_email'=>array(
		'title'=>'From Email Address'
		, 'type'=>'text'
		, 'style'=>'width: 500px;'
		, 'description'=>'Optional Header sent to change the from email address for new user notification.'
	)
	, 'header_reply_to'=>array(
		'title'=>'Reply To Email Address'
		, 'type'=>'text'
		, 'style'=>'width: 500px;'
		, 'description'=>'Optional Header sent to change the reply to address for new user notification.'
	)
	, 'header_send_as'=>array(
		'title'=>'Send Email As'
		, 'type'=>'select'
		, 'style'=>'width: 100px;'
		, 'options'=>array(
			'text'=>'TEXT'
			, 'html'=>'HTML'
		)
		, 'description'=>'Send email as Text or HTML (Rememeber to remove html from text emails).'
	)
	, 'header_additional'=>array(
		'title'=>'Additional Email Headers'
		, 'type'=>'textarea'
		, 'style'=>'width: 550px; height: 200px;'
		, 'description'=>'Optional field for advanced users to add more headers. Dont\'t forget to separate headers with \r\n.'
	)	
	, 'submit'=>array(
		'title'=>''
		, 'type'=>'submit'
		, 'value'=>'Update Settings'
	)	
	);
	
	$html .= '<div style="margin-bottom: 10px;">' . __('This page allows you to update the Wordpress welcome email and add headers to make it less likely to fall into spam. You can edit the templates for both the admin and user emails and assign admin members to receive the notifications. Use the following hooks in any of the boxes below: [site_url], [login_url], [user_email], [user_login], [plaintext_password], [blog_name], [admin_email]', 'sb_we') . '</div>';	
	$html .= sb_we_start_box('Settings');
	
	$html .= '<form method="POST">';
	$html .= '<table class="widefat form-table">';
	
	$i = 0;
	foreach ($page_options as $name=>$options) {
		if ($options['type'] == 'submit') {
			$value = $options['value'];
		} else {
			$value = stripslashes(sb_we_post($name, $settings->$name));
		}
		$title = (isset($options['title']) ? $options['title']:false);
		
		$html .= '	<tr class="' . ($i%2 ? 'alternate':'') . '">
					<th style="vertical-align: top;">
						' . $title . '
						' . ($options['description'] ? '<div style="font-size: 10px; color: gray;">' . $options['description'] . '</div>':'') . '
					</th>
					<td style="' . ($options['type'] == 'submit' ? 'text-align: right;':'') . '">';
					
		switch ($options['type']) {
			case 'text':
				$html .= sb_we_get_text($name, $value, $options['class'], $options['style']);
				break;
			case 'textarea':
				$html .= sb_we_get_textarea($name, $value, $options['class'], $options['style'], $options['rows'], $options['cols']);
				break;
			case 'select':
				$html .= sb_we_get_select($name, $options['options'], $value, $options['class'], $options['style']);
				break;			
			case 'submit':
				$html .= sb_we_get_submit($name, $value, $options['class'], $options['style']);
				break;
		}
		
		$html .= '		</td>
				</tr>';
				
		$i++;
	}
	
	$html .= '</table>';
	$html .= '</form>';
	
	$html .= sb_we_end_box();;
	
	return $html;
}

function sb_we_printr($array=false) {
    if (!$array) {
        $array = $_POST;
    }
    
    echo '<pre>';
    print_r($array);
    echo '</pre>';
}

function sb_we_get_textarea($name, $value, $class=false, $style=false, $rows=false, $cols=false) {
	$rows = ($rows ? ' rows="' . $rows . '"':'');
	$cols = ($cols ? ' cols="' . $cols . '"':'');
	$style = ($style ? ' style="' . $style . '"':'');
	$class = ($class ? ' class="' . $class . '"':'');
	
	return '<textarea name="' . $name . '" ' . $rows . $cols . $style . $class . '>' . wp_specialchars($value, true) . '</textarea>';
}

function sb_we_get_select($name, $options, $value, $class=false, $style=false) {
	$style = ($style ? ' style="' . $style . '"':'');
	$class = ($class ? ' class="' . $class . '"':'');
	
	$html = '<select name="' . $name . '" ' . $class . $style . '>';
	if (is_array($options)) {
		foreach ($options as $val=>$label) {
			$html .= '<option value="' . $val . '" ' . ($val == $value ? 'selected="selected"':'') . '>' . $label . '</option>';
		}
	}
	$html .= '</select>';
	
	return $html;
}

function sb_we_get_input($name, $type=false, $value=false, $class=false, $style=false) {
	$style = ($style ? ' style="' . $style . '"':'');
	$class = ($class ? ' class="' . $class . '"':'');
	$value = ($value ? ' value="' . wp_specialchars($value, true) . '"':'');
	$type = ($type ? ' type="' . $type . '"':'');
	
	return '<input name="' . $name . '" ' . $value . $type . $style . $class . ' />';
}

function sb_we_get_text($name, $value=false, $class=false, $style=false) {
	return sb_we_get_input($name, 'text', $value, $class, $style);
}

function sb_we_get_submit($name, $value=false, $class=false, $style=false) {
	if (strpos($class, 'button') === false) {
		$class .= 'button';
	}
	
	return sb_we_get_input($name, 'submit', $value, $class, $style);
}

function sb_we_start_box($title , $return=true){
	$html = '	<div class="postbox" style="margin: 5px 0px; min-width: 0px !important;">
					<h3>' . __($title, 'sb_we') . '</h3>
					<div class="inside">';

	if ($return) {
		return $html;
	} else {
		echo $html;
	}
}

function sb_we_end_box($return=true) {
	$html = '</div>
		</div>';

	if ($return) {
		return $html;
	} else {
		echo $html;
	}
}

function sb_we_admin_page() {
	global $sb_we_pages;
	
	$admin_page = 'sb_we_settings';
	$func = 'sb_we_admin_loader';
	$access_level = 'manage_options';

	add_menu_page(SB_WE_PRODUCT_NAME, SB_WE_PRODUCT_NAME, $access_level, $admin_page, $func);

	foreach ($sb_we_pages as $title=>$page) {
		add_submenu_page($admin_page, $title, $title, $access_level, $page, $func);
	}

}

function sb_we_admin_loader() {
	global $sb_we_admin_start, $sb_we_admin_end;
	
	$page = str_replace(SB_WE_PLUGIN_DIRNAME, '', trim($_REQUEST['page']));
	
	echo $sb_we_admin_start;
	echo $page();
	echo $sb_we_admin_end;
}

function sb_we_post($key, $default='', $escape=false, $strip_tags=false) {
	return sb_we_get_superglobal($_POST, $key, $default, $escape, $strip_tags);
}

function sb_we_session($key, $default='', $escape=false, $strip_tags=false) {
	return sb_we_get_superglobal($_SESSION, $key, $default, $escape, $strip_tags);
}

function sb_we_get($key, $default='', $escape=false, $strip_tags=false) {
	return sb_we_get_superglobal($_GET, $key, $default, $escape, $strip_tags);
}

function sb_we_request($key, $default='', $escape=false, $strip_tags=false) {
	return sb_we_get_superglobal($_REQUEST, $key, $default, $escape, $strip_tags);
}

function sb_we_get_superglobal($array, $key, $default='', $escape=false, $strip_tags=false) {

	if (isset($array[$key])) {
		$default = $array[$key];

		if ($escape) {
			$default = mysql_real_escape_string($default);
		}

		if ($strip_tags) {
			$default = strip_tags($default);
		}
	}

	return $default;
}

add_action('plugins_loaded', 'sb_we_loaded');

?>