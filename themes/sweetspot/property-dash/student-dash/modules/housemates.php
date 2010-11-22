<div class="module">
					<a href="#top" rel="self" class="right topLink">top &#94;</a>
					<a name="housemates"></a><h3 class="blue">Your Housemates</h3>
<?php
// $paid_holding_deposit is the ID of the property you're paid up on
$beds = get_post_meta($paid_holding_deposit, '_beds', true);
// this module should be shown if $submitted_housemate_info flag is not set
// the result of submitting the info should set the flag
if ($_POST && $_POST['submit_housemates']) :
	$dash_url = get_bloginfo('siteurl').'/dash';
	$welcome_email_text = "Hello there!\n\nYou've been added as a tenant to property {$paid_holding_deposit_address}. Please come and get started with the moving-in process.\n\nLogin at: {$dash_url}"; // TO-DO: get this into an admin screen
	$welcome_email_subject = "You've been added as an applicant to {$paid_holding_deposit_address}"; // TO-DO: get this into an admin screen
	/* 
		see if submit_housemates is there
		$email is lead tenant email
		collect for lead tenant: firstname, lastname, postcode, address, phone
		collect for other tenants: firstname$i, lastname$i, postcode$i, address$i, useYourAddress$i, email$i, phone$i
		add lead tenant fields to their account
		create new accounts for other tenants; make sure they get *appropriate* welcome emails
	*/
	$current_user_id = $current_user->id;
	update_user_meta($current_user_id, 'first_name', $_POST['firstname']);
	update_user_meta($current_user_id, 'last_name', $_POST['lastname']);
	update_user_meta($current_user_id, 'postcode', $_POST['postcode']);
	update_user_meta($current_user_id, 'address', $_POST['address']);
	update_user_meta($current_user_id, 'phone', $_POST['phone']);

	$housemate_emails = array();

	for($i=1; $i<$beds; $i++) {
		$firstname = $_POST['firstname'.$i];
		$lastname = $_POST['lastname'.$i];
		$useYourAddress = $_POST['useYourAddress'.$i];
		if($useYourAddress) {
			$postcode = $_POST['postcode'];
			$address = $_POST['address'];
		} else {
			$postcode = $_POST['postcode'.$i];
			$address = $_POST['address'.$i];
		}
		$email = $_POST['email'.$i];
		$housemate_emails[] = $email;
		$phone = $_POST['phone'.$i];
		if(!($firstname && $lastname && $address && $email && $phone)) {
			//$any_errors .= "i: {$i}, firstname: {$firstname}, lastname: {$lastname}, address: {$address}, email: {$email}, phone: {$phone}"; // JRL: debug
			// error! TO-DO: redirect back to this page with error set in the URL parameters
			// defering this for a while...
		} else {
			// create account if necessary
			require_once(ABSPATH . WPINC . '/registration.php');
			$user_id = username_exists( $email );
			if ( !$user_id ) {
				$random_password = wp_generate_password( 12, false );
				$user_id = wp_insert_user( array (
					'user_login' 	=> $email, 
					'user_pass' 	=> $random_password, 
					'user_email' 	=> $email,
					'display_name'	=> $firstname,
					'role'			=> 'applicant'
				));
				$login_text = "\n\nYour login ID and password are:\n\nLogin ID: {$email}\nPassword: {$random_password}\n\n";
				sweetspot_mail($email, $welcome_email_subject, $welcome_email_text.$login_text);
			} else {
				sweetspot_mail($email, $welcome_email_subject, $welcome_email_text);
			}
			update_user_meta($user_id, 'first_name', $firstname);
			update_user_meta($user_id, 'last_name', $lastname);
			update_user_meta($user_id, 'postcode', $postcode);
			update_user_meta($user_id, 'address', $address);
			update_user_meta($user_id, 'phone', $phone);
			update_user_meta($user_id, 'group', $current_user->user_login);
			// set flag on $current_user
			update_user_meta($current_user_id, 'submitted_housemate_info', implode(', ', $housemate_emails));
		}
	}
?>
					<p class="grid3col left">
						Thanks! We've created accounts for all those people.
					</p>
