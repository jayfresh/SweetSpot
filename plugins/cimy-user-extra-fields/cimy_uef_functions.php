<?php

function get_cimyFields($wp_fields=false, $order_by_section=false) {
	global $wpdb_fields_table, $wpdb_wp_fields_table, $wpdb;

	if ($wp_fields)
		$table = $wpdb_wp_fields_table;
	else
		$table = $wpdb_fields_table;

	// only extra fields can be order by fieldset
	if (($order_by_section) && (!$wp_fields))
		$order = " ORDER BY FIELDSET, F_ORDER";
	else
		$order = " ORDER BY F_ORDER";

	// if tables exist then read all fields else array empty, will be read after the creation
	if($wpdb->get_var("SHOW TABLES LIKE '".$table."'") == $table) {
		$sql = "SELECT * FROM ".$table.$order;
		$extra_fields = $wpdb->get_results($sql, ARRAY_A);
	
		if (!isset($extra_fields))
			$extra_fields = array();
		else {
			for ($i = 0; $i < count($extra_fields); $i++) {
				$extra_fields[$i]['RULES'] = unserialize($extra_fields[$i]['RULES']);
			}
			
			$extra_fields = $extra_fields;
		}
	}
	else
		$extra_fields = array();

	return $extra_fields;
}

function set_cimyFieldValue($user_id, $field_name, $field_value) {
	global $wpdb, $wpdb_data_table, $wpdb_fields_table;

	$users = array();
	$results = array();
	$radio_ids = array();

	if (empty($field_name))
		return $results;

	$field_name = $wpdb->escape(strtoupper($field_name));

	$sql = "SELECT ID, TYPE, LABEL FROM $wpdb_fields_table WHERE NAME='$field_name'";
	$efields = $wpdb->get_results($sql, ARRAY_A);

	if ($efields == NULL)
		return $results;

	$type = $efields[0]['TYPE'];

	if ($type == "radio") {
		foreach ($efields as $ef) {
			if ($ef['LABEL'] == $field_value) {
				$field_value = "selected";
				$field_id = $ef['ID'];
			}
			else
				$radio_ids[] = $ef['ID'];
		}

		// if there are no radio fields with that label abort
		if ($field_value != "selected")
			return $results;
	}
	else if ($type == "checkbox") {
		if (($field_value) || ($field_value == "YES"))
			$field_value = "YES";
		else
			$field_value = "NO";

		$field_id = $efields[0]['ID'];
	}
	else
		$field_id = $efields[0]['ID'];

	if ($user_id) {
		$user_id = intval($user_id);
		$user_info = get_userdata($user_id);
		if (!$user_info)
			return $results;
	}
	else {
		$sql = "SELECT ID FROM $wpdb->users";
		$users = $wpdb->get_results($sql, ARRAY_A);
	}

	if (empty($users))
		$users[]["ID"] = $user_id;

	$field_value = $wpdb->escape($field_value);

	foreach ($users as $user) {
		if (!current_user_can('edit_user', $user["ID"]))
			continue;

		$sql = "SELECT ID FROM $wpdb_data_table WHERE FIELD_ID=$field_id AND USER_ID=".$user["ID"];
		$exist = $wpdb->get_var($sql);

		if ($exist == NULL)
			$sql = "INSERT INTO $wpdb_data_table SET USER_ID=".$user["ID"].", VALUE='$field_value', FIELD_ID=$field_id";
		else
			$sql = "UPDATE $wpdb_data_table SET VALUE='$field_value' WHERE FIELD_ID=$field_id AND USER_ID=".$user["ID"];


		$add_field_result = $wpdb->query($sql);

		if ($add_field_result > 0)
			$results[]["USER_ID"] = $user["ID"];

		if ($type == "radio") {
			if (!empty($radio_ids)) {
				foreach ($radio_ids as $r_id) {
					$sql2 = "UPDATE $wpdb_data_table SET VALUE='' WHERE FIELD_ID=$r_id AND USER_ID=".$user["ID"];
					$result_sql2 = $wpdb->query($sql2);
				}
			}
		}
	}

	return $results;
}

