<?php function datetime_sortfunc($a, $b) {
	if ($a["datetime"] == $b["datetime"]) {
		return 0;
	}
	return ($a["datetime"] > $b["datetime"]) ? -1 : 1;
}
?>
<div class="module">
					<!--<a href="#top" rel="self" class="right topLink">top &#94;</a>-->
					<h3 class="orange">Houses</h3>
					<ul class="propertyList noBullets noMargin">
					<?php
					
					$peopleIDs = $wpdb->get_col( $wpdb->prepare(
						"SELECT $wpdb->users.ID FROM $wpdb->users"
					));
					$persons = array();
					foreach($peopleIDs as $personID) {
						$person = get_userdata($personID);
						$persons[$person->user_login] = $person;
					}
					query_posts('caller_get_posts=1&post_type=properties&post_status=publish&meta_key=_city&orderby=meta_value&order=ASC');
					if ( have_posts() ) : while ( have_posts() ) : the_post(); 

						$postID = get_the_ID();
					
						if($_POST['reset_property']) {
							reset_property($postID);
						}

						// Get the property values out of the post meta
						$beds 			= get_post_meta($postID, '_beds', true);
						$baths 			= get_post_meta($postID, '_baths', true); 
						$receptions 	= get_post_meta($postID, '_receptions', true); 
						$price 			= get_post_meta($postID, '_price', true); 
						$period 		= get_post_meta($postID, '_period', true); 
						$notes	 		= get_post_meta($postID, '_notes', true); 
						$mapnotes		= get_post_meta($postID, '_mapnotes', true); 
						$status 		= get_post_meta($postID, '_property_status', true); 	
						$address 		= get_post_meta($postID, '_address', true); 
						$city 			= get_post_meta($postID, '_city', true);  
						$postcode 		= get_post_meta($postID, '_postcode', true); 		
						$country 		= get_post_meta($postID, '_country', true);
						$deposit_payer 	= get_post_meta($postID, 'ipn_payer_email', true);
						$ipn_date		= get_post_meta($postID, 'ipn_datetime', true);
						$now 			= time();
						
						// Date Differencing :
						if ($ipn_date) {
							$interval = date_diff($now, $ipn_date);
						}
						// String manipulation for echoing property status :
						$pretty_status = str_replace ( "_" , " " , $status );
						
						// get login for deposit_payer email
						$lead_tenant;
						foreach($persons as $person) {
							if($person->user_email == $deposit_payer) {
								$lead_tenant = $person;
							}
						}
						?>
						<li>
							<h5><?php
								echo $address;
								echo ', '.$city;
								echo ' - <em>'.str_replace('_', ' ', $status).'</em>';
								echo ' &#x2193;';
							?></h5>
							<div>
								<div class="grid3col left property_image">
									<a href="<?php get_bloginfo( "url" ); ?>/property" title="Property Page"><?php the_post_thumbnail( 'student-dash-thumb', array()); ?></a>
									<?php property_status_banner($status); ?>
									<?php if ($ipn_date) { echo "<p class='margintopsmall last'>Under offer for {$interval} days</p>"; } ?>
									<?php if($_POST['reset_property']) :
										echo "<p>Property has been reset</p>";
									else :
										if($lead_tenant) :
									?>
									<a class="reset_property button red left margintopsmall">Reset Property</a>
									<div class="reset_property clearboth">
										<p>At the moment, we cannot refund PayPal payments automatically. The transaction references are noted below for your convenience:
										</p>
										<ul>
											<?php
											$security_deposits = 0;
											$group = $lead_tenant->user_login;
											foreach ($persons as $person) {
												if($person->group==$group && $person->paid_security_deposit) {
													$security_deposit = $person->paid_security_deposit;
													$security_deposits++;
													$link = makePayPalLink($security_deposit);
													echo "<li>{$link}</li>";
												}
											}
											if ($security_deposits==0) {
												echo "<li>No security deposits to show</li>";
											}
											?>
										</ul>
										<form method="post">
											<label for="reset_property">By clicking the button below, you will put <?php echo $address; ?> back on to the market. Any tenants participating in the moving-in process will be notified by email.</label>
											<input type="submit" class="red button" name="reset_property" id="reset_property" value="Reset <?php echo $address; ?>" />
										</form>
									</div>
									<?php
										endif;
									endif; ?>
								</div>
								<?php // pseudo-code
								// if there is a $deposit_payer, display the active group chunk
								// use $deposit_payer later to determine whether to show "Other" for "Other applicants"
								// if not, skip straight to Applicants
								if($deposit_payer) :
								?>
								<h6 class="grid9col right"><strong>Active Group</strong></h6>
								<div class="grid9col box right marginleft">
									<ul class="noBullets noMargin peopleList">
									<?php

									if(!$lead_tenant) {
										echo 'error: problem getting lead_tenant...';									
									} else {
									
									$group = $lead_tenant->user_login;
									$peoplenum		= 0;
									
									if($_GET['paragon_response'] && $_GET['paragon_response']=='OK') {
										$paragon_reference = $_GET['paragon_reference'];
										$submitter_login = $_GET['submitter'];
										if($submitter_login) {
											$submitter = get_userdatabylogin($submitter_login);
											update_user_meta($submitter->ID, 'submitted_to_paragon', $paragon_reference);
											// refresh the user object after updating the meta
											if($submitter_login==$lead_tenant->user_login) {
												$lead_tenant = get_userdatabylogin($submitter_login);
											} else {
												foreach ($persons as $id => $person) {
													if($submitter_login == $person->user_login) {
														$persons[$id] = get_userdatabylogin($submitter_login);
													}
												}
											}
											// it's possible this is the last action in a progress step, so check whether all students in the group are ready
											checkAllApplicants($postID); // $postID is the property ID
										}
									}
									
									?>								
										<li class="grid24-17col heading marginbottomsmall marginleft">
											<div class="grid24-5col left">
												Applicant
											</div>
											<div class="grid24-3col left marginleft">
												Guarantor
											</div>
											<div class="grid24-3col left marginleft">
												Deposit
											</div>
											<div class="grid24-3col left marginleft greyed">
												DD
											</div>
											<div class="grid24-3col left marginleft greyed">
												ASTs
											</div>	
											<br class="clearboth" />
										</li>
										<?php // do the lead tenant first ?>
												<li class="grid24-17col applicant marginleft">
												<?php $peoplenum++;?>
											<div class="grid24-5col left">
												<!--<strong><?php echo $lead_tenant->first_name;
															echo ' ' . $lead_tenant->last_name; ?></strong>-->
															<strong><?php echo $lead_tenant->display_name; ?></strong>
															(Lead Tenant)
												<div class="grid24-4col right phone">
													<?php echo $lead_tenant->phone; ?>
												</div>
												<div class="grid24-4col right email">
													<a href="mailto:<?php echo $lead_tenant->user_login; ?>"><?php echo dot($lead_tenant->user_login, 20); ?></a>
												</div>
											</div>
											<div class="grid24-3col left marginleft">
												<?php
												if($lead_tenant->submitted_guarantor_info) {
													if($lead_tenant->submitted_to_paragon) { ?>
												<a class="button green noAction">Sent</a>
												<span class="note"><?php echo $lead_tenant->submitted_to_paragon; ?></span>
												<?php } else {
														$guarantor_title = $lead_tenant->guarantor_title;
														$guarantor_first_name = $lead_tenant->guarantor_first_name;
														$guarantor_last_name = $lead_tenant->guarantor_last_name;
														$guarantor_email = $lead_tenant->guarantor_email;
														$tenancy_start = date("d/m/Y", time()+(60*60*24*30)); // 30 days in the future
												?>
												<form action="http://jnthnlstr.no.de/submit" method="post" id="paragon_form">
													<input type="hidden" name="postcode" value="<?php echo $postcode; ?>" />
													<input type="hidden" name="building_name" value="<?php echo $address; ?>" />
													<input type="hidden" name="town" value="<?php echo $city; ?>" />
													<input type="hidden" name="property_type" value="DETACHED" />
													<input type="hidden" name="bedrooms" value="<?php echo $beds; ?>" />
													<input type="hidden" name="rent" value="<?php echo $price; ?>" />
													<input type="hidden" name="tenancy_start" value="<?php echo $tenancy_start; ?>" />
													<input type="hidden" name="no_of_tenants" value="<?php echo $beds; ?>" />
													<input type="hidden" name="title" value="<?php echo $guarantor_title; ?>" />
													<input type="hidden" name="first_name" value="<?php echo $guarantor_first_name; ?>" />
													<input type="hidden" name="last_name" value="<?php echo $guarantor_last_name; ?>" />
													<input type="hidden" name="email" value="<?php echo $guarantor_email; ?>" />
													<input type="hidden" name="submitter" value="<?php echo $lead_tenant->user_login; ?>" />
													<input type="submit" class="button orange" name="submit_to_paragon" value="Send on" />
													<!-- used to be <a class="button orange">Send to Paragon</a> -->
												</form>
												<span class="note"><?php echo $guarantor_first_name.' '.$guarantor_last_name; ?></span>
												<?php }
												} else { ?>
												<a class="button noAction red">Not Received</a>
												<?php
												}
												?>
											</div>
											<div class="grid24-3col left marginleft">
												<?php
												if($lead_tenant->paid_security_deposit) { ?>
													<a class="button green noAction">Paid</a>
													<span class="note"><?php echo dot($lead_tenant->paid_security_deposit, 10); ?></span>
												<?php } else { ?>
													<a class="button red noAction">Not Paid</a>
												<?php
												}
												?>
											</div>
											<div class="grid24-3col left marginleft">
												<?php
												if($lead_tenant->setup_dd) { ?>
													<a class="button green">Ref</a>
												<?php } else { ?>
													<a class="button grey">N/A</a>
												<?php
												}
												?>
											</div>
											<div class="grid24-3col left marginleft">
												<a class="button grey">N/A</a>
											</div>
											<?php
									foreach ($persons as $person) {
										if($person->ID!=$lead_tenant->ID) {
											if($person->group==$group) {
												// do something, this is a member of the group
												?>
												<li class="grid24-17col applicant marginleft">
													<?php $peoplenum++;?>
											<div class="grid24-5col left">
												<strong><?php echo $person->first_name;
															echo ' ' . $person->last_name ; ?></strong>
												<div class="grid24-4col right phone">
													<?php echo $person->phone; ?>
												</div>
												<div class="grid24-4col right email">
													<a href="mailto:<?php echo $person->user_login; ?>"><?php echo dot($person->user_login, 20); ?></a>
												</div>
											</div>
											<div class="grid24-3col left marginleft">
												<?php
												if($person->submitted_guarantor_info) {
													if($person->submitted_to_paragon) { ?>
												<a class="button green noAction">Sent</a>
												<span class="note"><?php echo $person->submitted_to_paragon; ?></span>
												<?php } else {
														$guarantor_title = $person->guarantor_title;
														$guarantor_first_name = $person->guarantor_first_name;
														$guarantor_last_name = $person->guarantor_last_name;
														$guarantor_email = $person->guarantor_email;
														$tenancy_start = date("d/m/Y", time()+(60*60*24*30)); // 30 days in the future
												?>
												<form action="http://jnthnlstr.no.de/submit" method="post" id="paragon_form">
													<input type="hidden" name="postcode" value="<?php echo $postcode; ?>" />
													<input type="hidden" name="building_name" value="<?php echo $address; ?>" />
													<input type="hidden" name="town" value="<?php echo $city; ?>" />
													<input type="hidden" name="property_type" value="DETACHED" />
													<input type="hidden" name="bedrooms" value="<?php echo $beds; ?>" />
													<input type="hidden" name="rent" value="<?php echo $price; ?>" />
													<input type="hidden" name="tenancy_start" value="<?php echo $tenancy_start; ?>" />
													<input type="hidden" name="no_of_tenants" value="<?php echo $beds; ?>" />
													<input type="hidden" name="title" value="<?php echo $guarantor_title; ?>" />
													<input type="hidden" name="first_name" value="<?php echo $guarantor_first_name; ?>" />
													<input type="hidden" name="last_name" value="<?php echo $guarantor_last_name; ?>" />
													<input type="hidden" name="email" value="<?php echo $guarantor_email; ?>" />
													<input type="hidden" name="submitter" value="<?php echo $person->user_login; ?>" />
													<input type="submit" class="button orange" name="submit_to_paragon" value="Send on" />
													<!-- used to be <a class="button orange">Send to Paragon</a> -->
												</form>
												<span class="note"><?php echo $guarantor_first_name.' '.$guarantor_last_name; ?></span>
												<?php }
												} else { ?>
												<a class="button noAction red">Not Received</a>
												<?php
												}
												?>
											</div>
											<div class="grid24-3col left marginleft">
												<?php
												if($person->paid_security_deposit) { ?>
													<a class="button green noAction">Paid</a>
													<span class="note"><?php echo dot($person->paid_security_deposit, 10) ?></span>
												<?php } else { ?>
													<a class="button red noAction">Not Paid</a>
												<?php
												}
												?>
											</div>
											<div class="grid24-3col left marginleft">
												<?php
												if($person->setup_dd) { ?>
													<a class="button green">Ref</a>
												<?php } else { ?>
													<a class="button grey">N/A</a>
												<?php
												}
												?>
											</div>
											<div class="grid24-3col left marginleft">
												<a class="button grey">N/A</a>
											</div>
											<?php
											}
										}
									}
									?>																				
										</li>
										<?php for ($i = $peoplenum; $i < $beds; $i++) : ?>
										<li class="grid24-17col applicant marginleft">
											<div class="grid24-5col left">
												<strong>Unfilled Slot</strong>
												<div class="grid24-4col right phone">
													
												</div>
												<div class="grid24-4col right email">
													
												</div>
											</div>
											<div class="grid24-3col left marginleft">
												<a class="button grey">N/A</a>
											</div>
											<div class="grid24-3col left marginleft">
												<a class="button grey">N/A</a>
											</div>
											<div class="grid24-3col left marginleft">
												<a class="button grey">N/A</a>
											</div>
											<div class="grid24-3col left marginleft">
												<a class="button grey">N/A</a>
											</div>																				
										</li>
										<?php endfor;
										
										} // the end of the else after checking lead_tenant
										?>
									</ul>
								</div>
								<?php endif; ?>
								<?php // collect properties you have booked viewings for
										
							     	// pseudo code:
							     	// 1. find all viewings posts.
							     	// 2. get their addresses & times
							     	// 3. get their author ids
							     	// 4. if the viewing address matches the address for the main loop above, display user data
							     	// 5. loop through matching viewing / addresses displaying all users.
							     	
							     	//$my_array = array();
							     	//$my_array[] = $new_thing;
							     	// $string_thing = implode(",", $my_array);
							     	
							     	$all_emails = array();
							     	$future_app_emails = array();
							     	$past_app_emails = array();
							     	
							     	
									global $post;
									$loop = new WP_Query('caller_get_posts=1&post_type=viewings&post_status=publish&order=ASC');
									$viewings_booked = array();
									while ( $loop->have_posts() ) : $loop->the_post();
										$view_address 	= get_post_meta($post->ID,'address',true);
										
										$id		 		= get_the_author_meta('ID');
										$applicant 		= get_userdata($id);
										$ugly_date 		= get_post_meta($post->ID,'datetime',true);
										$now			= time();
										
										$datetime 		= 	strtotime(str_replace('/', '-', $ugly_date));
										$pretty_date	=	"<strong>".date("H:i", $datetime)."</strong> ".date(" d/m/Y ", $datetime);
										if($address == $view_address) {
											if(!$deposit_payer || $deposit_payer != $applicant->user_login) {
												$viewings_booked[] = array(
													"datetime"=>$datetime,
													"pretty_date"=>$pretty_date,
													"applicant"=>$applicant
												);
											}
										}
										
									endwhile;
									
									 ?>
								<?php if(!count($viewings_booked)) : ?>
								<h6 class="grid9col right"><strong>No applicants to show</strong></h6>
								<?php else :?>
								<h6 class="grid9col right<?php if($deposit_payer) { echo " margintopsmall"; } ?>"><strong><?php if($deposit_payer) { echo "Other "; } ?>Applicants</strong></h6>
									<div class="grid9col box right marginleft">
									<?php
										uasort($viewings_booked, datetime_sortfunc);
									?>
									<ul class="noBullets noMargin peopleList">
										<li class="grid24-17col heading marginbottomsmall marginleft">
											<div class="grid24-5col left">
												Applicant
											</div>
											<div class="grid24-3col left marginleft">
												Viewing
											</div>
											<div class="grid24-3col left marginleft">
												Phone
											</div>
											<div class="grid24-3col left marginleft">
												Email
											</div>
											<div class="grid24-3col left marginleft">
												Holding Deposit
											</div>	
											<br class="clearboth" />
										</li>
										<?php // do step 5
										foreach ($viewings_booked as $viewing) :
											$applicant = $viewing['applicant'];
											$pretty_date = $viewing['pretty_date'];
											$datetime = $viewing['datetime'];
										?>
										<li class="grid24-17col applicant marginleft">
											<div class="grid24-5col left">
												<strong><?php
													/*$name = $applicant->first_name.' '.$applicant->last_name;
													if($name==' ') {
														$name = "n/a";
													}*/
													$name = $applicant->display_name;
													if(!$name) {
														$name = "n/a";
													}
												echo $name; ?>
												</strong>
											</div>
											<div class="grid24-3col left marginleft">
												<?php echo $pretty_date;?>
											</div>
											<div class="grid24-3col left marginleft phone">
												<?php
													$phone = $applicant->phone ? $applicant->phone : "n/a";
													echo $phone; 											
												?>
											</div>
											<div class="grid24-3col left marginleft email">
												<a href="mailto:<?php 
												$all_emails[] = $applicant->user_email;
												if ($now < $datetime){ 
													$future_app_emails[] = $applicant->user_email;
												} else if ($now >= $datetime){
													$past_app_emails[] = $applicant->user_email;
												}
												echo $applicant->user_login; ?>"><?php echo dot($applicant->user_email, 20); ?></a>
											</div>
											<div class="grid24-3col left marginleft alignright">
											<?php if ($deposit_payer) : ?>
												<span>N/A</span>
											<?php else : ?>
												<form method="post" action="handle_ipn">
													<input type="hidden" value="Cash" name="txn_id" />
													<input type="hidden" value="Completed" name="payment_status" />
													<input type="hidden" value="Holding Deposit" name="item_name" />
													<input type="hidden" value="<?php echo $address; ?>" name="item_number" />
													<input type="hidden" value="<?php echo $applicant->user_login; ?>" name="custom" />
													<input type="submit" class="button orange pay" value="Pay" />
												</form>
											<?php endif; ?>
											</div>																				
										</li>
										<?php
										endforeach;
										?>
									</ul>
									<div class="grid5col right">
										<h6 class="left">Email:</h6>
										<a href="mailto:<?php 	$all_list = implode(", ", $all_emails);
												echo $all_list;
										 ?>" class="button orange left marginleft">All</a>
										<a href="mailto:<?php 	$past_list = implode(", ", $past_app_emails);
												echo $past_list;
										 ?>" class="button left marginleft <?php
										 	if (count($past_app_emails) == 0 ) {
										 		echo 'grey';
										 	} else {
										 		echo 'orange';
										 	}
										 ?>">Past Viewings</a>
										<a href="mailto:<?php 	$future_list = implode(", ", $future_app_emails);
												echo $future_list;
										 ?>" class="button left marginleft <?php
										 	if (count($future_app_emails) == 0 ) {
										 		echo 'grey';
										 	} else {
										 		echo 'orange';
										 	}
										 ?>">Future Viewings</a>
									</div>
								</div>
								<?php
									endif;
								?>
								<br class="clearboth"/>
							</div>
						</li>
					
					<?php endwhile;
						endif;	
					?>
					</ul>
					
					
				</div>