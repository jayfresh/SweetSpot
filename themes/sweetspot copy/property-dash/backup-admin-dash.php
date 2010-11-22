<h4 class="margintopsmall">All Properties</h4>


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
		<ul class="left grid3col marginleft noMargin">
			<li><strong><?php echo $address; ?></strong></li>
			<li><?php echo $postcode; ?></li>			
		</ul>
		<p class=" right alignright last">
			<!-- <a class="small shiny orange round button" title="Map" href="#">Map &raquo;</a> -->
		</p>
		
		<?php 	$applicants = array();
			$future_tenants = array();
			$managers = array();
			
			$co_authors = get_coauthors($post->ID);
			foreach ($co_authors as $author) {
			
				$user = new WP_User( $author->ID );
				// print_r($author);
				if ( !empty( $user->roles ) && is_array( $user->roles ) ) {
					foreach ( $user->roles as $role ) {
						// echo $role;
						if($role=="applicant") {
							$applicants[] = $user;
						} elseif($role=="future_tenant") {
							$future_tenants[] = $user;
						} elseif($role=="manager") {
							$managers[] = $user;
						} elseif($role=="tenant") {
							$tenants[] = $user;
						}
					}
				
				}
			
			}
			if(count($applicants) > 0 ){
		?>
		<h6 class="clearboth margintopsmall left">Applicants:</h6>			
		<ul class="noMargin left grid6col peopleList small">
		<?php } ?>
			<?php foreach($applicants as $applicant) : ?>
			<li class="clearboth expanded">
				<span class="left grid2col"><?php echo $applicant->display_name; ?></span>
				<?php $deposit_id = get_post_meta($post->ID, 'ipn_txn_id');
					if($deposit_id) {
						$deposit_id = $deposit_id[0]; ?>
				<span class="left grid2col marginleft">Deposit paid, PayPal ID: <?php echo $deposit_id; ?></span>
				<?php } else { ?>
				<span class="left grid2col marginleft">No deposit</span>
				<?php } ?>
				<span class="right grid2col">Contact</span>
				<p class="left grid6col last">
					<span class="grid2col left">Email:</span>
					<span class="grid4col left marginleft"><?php echo $applicant->user_email; ?></span>
					<span class="grid2col left">Phone:</span>
					<span class="grid4col left marginleft"><?php echo $applicant->user_phone; ?></span>
				</p>
				<br class="clearboth" />
			</li>
			<?php endforeach; ?>
			<?php if(count($applicants) > 0 ){
		?>
		</ul>
		<br class="clearboth" />
		<?php }
		if(count($managers) > 0 ){
		?>
		<h6 class="clearboth margintopsmall left">Managers:</h6>			
		<ul class="noMargin left grid6col peopleList small">
		<?php } ?>
			<?php foreach($managers as $manager) : ?>
			<li class="clearboth expanded">
				<span class="left grid2col"><?php echo $manager->display_name; ?></span>
				<span class="left grid2col marginleft"></span>
				<span class="right grid2col">Contact</span>
				<p class="left grid6col last">
					<span class="grid2col left">Email:</span>
					<span class="grid4col left marginleft"><?php echo $manager->user_email; ?></span>
					<span class="grid2col left">Phone:</span>
					<span class="grid4col left marginleft"><?php echo $manager->user_phone; ?></span>
				</p>
				<br class="clearboth" />
			</li>
			<?php endforeach; ?>
			<?php if(count($managers) > 0 ){
		?>
		</ul>
		<br class="clearboth" />
		<?php } if(count($future_tenants) > 0 ){
		?>
		<h6 class="clearboth margintopsmall left">Future Tenants:</h6>			
		<ul class="noMargin left grid6col peopleList small">
		<?php } ?>
			<?php foreach($future_tenants as $future_tenant) : ?>
			<li class="clearboth expanded">
				<span class="left grid2col"><?php echo $future_tenant->display_name; ?></span>
				<span class="left grid2col marginleft">Pay Deposit</span> <!-- JRL: should this even be here? How can they be a future tenant if they haven't paid a deposit? -->
				<span class="right grid2col">Contact</span>
				<p class="left grid6col last">
					<span class="grid2col left">Email:</span>
					<span class="grid4col left marginleft"><?php echo $future_tenant->user_email; ?></span>
					<span class="grid2col left">Phone:</span>
					<span class="grid4col left marginleft"><?php echo $future_tenant->user_phone; ?></span>
				</p>
				<br class="clearboth" />
			</li>
			<?php endforeach; ?>
			<?php if(count($future_tenants) > 0 ){
		?>
		</ul>
		<br class="clearboth" />
		<?php } if(count($tenants) > 0 ){
		?>
		<h6 class="clearboth margintopsmall left">Tenants:</h6>			
		<ul class="noMargin left grid6col peopleList small">
		<?php } ?>
			<?php foreach($tenants as $tenant) : ?>
			<li class="clearboth expanded">
				<span class="left grid2col"><?php echo $tenant->display_name; ?></span>
				<span class="left grid2col marginleft"></span>
				<span class="right grid2col">Contact</span>
				<p class="left grid6col last">
					<span class="grid2col left">Email:</span>
					<span class="grid4col left marginleft"><?php echo $tenant->user_email; ?></span>
					<span class="grid2col left">Phone:</span>
					<span class="grid4col left marginleft"><?php echo $tenant->user_phone; ?></span>
				</p>
				<br class="clearboth" />
			</li>
			<?php endforeach; ?>
			<?php if(count($tenants) > 0 ){
		?>
		<br class="clearboth" />
		</ul>
		<br class="clearboth" />
		<?php } ?>
		
		
		
	</li>
<?php endwhile; endif; ?>
</ul>
<br class="clearboth" />					
					
					

	
	
	