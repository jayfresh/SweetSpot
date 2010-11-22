<?php
/*
Plugin Name: MapPress Easy Google Maps
Plugin URI: http://www.wphostreviews.com/mappress
Author URI: http://www.wphostreviews.com/mappress
Description: MapPress makes it easy to insert Google Maps in WordPress posts and pages.
Version: 1.6.1
Author: Chris Richardson
*/

/*
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the license.txt file for details.
*/

// ----------------------------------------------------------------------------------
// Class mappress - plugin class
// ----------------------------------------------------------------------------------
class mappress {
	var $wordpress_tag = 'mappress-google-maps-for-wordpress';    // tag assigned by wordpress.org
	var $version = '1.6.1';
	var $doc_link = 'http://wphostreviews.com/mappress/mappress-documentation-144';
	var $bug_link = 'http://wphostreviews.com/mappress/chris-contact';
	var $map_defaults = array ('icons_url' => '', 'api_key' => '', 'country' => '', 'size' => 'MEDIUM', 'width' => 0, 'height' => 0, 'zoom' => 0, 'center_lat' => 0, 'center_lng' => 0,
								'address_format' => 'CORRECTED', 'bigzoom' => 1, 'googlebar' => 0, 'scrollwheel_zoom' => 0, 'language' => '',
								'maptypes' => 0, 'directions' => 1, 'maptype' => 'normal', 'streetview' => 1, 'traffic' => 1, 'open_info' => 0, 'default_icon' => '', 'poweredby' => 1);
	var $map_sizes = array ('SMALL' => array('width' => 300, 'height' => 225),
							'MEDIUM' => array('width' => 400, 'height' => 300),
							'LARGE' => array('width' => 640, 'height' => 480) );

	var $div_num = 0;    // Current map <div>
	var $plugin_page = '';

	function mappress()  {
		global $wpdb, $wp_version;

		// help_debug=errors -> PHP errors, help_debug=info -> phpinfo + args, help_debug=maps -> maps, help_debug=script -> script
        $this->debug = (isset($_GET['help_debug'])) ? $_GET['help_debug'] : null;
        if ($this->debug == 'errors') {
            error_reporting(E_ALL);
            ini_set('error_reporting', E_ALL);
            ini_set('display_errors','On');
        } elseif ($this->debug == 'info') {
            $bloginfo = array('version', 'language', 'stylesheet_url', 'wpurl', 'url');
            foreach ($bloginfo as $key=>$info)
                echo "$info: " . bloginfo($info) . '<br \>';
            phpinfo();
        }

		// This plugin doesn't work for feeds!
		if (is_feed())
			return;

		load_plugin_textdomain('mappress', false, dirname(plugin_basename( __FILE__ )) . '/languages');

		// Notices
		add_action('admin_notices', array(&$this, 'hook_admin_notices'));

		// Install and activate
		register_activation_hook(__FILE__, array(&$this, 'hook_activation'));
		add_action('admin_menu', array(&$this, 'hook_admin_menu'));

		// Shortcode processing
		add_shortcode('mappress', array(&$this, 'map_shortcodes'));

		// Post save hook for saving maps
		add_action('save_post', array(&$this, 'hook_save_post'));

		// Non-admin scripts & stylesheets
		add_action("wp_print_scripts", array(&$this, 'hook_print_scripts'));
		add_action("wp_print_styles", array(&$this, 'hook_print_styles'));
		add_action('wp_head', array(&$this, 'hook_head'));

		// Uninstall
		register_uninstall_hook(__FILE__, array(&$this, 'hook_uninstall'));
	}

	/**
	* Add admin menu and admin scripts/stylesheets
	* Admin script - post edit and options page
	* Content script - content (and also post-edit map)
	* CSS - content, plugins, post-edit
	*
	*/
	function hook_admin_menu() {
		// Add menu
		$mypage = add_options_page('MapPress', 'MapPress', 'manage_options', 'mappress', array(&$this, 'admin_menu'));
		$this->plugin_page = $mypage;

		// Post edit shortcode boxes - note that this MUST be admin_menu call
		add_meta_box('mappress', 'MapPress', array(&$this, 'meta_box'), 'post', 'normal', 'high');
		add_meta_box('mappress', 'MapPress', array($this, 'meta_box'), 'page', 'normal', 'high');
		add_meta_box('mappress', 'MapPress', array($this, 'meta_box'), 'properties', 'normal', 'high');


		// Add scripts & styles for admin pages
		add_action("admin_print_scripts-$mypage", array(&$this, 'hook_admin_print_scripts'));
		add_action("admin_print_scripts-post.php", array(&$this, 'hook_admin_print_scripts'));
		add_action("admin_print_scripts-post-new.php", array(&$this, 'hook_admin_print_scripts'));
		add_action("admin_print_scripts-page.php", array(&$this, 'hook_admin_print_scripts'));
		add_action("admin_print_scripts-page-new.php", array(&$this, 'hook_admin_print_scripts'));

		add_action("admin_print_styles-$mypage", array(&$this, 'hook_admin_print_styles'));
		add_action("admin_print_styles-post.php", array(&$this, 'hook_admin_print_styles'));
		add_action("admin_print_styles-post-new.php", array(&$this, 'hook_admin_print_styles'));
		add_action("admin_print_styles-page.php", array(&$this, 'hook_admin_print_styles'));
		add_action("admin_print_styles-page-new.php", array(&$this, 'hook_admin_print_styles'));
	}

