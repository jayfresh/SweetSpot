<div class="grid6col left">
	
	<h4 class="margintopsmall">All Properties</h4>
	
	
	<ul class="noBullets noMargin">
	<?php query_posts('caller_get_posts=1&post_type=properties&post_status=publish&order=ASC');
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
			$deposit_payer = get_post_meta($postID, 'payer_email', true);
			
			// String manipulation for echoing property status :
			
			$pretty_status = str_replace ( "_" , " " , $status );
			
	
	?>
		<li class="clearboth property">
			<span class="left grid2col">
				<?php the_post_thumbnail( 'property-gall-thumb', array( 'class' => 'dashimg' )); ?>
				
				<br class="clearboth" />
			</span>
			<ul class="left grid4col marginleft noMargin">
				<li><strong><?php echo $address; ?></strong><span class="small right">Property <?php echo $pretty_status; ?></span> </li>
				<li><?php echo $postcode; ?></li>			
				<li>
				<?php global $post;
				$loop = new WP_Query('caller_get_posts=1&post_type=viewings&post_status=publish&order=ASC');
				while ( $loop->have_posts() ) : $loop->the_post();
					$viewing_address = get_post_meta($post->ID,'address', true);
					$datetime = get_post_meta($post->ID,'datetime',true);
					$author = get_the_author();
					if ($viewing_address == $address) {
						echo '<span class="small">Viewing with: '.$author.' on '.$datetime.'.</span>' ;
					}
				endwhile; ?>
				</li>
				<?php if($deposit_payer) : ?>
				<li><span class="small">Holding deposit paid by: <?php echo $deposit_payer; ?></span></li>
				<?php endif; ?>
			</ul>
		</li>
	<?php endwhile; endif; ?>
		<br class="clearboth" />					
	</ul>
	<br class="clearboth" />					
</div>						
<br class="clearboth" />					

		
		
		