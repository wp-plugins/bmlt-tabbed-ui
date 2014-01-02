<?php
	/**
	Plugin Name: BMLT Tabbed UI
	Plugin URI: http://wordpress.org/extend/plugins/bmlt-tabbed-ui/
	Description: Adds a jQuery Tabbed UI for BMLT.
	Author: Jack S Florida Region
	Version: 4.8.6
	*/
	/* Disallow direct access to the plugin file */
	if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
		//die('Sorry, but you cannot access this page directly.');
	}
	if (!class_exists("BMLTTabs")) {
		class BMLTTabs
		{
			/**
			 * @var string The plugin version
			 */
			var $version = '4.8.6';
	
			/**
			 * @var string The options string name for this plugin
			 */
			var $optionsName = 'bmlt_tabs_options';
	
			var $options = array();
	
			function __construct()
			{

				$this->getOptions();
				
				if (is_admin()) {
					// Back end
					//Initialize the options
					add_action("admin_notices", array(
						&$this,
						"is_root_server_missing"
					));
					add_action("wp_head", array(
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

			function is_root_server_missing() {
				$root_server = $this->options['root_server'];
				if ( $root_server == '' ) {
					echo '<div id="message" class="error"><p>Missing BMLT Root Server in settings for BMLT Tabs.</p>';
					$url = admin_url( 'options-general.php?page=bmlt-tabbed-ui.php' );
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
			function enqueue_backend_files()
			{
				wp_enqueue_style("bmlttabsfrontend-css", plugin_dir_url(__FILE__) . "css/bmlt_tabs.css", false, null, false);
			}
			/**
			* @desc Adds JS/CSS to the header
			*/
			function enqueue_frontend_files()
			{
				wp_enqueue_style("bmlt-tabs-jqueryui-css", plugin_dir_url(__FILE__) . "css/jquery-ui.custom.min.css", false, null, false);
				wp_enqueue_style("bmlt-tabs-select2-css", plugin_dir_url(__FILE__) . "css/select2.css", false, null, false);
				wp_enqueue_style("bmlt-tabs-css", plugin_dir_url(__FILE__) . "css/bmlt_tabs.css", false, null, false);
				wp_enqueue_script("bmlt-tabls-jqueryui-js", plugin_dir_url(__FILE__) . "js/jqueryui.min.js", array('jquery'), null, false);
				wp_enqueue_script("bmlt-tabs-select2-js", plugin_dir_url(__FILE__) . "js/select2.min.js", array('jquery'), null, false);
				wp_enqueue_script("bmlt-tabs-js", plugin_dir_url(__FILE__) . "js/bmlt_tabs.js", array('jquery'), null, false);
			}
			/**
			* @desc BMLT Tabs Create shortcode
			*/

			/**
			 * Create Unique Arrays using an md5 hash
			 *
			 * @param array $array
			 * @return array
			 */
			function arrayUnique($array, $preserveKeys = true)
			{
				// Unique Array for return
				$arrayRewrite = array();
				// Array with the md5 hashes
				$arrayHashes = array();
				foreach($array as $key => $item) {
					// Serialize the current element and create a md5 hash
					$hash = md5(serialize($item));
					// If the md5 didn't come up yet, add the element to
					// to arrayRewrite, otherwise drop it
					if (!isset($arrayHashes[$hash])) {
						// Save the current element hash
						$arrayHashes[$hash] = $hash;
						// Add element to the unique Array
						if ($preserveKeys) {
							$arrayRewrite[$key] = $item;
						} else {
							$arrayRewrite[] = $item;
						}
					}
				}
				return $arrayRewrite;
			}

			function sortBySubkey(&$array, $subkey, $sortType = SORT_ASC) {
				foreach ($array as $subarray) {
					$keys[] = $subarray[$subkey];
				}
				array_multisort($keys, $sortType, $array);
			}
  
			function tabbed_ui($atts, $content = null)
			{
				global $template, $unique_areas;				extract(shortcode_atts(array(
					"service_body" => '',
					"service_body_parent" => '',
					"has_tabs" => '',
					"has_formats" => '',
					"template" => '',
					"dropdown_width" => '',
					"has_zip_codes" => '',
					"header" => ''
				), $atts));
				$root_server = $this->options['root_server'];
				if ( $root_server == '' ) {
					Return '<p><b>BMLT Tabs Error: Root Server missing.<br/><br/>Please go to Settings -> BMLT_Tabs and verify Root Server</b></p>';
				}
				$output   = '';
				$output .= '<script type="text/javascript">';
				$output .= 'jQuery( "body" ).addClass( "bmlt-tabs");';
				$output .= '</script>';	
				$services = '';
				if ( $template == '' ) {
					$template = '1';
				}
				if ( $dropdown_width == '' ) {
					$dropdown_width = 'auto';
				}
				if ( $has_zip_codes == '' ) {
					$has_zip_codes = '1';
				}
				if ( $has_formats == '' ) {
					$has_formats = '1';
				}
				if ( $has_tabs != '0' ) {
					$has_tabs = '1';
				}
				if ( $header != '0' ) {
					$header = '1';
				}
				if ($template != '1' && $template != '2' && $template != '3' && $template != 'btw' && $template != 'voting') {
					Return '<p>BMLT Tabs Error: Template must = 1 or 2 or 3.</p>';
				}
				if ($service_body_parent != Null && $service_body != Null) {
					Return '<p>BMLT Tabs Error: Cannot use service_body_parent and service_body at the same time.</p>';
				}
				if ($service_body == '' && $service_body_parent == '') {
					Return '<p>BMLT Tabs Error: Service body missing from shortcode.</p>';
				}
				if ($service_body != Null) {
					$service_body = array_map('trim', explode(",", $service_body));
					foreach ($service_body as $key) {
						if ($template == 'btw') {
							$services .= '&services[]=' . $key . '&formats[]=46';
						} else {
							$services .= '&services[]=' . $key;
						}
					}
				}
				if ($service_body_parent != Null) {
					$service_body = array_map('trim', explode(",", $service_body_parent));
					foreach ($service_body as $key) {
						if ($template == 'btw') {
							$services .= '&recursive=1&services[]=' . $key . '&formats[]=46';
						} else {
							$services .= '&recursive=1&services[]=' . $key;
						}
					}
				}
						
				$timeout = 10; // set to zero for no timeout
				$ch      = curl_init();
				curl_setopt($ch, CURLOPT_URL, "$root_server/client_interface/json/?switcher=GetFormats");
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
				curl_setopt ( $ch, CURLOPT_HEADER, false );
				curl_setopt ( $ch, CURLOPT_MAXREDIRS, 3 );
				curl_setopt ( $ch, CURLOPT_ENCODING, 'gzip,deflate' );
				$results = curl_exec($ch);
				$c_error = curl_error ($ch);
				$c_errno = curl_errno ($ch);

				if ( $results == False ) {
					echo "<p><b>BMLT Server Error: ".$c_errno.", ".$c_error."<br/>Please try again later</b></p>";
					return '';
				}
				curl_close($ch);
				
				if ( $template == 'voting' ) {

					$ch      = curl_init();
					//curl_setopt($ch, CURLOPT_URL, "$root_server/client_interface/json/index.php?switcher=GetSearchResults&sort_key=meeting_name&data_field_key=meeting_name,location_street,location_municipality" . $services);
					curl_setopt($ch, CURLOPT_URL, "$root_server/client_interface/json/index.php?switcher=GetSearchResults&sort_key=meeting_name&data_field_key=meeting_name,worldid_mixed&formats[]=-47" . $services);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
					$unique_group = curl_exec($ch);
					curl_close($ch);
					$unique_group = json_decode($unique_group,true);
					$unique_group = $this->arrayUnique($unique_group);
					$this->sortBySubkey($unique_group, 'meeting_name');
				}

				if ( $template == '3' ) {

					$ch      = curl_init();
					curl_setopt($ch, CURLOPT_URL, "$root_server/client_interface/json/index.php?switcher=GetSearchResults&sort_key=town&data_field_key=location_municipality" . $services);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
					$unique_city = curl_exec($ch);
					curl_close($ch);
					$unique_city = json_decode($unique_city, true);

					$ch      = curl_init();
					curl_setopt($ch, CURLOPT_URL, "$root_server/client_interface/json/?switcher=GetSearchResults$services&sort_key=town");
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
					curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
					$city = curl_exec($ch);
					curl_close($ch);
					$city = json_decode($city,true);
				}
				
				if ( $template == 'btw' ) {
					$resource = curl_init();
					curl_setopt ( $resource, CURLOPT_URL, "$root_server/client_interface/xml/GetServiceBodies.php" );
					curl_setopt ( $resource, CURLOPT_RETURNTRANSFER, true );
					curl_setopt ( $resource, CURLOPT_HEADER, false );
					curl_setopt ( $resource, CURLOPT_MAXREDIRS, 3 );
					curl_setopt ( $resource, CURLOPT_CONNECTTIMEOUT, 10 );
					curl_setopt ( $resource, CURLOPT_ENCODING, 'gzip,deflate' );
					curl_exec ( $resource );
					$content = false;
					$content = curl_multi_getcontent ( $resource );
					$http_status = curl_getinfo ($resource, CURLINFO_HTTP_CODE );
					curl_close ( $resource );
					
					$info_file = new DOMDocument;
					@$info_file->loadXML ( $content );
					$has_info = $info_file->getElementsByTagName ( "serviceBodies" );
					$sb_node = $has_info->item(0);

					$tmp_array = explode('=',$services);
					$num = $tmp_array[1];

					$unique_areas = array();
					
					foreach ( $sb_node->childNodes as $node ) {
						$id = $node->getAttribute('id');
						$name = $node->getAttribute('sb_name');
						foreach ( $node->childNodes as $sb_node1 ) {
							//$unique_areas['id'][] = $sb_node1->getAttribute('id');
							//$unique_areas['area'][] = $sb_node1->getAttribute('sb_name');
							$unique_areas[$sb_node1->getAttribute('id')]=$sb_node1->getAttribute('sb_name');
							if ( $num == $sb_node1->getAttribute('id') ) {
								$this_area = $sb_node1->getAttribute('sb_name');
							}
						}
					}
				}
				
				if ( $template != 'voting') {
					$root_server_services = 'ff_'.$root_server;
					if ( false === ( $format = get_transient( $root_server_services ) ) ) {
						// It wasn't there, so regenerate the data and save the transient
						$ch      = curl_init();
						curl_setopt($ch, CURLOPT_URL, "$root_server/client_interface/json/?switcher=GetFormats");
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
						curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
						$formats = curl_exec($ch);
						curl_close($ch);
						$format = json_decode($formats,true);
						set_transient( $root_server_services, $format, 60*60*1 );
					}

					$format_table = '<table class="bmlt-table">';
					if ( $template == '2' ) {
						$format_table .= "<tr><td colspan='3' style='font-size:16px !important; color:#0066cc'><strong>MEETING FORMATS</strong></td></tr>";
					}
					asort($format);
					foreach ($format as $key => $value) {
						$format_table .= '<tr>';
						$format_table .= "<td>$value[key_string]</td>";
						$format_table .= "<td>$value[name_string]</td>";
						$format_table .= "<td>$value[description_string]</td>";
						$format_table .= "</tr>";
					}
					$format_table .= "</table>";
					
					$format_table = preg_replace('/(https?):\/\/([A-Za-z0-9\._\-\/\?=&;%,]+)/i', '<a href="$1://$2" target="_blank">$1://$2</a>', $format_table);
				}

				if ( $template == '1' || $template == '2' || $template == 'btw' ) {
					$root_server_services = 't_'.$root_server.''.$services;
					if ( false === ( $result = get_transient( $root_server_services ) ) ) {
							// It wasn't there, so regenerate the data and save the transient
						$ch      = curl_init();
						curl_setopt($ch, CURLOPT_URL, "$root_server/client_interface/json/?switcher=GetSearchResults$services&sort_key=time");
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
						curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
						$results = curl_exec($ch);
						$c_error = curl_error ($ch);
						if ( $c_error == "couldn't connect to host" ) {
							echo "<p><b>Could not connect to the BMLT server.  Please check back later.</b></p>";
							return '';
						}
						curl_close($ch);
						$result = json_decode($results,true);
						set_transient( $root_server_services, $result, 60*60*1 );
					}
					$unique_formats = array();
					foreach ($result as $key => $value) {
						$tvalue = explode(',',$value[formats]);
						foreach ($tvalue as $t_value) {
							$unique_formats[] = $t_value;
						}
					}
					$unique_zips = array();
					$unique_cities = array();
					$unique_groups = array();
					$unique_locations = array();
					foreach ($result as $key => $value) {
						$unique_cities[] = $value[location_municipality];
						if ( $value[location_street] ) {
							$unique_groups[] = $value[meeting_name];
							$unique_locations[] = $value[location_text];
						}
						if ( $value[location_postal_code_1] ) {
							$unique_zips[] = $value[location_postal_code_1];
						}
					}
					
					$unique_zip = array_unique($unique_zips);
					$unique_city = array_unique($unique_cities);
					$unique_group = array_unique($unique_groups);
					$unique_location = array_unique($unique_locations);
					$unique_format = array_unique($unique_formats);
					
					$unique_zip = array_filter( $unique_zip );
					$unique_city = array_filter( $unique_city );
					$unique_group = array_filter( $unique_group );
					$unique_location = array_filter( $unique_location );
					$unique_format = array_filter( $unique_format );
					
					$unique_zip = array_slice($unique_zip, 0);
					$unique_city = array_slice($unique_city, 0);
					$unique_group = array_slice($unique_group, 0);
					$unique_location = array_slice($unique_location, 0);
					$unique_format = array_slice($unique_format, 0);
					
					asort($unique_zip);
					asort($unique_city);
					$number_cities = count($unique_city);
					$number_locations = count($unique_location);
					asort($unique_group);
					asort($unique_location);
					asort($unique_format);

					$unique_format_name_string = array();
					foreach ($format as $key => $value) {
						$key_string = $value[key_string];
						$name_string = $value[name_string];
						foreach ($unique_format as $value1) {
							if ($value1 == $key_string) {
								$unique_format_name_string[] = $name_string;
							}								
						}
					}
				}

				if ( $template == '3' ) {
					$unique_cities = array();
					foreach ($city as $key => $value) {
						$unique_cities[] = $value[location_municipality];
					}
						$unique_city = array_unique($unique_cities);
					If ( $header == '1' ) {
						$tmp_array = explode('=',$services);
						$num = $tmp_array[1];
						$number_meetings = do_shortcode("[bmlt_count service_body='$num']");
						$number_groups = do_shortcode("[group_count service_body='$num']");
						$cities .= '<table class="bmlt_simple_meetings_table cities" cellpadding="0" cellspacing="0" summary="Meetings">';
						$cities .= "<thead>";
						$cities .= "<tr><th colspan='4'><span class='meetings_per'>$this_area Meetings by City</span><br/><span class='we_now_have'>We now have $number_groups Home Groups with $number_meetings Meetings per Week</span></th></tr>";
						$cities .= "</thead>";
						$cities .= "</table><br/>";
					}
					foreach ($unique_city as $city_value) {
						$cities .= '<table class="bmlt_simple_meetings_table cities" cellpadding="0" cellspacing="0" summary="Meetings">';
						$cities .= "<thead>";
						$cities .= "<tr><th colspan='4'>$city_value</th></tr>";
						$cities .= "</thead>";
						foreach ($city as $key => $value) {
							if ( $city_value == $value[location_municipality] && $value[location_street] ) {
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
									$location_info = '<i>'.preg_replace('/(https?):\/\/([A-Za-z0-9\._\-\/\?=&;%,]+)/i', '<a href="$1://$2" target="_blank">$1://$2</a>', $location_info).'</i/>';
								} else { 
									$location_info = '';
								};
								if ($value[comments]) {
									$value[comments] = '<br/>' . $value[comments];
									$value[comments] = preg_replace('/(https?):\/\/([A-Za-z0-9\._\-\/\?=&;%,]+)/i', '<a href="$1://$2" target="_blank">$1://$2</a>', $value[comments]);
								};
								if ( $value[weekday_tinyint] == 1 ) {
									$today = 'Sunday';
								}
								if ( $value[weekday_tinyint] == 2 ) {
									$today = 'Monday';
								}
								if ( $value[weekday_tinyint] == 3 ) {
									$today = 'Tuesday';
								}
								if ( $value[weekday_tinyint] == 4 ) {
									$today = 'Wednesday';
								}
								if ( $value[weekday_tinyint] == 5 ) {
									$today = 'Thursday';
								}
								if ( $value[weekday_tinyint] == 6 ) {
									$today = 'Friday';
								}
								if ( $value[weekday_tinyint] == 7 ) {
									$today = 'Saturday';
								}
								$location = "<b>$value[meeting_name]</b><br/>$location_text$value[location_street] $value[location_municipality], $value[location_province] $value[location_postal_code_1]$location_info";
								$map = "<a target='_blank' href='http://maps.google.com/maps?q=$value[latitude],$value[longitude]+($value[meeting_name])&ll=$value[latitude],$value[longitude]'>Map and Directions</a>";
								$cities .= "<tr class='123'>";
								$cities .= "<td class='bmlt_simple_meeting_one_meeting_time_t2_td'><b>$today<br/>$value[start_time]</b><br/>$value[formats]$value[comments]</td>";
								$cities .= "<td class='bmlt-column2'>$location</td>";
								$cities .= "<td class='bmlt_simple_meeting_one_meeting_map_t2_td'>$map</td>";
								$cities .= "</tr>";
							}
						}
						$cities .= "</table></br>";
					}
				}
				if ( $template == '2' ) {
					If ( $header == '1' ) {
						$tmp_array = explode('=',$services);
						$num = $tmp_array[1];
						$number_meetings = do_shortcode("[bmlt_count service_body='$num']");
						$number_groups = do_shortcode("[group_count service_body='$num']");
						$table .= '<table class="bmlt_simple_meetings_table cities" cellpadding="0" cellspacing="0" summary="Meetings">';
						$table .= "<thead>";
						$table .= "<tr><th colspan='4'><span class='meetings_per'>$this_area Meetings</span><br/><span class='we_now_have'>We now have $number_groups Home Groups with $number_meetings Meetings per Week</span</th></tr>";
						$table .= "</thead>";
						$table .= "</table><br/>";
					}
					$table .= '<table class="bmlt_simple_meetings_table" cellpadding="0" cellspacing="0" summary="Meetings">';
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
							$location_info = '<i>'.preg_replace('/(https?):\/\/([A-Za-z0-9\._\-\/\?=&;%,]+)/i', '<a href="$1://$2" target="_blank">$1://$2</a>', $location_info).'</i/>';
						} else { 
							$location_info = '';
						};
						if ($value[comments]) {
							$value[comments] = '<br/>' . $value[comments];
							$value[comments] = preg_replace('/(https?):\/\/([A-Za-z0-9\._\-\/\?=&;%,]+)/i', '<a href="$1://$2" target="_blank">$1://$2</a>', $value[comments]);
						};
						$location = "<b>$value[meeting_name]</b><br/>$location_text$value[location_street] $value[location_municipality], $value[location_province] $value[location_postal_code_1]$location_info";
						$map = "<a target='_blank' href='http://maps.google.com/maps?q=$value[latitude],$value[longitude]+($value[meeting_name])&ll=$value[latitude],$value[longitude]'>Map and Directions</a>";
						if ( $value[weekday_tinyint] == 1 && $value[location_street] ) {
							$sunday .= "<tr class='bmlt_simple_meeting_one_meeting_tr'>";
							$sunday .= "<td class='bmlt_simple_meeting_one_meeting_time_t2_td'><b>$value[start_time]</b><br/><a class='show-popup bmlt-button' href='#'>$value[formats]</a></div>$value[comments]</td>";
							$sunday .= "<td class='bmlt_simple_meeting_one_meeting_address_t2_td'>$location</td>";
							$sunday .= "<td class='bmlt_simple_meeting_one_meeting_map_t2_td'>$map</td>";
							$sunday .= "</tr>";
						} elseif ( $value[weekday_tinyint] == 2 && $value[location_street] ) {
							$monday .= "<tr class='bmlt_simple_meeting_one_meeting_tr'>";
							$monday .= "<td class='bmlt_simple_meeting_one_meeting_time_t2_td'><b>$value[start_time]</b><br/><a class='show-popup bmlt-button' href='#'>$value[formats]</a></div>$value[comments]</td>";
							$monday .= "<td class='bmlt_simple_meeting_one_meeting_address_t2_td'>$location</td>";
							$monday .= "<td class='bmlt_simple_meeting_one_meeting_map_t2_td'>$map</td>";
							$monday .= "</tr>";
						} elseif ( $value[weekday_tinyint] == 3 && $value[location_street] ) {
							$tuesday .= "<tr class='bmlt_simple_meeting_one_meeting_tr'>";
							$tuesday .= "<td class='bmlt_simple_meeting_one_meeting_time_t2_td'><b>$value[start_time]</b><br/><a class='show-popup bmlt-button' href='#'>$value[formats]</a></div>$value[comments]</td>";
							$tuesday .= "<td class='bmlt_simple_meeting_one_meeting_address_t2_td'>$location</td>";
							$tuesday .= "<td class='bmlt_simple_meeting_one_meeting_map_t2_td'>$map</td>";
							$tuesday .= "</tr>";
						} elseif ( $value[weekday_tinyint] == 4 && $value[location_street] ) {
							$wednesday .= "<tr class='bmlt_simple_meeting_one_meeting_tr'>";
							$wednesday .= "<td class='bmlt_simple_meeting_one_meeting_time_t2_td'><b>$value[start_time]</b><br/><a class='show-popup bmlt-button' href='#'>$value[formats]</a></div>$value[comments]</td>";
							$wednesday .= "<td class='bmlt_simple_meeting_one_meeting_address_t2_td'>$location</td>";
							$wednesday .= "<td class='bmlt_simple_meeting_one_meeting_map_t2_td'>$map</td>";
							$wednesday .= "</tr>";
						} elseif ( $value[weekday_tinyint] == 5 && $value[location_street] ) {
							$thursday .= "<tr class='bmlt_simple_meeting_one_meeting_tr'>";
							$thursday .= "<td class='bmlt_simple_meeting_one_meeting_time_t2_td'><b>$value[start_time]</b><br/><a class='show-popup bmlt-button' href='#'>$value[formats]</a></div>$value[comments]</td>";
							$thursday .= "<td class='bmlt_simple_meeting_one_meeting_address_t2_td'>$location</td>";
							$thursday .= "<td class='bmlt_simple_meeting_one_meeting_map_t2_td'>$map</td>";
							$thursday .= "</tr>";
						} elseif ( $value[weekday_tinyint] == 6 && $value[location_street] ) {
							$friday .= "<tr class='bmlt_simple_meeting_one_meeting_tr'>";
							$friday .= "<td class='bmlt_simple_meeting_one_meeting_time_t2_td'><b>$value[start_time]</b><br/><a class='show-popup bmlt-button' href='#'>$value[formats]</a></div>$value[comments]</td>";
							$friday .= "<td class='bmlt_simple_meeting_one_meeting_address_t2_td'>$location</td>";
							$friday .= "<td class='bmlt_simple_meeting_one_meeting_map_t2_td'>$map</td>";
							$friday .= "</tr>";
						} elseif ( $value[weekday_tinyint] == 7 && $value[location_street] ) {
							$saturday .= "<tr class='bmlt_simple_meeting_one_meeting_tr'>";
							$saturday .= "<td class='bmlt_simple_meeting_one_meeting_time_t2_td'><b>$value[start_time]</b><br/><a class='show-popup bmlt-button' href='#'>$value[formats]</a></div>$value[comments]</td>";
							$saturday .= "<td class='bmlt_simple_meeting_one_meeting_address_t2_td'>$location</td>";
							$saturday .= "<td class='bmlt_simple_meeting_one_meeting_map_t2_td'>$map</td>";
							$saturday .= "</tr>";
						}
					}
					$table .= "<tr class='weekdays'><td colspan='3'><strong>MONDAY</strong></td></tr>";
					$table .= $monday;
					$table .= "<tr class='weekdays'><td colspan='3'><strong>TUESDAY</strong></td></tr>";
					$table .= $tuesday;
					$table .= "<tr class='weekdays'><td colspan='3'><strong>WEDNESDAY</strong></td></tr>";
					$table .= $wednesday;
					$table .= "<tr class='weekdays'><td colspan='3'><strong>THURSDAY</strong></td></tr>";
	+				$table .= $thursday;
					$table .= "<tr class='weekdays'><td colspan='3'><strong>FRIDAY</strong></td></tr>";
					$table .= $friday;
					$table .= "<tr class='weekdays'><td colspan='3'><strong>SATURDAY</strong></td></tr>";
					$table .= $saturday;
					$table .= "<tr class='weekdays'><td colspan='3'><strong>SUNDAY</strong></td></tr>";
					$table .= $sunday;
					$table .= '</table><br/>';
				}
				if ( $template == 'btw-testing' ) {
					If ( $header == '1' ) {
						$tmp_array = explode('=',$services);
						$num = $tmp_array[1];
						$number_meetings = do_shortcode("[bmlt_count service_body='$num']");
						$number_groups = do_shortcode("[group_count service_body='$num']");
						$table .= '<table class="bmlt_simple_meetings_table cities" cellpadding="0" cellspacing="0" summary="Meetings">';
						$table .= "<thead>";
						$table .= "<tr><th colspan='4'><span class='meetings_per'>$this_area Meetings</span><br/><span class='we_now_have'>We now have $number_groups Home Groups with $number_meetings Meetings per Week</span</th></tr>";
						$table .= "</thead>";
						$table .= "</table><br/>";
					}
					$table .= '<table class="bmlt_simple_meetings_table" cellpadding="0" cellspacing="0" summary="Meetings">';
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
							$location_info = '<i>'.preg_replace('/(https?):\/\/([A-Za-z0-9\._\-\/\?=&;%,]+)/i', '<a href="$1://$2" target="_blank">$1://$2</a>', $location_info).'</i/>';
						} else { 
							$location_info = '';
						};
						if ($value[comments]) {
							$value[comments] = '<br/>' . $value[comments];
							$value[comments] = preg_replace('/(https?):\/\/([A-Za-z0-9\._\-\/\?=&;%,]+)/i', '<a href="$1://$2" target="_blank">$1://$2</a>', $value[comments]);
						};
						$location = "<b>$value[meeting_name]</b><br/>$location_text$value[location_street] $value[location_municipality], $value[location_province] $value[location_postal_code_1]$location_info";
						$map = "<a target='_blank' href='http://maps.google.com/maps?q=$value[latitude],$value[longitude]+($value[meeting_name])&ll=$value[latitude],$value[longitude]'>Map and Directions</a>";
						if ( $value[weekday_tinyint] == 1 && $value[location_street] ) {
							$sunday .= "<tr class='bmlt_simple_meeting_one_meeting_tr'>";
							$sunday .= "<td class='bmlt_simple_meeting_one_meeting_time_t2_td'><b>$value[start_time]</b><br/>$value[formats]</td>";
							$sunday .= "<td class='bmlt_simple_meeting_one_meeting_address_t2_td'>$location$value[comments]</td>";
							$sunday .= "<td class='bmlt_simple_meeting_one_meeting_map_t2_td'>$map</td>";
							$sunday .= "</tr>";
						} elseif ( $value[weekday_tinyint] == 2 && $value[location_street] ) {
							$monday .= "<tr class='bmlt_simple_meeting_one_meeting_tr'>";
							$monday .= "<td class='bmlt_simple_meeting_one_meeting_time_t2_td'><b>$value[start_time]</b><br/>$value[formats]</td>";
							$monday .= "<td class='bmlt_simple_meeting_one_meeting_address_t2_td'>$location$value[comments]</td>";
							$monday .= "<td class='bmlt_simple_meeting_one_meeting_map_t2_td'>$map</td>";
							$monday .= "</tr>";
						} elseif ( $value[weekday_tinyint] == 3 && $value[location_street] ) {
							$tuesday .= "<tr class='bmlt_simple_meeting_one_meeting_tr'>";
							$tuesday .= "<td class='bmlt_simple_meeting_one_meeting_time_t2_td'><b>$value[start_time]</b><br/>$value[formats]</td>";
							$tuesday .= "<td class='bmlt_simple_meeting_one_meeting_address_t2_td'>$location$value[comments]</td>";
							$tuesday .= "<td class='bmlt_simple_meeting_one_meeting_map_t2_td'>$map</td>";
							$tuesday .= "</tr>";
						} elseif ( $value[weekday_tinyint] == 4 && $value[location_street] ) {
							$wednesday .= "<tr class='bmlt_simple_meeting_one_meeting_tr'>";
							$wednesday .= "<td class='bmlt_simple_meeting_one_meeting_time_t2_td'><b>$value[start_time]</b><br/>$value[formats]</td>";
							$wednesday .= "<td class='bmlt_simple_meeting_one_meeting_address_t2_td'>$location$value[comments]</td>";
							$wednesday .= "<td class='bmlt_simple_meeting_one_meeting_map_t2_td'>$map</td>";
							$wednesday .= "</tr>";
						} elseif ( $value[weekday_tinyint] == 5 && $value[location_street] ) {
							$thursday .= "<tr class='bmlt_simple_meeting_one_meeting_tr'>";
							$thursday .= "<td class='bmlt_simple_meeting_one_meeting_time_t2_td'><b>$value[start_time]</b><br/>$value[formats]</td>";
							$thursday .= "<td class='bmlt_simple_meeting_one_meeting_address_t2_td'>$location$value[comments]</td>";
							$thursday .= "<td class='bmlt_simple_meeting_one_meeting_map_t2_td'>$map</td>";
							$thursday .= "</tr>";
						} elseif ( $value[weekday_tinyint] == 6 && $value[location_street] ) {
							$friday .= "<tr class='bmlt_simple_meeting_one_meeting_tr'>";
							$friday .= "<td class='bmlt_simple_meeting_one_meeting_time_t2_td'><b>$value[start_time]</b><br/>$value[formats]</td>";
							$friday .= "<td class='bmlt_simple_meeting_one_meeting_address_t2_td'>$location$value[comments]</td>";
							$friday .= "<td class='bmlt_simple_meeting_one_meeting_map_t2_td'>$map</td>";
							$friday .= "</tr>";
						} elseif ( $value[weekday_tinyint] == 7 && $value[location_street] ) {
							$saturday .= "<tr class='bmlt_simple_meeting_one_meeting_tr'>";
							$saturday .= "<td class='bmlt_simple_meeting_one_meeting_time_t2_td'><b>$value[start_time]</b><br/>$value[formats]</td>";
							$saturday .= "<td class='bmlt_simple_meeting_one_meeting_address_t2_td'>$location$value[comments]</td>";
							$saturday .= "<td class='bmlt_simple_meeting_one_meeting_map_t2_td'>$map</td>";
							$saturday .= "</tr>";
						}
					}
					$table .= "<tr class='ui-widget-header ui-state-default'><td colspan='3'><strong>SUNDAY</strong></td></tr>";
					if ( !$sunday ) { $sunday .= "<tr><td colspan='3'>No Meetings</td></tr>"; }
					$table .= $sunday;
					$table .= "<tr class='ui-widget-header ui-state-default'><td colspan='3'><strong>MONDAY</strong></td></tr>";
					if ( !$monday ) { $monday .= "<tr><td colspan='3'>No Meetings</td></tr>"; }
					$table .= $monday;
					$table .= "<tr class='ui-widget-header ui-state-default'><td colspan='3'><strong>TUESDAY</strong></td></tr>";
					if ( !$tuesday ) { $tuesday .= "<tr><td colspan='3'>No Meetings</td></tr>"; }
					$table .= $tuesday;
					$table .= "<tr class='ui-widget-header ui-state-default'><td colspan='3'><strong>WEDNESDAY</strong></td></tr>";
					if ( !$wednesday ) { $wednesday .= "<tr><td colspan='3'>No Meetings</td></tr>"; }
					$table .= $wednesday;
					$table .= "<tr class='ui-widget-header ui-state-default'><td colspan='3'><strong>THURSDAY</strong></td></tr>";
					if ( !$thursday ) { $thursday .= "<tr><td colspan='3'>No Meetings</td></tr>"; }
	+				$table .= $thursday;
					$table .= "<tr class='ui-widget-header ui-state-default'><td colspan='3'><strong>FRIDAY</strong></td></tr>";
					if ( !$friday ) { $friday .= "<tr><td colspan='3'>No Meetings</td></tr>"; }
					$table .= $friday;
					$table .= "<tr class='ui-widget-header ui-state-default'><td colspan='3'><strong>SATURDAY</strong></td></tr>";
					if ( !$saturday ) { $saturday .= "<tr><td colspan='3'>No Meetings</td></tr>"; }
					$table .= $saturday;
					$table .= '</table>';
				}
				if ( $template == '1' || $template == 'btw' ) {
					if ( $header == '0' ) {
						$sunday = "<table class='ui-bmlt-table'>";
						$monday = "<table class='ui-bmlt-table'>";
						$tuesday = "<table class='ui-bmlt-table'>";
						$wednesday = "<table class='ui-bmlt-table'>";
						$thursday = "<table class='ui-bmlt-table'>";
						$friday = "<table class='ui-bmlt-table'>";
						$saturday = "<table class='ui-bmlt-table'>";
					} else {
						$sunday = "<table class='bmlt-table'>";
						$monday = "<table class='bmlt-table'>";
						$tuesday = "<table class='bmlt-table'>";
						$wednesday = "<table class='bmlt-table'>";
						$thursday = "<table class='bmlt-table'>";
						$friday = "<table class='bmlt-table'>";
						$saturday = "<table class='bmlt-table'>";
					}
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
							$location_info = '<i>'.preg_replace('/(https?):\/\/([A-Za-z0-9\._\-\/\?=&;%,]+)/i', '<a href="$1://$2" target="_blank">$1://$2</a>', $location_info).'</i/>';
						} else { 
							$location_info = '';
						};
						if ($value[comments]) {
							$value[comments] = $value[comments];
							$value[comments] = preg_replace('/(https?):\/\/([A-Za-z0-9\._\-\/\?=&;%,]+)/i', '<a href="$1://$2" target="_blank">$1://$2</a>', $value[comments]);
						};
						$area = '';
						if ( $template == 'btw' ) {
							$area = $unique_areas[$value[service_body_bigint]];
							if ( $area == '' ) {
								$area = 'Florida Region';
							}
							$area = '<br/>('.$area.')';
						}
						//$value[meeting_name] = strtoupper($value[meeting_name]);
						$location = "<b>$value[meeting_name]</b></br>$location_text$value[location_street] $value[location_municipality], $value[location_province] $value[location_postal_code_1]$location_info$area";
						$map_location = urlencode('"') . urlencode($value[meeting_name]) . '+-+' . urlencode($value[location_street]) . '+' . urlencode($value[location_municipality]) . '+' . urlencode($value[location_province]) . '+' . urlencode($value[location_postal_code_1]) . urlencode('"');
						$map_location = str_replace("%28", "[", $map_location);
						$map_location = str_replace("%29", "]", $map_location);
						$map = "<a class='bmlt-button' target='_blank' href='http://maps.google.com/maps?q=$value[latitude],$value[longitude]($map_location)&z=18&iwloc=A'>Map</a>";
						$column1 = "<div class='bmlt-time'><b>$value[start_time]</b></div><div class='show-popup bmlt-button'><a id='".preg_replace('/[\s\W]+/', '', $value[meeting_name])."-cities' href='#'>$value[formats]</a></div><div class='bmlt-comments'>".wordwrap($value[comments],35,"\n")."</div>";
						
						if ( $value[weekday_tinyint] == 1 && $value[location_street] ) {
							$sunday .= "<tr>";
							$sunday .= "<td class='bmlt-column1'>$column1</td>";
							$sunday .= "<td class='bmlt-column2'>$location</td>";
							$sunday .= "<td class='bmlt-column3'>$map</td>";
							$sunday .= "</tr>";
						} elseif ( $value[weekday_tinyint] == 2 && $value[location_street] ) {
							$monday .= "<tr>";
							$monday .= "<td class='bmlt-column1'>$column1</td>";
							$monday .= "<td class='bmlt-column2'>$location</td>";
							$monday .= "<td class='bmlt-column3'>$map</td>";
							$monday .= "</tr>";
						} elseif ( $value[weekday_tinyint] == 3 && $value[location_street] ) {
							$tuesday .= "<tr>";
							$tuesday .= "<td class='bmlt-column1'>$column1</td>";
							$tuesday .= "<td class='bmlt-column2'>$location</td>";
							$tuesday .= "<td class='bmlt-column3'>$map</td>";
							$tuesday .= "</tr>";
						} elseif ( $value[weekday_tinyint] == 4 && $value[location_street] ) {
							$wednesday .= "<tr>";
							$wednesday .= "<td class='bmlt-column1'>$column1</td>";
							$wednesday .= "<td class='bmlt-column2'>$location</td>";
							$wednesday .= "<td class='bmlt-column3'>$map</td>";
							$wednesday .= "</tr>";
						} elseif ( $value[weekday_tinyint] == 5 && $value[location_street] ) {
							$thursday .= "<tr>";
							$thursday .= "<td class='bmlt-column1'>$column1</td>";
							$thursday .= "<td class='bmlt-column2'>$location</td>";
							$thursday .= "<td class='bmlt-column3'>$map</td>";
							$thursday .= "</tr>";
						} elseif ( $value[weekday_tinyint] == 6 && $value[location_street] ) {
							$friday .= "<tr>";
							$friday .= "<td class='bmlt-column1'>$column1</td>";
							$friday .= "<td class='bmlt-column2'>$location</td>";
							$friday .= "<td class='bmlt-column3'>$map</td>";
							$friday .= "</tr>";
						} elseif ( $value[weekday_tinyint] == 7 && $value[location_street] ) {
							$saturday .= "<tr>";
							$saturday .= "<td class='bmlt-column1'>$column1</td>";
							$saturday .= "<td class='bmlt-column2'>$location</td>";
							$saturday .= "<td class='bmlt-column3'>$map</td>";
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

					If ( $header == '1' ) {
					
						$output .= '

						<div class="hide ui-bmlt-header ui-state-default">';
						
							
							$output .= '<div class="bmlt-button-container"><a id="day" class="bmlt-button bmlt-button-weekdays">Weekdays</a></div>';
							$output .= '
							<select style="width:'.$dropdown_width.';" data-placeholder="Cities" id="e2">
								<option></option>';
								foreach ($unique_city as $city_value) {
									$output .= "<option value=".preg_replace('/[\s\W]+/', '', $city_value).">$city_value</option>";
								}
								$output .= '
							</select>

							<select style="width:'.$dropdown_width.';" data-placeholder="Groups" id="e3">
								<option></option>';
								foreach ($unique_group as $group_value) {
									$output .= "<option value=".preg_replace('/[\s\W]+/', '', $group_value).">$group_value</option>";
								}
								$output .= '
							</select>

							<select style="width:'.$dropdown_width.';" data-placeholder="Locations" id="e4">
								<option></option>';
								foreach ($unique_location as $location_value) {
									$output .= "<option value=".preg_replace('/[\s\W]+/', '', $location_value).">$location_value</option>";
								}
								$output .= '
							</select>';

							if ( $has_zip_codes == '1' ) {

							$output .= '

							<select style="width:'.$dropdown_width.';" data-placeholder="Zips" id="e5">
								<option></option>';
								foreach ($unique_zip as $zip_value) {
									$output .= "<option value=".preg_replace('/[\s\W]+/', '', $zip_value).">$zip_value</option>";
								}
								$output .= '
							</select>';
							
							}

							if ( $has_formats == '1' ) {

							$output .= '

							<select style="width:'.$dropdown_width.';" data-placeholder="Formats" id="e6">
								<option></option>';
								foreach ($unique_format_name_string as $format_value) {
									$output .= "<option value=".preg_replace('/[\s\W]+/', '', $format_value).">$format_value</option>";
								}
								$output .= '
							</select>';
							
							}

							$output .= '
							
						</div>';
					}
					if ( $has_tabs == '1' ) {

						$output .= '

						<div class="bmlt-page hide" id="days">

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
									<div id="ui-tabs-1">' . $sunday . '</div>
									<div id="ui-tabs-2">' . $monday . '</div>
									<div id="ui-tabs-3">' . $tuesday . '</div>
									<div id="ui-tabs-4">' . $wednesday . '</div>
									<div id="ui-tabs-5">' . $thursday . '</div>
									<div id="ui-tabs-6">' . $friday . '</div>
									<div id="ui-tabs-7">' . $saturday . '</div>
							
							</div>
						
						</div>';
					
					} else {
					
						$output .= '<div class="bmlt-page hide" id="days">';

						$output .= '<div id="bmlt-table-div">';
						$output .= "<table class='bmlt-table header'>";
						$output .= "<thead>";
						$output .= "<tr class='ui-state-default'><th colspan='4'>SUNDAY</th></tr>";
						$output .= "</thead>";
						$output .= "</table>";
						$output .= $sunday;
						$output .= '</div>';
						$output .= '<div id="bmlt-table-div">';
						$output .= "<table class='bmlt-table header'>";
						$output .= "<thead>";
						$output .= "<tr class='ui-state-default'><th colspan='4'>MONDAY</th></tr>";
						$output .= "</thead>";
						$output .= "</table>";
						$output .= $monday;
						$output .= '</div>';
						$output .= '<div id="bmlt-table-div">';
						$output .= "<table class='bmlt-table header'>";
						$output .= "<thead>";
						$output .= "<tr class='ui-state-default'><th colspan='4'>TUESDAY</th></tr>";
						$output .= "</thead>";
						$output .= "</table>";
						$output .= $tuesday;
						$output .= '</div>';
						$output .= '<div id="bmlt-table-div">';
						$output .= "<table class='bmlt-table header'>";
						$output .= "<thead>";
						$output .= "<tr class='ui-state-default'><th colspan='4'>WEDNESDAY</th></tr>";
						$output .= "</thead>";
						$output .= "</table>";
						$output .= $wednesday;
						$output .= '</div>';
						$output .= '<div id="bmlt-table-div">';
						$output .= "<table class='bmlt-table header'>";
						$output .= "<thead>";
						$output .= "<tr class='ui-state-default'><th colspan='4'>THURSDAY</th></tr>";
						$output .= "</thead>";
						$output .= "</table>";
						$output .= $thursday;
						$output .= '</div>';
						$output .= '<div id="bmlt-table-div">';
						$output .= "<table class='bmlt-table header'>";
						$output .= "<thead>";
						$output .= "<tr class='ui-state-default'><th colspan='4'>FRIDAY</th></tr>";
						$output .= "</thead>";
						$output .= "</table>";
						$output .= $friday;
						$output .= '</div>';
						$output .= '<div id="bmlt-table-div">';
						$output .= "<table class='bmlt-table header'>";
						$output .= "<thead>";
						$output .= "<tr class='ui-state-default'><th colspan='4'>SATURDAY</th></tr>";
						$output .= "</thead>";
						$output .= "</table>";
						$output .= $saturday;
						$output .= '</div>';

						$output .= '</div>';
					
					}

					$output .= $this->get_the_meetings($result, $unique_city, "location_municipality", Null);
					$output .= $this->get_the_meetings($result, $unique_group, "meeting_name", Null);
					$output .= $this->get_the_meetings($result, $unique_location, "location_text", Null);
					if ( $has_zip_codes == '1' ) {
						$output .= $this->get_the_meetings($result, $unique_zip, "location_postal_code_1", Null);
					}
					if ( $has_formats == '1' ) {
						$output .= $this->get_the_meetings($result, $unique_format_name_string, "name_string", $format);
					}
				}
				
				if ( $template == 'voting' ) {
					$output = $this->get_the_group($unique_group);				
				} else {
					if ( $template == '2' ) {
						$output .= '<div>' . $table . '</div>';
					}
					if ( $template == 'btw' ) {
						$output .= '<div>' . $table . '</div>';
					}
					if ( $template == '3' ) {
						$output .= '<div>' . $cities . '</div>';
					}
					
					//$output .= '<script type="text/javascript">';
					//$output .= 'jQuery( "#days" ).removeClass("hide").addClass("show");';
					//$output .= 'jQuery( ".ui-bmlt-header" ).removeClass("hide").addClass("show");';
					//$output .= '</script>';
				}
				
				$output .'<div id="bmlt-tabs" class="hide">'.$output.'</div>';

				$output .= "
				<div class='overlay-bg'>
				<div class='overlay-title'>
				<span class='overlay-title-text'>Meeting Formats</span>
				<a class='bmlt-button close-btn' href='#'>Close</a>
				</div>
				<div class='overlay-content'>
				$format_table
				</div>
				</div>";				

				return $output;
			}
			
			function get_the_group($result_data)
			{
				global $wpdb, $user_identity, $user_ID;
				$user_ID = intval($user_ID);

				$this_output = "<div>";
				$this_output = "<table class='bmlt-table'>";
				$this_output = "<table class='bmlt-table'>";
				$this_output .= "<thead>";
				$this_output .= "<tr class='ui-state-default'><th>GROUP</th><th style='text-align: center;' colspan='2'>GROUP ID</th></tr>";
				$this_output .= "</thead><tbody>";
				foreach ($result_data as $key => $value) {
					if ($value[location_text]) {
						$location_text = $value[location_text] . ',';
					} else { 
						$location_text = '';
					};
					if ($value[location_info]) {
						$location_info = '<br/>' . $value[location_info];
						$location_info = '<i>'.preg_replace('/(https?):\/\/([A-Za-z0-9\._\-\/\?=&;%,]+)/i', '<a href="$1://$2" target="_blank">$1://$2</a>', $location_info).'</i/>';
					} else { 
						$location_info = '';
					};
					//$location = "$value[location_street], $value[location_municipality]";
					$this_output .= "<tr>";
					$this_output .= "<td style='width:40%; font-size:20px;'>$value[meeting_name]</td>";
					$this_output .= "<td style='width:30%; font-size:20px; text-align: center;'>$value[worldid_mixed]</td>";
					if ( $value[worldid_mixed] && $user_ID == 0 ) {
						$this_output .= "<td style='width:30%; text-align: center;'><div class=''><a class='blue button' href='http://naflorida.org/wp-login.php'>LOGIN TO VOTE</a></div</td>";
					} elseif ( $value[worldid_mixed] && $user_ID > 0 ) {
						$this_output .= "<td style='width:30%; text-align: center;'><div class=''><a class='blue button' href='http://naflorida.org/car-vote/'>VOTE</a></div</td>";
					} else { 
						$this_output .= "<td style='font-size:20px;'> </td>";
					}
					$this_output .= "</tr>";
				}
				$this_output .= '</tbody></table>';
				return $this_output;
			}

			function get_the_meetings($result_data, $unique_data, $unique_value, $format_db)
			{
			global $template, $unique_areas;
				if ($unique_value=='name_string') {
					//$unique_data = $unique_formats;
				}
				foreach ($unique_data as $this_value) {
					$this_output .= "<div class='hide bmlt-page' id='".preg_replace('/[\s\W]+/', '', $this_value)."'>";
					$sunday_init = 0;
					$monday_init = 0;
					$tuesday_init = 0;
					$wednesday_init = 0;
					$thursday_init = 0;
					$friday_init = 0;
					$saturday_init = 0;
					foreach ($result_data as $key => $value) {
						if ( ($this_value == $value[$unique_value] || $unique_value=='name_string') && $value[location_street] ) {
							if ($unique_value=='name_string') {
								$good = False;
								foreach ($format_db as $key => $value1) {
									$key_string = $value1[key_string];
									$name_string = $value1[name_string];
									if ( $name_string == $this_value ) {
										$tvalue = explode(',',$value[formats]);
										foreach ($tvalue as $t_value) {
											if ( $t_value == $key_string ) {
												$good = True;
											}
										}
									}
								}
								//var_dump($good);
								if ( $good == False ) {
									continue;
								}
							}
							

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
								$location_info = '<i>'.preg_replace('/(https?):\/\/([A-Za-z0-9\._\-\/\?=&;%,]+)/i', '<a href="$1://$2" target="_blank">$1://$2</a>', $location_info).'</i/>';
							} else { 
								$location_info = '';
							};
							if ($value[comments]) {
								$value[comments] = $value[comments];
								$value[comments] = preg_replace('/(https?):\/\/([A-Za-z0-9\._\-\/\?=&;%,]+)/i', '<a href="$1://$2" target="_blank">$1://$2</a>', $value[comments]);
							};
							$area = '';
							if ( $template == 'btw' ) {
								$area = $unique_areas[$value[service_body_bigint]];
								if ( $area == '' ) {
									$area = 'Florida Region';
								}
								$area = '<br/>('.$area.')';
							}
							$location = "<b>$value[meeting_name]</b></br>$location_text$value[location_street] $value[location_municipality], $value[location_province] $value[location_postal_code_1]$location_info$area";
							$map_location = urlencode('"') . urlencode($value[meeting_name]) . '+-+' . urlencode($value[location_street]) . '+' . urlencode($value[location_municipality]) . '+' . urlencode($value[location_province]) . '+' . urlencode($value[location_postal_code_1]) . urlencode('"');
							$map_location = str_replace("%28", "[", $map_location);
							$map_location = str_replace("%29", "]", $map_location);
							$map = "<a class='bmlt-button' target='_blank' href='http://maps.google.com/maps?q=$value[latitude],$value[longitude]($map_location)&z=18&iwloc=A'>Map</a>";
							$column1 = "<div class='bmlt-time'><b>$value[start_time]</b></div><div class='show-popup bmlt-button'><a id='".preg_replace('/[\s\W]+/', '', $value[meeting_name])."-cities' href='#'>$value[formats]</a></div><div class='bmlt-comments'>".wordwrap($value[comments],35,"\n")."</div>";
							if ( $value[weekday_tinyint] == 1  && $value[location_street] ) {
								if ( $sunday_init == 0 ) {
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
							} elseif ( $value[weekday_tinyint] == 2 && $value[location_street] ) {
								if ( $monday_init == 0 ) {
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
							} elseif ( $value[weekday_tinyint] == 3 && $value[location_street] ) {
								if ($tuesday_init == 0 ) {
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
							} elseif ( $value[weekday_tinyint] == 4 && $value[location_street] ) {
								if ($wednesday_init == 0 ) {
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
							} elseif ( $value[weekday_tinyint] == 5 && $value[location_street] ) {
								if ($thursday_init == 0 ) {
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
							} elseif ( $value[weekday_tinyint] == 6 && $value[location_street] ) {
								if ($friday_init == 0 ) {
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
							} elseif ( $value[weekday_tinyint] == 7 && $value[location_street] ) {
								if ($saturday_init == 0 ) {
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
					}
					if ( $sunday_init == 1 ) {
						$this_output .= "<div id='bmlt-table-div'>$sunday_data</tbody></table></div>";
					}
					if ( $monday_init == 1 ) {
						$this_output .= "<div id='bmlt-table-div'>$monday_data</tbody></table></div>";
					}
					if ( $tuesday_init == 1 ) {
						$this_output .= "<div id='bmlt-table-div'>$tuesday_data</tbody></table></div>";
					}
					if ( $wednesday_init == 1 ) {
						$this_output .= "<div id='bmlt-table-div'>$wednesday_data</tbody></table></div>";
					}
					if ( $thursday_init == 1 ) {
						$this_output .= "<div id='bmlt-table-div'>$thursday_data</tbody></table></div>";
					}
					if ( $friday_init == 1 ) {
						$this_output .= "<div id='bmlt-table-div'>$friday_data</tbody></table></div>";
					}
					if ( $saturday_init == 1 ) {
						$this_output .= "<div id='bmlt-table-div'>$saturday_data</tbody></table></div>";
					}
					$this_output .= '</div>';
				}
				return $this_output;
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
				$root_server = $this->options['root_server'];
				$services = '';
				$subtract = intval($subtract);
				if ($service_body_parent != Null && $service_body != Null) {
					Return '<p>BMLT Tabs Error: Cannot use service_body_parent and service_body at the same time.</p>';
				}
				if ($service_body != Null && $service_body != 'btw' ) {
					$service_body = array_map('trim', explode(",", $service_body));
					foreach ($service_body as $key) {
						$services .= '&services[]=' . $key;
						$t_services .= $key;
					}
				}
				if ($service_body_parent != Null && $service_body != 'btw') {
					$service_body = array_map('trim', explode(",", $service_body_parent));
					$services .= '&recursive=1';
					foreach ($service_body as $key) {
						$services .= '&services[]=' . $key;
						$t_services .= $key;
					}
				}
				if ($service_body == 'btw') {
					$ch      = curl_init();
					$timeout = 30; // set to zero for no timeout
					curl_setopt($ch, CURLOPT_URL, "$root_server/client_interface/json/index.php?switcher=GetSearchResults&formats[]=46");
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
					$results = curl_exec($ch);
					curl_close($ch);
					$results = count(json_decode($results)) - $subtract;
				} else {
					$root_server_services = 'm_'.$root_server.''.$t_services;
					if ( false === ( $results = get_transient( $root_server_services ) ) ) {
						$ch      = curl_init();
						$timeout = 30; // set to zero for no timeout
						curl_setopt($ch, CURLOPT_URL, "$root_server/client_interface/json/index.php?switcher=GetSearchResults&formats[]=-47" . $services);
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
						curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
						$results = curl_exec($ch);
						curl_close($ch);
						$results = count(json_decode($results)) - $subtract;
						set_transient( $root_server_services, $results, 60*60*1 );
					}
				}
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
				$root_server = $this->options['root_server'];
				$services = '';
				$subtract = intval($subtract);
				if ($service_body_parent != Null && $service_body != Null) {
					Return '<p>BMLT Tabs Error: Cannot use service_body_parent and service_body at the same time.</p>';
				}
				if ($service_body != Null && $service_body != 'btw' ) {
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
					$ch      = curl_init();
					$timeout = 0; // set to zero for no time-out
						curl_setopt($ch, CURLOPT_URL, "$root_server/client_interface/json/index.php?switcher=GetSearchResults&data_field_key=meeting_name&formats[]=46" . $services);
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
				} else {
					$root_server_services = 'g_'.$root_server.''.$services;
					if ( false === ( $result = get_transient( $root_server_services ) ) ) {
						// It wasn't there, so regenerate the data and save the transient
						$ch      = curl_init();
						$timeout = 0; // set to zero for no time-out
						curl_setopt($ch, CURLOPT_URL, "$root_server/client_interface/json/index.php?switcher=GetSearchResults&data_field_key=meeting_name&formats[]=-47" . $services);
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
						set_transient( $root_server_services, $result, 60*60*1 );
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
					$this->options['root_server'] = $_POST['root_server'];   
					$this->save_admin_options();
					set_transient( 'admin_notice', 'Please put down your weapon. You have 20 seconds to comply.' );
					echo '<div class="updated"><p>Success! Your changes were successfully saved!</p></div>';
				}
				?>
				<div class="wrap">
				<div id="icon-options-general" class="icon32"></div><h2>BMLT Tabs</h2>
				<hr />
				<p><b>BMLT Root Server URL (example: http://naflorida.org/bmlt_server)</b></p>
				<form method="POST" id="bmlt_tabs_options">
					<?php wp_nonce_field('bmlttabsupdate-options'); ?>
					<ul>
						<li><label for="root_server">Root Server: </label>
						<input id="root_server" type="text" maxlength="100" size="100" name="root_server" value="<?php echo $this->options['root_server'] ;?>" /></li>    
					</ul>
					<input type="submit" value="Save Changes" name="bmlttabssave" class="button-primary" />
				</form>
				<br />
				<hr />
				<h2>BMLT Tabs Shortcode Usage</h2>
				<p>Insert the following shortcode into a page.</p>
				<p><b>[bmlt_tabs service_body="2,3,4"]</b></p>
				<p>service_body = one or more BMLT service body IDs.</p>
				<p>Using multiple IDs will combine meetings from each service body into the BMLT Tabs interface.</p>
				<p><b>[bmlt_tabs service_body_parent="1,2,3"]</b></p>
				<p>service_body_parent = one or more BMLT parent service body IDs.</p>
				<p>An example parent service body is a Region.  This would be useful to get all meetings from a specific Region.</p>
				<p>If you don't know your service body ID, ask your BMLT administrator.</p>
				<p><i>You cannot combine the service_body and parent_service_body parameters.</i></p>
				<h2>Dropdown Width</h2>
				<p>With this parameter you can change the width of the dropdowns.</p>
				<p><b>[bmlt_tabs service_body="2" header="1" dropdown_width="auto"|130px|20%]</b></p>
				<p>dropdown_width="auto" (width will be calculated automatically) (default)</p>
				<p>dropdown_width="130px" (width will be calculated in pixels)</p>
				<p>dropdown_width="20%" (width will be calculated as a percent of the container width)</p>
				<h2>Formats or No Formats</h2>
				<p>With this parameter you can show or hide the formats dropdown.</p>
				<p><b>[bmlt_tabs service_body="2" header="1" has_formats='0']</b></p>
				<p>has_formats="0" (hide formats dropdown)</p>
				<p>has_formats="1" (show formats dropdown) (default)</p>
				<h2>Zip Codes or No Zip Codes</h2>
				<p>With this parameter you can show or hide the zip code dropdown.</p>
				<p><b>[bmlt_tabs service_body="2" header="1" has_zip_codes='0']</b></p>
				<p>has_zip_codes="0" (hide zip code dropdown)</p>
				<p>has_zip_codes="1" (show zip code dropdown) (default)</p>
				<h2>Tabs or No Tabs</h2>
				<p>With this parameter you can display meetings without tabs.</p>
				<p><b>[bmlt_tabs service_body="2" has_tabs="0"]</b></p>
				<p>has_tabs="0" (display meetings without tabs)</p>
				<p>has_tabs="1" (display meetings with tabs) (default)</p>
				<h2>Header or No Header</h2>
				<p>The header will show dropdowns to list meetings for cities, groups and locations.</p>
				<p><b>[bmlt_tabs service_body="2" header="1"]</b></p>
				<p>header="0" (do not display the header)</p>
				<p>header="1" (display the header) (default)</p>
				<h2>BMLT Count</h2>
				<p><b>[bmlt_count service_body="2,3,4"]</b></p>
				<p>Will return the number of meetings for one or more BMLT service bodies.</p>
				<p><b>[bmlt_count service_body_parent="1,2,3"]</b></p>
				<p>Will return the number of meetings in one or more BMLT parent service bodies.</p>
				<p><b>[bmlt_count service_body="2" subtract="3"]</b></p>
				<p>subtract = number of meetings to subtract from total meetings (optional)</p>
				<p><i>Subtract is useful when you are using BMLT for subcommittee meetings and do want to count those meetings.</i></p>
				<h2>BMLT Group Count</h2>
				<p><b>[group_count service_body="2,3,4"]</b></p>
				<p>Will return the number of Groups for one or more BMLT service bodies.</p>
				<p><b>[group_count service_body_parent="1,2,3"]</b></p>
				<p>Will return the number of Groups in one or more BMLT parent service bodies.</p>
				<?
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
			/**
			* Retrieves the plugin options from the database.
			* @return array
			*/
			function getOptions() {
				//Don't forget to set up the default options
				if (!$theOptions = get_option($this->optionsName)) {
					$theOptions = array(
						'root_server' => ''
				);
					update_option($this->optionsName, $theOptions);
				}
				$this->options = $theOptions;
			}
		  
			/**
			* Saves the admin options to the database.
			*/
			function save_admin_options(){
				update_option($this->optionsName, $this->options);
				return;
			}
		} //End Class BMLTTabs
	} // end if
	//instantiate the class
	if (class_exists("BMLTTabs")) {
		$BMLTTabs_instance = new BMLTTabs();
	}
?>