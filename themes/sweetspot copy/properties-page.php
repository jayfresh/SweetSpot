<?php 
/*

Template Name: Properties-page

*/


get_header() ?>
<div id="content"> 
		<div class="container"> 
			<div class="description"> 
				<br>
					<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
					<?php the_content(); ?>
					<?php endwhile; endif; ?>
				<br> 
				<img src='<?php bloginfo('template_url'); ?>/images/chair_2_blue.png' class='hero_floater' alt="chair"> 
			</div> 
		
 
			<a rel='self' href='#contact'>Get in touch</a> if you'd like us to get things moving in a town near you.
			
			
			<div class='properties'>
				<?php // get the text for the Pay Deposit tab
					$tempPost = $post;
					$featuredPosts = new WP_Query();
					$featuredPosts->query('pagename=holding-deposit-agreement');
					while ($featuredPosts->have_posts()) : $featuredPosts->the_post();
						//$post = $featuredPosts[0];
						$holding_deposit_text = get_the_content();
					endwhile;
					$post = $tempPost;
				?>
				<?php query_posts('post_type=properties'); ?>
				<?php if (have_posts()) : ?>
				<?php $count = 0; ?>
				
				<?php while (have_posts()) : the_post(); ?>
				<?php $count++; ?>
				<div id="property-tabs" class="vcard"> 
					<ul class="tabnav"> 
						<li> 
							<a href='#details<?php echo $count; ?>'>Details</a> 
						</li> 
						<li> 
							<a href='#photos<?php echo $count; ?>'>More photos</a> 
						</li> 
						<li> 
							<a href='#floorplan<?php echo $count; ?>'>Floorplan</a> 
						</li> 
						<li> 
							<a href='#map-embed<?php echo $count; ?>'>Map</a> 
						</li> 
						<li> 
							<a href='#bookview<?php echo $count; ?>'>Book a Viewing</a> 
						</li> 
						<li> 
							<a href='#paydeposit<?php echo $count; ?>'>Pay Deposit</a> 
						</li>
					</ul>	
	
					<div id="details<?php echo $count; ?>" class='property vcard tabdiv'>	
						<div class="property_image">							
							<?php the_post_thumbnail( 'main-property-thumb', array( 'class' => 'left' )); ?>
							
							<?php  
									$property_status = get_post_meta($post->ID, '_property_status', true);
								if($property_status=="under_offer") {
									echo '<span class="propertyUnderOffer"></span>';
								} else if($property_status=="let") {
									echo '<span class="propertyLet"></span>';
								}
							?>
						</div>
						<div class="adr"> 
							<div class="street-address"> 
								<?php get_custom_field('_address'); ?>
							</div> 
							<div> <?php $town = get_custom_field('_city', array('raw'=>'true')); ?>
								<abbr class="region" title="<?php echo $town; ?>"><?php echo $town; ?></abbr>, <span class="postal-code"><?php get_custom_field('_postcode'); ?></span></div> 
							<div class="country-name"> 
								<?php get_custom_field('_country'); ?>
							</div>
						</div> 
						<div class='details'> 
							<p class='bedrooms'><?php get_custom_field('_beds'); ?> bedrooms</p> 
							<p class='bathrooms'><?php get_custom_field('_baths'); ?> bathrooms</p> 
							<p class='receptions'><?php get_custom_field('_receptions'); ?> receptions</p> 
							<p class='price'>&pound;<?php get_custom_field('_price'); ?> per person per week</p> 
							<p><?php get_custom_field('_period'); ?></p>
							<p><?php get_custom_field('_notes'); ?></p>
							<br>		
						</div>					
					</div> 
					
					<div id="photos<?php echo $count; ?>" class='property tabdiv'> 
						<?php attachment_toolbox('property-gall-thumb', 'house', $count, "gallery", "<p>Photos coming soon...</p>"); ?>
					</div> 
					
					<div id="map-embed<?php echo $count; ?>" class='property tabdiv'> 
						<div class="left map"> 
							<?php echo do_shortcode('[mappress]'); ?>
						</div> 
						<div class="maptext left"> 
							<?php get_custom_field('_mapnotes'); ?>
						</div> 
						
					</div> 
					
					<div id="floorplan<?php echo $count; ?>" class='property tabdiv'> 
						<?php attachment_toolbox('property-gall-thumb', 'floorplan', $count, "gallery outlined", "<p>Floorplan coming soon...</p>"); ?>
					</div> 
					
					<div id="bookview<?php echo $count; ?>" class='property tabdiv'>
						<?php if ($property_status=="let") : ?>			
						<p>This property is let.</p>
						<?php else: ?>
						<?php $address = get_post_meta($post->ID, '_address', true);
						$add2 = rawurlencode($address);
						echo "<iframe name='bookings' width='780px' height='630px' src='http://sweetsoft.joshuabradley.co.uk/booking?accountID=Joshua&property=". $add2 ."' scrolling='no'></iframe>";
						
						?>
						<?php endif; ?>
					</div>
					
					<div id="paydeposit<?php echo $count; ?>" class='property tabdiv'> 
						<?php if ($property_status=="on_the_market") : ?>							
							
							<?php echo $holding_deposit_text; ?>
							
							
						<form class="margintop deposit_payment" action="https://www.sandbox.paypal.com/cgi-bin/webscr" method="post">
							<input type="hidden" name="cmd" value="_xclick" />
							<input type="hidden" name="currency_code" value="GBP" />
							<input type="hidden" name="business" value="jnthnl_1281699601_biz@gmail.com" />
							<input type="hidden" name="notify_url" value="http://www.postbin.org/p5e6ay?http://test.sweetspot.com/property/notify" />
							<input type="hidden" name="amount" value="100" />
							<input type="hidden" name="item_name" value="Holding Deposit" />
							<input type="hidden" name="item_number" value="<?php echo get_custom_field('_address'); ?>" />
							<input type="hidden" name="return" value="http://sweetspot.com/property/thanks" />
							<input type="hidden" name="image_url" value="http://dl.dropbox.com/u/331606/tmp/logotype.gif" />
							<input type="hidden" name="no_note" value="1" />
							<input type="hidden" name="no_shipping" value="1" />
							<input type="hidden" name="cancel_return" value="http://sweetspot.com/property/cancelled_deposit" />
							<div class="grid8col">
								<div class="grid3col left">
									<input type="image" class="clickable left" src="<?php bloginfo('template_url'); ?>/images/paydeposit.gif" border="0" name="submitButton" alt="Pay deposit with PayPal" />
									<img alt="" border="0" src="https://www.paypal.com/en_GB/i/scr/pixel.gif" width="1" height="1">
								</div>
								<div class="left grid3col">
									<label for="custom">Your name:</label>
									<input name="custom required" id="custom" type="text" size="20" />
								</div>
								<br class="clearboth" />
							</div>
						</form>
						<?php elseif ($property_status=="under_offer"): ?>
						<p>This property is under offer. Please check back soon as it may come back on the market.</p>
						<?php else: ?>
						<p>This property is let.</p>
						<?php endif; ?>
					</div>
					
				</div>
				<?php endwhile; endif; ?>
			</div> 
	
			<p> 
				Nothing here for you? We are working on the next batch of homes now. 
			</p> 
 
			
		</div> 
	</div> 

	<?php get_footer(); ?>
