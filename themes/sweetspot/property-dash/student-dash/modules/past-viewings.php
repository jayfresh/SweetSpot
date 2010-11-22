<?php
	// this module assumes that you can only pay a holding deposit on a property you have personally viewed - if that changes, we'd need to separate out a holding deposit module...
?>
<div class="module">
					<?php // collect properties you have booked viewings at
					global $post;
					$loop = new WP_Query('caller_get_posts=1&author='.$current_user->ID.'&post_type=viewings&post_status=publish&order=ASC');
					$viewings_booked = array();
					while ( $loop->have_posts() ) : $loop->the_post();
						$address = get_post_meta($post->ID,'address',true);
						$viewings_booked[$address] = array(
							'datetime'	=> get_post_meta($post->ID,'datetime',true)
						);
					endwhile; ?>
					<a href="#top" rel="self" class="right topLink">top &#94;</a>
					<a name="past_viewings"></a><h3 class="orange">Houses Viewed</h3>
					<p class="grid3col left">
						All of the Sweetspot homes you've viewed are listed here. Click the button below a property to secure a property with a holding deposit via Paypal.
					</p>
					<ul class="noBullets noMargin left houselist">
						<?php // House Loop
					
						$loop = new WP_Query('caller_get_posts=1&post_type=properties&post_status=publish');
						while ( $loop->have_posts() ) : $loop->the_post();
	
							$postID = 	$post->ID;
							$address 		= 	get_post_meta($postID,'_address',true);
							$postcode 		= 	get_post_meta($postID,'_postcode',true);
							$beds 			= 	get_post_meta($postID,'_beds',true);
							$viewings_match = 	$viewings_booked[$address];
							$status 		= 	get_post_meta($postID, '_property_status', true);
							$pretty_status 	= 	str_replace ( "_" , " " , $status ); // String manipulation for removing property status
							$datetime 		= 	strtotime(str_replace('/', '-', $viewings_match['datetime']));
							$viewingdate	=	date("H:i \\o\\n d/m/Y ", $datetime);
							$now			=	time();	
							
							// show the property if there is a viewing in the past
							if($viewings_match) :
								if($now > $datetime) :
							?>
						<li class="grid3col marginleft">
							<h6><strong><?php echo $address; ?>, <?php echo $postcode; ?></strong></h6>
							<div class="property_image">
								<a href="<?php get_bloginfo( "url" ); ?>/property" title="Property Page"><?php the_post_thumbnail( 'student-dash-thumb', array()); ?></a>
								<?php property_status_banner($status);?>
							</div>
							Viewing at <?php echo $viewingdate; ?>
							<br />
							<?php 
								if($status == "on_the_market" && !$paid_holding_deposit): ?>
							<a class="pay" href="#">Pay Deposit</a>
							<br class="clearboth" />
							<div class="small margintop pay_deposit_form">
							
							<?php 
								query_posts('pagename=holding-deposit-agreement');
								if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
									
							<?php 	$contento = get_the_content();
									$contento = str_replace ( "(deposit_amount)" , '£'.($beds * 50) , $contento ); 
									echo $contento; 
									?>

<?php
									endwhile;
								endif;
								
								wp_reset_query();

							?>
							
								<form class="margintop grid3col" action="https://www.sandbox.paypal.com/cgi-bin/webscr" method="post">
									<input type="hidden" name="cmd" value="_xclick" />
									<input type="hidden" name="currency_code" value="GBP" />
									<input type="hidden" name="business" value="jnthnl_1281699601_biz@gmail.com" />
									<!--<input type="hidden" name="notify_url" value="http://www.postbin.org/p5e6ay?http://test.sweetspot.com/property/handle_ipn" />-->
									<input type="hidden" name="notify_url" value="http://test.sweetspot.com/property/handle_ipn" />
									<input type="hidden" name="amount" value="<?php echo($beds * 50); ?>" />
									<input type="hidden" name="item_name" value="Holding Deposit" />
									<input type="hidden" name="item_number" value="<?php echo $address; ?>" />
									<input type="hidden" name="return" value="http://test.sweetspot.com/property/thanks" />
									<input type="hidden" name="image_url" value="http://dl.dropbox.com/u/331606/tmp/logotype.gif" />
									<input type="hidden" name="no_note" value="1" />
									<input type="hidden" name="no_shipping" value="1" />
									<input type="hidden" name="cancel_return" value="http://test.sweetspot.com/property/cancelled_deposit" />
									<input type="image" style="width:220px" class="clickable" src="<?php bloginfo('template_url'); ?>/images/paydeposit.gif" border="0" name="submit" alt="Pay deposit with PayPal">
									<input name="custom" id="custom" type="hidden" value="<?php echo $current_user->user_login; ?>" />
									<img alt="" border="0" src="https://www.paypal.com/en_GB/i/scr/pixel.gif" width="1" height="1">
								</form>
							</div>
							
							<?php 
								endif; 
							?>
						</li>
						<?php 
							endif;
						endif;
					endwhile; 
					?>
					</ul>
				</div>