<?php

add_theme_support( 'post-thumbnails' );
add_image_size( 'main-thumb', 140, 140, true );
add_image_size( 'student-dash-thumb', 220, 146, true ); 
add_image_size( 'large-image', 620, 1200, true );


function attachment_toolbox($size = thumbnail, $ulClass = '') {

	$images = get_children(array(
		'post_parent'    => get_the_ID(),
		'post_type'      => 'attachment',
		'numberposts'    => -1, // show all
		'post_status'    => null,
		'post_mime_type' => 'image',
		'order'          => 'ASC',
		'orderby'        => 'menu_order',
	));			
	
	$count = 0;
	$out = '<ul class="'.$ulClass.'">';
	foreach($images as $image) {
		$attimg  = wp_get_attachment_image_src($image->ID,$size);
		$attimgurl = $attimg[0];
		$atturl   = wp_get_attachment_url($image->ID);
		$attlink  = get_attachment_link($image->ID);
		$postlink = get_permalink($image->post_parent);
		$atttitle = apply_filters('the_title',$image->post_title);
		$attcontent = $image->post_content;
		$attimgtype	= get_post_meta($image->ID,"_mySelectBox", true);
		$imglink	= $image->guid;

		if($attimgtype==$type) {
			$count++;
			$out .= '<li><img title="'.$atttitle.'" src="'.$attimgurl.'"/></li>';
		}
	}
	$out .= '</ul>';
}

function attachment_selectbox_edit($form_fields, $post) {
	
	// select options: you could code these manually or get it from a database
	$select_options = array(
		"house" => "House",
		"floorplan" => "Floorplan",
	);

	// get the current value of our custom field
	$current_value = get_post_meta($post->ID, "_mySelectBox", true);
	
	// build the html for our select box
	$mySelectBoxHtml = "<select name='attachments[{$post->ID}][mySelectBox]' id='attachments[{$post->ID}][mySelectBox]'>";
	foreach($select_options as $value => $text){
	
		// if this value is the current_value we'll mark it selected
		$selected = ($current_value == $value) ? ' selected ' : '';
		
		// escape value	for single quotes so they won't break the html
		$value = addcslashes( $value, "'");
		
		$mySelectBoxHtml .= "<option value='{$value}' {$selected}>{$text}</option>";
	}
	$mySelectBoxHtml .= "</select>";
	
	// add our custom select box to the form_fields
	$form_fields["mySelectBox"]["label"] = __("Image Type");
	$form_fields["mySelectBox"]["input"] = "html";
	$form_fields["mySelectBox"]["html"] = $mySelectBoxHtml;

	return $form_fields;
}
add_filter("attachment_fields_to_edit", "attachment_selectbox_edit", null, 2);


function attachment_selectbox_save($post, $attachment) {
	if( isset($attachment['mySelectBox']) ){
		update_post_meta($post['ID'], '_mySelectBox', $attachment['mySelectBox']);
	}
	return $post;
}
add_filter("attachment_fields_to_save", "attachment_selectbox_save", null, 2);

