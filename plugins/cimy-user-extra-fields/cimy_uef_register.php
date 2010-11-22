<?php

function cimy_register_user_extra_hidden_fields_stage2() {
	global $start_cimy_uef_comment, $end_cimy_uef_comment;

	echo "\n".$start_cimy_uef_comment;

	foreach ($_POST as $name=>$value) {
		if (!(stristr($name, "cimy_uef_")) === FALSE) {
			echo "\t\t<input type=\"hidden\" name=\"".$name."\" value=\"".esc_attr($value)."\" />\n";
		} else if ($name == "blog_id") {
			echo "\t\t<input type=\"hidden\" name=\"".$name."\" value=\"".esc_attr($value)."\" />\n";
		}
	}

	echo $end_cimy_uef_comment;
}

function cimy_register_user_extra_fields_signup_meta($meta) {

	foreach ($_POST as $name=>$value) {
		if (!(stristr($name, "cimy_uef_")) === FALSE) {
			$meta[$name] = $value;
		} else if ($name == "blog_id") {
			$meta[$name] = $value;
		}
	}

	return $meta;
}

function cimy_register_user_extra_fields_mu_wrapper($blog_id, $user_id, $password, $signup, $meta) {
	cimy_register_user_extra_fields($user_id, $password, $meta);
}

function cimy_register_overwrite_password($password) {
	global $wpdb;

	if (!is_multisite()) {
		if (isset($_POST["cimy_uef_wp_PASSWORD"]))
			$password = $_POST["cimy_uef_wp_PASSWORD"];
	}
	else {
		if (!empty($_GET['key']))
			$key = $_GET['key'];
		else
			$key = $_POST['key'];

		if (!empty($key)) {
			// seems useless since this code cannot be reached with a bad key anyway you never know
			$key = $wpdb->escape($key);

			$sql = "SELECT active, meta FROM ".$wpdb->signups." WHERE activation_key='".$key."'";
			$data = $wpdb->get_results($sql);

			// is there something?
			if (isset($data[0])) {
				// if not already active
				if (!$data[0]->active) {
					$meta = unserialize($data[0]->meta);

					if (!empty($meta["cimy_uef_wp_PASSWORD"])) {
						$password = $meta["cimy_uef_wp_PASSWORD"];
					}
				}
			}
		}
	}

	return $password;
}