function get_cimyFieldValue($user_id, $field_name, $field_value=false) {
	global $wpdb, $wpdb_data_table, $wpdb_fields_table;
	
	$sql_field_value = "";

	if ((!isset($user_id)) || (!isset($field_name)))
		return NULL;
	
	if ($field_name) {
		$field_name = strtoupper($field_name);
		$field_name = $wpdb->escape($field_name);
	}
	
	if ($field_value) {
		if (is_array($field_value)) {
			if (isset($field_value['value'])) {
				$sql_field_value = $wpdb->escape($field_value['value']);
				
				if ($field_value['like'])
					$sql_field_value = " AND data.VALUE LIKE '%".$sql_field_value."%'";
				else
					$sql_field_value = " AND data.VALUE='".$sql_field_value."'";
			}
		} else {
		
			$field_value = $wpdb->escape($field_value);
			$sql_field_value = " AND data.VALUE='".$field_value."'";
		}
	}

	if ($user_id) {
		$user_id = intval($user_id);
		
		if (!$user_id)
			return NULL;
	}
	
	// FIELD_NAME and USER_ID provided
	if (($field_name) && ($user_id)) {
		/*
			$sql will be:
		
			SELECT	efields.LABEL,
				efields.TYPE,
				data.VALUE
		
			FROM 	<uef data table> as data
		
			JOIN	<uef fields table> as efields
		
			ON	efields.id=data.field_id
		
			WHERE	efields.name=<field_name>
				AND data.USER_ID=<user_id>
				AND efields.TYPE!='password'
				AND (efields.TYPE!='radio' OR data.VALUE!='')
				[AND data.VALUE=<field_value>]
		*/
		$sql = "SELECT efields.LABEL, efields.TYPE, data.VALUE FROM ".$wpdb_data_table." as data JOIN ".$wpdb_fields_table." as efields ON efields.id=data.field_id WHERE efields.name='".$field_name."' AND data.USER_ID=".$user_id." AND efields.TYPE!='password' AND (efields.TYPE!='radio' OR data.VALUE!='')".$sql_field_value;
	}
	
	// only USER_ID provided
	if ((!$field_name) && ($user_id)) {
		/*
			$sql will be:
		
			SELECT	efields.LABEL,
				efields.TYPE,
				efields.NAME,
				data.VALUE
		
			FROM 	<uef data table> as data
		
			JOIN	<uef fields table> as efields
		
			ON	efields.id=data.field_id
		
			WHERE	AND data.USER_ID=<user_id>
				AND efields.TYPE!='password'
				AND (efields.TYPE!='radio' OR data.VALUE!='')
				[AND data.VALUE=<field_value>]
		
			ORDER BY efields.F_ORDER
		*/
		$sql = "SELECT efields.LABEL, efields.TYPE, efields.NAME, data.VALUE FROM ".$wpdb_data_table." as data JOIN ".$wpdb_fields_table." as efields ON efields.id=data.field_id WHERE data.USER_ID=".$user_id." AND efields.TYPE!='password' AND (efields.TYPE!='radio' OR data.VALUE!='')".$sql_field_value." ORDER BY efields.F_ORDER";
	}
	
	// only FIELD_NAME provided
	if (($field_name) && (!$user_id)) {
		/*
			$sql will be:
		
			SELECT	efields.LABEL,
				efields.TYPE,
				users.ID as user_id,
				users.user_login,
				data.VALUE
		
			FROM 	<wp users table> as users,
				<uef data table> as data
		
			JOIN	<uef fields table> as efields
		
			ON	efields.id=data.field_id
		
			WHERE	efields.name=<field_name>
				AND data.USER_ID=users.ID
				AND efields.TYPE!='password'
				AND (efields.TYPE!='radio' OR data.VALUE!='')
				[AND data.VALUE=<field_value>]
		
			ORDER BY users.user_login
		*/
		$sql = "SELECT efields.LABEL, efields.TYPE, users.ID as user_id, users.user_login, data.VALUE FROM ".$wpdb->users." as users, ".$wpdb_data_table." as data JOIN ".$wpdb_fields_table." as efields ON efields.id=data.field_id WHERE efields.name='".$field_name."' AND users.ID=data.USER_ID AND efields.TYPE!='password' AND (efields.TYPE!='radio' OR data.VALUE!='')".$sql_field_value." ORDER BY users.user_login";
	}
	
	// nothing provided
	if ((!$field_name) && (!$user_id)) {
		/*
			$sql will be:
		
			SELECT	users.ID as user_id,
				users.user_login,
				efields.NAME,
				efields.LABEL,
				efields.TYPE,
				data.VALUE
		
			FROM 	<wp users table> as users,
				<uef data table> as data
		
			JOIN	<uef fields table> as efields
		
			ON	efields.id=data.field_id
		
			WHERE	data.USER_ID=users.ID
				AND efields.TYPE!='password'
				AND (efields.TYPE!='radio' OR data.VALUE!='')
				[AND data.VALUE=<field_value>]
		
			ORDER BY users.user_login,
				efields.F_ORDER
		*/
		$sql = "SELECT users.ID as user_id, users.user_login, efields.NAME, efields.LABEL, efields.TYPE, data.VALUE FROM ".$wpdb->users." as users, ".$wpdb_data_table." as data JOIN ".$wpdb_fields_table." as efields ON efields.id=data.field_id WHERE users.ID=data.USER_ID AND efields.TYPE!='password' AND (efields.TYPE!='radio' OR data.VALUE!='')".$sql_field_value." ORDER BY users.user_login, efields.F_ORDER";
	}

	$field_data = $wpdb->get_results($sql, ARRAY_A);
	
	if (isset($field_data)) {
		if ($field_data != NULL)
			$field_data = $field_data;
	}
	else
		return NULL;

	$field_data = cimy_change_radio_labels($field_data);

	if (($field_name) && ($user_id))
		$field_data = $field_data[0]['VALUE'];

	return $field_data;
}

