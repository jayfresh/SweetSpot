<div class="module">
					<a href="#top" rel="self" class="right topLink">top &#94;</a>
					<a name="guarantor"></a><h3 class="pink">Your Guarantor</h3>
					

<?php
	// this module should be shown if $submitted_guarantor_info flag is not set
	// the result of submitting the info should set the flag
	
	// the info should be saved as the value of the flag, so it can be picked up and used by the manager
	// this means there will be another flag called $submitted_to_paragon or something like that, which gets set when the manager sends the info on to Paragon... and then there will need to be a flag to say whether the tenant has been accepted or rejected


// this module should be shown if $submitted_guarantor_info flag is not set
// the result of submitting the info should set the flag
if ($_POST && $_POST['submit_guarantor']) :
	$current_user_id = $current_user->id;
	update_user_meta($current_user_id, 'submitted_guarantor_info', 'yes');
	update_user_meta($current_user_id, 'guarantor_first_name', $_POST['first_name']);
	update_user_meta($current_user_id, 'guarantor_last_name', $_POST['last_name']);
	update_user_meta($current_user_id, 'guarantor_email', $_POST['email']);
	update_user_meta($current_user_id, 'guarantor_phone', $_POST['phone']);
	
	$person = get_userdata($current_user_id);
	
	$manager_email_text = "Hello there! {$person->display_name} has submitted their guarantor information for {$paid_holding_deposit_address}. Please send on to Paragon at: http://sweetspot.com/property/dash"; // TO-DO: get this into an admin screen
	$welcome_email_subject = "Guarantor information submission from {$person->display_name}"; // TO-DO: get this into an admin screen
	
	sweetspot_mail('glenn@sweetspot.com', $manager_email_subject, $manager_email_text, true); // TO-DO: get manager email dynamically
	?>
	<p class="grid3col left">Thanks for submitting your guarantor information. We will contact them and make reference checks over the next few days. </p>
	<?php
else :
	
?>

					<?php if ($submitted_guarantor_info) : ?>
						<p class="grid3col left">You have already submitted your guarantor information. Thanks!</p>
						<?php if(!$submitted_to_paragon) : ?>
						<p class="grid3col left">Just so you know, we haven't sent off the reference check yet.</p>
						<?php endif; ?>
					<?php else: ?>
					
					<p class="grid3col left">
						Please enter your guarantor's details here, so we can contact them for references and credit checks.
					</p>
					<div class="grid9col right marginleft">
						<form id="guarantorDetails" method="post">
							<div class="guarantorDetails">
								<h5>Your Guarantor:</h5>
								<div class="grid3col left names">
									<div class="left">
										<label for="first_name">First</label>
										<input class="required first_name" type="text" name="first_name" id="first_name">
										<!-- <label for="name" generated="true" class="error">This field is required.</label> -->
									</div>
									<div class="left">
										<label for="lastName">Last</label>
										<input class="required last_name" type="text" name="last_name" id="last_name">
										<!-- <label for="name" generated="true" class="error">This field is required.</label> -->
									</div>
								</div>								
								<div class="grid2col left marginleft">
									<label for="email">Email</label>
									<input class="required email" type="text" name="email" id="email">
									<!-- <label for="name" generated="true" class="error">This field is required.</label> -->
								</div>
								
								<div class="grid2col left marginleft">
									<label for="phone">Phone</label>
									<input class="required phone" type="text" name="phone" id="phone">
									<!-- <label for="name" generated="true" class="error">This field is required.</label> -->
								</div>
							</div>
							<input class="right submitDetails" type="submit" name="submit_guarantor" value="Send Guarantor Details">
						</form>
					</div>
					<?php 
					endif; 
endif; 				?>
				</div>