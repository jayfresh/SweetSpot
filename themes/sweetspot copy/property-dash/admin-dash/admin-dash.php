<?php include( TEMPLATEPATH . '/property-dash/admin-dash/admin-dash-header.php' ); ?>
		<div id="wrap" class="jbasewrap">
			<div id="header">
				<h1 class="padtop left"><a class="grid2col imgreplace" href="#">Sweetspot</a></h1>
				<ul>
					<li><a class="orange" rel="self" title="Log Out" href="<?php echo wp_logout_url( get_permalink() ); ?>">Log Out &raquo;</a></li>
				<!--<li><a class="orange" rel="self" href="#guarantors">Guarantors</a></li>
					<li><a class="pink" rel="self" href="#housemates">Housemates</a></li>
					<li><a class="green" rel="self" href="#progress">Progress</a></li>
					<li><a class="blue" rel="self" href="#deposit">Pay Deposit</a></li>-->
				</ul>
				<h3 class="grid6col left marginleft">Hi There, <?php echo $current_user->display_name; ?>!</h3>
				<p class="grid6col left marginleft">Welcome to your SweetSpot Manager dashboard. From here you can see applicants for properties, viewings, and action things like guarantor submissions.</p>		
			</div>
			<div id="modules">
				<?php include( TEMPLATEPATH . '/property-dash/admin-dash/modules/house-list.php' ); ?>
			</div>
			
			<div class="push"></div>
		</div>
		<?php include( TEMPLATEPATH . '/property-dash/admin-dash/admin-dash-footer.php' ); ?>
		