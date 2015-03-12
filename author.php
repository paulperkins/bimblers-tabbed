<?php 
/*
Template Name: Bimbler Author Page Template
Template Description: A custom template to display the Bimbler sidebar.
*/

 get_header(); 

 $user = (get_query_var('author_name')) ? get_user_by('slug', get_query_var('author_name')) :
 											get_userdata(get_query_var('author'));
 
 $avatar = get_avatar ($user->ID, 250);//, null, null, $user_info->user_login);

 $time_str = 'g:ia';
 $date_str = 'j M Y';
 $timestamp_str = 'Y-m-d\TH:i';
 
 	function get_rides_attended ($user_id)
 	{
 		$rsvp = Bimbler_RSVP::get_instance ();
 		$rides = $rsvp->get_user_events_attended ($user_id);
 		
 		return $rides;
 	}
 	
 	function get_rsvps ($user_id)
 	{
 		global $wpdb;
 		
 		$rsvp = Bimbler_RSVP::get_instance ();
 		$rsvps = $rsvp->get_user_rsvps ($user_id);
 		
 		if (null === $rsvps) {
			error_log ('get_rsvps: cannot get RSVPs.');
 			$wpdb->print_error();
 		}
 			
 		return $rsvps;
 	}
 
 	function get_activity ($user_id)
 	{
 		global $wpdb;

 		$rsvp = Bimbler_RSVP::get_instance ();
 		$rsvps = $rsvp->get_user_activity ($user_id);
 			
 		if (null === $rsvps) {
 			error_log ('get_activity: cannot get activity.');
 			$wpdb->print_error();
 		}
 		
 		return $rsvps;
  	}
  	
  	function get_last_login ($user_object) 
  	{
  		$meta = 'wp-last-login';
		$time_str = 'j M g:ia';
		$timezone = 'Australia/Brisbane';
		
		date_default_timezone_set($timezone);

		//$timestamp = strtotime($stored_time);
		//$local_time = $timestamp + date('Z');
		
		
		$time = '';
  		
  		$meta_time = get_user_meta ($user_object->ID, $meta);
  		
  		if (isset ($meta_time))
  		{
  			//$time = date ($time_str, ($meta_time[0] + date('Z')));
  			$time = date ($time_str, $meta_time[0]);
  		}
  		
  		return $time;
  	}
  	
  	function get_profile_question ($user, $question) {
  		
  		$meta = get_user_meta ($user, $question);
  		
/*  		if (0 == strlen ($meta)) {
  			return null;
  		} */
  		
  		return $meta[0];
  	}

  	function get_avatar_img ($avatar) {
  	
  		preg_match( '#src=["|\'](.+)["|\']#Uuis', $avatar, $matches );
  	
  		return ( isset( $matches[1] ) && ! empty( $matches[1]) ) ?
  		(string) $matches[1] : '';
  	}
  	 
  	
  	function render_timeline ($user) 
  	{
  		global $date_str;
  		global $time_str;
  		global $timestamp_str;
  		
  		$user_activity = get_activity($user->ID);
  		
  		$left = false;
  		
  		//var_dump ($user_rsvps);
  		
  		foreach ($user_activity as $activity)
  		{
  			$begin = '';
  				
  			if ('rsvp' == $activity->type)
  			{
  				$post = get_post ($activity->post_id);
  				$title = $post->post_title;
  				$time = $activity->time;
  				$link = tribe_get_event_link ($post);
  				$rsvp = $activity->other1;
  		
  				$icon = 'fa fa-check-square-o';
  					
  				if ('Y' == $rsvp) {
  					$colour = 'bg-success';
  		
  					$text = $user->first_name . ' RSVPd Yes to <a href="' . $link . '">' . $title . '</a>.';
  				} else {
  					$colour = 'bg-danger';
  		
  					$text = $user->first_name . ' RSVPd No to <a href="' . $link . '">' . $title . '</a>.';
  				}
  			}
  				
  			if ('comment' == $activity->type)
  			{
  				$post = get_post ($activity->post_id);
  				$title = $post->post_title;
  				$time = $activity->time;
  				$comment_id = $activity->other1;
  				//$comment_object = get_comment ($comment_id);
  				$link = esc_url(get_comment_link($comment_id));
  		
  				$icon = 'fa fa-comments-o';
  				$colour = 'bg-success';
  		
  				$str=explode(' ',get_comment_excerpt($comment_id));
  				$comment_excerpt=implode(' ',array_slice($str,0,11));
  				if(count($str) > 11 && substr($comment_excerpt,-1)!='.') $comment_excerpt.='...';
  		
  				$text = $user->first_name . ' said, &quot;' .  $comment_excerpt . '&quot; about <a href="' . $link . '">' . $title . '</a>.';
  			}

  			if ('photo' == $activity->type)
  			{
  				$time = $activity->time;
  					
  				$icon = 'fa fa-camera';
  				$colour = 'bg-success';
  					
  				$text = $user->first_name . ' uploaded a photo.';
  				
  				$src = '';
  				$thumb = '';
  				
  				if ('/' != $activity->other1[0]) {
  					$src .= '/';
  					$thumb .= '/';
  				}
  				
  				$src .= $activity->other1 . '/' . $activity->other2;
  				$thumb .= $activity->other1 . '/thumbs/thumbs_' . $activity->other2;
  				
  				$text .= '<div class="row">';
  				$text .= '<div id="ngg-image-0" class="ngg-gallery-thumbnail-box">';
  				$text .= '<div class="ngg-gallery-thumbnail">' . PHP_EOL;
  				$text .= '<a href="'. $src . '"' . PHP_EOL;
  				$text .= 'data-src="' . $src . '"' . PHP_EOL;
  				$text .= 'data-thumbnail="' . $thumb . '"' . PHP_EOL;
  				//$text .= 'data-fancybox-group="27452b167807389dcf163ec7c4e03497"' . PHP_EOL; // New
  				$text .= 'class="ngg-fancybox" ' . PHP_EOL;
  				$text .= 'class="fancybox" ' . PHP_EOL;
  				$text .= 'rel="27452b167807389dcf163ec7c4e03497"' . PHP_EOL;
  				$text .= '>'; 
  				$text .= '<img class="pull-rightx" src="' . $thumb . '" border=0></img>';
  				$text .= '</a></div></div>';
  				$text .= '</div>';
  			}
  			
  			if ('joined' == $activity->type)
  			{
  				$time = $activity->time;
  		
  				$icon = 'fa fa-smile-o';
  				$colour = 'bg-success';
  		
  				$text = $user->first_name . ' joined.';
  		
  				$begin = ' begin';
  			}
  			
  			if (current_user_can( 'manage_options') && ('login' == $activity->type))
  			{
  				$time = $activity->time;
  			
  				$icon = 'fa fa-keyboard-o';
  				$colour = 'bg-success';
  			
  				$text = $user->first_name . ' logged in.';
  			
  				$begin = '';
  			}
  			
  			if (current_user_can( 'manage_options') && ('order' == $activity->type))
  			{
  				$time = $activity->time;
  					
  				$icon = 'fa fa-shopping-cart';
  				$colour = 'bg-success';
  					
  				$text = $user->first_name . ' placed an order.';
  					
  				$begin = '';
  			}

  			if ('attended' == $activity->type)
  			{
  				$post = get_post ($activity->post_id);
  				$title = $post->post_title;
  				$time = $activity->time;
  				$link = tribe_get_event_link ($post);
  			
  				$icon = 'fa fa-bicycle';
  					
				$colour = 'bg-success';
				  			
				$text = $user->first_name . ' attended <a href="' . $link . '">' . $title . '</a>.';
  			} 
  			
  				
  			
  			?>
  					<article class="timeline-entry<?php if (true == $left) { echo ' left-aligned'; }?><?php echo $begin; ?>">
  						
  						<div class="timeline-entry-inner">
  							<time class="timeline-time" datetime="<?php echo date ($timestamp_str, strtotime ($time));?>"><span><?php echo date ($time_str, strtotime($time)); ?></span> <span><?php echo date ($date_str, strtotime($time)); ?></span></time>
  							
  							<div class="timeline-icon <?php echo $colour; ?>">
  								<i class="<?php echo $icon; ?>"></i>
  							</div>
  							
  							<div class="timeline-label">
  								<span><?php echo $text; ?></span>
  							</div>
  						</div>
  						
  					</article>
<?php 
 				if (true == $left) { 
 					$left = false; 
 				} else {
 					$left = true; 
 				}
 			}
  	}
 ?>
 
