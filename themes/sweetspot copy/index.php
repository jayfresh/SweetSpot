
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
