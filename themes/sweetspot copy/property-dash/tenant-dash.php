<div class="grid6col left">
	<h4 class="margintopsmall">The Property You Live in</h4>
	
	
	<ul class="noBullets noMargin">
	<?php query_posts('caller_get_posts=1&author=' . $current_user->ID . '&post_type=properties&post_status=publish&order=ASC');
	if ( have_posts() ) : while ( have_posts() ) : the_post(); 
	
	// Get the property values out of the post meta
	
			$beds 		= get_post_meta(get_the_ID(), '_beds', true);
			$baths 		= get_post_meta(get_the_ID(), '_baths', true); 
			$receptions = get_post_meta(get_the_ID(), '_receptions', true); 
			$price 		= get_post_meta(get_the_ID(), '_price', true); 
			$period 	= get_post_meta(get_the_ID(), '_period', true); 
			$notes	 	= get_post_meta(get_the_ID(), '_notes', true); 
			$mapnotes 	= get_post_meta(get_the_ID(), '_mapnotes', true); 
			$status 	= get_post_meta(get_the_ID(), '_status', true); 
			$address 	= get_post_meta(get_the_ID(), '_address', true); 
			$city 		= get_post_meta(get_the_ID(), '_city', true);  
			$postcode 	= get_post_meta(get_the_ID(), '_postcode', true); 		
			$country 	= get_post_meta(get_the_ID(), '_country', true); 
	
	?>
		<li class="clearboth property">
			<span class="left grid2col">
				<?php the_post_thumbnail( 'property-gall-thumb', array( 'class' => 'dashimg' )); ?>
			</span>
			<ul class="left grid4col marginleft noMargin">
				<li><strong><?php echo $address; ?></strong></li>
				<li><?php echo $postcode; ?></li>
				
			</ul>
			<p class=" right alignright last">
				<!-- <a class="small shiny orange round button" title="Map" href="#">Map &raquo;</a> -->
			</p>
		</li>
	<?php endwhile; endif; ?>
	</ul>
	<br class="clearboth" />
</div