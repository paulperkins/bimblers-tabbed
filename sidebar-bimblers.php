<?php 

if (!defined ('BIMBLER_AJAX_CLASS')) {
	define( 'BIMBLER_AJAX_CLASS', 'Bimbler_Ajax' );
}

/**
 * Determines if the current user has RSVPd to this event.
 *
 * @param
 */
function get_current_rsvp ()
{
	global $wp_query;
	$postid = $wp_query->post->ID;

	global $current_user;
	get_currentuserinfo();

	global $wpdb;
	global $rsvp_db_table;

	$table_name = $wpdb->prefix . $rsvp_db_table;

	//error_log ('Determining if user has RSVPd for this event.');

	// User ID
	$user_id = $current_user->ID;

	$sql = 'SELECT * from '. $table_name;
	$sql .= ' WHERE user_id = '. $user_id .' ';
	$sql .= ' AND event = '. $postid;
	$sql .= ' ORDER BY id DESC';

	//error_log ('    '. $sql);

	$link = $wpdb->get_row ($sql);

	if (null == $link) {
		//error_log ('   No previous RSVP');

		return null;
	}
	else {
		//error_log ('  RSVP is: '. $link->rsvp);

		return $link->rsvp;
	}
}

function get_yes_rsvps ($postid) {
	global $wpdb;
	global $rsvp_db_table;
	
	$table_name = $wpdb->prefix . $rsvp_db_table;
	
	$sql_y =  'SELECT * FROM '. $table_name;
	$sql_y .= ' WHERE event = '. $postid;
	$sql_y .= ' AND rsvp = \'Y\'';
	$sql_y .= ' ORDER BY time DESC';
	
	return $wpdb->get_results ($sql_y);
}

function get_no_rsvps ($postid) {
	global $wpdb;
	global $rsvp_db_table;
	
	$table_name = $wpdb->prefix . $rsvp_db_table;

	$sql_n =  'SELECT * FROM '. $table_name;
	$sql_n .= ' WHERE event = '. $postid;
	$sql_n .= ' AND rsvp = \'N\'';
	$sql_n .= ' ORDER BY time DESC';

	return $wpdb->get_results ($sql_n);
}

function get_yes_rsvp_count ($postid) {
	global $wpdb;
	global $rsvp_db_table;
	
	$table_name = $wpdb->prefix . $rsvp_db_table;

	$sql_y =  'SELECT COUNT(*) AS yes FROM '. $table_name;
	$sql_y .= ' WHERE event = '. $postid;
	$sql_y .= ' AND rsvp = \'Y\'';
	
	//error_log ('Yes RSVP count: \'' . $sql_y . '\'.');

	$yes = $wpdb->get_row ($sql_y);

	//var_dump ($yes);
	
	if (!isset ($yes)) {
		return -1;
	}
	
	$yes_count = intval ($yes->yes);
	
	//error_log ('Counted ' . $yes_count .' RSVP yes.');
	
	// Note the lack of a space between SUM and (. MySQL breaks if this is not so.
	$sql_y =  'SELECT SUM(guests) AS yes FROM '. $table_name;
	$sql_y .= ' WHERE event = '. $postid;
	$sql_y .= ' AND rsvp = \'Y\'';

	//error_log ('Yes RSVP guest count: \'' . $sql_y . '\'.');
	
	$yes = $wpdb->get_row ($sql_y);
	
	if (!isset ($yes)) {
		return -1;
	}

	//error_log ('Counted ' . intval ($yes->yes) .' RSVP yes guests.');
	
	$yes_count += intval ($yes->yes);
	
	return $yes_count;
}


function get_rsvp_attendee_count ($postid) {
	global $wpdb;
	global $rsvp_db_table;

	$table_name = $wpdb->prefix . $rsvp_db_table;

	$sql_y =  'SELECT COUNT(*) AS yes FROM '. $table_name;
	$sql_y .= ' WHERE event = '. $postid;
	$sql_y .= ' AND rsvp = \'Y\'';
	$sql_y .= ' AND attended = \'Y\'';

	//error_log ('Attended RSVP count: \'' . $sql_y . '\'.');

	$yes = $wpdb->get_row ($sql_y);

	//var_dump ($yes);

	if (!isset ($yes)) {
		return -1;
	}

	$yes_count = intval ($yes->yes);

	//error_log ('Counted ' . $yes_count .' RSVP yes.');

	// Note the lack of a space between SUM and (. MySQL breaks if this is not so.
	$sql_y =  'SELECT SUM(guests) AS yes FROM '. $table_name;
	$sql_y .= ' WHERE event = '. $postid;
	$sql_y .= ' AND rsvp = \'Y\'';
	$sql_y .= ' AND attended = \'Y\'';

	//error_log ('Yes RSVP guest count: \'' . $sql_y . '\'.');

	$yes = $wpdb->get_row ($sql_y);

	if (!isset ($yes)) {
		return -1;
	}

	//error_log ('Counted ' . intval ($yes->yes) .' RSVP yes guests.');

	$yes_count += intval ($yes->yes);

	return $yes_count;
}

