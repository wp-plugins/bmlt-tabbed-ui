<?php
/*
Plugin Name: BMLT Tabbed UI
Plugin URI: http://wordpress.org/extend/plugins/bmlt-tabbed-ui/
Description: Adds a jQuery Tabbed UI for BMLT.
Author: Jack S Florida Region
Version: 5.1.3
*/

/* Disallow direct access to the plugin file */

if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
	// die('Sorry, but you cannot access this page directly.');
}


if (!class_exists("BMLTTabs")) {
	class BMLTTabs {
		/*** @var string The plugin version */
		var $version = '5.1.3';
		/*** @var string The options string name for this plugin */
		var $optionsName = 'bmlt_tabs_options';
		var $options = array();
		function __construct() {
			$this->getOptions();
			
			if (is_admin()) {
				// Back end
				// Initialize the options
				add_action("admin_notices", array(
					&$this,
					"is_root_server_missing"
				));
				add_action("admin_init", array(
					&$this,
					"enqueue_backend_files"
				));
				add_action("admin_menu", array(
					&$this,
					"admin_menu_link"
				));
			} else {
				// Front end
				
				if (function_exists('bp_is_groups_component') && bp_is_groups_component()) {
					/* Do not add JS/CSS if Buddypress installed and in a Buddypress group */
					return;
				}
				
				add_action("init", array(
					&$this,
					"enqueue_frontend_files"
				));
				/*
				add_action("wp_head", array(
				&$this,
				"has_shortcode"
				));
				*/
				add_shortcode('bmlt_tabs', array(
					&$this,
					"tabbed_ui"
				));
				add_shortcode('bmlt_count', array(
					&$this,
					"meeting_count"
				));
				add_shortcode('meeting_count', array(
					&$this,
					"meeting_count"
				));
				add_shortcode('group_count', array(
					&$this,
					"bmlt_group_count"
				));
			}
			
			// Content filter
			add_filter('the_content', array(
				&$this,
				'filter_content'
			), 0);
		}
		
		
		function has_shortcode() {
			$post_to_check = get_post(get_the_ID());
			// false because we have to search through the post content first
			$found         = false;
			var_dump($content);
			exit;
			// check the post content for the short code
			
			if (stripos($post_to_check->post_content, '[bmlt_tabs') !== false) {
				return true;
			}
			
			
			if (stripos($post_to_check->post_content, '[bmlt_count') !== false) {
				return true;
			}
			
			
			if (stripos($post_to_check->post_content, '[meeting_count') !== false) {
				return true;
			}
			
			
			if (stripos($post_to_check->post_content, '[group_count') !== false) {
				return true;
			}
			
			return false;
		}
		
		
		function is_root_server_missing() {
			$root_server = $this->options['root_server'];
			
			if ($root_server == '') {
				echo '<div id="message" class="error"><p>Missing BMLT Root Server in settings for BMLT Tabs.</p>';
				$url = admin_url('options-general.php?page=bmlt-tabbed-ui.php');
				echo "<p><a href='$url'>BMLT_Tabs Settings</a></p>";
				echo '</div>';
			}
			
			add_action("admin_notices", array(
				&$this,
				"clear_admin_message"
			));
		}
		
		
		function clear_admin_message() {
			remove_action("admin_notices", array(
				&$this,
				"is_root_server_missing"
			));
		}
		
		
		function clear_admin_message2() {
			echo '<div id="message" class="error"><p>what</p></div>';
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
		function enqueue_backend_files() {
			wp_enqueue_style("bmlt-tabs-admin", plugin_dir_url(__FILE__) . "css/bmlt_tabs.css", false, "1.0", 'all');
			wp_enqueue_script("bmlt-tabs-admin", plugin_dir_url(__FILE__) . "js/bmlt_tabs_admin.js", array('jquery'), null, false);
		}
		
		/**
		 * @desc Adds JS/CSS to the header
		 */
		function enqueue_frontend_files() {
			wp_enqueue_style("bmlt-tabs-jqueryui", plugin_dir_url(__FILE__) . "css/jquery-ui.min.css", false, null, false);
			wp_enqueue_style("bmlt-tabs-select2", plugin_dir_url(__FILE__) . "css/select2.css", false, null, false);
			wp_enqueue_style("bmlt-tabs", plugin_dir_url(__FILE__) . "css/bmlt_tabs.css", false, null, false);
			wp_enqueue_script("tooltipster", plugin_dir_url(__FILE__) . "js/jquery.tooltipster.min.js", array('jquery'), null, true);
			//wp_enqueue_script("google-maps", "https://maps.googleapis.com/maps/api/js?v=3.exp", array('jquery'), null, true);
			wp_enqueue_script('jquery-ui-core');
			wp_enqueue_script('jquery-ui-tabs');
			wp_enqueue_script('jquery-ui-dialog');
			wp_enqueue_script("bmlt-tabs-select2", plugin_dir_url(__FILE__) . "js/select2.min.js", array('jquery'), null, true);
			wp_enqueue_script("spin", plugin_dir_url(__FILE__) . "js/spin.min.js", array('jquery'), null, false);
			wp_enqueue_script("bmlt-tabs", plugin_dir_url(__FILE__) . "js/bmlt_tabs.js", array('jquery'), null, false);
			wp_enqueue_style('dashicons');
		}
		
		function sortBySubkey(&$array, $subkey, $sortType = SORT_ASC) {
			foreach ($array as $subarray) {
				$keys[] = $subarray[$subkey];
			}
			
			array_multisort($keys, $sortType, $array);
		}
		
		
		function getAllMeetings($root_server, $services, $format_id) {
			if ( $format_id != '' ) {
				$format_id = "&formats[]=$format_id";
			}
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, "$root_server/client_interface/json/?switcher=GetSearchResults$format_id$services&sort_key=time");
			curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_VERBOSE, false);
			curl_setopt($ch, CURLOPT_TIMEOUT, 10);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_SSLVERSION, 3);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
			$results  = curl_exec($ch);
			// echo curl_error($ch);
			$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			$c_error  = curl_error($ch);
			$c_errno  = curl_errno($ch);
			curl_close($ch);
			
			if ($httpcode != 200 && $httpcode != 302 && $httpcode != 304) {
				echo "<p style='color: #FF0000;'>Problem Connecting to BMLT Root Server: $root_server</p>";
				return 0;
			}
			
			$result = json_decode($results, true);
			
			If (count($result) == 0 || $result == null) {
				echo "<p style='color: #FF0000;'>No Meetings were Found: $root_server/client_interface/json/?switcher=GetSearchResults$format_id$services&sort_key=time</p>";
				return 0;
			}
			
			return $result;
		}
		
		
		function getday($day) {
			
			if ($day == 1) {
				Return "Sunday";
			}
			
			elseif ($day == 2) {
				return "Monday";
			} elseif ($day == 3) {
				return "Tuesday";
			} elseif ($day == 4) {
				return "Wednesday";
			} elseif ($day == 5) {
				return "Thursday";
			} elseif ($day == 6) {
				return "Friday";
			} elseif ($day == 7) {
				return "Saturday";
			}
			
		}
		
		
		function getTheFormats($root_server) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, "$root_server/client_interface/json/?switcher=GetFormats");
			curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
			$formats = curl_exec($ch);
			curl_close($ch);
			$format = json_decode($formats, true);
			return $format;
		}
		
		function testRootServer($root_server) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, "$root_server/client_interface/serverInfo.xml");
			curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_VERBOSE, false);
			curl_setopt($ch, CURLOPT_TIMEOUT, 10);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_SSLVERSION, 3);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
			$results  = curl_exec($ch);
			$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			$c_error  = curl_error($ch);
			$c_errno  = curl_errno($ch);
			curl_close($ch);
			if ($httpcode != 200 && $httpcode != 302 && $httpcode != 304) {
				//echo '<p>Problem Connecting to BMLT Root Server: ' . $root_server . '</p>';
				return false;
			}
			
			;
			return $results;
		}
		
		function doQuit($message = '') {
			
			ob_flush();
			flush();
			$message .= '
			<script>
			function removeSpinner() {
				var target = document.getElementById("spinner");
				spinner.stop(target);
			}
			removeSpinner();
			</script>';
			return $message;
			
		}
		
		function tabbed_ui($atts, $content = null) {
			
			ini_set('memory_limit', '-1');
			
			global $unique_areas;
			extract(shortcode_atts(array(
				"root_server" => '',
				"service_body" => '',
				"service_body_parent" => '',
				"has_tabs" => '1',
				"has_groups" => '1',
				"has_cities" => '1',
				"has_meetings" => '1',
				"has_formats" => '1',
				"has_locations" => '1',
				"include_city_button" => '1',
				"include_weekday_button" => '1',
				"view_by" => 'weekday',
				"dropdown_width" => 'auto',
				"has_zip_codes" => '1',
				"header" => '1',
				"format_key" => ''
			), $atts));
			$root_server            = ($root_server != '' ? $root_server : $this->options['root_server']);
			$has_tabs               = ($has_meetings == '0' ? '0' : $has_tabs);
			// $has_tabs = ($include_weekday_button == '0' ? '1' : $has_tabs);
			$include_city_button    = ($view_by == 'city' ? '1' : $include_city_button);
			$include_weekday_button = ($view_by == 'weekday' ? '1' : $include_weekday_button);
			$include_city_button    = ($has_meetings == '0' ? '0' : $include_city_button);
			$include_weekday_button = ($has_meetings == '0' ? '0' : $include_weekday_button);
			$format_key          	= ($format_key != '' ? strtoupper($format_key) : '');
			
			// $has_tabs = ($view_by == 'city' ? '0' : $has_tabs);
			
			if ($view_by != 'city' && $view_by != 'weekday') {
				Return '<p>BMLT Tabs Error: view_by must = "city" or "weekday".</p>';
			}
			
			
			if ($include_city_button != '0' && $include_city_button != '1') {
				Return '<p>BMLT Tabs Error: include_city_button must = "0" or "1".</p>';
			}
			
			
			if ($include_weekday_button != '0' && $include_weekday_button != '1') {
				Return '<p>BMLT Tabs Error: include_weekday_button must = "0" or "1".</p>';
			}
			
			
			if ($service_body_parent == Null && $service_body == Null) {
				$area_data       = explode(',', $this->options['service_body_1']);
				$area            = $area_data[0];
				$service_body_id = $area_data[1];
				$parent_body_id  = $area_data[2];
				
				if ($parent_body_id == '0') {
					$service_body_parent = $service_body_id;
				} else {
					$service_body = $service_body_id;
				}
				
			}
			
			
			if ($root_server == '') {
				Return '<p><strong>BMLT Tabs Error: Root Server missing.<br/><br/>Please go to Settings -> BMLT_Tabs and verify Root Server</strong></p>';
			}
			
			$services = '';
			
			if ($service_body_parent != Null && $service_body != Null) {
				Return '<p>BMLT Tabs Error: Cannot use service_body_parent and service_body at the same time.</p>';
			}
			
			
			if ($service_body == '' && $service_body_parent == '') {
				Return '<p>BMLT Tabs Error: Service body missing from shortcode.</p>';
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
			
?>
			<div id="spinner"></div>
			<script>
			var target = document.getElementById("spinner");
			var spinner = new Spinner().spin(target);
			</script>
			<?
			ob_flush();
			flush();
			
			$transient_key = 'bmlt_tabs_' . md5($root_server . $services . $has_tabs . $has_groups . $has_cities . $has_meetings . $has_formats . $has_locations . $include_city_button . $include_weekday_button . $view_by . $dropdown_width . $has_zip_codes . $header . $format_key);
			
			if (intval($this->options['cache_time']) > 0) {
				
				//$output = get_transient('_transient_'.$transient_key);
				$output = get_transient($transient_key);
				
				//$output = gzuncompress($output);
				
				if ($output != '') {
					return $output;
				}
				
			}
			
			$formats = $this->getTheFormats($root_server);
			
			$format_id = '';
			
			if ( $format_key != '' ) {
			
				foreach ($formats as $value) {
				
					if ($value['key_string'] == $format_key) {
					
						$format_id = $value['id'];
						
					}
					
				}
				
			}

			$the_meetings = $this->getAllMeetings($root_server, $services, $format_id);
			
			if ($the_meetings == 0) {
				return $this->doQuit('');
			}
			
			if ($format_key == 'BTW') {
				$unique_areas = $this->get_areas($root_server, 'BTW');
			}
			
			$unique_zip = $unique_city = $unique_group = $unique_location = $unique_format = $unique_weekday = $unique_format_name_string = array();
			
			foreach ($the_meetings as $value) {
				
				$tvalue = explode(',', $value['formats']);
				
				if ($format_key != '' && !in_array($format_key, $tvalue)) {
					continue;
				}
				
				foreach ($tvalue as $t_value) {
					
					$unique_format[] = $t_value;
					
					foreach ($formats as $s_value) {
						
						if ($s_value['key_string'] == $t_value) {
							$unique_format_name_string[] = $s_value['name_string'];
						}
						
					}
					
				}
				
				if (isset($value['location_municipality'])) {
					$unique_city[] = $value['location_municipality'];
				}
				
				
				if (isset($value['meeting_name'])) {
					$unique_group[] = $value['meeting_name'];
				}
				
				
				if (isset($value['location_text'])) {
					$unique_location[] = $value['location_text'];
				}
				
				
				if (isset($value['location_postal_code_1'])) {
					$unique_zip[] = $value['location_postal_code_1'];
				}
				
			}
			
			if (count($unique_group) == 0) {
				return $this->doQuit('No Meetings Found');
			}
			
			$unique_zip                = array_unique($unique_zip);
			$unique_city               = array_unique($unique_city);
			$unique_group              = array_unique($unique_group);
			$unique_location           = array_unique($unique_location);
			$unique_format             = array_unique($unique_format);
			$unique_format_name_string = array_unique($unique_format_name_string);
			
			asort($unique_zip);
			asort($unique_city);
			asort($unique_group);
			asort($unique_location);
			asort($unique_format);
			asort($unique_format_name_string);
			
			array_push($unique_weekday, "1", "2", "3", "4", "5", "6", "7");
			
			$meetings_cities = $meetings_days = $meeting_header = $meetings_tab = "";
			
			for ($x = 0; $x <= 1; $x++) {
				
				if ($x == 0) {
					$unique_values = $unique_city;
				} else {
					$unique_values = $unique_weekday;
				}
				
				foreach ($unique_values as $this_value) {
					
					$this_meeting = $meeting_header = $meeting_tab_header = "";							
					
					foreach ($the_meetings as $value) {
						
						if ($x == 0) {
							
							if (!isset($value['location_municipality']) || $this_value != $value['location_municipality']) {
								continue;
							}
							
						} elseif ($x == 1) {
						
							
							if ($this_value != $value['weekday_tinyint']) {
								continue;
							}
							
						}
						
						$duration            = explode(':', $value['duration_time']);
						$minutes             = intval($duration[0]) * 60 + intval((isset($duration[1]) ? $duration[1] : '0'));
						$addtime             = '+ ' . $minutes . ' minutes';
						$end_time            = date('g:i A', strtotime($value['start_time'] . ' ' . $addtime));
						$value['start_time'] = date('g:i A', strtotime($value['start_time']));
						$value['start_time'] = $value['start_time'] . " - " . $end_time;
						
						$location = $this->getLocation($value, $format_key);
						
						if (isset($value['comments'])) {
							$value['comments'] = $value['comments'];
							$value['comments'] = preg_replace('/(http|https):\/\/([A-Za-z0-9\._\-\/\?=&;%,]+)/i', '<a style="text-decoration: underline;" href="$1://$2" target="_blank">$1://$2</a>', $value['comments']);
							$value['comments'] = "<div class='bmlt-comments'>" . $value['comments'] . "</div>";
						} else {
							$value['comments'] = '';
						}
						
						$map = $this->getMap($value);
						
						$today = '';

						if ($x == 0) {
							$today = "<div class='bmlt-day'>" . $this->getDay($value['weekday_tinyint']) . "</div>";
						}
						
						$meeting_formats = $this->getMeetingFormats($value, $formats);
						
						if ($x == 0) {
							$class = 'bmlt-time';
						} else {
							$class = 'bmlt-time-2';
						}
						
						
						if ($value['formats']) {
							$column1 = "$today<div class=$class>" . $value['start_time'] . "</div><div title='$meeting_formats' class='bmlt-formats tooltip'><div class='dashicons dashicons-search'></div>" . $value['formats'] . "</div>" . $value['comments'];
						} else {
							$column1 = "$today<div class=$class>" . $value['start_time'] . "</div>" . $value['comments'];
						}
						
						$this_meeting .= "<tr>";
						$this_meeting .= "<td class='bmlt-column1'>$column1</td>";
						$this_meeting .= "<td class='bmlt-column2'>$location</td>";
						$this_meeting .= "<td class='bmlt-column3'>$map</td>";
						$this_meeting .= "</tr>";
					}
					
					if ( $this_meeting != "" ) {
					
						$meeting_header = '<div id="bmlt-table-div">';
						
						$meeting_header .= "<table class='bmlt-table header'>";
						
						$meeting_header .= "<thead>";
						
						if ($x == 0) {
							
							if ($this_value) {
								$meeting_header .= "<tr class='meeting-header'><th colspan='4'>" . $this_value . "</th></tr>";
							} else {
								$meeting_header .= "<tr class='meeting-header'><th colspan='4'>NO CITY IN BMLT</th></tr>";
							}
							
						} else {
							
							$meeting_tab_header = "<div id='ui-tabs-" . $this_value . "'>";
							
							$meeting_tab_header .= "<table class='bmlt-table'>";

							$meeting_header .= "<tr class='meeting-header'><th colspan='4'>" . $this->getDay($this_value) . "</th></tr>";
							
						}
						
						$meeting_header .= "</thead>";
						
						$this_meeting .= '</table>';
						$this_meeting .= '</div>';
						
						if ($x == 0) {
							$meetings_cities .= $meeting_header;
							$meetings_cities .= $this_meeting;
						} else {
							$meetings_days .= $meeting_header;
							$meetings_days .= $this_meeting;
							$meetings_tab .= $meeting_tab_header;
							$meetings_tab .= $this_meeting;
						}
						
					}
					
				}
				
			}
			$this_meeting = "";
			/*
			$output = '';
			$output.= '<script type="text/javascript">';
			$output.= 'jQuery( "body" ).addClass( "bmlt-tabs");';
			$output.= '</script>';
			*/
			If ($header == '1') {
			
				$output .= '<div class="hide ui-bmlt-header ui-widget-header">';
				
				if ($view_by == 'weekday') {
					
					if ($include_weekday_button == '1') {
						$output .= '<div class="bmlt-button-container"><a id="day" style="color: #FFF; background-color: #FF6B7F;" class="bmlt-button bmlt-button-weekdays">Weekday</a></div>';
					}
					
					
					if ($include_city_button == '1') {
						$output .= '<div class="bmlt-button-container"><a id="city" style="color: #000; background-color: #63B8EE;" class="bmlt-button bmlt-button-cities">City</a></div>';
					}
					
				} else {
					
					if ($include_weekday_button == '1') {
						$output .= '<div class="bmlt-button-container"><a id="day" style="color: #000; background-color: #63B8EE;" class="bmlt-button bmlt-button-weekdays">Weekday</a></div>';
					}
					
					
					if ($include_city_button == '1') {
						$output .= '<div class="bmlt-button-container"><a id="city" style="color: #FFF; background-color: #FF6B7F;" class="bmlt-button bmlt-button-cities">City</a></div>';
					}
					
				}
				
				
				if ($has_cities == '1') {
					$output .= '
						<select style="width:' . $dropdown_width . ';" data-placeholder="Cities" id="e2">
							<option></option>';
					foreach ($unique_city as $city_value) {
						$output .= "<option value=a-" . strtolower(preg_replace("/\W|_/", '-', $city_value)) . ">$city_value</option>";
					}
					
					$output .= '
						</select>';
				}
				
				
				if ($has_groups == '1') {
					$output .= '
						<select style="width:' . $dropdown_width . ';" data-placeholder="Groups" id="e3">
							<option></option>';
					foreach ($unique_group as $group_value) {
						$output .= "<option value=a-" . strtolower(preg_replace("/\W|_/", '-', $group_value)) . ">$group_value</option>";
					}
					
					$output .= '
						</select>';
				}
				
				
				if ($has_locations == '1') {
					$output .= '
						<select style="width:' . $dropdown_width . ';" data-placeholder="Locations" id="e4">
							<option></option>';
					foreach ($unique_location as $location_value) {
						$output .= "<option value=a-" . strtolower(preg_replace("/\W|_/", '-', $location_value)) . ">$location_value</option>";
					}
					
					$output .= '
						</select>';
				}
				
				
				if ($has_zip_codes == '1') {
					$output .= '
						<select style="width:' . $dropdown_width . ';" data-placeholder="Zip Codes" id="e5">
							<option></option>';
					foreach ($unique_zip as $zip_value) {
						$output .= "<option value=a-" . strtolower(preg_replace("/\W|_/", '-', $zip_value)) . ">$zip_value</option>";
					}
					
					$output .= '
						</select>';
				}
				
				
				if ($has_formats == '1') {
					$output .= '
						<select style="width:' . $dropdown_width . ';" data-placeholder="Formats" id="e6">
							<option></option>';
					foreach ($unique_format_name_string as $format_value) {
						$output .= "<option value=a-" . strtolower(preg_replace("/\W|_/", '-', $format_value)) . ">$format_value</option>";
					}
					
					$output .= '
						</select>';
				}
				
				$output .= '</div>';
			}
			
			
			if ($has_tabs == '1' && $has_meetings == '1') {
				
				if ($view_by == 'weekday') {
					$output .= '<div class="bmlt-page show" id="days">';
				} else {
					$output .= '<div class="bmlt-page hide" id="days">';
				}
				
				$output .= '
					<div class="ui-tabs" id="ui-tabs">
						<ul>
							<li><a href="#ui-tabs-1">Sunday</a></li>
							<li><a href="#ui-tabs-2">Monday</a></li>
							<li><a href="#ui-tabs-3">Tuesday</a></li>
							<li><a href="#ui-tabs-4">Wednesday</a></li>
							<li><a href="#ui-tabs-5">Thursday</a></li>
							<li><a href="#ui-tabs-6">Friday</a></li>
							<li><a href="#ui-tabs-7">Saturday</a></li>
						</ul>
							' . $meetings_tab . '
					</div>
				</div>';
			}
			
			
			if ($has_tabs == '0' && $has_meetings == '1') {
				// if ( $include_weekday_button == '1' ) {
				
				if ($view_by == 'weekday') {
					$output .= '<div class="bmlt-page show" id="days">';
				} else {
					$output .= '<div class="bmlt-page hide" id="days">';
				}
				
				$output .= $meetings_days;
				$output .= '</div>';
				// }
			}
			
			
			if ($has_meetings == '1') {
				// if ( $include_city_button == '1' ) {
				
				if ($view_by == 'weekday') {
					$output .= '<div class="bmlt-page hide" id="cities">';
				} else {
					$output .= '<div class="bmlt-page show" id="cities">';
				}
				
				$output .= $meetings_cities;
				$output .= '</div>';
				$meetings_cities = '';
				// }
			}
			
			
			if ($has_cities == '1') {
				$output .= $this->get_the_meetings($the_meetings, $unique_city, "location_municipality", $formats, $format_key, "City");
			}
			
			
			if ($has_groups == '1') {
				$output .= $this->get_the_meetings($the_meetings, $unique_group, "meeting_name", $formats, $format_key, "Group");
			}
			
			
			if ($has_locations == '1') {
				$output .= $this->get_the_meetings($the_meetings, $unique_location, "location_text", $formats, $format_key, "Location");
			}
			
			
			if ($has_zip_codes == '1') {
				$output .= $this->get_the_meetings($the_meetings, $unique_zip, "location_postal_code_1", $formats, $format_key, "Zip Code");
			}
			
			
			if ($has_formats == '1') {
				$output .= $this->get_the_meetings($the_meetings, $unique_format_name_string, "name_string", $formats, $format_key, "Format");
			}
			
			$output = '<div id="bmlt-tabs" class="bmlt-tabs ui-widget-header hide">' . $output . '</div>';
			
			$output .= '
			<script>
			function removeSpinner() {
				var target = document.getElementById("spinner");
				spinner.stop(target);
			}
			removeSpinner();
			</script>';
			
			$output .= '<div id="divId" class="bmlt-tabs" title="Dialog Title"></div>';
						
			if (intval($this->options['cache_time']) > 0) {
				
				set_transient($transient_key, $output, intval($this->options['cache_time']) * HOUR_IN_SECONDS);
				
			}
			
			return $output;
			
		}
		
		function get_the_meetings($result_data, $unique_data, $unique_value, $formats, $format_key, $where) {
			global $unique_areas;
			
			if ($unique_value == 'name_string') {
				// $unique_data = $unique_format;
			}
			
			$this_output = '';
			foreach ($unique_data as $this_value) {
				$this_output .= "<div class='hide bmlt-page' id='a-" . strtolower(preg_replace("/\W|_/", '-', $this_value)) . "'>";
				//$this_output .= "<div style='text-align: left;margin-bottom: 10px;margin-left: 5px;color: #000;'>$where: $this_value</div>
				$sunday_init    = 0;
				$monday_init    = 0;
				$tuesday_init   = 0;
				$wednesday_init = 0;
				$thursday_init  = 0;
				$friday_init    = 0;
				$saturday_init  = 0;
				foreach ($result_data as $key => $value) {
					
					if ($unique_value == 'name_string') {
						$good = False;
						foreach ($formats as $key => $value1) {
							$key_string  = $value1['key_string'];
							$name_string = $value1['name_string'];
							
							if ($name_string == $this_value) {
								$tvalue = explode(',', $value['formats']);
								foreach ($tvalue as $t_value) {
									
									if ($t_value == $key_string) {
										$good = True;
									}
									
								}
								
							}
							
						}
						
						// var_dump($good);
						
						if ($good == False) {
							continue;
						}
						
						if ($format_key != '' && !in_array($format_key, $tvalue)) {
							continue;
						}
						
					}
					
					elseif (!isset($value[$unique_value])) {
						continue;
					} elseif ($this_value != $value[$unique_value]) {
						continue;
					}
					
					$duration            = explode(':', $value['duration_time']);
					$minutes             = intval($duration[0]) * 60 + intval((isset($duration[1]) ? $duration[1] : '0'));
					$addtime             = '+ ' . $minutes . ' minutes';
					$end_time            = date('g:i A', strtotime($value['start_time'] . ' ' . $addtime));
					$value['start_time'] = date('g:i A', strtotime($value['start_time']));
					$value['start_time'] = $value['start_time'] . " - " . $end_time;
					
					$location = $this->getLocation($value, $format_key);
					
					if (isset($value['comments'])) {
						$value['comments'] = $value['comments'];
						$value['comments'] = preg_replace('/(http|https):\/\/([A-Za-z0-9\._\-\/\?=&;%,]+)/i', '<a style="text-decoration: underline;" href="$1://$2" target="_blank">$1://$2</a>', $value['comments']);
						$value['comments'] = "<div class='bmlt-comments'>" . $value['comments'] . "</div>";
					} else {
						$value['comments'] = '';
					}
					
					$map = $this->getMap($value);
					
					$meeting_formats = $this->getMeetingFormats($value, $formats);
					
					if ($value['formats']) {
						$column1 = "<div class='bmlt-time-2'>" . $value['start_time'] . "</div><div title='$meeting_formats' class='bmlt-formats tooltip'><div class='dashicons dashicons-search'></div>" . $value['formats'] . "</div>" . $value['comments'];
					} else {
						$column1 = "<div class='bmlt-time-2'>" . $value['start_time'] . "</div>" . $value['comments'];
					}
					
					
					if ($value['weekday_tinyint'] == 1) {
						
						if ($sunday_init == 0) {
							$sunday_data = "<table class='bmlt-table'>";
							$sunday_data .= "<thead>";
							$sunday_data .= "<tr class='ui-state-default'><th colspan='4'>SUNDAY</th></tr>";
							$sunday_data .= "</thead><tbody>";
							$sunday_init = 1;
						}
						
						$sunday_data .= "<tr>";
						$sunday_data .= "<td class='bmlt-column1'>$column1</td>";
						$sunday_data .= "<td class='bmlt-column2'>$location</td>";
						$sunday_data .= "<td class='bmlt-column3'>$map</td>";
						$sunday_data .= "</tr>";
					}
					
					elseif ($value['weekday_tinyint'] == 2) {
						
						if ($monday_init == 0) {
							$monday_data = "<table class='bmlt-table'>";
							$monday_data .= "<thead>";
							$monday_data .= "<tr class='ui-state-default'><th colspan='4'>MONDAY</th></tr>";
							$monday_data .= "</thead><tbody>";
							$monday_init = 1;
						}
						
						$monday_data .= "<tr>";
						$monday_data .= "<td class='bmlt-column1'>$column1</td>";
						$monday_data .= "<td class='bmlt-column2'>$location</td>";
						$monday_data .= "<td class='bmlt-column3'>$map</td>";
						$monday_data .= "</tr>";
					} elseif ($value['weekday_tinyint'] == 3) {
						
						if ($tuesday_init == 0) {
							$tuesday_data = "<table class='bmlt-table'>";
							$tuesday_data .= "<thead>";
							$tuesday_data .= "<tr class='ui-state-default'><th colspan='4'>TUESDAY</th></tr>";
							$tuesday_data .= "</thead><tbody>";
							$tuesday_init = 1;
						}
						
						$tuesday_data .= "<tr>";
						$tuesday_data .= "<td class='bmlt-column1'>$column1</td>";
						$tuesday_data .= "<td class='bmlt-column2'>$location</td>";
						$tuesday_data .= "<td class='bmlt-column3'>$map</td>";
						$tuesday_data .= "</tr>";
					} elseif ($value['weekday_tinyint'] == 4) {
						
						if ($wednesday_init == 0) {
							$wednesday_data = "<table class='bmlt-table'>";
							$wednesday_data .= "<thead>";
							$wednesday_data .= "<tr class='ui-state-default'><th colspan='4'>WEDNESDAY</th></tr>";
							$wednesday_data .= "</thead><tbody>";
							$wednesday_init = 1;
						}
						
						$wednesday_data .= "<tr>";
						$wednesday_data .= "<td class='bmlt-column1'>$column1</td>";
						$wednesday_data .= "<td class='bmlt-column2'>$location</td>";
						$wednesday_data .= "<td class='bmlt-column3'>$map</td>";
						$wednesday_data .= "</tr>";
					} elseif ($value['weekday_tinyint'] == 5) {
						
						if ($thursday_init == 0) {
							$thursday_data = "<table class='bmlt-table'>";
							$thursday_data .= "<thead>";
							$thursday_data .= "<tr class='ui-state-default'><th colspan='4'>THURSDAY</th></tr>";
							$thursday_data .= "</thead><tbody>";
							$thursday_init = 1;
						}
						
						$thursday_data .= "<tr>";
						$thursday_data .= "<td class='bmlt-column1'>$column1</td>";
						$thursday_data .= "<td class='bmlt-column2'>$location</td>";
						$thursday_data .= "<td class='bmlt-column3'>$map</td>";
						$thursday_data .= "</tr>";
					} elseif ($value['weekday_tinyint'] == 6) {
						
						if ($friday_init == 0) {
							$friday_data = "<table class='bmlt-table'>";
							$friday_data .= "<thead>";
							$friday_data .= "<tr class='ui-state-default'><th colspan='4'>FRIDAY</th></tr>";
							$friday_data .= "</thead><tbody>";
							$friday_init = 1;
						}
						
						$friday_data .= "<tr>";
						$friday_data .= "<td class='bmlt-column1'>$column1</td>";
						$friday_data .= "<td class='bmlt-column2'>$location</td>";
						$friday_data .= "<td class='bmlt-column3'>$map</td>";
						$friday_data .= "</tr>";
					} elseif ($value['weekday_tinyint'] == 7) {
						
						if ($saturday_init == 0) {
							$saturday_data = "<table class='bmlt-table'>";
							$saturday_data .= "<thead>";
							$saturday_data .= "<tr class='ui-state-default'><th colspan='4'>SATURDAY</th></tr>";
							$saturday_data .= "</thead><tbody>";
							$saturday_init = 1;
						}
						
						$saturday_data .= "<tr>";
						$saturday_data .= "<td class='bmlt-column1'>$column1</td>";
						$saturday_data .= "<td class='bmlt-column2'>$location</td>";
						$saturday_data .= "<td class='bmlt-column3'>$map</td>";
						$saturday_data .= "</tr>";
					}
					
				}
				
				
				if ($sunday_init == 1) {
					$this_output .= "<div id='bmlt-table-div'>$sunday_data</tbody></table></div>";
				}
				
				
				if ($monday_init == 1) {
					$this_output .= "<div id='bmlt-table-div'>$monday_data</tbody></table></div>";
				}
				
				
				if ($tuesday_init == 1) {
					$this_output .= "<div id='bmlt-table-div'>$tuesday_data</tbody></table></div>";
				}
				
				
				if ($wednesday_init == 1) {
					$this_output .= "<div id='bmlt-table-div'>$wednesday_data</tbody></table></div>";
				}
				
				
				if ($thursday_init == 1) {
					$this_output .= "<div id='bmlt-table-div'>$thursday_data</tbody></table></div>";
				}
				
				
				if ($friday_init == 1) {
					$this_output .= "<div id='bmlt-table-div'>$friday_data</tbody></table></div>";
				}
				
				
				if ($saturday_init == 1) {
					$this_output .= "<div id='bmlt-table-div'>$saturday_data</tbody></table></div>";
				}
				
				$this_output .= '</div>';
			}
			
			
			return $this_output;
		}
		
		function getMap($value) {
			
			$isaddress = False;
			
			$map = '<table class="bmlt_a_format">';
			
			$map .= '<tr><td><a onclick="getDirections(' . $value[latitude] . ',' . $value[longitude] . ')" href="#">Directions</a></td><td>From Current Location</td></tr>';
			
			$map .= '<tr><td><a onclick="getMap(' . $value[latitude] . ',' . $value[longitude] . ')" href="#">Street Map</a></td><td>Street Map</td></tr>';
			
			$map .= '<tr><td><a target="_blank" href="https://www.google.com/maps/place/' . $value[latitude] . ',' . $value[longitude] . '/@' . $value[latitude] . ',' . $value[longitude] . ',1436m/data=!3m1!1e3!4m2!3m1!1s0x0:0x0">Earth</a></td><td>Satellite Map</td></tr>';
			
			$map .= '<tr><td><a target="_blank" href="https://maps.google.com/maps?t=h&cid=0&q=' . $value[latitude] . ',' . $value[longitude] . '&ie=UTF8&ll=' . $value[latitude] . ',' . $value[longitude] . '&spn=0.002371,0.004128&z=19&vpsrc=6&ei=en0tVOvaDuOJwQGy94DAAQ&pw=2">Print</a></td><td>Print Map</td></tr>';
			
			$map .= '<tr><td>GPS<td>' . $value[latitude] . ',' . $value[longitude] . '</td></tr>';
			
			if ($isaddress) {
				$map .= '<tr><td><a target="_blank" href="https://maps.google.com/maps?q=' . urlencode($value["location_street"]) . ',+' . urlencode($value["location_municipality"]) . ',+' . urlencode($value["location_province"]) . '&es_sm=122&um=1&ie=UTF-8&sa=X&ei=on0sVL6MMMiTyASf3ICIBQ&ved=0CAgQ_AUoAQ">Street</a></td><td><em>May not work everywhere</em></td></tr>';
			}
			
			$map .= '</table>';
			
			$map = "<div id='map-button' class='bmlt-button'><div title='$map' class='tooltip-map'><div class='dashicons dashicons-map'></div>&#160;Map</div></div>";
			
			return $map;
			
		}
		
		function getMeetingFormats($value, $formats) {
			
			$meeting_formats = '<table class="bmlt_a_format">';
			$tvalue          = explode(',', $value['formats']);
			foreach ($tvalue as $t_value) {
				foreach ($formats as $fkey => $fvalue) {
					$key_string  = $fvalue['key_string'];
					$name_string = $fvalue['name_string'];
					
					if ($t_value == $key_string) {
						$meeting_formats .= '<tr><td>' . $t_value . '</td><td>' . $fvalue['name_string'] . '</td><td>' . $fvalue['description_string'] . '</td></tr>';
					}
				}
			}
			$meeting_formats .= '</table>';
			return $meeting_formats;
			
		}
		
		function getLocation($value, $format_key) {
			
			global $unique_areas;
			
			$location = $address = '';
			
			if (isset($value['meeting_name'])) {
				$location .= "<div class='meeting-name'>" . $value['meeting_name'] . "</div>";
			} else {
				$value['meeting_name'] = '';
			}
			
			
			if (isset($value['location_text']) && $value['location_text'] != '') {
				$location .= "<div class='location-text'>" . $value['location_text'] . '</div>';
			} else {
				$value['location_text'] = '';
			}
			
			$isaddress = True;
			
			if (isset($value['location_street'])) {
				$address .= $value['location_street'];
			} else {
				$value['location_street'] = '';
				$isaddress                = False;
			}
			
			
			if (isset($value['location_municipality'])) {
				
				if ($address != '' && $value['location_municipality'] != '') {
					$address .= ', ' . $value['location_municipality'];
				} else {
					$address .= $value['location_municipality'];
				}
				
			} else {
				$value['location_municipality'] = '';
				$isaddress                      = False;
			}
			
			
			if (isset($value['location_province'])) {
				
				if ($address != '' && $value['location_province'] != '') {
					$address .= ', ' . $value['location_province'];
				} else {
					$address .= $value['location_province'];
				}
				
			} else {
				$value['location_province'] = '';
				$isaddress                  = False;
			}
			
			
			if (isset($value['location_postal_code_1'])) {
				
				if ($address != '' && $value['location_postal_code_1'] != '') {
					$address .= ', ' . $value['location_postal_code_1'];
				} else {
					$address .= $value['location_postal_code_1'];
				}
				
			} else {
				$value['location_postal_code_1'] = '';
			}
			
			$location .= "<div class='meeting-address'>" . $address . '</div>';
			
			if (isset($value['location_info'])) {
				$location .= "<div class='location-information'>" . preg_replace('/(https?):\/\/([A-Za-z0-9\._\-\/\?=&;%,]+)/i', '<a href="$1://$2" target="_blank">$1://$2</a>', $value['location_info']) . '</i/></div>';
			} else {
				$value['location_info'] = '';
			}
			
			if ($format_key == 'BTW') {
				
				$area = '';
				
				$area = $unique_areas[$value['service_body_bigint']];
				
				if ($area == '') {
					$area = '<br/>(Florida Region)';
				}
				
				$location .= "<div class='meeting-area'>(" . $area . ")</div>";
			}
			
			return $location;
			
		}
		
		/**
		 * @desc BMLT Meeting Count
		 */
		function meeting_count($atts, $content = null) {
			extract(shortcode_atts(array(
				"service_body" => '',
				"root_server" => '',
				"subtract" => '',
				"service_body_parent" => ''
			), $atts));
			
			if ($atts == "") {
				// return;
			}
			
			$root_server = ($root_server != '' ? $root_server : $this->options['root_server']);
			
			if ($service_body_parent == Null && $service_body == Null) {
				$area_data       = explode(',', $this->options['service_body_1']);
				$area            = $area_data[0];
				$service_body_id = $area_data[1];
				$parent_body_id  = $area_data[2];
				
				if ($parent_body_id == '0') {
					$service_body_parent = $service_body_id;
				} else {
					$service_body = $service_body_id;
				}
				
			}
			
			$services = '';
			$subtract = intval($subtract);
			
			if ($service_body_parent != Null && $service_body != Null) {
				Return '<p>BMLT Tabs Error: Cannot use service_body_parent and service_body at the same time.</p>';
			}
			
			$t_services = '';
			
			if ($service_body != Null && $service_body != 'btw') {
				$service_body = array_map('trim', explode(",", $service_body));
				foreach ($service_body as $key) {
					$services .= '&services[]=' . $key;
				}
				
			}
			
			elseif ($service_body_parent != Null && $service_body != 'btw') {
				$service_body = array_map('trim', explode(",", $service_body_parent));
				$services .= '&recursive=1';
				foreach ($service_body as $key) {
					$services .= '&services[]=' . $key;
				}
				
			}
			
			
			if ($service_body == 'btw') {
				$the_query = $root_server . "/client_interface/json/index.php?switcher=GetSearchResults&formats[]=46";
			} else {
				$the_query = $root_server . "/client_interface/json/index.php?switcher=GetSearchResults&formats[]=-47" . $services;
			}
			
			// print_r($the_query);return;
			$transient_key = 'bmlt_tabs_' . md5($the_query);
			
			if (false === ($results = get_transient($transient_key)) || intval($this->options['cache_time']) == 0) {
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $the_query);
				curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_VERBOSE, false);
				curl_setopt($ch, CURLOPT_TIMEOUT, 10);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
				curl_setopt($ch, CURLOPT_SSLVERSION, 3);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
				$results  = curl_exec($ch);
				$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				curl_close($ch);
				
				if ($httpcode != 200 && $httpcode != 302 && $httpcode != 304) {
					return '[connect error]';
				}
				
				;
				
				if (intval($this->options['cache_time']) > 0) {
					set_transient($transient_key, $results, intval($this->options['cache_time']) * HOUR_IN_SECONDS);
				}
				
			}
			
			$results = count(json_decode($results)) - $subtract;
			return $results;
		}
		
		/**
		 * @desc BMLT Group Count
		 */
		function bmlt_group_count($atts, $content = null) {
			extract(shortcode_atts(array(
				"service_body" => '',
				"subtract" => '',
				"root_server" => '',
				"service_body_parent" => ''
			), $atts));
			
			if ($atts == "") {
				// return;
			}
			
			$root_server = ($root_server != '' ? $root_server : $this->options['root_server']);
			
			if ($service_body_parent == Null && $service_body == Null) {
				$area_data       = explode(',', $this->options['service_body_1']);
				$area            = $area_data[0];
				$service_body_id = $area_data[1];
				$parent_body_id  = $area_data[2];
				
				if ($parent_body_id == '0') {
					$service_body_parent = $service_body_id;
				} else {
					$service_body = $service_body_id;
				}
				
			}
			
			$services = '';
			$subtract = intval($subtract);
			
			if ($service_body_parent != Null && $service_body != Null) {
				Return '<p>BMLT Tabs Error: Cannot use service_body_parent and service_body at the same time.</p>';
			}
			
			
			if ($service_body != Null && $service_body != 'btw') {
				$service_body = array_map('trim', explode(",", $service_body));
				foreach ($service_body as $key) {
					$services .= '&services[]=' . $key;
				}
				
			}
			
			
			if ($service_body_parent != Null && $service_body != 'btw') {
				$service_body = array_map('trim', explode(",", $service_body_parent));
				$services .= '&recursive=1';
				foreach ($service_body as $key) {
					$services .= '&services[]=' . $key;
				}
				
			}
			
			
			if ($service_body == 'btw') {
				$the_query = "$root_server/client_interface/json/index.php?switcher=GetSearchResults&data_field_key=meeting_name&formats[]=46" . $services;
			} else {
				$the_query = "$root_server/client_interface/json/index.php?switcher=GetSearchResults&data_field_key=meeting_name&formats[]=-47" . $services;
			}
			
			$transient_key = 'bmlt_tabs_' . md5($the_query);
			
			if (false === ($result = get_transient($transient_key)) || intval($this->options['cache_time']) == 0) {
				// It wasn't there, so regenerate the data and save the transient
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $the_query);
				curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_VERBOSE, false);
				curl_setopt($ch, CURLOPT_TIMEOUT, 10);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
				curl_setopt($ch, CURLOPT_SSLVERSION, 3);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
				$results  = curl_exec($ch);
				$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				curl_close($ch);
				
				if ($httpcode != 200 && $httpcode != 302 && $httpcode != 304) {
					return '[connect error]';
				}
				
				;
				$result = json_decode($results);
				$result = array_map("unserialize", array_unique(array_map("serialize", $result)));
				foreach ($result as $key => $value) {
					
					if (is_array($value)) {
						$result[$key] = super_unique($value);
					}
					
				}
				
				
				if (intval($this->options['cache_time']) > 0) {
					set_transient($transient_key, $result, intval($this->options['cache_time']) * HOUR_IN_SECONDS);
				}
				
			}
			
			return count($result);
		}
		
		/**
		 * @desc Adds the options sub-panel
		 */
		function get_areas($root_server, $source) {
			$transient_key = 'bmlt_tabs_' . md5("$root_server/client_interface/json/?switcher=GetServiceBodies");
			
			if (false === ($result = get_transient($transient_key)) || intval($this->options['cache_time']) == 0) {
				$resource = curl_init();
				curl_setopt($resource, CURLOPT_URL, "$root_server/client_interface/json/?switcher=GetServiceBodies");
				curl_setopt($resource, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($resource, CURLOPT_CONNECTTIMEOUT, 10);
				curl_setopt($resource, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
				$results  = curl_exec($resource);
				$result   = json_decode($results, true);
				$httpcode = curl_getinfo($resource, CURLINFO_HTTP_CODE);
				$c_error  = curl_error($resource);
				$c_errno  = curl_errno($resource);
				curl_close($resource);
				
				if ($results == False) {
					echo '<div style="font-size: 20px;text-align:center;font-weight:normal;color:#F00;margin:0 auto;margin-top: 30px;"><p>Problem Connecting to BMLT Root Server</p><p>' . $root_server . '</p><p>Error: ' . $c_errno . ', ' . $c_error . '</p><p>Please try again later</p></div>';
					return 0;
				}
				
				
				if (intval($this->options['cache_time']) > 0) {
					set_transient($transient_key, $result, intval($this->options['cache_time']) * HOUR_IN_SECONDS);
				}
				
			}
			
			if ($source == 'dropdown') {
				$unique_areas = array();
				foreach ($result as $value) {
					$parent_name = 'None';
					foreach ($result as $parent) {
						if ( $value['parent_id'] == $parent['id'] ) {
							$parent_name = $parent['name'];
						}
					}
					$unique_areas[] = $value['name'] . ',' . $value['id'] . ',' . $value['parent_id'] . ',' . $parent_name;
				}
				
			} else {
				$unique_areas = array();
				foreach ($result as $value) {
					$unique_areas[$value['id']] = $value['name'];
				}
				
			}
			
			return $unique_areas;
		}
		
		
		function admin_menu_link() {
			// If you change this from add_options_page, MAKE SURE you change the filter_plugin_actions function (below) to
			// reflect the page file name (i.e. - options-general.php) of the page your plugin is under!
			add_options_page('BMLT Tabs', 'BMLT Tabs', 'activate_plugins', basename(__FILE__), array(
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
		function admin_options_page() {
			
			if (!isset($_POST['bmlttabssave'])) {
				$_POST['bmlttabssave'] = false;
			}
			
			
			if (!isset($_POST['delete_cache_action'])) {
				$_POST['delete_cache_action'] = false;
			}
			
			
			if ($_POST['bmlttabssave']) {
				
				if (!wp_verify_nonce($_POST['_wpnonce'], 'bmlttabsupdate-options'))
					die('Whoops! There was a problem with the data you posted. Please go back and try again.');
				$this->options['cache_time']     = $_POST['cache_time'];
				$this->options['root_server']    = $_POST['root_server'];
				$this->options['service_body_1'] = $_POST['service_body_1'];
				$this->save_admin_options();
				set_transient('admin_notice', 'Please put down your weapon. You have 20 seconds to comply.');
				echo '<div class="updated"><p>Success! Your changes were successfully saved!</p></div>';
				
				if (intval($this->options['cache_time']) == 0) {
					$num = $this->delete_transient_cache();
					
					if ($num > 0) {
						echo "<div class='updated'><p>Success! BMLT Cache Deleted! ($num entries found and deleted)</p></div>";
					}
					
				} else {
					echo "<div class='updated'><p>Note: consider Deleting Cache (unless you know what you're doing)</p></div>";
				}
				
			}
			
			
			if ($_POST['delete_cache_action']) {
				
				if (!wp_verify_nonce($_POST['_wpnonce'], 'delete_cache_nonce'))
					die('Whoops! There was a problem with the data you posted. Please go back and try again.');
				$num = $this->delete_transient_cache();
				set_transient('admin_notice', 'Please put down your weapon. You have 20 seconds to comply.');
				
				if ($num > 0) {
					echo "<div class='updated'><p>Success! BMLT Cache Deleted! ($num entries found and deleted)</p></div>";
				} else {
					echo "<div class='updated'><p>Success! BMLT Cache - Nothing Deleted! ($num entries found)</p></div>";
				}
				
			}
			
?>
			<div class="wrap">
				<h2>BMLT Tabs</h2>
				<form style="display:inline!important;" method="POST" id="bmlt_tabs_options" name="bmlt_tabs_options">
					<?php wp_nonce_field('bmlttabsupdate-options'); ?>
					<?php $this_connected = $this->testRootServer($this->options['root_server']); ?>
					<?php $connect = "<p><div style='color: #f00;font-size: 16px;vertical-align: text-top;' class='dashicons dashicons-no'></div><span style='color: #f00;'>Connection to Root Server Failed.  Check spelling or try again.  If you are certain spelling is correct, Root Server could be down.</span></p>"; ?>
					<?php if ( $this_connected != False) { ?>
						<?php $connect = "<span style='color: #00AD00;'><div style='font-size: 16px;vertical-align: text-top;' class='dashicons dashicons-smiley'></div>Version ".$this_connected."</span>"?>
						<?php $this_connected = true; ?>
					<?php } ?>
					<div style="margin-top: 20px; padding: 0 15px;" class="postbox">
						<h3>BMLT Root Server URL</h3>
						<p>Example: http://naflorida.org/bmlt_server</p>
						<ul>
							<li>
								<label for="root_server">Default Root Server: </label>
								<input id="root_server" type="text" size="40" name="root_server" value="<?php echo $this->options['root_server']; ?>" /> <?php echo $connect; ?>
							</li>
						</ul>
					</div>
					<div style="padding: 0 15px;" class="postbox">
						<h3>Service Body</h3>
						<p>This service body will be used when no service body is defined in the shortcode.</p>
						<ul>
							<li>
								<label for="service_body_1">Default Service Body: </label>
								<select style="display:inline;" onchange="getValueSelected()" id="service_body_1" name="service_body_1">
								<?php if ($this_connected) { ?>
									<?php $unique_areas = $this->get_areas($this->options['root_server'], 'dropdown'); ?>
									<?php asort($unique_areas); ?>
									<?php foreach ($unique_areas as $key => $unique_area) { ?>
										<?php $area_data = explode(',', $unique_area); ?>
										<?php $area_name = $area_data[0]; ?>
										<?php $area_id = $area_data[1]; ?>
										<?php $area_parent = $area_data[2]; ?>
										<?php $area_parent_name = $area_data[3]; ?>
										<?php if ($unique_area == $this->options['service_body_1']) { ?>
											<option selected="selected" value="<?php echo $unique_area; ?>"><?php echo $area_name; ?></option>
										<?php } else { ?>
											<option value="<?php echo $unique_area; ?>"><?php echo $area_name; ?></option>
										<?php } ?>
									<?php } ?>
								<?php } else { ?>
									<option selected="selected" value="<?php echo $this->options['service_body_1']; ?>"><?php echo 'Not Connected - Can not get Service Bodies'; ?></option>
								<?php } ?>
								</select>							
								<div style="display:inline; margin-left:15px;" id="txtSelectedValues1"></div>
								<p id="txtSelectedValues2"></p>
							</li> 
						</ul>
					</div>
					<div style="padding: 0 15px;" class="postbox">
						<h3>Meeting Cache (<?php echo $this->count_transient_cache(); ?> Cached Entries)</h3>
						<?php global $_wp_using_ext_object_cache; ?>
						<?php if ($_wp_using_ext_object_cache) { ?>
							<p>This site is using an external object cache.</p>
						<?php } ?>
						<p>Meeting data is cached (as database transient) to load BMLT Tabs faster.</p>
						<ul>
							<li>
								<label for="cache_time">Cache Time: </label>
								<input id="cache_time" onKeyPress="return numbersonly(this, event)" type="number" min="0" max="999" size="3" maxlength="3" name="cache_time" value="<?php echo $this->options['cache_time']; ?>" />&nbsp;&nbsp;<em>0 - 999 Hours (0 = disable and delete cache)</em>&nbsp;&nbsp;
							</li>
						</ul>
						<p><em>The DELETE CACHE button is useful for the following:
						<ol>
						<li>After updating meetings in BMLT.</li>
						<li>Meeting information is not correct on the website.</li>
						<li>Changing the Cache Time value.</li>
						</ol>
						</em>
						</p>
					</div>
					<input type="submit" value="SAVE CHANGES" name="bmlttabssave" class="button-primary" />					
				</form>
				<form style="display:inline!important;" method="post">
					<?php wp_nonce_field('delete_cache_nonce'); ?>
					<input style="color: #000;" type="submit" value="DELETE CACHE" name="delete_cache_action" class="button-primary" />					
				</form>
				<br/><br/>
				<div style="padding: 0 15px;" class="postbox">
					<h3>BMLT Tabs Shortcode Usage</h3>
					<p>Insert the following shortcodes into a page.</p>
					<p><strong>[bmlt_tabs]</strong></p>
					<p><strong>[meeting_count]</strong></p>
					<p><strong>[group_count]</strong></p>
					<p><strong>Example: We now have [group_count] groups with [meeting_count] per week.</strong></p>
					<p><em>Detailed instructions for each shortcode are provided as follows.</em></p>
				</div>
				<div style="padding: 0 15px;" class="postbox">
					<h3>Service Body Parameter</h3>
					<p>For all shortcodes the service_body parameter is optional.</p>
					<p>When no service_body is specified the default service body will be used.</p>
					<p><strong>[bmlt_tabs service_body="2,3,4"]</strong></p>
					<p>service_body = one or more BMLT service body IDs.</p>
					<p>Using multiple IDs will combine meetings from each service body into the BMLT Tabs interface.</p>
					<p><strong>[bmlt_tabs service_body_parent="1,2,3"]</strong></p>
					<p>service_body_parent = one or more BMLT parent service body IDs.</p>
					<p>An example parent service body is a Region.  This would be useful to get all meetings from a specific Region.</p>
					<p>You can find the service body ID (with shortcode) next to the Default Service Body dropdown above.</p>
					<p><em>You cannot combine the service_body and parent_service_body parameters.</em></p>
				</div>
				<div style="padding: 0 15px;" class="postbox">
					<h3>Root Server</h3>
					<p>Use a different Root Server.</p>
					<p><strong>[bmlt_tabs service_body="2" root_server="http://naflorida.org/bmlt_server"]</strong></p>
					<p>Useful for displaying meetings from a different root server.</p>
					<em><p>Hint: To find service body IDs enter the different root server into the "BMLT Root Server URL" box and save.</p>
					<p>Remember to enter your current Root Server back into the "BMLT Root Server URL".</p></em>
				</div>
				<div style="padding: 0 15px;" class="postbox">
					<h3>View By City or Weekday</h3>
					<p>With this parameter you can initially view meetings by City or Weekday.</p>
					<p><strong>[bmlt_tabs view_by="city|weekday"]</strong></p>
					<p>city = view meetings by City</p>
					<p>weekday = view meetings by Weekdays (default)</p>
				</div>
				<div style="padding: 0 15px;" class="postbox">
					<h3>Exclude City Button</h3>
					<p>With this parameter you can exclude the City button.</p>
					<p><strong>[bmlt_tabs include_city_button="0|1"]</strong></p>
					<p>0 = exclude City button</p>
					<p>1 = include City button (default)</p>
					<p><em>City button will be included when view_by = "city" (include_city_button will be set to "1").</em></p>
				</div>
				<div style="padding: 0 15px;" class="postbox">
					<h3>Exclude Weekday Button</h3>
					<p>With this parameter you can exclude the Weekday button.</p>
					<p><strong>[bmlt_tabs include_weekday_button="0|1"]</strong></p>
					<p>0 = exclude Weekday button</p>
					<p>1 = include Weekday button (default)</p>
					<p><em>Weekday button will be included when view_by = "weekday" (include_weekday_button will be set to "1").</em></p>
				</div>
				<div style="padding: 0 15px;" class="postbox">
					<h3>Tabs or No Tabs</h3>
					<p>With this parameter you can display meetings without weekday tabs.</p>
					<p><strong>[bmlt_tabs service_body="2" has_tabs="0|1"]</strong></p>
					<p>0 = display meetings without tabs</p>
					<p>1 = display meetings with tabs (default)</p>
					<p><em>Hiding weekday tabs is useful for smaller service bodies.</em></p>
				</div>
				<div style="padding: 0 15px;" class="postbox">
					<h3>Header or No Header</h3>
					<p>The header will show dropdowns.</p>
					<p><strong>[bmlt_tabs service_body="2" header="0|1"]</strong></p>
					<p>0 = do not display the header</p>
					<p>1 = display the header (default)</p>
				</div>
				<div style="padding: 0 15px;" class="postbox">
					<h3>Dropdowns</h3>
					<p>With this parameter you can show or hide the dropdowns.</p>
					<p><strong>[bmlt_tabs service_body="2" has_cities='0|1' has_groups='0|1' has_locations='0|1' has_zip_codes='0|1' has_formats='0|1']</strong></p>
					<p>0 = hide dropdown<p>
					<p>1 = show dropdown (default)<p>
				</div>
				<div style="padding: 0 15px;" class="postbox">	
					<h3>Dropdown Width</h3>
					<p>With this parameter you can change the width of the dropdowns.</p>
					<p><strong>[bmlt_tabs service_body="2" dropdown_width="auto|130px|20%"]</strong></p>
					<p>auto = width will be calculated automatically (default)</p>
					<p>130px = width will be calculated in pixels</p>
					<p>20%" = width will be calculated as a percent of the container width</p>
				</div>
				<div style="padding: 0 15px;" class="postbox">
					<h3>BMLT Count</h3>
					<p>[bmlt_count] depreciated. Replaced with Meeting Count. See below. Will continue to work.</p>
				</div>
				<div style="padding: 0 15px;" class="postbox">
					<h3>Meeting Count</h3>
					<p>Will return the number of meetings for one or more BMLT service bodies.</p>
					<p><strong>[meeting_count]</strong> <em>Will use the default service body (above).</em></p>					
					<p><strong>[meeting_count service_body="2,3,4"]</strong></p>
					<p><strong>[meeting_count service_body_parent="1,2,3"]</strong></p>
					<p>Will return the number of meetings in one or more BMLT parent service bodies.</p>
					<p><strong>[meeting_count service_body="2" subtract="3"]</strong></p>
					<p>subtract = number of meetings to subtract from total meetings (optional)</p>
					<p><em>Subtract is useful when you are using BMLT for subcommittee meetings and do want to count those meetings.</em></p>
				</div>
				<div style="padding: 0 15px;" class="postbox">
					<h3>Group Count</h3>
					<p>Will return the number of Groups for one or more BMLT service bodies.</p>
					<p><strong>[group_count]</strong> <em>Will use the default service body (above).</em></p>					
					<p><strong>[group_count service_body="2,3,4"]</strong></p>
					<p><strong>[group_count service_body_parent="1,2,3"]</strong></p>
					<p>Will return the number of Groups in one or more BMLT parent service bodies.</p>
				</div>
			</div>
			<script>
			getValueSelected();
			</script>
			<?php
		}
		
		/**
		 * Deletes transient cache
		 */
		function delete_transient_cache() {
			global $wpdb, $_wp_using_ext_object_cache;
			;
			wp_cache_flush();
			$num1 = $wpdb->query($wpdb->prepare("DELETE FROM $wpdb->options WHERE option_name LIKE %s ", '_transient_bmlt_tabs_%'));
			$num2 = $wpdb->query($wpdb->prepare("DELETE FROM $wpdb->options WHERE option_name LIKE %s ", '_transient_timeout_bmlt_tabs_%'));
			wp_cache_flush();
			return $num1 + $num2;
		}
		
		/**
		 * count transient cache
		 */
		function count_transient_cache() {
			global $wpdb, $_wp_using_ext_object_cache;
			wp_cache_flush();
			$num1 = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $wpdb->options WHERE option_name LIKE %s ", '_transient_bmlt_tabs_%'));
			$num2 = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $wpdb->options WHERE option_name LIKE %s ", '_transient_timeout_bmlt_tabs_%'));
			wp_cache_flush();
			return $num1;
		}
		
		/**
		 * @desc Adds the Settings link to the plugin activate/deactivate page
		 */
		function filter_plugin_actions($links, $file) {
			// If your plugin is under a different top-level menu than Settings (IE - you changed the function above to something other than add_options_page)
			// Then you're going to want to change options-general.php below to the name of your top-level page
			$settings_link = '<a href="options-general.php?page=' . basename(__FILE__) . '">' . __('Settings') . '</a>';
			array_unshift($links, $settings_link);
			// before other links
			return $links;
		}
		
		/**
		 * Retrieves the plugin options from the database.
		 * @return array
		 */
		function getOptions() {
			// Don't forget to set up the default options
			
			if (!$theOptions = get_option($this->optionsName)) {
				$theOptions = array(
					'cache_time' => '3600',
					'root_server' => '',
					'service_body_1' => ''
				);
				update_option($this->optionsName, $theOptions);
			}
			
			$this->options = $theOptions;
		}
		
		/**
		 * Saves the admin options to the database.
		 */
		function save_admin_options() {
			update_option($this->optionsName, $this->options);
			return;
		}
		
		function geo() {
		
			$geo = 'http://maps.google.com/maps/api/geocode/xml?latlng='.htmlentities(htmlspecialchars(strip_tags($_GET['latlng']))).'&sensor=true';
			$xml = simplexml_load_file($geo);

			foreach($xml->result->address_component as $component){
				if($component->type=='street_address'){
					$geodata['precise_address'] = $component->long_name;
				}
				if($component->type=='natural_feature'){
					$geodata['natural_feature'] = $component->long_name;
				}
				if($component->type=='airport'){
					$geodata['airport'] = $component->long_name;
				}
				if($component->type=='park'){
					$geodata['park'] = $component->long_name;
				}
				if($component->type=='point_of_interest'){
					$geodata['point_of_interest'] = $component->long_name;
				}
				if($component->type=='premise'){
					$geodata['named_location'] = $component->long_name;
				}
				if($component->type=='street_number'){
					$geodata['house_number'] = $component->long_name;
				}
				if($component->type=='route'){
					$geodata['street'] = $component->long_name;
				}
				if($component->type=='locality'){
					$geodata['town_city'] = $component->long_name;
				}
				if($component->type=='administrative_area_level_3'){
					$geodata['district_region'] = $component->long_name;
				}
				if($component->type=='neighborhood'){
					$geodata['neighborhood'] = $component->long_name;
				}
				if($component->type=='colloquial_area'){
					$geodata['locally_known_as'] = $component->long_name;
				}
				if($component->type=='administrative_area_level_2'){
					$geodata['county_state'] = $component->long_name;
				}
				if($component->type=='postal_code'){
					$geodata['postcode'] = $component->long_name;
				}
				if($component->type=='country'){
					$geodata['country'] = $component->long_name;
				}
			}

			list($lat,$long) = explode(',',htmlentities(htmlspecialchars(strip_tags($_GET['latlng']))));
			$geodata['latitude'] = $lat;
			$geodata['longitude'] = $long;
			$geodata['formatted_address'] = $xml->result->formatted_address;
			$geodata['accuracy'] = htmlentities(htmlspecialchars(strip_tags($_GET['accuracy'])));
			$geodata['altitude'] = htmlentities(htmlspecialchars(strip_tags($_GET['altitude'])));
			$geodata['altitude_accuracy'] = htmlentities(htmlspecialchars(strip_tags($_GET['altitude_accuracy'])));
			$geodata['directional_heading'] = htmlentities(htmlspecialchars(strip_tags($_GET['heading'])));
			$geodata['speed'] = htmlentities(htmlspecialchars(strip_tags($_GET['speed'])));
			$geodata['google_api_src'] = $geo;
			$data = '<img src="http://maps.google.com/maps/api/staticmap?center='.$lat.','.$long.'&zoom=16&size=150x150&maptype=roadmap&&sensor=true" width="150" height="150" alt="'.$geodata['formatted_address'].'" \/><br /><br />';
			$data .= 'Latitude: '.$lat.' Longitude: '.$long.'<br />';
			foreach($geodata as $name => $value){
				$data .= ''.$name.': '.str_replace('&','&amp;',$value).'<br />';
			}
			
			return $data;
			
		}
		
	}
	
	//End Class BMLTTabs
}

// end if
// instantiate the class

if (class_exists("BMLTTabs")) {
	$BMLTTabs_instance = new BMLTTabs();
}
?>