	/**
	* Scripts for non-admin screens
	*
	*/
	function hook_print_scripts() {

		$key = $this->get_array_option('api_key', 'map_options');
		$lang = $this->get_array_option('language', 'map_options');

		// Only load for non-admin, non-feed
		if (is_admin() || is_feed()) {
			return;
		}

		if ($this->debug == 'maps') {
			echo "\r\n<!-- mappress - print_scripts: API key = $key, language = $lang -->\r\n";
			$result = $this->all_maps();
			echo "<!-- mappress - all_maps(): ";
			print_r($result);
			echo "-->\r\n ";
		}

		// Only load if API key isn't empty'
		if (empty($key))
			return;

		// Only load scripts if at least one post has map coordinates (we don't check if map shortcode is present, though)
		if (!$this->has_maps())

		if (!$this->has_maps())
			//return; JRL: debug;

		wp_enqueue_script('googlemaps', "http://maps.google.com/maps?file=api&v=2&key=$key&hl=$lang");

		if (substr($this->debug, 0, 4) == 'http')
			$script = $this->debug;
		else
			$script = plugins_url('mappress.js', __FILE__);

		wp_enqueue_script('mappress', $script);
		wp_enqueue_script('mapcontrol', plugins_url('mapcontrol.js', __FILE__));

		// Stylesheet
		wp_enqueue_style('mappress', plugins_url('mappress.css', __FILE__));

		// Localize script texts
		wp_localize_script('mappress', 'mappressl10n', array(
			'dir_400' => __('Google error: BAD REQUEST', 'mappress'),
			'dir_500' => __('Google internal error.  Try again later.', 'mappress'),
			'dir_601' => __('The starting or ending address was missing.', 'mappress'),
			'dir_602' => __('The starting or ending address could not be found.', 'mappress'),
			'dir_603' => __('Google cannot return those directions for legal or contractual reasons', 'mappress'),
			'dir_604' => __('Google cannot return directions between those addresses.  There is no route between them or the routing information is not available.', 'mappress'),
			'dir_610' => __('Invalid map API key', 'mappress'),
			'dir_620' => __('Your key has issued too many queries in one day.', 'mappress'),
			'dir_default' => __('Unknown error, unable to return directions.  Status code = ', 'mappress'),
			'enter_address' => __('Enter address'),
			'no_address' => __('No matching address', 'mappress'),
			'did_you_mean' => __('Did you mean: ', 'mappress'),
			'street_603' => __('Error: your browser does not seem to support the street view Flash player', 'mappress'),
			'street_600' => __('Sorry, no street view data is available for this location', 'mappress'),
			'street_default' => __('Sorry, Google was unable to display the street view in your browser', 'mappress'),
			'street_view' => __('Street view', 'mappress'),
			'directions' => __('Get directions', 'mappress'),
			'address' => __('Address', 'mappress'),
			'to_here' => __('to here', 'mappress'),
			'from_here' => __('from here', 'mappress'),
			'go' => __('Go', 'mappress')
		));
	}

	/**
	* Stylesheets for non-admin pages
	*
	*/
	function hook_print_styles() {
		// Only load for non-admin, non-feed
		if (is_admin() || is_feed())
			return;

		// Only load stylesheets if at least one post has map coordinates (we don't check if map shortcode is present, though)
		if (!$this->has_maps())
			//return; JRL: debug;

		wp_enqueue_style('mappress', plugins_url("mappress.css", __FILE__));
    }


	/**
	* Scripts only for our specific admin pages
	*
	*/
	function hook_admin_print_scripts() {

		if ($this->debug == 'maps')
			echo "\r\n<!-- mappress - hook_admin_print_scripts() -->\r\n";

		// We need maps API to validate the key on options page; key may be being updated in $_POST when we hit this event
		if (isset($_POST['api_key']))
			$key = $_POST['api_key'];
		else
			$key = $this->get_array_option('api_key', 'map_options');

		$lang = $this->get_array_option('language', 'map_options');

		if (!empty($key))
			wp_enqueue_script('googlemaps', "http://maps.google.com/maps?file=api&v=2&key=$key&hl=$lang");

		wp_enqueue_script('mappress_admin', plugins_url('mappress_admin.js', __FILE__), array('jquery-ui-core', 'jquery-ui-dialog'));
		wp_enqueue_script('mappress', plugins_url('mappress.js', __FILE__), array('jquery-ui-core', 'jquery-ui-dialog'));

		$script = plugins_url('mappress.js', __FILE__);

		wp_localize_script('mappress', 'mappress' . 'l10n', array(
			'api_missing' => __('Please enter your API key. Need an API key?  Get one ', 'mappress'),
			'api_incompatible' => __('MapPress could not load google maps.  Either your browser is incompatible or your API key is invalid.  Need an API key?  Get one ', 'mappress'),
			'here' => __('here', 'mappress'),
			'no_address' => __('No matching address', 'mappress'),
			'address_exists' => __('That address is already on the map : ', 'mappress'),
			'edit' => __('Edit', 'mappress'),
			'save' => __('Save', 'mappress'),
			'cancel' => __('Cancel', 'mappress'),
			'del' => __('Delete', 'mappress'),
			'enter_location' => __('Please enter a location to map', 'mappress'),
			'title' => __('Title', 'mappress'),
			'delete_this_marker' => __('Delete this map marker?', 'mappress'),
			'select_icon' => __('Press escape or click here to cancel: ', 'mappress'),
			'currently_mapped' => __('Currently mapped', 'mappress')
		));

		// Add action to load our geocoder and icons declarations that can't be enqueued
		add_action('admin_head', array(&$this, 'hook_head'));
		if ($this->debug == 'maps')
			echo "\r\n<!-- mappress - hook_admin_print_scripts() - add_action wp_head() -->\r\n";
	}