/*
 * Determines if the user can execute Ajax, and checks if the Ajax Bimbler plugin is loaded.
 */
function can_do_ajax () {

	if (!class_exists (BIMBLER_AJAX_CLASS)) {
		return false;
	}
	
	if (!current_user_can ('manage_options')) {
		return false;
	}
	
	return true;
}

/**
 * TODO: Move to common code.
 * 
 * @param unknown $event_id
 * @return boolean
 */
function has_event_passed ($event_id) {
	// Check if event 3
	$gmt_offset = ( get_option( 'gmt_offset' ) >= '0' ) ? ' +' . get_option( 'gmt_offset' ) : " " . get_option( 'gmt_offset' );
	$gmt_offset = str_replace( array( '.25', '.5', '.75' ), array( ':15', ':30', ':45' ), $gmt_offset );
	 
	if (strtotime( tribe_get_end_date( $event_id, false, 'Y-m-d G:i' ) . $gmt_offset ) <= time() ) {
		return true;
	}
		
	return false;
}

/**
 * Adds the RSVP list to the event.
 *
 * @param
 */
function show_rsvps () {
	// The current Post (event) ID.
	global $wp_query;
	$postid = $wp_query->post->ID;
		
	global $wpdb;
	global $rsvp_db_table;
	
	$has_event_passed = false;
		
	/*$table_name = $wpdb->prefix . $rsvp_db_table;

	$sql_y =  'SELECT * FROM '. $table_name;
	$sql_y .= ' WHERE event = '. $postid;
	$sql_y .= ' AND rsvp = \'Y\'';
	$sql_y .= ' ORDER BY time DESC';
		
	$sql_n =  'SELECT * FROM '. $table_name;
	$sql_n .= ' WHERE event = '. $postid;
	$sql_n .= ' AND rsvp = \'N\'';
	$sql_n .= ' ORDER BY time DESC'; */

	//error_log ('Show RSVP list.');
		
	//error_log ('    SQL Y: '. $sql_y);
	//error_log ('    SQL N: '. $sql_n);

	// Only show content to logged-in users, and only if we're on an event page.
//	if (is_user_logged_in() && is_single())
	if (is_single())
	{
		$html = '<div id="rsvp-list" class="widget">';
		$html .= '		    <h3 id="reply-title" class="comment-reply-title">Who\'s Coming</h3>';
		
			//$rsvps_y = $wpdb->get_results ($sql_y);
		//$rsvps_n = $wpdb->get_results ($sql_n);
		
		$rsvps_y = get_yes_rsvps ($postid);
		$rsvps_n = get_no_rsvps ($postid);
		$count_rsvps = get_yes_rsvp_count ($postid);
		$count_atts = get_rsvp_attendee_count ($postid);
		
		$has_event_passed = has_event_passed($postid);
		
		$html .= '<div class="bimbler-count-tags" style="overflow-y: hidden;">';
		$html .= '  <div style="float: left;">RSVPs: </div>';
		$html .= '  <div id="yes-count" style="float: left;">' . $count_rsvps .'</div>';
		$html .= '</div>';
		
		if (can_do_ajax ()) {
			$html .= '<div class="bimbler-count-tags" style="overflow-y: hidden;">';
			
			if ($has_event_passed) {
				$html .= '  <div style="float: left;">Attended: </div>';
			} else {
				$html .= '  <div style="float: left;">Confirmed: </div>';
			}
			
			$html .= '  <div id="attendee-count" style="float: left;">' . $count_atts .'</div>';
			$html .= '</div>';
		}
		
		$html .= '<div id="AvatarListSide" class="AvatarListSide-wrap">';
		$html .= '	<form method="post" id="commentform" class="commentform" enctype="multipart/form-data">';
		
		if ((0 == $rsvps_y) || (0 == $rsvps_n))// || (-1 == $count_rsvps))
		{
			$html .= '<p>Error in SQL.</p>';
		}
		else if ((0 == count ($rsvps_y)) && (0 == count ($rsvps_n)))
		{
			$html .= '<p>No RSVPs yet.</p>';
		}
		else if (!is_user_logged_in())
		{
			$html .= "<p>You must be logged in to see RSVPs.</p>";
		}
		else
		{
			//error_log ('    Yes returned '. count ($rsvps_y) .' rows.');
			//error_log ('    No returned '. count ($rsvps_n) .' rows.');
			//error_log ('    Count returned '. $count_rsvps .' attendees.');
				
			// Show Yes RSVPs.
			$rsvps = $rsvps_y;
				
			//$count = count($rsvps);
			//$count_rsvps;

			if ($count_rsvps > 0)
			{
				$html .= '		    <ul>';
						
				foreach ( $rsvps as $rsvp) {

					$user_info   = get_userdata ($rsvp->user_id);

					$avatar = '';
					
					if (isset ($user_info->user_login)) {
						$avatar .= get_avatar ($rsvp->user_id, null, null, $user_info->user_login);
					}
					
					$comment = stripslashes ($rsvp->comment); // De-escape the DB data.
					$attend = $rsvp->attended;

					$html .= '<li class="AvatarListSide">';
					
					// Output an innocuous DIV if the user cannot amend attendance, or if the Ajax module is not loaded.
					if (!can_do_ajax ()) {
						$html .= '<div class="rsvp-checkin-container-noajax">';
					}
					else {
						// Store the RSVP ID.
						$html .= '<div class="rsvp-checkin-container" id="'. $rsvp->id .'">';
					}
					
					// Only allow changes if this is the currently logged-in user or admin.
					$html .= $avatar; // IMG.

					// Only show if the event has ended or we're admin.
					if (current_user_can( 'manage_options') || $has_event_passed) 
					{								
						$html .= '<div class="rsvp-checkin-indicator" id="rsvp-checkin-indicator-'. $rsvp->id .'">'; // Content will be replaced by Ajax.
						
						if (!isset ($attend)) {
							$html .= '<div class="rsvp-checkin-indicator-none"><i class="fa-question-circle"></i></div>';
						} else if ('Y' == $attend) {
							$html .= '<div class="rsvp-checkin-indicator-yes"><i class="fa-check-circle"></i></div>';
						}
						else {
							$html .= '<div class="rsvp-checkin-indicator-no"><i class="fa-times-circle"></i></div>';
						}
	
						$html .= '</div>';
					} 
						
					$html .= '</div> <!-- rsvp-checkin-container -->';

					if (isset ($user_info->user_nicename)) {
						$html .= '<p><a href="/profile/' . urlencode ($user_info->user_nicename) .'/">' . $user_info->nickname; 
	
						if ($rsvp->guests > 0) {
							$html .= ' + ' . $rsvp->guests;
						}
						
						$html .= '</a></p>';
					}
					
					$html .= '</li>';
				}

				$html .= '		    </ul>';
				
			}
			// Show No RSVPs.
			$rsvps = $rsvps_n;

			$count = count($rsvps_n);
			
			//$html .= print_r ($rsvps_n, true);
				
			if ($count > 0)
			{
				if (1 == $count) {
					$html .= '<p>'. count($rsvps) .' not attending:</p>';
				} else {
					$html .= '<p>'. count($rsvps) .' not attending:</p>';
				}
					
				$html .= '		    <ul>';
					
				foreach ( $rsvps_n as $rsvp) {

					$comment = stripslashes ($rsvp->comment); // De-escape the DB data.
					
					$user_info   = get_userdata ($rsvp->user_id);
					
					//$html .= print_r ($user_info, true);
					
					if (isset ($user_info->user_login)) {
						$avatar = get_avatar ($rsvp->user_id, null, null, $user_info->user_login);

						$html .= '<li class="AvatarListSide"><div class="permalink"></div><a href="">'. $avatar;
						
						$html .= '<p><a href="/profile/' . urlencode ($user_info->user_nicename) .'/">' . $user_info->nickname; 
	
						$html .= '</a><p></li>';
					}
				}
					
				$html .= '		    </ul>';
			}
		}

		$html .= '		</form>';
		$html .= '		    </div> <!-- #rsvp-list-->';
		$html .= '		</div><!-- #footer Wrap-->';

		echo $html;
	}
}


