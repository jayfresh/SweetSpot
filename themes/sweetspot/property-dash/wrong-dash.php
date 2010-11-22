<?php 

//Template Name: Dash 

 get_header(); ?> 
 

<?php 	if ( $user_ID ) { ?>
    <!-- text that logged in users will see -->

<?php 	global $current_user;	
      	get_currentuserinfo();
		$user = new WP_User( $current_user->ID );

		if ( !empty( $user->roles ) && is_array( $user->roles ) ) {
		foreach ( $user->roles as $role );
}
      ?>
      
     
			<ul class="small right noBullets alignright">
				<li class="left grid2col"><a title="Help" href="#">Help</a></li>
				<li class="left grid2col"><a title="Settings" href="#">Settings</a></li>
				<li class="left grid2col"><a title="Contacts" href="#">Contacts</a></li>
			</ul>
			<h1 class="padtop">Hi there, <?php echo $current_user->user_login; ?>!</h1>
			<p class="left"><em>This is the <?php echo $role ?> dash</em></p>
			<ul class="small right noBullets alignright">
				<!-- <li class="left grid2col">
					<a class="shiny orange round button" title="All Events" href="#">All Events &raquo;</a>
				</li>
				<li class="left grid2col">
					<a class="shiny orange round button" title="All Students" href="#">All Students &raquo;</a>
				</li> -->
				<li class="left grid2col">
					<a class="shiny orange round button" title="Log Out" href="<?php echo wp_logout_url( get_permalink() ); ?>">Log Out &raquo;</a>
				</li>
			</ul>
			<br class="clearboth" />
			<div id="dash" class="bordertop">
				<div class="grid6col left">      
<?php /*	
      echo 'Username: ' . $current_user->user_login . "<br />\n";
      echo 'User email: ' . $current_user->user_email . "<br />\n";
      echo 'User level: ' . $current_user->user_level . "<br />\n";
      echo 'User first name: ' . $current_user->user_firstname . "<br />\n";
      echo 'User last name: ' . $current_user->user_lastname . "<br />\n";
      echo 'User display name: ' . $current_user->display_name . "<br />\n";
      echo 'User ID: ' . $current_user->ID . "<br />\n";
      
      */
?>


<?php
	
	// Insert pre-code for both here

if ( $role == 'administrator' ) {

	// Insert code for admins here ?>


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
	<li class="clearboth">
		<span class="grid2col left">
			<?php the_post_thumbnail( 'main-property-thumb', array( 'class' => 'grid2col' )); ?>
		</span>
		<ul class="left grid3col marginleft noMargin">
			<li><?php echo $address; ?></li>
			<li><?php echo $postcode; ?></li>

			<li>Property with unfilled tenancy</li>
			
		</ul>
		<p class=" right alignright last">
			<a class="small shiny orange round button" title="Map" href="#">Map &raquo;</a>
		</p>
		
		<?php	$applicants = array();
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
				<span class="left grid2col marginleft">Pay Deposit</span>
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
				<span class="left grid2col marginleft">Pay Deposit</span>
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
				<span class="left grid2col marginleft">Pay Deposit</span>
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
				<span class="left grid2col marginleft">Pay Deposit</span>
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
		</ul>
		<br class="clearboth" />
		<?php } ?>
		
		
		
	</li>
<?php endwhile; endif; ?>
</ul>
					
					
					

	
	
	
<?php } else if ( $role == 'applicant' ) {

	echo '<h1>YO APPLICANT!</H1>'; 

	// Insert code for applicants here
}

	// Insert post-code for both here

query_posts('caller_get_posts=1&author=' . $current_user->ID . '&post_type=properties&post_status=publish&order=ASC');
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

<p>
<!-- 	House: <?php the_title(); ?>
</p>
<p>City:
<?php 	
	 echo $city;
?>

</p> -->

<?php 	endwhile; 
		endif;
?>

<?php } else {   ?>
    <!-- this is shown to anyone not logged in -->

	<p>Please <a href="<?php bloginfo('url'); ?>/wp-login.php">login</a> or <a href="<?php bloginfo('url'); ?>/wp-register.php">register</a>.</p>

	<?php wp_login_form(array( 'redirect' => ( get_permalink()))); ?>

<?php }  get_footer(); ?>