	function hook_admin_print_styles() {
	    wp_enqueue_style('mappress', plugins_url('mappress.css', __FILE__));
	}

	/**
	* Add js declarations since they can't be 'enqueued', needed by both admin and regular pages
	*
	*/
	function hook_head() {
		$key = $this->get_array_option('api_key', 'map_options');

		if ($this->debug == 'maps')
			echo "\r\n<!-- mappress - hook_head(): API key = $key -->\r\n";

		// For non-admin pages ONLY: load scripts only if at least one post has map coordinates (we don't check if map shortcode is present, though)
		/* if(is_admin()) {
			echo "<!-- is_admin true -->";
		} else {
			echo "<!-- is_admin false -->";
		} 
		if (is_admin()) { // JRL: debug - was !is_admin()
			// && !$this->has_maps()) JRL: debug
			return;
		}
		echo "dddddd2";
		*/
		// Do nothing if no API key available
		if (empty($key))
			return;

		// Load geocoder
		$script = "var mappGeocoder = new GClientGeocoder();";
		$country = $this->get_array_option('country', 'map_options');
		if (!empty($country))
			$script .= "mappGeocoder.setBaseCountryCode('$country'); ";
		echo $this->script($script);

		// Load needed icons
		$this->icons = $this->get_array_option('icons');
		if (empty($this->icons)) {
			$this->icons = mpicon::read($url, 'icons.txt');
			$this->update_array_option('icons', $this->icons);
		}

		// Only declare the icons needed to render current page
		// TODO : extend this code for marker icons - or better yet replace with AJAX call to fetch only what we need for each map
		$default_icon = $this->get_array_option('default_icon', 'map_options');

		if ($this->debug == 'maps') {
			echo "\r\n<!-- mappress - hook_head(): default icon = $default_icon, iconslist = ";
			print_r($this->icons);
			echo "-->\r\n";
		}

		$default_icon = $this->get_array_option('default_icon', 'map_options');
        if ($default_icon) {
		    $needed_icons = array($this->icons[$default_icon]);
		    mpicon::draw($needed_icons);
        }

		// Load map sizes
        $script = "var mapSizes = " . mapp_json_encode($this->map_sizes);
        echo $this->script($script);
		if ($this->debug == 'maps')
			echo "\r\n<!-- mappress - hook_head(): map size = " . mapp_json_encode($this->map_sizes) . "-->\r\n";
	}

	function hook_activation() {
		// upgrade
		$current_version = $this->get_array_option('version');

		// Re-read the icon list (format may change)
		$this->icons = mpicon::read($url, 'icons.txt');
		$this->update_array_option('icons', $this->icons);

		// Save current version #
		$this->update_array_option('version', $this->version);
	}

	/**
	* Delete all option on uninstall
	*
	*/
	function hook_uninstall() {
		update_options('mappress', '');
	}

	function hook_save_post($post_id) {
		// This hook gets triggered on autosaves, but WP doesn't populate all of the _POST variables (sigh)
		// So ignore it unless at least one of our fields is set.
		if (!isset($_POST['mapp_zoom']))
			return;

		delete_post_meta($post_id, '_mapp_map');
		delete_post_meta($post_id, '_mapp_pois');

		// Process map header fields.  Filter out empty strings so as not to affect shortcode_atts() calls later
		if (!empty($_POST['mapp_size']))
			$map['size'] = $_POST['mapp_size'];
		if (!empty($_POST['mapp_maptype']))
			$map['maptype'] = $_POST['mapp_maptype'];
		if (!empty($_POST['mapp_width']))
			$map['width'] = $_POST['mapp_width'];
		if (!empty($_POST['mapp_height']))
			$map['height'] = $_POST['mapp_height'];
		if (!empty($_POST['mapp_zoom']))
			$map['zoom'] = $_POST['mapp_zoom'];
		if (!empty($_POST['mapp_center_lat']))
			$map['center_lat'] = $_POST['mapp_center_lat'];
		if (!empty($_POST['mapp_center_lng']))
			$map['center_lng'] = $_POST['mapp_center_lng'];

		update_post_meta($post_id, '_mapp_map', $map);

		// Process POIs
		$addresses = (array) $_POST['mapp_poi_address'];
		foreach($addresses as $key=>$address) {
			// Get the data for the POI.
			$caption = $_POST['mapp_poi_caption'][$key];
			$body = $_POST['mapp_poi_body'][$key];
			$corrected_address = $_POST['mapp_poi_corrected_address'][$key];
			$lat = $_POST['mapp_poi_lat'][$key];
			$lng = $_POST['mapp_poi_lng'][$key];
			$boundsbox_north = $_POST['mapp_poi_boundsbox_north'][$key];
			$boundsbox_south = $_POST['mapp_poi_boundsbox_south'][$key];
			$boundsbox_west = $_POST['mapp_poi_boundsbox_west'][$key];
			$boundsbox_east = $_POST['mapp_poi_boundsbox_east'][$key];

			// If somehow we didn't get lat/lng then skip this POI
			if (empty($lat) || empty($lng))
				continue;

			// Add the POI to our array for the metadata
			$pois[] = array('address' => $address, 'caption' => $caption, 'body' => $body,
							'corrected_address' => $corrected_address, 'lat' => $lat, 'lng' => $lng,
							'boundsbox' => array('north' => $boundsbox_north, 'south' => $boundsbox_south, 'east' => $boundsbox_east,
							'west' => $boundsbox_west));
		}

		if (!empty($pois))
			update_post_meta($post_id, '_mapp_pois', $pois);
	}

