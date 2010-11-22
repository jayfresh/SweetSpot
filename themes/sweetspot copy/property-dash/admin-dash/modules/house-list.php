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
					query_posts('caller_get_posts=1&post_type=properties&post_status=publish&order=ASC');
					if ( have_posts() ) : while ( have_posts() ) : the_post(); 
					
					// Get the property values out of the post meta
							$postID = get_the_ID();
					
							$beds 		= get_post_meta($postID, '_beds', true);
							$baths 		= get_post_meta($postID, '_baths', true); 
							$receptions = get_post_meta($postID, '_receptions', true); 
							$price 		= get_post_meta($postID, '_price', true); 
							$period 	= get_post_meta($postID, '_period', true); 
							$notes	 	= get_post_meta($postID, '_notes', true); 
							$mapnotes 	= get_post_meta($postID, '_mapnotes', true); 
							$status 	= get_post_meta($postID, '_property_status', true); 	
							$address 	= get_post_meta($postID, '_address', true); 
							$city 		= get_post_meta($postID, '_city', true);  
							$postcode 	= get_post_meta($postID, '_postcode', true); 		
							$country 	= get_post_meta($postID, '_country', true);
							$deposit_payer = get_post_meta($postID, 'ipn_payer_email', true);
							
							// String manipulation for echoing property status :
							
							$pretty_status = str_replace ( "_" , " " , $status );
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
									<a href="#"><?php the_post_thumbnail( 'student-dash-thumb', array()); ?></a>
									<?php property_status_banner($status); ?>
									<a class="button red left margintopsmall">Reset Property</a>
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
	
									// get login for deposit_payer email
									$lead_tenant;
									foreach($persons as $person) {
										if($person->user_email == $deposit_payer) {
											$lead_tenant = $person;
										}
									}
									if(!$lead_tenant) {
										echo 'error: problem getting lead_tenant...';									
									} else {
									
									$group = $lead_tenant->user_login;
									$peoplenum		= 0;
									
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
															echo ' ' . $lead_tenant->last_name ; ?></strong>-->
															<strong><?php echo $lead_tenant->display_name; ?></strong>
															(Lead Tenant)
												<div class="grid24-4col right phone">
													<?php echo $lead_tenant->phone; ?>
												</div>
												<div class="grid24-4col right email">
													<a href="mailto:<?php echo $lead_tenant->user_login; ?>"><?php echo dot($lead_tenant->user_login, 20) ?></a>
												</div>
											</div>
											<div class="grid24-3col left marginleft">
												<?php
												if($lead_tenant->submitted_guarantor_info) { ?>
													<a class="button orange">Send to Paragon</a>
													<span class="note"><? echo $lead_tenant->submitted_guarantor_info; ?></span>
												<?php } else { ?>
													<a class="button noAction red">Not Received</a>
												<?php
												}
												?>
											</div>
											<div class="grid24-3col left marginleft">
												<?php
												if($lead_tenant->paid_security_deposit) { ?>
													<a class="button green noAction">Paid</a>
													<span class="note"><? echo dot($lead_tenant->paid_security_deposit, 10) ?></span>
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
													<a href="mailto:<?php echo $person->user_login; ?>"><?php echo dot($person->user_login, 20) ?></a>
												</div>
											</div>
											<div class="grid24-3col left marginleft">
												<?php
												if($person->submitted_guarantor_info) { ?>
													<a class="button orange">Send to Paragon</a>
													<span class="note"><? echo $person->submitted_guarantor_info; ?></span>
												<?php } else { ?>
													<a class="button noAction red">Not Received</a>
												<?php
												}
												?>
											</div>
											<div class="grid24-3col left marginleft">
												<?php
												if($person->paid_security_deposit) { ?>
													<a class="button green noAction">Paid</a>
													<span class="note"><? echo dot($person->paid_security_deposit, 10) ?></span>
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
												<a class="button <?php if($deposit_payer) { echo " grey"; } else { echo " orange"; }?>"><?php if($deposit_payer) { echo "N/A"; } else { echo "Pay"; }?></a>
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