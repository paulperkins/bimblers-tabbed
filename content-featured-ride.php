<article id="post-<?php the_ID(); ?>" <?php post_class('group'); ?>>	
	<div class="post-inner post-hover">
	
<?php 

	$date_str = 'D j M';

	$get_posts = Bimbler_RSVP::get_instance()->get_next_event();

	$event = $get_posts[0];
	
	$ride_title = $event->post_title; 
	$ride_url 	= get_permalink ($event->ID);
	$ride_rwgps = Bimbler_RSVP::get_instance()->get_rwgps_id ($event->ID);
	
	$map_id = 'bimbler-next-ride-map';

	// Get the excerpt if it exists, or use the event text otherwise.
	$ride_excerpt = $event->post_excerpt;
	
	if (empty ($ride_excerpt)) {
		$ride_excerpt = $event->post_content;
	}
	
	//Don't show the route if the user is not logged in.
	if (!is_user_logged_in()) {
		$ride_rwgps = 0;
	}
	
	$start_date = tribe_get_start_date($event->ID, false, $date_str);
	
	//$map_style = "width: 425px; height: 200px; border-top-left-radius: 15px; border-top-right-radius: 15px; border-bottom-left-radius: 15px; border-bottom-right-radius: 15px;";
	$map_style = "width: 100%; height: 200px; border-top-left-radius: 15px; border-top-right-radius: 15px; border-bottom-left-radius: 15px; border-bottom-right-radius: 15px;";
	
	
	?>
		<div class="post-thumbnail">
			<div class="next-ride" id="<?php echo $map_id; ?>" data-rwgps-id="<?php echo $ride_rwgps; ?>" style="<?php echo $map_style; ?>">
			</div><!--/.next-ride -->
		</div><!--/.post-thumbnail-->
				
		<h2 class="post-title">
			<a href="<?php echo $ride_url; ?>" rel="bookmark" title="<?php echo $ride_title; ?>">Next ride: <?php echo $ride_title; ?> on <?php echo $start_date; ?></a>
			</h2><!--/.post-title-->
		
		<?php if (ot_get_option('excerpt-length') != '0'): ?>
		<div class="entry excerpt">				
			<?php echo $ride_excerpt; //the_excerpt(); ?>
<!--  			 <p class="read-more button"><a href="<?php //echo esc_url( get_permalink() ); ?>"><?php _e( 'Read more &raquo;', 'hueman' ); ?></a></p>--> <!-- PP -->
		</div><!--/.entry-->
		<?php endif; ?>
		
	</div><!--/.post-inner-->	
</article><!--/.post-->	