	/**
	* Hook: admin notices
	* Used for upgrade notification
	*/
	function hook_admin_notices() {
		global $pagenow;

		// Check if API key entered; it may be in process of being updated
		if (isset($_POST['api_key']))
			$key = $_POST['api_key'];
		else
			$key = $this->get_array_option('api_key', 'map_options');

		if (empty($key)) {
			echo "<div id='error' class='error'><p>"
			. __("MapPress isn't ready yet.  Please enter your Google Maps API Key on the ", 'mappress')
			. "<a href='options-general.php?page=mappress'>"
			. __("MapPress options screen.", 'mappress') . "</a></p></div>";

			return;
		}
	}

	function has_maps() {
		global $posts;

		if ($this->debug == 'maps')
			echo "\r\n<!-- mappress - begin has_maps() - version = " . $this->version . "-->\r\n";

		$found = false;
		
		if (empty($posts))
			return false;

		// echo count($posts);
		// foreach($posts as $key=>$post) {
			//print_r(get_ept_field('_mapp_pois', array('raw'=>'true')));
			//echo $post->ID;
			//if (get_post_meta($post->ID, '_mapp_pois', true)) {
			//	$found = true;
			// }
		//}

		//if($found)
		//	echo "true";
		//else
		//	echo "false";

		if ($this->debug == 'maps')
			echo "\r\n<!-- mappress - has_maps() result: $found -->\r\n";

		return $found;
	}

	function all_maps() {
		global $wpdb;
		$sql = "SELECT m.post_id, p.post_title FROM $wpdb->postmeta m, $wpdb->posts p "
				. " WHERE m.meta_key = '_mapp_pois' AND m.post_id = p.id AND m.meta_value != '' AND p.post_content like '%[mappress]%' AND p.post_status = 'publish'";
		$results = $wpdb->get_results($sql);

		foreach ((array)$results as $result)
			$all_maps[] = get_permalink($result->post_id);

		return $all_maps;
	}

	function get_debug() {
		$result = $this->all_maps();
		return array($result, count($result));
	}

	/**
	* Shortcode form for post edit screen
	*
	*/
	function meta_box($post) {
		$map = get_post_meta($post->ID, '_mapp_map', true);
		$pois = get_post_meta($post->ID, '_mapp_pois', true);
		//echo "_mapp_pois: "; 
		//print_r($pois);

		// Load the edit map
		// Note that mapTypes is hardcoded = TRUE (so user can change type, even if not displayed in blog)
		$map['maptypes'] = 1;
		$this->map($map, $pois, true);

		// The <div> will be filled in with the list of POIs
		echo "<div id='admin_poi_div'></div>";
	}

	function output_map_sizes($selected = "", $width = 0, $height = 0) {

		// Output small/med/large
		foreach ($this->map_sizes as $key => $ms) {
			$checked = ($selected == $key) ? "checked = 'checked'" : $checked = "";

			if ($key == 'SMALL')
				$label = __('Small', 'mappress');
			if ($key == 'MEDIUM')
				$label = __('Medium', 'mappress');
			if ($key == 'LARGE')
				$label = __('Large', 'mappress');

			echo "<input type='radio' name='mapp_size' id='mapp_size' value='$key' $checked />$label ({$ms['width']}x{$ms['height']}) ";
		}

		// Output 'custom' option
		$checked = ($selected == 'CUSTOM') ? "checked = 'checked'" : $checked = "";
		echo "<br/><input type='radio' name='mapp_size' id='mapp_size' value='CUSTOM' $checked />" . __('Custom', 'mappress');

		// Output custom width and height
        if ($width == 0 && $height == 0) {
            $width = $this->map_sizes['SMALL']['width'];
            $height = $this->map_sizes['SMALL']['height'];
        }

		echo "<input type='text' name='mapp_width' id='mapp_width' size='2' value='$width' />";
		echo " x <input type='text' name='mapp_height' id='mapp_height' size='2' value='$height' />";
	}

	/**
	* Map a shortcode in a post.  Called by WordPress shortcode processor.
	*
	* @param mixed $atts - shortcode attributes
	*/
	function map_shortcodes($atts='') {
		global $id;

		if ($this->debug == 'maps')
			echo "\r\n<!-- mappress - map_shortcodes() begin -->\r\n";

		if (is_feed())
			return;

		$map = get_post_meta($id, '_mapp_map', true);
		$pois = get_post_meta($id, '_mapp_pois', true);

		$result = $this->map($map, $pois, false);

		if ($this->debug == 'maps') {
			echo "\r\n<!-- mappress - map_shortcodes() processing \r\n";
			print_r($map);
			print_r($pois);
			echo "output map = " . $result . "-->\r\n";
		}

		return $result;
	}