/**
 * Adds the RSVP buttons to the event.
 *
 * @param
 */
function add_rsvp_form() {

	global $wp_query;
	$postid = $wp_query->post->ID;

	error_log ('add_rsvp_form: post ID '. $postid);
		
	// Only show content to logged-in users, and only if we're on an event page.
	if (is_user_logged_in() && is_single() && (0 != $postid)) {

		$rsvp = get_current_rsvp ();

		if (null == $rsvp) {
			$status = 'You have not RSVPd.';
		}
		else {
			if ('Y' == $rsvp) {
				$status = 'You have RSVPd \'yes\'.';
			} else {
				$status = 'You have RSVPd \'no\'.';
			}
		}
			
		$html  = '<div id="rsvp-form">';
		$html .= '<div id="respond" class="comment-respond">';
		$html .= '	<form method="post" id="commentform" class="commentform" enctype="multipart/form-data">';
		$html .= '	<h3 id="reply-title" class="comment-reply-title">RSVP</h3>';
		$html .= '<p>'. $status .'</p>';
		$html .= wp_nonce_field('rsvp', 'rsvp_nonce', true, true);
		$html .= '	<p class="form-submit">';
		$html .= '  <input name="submit" class="button-primary" type="submit" id="submit" value="RSVP Yes" style="background: #6aab2d;"><input type="hidden" name="rsvp_post_id" id="rsvp_post_id" value="'. $postid .'">';
		$html .= '	<input name="submit" class="button-primary" type="submit" id="submit" value="RSVP No" style="background: #f75300;"><input type="hidden" name="rsvp_post_id" id="rsvp_post_id" value="'. $postid .'">';
		$html .= '	</p></form>';
		$html .= '</div> <!--#rsvp-respond-->';
		$html .= '</div> <!-- #rsvp-form -->';

		echo $html;

	} // end if

} // end add_rsvp_form