/* supports notifications from PayPal */
function handle_ipn($vars) {
	require_once(ABSPATH . WPINC . '/registration.php');
	$payment_status = $vars['payment_status'];	// Completed/Refunded 
	$item_name = $vars['item_name']; 			// Holding/Security Deposit
	$IPN_reference = $vars['txn_id']; 			// Ref/'cash'
	$IPN_address = $vars['item_number']; 		// Address
	$user_id = $vars['custom'];					// Login id of person paying
	$user = get_userdatabylogin($user_id);
	$IPN_payer_email = $vars['payer_email'];
	$IPN_all = implode($vars,"~~");
	activity_log(array(
		'type'=>'IPN',
		'entry'=>$IPN_all
	));
	if($IPN_reference and $IPN_address and $payment_status and $user_id) {
		if($item_name=="Holding Deposit") {
			$loop = new WP_Query("post_type=properties"); // JRL: I want the properties where $address_field matches $IPN_address, but I don't think I can just search by custom field (address) - maybe a SQL query?
			$property_matches=0;
			global $post;
			while ( $loop->have_posts() ) : $loop->the_post();
				$address_field = get_post_meta($post->ID,"_address");
				$address_field = $address_field[0];
				if($address_field==$IPN_address) {
					$property_matches++;
					$the_ID = get_the_ID();
					if($payment_status=="Completed") {
						update_post_meta($the_ID, '_property_status', 'under_offer');
						add_post_meta($the_ID, 'ipn_txn_id', $IPN_reference, true) or update_post_meta($the_ID, 'ipn_txn_id', $IPN_reference);
						add_post_meta($the_ID, 'ipn_payer_email', $user_id, true) or update_post_meta($the_ID, 'ipn_payer_email', $user_id);
						add_post_meta($the_ID, 'ipn_all', $IPN_all, true) or update_post_meta($the_ID, 'ipn_all', $IPN_all);
						// add payer to their own group
						update_user_meta($user->ID, 'group', $user_id);
						$email = $user->user_email.",glenn@sweetspot.com"; // TO-DO: get the manager addresses for a property into an admin page
						$email_body = "Hello there!\n\nThanks for paying a holding deposit. The property has been taken off the market for a while to give you and your friends chance to get confirmed as future tenants. If you have any questions, please speak to your property manager, check the FAQ at http://sweetspot.com/faq or email us at hello@sweetspot.com.\n\n"; // TO-DO: get this into an admin screen
						$email_subject = "Thanks for paying a holding deposit for {$IPN_address}"; // TO-DO: get this into an admin screen
						sweetspot_mail($email, $email_subject, $email_body);
						if ($IPN_reference=="Cash") {
							header('Location: '.bloginfo('siteurl').'dash'); // TO-DO: replace this with meta-redirect since output already started
						} else {
							echo "Payment acknowledged";
						}
					} else if ($payment_status=="Refunded") {
						update_post_meta($the_ID, '_property_status', 'on_the_market');
						delete_post_meta($the_ID, 'ipn_txn_id');
						delete_post_meta($the_ID, 'ipn_payer_email');
						delete_post_meta($the_ID, 'ipn_all');
						$email = $user->user_email;
						$email_body = "Hello there!\n\nThis is to confirm we've refunded your holding deposit."; // TO-DO: get this into an admin screen
						$email_subject = "Refund for holding deposit on {$IPN_address}"; // TO-DO: get this into an admin screen
						sweetspot_mail($email, $email_subject, $email_body);
						echo "Refund processed";
					}
				}
			endwhile;
			if($property_matches==0) {
				echo "did not find any property matches for ".$IPN_address;
			}
		} else if($item_name=="Security Deposit") {
			if($payment_status=="Completed") {
				update_user_meta($user->ID, 'paid_security_deposit', $IPN_reference);
				$email = $user->user_email;
				$email_body = "Hello there!\n\nThanks for paying your security deposit and 1st month's rent. We're going to be insuring your deposit with MyDeposits.co.uk. If you have any questions, please speak to your property manager, check the FAQ at http://sweetspot.com/faq or email us at hello@sweetspot.com.\n\n"; // TO-DO: get this into an admin screen
				$email_subject = "Thanks for paying a security deposit and 1st month's rent for {$IPN_address}"; // TO-DO: get this into an admin screen
				sweetspot_mail($email, $email_subject, $email_body);
				// figure out if all members of a group have paid their deposits and notify manager if so
				$lead_tenant = $user->group;
				$peopleIDs = $wpdb->get_col( $wpdb->prepare(
					"SELECT $wpdb->users.ID FROM $wpdb->users"
				));
				$all_paid = true;
				foreach ($peopleIDs as $personID) {
					$person = get_userdata($personID);
					if($person->group==$lead_tenant && !$person->paid_security_deposit) {
						$all_paid = false;
					}
				}
				if($all_paid) {
					$email_text = "Hi Glenn,\n\nIt looks like the applicants for {$IPN_address} have all paid their security deposits. They've been notified that their deposit will be registered with MyDeposits.";
					sweetspot_mail('glenn@sweetspot.com', "All security deposits paid for {$IPN_address}", $email_text);
				}
				echo "Payment acknowledged";
			} else if ($payment_status=="Refunded") {
				delete_post_meta($user->ID, 'paid_security_deposit');
				$email = $user->user_email;
				$email_body = "Hello there!\n\nThis is to confirm that we've refunded your security deposit and 1st month's rent."; // TO-DO: get this into an admin screen
				$email_subject = "Refund for security deposit and 1st month's rent on {$IPN_address}"; // TO-DO: get this into an admin screen
				sweetspot_mail($email, $email_subject, $email_body);
				echo "Refund processed";
			}
		} else {
			// unknown item name
			echo "error: do not understand item name: ".$item_name;
		}
	} else {
		echo "error: send at least 'txn_id', 'item_number', 'payment_status' and 'custom' (containing person's SweetSpot account name)";
	}
}

