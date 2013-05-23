<?php

/**

Plugin Name: BMLT Tabbed UI
Plugin URI: http://wordpress.org/extend/plugins/bmlt-tabbed-ui/
Description: Adds a jQuery Tabbed UI for BMLT.
Version: 2.0

*/

/* Disallow direct access to the plugin file */

if (basename($_SERVER['PHP_SELF']) == basename (__FILE__)) {

	//die('Sorry, but you cannot access this page directly.');

}

if (!class_exists("BMLTTabs")) {

class BMLTTabs {

	// Class Variables

	function __construct() {

		if ( is_admin() ) {

			// Back end

			add_action("admin_menu", array(&$this,"admin_menu_link"));

		} else {

			// Front end

			if ( function_exists('bp_is_groups_component') && bp_is_groups_component() ) {

				/* Do not add JS/CSS if Buddypress installed and in a Buddypress group */

			} else {

				add_action("init", array(&$this,"enqueue_frontend_files"));

				add_shortcode('bmlt_tabs', array(&$this,"tabbed_ui"));
				
				add_shortcode('bmlt_count', array(&$this,"meeting_count"));

			}

		}

		// Content filter

		add_filter('the_content', array(&$this, 'filter_content'), 0);

	}

	function BMLTTabs() {

		$this->__construct();

	}

	function filter_content($content) {

		return $content;

	}

	/**

	* @desc Adds JS/CSS to the header

	*/
	function enqueue_frontend_files() {

		wp_enqueue_style("bmlttabsfrontend-css", plugin_dir_url( __FILE__ ) . "css/bmlt_tabs.css", false, "1.0", 'all');

		wp_enqueue_style("custom.min", plugin_dir_url( __FILE__ ) . "css/custom.min.css", false, "1.0", 'all');

		wp_enqueue_script("custom.min", plugin_dir_url( __FILE__ ) . "js/custom.min.js", array('jquery'), "1.0", false);

		wp_enqueue_script("bmlt-ui-dataTables-js", plugin_dir_url( __FILE__ ) . "js/jquery.dataTables.min.js", array('jquery'), "1.0", false);

		wp_enqueue_script("bmlt-ui-colReorder-js", plugin_dir_url( __FILE__ ) . "js/ColReorder.min.js", array(), "1.0", false);

		wp_enqueue_script("bmlttabsfrontend-js", plugin_dir_url( __FILE__ ) . "js/bmlt_tabs.js", array(), "1.0", false);

	}

	/**

	* @desc Create shortcode

	*/

	function tabbed_ui($atts, $content = null){

	extract(shortcode_atts(array("service_body" => '',"service_body_parent" => ''), $atts));
	
	$output = '';
	$services = '';

	if( $service_body_parent != Null && $service_body != Null ) {

		Return '<p>BMLT Tabs Error: Cannot use service_body_parent and service_body at the same time.</p>';
		
	}

	if( $service_body != Null) {

		$service_body = array_map('trim',explode(",",$service_body));

		foreach($service_body as $key) {

			$services .= '&services[]=' . $key;

		}
	
	}
	
	if( $service_body_parent != Null) {

		$service_body = array_map('trim',explode(",",$service_body_parent));

		foreach($service_body as $key) {

			$services .= '&recursive=1&services[]=' . $key;

		}
	
	}

	$output .= '<script type="text/javascript">';
	$output .= 'jQuery(document).ready( function($) {';
	$output .= '$( "#tabs" ).tabs();';
	$output .= 'var d = new Date();';
	$output .= 'var n = d.getDay();';
	$output .= '$( "#tabs" ).tabs("select", n);';
	$output .= '});';
	$output .= '</script>';

	$output .= '<div id="tabs">';

	$output .= '<ul>';

	$output .= '<li><a href="#tabs-1">Sunday</a></li>';

	$output .= '<li><a href="#tabs-2">Monday</a></li>';

	$output .= '<li><a href="#tabs-3">Tuesday</a></li>';

	$output .= '<li><a href="#tabs-4">Wednesday</a></li>';

	$output .= '<li><a href="#tabs-5">Thursday</a></li>';

	$output .= '<li><a href="#tabs-6">Friday</a></li>';

	$output .= '<li><a href="#tabs-7">Saturday</a></li>';

	$output .= '<li><a href="#tabs-8">Formats</a></li>';

	$output .= '</ul>';

	$output .= '<div id="tabs-1">' . apply_filters( 'the_content',"<!--BMLT_SIMPLE(switcher=GetSearchResults&weekdays[]=1{$services})-->" ) . '</div>';

	$output .= '<div id="tabs-2">' . apply_filters( 'the_content',"<!--BMLT_SIMPLE(switcher=GetSearchResults&weekdays[]=2{$services})-->" ) . '</div>';

	$output .= '<div id="tabs-3">' . apply_filters( 'the_content',"<!--BMLT_SIMPLE(switcher=GetSearchResults&weekdays[]=3{$services})-->" ) . '</div>';

	$output .= '<div id="tabs-4">' . apply_filters( 'the_content',"<!--BMLT_SIMPLE(switcher=GetSearchResults&weekdays[]=4{$services})-->" ) . '</div>';

	$output .= '<div id="tabs-5">' . apply_filters( 'the_content',"<!--BMLT_SIMPLE(switcher=GetSearchResults&weekdays[]=5{$services})-->" ) . '</div>';

	$output .= '<div id="tabs-6">' . apply_filters( 'the_content',"<!--BMLT_SIMPLE(switcher=GetSearchResults&weekdays[]=6{$services})-->" ) . '</div>';

	$output .= '<div id="tabs-7">' . apply_filters( 'the_content',"<!--BMLT_SIMPLE(switcher=GetSearchResults&weekdays[]=7{$services})-->" ) . '</div>';

	$output .= '<div id="tabs-8">' . apply_filters( 'the_content',"<!--BMLT_SIMPLE(switcher=GetFormats)-->" ) . '</div>';

	$output .= '</div>';

	return $output;

	}

	function meeting_count($atts, $content = null){

	extract(shortcode_atts(array("service_body" => '', "subtract" => '', "service_body_parent" => ''), $atts));

	$services = '';
	
	$subtract = intval($subtract);
		
	if( $service_body_parent != Null && $service_body != Null ) {

		Return '<p>BMLT Tabs Error: Cannot use service_body_parent and service_body at the same time.</p>';
		
	}

	if( $service_body != Null) {

		$service_body = array_map('trim',explode(",",$service_body));

		foreach($service_body as $key) {

			$services .= '&services[]=' . $key;

		}
	
	}
	
	if( $service_body_parent != Null) {

		$service_body = array_map('trim',explode(",",$service_body_parent));

		$services .= '&recursive=1';

		foreach($service_body as $key) {

			$services .= '&services[]=' . $key;

		}
	
	}	
	$ch = curl_init();

	$timeout = 30; // set to zero for no timeout

	curl_setopt ($ch, CURLOPT_URL, 'http://naflorida.org/bmlt_server/client_interface/json/index.php?switcher=GetSearchResults'.$services);

	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);

	curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);

	$results = curl_exec($ch);

	curl_close($ch);

	$results = count(json_decode($results)) - $subtract;

	return $results;
	}

	/**

	* @desc Adds the options subpanel

	*/

	function admin_menu_link() {

		//If you change this from add_options_page, MAKE SURE you change the filter_plugin_actions function (below) to

		//reflect the page filename (ie - options-general.php) of the page your plugin is under!

		add_options_page('BMLT Tabs', 'BMLT Tabs', 10, basename(__FILE__), array(&$this,'admin_options_page'));

		add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array(&$this, 'filter_plugin_actions'), 10, 2 );

	}

	/**

	* Adds settings/options page

	*/

	function admin_options_page() { 

		if($_POST['bmlttabssave']){

		if (! wp_verify_nonce($_POST['_wpnonce'], 'bmlttabsupdate-options') ) 

			die('Whoops! There was a problem with the data you posted. Please go back and try again.'); 

		$this->save_admin_options();

		echo '<div class="updated"><p>Success! Your changes were sucessfully saved!</p></div>';

		}

		echo '<div class="wrap">';

		echo '<h2>BMLT Tabs</h2>';

		echo '<form method="post" id="bmlttabsoptions">';

		wp_nonce_field('bmlttabsupdate-options');

		echo '<p><b>Shortcode Usage</b></p>';

		echo '<p>Insert the following shortcode into a page.</p>';

		echo '<p><b>[bmlt_tabs service_body="2,3,4"]</b></p>';

		echo '<p>service_body = one or more BMLT child service body IDs.</p>';

		echo '<p>Using multiple IDs will combine meetings from each service body into the BMLT Tabs interface.</p>';

		echo '<p><i>This will not work for a parent service body ID.  Use service_body_parent switch below.</i></p>';

		echo '<p><b>[bmlt_tabs service_body_parent="1,2,3"]</b></p>';

		echo '<p>service_body_parent = one or more BMLT parent service body IDs.</p>';

		echo '<p>An example parent service body is a Region.  This would be useful to get meetings from a specific Region.</p>';

		echo '<p><i>Do not use child service bodies with this switch.  You will get unexpected results</i></p>';

		echo '<p><b>[bmlt_tabs]</b></p>';

		echo '<p><i>Using the shortcode with no switches will include all meetings from all parent service bodies.</i></p>';

		echo "<p>If you don't know your service body ID, ask your BMLT administrator.</p>";

		echo '<p><i>You cannot combine the service_body and parent_service_body switches.</i></p>';

		echo '</form>';

		echo '<h2>BMLT Count</h2>';

		echo '<p><b>Shortcode Usage</b></p>';

		echo '<p>Insert the following shortcode into a page.</p>';

		echo '<p><b>[bmlt_count service_body="2,3,4"]</b></p>';

		echo '<p>service_body = one or more BMLT child service body IDs.</p>';

		echo '<p>Will return the number of meetings in one or more BMLT service bodies.</p>';

		echo '<p><i>This will not work for a parent service body ID.  Use service_body_parent switch below.</i></p>';

		echo '<p><b>[bmlt_count service_body_parent="1,2,3"]</b></p>';

		echo '<p>service_body_parent = one or more BMLT parent service body IDs.</p>';

		echo '<p>Will return the number of meetings in one or more BMLT parent service bodies.</p>';

		echo '<p><i>Do not use child service bodies with this switch.  You will get unexpected results</i></p>';

		echo '<p><b>[bmlt_count]</b></p>';

		echo '<p><i>Using the shortcode with no switches will return the number of meetings from all parent service bodies.</i></p>';

		echo '<p><b>[bmlt_count service_body="2" subtract="3"]</b></p>';

		echo '<p>subtract = number of meetings to subtract from total meetings (optional)</p>';

		echo '<p><i>Subtract is useful when you are using BMLT for subcommittee meetings and do want to count those meetings.</i></p>';

	}

	/**

	* @desc Adds the Settings link to the plugin activate/deactivate page

	*/

	function filter_plugin_actions($links, $file) {

	   //If your plugin is under a different top-level menu than Settiongs (IE - you changed the function above to something other than add_options_page)

	   //Then you're going to want to change options-general.php below to the name of your top-level page

	   $settings_link = '<a href="options-general.php?page=' . basename(__FILE__) . '">' . __('Settings') . '</a>';

	   array_unshift( $links, $settings_link ); // before other links

	   return $links;

	}

} //End Class BMLTTabs

} // end if

//instantiate the class

if (class_exists("BMLTTabs")) {

    $BMLTTabs_instance = new BMLTTabs();

}

?>