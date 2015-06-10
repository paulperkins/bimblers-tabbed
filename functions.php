<?php

/*  Enqueue css
 /* ------------------------------------ */
if ( ! function_exists( 'bimbler_styles' ) ) {

	function bimbler_styles() {
		wp_enqueue_style( 'parent-style', get_template_directory_uri().'/style.css' );
		wp_enqueue_style( 'child-style', get_stylesheet_uri(), array('parent-style')  );
		
		//wp_enqueue_style('font-awesome-min', get_stylesheet_directory_uri().  '/font-awesome-4.2.0/css/font-awesome.min.css');
		wp_enqueue_style('font-awesome-min', get_stylesheet_directory_uri().  '/font-awesome-4.3.0/css/font-awesome.min.css');
	}

}
add_action( 'wp_enqueue_scripts', 'bimbler_styles' );


/* PP - Added. Enable WYSIWYG editing in the Visual Editor. */
//add_editor_style('style.css');

/* PP - Added. Disable menu bar. */
if ( ! current_user_can( 'manage_options' ) ) {
	show_admin_bar( false );
}

/*
 * Generates CSS elements which contain the 'Primary Color' setting in the Theme's 'Styling' configuration.
 */
function bimbler_add_dynamic_style () {
	
	$output = '<style type="text/css">' . PHP_EOL;
	
	$colour = ot_get_option('color-1');
	
	
	$output .= '.alx-tabs-nav li.active a { border-bottom-color: ' . $colour . ';  }' . PHP_EOL;
	$output .= '.post-comments span:before { border-right-color: ' . $colour . '; }' . PHP_EOL;
	
	$output .= '.alx-tabs-nav li.active a, #footer .alx-tabs-nav li.active a, .comment-tabs li.active a, .wp-pagenavi a:hover, .wp-pagenavi a:active, .wp-pagenavi span.current {' . PHP_EOL;
	$output .= '	border-bottom-color: ' . $colour . '!important;' . PHP_EOL;
	$output .= '}' . PHP_EOL;
	
	
	/* Make the current day in the events calendar view styled as a Bimbler orange background. */
	$output .= '.tribe-events-calendar td.tribe-events-present div[id*="tribe-events-daynum-"] {' . PHP_EOL;
	$output .= '		background-color: ' . $colour . ';' . PHP_EOL;
	$output .= '}' . PHP_EOL;
	
	$output .= '</style>' . PHP_EOL;

	echo $output;
}

add_action('wp_head','bimbler_add_dynamic_style');

//add_theme_support( 'post-thumbnails', array( 'post', 'page', 'movie', 'product' ) );

/* PP - Added. Disable Event GCal and iCal links on event pages. */
remove_action('tribe_events_single_event_after_the_content', array('TribeiCal', 'single_event_links'));
remove_filter('tribe_events_after_footer', array('TribeiCal', 'maybe_add_link'), 10, 1);

// Patch for Ghost vuln.
add_filter( 'xmlrpc_methods' , function( $methods ) { unset( $methods[ 'pingback.ping' ] ); return $methods; } );


