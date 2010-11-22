<?php include( TEMPLATEPATH . '/property-dash/student-dash/student-dash-header.php' ); ?>
		<div id="wrap" class="jbasewrap">
			<div id="header">
				<h1 class="padtop left"><a class="grid2col imgreplace" href="#">Sweetspot</a></h1>
				<ul>
					<li><a class="orange" rel="self" title="Log Out" href="<?php echo wp_logout_url( get_permalink() ); ?>">Log Out &raquo;</a></li>
					<li><a class="orange" rel="self" href="#guarantors">Guarantors</a></li>
					<li><a class="pink" rel="self" href="#housemates">Housemates</a></li>
					<li><a class="green" rel="self" href="#progress">Progress</a></li>
					<li><a class="blue" rel="self" href="#deposit">Pay Deposit</a></li>
				</ul>
				<h3 class="grid6col left marginleft">Hi There, <?php echo $current_user->display_name; ?>!</h3>
				<p class="grid6col left marginleft">Welcome to your SweetSpot student dashboard. From here you can see your viewings, check your moving-in progress, and later it will provide house documents, access to smartmeter systems, our services, and more.</p>				
			</div>
			<?php
				$in_group = $current_user->group;
				$is_lead_tenant = $in_group && $in_group == $current_user->user_login;
				if($is_lead_tenant) {
					$lead_tenant_email = $current_user->user_email;
				} else {
					$lead_tenant_email = get_userdatabylogin($in_group)->user_email; // JRL: technically, the group value should be the email of the lead tenant, since accounts are meant to have email addresses as their login ID's, but this is more robust
				}
				
				// flags
				$paid_holding_deposit; // set below
				$paid_security_deposit = $current_user->paid_security_deposit;
				if($is_lead_tenant) {
					$submitted_housemate_info = $current_user->submitted_housemate_info;
				}
				$submitted_guarantor_info = $current_user->submitted_guarantor_info;
				$set_up_DD; // TO-DO: support this flag when the module is working
				$signed_AST; // TO-DO: support this flag when the module is working

				
				//print_r($current_user);
				//echo "ID: {$current_user->id}<br/>";
				//echo "email: {$email}";
				
				// set $paid_holding_deposit flag
				if($lead_tenant_email) { // don't bother looking if there is no lead tenant
					$loop = new WP_Query("post_type=properties");
					global $post;
					while ( $loop->have_posts() ) : 
						$loop->the_post();
						$the_ID = get_the_ID();
						$IPN_payer_email = get_post_meta($the_ID, 'ipn_payer_email', true);
						if($IPN_payer_email==$lead_tenant_email) {
							$paid_holding_deposit = $the_ID;
							$paid_holding_deposit_address = get_post_meta($the_ID, '_address', true);
						}
					endwhile;
				}
			?>
			<div id="modules">
				<?php include( TEMPLATEPATH . '/property-dash/student-dash/modules/progress.php' ); ?>
				<?php include( TEMPLATEPATH . '/property-dash/student-dash/modules/future-viewings.php' ); ?>
				<?php include( TEMPLATEPATH . '/property-dash/student-dash/modules/past-viewings.php' ); ?>
				<?php if ($progress_step > 1) {
					if($is_lead_tenant) {
						include( TEMPLATEPATH . '/property-dash/student-dash/modules/housemates.php' );
					}
					include( TEMPLATEPATH . '/property-dash/student-dash/modules/security-deposit.php' );
					include( TEMPLATEPATH . '/property-dash/student-dash/modules/guarantor.php' );
				}
				?>
			</div>
			
			<div class="push"></div>
		</div>
		<?php include( TEMPLATEPATH . '/property-dash/student-dash/student-dash-footer.php' ); ?>
		