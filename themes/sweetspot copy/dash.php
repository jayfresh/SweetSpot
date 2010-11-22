<?php 

//Template Name: Dash 
 if ( $user_ID ) 
 	{ 
 
    // text that logged in users will see

	global $current_user;	
		get_currentuserinfo();
	$user = new WP_User( $current_user->ID );
	
	if ( !empty( $user->roles ) && is_array( $user->roles ) ) {
		foreach ( $user->roles as $role );
	}
	
	if ( $role == 'applicant' ) {
		include( TEMPLATEPATH . '/property-dash/student-dash/student-dash.php' );
	} else if ( $role == 'administrator' ) {
		include( TEMPLATEPATH . '/property-dash/admin-dash/admin-dash.php' );
	} else {
	include( TEMPLATEPATH . '/property-dash/header.php' );
	include( TEMPLATEPATH . '/property-dash/dash-nav.php' ); 
	}
	?>
	

	<div id="dash" class="bordertop">
		
	<?php
		
	// Insert information before all roles here
	
	if ( $role == 'tenant' ) { 
		include( TEMPLATEPATH . '/property-dash/tenant-dash.php' );
	} else if ( $role == 'manager' ) { 
		include( TEMPLATEPATH . '/property-dash/manager-dash.php' );
	} else if ( $role == 'future_tenant' ) { 
		include( TEMPLATEPATH . '/property-dash/future_tenant-dash.php' );
	}
	
	// Insert information for all roles here
	?>
	</div>
<?php } else {   ?>

    <!-- this is shown to anyone not logged in -->

	<p>Please <a href="<?php bloginfo('url'); ?>/wp-login.php">login</a> or <a href="<?php bloginfo('url'); ?>/wp-register.php">register</a>.</p>

	<?php wp_login_form(array( 'redirect' => ( get_permalink()))); ?>
	<br class="clearboth" />
	<a href="<?php echo wp_lostpassword_url( get_permalink() ); ?>" title="Lost Password">Lost password?</a>
<?php }

	if ( $role != 'applicant' && $role!= 'administrator' ) {
		include( TEMPLATEPATH . '/property-dash/footer.php' ); 
	}
	?>