/* adds custom column to properties table view, so we can get to PayPal */
add_filter("manage_edit-properties_columns", "propertiesColumns");
function propertiesColumns($columns) {
	$columns['paypal'] = 'PayPal link';
	return $columns;
}
add_action("manage_posts_custom_column", "propertiesColumnsValue");
function propertiesColumnsValue($name) {
	global $post;
	if($name=='paypal') {
		$txn_id = get_post_meta($post->ID, "ipn_txn_id");
		if($txn_id[0]!="") {
			//echo $txn_id[0];
			echo makePayPalLink($txn_id[0]);
		} else {
			echo "no transactions";
		}
	}
}

function makePayPalLink($id) { // JRL: I want to make this link directly to the transaction or refund page, but I haven't figured out how to yet.
	return '<a href="https://www.paypal.com/uk/cgi-bin/webscr?cmd=_history">Reset for holding deposit transaction '.$id."</a>";
}

// adds properties reset panel to a Properties page 
add_action('admin_menu', 'add_properties_reset_box');
function add_properties_reset_box() {
	add_meta_box( 'properties_reset', 'properties Reset', 'display_reset_panel', 'properties');
}
function display_reset_panel() {
	global $post;
	$txn_id = get_post_meta($post->ID, "ipn_txn_id");
	echo "<p>Clicking the button below will take you to a page where you can refund a properties's holding deposit. Once the refund has completed, the property's status will be updated to 'on the market'.</p>";
	echo makePayPalLink($txn_id[0]);
}



// ************************************   commence special user-meta fields:


add_action( 'show_user_profile', 'my_show_extra_profile_fields' );
add_action( 'edit_user_profile', 'my_show_extra_profile_fields' );

function my_show_extra_profile_fields( $user ) { ?>

	<h3>Extra Applicant information</h3>

	<table class="form-table">
		<tr>
			<th><label for="phone">Phone Number</label></th>
			<td>
				<input type="text" name="phone" id="phone" value="<?php echo esc_attr( get_the_author_meta( 'phone', $user->ID ) ); ?>" class="regular-text" />
			</td>
		</tr>
		<tr>
			<th><label for="postcode">Postcode</label></th>
			<td>
				<input type="text" name="postcode" id="postcode" value="<?php echo esc_attr( get_the_author_meta( 'postcode', $user->ID ) ); ?>" class="regular-text" />
			</td>
		</tr>
		<tr>
			<th><label for="address">Address</label></th>
			<td>
				<input type="text" name="address" id="address" value="<?php echo esc_attr( get_the_author_meta( 'address', $user->ID ) ); ?>" class="regular-text" /><br />
			</td>
		</tr>
		<tr>
			<th><label for="group">Group</label></th>
			<td>
				<input type="text" name="group" id="group" value="<?php echo esc_attr( get_the_author_meta( 'group', $user->ID ) ); ?>" class="regular-text" /><br />
			</td>
		</tr>
		<tr>
			<th><label for="submitted_housemate_info">Submitted housemate info</label></th>
			<td>
				<input type="text" name="submitted_housemate_info" id="submitted_housemate_info" value="<?php echo esc_attr( get_the_author_meta( 'submitted_housemate_info', $user->ID ) ); ?>" class="regular-text" /><br />
			</td>
		</tr>
		<tr>
			<th><label for="paid_security_deposit">Paid Security Deposit</label></th>
			<td>
				<input type="text" name="paid_security_deposit" id="paid_security_deposit" value="<?php echo esc_attr( get_the_author_meta( 'paid_security_deposit', $user->ID ) ); ?>" class="regular-text" /><br />
			</td>
		</tr>
	</table>
	
	
	<h3>Guarantor information</h3>

	<table class="form-table">
		<tr>
			<th><label for="submitted_housemate_info">Submitted guarantor info</label></th>
			<td>
				<input type="text" name="submitted_guarantor_info" id="submitted_guarantor_info" value="<?php echo esc_attr( get_the_author_meta( 'submitted_guarantor_info', $user->ID ) ); ?>" class="regular-text" /><br />
			</td>
		</tr>
		<tr>
			<th><label for="postcode">Guarantor First Name</label></th>
			<td>
				<input type="text" name="guarantor_first_name" id="guarantor_first_name" value="<?php echo esc_attr( get_the_author_meta( 'guarantor_first_name', $user->ID ) ); ?>" class="regular-text" />
			</td>
		</tr>
		<tr>
			<th><label for="group">Guarantor Last Name</label></th>
			<td>
				<input type="text" name="guarantor_last_name" id="guarantor_last_name" value="<?php echo esc_attr( get_the_author_meta( 'guarantor_last_name', $user->ID ) ); ?>" class="regular-text" /><br />
			</td>
		</tr>
		<tr>
			<th><label for="address">Guarantor Email</label></th>
			<td>
				<input type="text" name="guarantor_email" id="guarantor_email" value="<?php echo esc_attr( get_the_author_meta( 'guarantor_email', $user->ID ) ); ?>" class="regular-text" /><br />
			</td>
		</tr>
		<tr>
			<th><label for="phone">Guarantor Phone Number</label></th>
			<td>
				<input type="text" name="guarantor_phone" id="guarantor_phone" value="<?php echo esc_attr( get_the_author_meta( 'guarantor_phone', $user->ID ) ); ?>" class="regular-text" />
			</td>
		</tr>		
	</table>
<?php }

