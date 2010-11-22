<?php
// collect properties you have booked viewings at
global $post;
$loop = new WP_Query('caller_get_posts=1&author='.$current_user->ID.'&post_type=viewings&post_status=publish&order=ASC');
$viewings_booked = array();
while ( $loop->have_posts() ) : $loop->the_post();
	$address = get_post_meta($post->ID,'address',true);
	$viewings_booked[$address] = array(
		'datetime'	=> get_post_meta($post->ID,'datetime',true)
	);
endwhile;

// get text for Pay Deposit agreement
$featuredPosts = new WP_Query('pagename=holding-deposit-agreement');
while ($featuredPosts->have_posts()) : $featuredPosts->the_post();
	$holding_deposit_text = get_the_content();
endwhile;
?>

<div class="grid6col left">
	<h4 class="margintopsmall">Your properties:</h4>
	<ul class="noBullets noMargin">
	<?php
	$loop = new WP_Query('caller_get_posts=1&post_type=properties&post_status=publish');
	while ( $loop->have_posts() ) : $loop->the_post();
		$postID = $post->ID;
	
		$payer = get_post_meta($postID,'ipn_payer_email', true);
		$ipn_txn_id = get_post_meta($postID, 'ipn_txn_id', true);
		$user_email = $current_user->user_email;

		$address = get_post_meta($postID,'_address',true);
		$postcode = get_post_meta($postID,'_postcode',true);
		$viewings_match = $viewings_booked[$address];
		$status 	= get_post_meta($postID, '_property_status', true);
		$pretty_status = str_replace ( "_" , " " , $status ); // String manipulation for removing property status

		
		// show the property if there is a viewing or a deposit for it
		if($payer==$user_email || $viewings_match) : ?>
		<li class="clearboth property">
			<span class="left grid2col">
				<?php the_post_thumbnail( 'property-gall-thumb', array( 'class' => 'dashimg' )); ?>	
			</span>
			
			<ul class="left grid4col marginleft noMargin">
				<li><strong><?php echo $address; ?></strong><span class="small right">Property <?php echo $pretty_status; ?></span></li>
				<li><?php echo $postcode; ?></li>
				
				<?php if ($viewings_match) : $datetime = $viewings_match['datetime']; ?>
				<li><span class="small">Viewing booked on <?php echo $datetime; ?></span></li>
				<?php endif;
				if($payer==$user_email) : ?>
				<li><span class="small">Holding deposit paid on this property. PayPal reference: <?php echo ($ipn_txn_id ? $ipn_txn_id : "IPN transaction ID missing!"); ?></span></li>
				<?php elseif($status == "on_the_market"): ?>
				<li><a href="#" class="small pay_deposit">pay deposit</a></span>
					<div class="small pay_deposit_form"><?php echo $holding_deposit_text ?>
						<form class="margintop grid2col" action="https://www.sandbox.paypal.com/cgi-bin/webscr" method="post">
							<input type="hidden" name="cmd" value="_xclick" />
							<input type="hidden" name="currency_code" value="GBP" />
							<input type="hidden" name="business" value="jnthnl_1281699601_biz@gmail.com" />
							<input type="hidden" name="notify_url" value="http://www.postbin.org/p5e6ay?http://test.sweetspot.com/property/notify" />
							<input type="hidden" name="amount" value="100" />
							<input type="hidden" name="item_name" value="Holding Deposit" />
							<input type="hidden" name="item_number" value="<?php echo get_custom_field('_address'); ?>" />
							<input type="hidden" name="return" value="http://test.sweetspot.com/property/thanks" />
							<input type="hidden" name="image_url" value="http://dl.dropbox.com/u/331606/tmp/logotype.gif" />
							<input type="hidden" name="no_note" value="1" />
							<input type="hidden" name="no_shipping" value="1" />
							<input type="hidden" name="cancel_return" value="http://test.sweetspot.com/property/cancelled_deposit" />
							<input type="image" class="clickable" src="<?php bloginfo('template_url'); ?>/images/paydeposit.gif" border="0" name="submit" alt="Pay deposit with PayPal">
							<img alt="" border="0" src="https://www.paypal.com/en_GB/i/scr/pixel.gif" width="1" height="1">
						</form>
					</div>
				</li>
				<?php endif; ?>
			</ul>
		</li>
		<?php endif;
	endwhile; ?>
	</ul>
</div>
<br class="clearboth" />