	function map($map, $pois, $editable = false) {
		if ($this->debug == 'maps') {
			echo "\r\n<!-- mappress - map(): editable = $editable";
			print_r($map);
			print_r($pois);
			echo " -->\r\n";
		}

		$map_args = $this->map_defaults;
		$map_args = shortcode_atts($map_args, $this->get_array_option('map_options'));
		$map_args = shortcode_atts($map_args, $map);

		if ($editable) {
			$map_name = 'editMap';
			$map_args['editable'] = 1;
		} else {
			$map_name = 'mappress' . $this->div_num;
			$this->div_num++;
			$map_args['editable'] = 0;
		}

        if (empty($map_args['size']))
            $map_args['size'] = 'SMALL';

		$args = array("mapname" => $map_name, "editable" => $map_args['editable'], "size" => $map_args['size'], "width" => $map_args['width'], "height" => $map_args['height'],
		"zoom" => $map_args['zoom'], "centerLat" => $map_args['center_lat'], "centerLng" => $map_args['center_lng'],
		"addressFormat" => $map_args['address_format'], "bigZoom" => $map_args['bigzoom'], "googlebar" => 0, 'scrollWheelZoom' => $map_args['scrollwheel_zoom'],
		"language" => $map_args['language'], "mapTypes" => $map_args['maptypes'], "directions" => $map_args['directions'], "mapType" => $map_args['maptype'],
		"streetView" => $map_args['streetview'], "traffic" => $map_args['traffic'], "initialOpenInfo" => $map_args['open_info'],
		"defaultIcon" => $map_args['default_icon'], "pois" => $pois);

		$args = mapp_json_encode($args);

		if ($this->debug == 'maps')
			echo "\r\n<!-- mappress - map() json_encode = $args -->\r\n";

		// If we couldn't encode just give up
		if (empty($args))
			return;

		if ($this->debug == 'maps')
			echo "\r\n<!-- mappress - map() args are not empty -->\r\n";

		if ($editable) {
			?>
				<div>
					<div style="float:right">
                        <div>
						    <a target='_blank' href='<?php echo $this->bug_link ?>'><?php _e('Report a bug', 'mappress')?></a>
						    | <a target='_blank' href='<?php echo $this->doc_link ?>'><?php _e('MapPress help', 'mappress')?></a>
                        </div>
                        <div>
                            <input id='mapp_paypal' type="image" src="https://www.paypal.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="donate" alt="PayPal - The safer, easier way to pay online!" />
                            <br/>
                            <b><?php echo __("Please donate today!", 'mappress') ?></b>
                        </div>
					</div>

					<p><b><?php _e('Map size', 'mappress') ?></b></p>
					<?php $this->output_map_sizes($map_args['size'], $map_args['width'], $map_args['height']); ?>
					<br />
					<input type="hidden" size="2" name="mapp_zoom" id="mapp_zoom" value="<?php echo $map['zoom'] ?>" />
					<input type="hidden" size="20" name="mapp_maptype" id="mapp_maptype" value="<?php echo $map['maptype'] ?>" />
					<input type="hidden" size="10" name="mapp_center_lat" id="mapp_center_lat" value="<?php echo $map['center_lat'] ?>" />
					<input type="hidden" size="10" name="mapp_center_lng" id="mapp_center_lng" value="<?php echo $map['center_lng'] ?>" />

					<p class="submit" style="padding: 0; float: none" >
						<input type="button" id="mapp_insert" value="<?php _e('Insert map shortcode in post &raquo;', 'mappress'); ?>" />
					</p>
				</div>

				<div>
					<p><b><?php _e('Add locations', 'mappress') ?></b></p>
					<?php _e('Street Address, city, state, country, or place', 'mappress') ?><br />
					<input style="width:80%" type="text" name="mapp_input_address" id="mapp_input_address" value="" />
					<br />
					<?php _e('Latitude/Longitude ', 'mappress') ?><br />
					<input type="text" name="mapp_input_lat" id="mapp_input_lat" value="" />
					<input type="text" name="mapp_input_lng" id="mapp_input_lng" value="" />
					<p class="submit" style="padding: 0; float: none">
						<input type="button" id="mapp_add_location" value="<?php _e('Add location', 'mappress') ?>" />
					</p>

					<p id="mapp_message"></p>

					<p><b><?php _e('Preview', 'mappress') ?></b><br /></p>

					<p class="submit" style="padding: 0; float: none">
						<input type="button" id="mapp_recenter" value="<?php _e('Center map', 'mappress'); ?>" />
					</p>
					<br />
					<div id="<?php echo $map_name ?>" class="mapp-div"></div>
					<p><b><?php _e('Currently mapped', 'mappress') ?></b></p>
					<?php _e('Click on a marker or use the links below to edit or delete markers.', 'mappress') ?>
				</div>
			<?php

			echo $this->script("var $map_name = new mappEdit($args);");

		// Display maps
		} else {
			if ($this->debug == 'maps')
				echo "\r\n<!-- mappress - map() preparing map display code -->\r\n";

			// If there are no POIs then don't try to draw the map at all
			if (empty($pois))
				return;

			if ($this->debug == 'maps')
				echo "\r\n<!-- mappress - map() preparing map display - pois not empty -->\r\n";

			$map = "<div id='$map_name' class='mapp-div'></div>";
			$map .= $this->script("var $map_name = new mappDisplay($args);");

			if ($this->debug == 'maps')
				echo "\r\n<!-- mappress - map() map script issued - $map -->\r\n";

			if ($map_args['poweredby'])
				$map .= "";

			if ($map_args['directions']) {
				$map .= "<div id='{$map_name}_directions_outer_div' class='mapp-directions-div' style='display:none;'>";
				$map .= "<div style='float:right'><a href='#' onclick='{$map_name}.directionsClose(); return false;'>Close</a></div>";
				$map .= "<br />";

				$map .= "<form onsubmit='return false;' action='' >"
						."<table style='width:100%'>"
						."<tr>"
						."<td style='width: 32px'><img src='http://maps.google.com/intl/en_us/mapfiles/icon_greenA.png' alt='start' style='vertical-align:middle' /></td>"
						."<td><input type='text' id='{$map_name}_saddr' value='' class='mapp-address' style='width:100%'/>"
						."<p id='{$map_name}_saddr_corrected' class='mapp-address-corrected'></p></td>"
						."</tr>"

						."<tr>"
						."<td style='width: 32px'><img src='http://maps.google.com/intl/en_us/mapfiles/icon_greenB.png' alt='end' style='vertical-align:middle' /></td>"
						."<td><input type='text' id='{$map_name}_daddr' value='' class='mapp-address' style='width:100%' />"
						."<p id='{$map_name}_daddr_corrected' class='mapp-address-corrected'></p></td>"
						."</tr>"

						."</table>"

						."<input type='submit' value='" . __('Get Directions', 'mappress') . "' onclick='{$map_name}.directionsGet(); return false;' />"
						."<input type='submit' value='" . __('Print Directions', 'mappress') . "' onclick='{$map_name}.directionsPrint(); return false;' />"
						."</form>";

				$map .= "<div id='{$map_name}_directions_div'></div>";
				$map .= "</div>";
			}

			if ($this->debug == 'maps')
				echo "\r\n<!-- mappress - map(): returns = $map -->\r\n";

			return $map;
		}
	}