add_action( 'personal_options_update', 'my_save_extra_profile_fields' );
add_action( 'edit_user_profile_update', 'my_save_extra_profile_fields' );

function my_save_extra_profile_fields( $user_id ) {

	if ( !current_user_can( 'edit_user', $user_id ) )
		return false;

	update_user_meta( $user_id, 'phone', $_POST['phone'] );
	update_user_meta( $user_id, 'postcode', $_POST['postcode'] );
	update_user_meta( $user_id, 'address', $_POST['address'] );
	update_user_meta( $user_id, 'group', $_POST['group'] );
	
	/* JRL: not necessarily wanting to show the following, but doing so for transparency - put them in another box? */
	update_user_meta( $user_id, 'submitted_housemate_info', $_POST['submitted_housemate_info'] );
	update_user_meta( $user_id, 'paid_security_deposit', $_POST['paid_security_deposit'] );
	update_user_meta( $user_id, 'submitted_guarantor_info', $_POST['submitted_guarantor_info'] );
	
	/* Guarantor Fields:  */
	update_user_meta( $user_id, 'guarantor_first_name', $_POST['guarantor_first_name'] );
	update_user_meta( $user_id, 'guarantor_last_name', $_POST['guarantor_last_name'] );
	update_user_meta( $user_id, 'guarantor_email', $_POST['guarantor_email'] );
	update_user_meta( $user_id, 'guarantor_phone', $_POST['guarantor_phone'] );
}


// ************************************ create post type: Properties

if ( ! function_exists( 'post_type_properties' ) ) :

function post_type_properties() {

	register_post_type( 
		'properties',
		array( 
			'label' => __('Properties'), 
			'description' => __('Create a property.'), 
			'public' => true, 
			'show_ui' => true,
			'register_meta_box_cb' => 
                        'init_metaboxes_properties',
			'supports' => array (
				'title',
				'editor',
				'thumbnail',
				'excerpt',
				'custom-fields',
				'author'
				
			)
		)
	);

register_taxonomy( 'breeds', 'property-breeds', 
array ('hierarchical' => true, 'label' => __('Breeds'))); 

register_taxonomy( 'kittenmeta', 'property-meta', 
array ('hierarchical' => false, 'label' => __('Meta Keywords'),
'query_var' => 'propertymeta'));

}

endif;

add_action('init', 'post_type_properties');

