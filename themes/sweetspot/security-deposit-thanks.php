<?php 
/*

Template Name: Security Deposit thank you

*/
// at Phase VIII - check to see if everyone else in the group is also at step 3 - if so, notify the manager and set a flag on the lead tenant to say he has been notified of the need to send out the AST
$lead_tenant_email = get_userdatabylogin($current_user->group)->user_email;
if($lead_tenant_email) { // don't bother looking if there is no lead tenant
	$loop = new WP_Query("post_type=properties");
	global $post;
	while ( $loop->have_posts() ) : 
		$loop->the_post();
		$the_ID = get_the_ID();
		$IPN_payer_email = get_post_meta($the_ID, 'ipn_payer_email', true);
		if($IPN_payer_email==$lead_tenant_email) {
			$paid_holding_deposit = $the_ID;
		}
	endwhile;
	if($paid_holding_deposit) {
		checkAllApplicants($propertyID);
	}
}
?>
<?php get_header() ?>
<div id="content"> 
	<div class="container"> 
		<div class="description"> 
			<br>
				<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
				<?php the_content(); ?>
				<?php endwhile; endif; ?>
			<br> 
			<img src='../images/chair_2_blue.png' class='hero_floater' alt="chair"> 
		</div> 			
	</div> 
</div> 

<?php get_footer(); ?>