    function script($script) {
        $cr = "\r\n";

        // Note: commented lines can be removed only when WordPress fixes TRAC #3670
        $html = "$cr <script type='text/javascript'>"
//            . "$cr /* <![CDATA[ */ $cr"
            . $script
//            . "$cr /* ]]> */ "
            . "$cr </script> $cr";
        return $html;
    }

	/**
	* Get option value.  Options are stored under a single key
	*/
	function get_array_option($option, $subarray='') {
		$options = get_option('mappress');
		if (empty($options))
			return false;

		if ($subarray) {
			if (isset($options[$subarray][$option]))
				return $options[$subarray][$option];
			else
				return false;
		}

		// No subarray
		if (isset($options[$option]))
			return $options[$option];
		else
			return false;

		// If we get here it's an error
		return false;
	}

	/**
	* Set option value.  Options are stored as an array under a single key
	*/
	function update_array_option($option, $value) {
		$options = get_option('mappress');
		$options[$option] = $value;
		update_option('mappress', $options);
	}

	/**
	* Delete option value from option array.
	*
	*/
	function delete_array_option($option) {
		$options = get_option('mappress');
		if (isset($options[$option])) {
			unset ($options[$option]);
			update_option('mappress', $options);
			return true;
		}

		return false;
	}

	/**
	* Options page
	*
	*/
	function admin_menu() {
		if ( !current_user_can('manage_options') )
			die ( __( "ACCESS DENIED: You don't have permission to do this.", 'MapPress') );

		// If user hasn't specificed a URL for the icons, use plugin directory
		$url = $this->get_array_option('icons_url');
		if (empty($url) || $url == false)
            $url = plugins_url('/icons', __FILE__);

		// Read icons
		$this->icons = mpicon::read($url, 'icons.txt');
		if ($this->icons === false)
			$error = "Unable to read icons.  Check that the icons.txt file exists and does not have any errors.";

		// Save options
		if (isset($_POST['save'])) {
			check_admin_referer('mappress');

			foreach($_POST as $key=>$value)
				if (!empty($_POST[$key]) || $_POST[$key] === '0')
					$new_options[$key] = strip_tags(mysql_real_escape_string ($_POST[$key]));

			$this->update_array_option('map_options', $new_options);

			// Save the icons that we loaded
			$this->update_array_option('icons', $this->icons);

			$message = __('Settings saved', 'mappress');
		}

		$map_options = shortcode_atts($this->map_defaults, $this->get_array_option('map_options'));
		$icons = $this->get_array_option('icons');
		$cctld_link = '(<a target="_blank" href="http://en.wikipedia.org/wiki/CcTLD#List_of_ccTLDs">' . __("what's my country code?", 'mappress') . '</a>)';
		$lang_link = '(<a target="_blank" href="http://code.google.com/apis/maps/faq.html#languagesupport">' . __("supported languages", 'mappress') . '</a>)';
		$help_msg = $this->get_array_option('help_msg');
	?>
	<div class="wrap">
		<div id="icon-options-general" class="icon32"><br /></div>

			<h2><?php _e('MapPress Options', 'mappress') ?></h2>
			<?php
				if (!empty($message))
					echo "<div id='message' class='updated fade'><p>$message</p></div>";
				if (!empty($error))
					echo "<div id='error' class='error'><p>$error</p></div>";
			?>
			<div>
				<a target='_blank' href='<?php echo $this->bug_link ?>'><?php _e('Report a bug', 'mappress')?></a>
				| <a target='_blank' href='<?php echo $this->doc_link ?>'><?php _e('MapPress help', 'mappress')?></a>
			</div>

            <hr/>
            <h2><?php _e('Donate', 'mappress')?></h3>
            <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
                <input type="hidden" name="cmd" value="_s-xclick" />
                <input type="hidden" name="hosted_button_id" value="4339298" />
                <input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!" />
                <img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1" />
                <?php echo __("I spend many hours working on MapPress.  Please donate to keep me going!", 'mappress') ?>
            </form>
            <hr/>

			<form method="post" action="">
				<?php wp_nonce_field('mappress'); ?>

				<h4><?php _e('Google Maps API Key', 'mappress');?></h4><p>

				<table class="form-table">
					<tr valign='top'>
						<td id='api_block'><input type='text' id='api_key' name='api_key' size='110' value='<?php echo $map_options['api_key']; ?>'/>
						<p id='api_message'></p>
						</td>
					</td>
					<script type='text/javascript'>
						mappCheckAPI()
					</script>
				</table>

				<h4><?php _e('Optional Settings', 'mappress'); ?></h4>

				<table class="form-table">
					<?php $this->option_dropdown(__('Address format', 'mappress'), 'address_format', $map_options['address_format'],
						array('ENTERED' => __('Exactly as entered', 'mappress'), 'CORRECTED' => __('Corrected address', 'mappress'))); ?>

					<?php $this->option_string(__('Country code for searches', 'mappress'), 'country', $map_options['country'], 2, __('Enter a country code to use as a default when searching for an address.', 'mappress') . "<br />" . $cctld_link); ?>
					<?php $this->option_string(__('Force map language', 'mappress'), 'language', $map_options['language'], 2, __('Force Google to use a specific language for map controls.', 'mappress') . "<br />" . $lang_link); ?>
					<?php $this->option_checkbox(__('Directions', 'mappress'), 'directions', $map_options['directions'], __('Check to enable directions to/from map markers', 'mappress')); ?>										<?php $this->option_checkbox(__('Big map controls', 'mappress'), 'bigzoom', $map_options['bigzoom'], __('Check to show large map controls; uncheck for a small zoom control instead', 'mappress')); ?>
					<?php $this->option_checkbox(__('Map types button', 'mappress'), 'maptypes', $map_options['maptypes'], __('Check to enable the "map types" button on the map', 'mappress')); ?>
					<?php $this->option_dropdown(__('Initial map type', 'mappress'), 'maptype', $map_options['maptype'], array(
						'Map' => 'Street', 'Satellite' => 'Satellite', 'Hybrid' => 'Hybrid (street+satellite)', 'Terrain' => 'Terrain'),
						__('Choose the map type to use when the map is first displayed', 'mappress')); ?>
					<?php //$this->option_checkbox(__('Traffic button', 'mappress'), 'traffic', $map_options['traffic'], __('Check to enable the real-time traffic button on the map', 'mappress')); ?>
					<?php //$this->option_checkbox(__('Street view link', 'mappress'), 'streetview', $map_options['streetview'], __('Check to enable the "street view" link for map markers', 'mappress')); ?>
					<?php $this->option_checkbox(__('Initial marker', 'mappress'), 'open_info', $map_options['open_info'], __('Check to open the first marker when the map is displayed.', 'mappress')); ?>
					<?php // $this->option_checkbox(__('GoogleBar', 'mappress'), 'googlebar', $map_options['googlebar'], __('Check to show the "GoogleBar" search box for local business listings.', 'mappress')); ?>
					<?php //$this->option_checkbox(__('MapPress link', 'mappress'), 'poweredby', $map_options['poweredby'], __('Enable the "powered by" link.', 'mappress')); ?>
					<?php $this->option_checkbox(__('Scrollwheel zoom', 'mappress'), 'scrollwheel_zoom', $map_options['scrollwheel_zoom'], __('Enable zooming with the mouse scroll wheel.', 'mappress')); ?>
					<?php //$this->option_string(__('Icons URL', 'mappress'), 'icons_url', $map_options['icons_url'], 40, '<br/>' . __('URL to custom icon definitions.  Leave blank for the default:', 'mappress') . plugins_url('/' . $this->wordpress_tag . '/icons')); ?>
					<?php
						$default_icon_id = $map_options['default_icon'];
						$default_icon = $this->icons[$default_icon_id];
						$image_url = $default_icon->image;
						if (empty($image_url))
							$image_url = "http://maps.google.com/mapfiles/ms/micons/red-dot.png";
					?>
					<tr valign='top'><th scope='row'><?php _e('Default map icon: ', 'mappress'); ?></th>
					<td>
						<input type="hidden" name="default_icon" id="default_icon" value="<?php echo $default_icon->id ?>"/>
						<a href="javascript:void(0)"><img id="icon_picker" src="<?php echo $image_url ?>" alt="<?php echo $default_icon->id ?> title="<?php echo $default_icon->id ?>" /></a>
						<?php _e('(click the icon to choose)', 'mappress') ?>

						<div class='mapp-icon-list' id='mapp_icon_list'>
							<ul>
								<?php
									foreach ((array)$this->icons as $key => $icon) {
										if ($icon->image)
											$image_url = $icon->image;
										else
											$image_url = 'http://maps.google.com/mapfiles/ms/micons/red-dot.png';
										$shadow_url = $icon->shadow;
										$id = $icon->id;
										$description = $icon->description;
								?>
									<li><a><img src="<?php echo $image_url ?>" alt="<?php echo $id ?>" title="<?php echo $description ?>" id="<?php echo $icon->id?>" /></a></li>
								<?php
									}
								?>
							</ul>
						</div>
					</td>
				</table>

				<p class="submit"><input type="submit" class="submit" name="save" value="<?php _e('Save Changes', 'mappress') ?>"></p>
			</form>
		</div>
		<p><small>&copy; 2009, <a href="http://www.wphostreviews.com/mappress">C. Richardson</a></small></p>
	</div>
	<?php
	}