function cimy_register_user_extra_fields($user_id, $password="", $meta=array()) {
	global $wpdb_data_table, $wpdb, $max_length_value, $fields_name_prefix, $wp_fields_name_prefix, $wp_hidden_fields, $cimy_uef_file_types, $user_level;

	if (isset($meta["blog_id"]))
		cimy_switch_to_blog($meta);

	// avoid to save stuff if user is being added from: /wp-admin/user-new.php
	if ($_POST["action"] == "adduser")
		return;

	// if not set, set to -1 == anonymous
	if (!isset($user_level))
		$user_level = -1;

	$options = cimy_get_options();
	$extra_fields = get_cimyFields(false, true);
	$wp_fields = get_cimyFields(true);

	$user_signups = false;
	if ((!is_multisite()) && ($options["confirm_email"]) && (empty($meta)))
		$user_signups = true;

	// ok ok this is yet another call from wp_create_user function under cimy_uef_activate_signup, we are not yet ready for this, aboooort!
	if ($user_signups) {
		$user = new WP_User((int) $user_id);
		$signup = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix."signups WHERE user_login = %s AND active = 0", $user->user_login));
		if (!empty($signup))
			return;
	}

	$i = 1;

	// do first for the WP fields then for EXTRA fields
	while ($i <= 2) {
		if ($i == 1) {
			$are_wp_fields = true;
			$fields = $wp_fields;
			$prefix = $wp_fields_name_prefix;
		}
		else {
			$are_wp_fields = false;
			$fields = $extra_fields;
			$prefix = $fields_name_prefix;
		}
		
		$i++;

		foreach ($fields as $thisField) {

			$type = $thisField["TYPE"];
			$name = $thisField["NAME"];
			$field_id = $thisField["ID"];
			$label = $thisField["LABEL"];
			$rules = $thisField["RULES"];
			$input_name = $prefix.$wpdb->escape($name);

			// if the current user LOGGED IN has not enough permissions to see the field, skip it
			// apply only for EXTRA FIELDS
			if ($user_level < $rules['show_level'])
				continue;

			// if show_level == anonymous then do NOT ovverride other show_xyz rules
			if ($rules['show_level'] == -1) {
				// if flag to show the field in the registration is NOT activated, skip it
				if (!$rules['show_in_reg'])
					continue;
			}

			// uploading a file is not supported when confirmation email is enabled (on MS is turned on by default yes)
			if (((is_multisite()) || ($options["confirm_email"])) && (in_array($type, $cimy_uef_file_types)))
				continue;

			if (isset($meta[$input_name]))
				$data = stripslashes($meta[$input_name]);
			else if (isset($_POST[$input_name])) {
				if ($type == "dropdown-multi")
					$data = stripslashes(implode(",", $_POST[$input_name]));
				else
					$data = stripslashes($_POST[$input_name]);
			}
			else
				$data = "";

			if ($type == "avatar") {
				// since avatars are drawn max to 512px then we can save bandwith resizing, do it!
				$rules['equal_to'] = 512;
			}

			if (in_array($type, $cimy_uef_file_types)) {
				$data = cimy_manage_upload($input_name, sanitize_user($_POST['user_login']), $rules, false, false, $type);
			}
			else {
				if ($type == "picture-url")
					$data = str_replace('../', '', $data);
					
				if (isset($rules['max_length']))
					$data = substr($data, 0, $rules['max_length']);
				else
					$data = substr($data, 0, $max_length_value);
			}
		
			$data = $wpdb->escape($data);

			if ($user_signups)
				$meta[$input_name] = $data;
			else if (!$are_wp_fields) {
				$sql = "INSERT INTO ".$wpdb_data_table." SET USER_ID = ".$user_id.", FIELD_ID=".$field_id.", ";
	
				switch ($type) {
					case 'avatar':
					case 'picture-url':
					case 'picture':
					case 'textarea':
					case 'textarea-rich':
					case 'dropdown':
					case 'dropdown-multi':
					case 'password':
					case 'text':
					case 'file':
						$field_value = $data;
						break;
		
					case 'checkbox':
						$field_value = $data == '1' ? "YES" : "NO";
						break;
		
					case 'radio':
						$field_value = $data == $field_id ? "selected" : "";
						break;
						
					case 'registration-date':
						$field_value = mktime();
						break;
				}
		
				$sql.= "VALUE='".$field_value."'";
				$wpdb->query($sql);
			}
			else {
				$f_name = strtolower($thisField['NAME']);
				
				$userdata = array();
				$userdata['ID'] = $user_id;
				$userdata[$wp_hidden_fields[$f_name]['post_name']] = $data;
				
				wp_update_user($userdata);
			}
		}
	}

	if ($user_signups) {
		$sql = $wpdb->prepare("SELECT * FROM $wpdb->users WHERE ID=$user_id");
		$saved_user = array_shift($wpdb->get_results($sql));
		$key = substr( md5( time() . rand() . $saved_user->user_email ), 0, 16 );

		$wpdb->insert($wpdb->prefix."signups", array(
			'user_login' => $saved_user->user_login,
			'user_email' => $saved_user->user_email,
			'registered' => $saved_user->user_registered,
			'active' => '0',
			'activation_key' => $key,
			'meta' => serialize($meta),
		));
		$sql = $wpdb->prepare("DELETE FROM $wpdb->users WHERE ID=$user_id");
		$wpdb->query($sql);

		$sql = $wpdb->prepare("DELETE FROM $wpdb->usermeta WHERE user_id=$user_id");
		$wpdb->query($sql);

		cimy_signup_user_notification($saved_user->user_login, $saved_user->user_email, $key, serialize($meta));
	}

	cimy_switch_current_blog(true);
}

function cimy_registration_check_mu_wrapper($data) {
	$user_login = $data['user_name'];
	$user_email = $data['user_email'];
	$errors = $data['errors'];

	$data['errors'] = cimy_registration_check($user_login, $user_email, $errors);

	return $data;
}

// added for profile rules check
function cimy_profile_check_wrapper($errors, $update, $user) {
	$errors = cimy_registration_check($user->user_login, $user->user_email, $errors);

	if (!empty($errors))
		$update = false;
}

