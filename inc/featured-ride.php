<?php
// Query featured entries
$featured = new WP_Query(
	array(
		'no_found_rows'				=> false,
		'update_post_meta_cache'	=> false,
		'update_post_term_cache'	=> false,
		'ignore_sticky_posts'		=> 1,
		'posts_per_page'			=> 1, // PP
		'cat'						=> 79// 'Featured Ride' //ot_get_option('featured-category')
	)
);
?>
<?php 
	if (is_home () &&  !is_paged() ) {
?>
<!--  	<div class="featured"> -->
		<?php while ( $featured->have_posts() ): $featured->the_post(); ?>
			<?php 
				global $bimbler_post_count;
				
				$bimbler_post_count++;
				get_template_part('content-featured-ride'); ?>
		<?php endwhile; ?>	
	<!-- </div>--><!--/.featured-->
<?php } ?>

