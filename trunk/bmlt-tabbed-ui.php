<?php
	/**
	Plugin Name: BMLT Tabbed UI
	Plugin URI: http://wordpress.org/extend/plugins/bmlt-tabbed-ui/
	Description: Adds a jQuery Tabbed UI for BMLT.
	Version: 3.3
	*/
	/* Disallow direct access to the plugin file */
	if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
		//die('Sorry, but you cannot access this page directly.');
	}
	if (!class_exists("BMLTTabs")) {
		class BMLTTabs
		{
			// Class Variables
			function __construct()
			{
				if (is_admin()) {
					// Back end
					add_action("admin_menu", array(
						&$this,
						"admin_menu_link"
					));
				} else {
					// Front end
					if (function_exists('bp_is_groups_component') && bp_is_groups_component()) {
						/* Do not add JS/CSS if Buddypress installed and in a Buddypress group */
					} else {
						add_action("init", array(
							&$this,
							"enqueue_frontend_files"
						));
						add_shortcode('bmlt_tabs', array(
							&$this,
							"tabbed_ui"
						));
						add_shortcode('bmlt_count', array(
							&$this,
							"meeting_count"
						));
						add_shortcode('group_count', array(
							&$this,
							"bmlt_group_count"
						));
					}
				}
				// Content filter
				add_filter('the_content', array(
					&$this,
					'filter_content'
				), 0);
			}
			function BMLTTabs()
			{
				$this->__construct();
			}
			function filter_content($content)
			{
				return $content;
			}
			/**
			* @desc Adds JS/CSS to the header
			*/
			function enqueue_frontend_files()
			{
				wp_enqueue_style("bmlttabsfrontend-css", plugin_dir_url(__FILE__) . "css/bmlt_tabs.css", false, "1.0", 'all');
				wp_enqueue_style("custom.min", plugin_dir_url(__FILE__) . "css/custom.min.css", false, "1.0", 'all');
				wp_enqueue_script("custom.min", plugin_dir_url(__FILE__) . "js/custom.min.js", array('jquery'), "1.0", false);
			}
			/**
			* @desc BMLT Tabs Create shortcode
			*/
			function tabbed_ui($atts, $content = null)
			{
				extract(shortcode_atts(array(
					"service_body" => '',
					"service_body_parent" => '',
					"template" => ''
				), $atts));
				$output   = '';
				$services = '';
				if (!$template) {
					$template = '1';
				}
				if ($template != '1' && $template != '2' && $template != '3') {
					Return '<p>BMLT Tabs Error: Template must = 1 or 2 or 3.</p>';
				}
				if ($service_body_parent != Null && $service_body != Null) {
					Return '<p>BMLT Tabs Error: Cannot use service_body_parent and service_body at the same time.</p>';
				}
				if ($service_body != Null) {
					$service_body = array_map('trim', explode(",", $service_body));
					foreach ($service_body as $key) {
						$services .= '&services[]=' . $key;
					}
				}
				if ($service_body_parent != Null) {
					$service_body = array_map('trim', explode(",", $service_body_parent));
					foreach ($service_body as $key) {
						$services .= '&recursive=1&services[]=' . $key;
					}
				}
				$ch      = curl_init();
				$timeout = 30; // set to zero for no timeout
				curl_setopt($ch, CURLOPT_URL, "http://naflorida.org/bmlt_server/client_interface/json/?switcher=GetSearchResults&services[]=$services&sort_key=time");
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
				$results = curl_exec($ch);
				curl_close($ch);
				$ch      = curl_init();
				curl_setopt($ch, CURLOPT_URL, "http://naflorida.org/bmlt_server/client_interface/json/?switcher=GetFormats");
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
				$formats = curl_exec($ch);
				curl_close($ch);
				$format = json_decode($formats,true);
				$result = json_decode($results,true);
	
				$output .= '<script type="text/javascript">';
				$output .= 'jQuery(document).ready( function($) {';
				$output .= '$( "#tabs" ).tabs();';
				$output .= 'var d = new Date();';
				$output .= 'var n = d.getDay();';
				$output .= '$( "#tabs" ).tabs("select", n);';
				$output .= '$(".bmlt_simple_format_table tr:even, .sunday tr:even, .monday tr:even, .tuesday tr:even, .wednesday tr:even, .thursday tr:even, .friday tr:even, .saturday tr:even").addClass("bmlt_alt_0");';
				$output .= '$(".bmlt_simple_format_table tr:odd, .sunday tr:odd, .monday tr:odd, .tuesday tr:odd, .wednesday tr:odd, .thursday tr:odd, .friday tr:odd, .saturday tr:odd").addClass("bmlt_alt_1");';
				$output .= '$("#tabs").show();';
				$output .= '});';
				$output .= '</script>';
	
				if ( $template == '1' ) {
					$sunday = '<table class="bmlt_simple_meetings_table sunday" cellpadding="0" cellspacing="0" summary="Meetings">';
					$monday = '<table class="bmlt_simple_meetings_table monday" cellpadding="0" cellspacing="0" summary="Meetings">';
					$tuesday = '<table class="bmlt_simple_meetings_table tuesday" cellpadding="0" cellspacing="0" summary="Meetings">';
					$wednesday = '<table class="bmlt_simple_meetings_table wednesday" cellpadding="0" cellspacing="0" summary="Meetings">';
					$thursday = '<table class="bmlt_simple_meetings_table thursday" cellpadding="0" cellspacing="0" summary="Meetings">';
					$friday = '<table class="bmlt_simple_meetings_table friday" cellpadding="0" cellspacing="0" summary="Meetings">';
					$saturday = '<table class="bmlt_simple_meetings_table saturday" cellpadding="0" cellspacing="0" summary="Meetings">';
					foreach ($result as $key => $value) {
						$duration = explode(':',$value[duration_time]);
						$minutes = intval($duration[0])*60 + intval($duration[1]) + intval($duration[2]);
						$addtime = '+ ' . $minutes . ' minutes';
						$end_time = date ('g:ia',strtotime($value[start_time] . ' ' . $addtime));
						$value[start_time] = date ('g:i A',strtotime($value[start_time]));
						if ($value[location_text]) {
							$location_text = $value[location_text] . ',';
						} else { 
							$location_text = '';
						};
						if ($value[location_info]) {
							$location_info = ' (' . $value[location_info] . ')';
						} else { 
							$location_info = '';
						};
						$formats = $value[formats];
						$location = "<a target='_blank' href='http://maps.google.com/maps?q=$value[latitude],$value[longitude]+($value[meeting_name])&ll=$value[latitude],$value[longitude]'>$location_text $value[location_street]$location_info</a>";
						if ( $value[weekday_tinyint] == 1 && $value[location_street] ) {
							$sunday .= "<tr class='bmlt_simple_meeting_one_meeting_tr'>";
							$sunday .= "<td class='bmlt_simple_meeting_one_meeting_time_td'>$value[start_time]</td>";
							$sunday .= "<td class='bmlt_simple_meeting_one_meeting_name_td'>$value[meeting_name]</td>";
							$sunday .= "<td class='bmlt_simple_meeting_one_meeting_address_td'>$location</td>";
							$sunday .= "<td class='bmlt_simple_meeting_one_meeting_town_td'><span class='c_comdef_search_results_municipality'>$value[location_municipality]</span></td>";
							$sunday .= "<td class='bmlt_simple_meeting_one_meeting_format_td'>$formats</td>";
							$sunday .= "</tr>";
						} elseif ( $value[weekday_tinyint] == 2 && $value[location_street] ) {
							$monday .= "<tr class='bmlt_simple_meeting_one_meeting_tr'>";
							$monday .= "<td class='bmlt_simple_meeting_one_meeting_time_td'>$value[start_time]</td>";
							$monday .= "<td class='bmlt_simple_meeting_one_meeting_name_td'>$value[meeting_name]</td>";
							$monday .= "<td class='bmlt_simple_meeting_one_meeting_address_td'>$location</td>";
							$monday .= "<td class='bmlt_simple_meeting_one_meeting_town_td'><span class='c_comdef_search_results_municipality'>$value[location_municipality]</span></td>";
							$monday .= "<td class='bmlt_simple_meeting_one_meeting_format_td'>$formats</td>";
							$monday .= "</tr>";
						} elseif ( $value[weekday_tinyint] == 3 && $value[location_street] ) {
							$tuesday .= "<tr class='bmlt_simple_meeting_one_meeting_tr'>";
							$tuesday .= "<td class='bmlt_simple_meeting_one_meeting_time_td'>$value[start_time]</td>";
							$tuesday .= "<td class='bmlt_simple_meeting_one_meeting_name_td'>$value[meeting_name]</td>";
							$tuesday .= "<td class='bmlt_simple_meeting_one_meeting_address_td'>$location</td>";
							$tuesday .= "<td class='bmlt_simple_meeting_one_meeting_town_td'><span class='c_comdef_search_results_municipality'>$value[location_municipality]</span></td>";
							$tuesday .= "<td class='bmlt_simple_meeting_one_meeting_format_td'>$formats</td>";
							$tuesday .= "</tr>";
						} elseif ( $value[weekday_tinyint] == 4 && $value[location_street] ) {
							$wednesday .= "<tr class='bmlt_simple_meeting_one_meeting_tr'>";
							$wednesday .= "<td class='bmlt_simple_meeting_one_meeting_time_td'>$value[start_time]</td>";
							$wednesday .= "<td class='bmlt_simple_meeting_one_meeting_name_td'>$value[meeting_name]</td>";
							$wednesday .= "<td class='bmlt_simple_meeting_one_meeting_address_td'>$location</td>";
							$wednesday .= "<td class='bmlt_simple_meeting_one_meeting_town_td'><span class='c_comdef_search_results_municipality'>$value[location_municipality]</span></td>";
							$wednesday .= "<td class='bmlt_simple_meeting_one_meeting_format_td'>$formats</td>";
							$wednesday .= "</tr>";
						} elseif ( $value[weekday_tinyint] == 5 && $value[location_street] ) {
							$thursday .= "<tr class='bmlt_simple_meeting_one_meeting_tr'>";
							$thursday .= "<td class='bmlt_simple_meeting_one_meeting_time_td'>$value[start_time]</td>";
							$thursday .= "<td class='bmlt_simple_meeting_one_meeting_name_td'>$value[meeting_name]</td>";
							$thursday .= "<td class='bmlt_simple_meeting_one_meeting_address_td'>$location</td>";
							$thursday .= "<td class='bmlt_simple_meeting_one_meeting_town_td'><span class='c_comdef_search_results_municipality'>$value[location_municipality]</span></td>";
							$thursday .= "<td class='bmlt_simple_meeting_one_meeting_format_td'>$formats</td>";
							$thursday .= "</tr>";
						} elseif ( $value[weekday_tinyint] == 6 && $value[location_street] ) {
							$friday .= "<tr class='bmlt_simple_meeting_one_meeting_tr'>";
							$friday .= "<td class='bmlt_simple_meeting_one_meeting_time_td'>$value[start_time]</td>";
							$friday .= "<td class='bmlt_simple_meeting_one_meeting_name_td'>$value[meeting_name]</td>";
							$friday .= "<td class='bmlt_simple_meeting_one_meeting_address_td'>$location</td>";
							$friday .= "<td class='bmlt_simple_meeting_one_meeting_town_td'><span class='c_comdef_search_results_municipality'>$value[location_municipality]</span></td>";
							$friday .= "<td class='bmlt_simple_meeting_one_meeting_format_td'>$formats</td>";
							$friday .= "</tr>";
						} elseif ( $value[weekday_tinyint] == 7 && $value[location_street] ) {
							$saturday .= "<tr class='bmlt_simple_meeting_one_meeting_tr'>";
							$saturday .= "<td class='bmlt_simple_meeting_one_meeting_time_td'>$value[start_time]</td>";
							$saturday .= "<td class='bmlt_simple_meeting_one_meeting_name_td'>$value[meeting_name]</td>";
							$saturday .= "<td class='bmlt_simple_meeting_one_meeting_address_td'>$location</td>";
							$saturday .= "<td class='bmlt_simple_meeting_one_meeting_town_td'><span class='c_comdef_search_results_municipality'>$value[location_municipality]</span></td>";
							$saturday .= "<td class='bmlt_simple_meeting_one_meeting_format_td'>$formats</td>";
							$saturday .= "</tr>";
						}
					}
					$sunday .= "</table>";
					$monday .= "</table>";
					$tuesday .= "</table>";
					$wednesday .= "</table>";
					$thursday .= "</table>";
					$friday .= "</table>";
					$saturday .= "</table>";
				}
				if ( $template == '2' ) {
					$table = '<table class="bmlt_simple_meetings_table sunday" cellpadding="0" cellspacing="0" summary="Meetings">';
					foreach ($result as $key => $value) {
						$duration = explode(':',$value[duration_time]);
						$minutes = intval($duration[0])*60 + intval($duration[1]) + intval($duration[2]);
						$addtime = '+ ' . $minutes . ' minutes';
						$end_time = date ('g:i A',strtotime($value[start_time] . ' ' . $addtime));
						$value[start_time] = date ('g:i A',strtotime($value[start_time]));
						$value[start_time] = "$value[start_time] - $end_time";
						if ($value[location_text]) {
							$location_text = $value[location_text] . '<br/>';
						} else { 
							$location_text = '';
						};
						if ($value[location_info]) {
							$location_info = '<br/>' . $value[location_info];
							$location_info = preg_replace('/(https?):\/\/([A-Za-z0-9\._\-\/\?=&;%,]+)/i', '<a href="$1://$2" target="_blank">$1://$2</a>', $location_info);
						} else { 
							$location_info = '';
						};
						if ($value[comments]) {
							$value[comments] = '<br/>' . $value[comments];
							$value[comments] = preg_replace('/(https?):\/\/([A-Za-z0-9\._\-\/\?=&;%,]+)/i', '<a href="$1://$2" target="_blank">$1://$2</a>', $value[comments]);
						};
						$location = "$value[meeting_name]</br>$location_text$value[location_street] $value[location_municipality], $value[location_province] $value[location_postal_code_1]$location_info";
						$map = "<a target='_blank' href='http://maps.google.com/maps?q=$value[latitude],$value[longitude]+($value[meeting_name])&ll=$value[latitude],$value[longitude]'>Map and Directions</a>";
						if ( $value[weekday_tinyint] == 1 && $value[location_street] ) {
							$sunday .= "<tr class='bmlt_simple_meeting_one_meeting_tr'>";
							$sunday .= "<td class='bmlt_simple_meeting_one_meeting_time_t2_td'>$value[start_time]<br/><a href='#legend'>$value[formats]</a>$value[comments]</td>";
							$sunday .= "<td class='bmlt_simple_meeting_one_meeting_address_t2_td'>$location</td>";
							$sunday .= "<td class='bmlt_simple_meeting_one_meeting_map_t2_td'>$map</td>";
							$sunday .= "</tr>";
						} elseif ( $value[weekday_tinyint] == 2 && $value[location_street] ) {
							$monday .= "<tr class='bmlt_simple_meeting_one_meeting_tr'>";
							$monday .= "<td class='bmlt_simple_meeting_one_meeting_time_t2_td'>$value[start_time]<br/><a href='#legend'>$value[formats]</a>$value[comments]</td>";
							$monday .= "<td class='bmlt_simple_meeting_one_meeting_address_t2_td'>$location</td>";
							$monday .= "<td class='bmlt_simple_meeting_one_meeting_map_t2_td'>$map</td>";
							$monday .= "</tr>";
						} elseif ( $value[weekday_tinyint] == 3 && $value[location_street] ) {
							$tuesday .= "<tr class='bmlt_simple_meeting_one_meeting_tr'>";
							$tuesday .= "<td class='bmlt_simple_meeting_one_meeting_time_t2_td'>$value[start_time]<br/><a href='#legend'>$value[formats]</a>$value[comments]</td>";
							$tuesday .= "<td class='bmlt_simple_meeting_one_meeting_address_t2_td'>$location</td>";
							$tuesday .= "<td class='bmlt_simple_meeting_one_meeting_map_t2_td'>$map</td>";
							$tuesday .= "</tr>";
						} elseif ( $value[weekday_tinyint] == 4 && $value[location_street] ) {
							$wednesday .= "<tr class='bmlt_simple_meeting_one_meeting_tr'>";
							$wednesday .= "<td class='bmlt_simple_meeting_one_meeting_time_t2_td'>$value[start_time]<br/><a href='#legend'>$value[formats]</a>$value[comments]</td>";
							$wednesday .= "<td class='bmlt_simple_meeting_one_meeting_address_t2_td'>$location</td>";
							$wednesday .= "<td class='bmlt_simple_meeting_one_meeting_map_t2_td'>$map</td>";
							$wednesday .= "</tr>";
						} elseif ( $value[weekday_tinyint] == 5 && $value[location_street] ) {
							$thursday .= "<tr class='bmlt_simple_meeting_one_meeting_tr'>";
							$thursday .= "<td class='bmlt_simple_meeting_one_meeting_time_t2_td'>$value[start_time]<br/><a href='#legend'>$value[formats]</a>$value[comments]</td>";
							$thursday .= "<td class='bmlt_simple_meeting_one_meeting_address_t2_td'>$location</td>";
							$thursday .= "<td class='bmlt_simple_meeting_one_meeting_map_t2_td'>$map</td>";
							$thursday .= "</tr>";
						} elseif ( $value[weekday_tinyint] == 6 && $value[location_street] ) {
							$friday .= "<tr class='bmlt_simple_meeting_one_meeting_tr'>";
							$friday .= "<td class='bmlt_simple_meeting_one_meeting_time_t2_td'>$value[start_time]<br/><a href='#legend'>$value[formats]</a>$value[comments]</td>";
							$friday .= "<td class='bmlt_simple_meeting_one_meeting_address_t2_td'>$location</td>";
							$friday .= "<td class='bmlt_simple_meeting_one_meeting_map_t2_td'>$map</td>";
							$friday .= "</tr>";
						} elseif ( $value[weekday_tinyint] == 7 && $value[location_street] ) {
							$saturday .= "<tr class='bmlt_simple_meeting_one_meeting_tr'>";
							$saturday .= "<td class='bmlt_simple_meeting_one_meeting_time_t2_td'>$value[start_time]<br/><a href='#legend'>$value[formats]</a>$value[comments]</td>";
							$saturday .= "<td class='bmlt_simple_meeting_one_meeting_address_t2_td'>$location</td>";
							$saturday .= "<td class='bmlt_simple_meeting_one_meeting_map_t2_td'>$map</td>";
							$saturday .= "</tr>";
						}
					}
					$table .= "<tr><td colspan='3' style='font-size:16px !important;'><strong>MONDAY</strong></td></tr>";
					$table .= $monday;
					$table .= "<tr><td colspan='3' style='font-size:16px !important;'><strong>TUESDAY</strong></td></tr>";
					$table .= $tuesday;
					$table .= "<tr><td colspan='3' style='font-size:16px !important;'><strong>WEDNESDAY</strong></td></tr>";
					$table .= $wednesday;
					$table .= "<tr><td colspan='3' style='font-size:16px !important;'><strong>THURSDAY</strong></td></tr>";
	+				$table .= $thursday;
					$table .= "<tr><td colspan='3' style='font-size:16px !important;'><strong>FRIDAY</strong></td></tr>";
					$table .= $friday;
					$table .= "<tr><td colspan='3' style='font-size:16px !important;'><strong>SATURDAY</strong></td></tr>";
					$table .= $saturday;
					$table .= "<tr><td colspan='3' style='font-size:16px !important;'><strong>SUNDAY</strong></td></tr>";
					$table .= $sunday;
					$table .= '</table><br/>';
				}
				if ( $template == '3' ) {
					$sunday = '<table class="bmlt_simple_meetings_table sunday" cellpadding="0" cellspacing="0" summary="Meetings">';
					$monday = '<table class="bmlt_simple_meetings_table monday" cellpadding="0" cellspacing="0" summary="Meetings">';
					$tuesday = '<table class="bmlt_simple_meetings_table tuesday" cellpadding="0" cellspacing="0" summary="Meetings">';
					$wednesday = '<table class="bmlt_simple_meetings_table wednesday" cellpadding="0" cellspacing="0" summary="Meetings">';
					$thursday = '<table class="bmlt_simple_meetings_table thursday" cellpadding="0" cellspacing="0" summary="Meetings">';
					$friday = '<table class="bmlt_simple_meetings_table friday" cellpadding="0" cellspacing="0" summary="Meetings">';
					$saturday = '<table class="bmlt_simple_meetings_table saturday" cellpadding="0" cellspacing="0" summary="Meetings">';
					foreach ($result as $key => $value) {
						$duration = explode(':',$value[duration_time]);
						$minutes = intval($duration[0])*60 + intval($duration[1]) + intval($duration[2]);
						$addtime = '+ ' . $minutes . ' minutes';
						$end_time = date ('g:i A',strtotime($value[start_time] . ' ' . $addtime));
						$value[start_time] = date ('g:i A',strtotime($value[start_time]));
						$value[start_time] = "$value[start_time] - $end_time";
						if ($value[location_text]) {
							$location_text = $value[location_text] . '<br/>';
						} else { 
							$location_text = '';
						};
						if ($value[location_info]) {
							$location_info = '<br/>' . $value[location_info];
							$location_info = preg_replace('/(https?):\/\/([A-Za-z0-9\._\-\/\?=&;%,]+)/i', '<a href="$1://$2" target="_blank">$1://$2</a>', $location_info);
						} else { 
							$location_info = '';
						};
						if ($value[comments]) {
							$value[comments] = '<br/>' . $value[comments];
							$value[comments] = preg_replace('/(https?):\/\/([A-Za-z0-9\._\-\/\?=&;%,]+)/i', '<a href="$1://$2" target="_blank">$1://$2</a>', $value[comments]);
						};
						$location = "$value[meeting_name]</br>$location_text$value[location_street] $value[location_municipality], $value[location_province] $value[location_postal_code_1]$location_info";
						$map = "<a target='_blank' href='http://maps.google.com/maps?q=$value[latitude],$value[longitude]+($value[meeting_name])&ll=$value[latitude],$value[longitude]'>Map and Directions</a>";
						if ( $value[weekday_tinyint] == 1 && $value[location_street] ) {
							$sunday .= "<tr class='bmlt_simple_meeting_one_meeting_tr'>";
							$sunday .= "<td class='bmlt_simple_meeting_one_meeting_time_t2_td'>$value[start_time]<br/>$value[formats]$value[comments]</td>";
							$sunday .= "<td class='bmlt_simple_meeting_one_meeting_address_t2_td'>$location</td>";
							$sunday .= "<td class='bmlt_simple_meeting_one_meeting_map_t2_td'>$map</td>";
							$sunday .= "</tr>";
						} elseif ( $value[weekday_tinyint] == 2 && $value[location_street] ) {
							$monday .= "<tr class='bmlt_simple_meeting_one_meeting_tr'>";
							$monday .= "<td class='bmlt_simple_meeting_one_meeting_time_t2_td'>$value[start_time]<br/>$value[formats]$value[comments]</td>";
							$monday .= "<td class='bmlt_simple_meeting_one_meeting_address_t2_td'>$location</td>";
							$monday .= "<td class='bmlt_simple_meeting_one_meeting_map_t2_td'>$map</td>";
							$monday .= "</tr>";
						} elseif ( $value[weekday_tinyint] == 3 && $value[location_street] ) {
							$tuesday .= "<tr class='bmlt_simple_meeting_one_meeting_tr'>";
							$tuesday .= "<td class='bmlt_simple_meeting_one_meeting_time_t2_td'>$value[start_time]<br/>$value[formats]$value[comments]</td>";
							$tuesday .= "<td class='bmlt_simple_meeting_one_meeting_address_t2_td'>$location</td>";
							$tuesday .= "<td class='bmlt_simple_meeting_one_meeting_map_t2_td'>$map</td>";
							$tuesday .= "</tr>";
						} elseif ( $value[weekday_tinyint] == 4 && $value[location_street] ) {
							$wednesday .= "<tr class='bmlt_simple_meeting_one_meeting_tr'>";
							$wednesday .= "<td class='bmlt_simple_meeting_one_meeting_time_t2_td'>$value[start_time]<br/>$value[formats]$value[comments]</td>";
							$wednesday .= "<td class='bmlt_simple_meeting_one_meeting_address_t2_td'>$location</td>";
							$wednesday .= "<td class='bmlt_simple_meeting_one_meeting_map_t2_td'>$map</td>";
							$wednesday .= "</tr>";
						} elseif ( $value[weekday_tinyint] == 5 && $value[location_street] ) {
							$thursday .= "<tr class='bmlt_simple_meeting_one_meeting_tr'>";
							$thursday .= "<td class='bmlt_simple_meeting_one_meeting_time_t2_td'>$value[start_time]<br/>$value[formats]$value[comments]</td>";
							$thursday .= "<td class='bmlt_simple_meeting_one_meeting_address_t2_td'>$location</td>";
							$thursday .= "<td class='bmlt_simple_meeting_one_meeting_map_t2_td'>$map</td>";
							$thursday .= "</tr>";
						} elseif ( $value[weekday_tinyint] == 6 && $value[location_street] ) {
							$friday .= "<tr class='bmlt_simple_meeting_one_meeting_tr'>";
							$friday .= "<td class='bmlt_simple_meeting_one_meeting_time_t2_td'>$value[start_time]<br/>$value[formats]$value[comments]</td>";
							$friday .= "<td class='bmlt_simple_meeting_one_meeting_address_t2_td'>$location</td>";
							$friday .= "<td class='bmlt_simple_meeting_one_meeting_map_t2_td'>$map</td>";
							$friday .= "</tr>";
						} elseif ( $value[weekday_tinyint] == 7 && $value[location_street] ) {
							$saturday .= "<tr class='bmlt_simple_meeting_one_meeting_tr'>";
							$saturday .= "<td class='bmlt_simple_meeting_one_meeting_time_t2_td'>$value[start_time]<br/>$value[formats]$value[comments]</td>";
							$saturday .= "<td class='bmlt_simple_meeting_one_meeting_address_t2_td'>$location</td>";
							$saturday .= "<td class='bmlt_simple_meeting_one_meeting_map_t2_td'>$map</td>";
							$saturday .= "</tr>";
						}
					}
					$sunday .= "</table>";
					$monday .= "</table>";
					$tuesday .= "</table>";
					$wednesday .= "</table>";
					$thursday .= "</table>";
					$friday .= "</table>";
					$saturday .= "</table>";
				}
				$format_table = '<a id="legend" name="legend"></a><table class="bmlt_simple_format_table" cellpadding="0" cellspacing="0" summary="Format Codes">';
				if ( $template == '2' ) {
					$format_table .= "<tr><td colspan='3' style='font-size:16px !important;'><strong>MEETING FORMATS</strong></td></tr>";
				}
				asort($format);
				foreach ($format as $key => $value) {
					$format_table .= '<tr class="bmlt_simple_format_one_format_tr">';
					$format_table .= "<td class='bmlt_simple_format_one_format_key_td'>$value[key_string]</td>";
					$format_table .= "<td class='bmlt_simple_format_one_format_name_td'>$value[name_string]</td>";
					$format_table .= "<td class='bmlt_simple_format_one_format_description_td'>$value[description_string]</td>";
					$format_table .= "</tr>";
				}
				$format_table .= "</table>";
				
				$format_table = preg_replace('/(https?):\/\/([A-Za-z0-9\._\-\/\?=&;%,]+)/i', '<a href="$1://$2" target="_blank">$1://$2</a>', $format_table);			

				if ( $template == '1' || $template == '3' ) {
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
					$output .= '<div id="tabs-1">' . $sunday . '</div>';
					$output .= '<div id="tabs-2">' . $monday . '</div>';
					$output .= '<div id="tabs-3">' . $tuesday . '</div>';
					$output .= '<div id="tabs-4">' . $wednesday . '</div>';
					$output .= '<div id="tabs-5">' . $thursday . '</div>';
					$output .= '<div id="tabs-6">' . $friday . '</div>';
					$output .= '<div id="tabs-7">' . $saturday . '</div>';
					$output .= '<div id="tabs-8">' . $format_table . '</div>';
					$output .= '</div>';
				}
				if ( $template == '2' ) {
					$output .= '<div>' . $table . '</div>';
					$output .= '<div>' . $format_table . '</div>';
				}
				return $output;
			}
			/**
			* @desc BMLT Meeting Count
			*/
			function meeting_count($atts, $content = null)
			{
				extract(shortcode_atts(array(
					"service_body" => '',
					"subtract" => '',
					"service_body_parent" => ''
				), $atts));
				$services = '';
				$subtract = intval($subtract);
				if ($service_body_parent != Null && $service_body != Null) {
					Return '<p>BMLT Tabs Error: Cannot use service_body_parent and service_body at the same time.</p>';
				}
				if ($service_body != Null) {
					$service_body = array_map('trim', explode(",", $service_body));
					foreach ($service_body as $key) {
						$services .= '&services[]=' . $key;
					}
				}
				if ($service_body_parent != Null) {
					$service_body = array_map('trim', explode(",", $service_body_parent));
					$services .= '&recursive=1';
					foreach ($service_body as $key) {
						$services .= '&services[]=' . $key;
					}
				}
				$ch      = curl_init();
				$timeout = 30; // set to zero for no timeout
				curl_setopt($ch, CURLOPT_URL, 'http://naflorida.org/bmlt_server/client_interface/json/index.php?switcher=GetSearchResults&formats[]=-47' . $services);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
				$results = curl_exec($ch);
				curl_close($ch);
				$results = count(json_decode($results)) - $subtract;
				return $results;
			}
			/**
			* @desc BMLT Group Count
			*/
			function bmlt_group_count($atts, $content = null)
			{
				extract(shortcode_atts(array(
					"service_body" => '',
					"subtract" => '',
					"service_body_parent" => ''
				), $atts));
				$services = '';
				$subtract = intval($subtract);
				if ($service_body_parent != Null && $service_body != Null) {
					Return '<p>BMLT Tabs Error: Cannot use service_body_parent and service_body at the same time.</p>';
				}
				if ($service_body != Null) {
					$service_body = array_map('trim', explode(",", $service_body));
					foreach ($service_body as $key) {
						$services .= '&services[]=' . $key;
					}
				}
				if ($service_body_parent != Null) {
					$service_body = array_map('trim', explode(",", $service_body_parent));
					$services .= '&recursive=1';
					foreach ($service_body as $key) {
						$services .= '&services[]=' . $key;
					}
				}
				$ch      = curl_init();
				$timeout = 0; // set to zero for no time-out
				curl_setopt($ch, CURLOPT_URL, 'http://naflorida.org/bmlt_server/client_interface/json/index.php?switcher=GetSearchResults&data_field_key=meeting_name&formats[]=-47' . $services);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
				$results = curl_exec($ch);
				curl_close($ch);
				$result = json_decode($results);
				$result = array_map("unserialize", array_unique(array_map("serialize", $result)));
				foreach ($result as $key => $value)
				{
				if ( is_array($value) )
				{
				  $result[$key] = super_unique($value);
				}
				}
				return count($result);
	
			}
			/**
			* @desc Adds the options sub-panel
			*/
			function admin_menu_link()
			{
				//If you change this from add_options_page, MAKE SURE you change the filter_plugin_actions function (below) to
				//reflect the page file name (i.e. - options-general.php) of the page your plugin is under!
				add_options_page('BMLT Tabs', 'BMLT Tabs', 10, basename(__FILE__), array(
					&$this,
					'admin_options_page'
				));
				add_filter('plugin_action_links_' . plugin_basename(__FILE__), array(
					&$this,
					'filter_plugin_actions'
				), 10, 2);
			}
			/**
			* Adds settings/options page
			*/
			function admin_options_page()
			{
				if ($_POST['bmlttabssave']) {
					if (!wp_verify_nonce($_POST['_wpnonce'], 'bmlttabsupdate-options'))
						die('Whoops! There was a problem with the data you posted. Please go back and try again.');
					$this->save_admin_options();
					echo '<div class="updated"><p>Success! Your changes were successfully saved!</p></div>';
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
				echo '<h2>BMLT Tabs Template</h2>';
				echo '<p><b>Shortcode Usage</b></p>';
				echo '<p>Insert the following shortcode into a page.</p>';
				echo '<p><b>[bmlt_count template="1" service_body="2"]</b></p>';
				echo '<p>template = "1" will display a tabbed UI</p>';
				echo '<p>template = "2" will display a table (not tabbed).</p>';
				echo '<p>template = "3" will display a tabbed UI with a different twist.</p>';
				echo '<h2>BMLT Count</h2>';
				echo '<p>Will return the number of meetings for one or more BMLT service bodies.</p>';
				echo '<p><b>Shortcode Usage</b></p>';
				echo '<p>Insert the following shortcode into a page.</p>';
				echo '<p><b>[bmlt_count service_body="2,3,4"]</b></p>';
				echo '<p>service_body = one or more BMLT child service body IDs.</p>';
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
				echo '<h2>BMLT Group Count</h2>';
				echo '<p>Will return the number of Groups for one or more BMLT service bodies.</p>';
				echo '<p><b>Shortcode Usage</b></p>';
				echo '<p>Insert the following shortcode into a page.</p>';
				echo '<p><b>[group_count service_body="2,3,4"]</b></p>';
				echo '<p>service_body = one or more BMLT child service body IDs.</p>';
				echo '<p><i>This will not work for a parent service body ID.  Use service_body_parent switch below.</i></p>';
				echo '<p><b>[group_count service_body_parent="1,2,3"]</b></p>';
				echo '<p>service_body_parent = one or more BMLT parent service body IDs.</p>';
				echo '<p>Will return the number of Groups in one or more BMLT parent service bodies.</p>';
				echo '<p><i>Do not use child service bodies with this switch.  You will get unexpected results</i></p>';
				echo '<p><b>[group_count]</b></p>';
				echo '<p><i>Using the shortcode with no switches will return the number of Groups from all parent service bodies.</i></p>';
			}
			/**
			* @desc Adds the Settings link to the plugin activate/deactivate page
			*/
			function filter_plugin_actions($links, $file)
			{
				//If your plugin is under a different top-level menu than Settings (IE - you changed the function above to something other than add_options_page)
				//Then you're going to want to change options-general.php below to the name of your top-level page
				$settings_link = '<a href="options-general.php?page=' . basename(__FILE__) . '">' . __('Settings') . '</a>';
				array_unshift($links, $settings_link); // before other links
				return $links;
			}
		} //End Class BMLTTabs
	} // end if
	//instantiate the class
	if (class_exists("BMLTTabs")) {
		$BMLTTabs_instance = new BMLTTabs();
	}
	?>