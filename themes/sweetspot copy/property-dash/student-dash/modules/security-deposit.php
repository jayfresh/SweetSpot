<?php
	// this module should be shown if $paid_security_deposit flag is not set
	// the result of receiving the IPN for the payment should be to set the flag
	// keep the IPN reference as the value of the flag
?>
<div class="module securityPayment">
					<a name="deposit"></a><h3 class="orange">Pay Your Security Deposit and 1<sup>st</sup> Month Rent</h3>
					<p class="grid3col left">
						Your security deposit is equal to 6 weeks rent, and will cover damage to the property. All of your housemates and yourself need to have paid your security deposit and first month's rent in advance, in cleared funds, before you can move in. It can take a couple of days for your payment to clear, but once it has your status will be updated.<br /> <a href="http://www.sweetspot.com/faq#q34">What will happen to my deposit?</a>
					</p>
					<div class="grid9col right">
						<div class="grid5col left">
							<h5>Payment Details</h5>
							<?php if($current_user->paid_security_deposit) : ?>
							<p>You've paid! Thanks.</p>
							<?php
							else :
								// House Loop
								$loop = new WP_Query('caller_get_posts=1&post_type=properties&post_status=publish');
								while ( $loop->have_posts() ) : $loop->the_post();
			
									$postID 		= 	$post->ID;
									$ipn_payer_email 		= 	get_post_meta($postID,'ipn_payer_email',true);
									$address		=	get_post_meta($postID, '_address', true);
									$beds 			= 	get_post_meta($postID,'_beds',true);
									$price 			= 	get_post_meta($postID,'_price',true);
									
									//if($ipn_email == $current_user->user_email || $ipn_email == $lead_tenant_email) :
									if($address == $paid_holding_deposit_address) : // the one property we care about
									?>
							<table id="payment" class="grid5col left" border="0" cellspacing="0" cellpadding="0">
								<tr>	
						      		<th class="grid4col">Item</th>
						        	<th class="grid1col">Sum</th>
						      	</tr>
						      	<tr>
						      		<td>Security Deposit (6 Weeks Rent)</td>
						        	<td>£ <?php 
						        		$six_weeks = ($price * 6); 
						        		echo $six_weeks;
						        	?></td>
						      	</tr>
						      	<tr>
						      		<td>1 month rent in advance</td>
						        	<td>£ <?php 
						        		$one_month = round((($price * 52) / 12), 2);
						        		echo $one_month;
						        		 ?></td>
						      	</tr>
						      	<?php if($ipn_payer_email == ($current_user->user_email)) : ?>
						      	<tr class="reduction">
						      		<td>- holding deposit</td>
						         	<td>-£ <?php $holding_refund = ($beds * 50); 
						         			echo $holding_refund;
						         	?></td>
						      	</tr>
						      	<?php endif; ?>
						      	<tr class="total">
						      		<td class="alignright">Total:</td>
						        	<td class="figure">£<?php $security_deposit = $six_weeks + $one_month - $holding_refund;
						        	echo $security_deposit; ?>
						        	</td>
						      	</tr>
							</table>
								<?php 
									endif;
								endwhile; 
								?>
							<form class="margintop" action="https://www.sandbox.paypal.com/cgi-bin/webscr" method="post">
								<input type="hidden" name="cmd" value="_xclick" />
								<input type="hidden" name="currency_code" value="GBP" />
								<input type="hidden" name="business" value="jnthnl_1281699601_biz@gmail.com" />
								<input type="hidden" name="notify_url" value="http://www.postbin.org/p5e6ay?http://test.sweetspot.com/property/handle_ipn" />
								<input type="hidden" name="amount" value="<?php echo $security_deposit; ?>" />
								<input type="hidden" name="item_name" value="Security Deposit" />
								<input type="hidden" name="item_number" value="<?php echo $address; ?>" />
								<input type="hidden" name="return" value="http://test.sweetspot.com/property/thanks_security_deposit" />
								<input type="hidden" name="image_url" value="http://dl.dropbox.com/u/331606/tmp/logotype.gif" />
								<input type="hidden" name="no_note" value="1" />
								<input type="hidden" name="no_shipping" value="1" />
								<input type="hidden" name="cancel_return" value="http://test.sweetspot.com/property/cancelled_deposit" />
								<button class="pay" type="submit" border="0" name="submit" alt="Pay security deposit &amp; rent with PayPal">Pay Security Deposit &amp; Rent</button>
								<input name="custom" id="custom" type="hidden" value="<?php echo $current_user->user_login; ?>" />
								<img alt="" border="0" src="https://www.paypal.com/en_GB/i/scr/pixel.gif" width="1" height="1">
							</form>
							<?php
							endif;
							?>
						</div>
						<div class="grid4col right">
							<h5>Housemates Status</h5>
							<table id="housemateStatus" class="grid4col right" border="0" cellspacing="0" cellpadding="0">
								<thead>
									<tr>	
							      		<th class="grid3col">Housemate</th>
							        	<th class="grid1col">Status</th>
							      	</tr>
								</thead>
								<tbody>
								<?php
								$group = $current_user->group;
								$peopleIDs = $wpdb->get_col( $wpdb->prepare(
									"SELECT $wpdb->users.ID FROM $wpdb->users"
								));
								foreach ($peopleIDs as $personID) {
									if($personID!=$current_user->ID) {
										$person = get_userdata($personID);
										if($person->group==$group) {
											$paid = $person->paid_security_deposit ? "paid" : "unpaid";
										?>
									<tr class="<?php echo $paid; ?>">
										<!--<td><?php echo $person->first_name." ".$person->last_name; ?></td>-->
										<td><?php echo $person->display_name; ?></td>
										<td><?php echo $paid; ?></td>
									</tr>
										<?php
										}
									}
								}
								?>
								</tbody>
							</table>
						</div>
						
					
					</div>
				</div>