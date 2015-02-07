<?php get_header(); ?>

<section class="content">

	<?php get_template_part('inc/page-title'); ?>
	
	<div class="pad group">
		
		<?php //get_template_part('inc/featured'); ?>
		
		<?php if ( have_posts() ) : ?>
		
			<div class="post-list group">
				<?php 
				
					// Nudge the post count up to two - we'll be rendering two posts before
					// we enter the main loop.
					$bimbler_post_count = 2; 
					
					echo '<div class="post-row">'; 

					//get_template_part('inc/featured');

					//the_post();
						
					// Display the first post (which will be the Welcome page). 
					// the_post() will have already been called.
					get_template_part('content');
					
//					echo '</div><div class="post-row">';
						
					// And get the next post - the featured ride.
					the_post();
						
					// Format and display the featured ride. (Or do nothing if there's no featured ride.)
					// (Which introduces a bug that the second and subsequent pages of posts do not show
					// anything in the top-right.)
					get_template_part('inc/featured-ride');

					// Not needed, as it'll be done as part of the loop below.
					//the_post();
								
					echo '</div><div class="post-row">';
					
					while ( have_posts() ){ 
						the_post(); 
					
				 		get_template_part('content'); 
				 		
				 		if($bimbler_post_count % 2 == 0) { 
							echo '</div><div class="post-row">'; 
						} 
						
						$bimbler_post_count++; 
					} 
						
					echo '</div>';

				?>
					
			</div><!--/.post-list-->
		
			<?php get_template_part('inc/pagination'); ?>
			
		<?php endif; ?>
		
	</div><!--/.pad-->
	
</section><!--/.content-->

<?php get_sidebar(); ?>

<?php get_footer(); ?>