// add custom fields to the custom post type here
$sp_boxes = array (
	'About the Property' => array (
		array( '_beds', 'Beds', 'Number of beds in this property'),
		array( '_baths', 'Baths', 'Number of baths in this property'),
		array( '_receptions', 'Receptions', 'Number of receptions in this property'),
		array( '_price', 'Price', 'Price per person per week'),
		array( '_period', 'Let Period', 'Length of let'),
		array( '_notes', 'Notes', 'Sales description of the property', 'textarea' ),
		array( '_mapnotes', 'Map Notes', 'Notes to go alongside the map', 'textarea' ),
		array( '_property_status', 'Property Status', '', 'select' ),
		
	),
	'Address' => array (
		array( '_address', 'Address', 'Property name / number &amp; street name', 'textarea' ),
		array( '_city', 'City / Town' ),
		array( '_postcode', 'Postcode' ),
		array( '_country', 'Country' ),
	)
);

// Do not edit past this point.

// Use the admin_menu action to define the custom boxes
//add_action( 'admin_menu', 'sp_add_custom_box' ); - not being used in place of the register_meta_box_cb above
function init_metaboxes_properties() {
	sp_add_custom_box();
}

// Adds a custom section to the "advanced" Post and Page edit screens
function sp_add_custom_box() {
	global $sp_boxes;
	if ( function_exists( 'add_meta_box' ) ) {
		foreach ( array_keys( $sp_boxes ) as $box_name ) {
			add_meta_box( $box_name, __( $box_name, 'sp' ), 'sp_post_custom_box', 'properties', 'normal', 'high' );
		}
	}
}

// this handles the nonces for the meta boxes
if ( ! function_exists( 'sp_post_custom_box' ) ) :
function sp_post_custom_box ($obj, $box) {
	global $sp_boxes;
	static $sp_nonce_flag = false;
	echo '<div style="width: 95%%; margin: 10px auto 10px auto; background-color: #F9F9F9; border: 1px solid #DFDFDF; -moz-border-radius: 5px; -webkit-border-radius: 5px; padding: 10px;">';
	// Run once
	if ( ! $sp_nonce_flag ) {
		echo_sp_nonce();
		$sp_nonce_flag = true;
	}
	// Generate box contents
	foreach ( $sp_boxes[$box['id']] as $sp_box ) {
		echo field_html( $sp_box );
	}
	echo '</div>';
}
endif;

// this switch statement specifies different types of meta boxes
// you can add more types if you add a case and a corresponding function
// to handle it
if ( ! function_exists( 'field_html' ) ) :
function field_html ( $args ) {
	switch ( $args[3] ) {
		case 'textarea':
			return text_area( $args );
		case 'checkbox':
			return sp_checkbox( $args );
		case 'select':
			return sp_select( $args );
		default:
			return text_field( $args );
	}
}
endif;

// this is the default text field meta box
if ( ! function_exists( 'text_field' ) ) :
function text_field ( $args ) {
	global $post;
	$description = $args[2];
	// adjust data
	$args[2] = get_post_meta($post->ID, $args[0], true);
	$args[1] = __($args[1], 'sp' );
	$label_format =
		'<div style="overflow:hidden;  margin-top:10px;">'.
		'<div style="width:100px; float:left;"><label for="%1$s"><strong>%2$s</strong></label></div>'.
		'<div style="width:500px; float:left;"><input style="width: 80%%;" type="text" name="%1$s" value="%3$s" />'.
		'<p style="clear:both"><em>'.$description.'</em><p></div>'.
		'</div>';
	return vsprintf( $label_format, $args );
}
endif;

// this is the text area meta box
if ( ! function_exists( 'text_area' ) ) :
function text_area ( $args ) {
	global $post;
	$description = $args[2];
	// adjust data
	$args[2] = get_post_meta($post->ID, $args[0], true);
	$args[1] = __($args[1], 'sp' );
	$label_format =
		'<div style="overflow:hidden; margin-top:10px; ">'.
		'<div style="width:100px; float:left;"><label for="%1$s"><strong>%2$s</strong></label></div>'.
		'<div style="width:500px; float:left;"><textarea style="width: 90%%;" name="%1$s">%3$s</textarea>'.
		'<p style="clear:both"><em>'.$description.'</em></p></div>'.
		'</div>';
	return vsprintf( $label_format, $args );
}
endif;