function cimy_change_radio_labels($field_data) {
	$i = 0;
	
	while ($i < count($field_data)) {
		if ($field_data[$i]['TYPE'] == "radio") {
			$field_data[$i]['VALUE'] = $field_data[$i]['LABEL'];
		}
		else if (($field_data[$i]['TYPE'] == "dropdown") || ($field_data[$i]['TYPE'] == "dropdown-multi")) {
			$ret = cimy_dropDownOptions($field_data[$i]['LABEL'], false);
			
			$field_data[$i]['LABEL'] = $ret['label'];
		}
		
		$i++;
	}
	
	return $field_data;
}

function cimy_get_formatted_date($value, $date_format="%d %B %Y @%H:%M") {
	$locale = get_locale();

	if (stristr($locale, ".") === false)
		$locale2 = $locale.".utf8";
	else
		$locale2 = "";

	setlocale(LC_TIME, $locale, $locale2);

	if (($value == "") || (!isset($value)))
		$registration_date = "";
	else
		$registration_date = strftime($date_format, intval($value));

	return $registration_date;
}

function cimy_dropDownOptions($values, $selected) {
	
	$label_pos = strpos($values, "/");
	
	if ($label_pos) {
		$label = substr($values, 0, $label_pos);
		$values = substr($values, $label_pos + 1);
	}
	else
		$label = "";
	
	$items = explode(",", $values);
	$sel_items = explode(",", $selected);
	$html_options = "";
	$sel_i = 0;
	
	foreach ($items as $item) {
		$item_clean = trim($item, "\t\n\r");

		$html_options.= "\n\t\t\t";
		$html_options.= '<option value="'.$item_clean.'"';
	
		if  (isset($sel_items[$sel_i])) {
			if ($sel_items[$sel_i] == $item_clean) {
				$sel_i++;
				$html_options.= ' selected="selected"';
			}
		}

		$html_options.= ">".$item_clean."</option>";
	}
	
	$ret = array();
	$ret['html'] = $html_options;
	$ret['label'] = $label;
	
	return $ret;
}

function cimy_get_thumb_path($file_path, $oldname=false) {
	$file_path_purename = substr($file_path, 0, strrpos($file_path, "."));
	$file_path_ext = strtolower(substr($file_path, strlen($file_path_purename)));
	
	if ($oldname)
		$file_thumb_path = $file_path_purename.".thumbnail".$file_path_ext;
	else
		$file_thumb_path = $file_path_purename."-thumbnail".$file_path_ext;
	
	return $file_thumb_path;
}

function cimy_uef_sanitize_content($content, $override_allowed_tags=null) {
	global $allowedtags;

	if (is_array($override_allowed_tags))
		$cimy_allowedtags = $override_allowed_tags;
	else
		$cimy_allowedtags = $allowedtags;

	$content = wp_kses($content, $cimy_allowedtags);
	$content = wptexturize($content);

	return $content;
}

function cimy_check_admin($permission) {
	global $cimy_uef_plugins_dir;

	if ((is_multisite()) && ($cimy_uef_plugins_dir == "mu-plugins"))
		return is_super_admin();
	else
		return current_user_can($permission);
	
	return false;
}

function cimy_fieldsetOptions($selected=0, $order="") {
	global $cimy_uef_domain;

	if (!cimy_check_admin('manage_options'))
		return;

	$options = cimy_get_options();

	$i = 0;
	$html = "<select name=\"fieldset[".$order."]\">\n";

	if ($options['fieldset_title'] == "") {
		$html.= "\t<option value=\"$i\" selected=\"selected\">".__("no fieldset", $cimy_uef_domain)."</option>\n";
	}
	else {
		$fieldset_titles = explode(',', $options['fieldset_title']);

		foreach ($fieldset_titles as $fieldset) {
			if ($i == $selected)
				$selected_txt = " selected=\"selected\"";
			else
				$selected_txt = "";
	
			$html.= "\t<option value=\"$i\"".$selected_txt.">".$fieldset."</option>\n";
			$i++;
		}
	}

	$html.= "</select>";

	return $html;
}

function cimy_switch_to_blog($meta=array()) {
	global $cimy_uef_plugins_dir;

	if ((is_multisite()) && ($cimy_uef_plugins_dir == "plugins")) {
		if (isset($meta["blog_id"]))
			$mu_blog_id = intval($meta["blog_id"]);
		else if (isset($_GET["blog_id"]))
			$mu_blog_id = intval($_GET["blog_id"]);
		else if (isset($_POST["blog_id"]))
			$mu_blog_id = intval($_POST["blog_id"]);
		else
			$mu_blog_id = 1;

		if (cimy_uef_mu_blog_exists($mu_blog_id)) {
			if (switch_to_blog($mu_blog_id))
				cimy_uef_set_tables();
			else
				$mu_blog_id = 1;
		}
		else
			$mu_blog_id = 1;
	}
}

function cimy_switch_current_blog($hidden_field=false) {
	global $switched, $blog_id;

	if (isset($switched)) {
		if ($hidden_field)
			echo "\t<input type=\"hidden\" name=\"blog_id\" value=\"".$blog_id."\" />\n";

		//restore_current_blog();
	}
}

?>