<?php else: ?>
	<?php if ($submitted_housemate_info) : ?>
					<p class="grid3col left">
						You invited the people with these email addresses:
					</p>
					<ul class="grid3col left">
						<?php $housemate_emails = explode(", ", $submitted_housemate_info);
							foreach ($housemate_emails as $housemate) {
								echo "<li>".$housemate."</li>";
							}
						?>
					</ul>
	<?php else: ?>
					<p class="grid3col left">
						Who are you moving in with? Enter your future housemates' details here so we can get them started. Please give us your address and contact details too, as we'll need these for sending out the tenancy agreement and getting in touch.
					</p>
					<div class="grid9col right marginleft">
						<form id="housemateDetails" method="POST">
							<div class="housemateDetails">
								<h5>You:</h5>
								<div class="housemateForm">
									<div class="grid3col left names">
										<div class="left">
											<label for="firstName">First</label>
											<input class="required firstName" type="text" name="firstname" id="firstName" value="<?php echo $current_user->first_name; ?>" />
											<!-- <label for="name" generated="true" class="error">This field is required.</label> -->
										</div>
										<div class="left">
											<label for="lastName">Last</label>
											<input class="required lastName" type="text" name="lastname" id="lastName" value="<?php echo $current_user->last_name; ?>" />
											<!-- <label for="name" generated="true" class="error">This field is required.</label> -->
										</div>
									</div>
									
									<div class="left marginleft postcode">
										<label for="postCode">Postcode</label>
										<input class="required postCode" type="text" name="postcode" id="postCode" />
									</div>
									<div class="left marginleft postcode">										
										<label for="houseNo">House No.</label>
										<input class="required houseNo" type="text" name="address" id="address" />
										<!-- <input class="lookUp" type="button" name="lookup" id="lookUp" value="lookup" />
										<select class="required address" name="address" id="address">
											<option>1</option>
											<option>2</option>
										</select> -->
									</div>
									
									<div class="grid2col left marginleft">
										<label for="email">Email</label>
										<input class="required email" type="text" name="email" id="email" readonly="readonly" value="<?php echo $current_user->user_email; ?>" />
										<!-- <label for="name" generated="true" class="error">This field is required.</label> -->
									</div>
									
									<div class="grid2col left marginleft">
										<label for="phone">Phone</label>
										<input class="required phone" type="text" name="phone" id="phone" value="<?php echo $current_user->phone; ?>" />
										<!-- <label for="name" generated="true" class="error">This field is required.</label> -->
									</div>
								</div>
							</div>
							<?php
								for($i=1; $i<$beds; $i++) :
							?>
							<div class="housemateDetails">
								<h5>Housemate #<?php echo $i; ?>:</h5>
								<div class="housemateForm">
									<div class="grid3col left names">
										<div class="left">
											<label for="firstName<?php echo $i; ?>">First</label>
											<input class="required firstName" type="text" name="firstname<?php echo $i; ?>" id="firstName<?php echo $i; ?>" />
											<!-- <label for="name" generated="true" class="error">This field is required.</label> -->
										</div>
										<div class="left">
											<label for="lastName<?php echo $i; ?>">Last</label>
											<input class="required lastName" type="text" name="lastname<?php echo $i; ?>" id="lastName<?php echo $i; ?>" />
											<!-- <label for="name" generated="true" class="error">This field is required.</label> -->
										</div>
									</div>
									<div class="left marginleft grid2col">
										<div class="left postcode">
											<label for="postCode<?php echo $i; ?>">Postcode</label>
											<input class="required postCode" type="text" name="postcode<?php echo $i; ?>" id="postCode<?php echo $i; ?>" />
										</div>
									
										<div class="left marginleft postcode">
											<label for="houseNo<?php echo $i; ?>">House No.</label>
											<input class="required houseNo" type="text" name="address<?php echo $i; ?>" id="address<?php echo $i; ?>" />
											<!-- <?php // TO-DO: make this postcode lookup work ?>
											<input class="lookUp" type="button" name="lookup<?php echo $i; ?>" id="lookUp<?php echo $i; ?>" value="lookup" onclick="return false;"/>
											<select class="required address" name="address<?php echo $i; ?>" id="address<?php echo $i; ?>"></select>
	
											-->
										</div>
										<label for="useYourAddress<?php echo $i; ?>" class="useYourAddress">Tick here to use your address</label>
										<input type="checkbox" class="useYourAddress" id="useYourAddress<?php echo $i; ?>" name="useYourAddress<?php echo $i; ?>" /> 
									</div>
									
									
									<div class="grid2col left marginleft">
										<label for="email<?php echo $i; ?>">Email</label>
										<input class="required email" type="text" name="email<?php echo $i; ?>" id="email<?php echo $i; ?>" />
										<!-- <label for="name" generated="true" class="error">This field is required.</label> -->
									</div>
									
									<div class="grid2col left marginleft">
										<label for="phone<?php echo $i; ?>">Phone</label>
										<input class="required phone" type="text" name="phone<?php echo $i; ?>" id="phone<?php echo $i; ?>" />
										<!-- <label for="name" generated="true" class="error">This field is required.</label> -->
									</div>
								</div>
							</div>
							<?php
								endfor;
							?>
							<input class="right submitDetails" type="submit" name="submit_housemates" value="Send Housemate Details" />
						</form>
					</div>
	<?php endif; ?>
<?php endif; ?>
</div>