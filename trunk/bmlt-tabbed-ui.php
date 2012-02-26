<?php
/**
Plugin Name: BMLT Tabbed UI
Plugin URI: http://wordpress.org/extend/plugins/bmlt-tabbed-ui/
Description: Adds a jQuery Tabbed UI for BMLT.
Version: 1.4
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
		wp_enqueue_script("bmlt-ui-jquery-tools-js", plugin_dir_url( __FILE__ ) . "js/tabs.js", array('jquery'), "1.0", false);
		wp_enqueue_script("bmlt-ui-dataTables-js", plugin_dir_url( __FILE__ ) . "js/jquery.dataTables.min.js", array(), "1.0", false);
		wp_enqueue_script("bmlt-ui-colReorder-js", plugin_dir_url( __FILE__ ) . "js/ColReorder.min.js", array(), "1.0", false);
		wp_enqueue_script("bmlttabsfrontend-js", plugin_dir_url( __FILE__ ) . "js/bmlt_tabs.js", array(), "1.0", false);
	}
	/**
	* @desc Create shortcode
	*/
	function tabbed_ui($atts, $content = null){
	extract(shortcode_atts(array("service_body" => ''), $atts));
	$output = '<ul class="css-tabs">
					<li><a href="#">Sunday</a></li>
					<li><a href="#">Monday</a></li>
					<li><a href="#">Tuesday</a></li>
					<li><a href="#">Wednesday</a></li>
					<li><a href="#">Thursday</a></li>
					<li><a href="#">Friday</a></li>
					<li><a href="#">Saturday</a></li>
					<li><a href="#">Legend</a></li>
				</ul>
				<div class="css-panes">
					<div>' . apply_filters( 'the_content',"<!--BMLT_SIMPLE(switcher=GetSearchResults&weekdays[]=1&services[]={$service_body})-->" ) . '</div>
					<div>' . apply_filters( 'the_content',"<!--BMLT_SIMPLE(switcher=GetSearchResults&weekdays[]=2&services[]={$service_body})-->" ) . '</div>
					<div>' . apply_filters( 'the_content',"<!--BMLT_SIMPLE(switcher=GetSearchResults&weekdays[]=3&services[]={$service_body})-->" ) . '</div>
					<div>' . apply_filters( 'the_content',"<!--BMLT_SIMPLE(switcher=GetSearchResults&weekdays[]=4&services[]={$service_body})-->" ) . '</div>
					<div>' . apply_filters( 'the_content',"<!--BMLT_SIMPLE(switcher=GetSearchResults&weekdays[]=5&services[]={$service_body})-->" ) . '</div>
					<div>' . apply_filters( 'the_content',"<!--BMLT_SIMPLE(switcher=GetSearchResults&weekdays[]=6&services[]={$service_body})-->" ) . '</div>
					<div>' . apply_filters( 'the_content',"<!--BMLT_SIMPLE(switcher=GetSearchResults&weekdays[]=7&services[]={$service_body})-->" ) . '</div>
					<div>' . apply_filters( 'the_content',"<!--BMLT_SIMPLE(switcher=GetFormats)-->" ) . '</div>
				</div>';
	return $output;
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
		echo '<div class="wrap">
		<h2>BMLT Tabs</h2>
		<form method="post" id="bmlttabsoptions">';
		wp_nonce_field('bmlttabsupdate-options');
		echo '<p><b>Shortcode Usage</b></p>';
		echo '<p>Insert the following shortcode into a page.</p>';
		echo '<p>[bmlt_tabs service_body="1"]</p>';
		echo '<p>service_body = BMLT service body ID</p>';
		echo "<p>If you don't know your service body ID, ask your BMLT administrator.</p>";
		echo '</form>';
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