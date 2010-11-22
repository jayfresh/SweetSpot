<?php
	// this module can always be shown, since it is always OK to book a viewing on a property
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
					<a name="future_viewings"></a><h3 class="pink">Future Viewings</h3>
					<p class="grid3col left">
						All of your upcoming Sweetspot property viewings. Click a house to visit its property page.
					</p>
					<ul class="noBullets noMargin left houselist">
					<?php // House Loop
					$loop = new WP_Query('caller_get_posts=1&post_type=properties&post_status=publish');
					while ( $loop->have_posts() ) : $loop->the_post();

						$postID 		= 	$post->ID;
						$address 		= 	get_post_meta($postID,'_address',true);
						$postcode 		= 	get_post_meta($postID,'_postcode',true);
						$viewings_match = 	$viewings_booked[$address];
						$status 		= 	get_post_meta($postID, '_property_status', true);
						$pretty_status 	= 	str_replace ( "_" , " " , $status ); // String manipulation for removing property status
						$datetime 		= 	strtotime(str_replace('/', '-', $viewings_match['datetime']));
						$viewingdate	=	date("H:i \\o\\n d/m/Y ", $datetime);
						$now			=	time();
						
						// show the property if there is a viewing in the future
						if($viewings_match) :
							if($now < $datetime) :
						?>
						
						<li class="grid3col marginleft">
							<h6><strong><?php echo $address; ?>, <?php echo $postcode; ?></strong></h6>
							<div class="property_image">
								<a href="<?php get_bloginfo( "url" ); ?>/property" title="Property Page"><?php the_post_thumbnail( 'student-dash-thumb', array()); ?></a>
								<?php property_status_banner($status);?>
							</div>
							Viewing at <?php echo $viewingdate; ?>
						</li>
						<?php 
							endif;
						endif;
					endwhile;
					?>
					</ul>
				</div>