function cimy_registration_check($user_login, $user_email, $errors) {
	global $wpdb, $rule_canbeempty, $rule_email, $rule_maxlen, $fields_name_prefix, $wp_fields_name_prefix, $rule_equalto_case_sensitive, $apply_equalto_rule, $cimy_uef_domain, $cimy_uef_file_types, $rule_equalto_regex, $user_level;

// 	cimy_switch_to_blog();
	$options = cimy_get_options();

	// code for confirmation email check
	if ((!is_multisite()) && ($options["confirm_email"])) {
		$errors = cimy_check_user_on_signups($errors, $user_login, $user_email);
	}
	// avoid to save stuff if user is being added from: /wp-admin/user-new.php
	if ($_POST["action"] == "adduser")
		return $errors;

	// if not set, set to -1 == anonymous
	if (!isset($user_level))
		$user_level = -1;

	$extra_fields = get_cimyFields(false, true);
	$wp_fields = get_cimyFields(true);

	// if we are updating profile don't bother with WordPress fields' rules
	if ($_POST["from"] == "profile")
		$i = 2;
	else
		$i = 1;

	// do first for the WP fields then for EXTRA fields
	while ($i <= 2) {
		if ($i == 1) {
			$fields = $wp_fields;
			$prefix = $wp_fields_name_prefix;
		}
		else {
			$fields = $extra_fields;
			$prefix = $fields_name_prefix;
		}
		
		$i++;

		foreach ($fields as $thisField) {
			$field_id = $thisField['ID'];
			$name = $thisField['NAME'];
			$rules = $thisField['RULES'];
			$type = $thisField['TYPE'];
			$label = $thisField['LABEL'];
			$description = $thisField['DESCRIPTION'];
			$input_name = $prefix.$wpdb->escape($name);
			$unique_id = $prefix.$field_id;

			// if the current user LOGGED IN has not enough permissions to see the field, skip it
			// apply only for EXTRA FIELDS
			if ($user_level < $rules['show_level'])
				continue;

			// if show_level == anonymous then do NOT ovverride other show_xyz rules
			if ($rules['show_level'] == -1) {
				// if we are updating the profile check correct rule
				if ($_POST["from"] == "profile") {
					// if flag to show the field in the profile is NOT activated, skip it
					if (!$rules['show_in_profile'])
						continue;
				} else { // we are registering new user
					// if flag to show the field in the registration is NOT activated, skip it
					if (!$rules['show_in_reg'])
						continue;
				}
			}

			// uploading a file is not supported when confirmation email is enabled (on MS is turned on by default yes)
			if (((is_multisite()) || ($options["confirm_email"])) && (in_array($type, $cimy_uef_file_types)))
				continue;

			if ($_POST["from"] == "profile") {
				// if editing a different user (only admin)
				if (isset($_GET['user_id']))
					$get_user_id = $_GET['user_id'];
				else if (isset($_POST['user_id']))
					$get_user_id = $_POST['user_id'];
				// editing own profile
				else
					$get_user_id = $user_ID;

				if (!empty($get_user_id)) {
					global $wpdb_data_table;
					$get_user_id = intval($get_user_id);

					// we need the freaking old value
					$old_value = $wpdb->get_var($wpdb->prepare("SELECT VALUE FROM ".$wpdb_data_table." WHERE USER_ID=".$get_user_id." AND FIELD_ID=".$field_id));

					// Hey, no need to check for rules if anyway I can't edit due to low permissions, neeeext!
					if ((($old_value != "") && ($rules['edit'] == 'edit_only_if_empty'))
					|| (($old_value != "") &&  (!current_user_can('edit_users')) && ($rules['edit'] == 'edit_only_by_admin_or_if_empty'))
					|| ($rules['edit'] == 'no_edit')
					|| (($rules['edit'] == 'edit_only_by_admin') && (!current_user_can('edit_users'))))
						continue;
				}
			}

			if (isset($_POST[$input_name])) {
				if ($type == "dropdown-multi")
					$value = stripslashes(implode(",", $_POST[$input_name]));
				else
					$value = stripslashes($_POST[$input_name]);
			}
			else
				$value = "";
	
			if ($type == "dropdown") {
				$ret = cimy_dropDownOptions($label, $value);
				$label = $ret['label'];
				$html = $ret['html'];
			}
			
			if (in_array($type, $cimy_uef_file_types)) {
				// filesize in Byte transformed in KiloByte
				$file_size = $_FILES[$input_name]['size'] / 1024;
				$file_type = $_FILES[$input_name]['type'];
				$value = $_FILES[$input_name]['name'];
				$old_file = $_POST[$input_name."_oldfile"];
				$del_old_file = $_POST[$input_name."_del"];
			}

			switch ($type) {
				case 'checkbox':
					$value == 1 ? $value = "YES" : $value = "NO";
					break;
				case 'radio':
					intval($value) == intval($field_id) ? $value = "YES" : $value = "NO";
					break;
			}

			// if the flag can be empty is NOT set OR the field is not empty then other check can be useful, otherwise skip all
			if ((!$rules['can_be_empty']) || ($value != "")) {
				// yea $i should be == 1 but ++ already so == 2 :)
				if (($i == 2) && ($input_name == ($prefix."PASSWORD2"))) {
					if ($value != $_POST[$prefix."PASSWORD"])
						$errors->add($unique_id, '<strong>'.__("ERROR", $cimy_uef_domain).'</strong>: '.$label.' '.__('does not match.', $cimy_uef_domain));
				}
				if (($rules['email']) && (in_array($type, $rule_email))) {
					if (!is_email($value))
						$errors->add($unique_id, '<strong>'.__("ERROR", $cimy_uef_domain).'</strong>: '.$label.' '.__('hasn&#8217;t a correct email syntax.', $cimy_uef_domain));
				}

				if ((!$rules['can_be_empty']) && (in_array($type, $rule_canbeempty)) && ($value == "")) {
					$empty_error = true;

					// IF   1. it's a file type
					// AND  2. there is an old one uploaded
					// AND  3. this old one is not gonna be deleted
					// THEN   do not throw the empty error.
					if ((in_array($type, $cimy_uef_file_types)) && ($old_file != "") && ($del_old_file == ""))
						$empty_error = false;

					if ($empty_error)
						$errors->add($unique_id, '<strong>'.__("ERROR", $cimy_uef_domain).'</strong>: '.$label.' '.__('couldn&#8217;t be empty.', $cimy_uef_domain));
				}

				if ((isset($rules['equal_to'])) && (in_array($type, $apply_equalto_rule))) {

					$equalTo = $rules['equal_to'];
					// 	if the type is not allowed to be case sensitive
					// 	OR if case sensitive is not checked
					// AND
					// 	if the type is not allowed to be a regex
					// 	OR if regex rule is not set
					// THEN switch to uppercase
					if (((!in_array($type, $rule_equalto_case_sensitive)) || (!$rules['equal_to_case_sensitive'])) && ((!in_array($type, $rule_equalto_regex)) || (!$rules['equal_to_regex']))) {

						$value = strtoupper($value);
						$equalTo = strtoupper($equalTo);
					}

					if ($rules['equal_to_regex']) {
						if (!preg_match($equalTo, $value)) {
							$equalmsg = " ".__("isn&#8217;t correct", $cimy_uef_domain);
							$errors->add($unique_id, '<strong>'.__("ERROR", $cimy_uef_domain).'</strong>: '.$label.$equalmsg.'.');
						}
					}
					else if ($value != $equalTo) {
						if (($type == "radio") || ($type == "checkbox"))
							$equalTo == "YES" ? $equalTo = __("YES", $cimy_uef_domain) : __("NO", $cimy_uef_domain);

						if ($type == "password")
							$equalmsg = " ".__("isn&#8217;t correct", $cimy_uef_domain);
						else
							$equalmsg = ' '.__("should be", $cimy_uef_domain).' '.$equalTo;

						$errors->add($unique_id, '<strong>'.__("ERROR", $cimy_uef_domain).'</strong>: '.$label.$equalmsg.'.');
					}
				}

				// CHECK IF IT IS A REAL PICTURE
				if (($type == "picture") || ($type == "avatar")) {
					if ((stristr($file_type, "image/") === false) && ($value != "")) {
						$errors->add($unique_id, '<strong>'.__("ERROR", $cimy_uef_domain).'</strong>: '.$label.' '.__('should be an image.', $cimy_uef_domain));
					}
				}

				// MIN LEN
				if (isset($rules['min_length'])) {
					$minlen = intval($rules['min_length']);

					if (in_array($type, $cimy_uef_file_types)) {
						if ($file_size < $minlen) {

							$errors->add($unique_id, '<strong>'.__("ERROR", $cimy_uef_domain).'</strong>: '.$label.' '.__('couldn&#8217;t have size less than', $cimy_uef_domain).' '.$minlen.' KB.');
						}
					}
					else {
						if (strlen($value) < $minlen) {

							$errors->add($unique_id, '<strong>'.__("ERROR", $cimy_uef_domain).'</strong>: '.$label.' '.__('couldn&#8217;t have length less than', $cimy_uef_domain).' '.$minlen.'.');
						}
					}
				}

				// EXACT LEN
				if (isset($rules['exact_length'])) {
					$exactlen = intval($rules['exact_length']);

					if (in_array($type, $cimy_uef_file_types)) {
						if ($file_size != $exactlen) {

							$errors->add($unique_id, '<strong>'.__("ERROR", $cimy_uef_domain).'</strong>: '.$label.' '.__('couldn&#8217;t have size different than', $cimy_uef_domain).' '.$exactlen.' KB.');
						}
					}
					else {
						if (strlen($value) != $exactlen) {

							$errors->add($unique_id, '<strong>'.__("ERROR", $cimy_uef_domain).'</strong>: '.$label.' '.__('couldn&#8217;t have length different than', $cimy_uef_domain).' '.$exactlen.'.');
						}
					}
				}

				// MAX LEN
				if (isset($rules['max_length'])) {
					$maxlen = intval($rules['max_length']);

					if (in_array($type, $cimy_uef_file_types)) {
						if ($file_size > $maxlen) {

							$errors->add($unique_id, '<strong>'.__("ERROR", $cimy_uef_domain).'</strong>: '.$label.' '.__('couldn&#8217;t have size more than', $cimy_uef_domain).' '.$maxlen.' KB.');
						}
					}
					else {
						if (strlen($value) > $maxlen) {

							$errors->add($unique_id, '<strong>'.__("ERROR", $cimy_uef_domain).'</strong>: '.$label.' '.__('couldn&#8217;t have length more than', $cimy_uef_domain).' '.$maxlen.'.');
						}
					}
				}
			}
		}
	}

	if (isset($_POST["securimage_response_field"])) {
		global $cuef_plugin_dir;
		require_once($cuef_plugin_dir.'/securimage/securimage.php');
		$securimage = new Securimage();
		if ($securimage->check($_POST['securimage_response_field']) == false) {
			$errors->add("securimage_code", '<strong>'.__("ERROR", $cimy_uef_domain).'</strong>: '.__('Typed code is not correct.', $cimy_uef_domain));
		}
	}

	if (isset($_POST["recaptcha_response_field"])) {
		$recaptcha_code_ok = false;

		if ($_POST["recaptcha_response_field"]) {
			global $cuef_plugin_dir;
			require_once($cuef_plugin_dir.'/recaptcha/recaptchalib.php');

			$recaptcha_resp = recaptcha_check_answer($options["recaptcha_private_key"],
							$_SERVER["REMOTE_ADDR"],
							$_POST["recaptcha_challenge_field"],
							$_POST["recaptcha_response_field"]);

			$recaptcha_code_ok = $recaptcha_resp->is_valid;
		}

		if (!$recaptcha_code_ok)
			$errors->add("recaptcha_code", '<strong>'.__("ERROR", $cimy_uef_domain).'</strong>: '.__('Typed code is not correct.', $cimy_uef_domain));
	}

	cimy_switch_current_blog();

	return $errors;
}