function show_nexton_widget () {
	// Display the next-on widget for this ride.
	global $wp_query;
	
	// Fix bug for erroneously showing widget on front page - user get_queried_object_id.
	$post_id = get_queried_object_id();
	
	// Set up the ride page ID.
	$meta_ride_page = get_post_meta ($post_id, '_BimblerRidePage', true);
	
	//var_dump ($meta_ride_page);
	
	if (isset ($meta_ride_page) && (0 != strlen ($meta_ride_page))) {
		echo '<div id="rsvp-list" class="widget">';
		echo '		    <h3 id="reply-title" class="comment-reply-title">Schedule</h3>';
		
		
		$args = array (	"name"			=> "Primary",
				"id"			=> "primary",
				"description"	=> "Normal full width sidebar",
				"class"			=> "",
				"before_widget"	=> "",
				"after_widget"	=> "",
				"before_title"	=> "",
				"after_title"	=> "",
				"widget_id"		=> "bimbler_nexton_widget-3",
				"widget_name"	=> "Bimbler Next On Widget",
				"ride_page"		=> $meta_ride_page);
			
		$instance = array (	"title"			=> "",
				"tabs_category"	=> 1,
				"tabs_date"		=> 1,
				"future_enable"	=> 1,
				"past_enable"	=> 1,
				"future_num"	=> "5",
				"past_num"		=> "5",
				"order_future"	=> "1",
				"order_past"	=> "2");
	
		the_widget ('Bimbler_NextOn_Widget', $instance, $args);
		
		echo '</div>';
	}
}


function show_gps_download_widget () {
	// Display the TCX, GPX and KML for this ride.
	global $wp_query;

	// Don't display to non logged-in users.
	if (!is_user_logged_in()) {
		return;	
	}
	
	// Fix bug for erroneously showing widget on front page - user get_queried_object_id.
	$post_id = get_queried_object_id();

	$rwgps_id = Bimbler_RSVP::get_instance()->get_rwgps_id ($post_id);

	//error_log ('RWGPS for event ' . $post_id . ' is ' . $rwgps_id);
	
	if (0 != $rwgps_id) {
	
		echo '<div id="rsvp-list" class="widget">';
		echo '		    <h3 id="reply-title" class="comment-reply-title">GPS Downloads</h3>';


?>
		<div class="entry themeform">
			<div class="section" style="text-align: left; display: block; width: 100%; margin-left: auto; margin-right: auto;">
				<form>
					<div class="row" style="padding-top: 2px;">
						<div class="col-sm-6">Garmin TCX:</div><div class="col-sm-3"><input type="button" class="bimbler-button" title="Ideal for turn-by-turn navigation on Garmin units." value="Download" onclick="window.location.href='http://ridewithgps.com/routes/<?php echo $rwgps_id; ?>.tcx'" style="xbackground: #dd9933 !important;"></div>
					</div>
					<div class="row" style="padding-top: 2px;">
						<div class="col-sm-6">GPX:</div><div class="col-sm-3"><input type="button" title="For enabling track displays on non-Garmin units." value="Download" onclick="window.location.href='http://ridewithgps.com/routes/<?php echo $rwgps_id; ?>.gpx?sub_format=track'" class="bimbler-button"></div>
					</div>
					<div class="row" style="padding-top: 2px;">
						<div class="col-sm-6">Google KML:</div><div class="col-sm-3"><input type="button" title="For viewing in Google Earth." value="Download" onclick="window.location.href='http://ridewithgps.com/routes/<?php echo $rwgps_id; ?>.kml'" class="bimbler-button"></div>
					</div>
				</form>
			</div>
		</div>
<?php 			
		
		
		echo '</div>';
	}
}



