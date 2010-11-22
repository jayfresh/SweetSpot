			<!--<ul class="small right noBullets alignright">
				<li class="left grid2col"><a title="Help" href="#">Help</a></li>
				<li class="left grid2col"><a title="Settings" href="#">Settings</a></li>
				<li class="left grid2col"><a title="Contacts" href="#">Contacts</a></li>
			</ul>-->
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