function cimy_registration_form($errors=null, $show_type=0) {
	global $wpdb, $start_cimy_uef_comment, $end_cimy_uef_comment, $rule_maxlen_needed, $fields_name_prefix, $wp_fields_name_prefix, $cuef_plugin_dir, $cimy_uef_file_types, $cimy_uef_textarea_types, $user_level, $cimy_uef_domain;

// 	cimy_switch_to_blog();

	// if not set, set to -1 == anonymous
	if (!isset($user_level))
		$user_level = -1;

	// needed by cimy_uef_init_mce.php
	$cimy_uef_register_page = true;
	$extra_fields = get_cimyFields(false, true);
	$wp_fields = get_cimyFields(true);

	if (is_multisite())
		$input_class = "cimy_uef_input_mu";
	else
		$input_class = "cimy_uef_input_27";

	$options = cimy_get_options();

	$tabindex = 21;
	
	echo $start_cimy_uef_comment;
	echo "\t";
	// needed to apply default values only first time and not in case of errors
	echo '<input type="hidden" name="cimy_post" value="1" />';
	echo "\n";
	$radio_checked = array();

	$i = 1;
	$upload_image_function = false;

	// do first the WP fields then the EXTRA fields
	while ($i <= 2) {
		if ($i == 1) {
			$fields = $wp_fields;
			$prefix = $wp_fields_name_prefix;
		}
		else {
			$fields = $extra_fields;
			$prefix = $fields_name_prefix;
			$current_fieldset = -1;

			if ($options['fieldset_title'] != "")
				$fieldset_titles = explode(',', $options['fieldset_title']);
			else
				$fieldset_titles = array();
		}

		$tiny_mce_objects = "";
	
		foreach ($fields as $thisField) {
	
			$field_id = $thisField['ID'];
			$name = $thisField['NAME'];
			$rules = $thisField['RULES'];
			$type = $thisField['TYPE'];
			$label = $thisField['LABEL'];
			$description = $thisField['DESCRIPTION'];
			$fieldset = $thisField['FIELDSET'];
			$input_name = $prefix.esc_attr($name);
			$post_input_name = $prefix.$wpdb->escape($name);
			$maxlen = 0;
			$unique_id = $prefix.$field_id;

			// showing the search then there is no need to upload buttons
			if ($show_type == 1) {
				if ($type == "password")
					continue;

				if (($type == "avatar") || ($type == "picture") || ($type == "file"))
					$type = "text";
			}

			// if the current user LOGGED IN has not enough permissions to see the field, skip it
			// apply only for EXTRA FIELDS
			if (($user_level < $rules['show_level']) && ($i == 2))
				continue;

			// if show_level == anonymous then do NOT ovverride other show_xyz rules
			if ($rules['show_level'] == -1) {
				if ($show_type == 0) {
					// if flag to show the field in the registration is NOT activated, skip it
					if (!$rules['show_in_reg'])
						continue;
				} else if ($show_type == 1) {
					// if flag to show the field in the blog is NOT activated, skip it
					if (!$rules['show_in_search'])
						continue;
				}
			}

			// uploading a file is not supported when confirmation email is enabled (on MS is turned on by default yes)
			if (((is_multisite()) || ($options["confirm_email"])) && (in_array($type, $cimy_uef_file_types)))
				continue;

			if (isset($_POST[$post_input_name])) {
				if ($type == "dropdown-multi")
					$value = stripslashes(implode(",", $_POST[$post_input_name]));
				else
					$value = stripslashes($_POST[$post_input_name]);
			}
			else if (isset($_GET[$name])) {
				if ($type == "dropdown-multi")
					$value = stripslashes(implode(",", $_GET[$name]));
				else
					$value = stripslashes($_GET[$name]);
			}
			// if there is no value and not $_POST means is first visiting then put all default values
			else if (!isset($_POST["cimy_post"])) {
				$value = $thisField['VALUE'];
				
				switch($type) {
	
					case "radio":
						if ($value == "YES")
							$value = $field_id;
						else
							$value = "";
						
						break;
		
					case "checkbox":
						if ($value == "YES")
							$value = "1";
						else
							$value = "";
						
						break;
				}
			}
			else
				$value = "";
			
			$value = esc_attr($value);

			if (($fieldset > $current_fieldset) && (isset($fieldset_titles[$fieldset])) && ($i != 1)) {
				$current_fieldset = $fieldset;

				if (isset($fieldset_titles[$current_fieldset]))
					echo "\n\t<h2>".$fieldset_titles[$current_fieldset]."</h2>\n";
			}

			if (($description != "") && ($type != "registration-date")) {
				echo "\t";
				echo '<p id="'.$prefix.'p_desc_'.$field_id.'" class="desc"><br />'.$description.'</p>';
				echo "\n";
			}

			echo "\t";
			echo '<p id="'.$prefix.'p_field_'.$field_id.'">';
			echo "\n\t";
	
			switch($type) {
				case "picture-url":
				case "password":
				case "text":
					$obj_label = '<label for="'.$unique_id.'">'.$label.'</label>';
					$obj_class = ' class="'.$input_class.'"';
					$obj_name = ' name="'.$input_name.'"';

					if ($type == "picture-url")
						$obj_type = ' type="text"';
					else
						$obj_type = ' type="'.$type.'"';

					$obj_value = ' value="'.$value.'"';
					$obj_value2 = "";
					$obj_checked = "";
					$obj_tag = "input";
					$obj_closing_tag = false;
					break;

				case "dropdown":
				case "dropdown-multi":
					$ret = cimy_dropDownOptions($label, $value);
					$label = $ret['label'];
					$html = $ret['html'];

					if ($type == "dropdown-multi") {
						$obj_name = ' name="'.$input_name.'[]" multiple="multiple" size="6"';
					}
					else {
						$obj_name = ' name="'.$input_name.'"';
					}

					$obj_label = '<label for="'.$unique_id.'">'.$label.'</label>';
					$obj_class = ' class="'.$input_class.'"';
					$obj_type = '';
					$obj_value = '';
					$obj_value2 = $html;
					$obj_checked = "";
					$obj_tag = "select";
					$obj_closing_tag = true;
					break;

				case "textarea":
					$obj_label = '<label for="'.$unique_id.'">'.$label.'</label>';
					$obj_class = ' class="'.$input_class.'"';
					$obj_name = ' name="'.$input_name.'"';
					$obj_type = "";
					$obj_value = "";
					$obj_value2 = $value;
					$obj_checked = "";
					$obj_tag = "textarea";
					$obj_closing_tag = true;
					break;

				case "textarea-rich":
					if ($tiny_mce_objects == "")
						$tiny_mce_objects = $fields_name_prefix.$field_id;
					else
						$tiny_mce_objects .= ",".$fields_name_prefix.$field_id;

					$obj_label = '<label for="'.$unique_id.'">'.$label.'</label>';
					$obj_class = ' class="'.$input_class.'"';
					$obj_name = ' name="'.$input_name.'"';
					$obj_type = "";
					$obj_value = "";
					$obj_value2 = $value;
					$obj_checked = "";
					$obj_tag = "textarea";
					$obj_closing_tag = true;
					break;

				case "checkbox":
					$obj_label = '<label class="cimy_uef_label_checkbox" for="'.$unique_id.'"> '.$label.'</label><br />';
					$obj_class = ' class="cimy_uef_checkbox"';
					$obj_name = ' name="'.$input_name.'"';
					$obj_type = ' type="'.$type.'"';
					$obj_value = ' value="1"';
					$obj_value2 = "";
					$value == "1" ? $obj_checked = ' checked="checked"' : $obj_checked = '';
					$obj_tag = "input";
					$obj_closing_tag = false;
					break;
		
				case "radio":
					$obj_label = '<label class="cimy_uef_label_radio" for="'.$unique_id.'"> '.$label.'</label>';
					$obj_class = ' class="cimy_uef_radio"';
					$obj_name = ' name="'.$input_name.'"';
					$obj_type = ' type="'.$type.'"';
					$obj_value = ' value="'.$field_id.'"';
					$obj_value2 = "";
					$obj_tag = "input";
					$obj_closing_tag = false;
	
					// do not check if another check was done
					if ((intval($value) == intval($field_id)) && (!in_array($name, $radio_checked))) {
						$obj_checked = ' checked="checked"';
						$radio_checked += array($name => true);
					}
					else {
						$obj_checked = '';
					}

					break;

				case "avatar":
				case "picture":
				case "file":
					$allowed_exts = '';
					if (isset($rules['equal_to']))
						if ($rules['equal_to'] != "")
							$allowed_exts = "'".implode("', '", explode(",", $rules['equal_to']))."'";

					if ($type == "file") {
						// if we do not escape then some translations can break
						$warning_msg = $wpdb->escape(__("Please upload a file with one of the following extensions", $cimy_uef_domain));

						$obj_checked = ' onchange="uploadFile(\'registerform\', \''.$unique_id.'\', \''.$warning_msg.'\', Array('.$allowed_exts.'));"';
					}
					else {
						// if we do not escape then some translations can break
						$warning_msg = $wpdb->escape(__("Please upload an image with one of the following extensions", $cimy_uef_domain));

						$obj_checked = ' onchange="uploadFile(\'registerform\', \''.$unique_id.'\', \''.$warning_msg.'\', Array(\'gif\', \'png\', \'jpg\', \'jpeg\', \'tiff\'));"';
					}

					// javascript will be added later
					$upload_file_function = true;
					$obj_label = '<label for="'.$unique_id.'">'.$label.' </label>';
					$obj_class = ' class="cimy_uef_picture"';
					$obj_name = ' name="'.$input_name.'"';
					$obj_type = ' type="file"';
					$obj_value = ' value="'.$value.'"';
					$obj_value2 = "";
					$obj_tag = "input";
					$obj_closing_tag = false;
					break;

				case "registration-date":
					$obj_label = '';
					$obj_class = '';
					$obj_name = ' name="'.$input_name.'"';
					$obj_type = ' type="hidden"';
					$obj_value = ' value="'.$value.'"';
					$obj_value2 = "";
					$obj_checked = "";
					$obj_tag = "input";
					$obj_closing_tag = false;
					break;
			}
	
			$obj_id = ' id="'.$unique_id.'"';

			// tabindex not used in MU, dropping...
			if (is_multisite())
				$obj_tabindex = "";
			else {
				$obj_tabindex = ' tabindex="'.strval($tabindex).'"';
				$tabindex++;
			}

			$obj_maxlen = "";
	
			if ((in_array($type, $rule_maxlen_needed)) && (!in_array($type, $cimy_uef_file_types))) {
				if (isset($rules['max_length'])) {
					$obj_maxlen = ' maxlength="'.$rules['max_length'].'"';
				} else if (isset($rules['exact_length'])) {
					$obj_maxlen = ' maxlength="'.$rules['exact_length'].'"';
				}
			}

			if (in_array($type, $cimy_uef_textarea_types))
				$obj_rowscols = ' rows="3" cols="25"';
			else
				$obj_rowscols = '';

			echo "\t";
			$form_object = '<'.$obj_tag.$obj_type.$obj_name.$obj_id.$obj_class.$obj_value.$obj_checked.$obj_maxlen.$obj_rowscols.$obj_tabindex;

			if ($obj_closing_tag)
				$form_object.= ">".$obj_value2."</".$obj_tag.">";
			else
				$form_object.= " />";

			if (($type != "radio") && ($type != "checkbox"))
				echo $obj_label;

			if (is_multisite()) {
				if ( $errmsg = $errors->get_error_message($unique_id) ) {
					echo '<p class="error">'.$errmsg.'</p>';
				}
			}

			// write to the html the form object built
			echo $form_object;

			if (($i == 1) && ($options['password_meter'])) {
				if ($input_name == ($prefix."PASSWORD"))
					$pass1_id = $unique_id;

				if ($input_name == ($prefix."PASSWORD2")) {
					echo "\n\t\t<div id=\"pass-strength-result\">".__('Strength indicator')."</div>";
					echo "\n\t\t<p class=\"description indicator-hint\">".__('Hint: The password should be at least seven characters long. To make it stronger, use upper and lower case letters, numbers and symbols like ! \" ? $ % ^ &amp; ).')."</p><br />";
					$pass2_id = $unique_id;
				}
			}

			if (!(($type != "radio") && ($type != "checkbox")))
				echo $obj_label;

			echo "\n\t</p>\n";

			if (($type == "textarea-rich") || (in_array($type, $cimy_uef_file_types)))
				echo "\t<br />\n";
		}

		$i++;
	}

	if ($tiny_mce_objects != "") {
		$mce_skin = "";
		
		require_once($cuef_plugin_dir.'/cimy_uef_init_mce.php');
	}

	if ($options['password_meter']) {
	?>
		<script type='text/javascript' src='<?php trailingslashit(get_option('siteurl'));?>wp-includes/js/jquery/jquery.js?ver=1.2.3'></script>
	<?php
		require_once($cuef_plugin_dir.'/cimy_uef_init_strength_meter.php');
	}

	if ($options['captcha'] == "securimage") {
		global $cuef_securimage_webpath;
?>
		<div style="width: 278px; float: left; height: 80px; vertical-align: text-top;">
			<img id="captcha" align="left" style="padding-right: 5px; border: 0" src="<?php echo $cuef_securimage_webpath; ?>securimage_show_captcha.php" alt="CAPTCHA Image" />
			<object type="application/x-shockwave-flash" data="<?php echo $cuef_securimage_webpath; ?>securimage_play.swf?audio=<?php echo $cuef_securimage_webpath; ?>securimage_play.php&#038;bgColor1=#fff&#038;bgColor2=#fff&#038;iconColor=#777&#038;borderWidth=1&#038;borderColor=#000" height="19" width="19"><param name="movie" value="<?php echo $cuef_securimage_webpath; ?>securimage_play.swf?audio=<?php echo $cuef_securimage_webpath; ?>securimage_play.php&#038;bgColor1=#fff&#038;bgColor2=#fff&#038;iconColor=#777&#038;borderWidth=1&#038;borderColor=#000" /></object>
			<br /><br /><br /><br />
			<a align="right" tabindex="<?php echo $tabindex; $tabindex++; ?>" style="border-style: none" href="#" onclick="document.getElementById('captcha').src = '<?php echo $cuef_securimage_webpath; ?>securimage_show_captcha.php?' + Math.random(); return false"><img src="<?php echo $cuef_securimage_webpath; ?>/images/refresh.gif" alt="<?php _e("Change image", $cimy_uef_domain); ?>" border="0" onclick="this.blur()" align="bottom" /></a>
		</div>
		<div style="width: 278px; float: left; height: 50px; vertical-align: bottom; padding: 5px;">
			<?php _e("Insert the code:", $cimy_uef_domain); ?>&nbsp;<input type="text" name="securimage_response_field" size="10" maxlength="6" tabindex="<?php echo $tabindex; $tabindex++; ?>" />
		</div>
<?php
	}

	if (($options['captcha'] == "recaptcha") && (!empty($options['recaptcha_public_key'])) && (!empty($options['recaptcha_private_key']))) {
		require_once($cuef_plugin_dir.'/recaptcha/recaptchalib.php');

	?>
			<script type='text/javascript'>
				var RecaptchaOptions = {
					lang: '<?php echo substr(get_locale(), 0, 2); ?>',
					tabindex : <?php echo strval($tabindex); $tabindex++; ?>
				};
			</script>
	<?php

		// no need if Tiny MCE is present already
		if ($tiny_mce_objects == "") {
	?>
			<script type='text/javascript'>
				var login_div = document.getElementById("login");
				login_div.style.width = "375px";
			</script>
	<?php
		}
		echo recaptcha_get_html($options['recaptcha_public_key']);
	}

	if ($upload_file_function)
		wp_print_scripts("cimy_uef_upload_file");

	cimy_switch_current_blog(true);

	echo $end_cimy_uef_comment;
}

?>