	/**
	* Options - display option as a field
	*/
	function option_string($label, $name, $value='', $size='', $comment='', $class='') {
		if (!empty($class))
			$class = "class='$class'";

		echo "<tr valign='top'><th scope='row'>" . $label . "</th>";
		echo "<td $class><input type='text' id='$name' name='$name' value='$value' size='$size'/> $comment</td>";
		echo "</tr>";
	}

	/**
	* Options - display option as a radiobutton
	*/
	function option_radiobutton($label, $name, $value='', $keys, $comment='') {
		echo "<tr valign='top'><th scope='row'>" . $label . "</th>";
		echo "<td>";

		foreach ((array)$keys as $key => $description) {
			if ($key == $value)
				$checked = "checked";
			else
				$checked = "";
			echo "<input type='radio' id='$name' name='$name' value='" . htmlentities($key, ENT_QUOTES, 'UTF-8') . "' $checked />" . $description . "<br>";
		}
		echo $comment . "<br>";
		echo "</td></tr>";
	}

	/**
	* Options - display option as a checkbox
	*/
	function option_checkbox($label, $name, $value='', $comment='') {
		if ($value)
			$checked = "checked='checked'";
		else
			$checked = "";
		echo "<tr valign='top'><th scope='row'>" . $label . "</th>";
		echo "<td><input type='hidden' id='$name' name='$name' value='0' /><input type='checkbox' name='$name' value='1' $checked />";
		echo " $comment</td></tr>";
	}

