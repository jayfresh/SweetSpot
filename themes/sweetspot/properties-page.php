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
				<?php query_posts('post_type=properties&meta_key=_city&orderby=meta_value&order=ASC'); ?>
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
				</div>
				<?php endwhile; endif; ?>
			</div> 
	
			<p> 
				Nothing here for you? We are working on the next batch of homes now. 
			</p> 
 
			
		</div> 
	</div> 

	<?php get_footer(); ?>