<section class="content">
	
	<div class="page-title pad group">
		<h1><i class="fa fa-user"></i>Profile page: <?php echo $user->display_name; ?></h1>
	</div>
	
<?php 
	if (!is_user_logged_in()) {
		echo '<div class="bimbler-alert-box notice"><span>Notice: </span>You must be logged in to view this page.</div>';
	} else {
?>

<?php //var_dump ($user); ?>
	
	<div class="pad group">

	<div class="profile-env">
	
		<header class="row">
		
			<div class="col-sm-2 bimbler-avatar-large" style="xbackground-image: url('<?php echo get_avatar_img($avatar); ?>');" data-avatar-count="<?php echo get_rides_attended ($user->ID); ?>">
				<?php echo $avatar; ?>
			</div>
		
			<div class="col-sm-8">
			
				<ul class="profile-info-sections">
					<li>
						<div class="profile-name">
							<strong>
								<?php echo $user->first_name .' '. $user->last_name; ?>
								</strong>
							<span><?php  echo $user->nickname;
							
							if (current_user_can( 'manage_options')) {
								echo  ' - ' . $user->user_email;
								
								//http://bimblers.com/wp-admin/user-edit.php?user_id=89&wp_http_referer=%2Fwp-admin%2Fusers.php
								echo '&nbsp;<a href="/wp-admin/user-edit.php?user_id=' . $user->ID . '&wp_http_referer=%2Fwp-admin%2Fusers.php" target="_external">';
								echo '<i class="fa fa-cog fa-lg"></i></a>';
							}
							
							?></span>
						</div>
					</li>
					
<!--  					<li>
						<div class="profile-stat">
							<i class="fa fa-quote-left"></i>
							<h5></h5><span><?php //echo $user->description; ?></span>
							<i class="fa fa-quote-right"></i>
					
						
							 <h3><?php //echo get_rides_attended ($user->ID); ?></h3>
							<span>rides attended</span> 
						</div>
					</li> -->
					
<!--  					<li>
						<div class="profile-stat">
							<h3>10</h3>
							<span>future RSVPs</span>
						</div>
					</li> -->
				</ul>
			
			</div>
			
			<!-- ><div class="col-sm-3">
			</div> -->
		
		</header>
	
		
		<section class="profile-info-tabs">
			
			<div class="row">
				
				<div class="col-sm-offset-2 col-sm-10" border="1">
					
					<ul class="user-details">
					<?php if (strlen($user->description) > 0) {?>
						<li>
								<i class="fa fa-quote-left"></i>
								<span><?php echo $user->description; ?></span>
								<i class="fa fa-quote-right"></i>
						</li>
						<?php } ?>
						
						<li>
								<i class="fa fa-calendar fa-fw"></i>
								<span><strong>Joined:</strong> <?php echo date ($date_str, strtotime ($user->user_registered)); ?></span>
						</li>
						
						<li>
								<i class="fa fa-thumbs-o-up fa-fw"></i>
								<span><strong>Attended:</strong> <?php echo get_rides_attended ($user->ID); ?> events</span>
						</li>
						
						<li>
								<i class="fa fa-coffee fa-fw"></i>
								<span><strong>Favourite coffee shop:</strong> <?php echo get_profile_question ($user->ID, 'rpr_which_is_your_favourite_coffee_shop'); ?></span>
						</li>
						
						<li>
								<i class="fa fa-flash fa-fw"></i>
								<span><strong>Cycling tip:</strong> <?php echo get_profile_question ($user->ID, 'rpr_whats_your_best_cycling_tip'); ?></span>
						</li>
						
						<li>
								<i class="fa fa-bicycle fa-fw"></i>
								<span><strong>First bike:</strong> <?php echo get_profile_question ($user->ID, 'rpr_what_was_your_first_ever_bike'); ?></span>
						</li>
												
												
<?php 
						if (current_user_can( 'manage_options')) {
?>
							<li>
								<i class="fa fa-clock-o fa-fw"></i>
								<span><strong>Last login:</strong> <?php echo get_last_login ($user); ?></span>
							</li>
<?php 
						}
?>			
						</ul>

				</div>
				
			</div>
			
		</section>
		
		</div>
		
		
		<!--  <div class="col-md-6">
		<div class="panel panel-primary">
		
			<div class="panel-heading">
				<div class="panel-title">Timeline</div>
			</div> 
		
		
			<div class="panel-body"> -->

		
		<div class="timeline-centered scrollable" data-height="250">
		
		<?php
		
			render_timeline ($user);

		?>
		
		</div> <!-- timeline-centred -->

				<!--  </div> 
			</div> 
		</div> -->
	
		
	</div><!--/.pad-->
	
<?php 
	}
?>
	
</section><!--/.content-->

<?php  get_sidebar(); ?>

<?php get_footer(); ?>