function show_cuesheet_widget () {
	// Display the RWGPS cuesheet for this ride.
	global $wp_query;

	// Fix bug for erroneously showing widget on front page - user get_queried_object_id.
	$post_id = get_queried_object_id();

	$rwgps_id = Bimbler_RSVP::get_instance()->get_rwgps_id ($post_id);

	//error_log ('RWGPS for event ' . $post_id . ' is ' . $rwgps_id);

	if (0 != $rwgps_id) {

		echo '<div class="entry themeform">';
		echo '	<div id="bimbler-cuesheet-widget" class="widget">';
		echo '		    <h3 id="reply-title" class="comment-reply-title">Cuesheet</h3>';

		echo '<div id="bimbler_rwgps_cuesheet" data-rwgps-id="' . $rwgps_id . '">';
  		echo '<i class="fa fa-spinner fa-spin fa-2x"></i>';
  		echo '</div>'; 

  		echo '	</div>';
		echo '</div>';
	}
}


function show_edit_attendees_widget () {
	// Display the Edit Attendees widget.
	global $wp_query;

	echo '<div class="entry themeform">';
	

	echo '<div id="rsvp-list" class="widget">';
	echo '		    <h3 id="reply-title" class="comment-reply-title">Edit Attendees</h3>';


	$args = array (	"name"			=> "Primary",
			"id"			=> "primary",
			"description"	=> "Normal full width sidebar",
			"class"			=> "",
			"before_widget"	=> "",
			"after_widget"	=> "",
			"before_title"	=> "",
			"after_title"	=> "",
			"widget_id"		=> "bimbler_edit_attendees",
			"widget_name"	=> "Bimbler Edit Attendees Widget",
			);
		
	$instance = array (	"title"			=> "",
					);

	the_widget ('Bimbler_Edit_Attendees_Widget', $instance, $args);

	echo '</div>';
	
	echo '</div>';
}


	$sidebar = alx_sidebar_primary();
	$layout = alx_layout_class();
	
//	error_log('Displaying: "' . TribeEvents::instance()->displaying . '"');
	
	if (1 && ($layout != 'col-1c')):
?>
	
		<div class="sidebar s1">
		
		<a class="sidebar-toggle" title="<?php _e('Expand Sidebar','hueman'); ?>"><i class="fa icon-sidebar-toggle"></i></a>
		
		<div class="sidebar-content">
			
			<div class="sidebar-top group">
				<p><?php _e('Follow:','hueman'); ?></p>
				<?php alx_social_links() ; ?>
			</div>
			
			<?php if ( ot_get_option( 'post-nav' ) == 's1') { get_template_part('inc/post-nav'); } ?>
			
			<?php if( is_page_template('page-templates/child-menu.php') ): ?>
			<ul class="child-menu group">
				<?php wp_list_pages('title_li=&sort_column=menu_order&depth=3'); ?>
			</ul>
			<?php endif; ?>
			
			<?php //dynamic_sidebar($sidebar); ?>
			<?php //show_rsvps (); ?>

			<?php // add_rsvp_form (); ?>
			
			<?php if ( current_user_can( 'manage_options')) { show_edit_attendees_widget (); } ?>
			
			<?php //show_cuesheet_widget (); ?>
			
			<?php show_nexton_widget (); ?>

			<?php show_gps_download_widget (); ?>
			
			</div><!--/.sidebar-content-->
		
	</div><!--/.sidebar-->

	<?php if (
		( $layout == 'col-3cm' ) ||
		( $layout == 'col-3cl' ) ||
		( $layout == 'col-3cr' ) )
		{ get_template_part('sidebar-2'); } 
	?>
				
<?php endif; ?>