// this is the checkbox meta box
if ( ! function_exists( 'sp_checkbox' ) ) :
function sp_checkbox ( $args ) {
	global $post;
	$description = $args[2];
	// adjust data
	$args[2] = get_post_meta($post->ID, $args[0], true);
	$args[1] = __($args[1], 'sp' );
	$label_format =
		'<div style="width: 95%%; margin: 10px auto 10px auto; background-color: #F9F9F9; border: 1px solid #DFDFDF; -moz-border-radius: 5px; -webkit-border-radius: 5px; padding: 10px;">'.
		'<p><label for="%1$s"><strong>%2$s</strong></label></p>';
	$current_value = $args[2];
	$checked = ($current_value == "on") ? ' checked="checked"' : '';
	$label_format .= '<p><input type="checkbox" name="%1$s" '.$checked.' /></p>'.
		'<p><em>'.$description.'</em></p>'.
		'</div>';
	return vsprintf( $label_format, $args );
}
endif;

// this is the select meta box
if ( ! function_exists( 'sp_select' ) ) :
function sp_select ( $args ) {
	global $post;
	$description = $args[2];
	// adjust data
	$args[2] = get_post_meta($post->ID, $args[0], true);
	$args[1] = __($args[1], 'sp' );
	$label_format =
		'<div style="overflow:hidden; margin-top:10px; ">'.
		'<div style="width:100px; float:left;"><label for="%1$s"><strong>%2$s</strong></label></div>'.
		'<div style="width:500px; float:left;">'.
		'<select name="%1$s" id="%1$s">';
	
	$current_value = $args[2];
	$select_options = array( // JRL - we'll want to take this options definition out of this function and pop it up where people are setting up the metaboxes
		"on_the_market"=>"On the market",
		"under_offer"=>"Under offer",
		"let"=>"Let"
	);
	foreach($select_options as $value => $text){
	
		// if this value is the current_value we'll mark it selected
		
		$selected = ($current_value == $value) ? ' selected="selected"' : '';
		
		// escape value	for quotes so they won't break the html
		$value = addslashes($value);
		
		$label_format .= '<option value="'.$value.'"'.$selected.'>'.$text.'</option>';
	}
		
	$label_format .= '</select>'.
		'<p><em>'.$description.'</em></p></div>'.
		'</div>';
	return vsprintf( $label_format, $args );
}
endif;

/* When the post is saved, saves our custom data */
if ( ! function_exists( 'sp_save_postdata' ) ) :
function sp_save_postdata($post_id, $post) {
	global $sp_boxes;
	// verify this came from the our screen and with proper authorization,
	// because save_post can be triggered at other times
	if ( ! wp_verify_nonce( $_POST['sp_nonce_name'], plugin_basename(__FILE__) ) ) {
		return $post->ID;
	}
	// Is the user allowed to edit the post or page?
	if ( 'page' == $_POST['post_type'] ) {
		if ( ! current_user_can( 'edit_page', $post->ID ))
			return $post->ID;
		} else {
		if ( ! current_user_can( 'edit_post', $post->ID ))
			return $post->ID;
		}
		// OK, we're authenticated: we need to find and save the data
		// We'll put it into an array to make it easier to loop though.
		// The data is already in $sp_boxes, but we need to flatten it out.
		foreach ( $sp_boxes as $sp_box ) {
			foreach ( $sp_box as $sp_fields ) {
				$my_data[$sp_fields[0]] =  $_POST[$sp_fields[0]];
			}
		}
		// Add values of $my_data as custom fields
		// Let's cycle through the $my_data array!
		foreach ($my_data as $key => $value) {
			if ( 'revision' == $post->post_type  ) {
				// don't store custom data twice
				return;
			}
			// if $value is an array, make it a CSV (unlikely)
			$value = implode(',', (array)$value);
			if ( get_post_meta($post->ID, $key, FALSE) ) {
				// Custom field has a value.
				update_post_meta($post->ID, $key, $value);
			} else {
				// Custom field does not have a value.
				add_post_meta($post->ID, $key, $value);
		}
		if (!$value) {
			// delete blanks
			delete_post_meta($post->ID, $key);
		}
	}
}
endif;