	/**
	* Options - display as dropdown
	*/
	function option_dropdown($label, $name, $value, $keys, $comment='') {
		echo "<tr valign='top'><th scope='row'>" . $label . "</th>";
		echo "<td><select name='$name'>";

		foreach ((array)$keys as $key => $description) {
			if ($key == $value)
				$selected = "selected";
			else
				$selected = "";

			echo "<option value='" . htmlentities($key, ENT_QUOTES, 'UTF-8') . "' $selected>$description</option>";
		}
		echo "</select>";
		echo " $comment</td></tr>";
	}
}  // End plugin class

class mpicon {
	var     $id,
			$description,
			$image,
			$shadow,
			$iconSize,
			$shadowSize,
			$iconAnchor,
			$infoWindowAnchor,
			$transparent;

	function mpicon($args = '') {
		$properties = get_class_vars('mpicon');
		shortcode_atts($properties, $args);

		foreach ($properties as $key=>$value)
			if (isset($args[$key]))
				$this->$key = $args[$key];
	}

	function draw($icons) {
        if (!$icons)
            return;

		if (!is_array($icons))
			$icons = array($icons);

		echo "<script type='text/javascript'> \r\n";
		echo "  var mappIcons = []; \r\n";
		echo "  var baseIcon = new GIcon(G_DEFAULT_ICON); baseIcon.iconSize = new GSize(32, 32); baseIcon.shadowSize = new GSize(59,32); baseIcon.iconAnchor = new GPoint(16,32);";
		foreach ((array)$icons as $icon) {
			echo "var i = new GIcon(baseIcon);";

			if ($icon->image)
				echo "i.image = '$icon->image'; ";
			if ($icon->shadow)
				echo "i.shadow = '$icon->shadow'; ";
			if ($icon->iconSize)
				echo "i.iconSize = new GSize({$icon->iconSize->x}, {$icon->iconSize->y}); ";
			if ($icon->shadowSize)
				echo "i.shadowSize = new GSize({$icon->shadowSize->x}, {$icon->shadowSize->y}); ";
			if ($icon->iconAnchor)
				echo "i.iconAnchor = new GPoint({$icon->iconAnchor->x}, {$icon->iconAnchor->y}); ";
			if ($icon->infoWindowAnchor)
				echo "i.infoWindowAnchor = new GPoint({$icon->infoWindowAnchor->x}, {$icon->infoWindowAnchor->y}); ";
			if ($icon->transparent)
				echo "i.transparent = '$icon->transparent';" ;
			echo " mappIcons['$icon->id'] = i;";
		}

		echo "\r\n</script>";
	}

	function read($url, $filename) {

		// ------------------------- 1.4.3 ----------------------------
		$default = new mpicon(array('id' => ''));

		$yellow = new mpicon(array('id' => 'yellow-dot.png', 'image' => 'http://maps.google.com/mapfiles/ms/micons/yellow-dot.png'));
		$blue = new mpicon(array('id' => 'blue-dot.png', 'image' => 'http://maps.google.com/mapfiles/ms/micons/blue-dot.png'));
		$green = new mpicon(array('id' => 'green-dot.png', 'image' => 'http://maps.google.com/mapfiles/ms/micons/green-dot.png'));
		$ltblue = new mpicon(array('id' => 'ltblue-dot.png', 'image' => 'http://maps.google.com/mapfiles/ms/micons/ltblue-dot.png'));
		$pink = new mpicon(array('id' => 'pink-dot.png', 'image' => 'http://maps.google.com/mapfiles/ms/micons/pink-dot.png'));
		$purple = new mpicon(array('id' => 'purple-dot.png', 'image' => 'http://maps.google.com/mapfiles/ms/micons/purple-dot.png'));
		$icons = array($default->id => $default, $yellow->id => $yellow, $blue->id => $blue, $ltblue->id => $ltblue, $pink->id => $pink, $purple->id => $purple);
		return $icons;
	}
}

// There's a bug in wordpress (http://core.trac.wordpress.org/ticket/11537) 2.9.
// Wordpress tries to create a 'json_encode' function if none exists (in compat.php)
// The 2.9 function calls the wrong method in the underlying Services_JSON class.
// It calls 'encode', but it should call '_encode' or 'encodeUnsafe'.
//
// So, I can't call json_encode in WP2.9 because in PHP < 5.2 it'll call 'encode' which sends page headers.
//
// Also, other plugins include incompatible versions of JSON.php (which don't have the encodeUnsafe function at all)
// so I can't count on being able to call encodeUnsafe directly.
//
// I give up.  For now, I'm including a customized JSON.  Hopefully I can switch back to standard
// when WordPress fixes their code.
//
function mapp_json_encode($content) {
	global $mapp_json;

	if ( !is_a($mapp_json, 'Mapp_Services_JSON') ) {
		require_once( 'mappress-json.php' );
		$mapp_json = new Mapp_Services_JSON();
	}

	return $mapp_json->encodeUnsafe($content);
}



// Create new instance of the plugin
$mappress = new mappress();
?>