if ( ! function_exists( 'echo_sp_nonce' ) ) :
function echo_sp_nonce () {
	// Use nonce for verification ... ONLY USE ONCE!
	echo sprintf(
		'<input type="hidden" name="%1$s" id="%1$s" value="%2$s" />',
		'sp_nonce_name',
		wp_create_nonce( plugin_basename(__FILE__) )
	);
}
endif;

// A simple function to get data stored in a custom field
if ( ! function_exists( 'get_custom_field' ) ) :
if ( !function_exists('get_custom_field') ) {
	function get_custom_field($field) {
		global $post;
		$custom_field = get_post_meta($post->ID, $field, true);
		echo $custom_field;
	}
}
endif;

// Use the save_post action to do something with the data entered
// Save the custom fields
add_action( 'save_post', 'sp_save_postdata', 1, 2 );


// ****************************************************************** Register Viewing Post type

if ( ! function_exists( 'post_type_viewings' ) ) :

function post_type_viewings() {

	register_post_type( 
		'viewings',
		array( 
			'label' => __('Viewings'), 
			'description' => __('Logging booked viewings.'), 
			'public' => true, 
			'show_ui' => true,
			'supports' => array (
				'title',
				'editor',
				'thumbnail',
				'excerpt',
				'custom-fields',
				'author'
			)
		)
	);
}

endif;

add_action('init', 'post_type_viewings');


// ************ creating new accounts when receiving new viewing booking ***********

 function processViewingBooking($vars) {
	require_once(ABSPATH . WPINC . '/registration.php');
	$username =  $vars['booked_by'];
	$useremail = $username;
	$address = $vars['address'];
	$datetime = $vars['booking_datetime'];
	$booked_by_name = $vars['booked_by_name'];
	$booked_by_phone = $vars['booked_by_phone'];
	$vars_string = implode($vars,"~~");
	activity_log(array(
		'type'=>'viewing',
		'entry'=>$vars_string
	));
	if($username && $address && $datetime) {
		// create account if necessary
		$user_id = username_exists( $username );
		if ( !$user_id ) {
			if(!$booked_by_name) {
				$booked_by_name = $username;
			}
			$random_password = wp_generate_password( 12, false );
			$user_id = wp_insert_user( array (
				'user_login' 	=> $username, 
				'user_pass' 	=> $random_password, 
				'user_email' 	=> $useremail,
				'display_name'	=> $booked_by_name,
				'role'			=> 'applicant'
			));
			update_user_meta($user_id, 'phone', $booked_by_phone);
			echo "New account created for ".$useremail.", ID: ".$user_id;
			wp_new_user_notification($user_id, $random_password);
		} else {
			echo "Account identified for ".$useremail.", ID: ".$user_id;
		}
		// store event
		$new_event = array();
		$new_event['post_title'] = time();
		$new_event['post_type'] = 'viewings';
		$new_event['post_content'] = 'This is my new viewing.';
		$new_event['post_status'] = 'publish';
		$new_event['post_author'] = $user_id;
		$event_id = wp_insert_post($new_event);
		if($event_id) {
			update_post_meta($event_id, "address", $address);
			update_post_meta($event_id, "datetime", $datetime);
		}
	} else {
		$out = "please send booked_by, address and booking_datetime parameters. I found: ";
		foreach($vars as $key=>$value) {
			$out .= $key.": ".$value."; ";
		}
		echo $out;
	}
} 

// ************ end creating new accounts when receiving new viewing booking ************

function sweetspot_mail($to_address, $subject, $body) {
	$headers = '';
	$headers .= "Reply-To: hello@sweetspot.com\r\n";
	$headers .= "From: SweetSpot <hello@sweetspot.com>\r\n";
	wp_mail($to_address, $subject, $body, $headers);
}

// *********** Function to put property status flags over images ************

function property_status_banner($property_status) {
	global $post;
		$property_status = get_post_meta($post->ID, '_property_status', true);
	if($property_status=="under_offer") {
		echo '<span class="propertyUnderOffer"></span>';
	} else if($property_status=="let") {
		echo '<span class="propertyLet"></span>';
	}
}

// *********** Function to truncate long things ************


function dot($str, $len, $dots = " [..]") {
					    if (strlen($str) > $len) {
					        $dotlen = strlen($dots);
					        $str = substr_replace($str, $dots, $len - $dotlen);
					    }
					    return $str;
					}


?>