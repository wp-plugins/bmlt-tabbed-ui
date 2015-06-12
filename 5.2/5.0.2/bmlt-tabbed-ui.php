<?php

	/**

	Plugin Name: BMLT Tabbed UI

	Plugin URI: http://wordpress.org/extend/plugins/bmlt-tabbed-ui/

	Description: Adds a jQuery Tabbed UI for BMLT.

	Author: Jack S Florida Region

	Version: 5.0.2

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

			var $version = '5.0.2';

	

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

					} else {

						add_action("init", array(

							&$this,

							"enqueue_frontend_files"

						));

						add_action("wp_head", array(

							&$this,

							"has_shortcode"

						));

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
				$found = false;
				 
				// check the post content for the short code
				if ( stripos($post_to_check->post_content, '[bmlt_tabs') !== false ) {
					// we have found the short code
					
					$this->testRootServer($this->options['root_server']);
				}
				
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

				wp_enqueue_style("bmlt-tabs-admin", plugin_dir_url(__FILE__) . "css/bmlt_tabs.css", false, "1.0", 'all');


			}

			/**

			* @desc Adds JS/CSS to the header

			*/

			function enqueue_frontend_files()

			{

				wp_enqueue_style("bmlt-tabs-jqueryui", plugin_dir_url(__FILE__) . "css/jquery-ui.custom.min.css", false, null, false);

				wp_enqueue_style("bmlt-tabs-select2", plugin_dir_url(__FILE__) . "css/select2.css", false, null, false);

				wp_enqueue_style("bmlt-tabs", plugin_dir_url(__FILE__) . "css/bmlt_tabs.css", false, null, false);

				wp_enqueue_script("tooltipster", plugin_dir_url(__FILE__) . "js/jquery.tooltipster.min.js", array('jquery'), null, true);
				
				wp_enqueue_script('jquery-ui-core');

				wp_enqueue_script('jquery-ui-tabs');

				wp_enqueue_script("bmlt-tabs-select2", plugin_dir_url(__FILE__) . "js/select2.min.js", array('jquery'), null, true);

				wp_enqueue_script("bmlt-tabs", plugin_dir_url(__FILE__) . "js/bmlt_tabs.js", array('jquery'), null, true);
				
				wp_enqueue_style( 'dashicons' );

			}


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

			function getUniqueGroups() {

				$ch      = curl_init();

				curl_setopt($ch, CURLOPT_URL, "$root_server/client_interface/json/index.php?switcher=GetSearchResults&sort_key=meeting_name&data_field_key=meeting_name,worldid_mixed&formats[]=-47" . $services);

				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

				curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)"); 
				
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);

				$unique_group = curl_exec($ch);

				curl_close($ch);

				$unique_group = json_decode($unique_group,true);

				$unique_group = $this->arrayUnique($unique_group);

				$this->sortBySubkey($unique_group, 'meeting_name');
				
				return $unique_group;
			}
			
			function getAllMeetings($root_server, $services) {

				$transient_key = 'bmlt_tabs_'.md5($root_server."/client_interface/json/?switcher=GetSearchResults".$services."&sort_key=time");

				if ( false === ( $result = get_transient( $transient_key ) ) || intval($this->options['cache_time']) == 0 ) {

						// It wasn't there, so regenerate the data and save the transient

					$ch=curl_init();
					curl_setopt($ch, CURLOPT_URL,"$root_server/client_interface/json/?switcher=GetSearchResults$services&sort_key=time" );
					curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)"); 
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($ch,CURLOPT_VERBOSE,false);
					curl_setopt($ch, CURLOPT_TIMEOUT, 10);
					curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, FALSE);
					curl_setopt($ch,CURLOPT_SSLVERSION,3);
					curl_setopt($ch,CURLOPT_SSL_VERIFYHOST, FALSE);
					$results=curl_exec($ch);
					//echo curl_error($ch);
					$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
					$c_error = curl_error ($ch);
					$c_errno = curl_errno ($ch);
					curl_close($ch);
					
					if ( $results == False ) {
						echo '<div style="font-size: 20px;text-align:center;font-weight:normal;color:#F00;margin:0 auto;margin-top: 30px;"><p>Problem Connecting to BMLT Root Server</p><p>'.$root_server.'</p><p>Error: '.$c_errno.', '.$c_error.'</p><p>Please try again later</p></div>';
						return '';
					}

					$result = json_decode($results,true);
					
					if ( intval($this->options['cache_time']) > 0 ) {

						set_transient( $transient_key, $result, intval($this->options['cache_time']) * HOUR_IN_SECONDS );
						
					}

				}

				If ( count($result) == 0 || $result == null ) {
					echo "No meetings were found.";
					return 0;
				}
				
				return $result;
					
			}

			function getday ($day) {
				if ( $day == 1 ) {
					Return "Sunday";
				} elseif ( $day == 2 ) {
					return "Monday";
				} elseif ( $day == 3 ) {
					return "Tuesday";
				} elseif ( $day == 4 ) {
					return "Wednesday";
				} elseif ( $day == 5 ) {
					return "Thursday";
				} elseif ( $day == 6 ) {
					return "Friday";
				} elseif ( $day == 7 ) {
					return "Saturday";
				}
			}

			function getTheFormats($root_server) {
			
				$transient_key = 'bmlt_tabs_'.md5($root_server."/client_interface/json/?switcher=GetFormats");

				if ( false === ( $format = get_transient( $transient_key ) ) || intval($this->options['cache_time']) == 0 ) {

					// It wasn't there, so regenerate the data and save the transient

					$ch      = curl_init();

					curl_setopt($ch, CURLOPT_URL, "$root_server/client_interface/json/?switcher=GetFormats");

					curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)"); 
					
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 

					curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);

					$formats = curl_exec($ch);

					curl_close($ch);

					$format = json_decode($formats,true);

					if ( intval($this->options['cache_time']) > 0 ) {

						set_transient( $transient_key, $format, intval($this->options['cache_time']) * HOUR_IN_SECONDS );
						
					}

				}
				
				return $format;
				
			}

			function testRootServer($root_server) {

				$ch=curl_init();
				curl_setopt($ch, CURLOPT_URL,"$root_server/client_interface/json/?switcher=GetFormats" );
				curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)"); 
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch,CURLOPT_VERBOSE,false);
				curl_setopt($ch, CURLOPT_TIMEOUT, 10);
				curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, FALSE);
				curl_setopt($ch,CURLOPT_SSLVERSION,3);
				curl_setopt($ch,CURLOPT_SSL_VERIFYHOST, FALSE);
				$results=curl_exec($ch);
				//echo curl_error($ch);
				$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				$c_error = curl_error ($ch);
				$c_errno = curl_errno ($ch);
				curl_close($ch);
								
				if ( $results == False ) {
					echo '<div style="font-size: 20px;text-align:center;font-weight:normal;color:#F00;margin:0 auto;margin-top: 30px;"><p>Problem Connecting to BMLT Root Server</p><p>'.$root_server.'</p><p>Error: '.$c_errno.', '.$c_error.'</p><p>Please try again later</p></div>';
					exit;
				}
				
			}

			function tabbed_ui($atts, $content = null)

			{

				global $template, $unique_areas;

				extract(shortcode_atts(array(
					"this_root_server" => '',
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
					"template" => '1',
					"view_by" => 'weekday',
					"dropdown_width" => 'auto',
					"has_zip_codes" => '1',
					"header" => '1'
				), $atts));

				$has_tabs = ($has_meetings == '0' ? '0' : $has_tabs);

				//$has_tabs = ($include_weekday_button == '0' ? '1' : $has_tabs);

				$include_city_button = ($view_by == 'city' ? '1' : $include_city_button);
				
				$include_weekday_button = ($view_by == 'weekday' ? '1' : $include_weekday_button);
				
				$include_city_button = ($has_meetings == '0' ? '0' : $include_city_button);
				
				$include_weekday_button = ($has_meetings == '0' ? '0' : $include_weekday_button);
				
				//$has_tabs = ($view_by == 'city' ? '0' : $has_tabs);
								
				if ($template != '1' && $template != 'btw') {

					Return '<p>BMLT Tabs Error: Template must = "1" or "btw".</p>';

				}

				if ($view_by != 'city' && $view_by != 'weekday') {

					Return '<p>BMLT Tabs Error: view_by must = "city" or "weekday".</p>';

				}

				if ($include_city_button != '0' && $include_city_button != '1') {

					Return '<p>BMLT Tabs Error: include_city_button must = "0" or "1".</p>';

				}

				if ($include_weekday_button != '0' && $include_weekday_button != '1') {

					Return '<p>BMLT Tabs Error: include_weekday_button must = "0" or "1".</p>';

				}

				$root_server = ($this_root_server != '' ? $this_root_server : $this->options['root_server']);
				
//print_r($this->options['service_body_1']);exit;									
				if ( $service_body_parent == Null && $service_body == Null) {

					$area_data = explode(',',$this->options['service_body_1']);
					$area = $area_data[0];
					$service_body_id = $area_data[1];
					$parent_body_id = $area_data[2];
					
					if ( $parent_body_id == '0' ) {
						$service_body_parent = '0';
					} else {
						$service_body = $service_body_id;
					}

				}

				if ( $root_server == '' ) {

					Return '<p><strong>BMLT Tabs Error: Root Server missing.<br/><br/>Please go to Settings -> BMLT_Tabs and verify Root Server</strong></p>';

				}

				$output   = '';

				$output .= '<script type="text/javascript">';

				$output .= 'jQuery( "body" ).addClass( "bmlt-tabs");';
												
				$output .= '</script>';	

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

				$the_meetings = $this->getAllMeetings($root_server, $services);
				
				if ( $the_meetings == 0 ) {
				
					return;
					
				}

				$formats = $this->getTheFormats($root_server);
					
				if ( $template == 'btw' ) {

					$unique_areas = $this->get_areas($root_server, 'btw');
					
				}

				$unique_zips = $unique_cities = $unique_groups = $unique_locations = $unique_formats = $unique_weekdays = array();

				foreach ($the_meetings as $key => $value) {

					$tvalue = explode(',',$value['formats']);

					foreach ($tvalue as $t_value) {

						$unique_formats[] = $t_value;

					}
					
					if ( isset($value['location_municipality']) ) {

						$unique_cities[] = $value['location_municipality'];

					}

					if ( isset($value['meeting_name']) ) {

						$unique_groups[] = $value['meeting_name'];

					}

					if ( isset($value['location_text']) ) {

						$unique_locations[] = $value['location_text'];

					}

					if ( isset($value['location_postal_code_1']) ) {

						$unique_zips[] = $value['location_postal_code_1'];

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

				asort($unique_group);

				asort($unique_location);

				asort($unique_format);

				$unique_format_name_string = array();
				
				foreach ($formats as $key => $value) {

					$key_string = $value['key_string'];

					$name_string = $value['name_string'];

					foreach ($unique_format as $value1) {

						if ($value1 == $key_string) {

							$unique_format_name_string[] = $name_string;

						}								

					}

				}
				
				asort($unique_format_name_string);
				
				$unique_weekday = array();

				array_push($unique_weekday, "1", "2", "3", "4", "5", "6", "7");

				$meetings_cities = $meetings_days = $meetings_header = $meetings_tab = "";

				for ($x=0; $x<=1; $x++) {

					//if ( $view_by == 'city' ) {
					if ( $x == 0 ) {
					
						$unique_values = $unique_city;
						
					} else {
					
						$unique_values = $unique_weekday;
						
					}

					foreach ($unique_values as $this_value) {
														
						$meetings_header = '<div id="bmlt-table-div">';

						$meetings_header .= "<table class='bmlt-table header'>";

						$meetings_header .= "<thead>";

						//if ( $view_by == 'city' ) {
						if ( $x == 0 ) {
						
							if ( $this_value ) {

								$meetings_header .= "<tr class='ui-state-default meeting-header'><th colspan='4'>".$this_value."</th></tr>";
								
							} else {

								$meetings_header .= "<tr class='ui-state-default meeting-header'><th colspan='4'>NO CITY IN BMLT</th></tr>";
								
							}
							
						} else {
										
							if ( $this_value ) {

								$meetings_header .= "<tr class='ui-state-default meeting-header'><th colspan='4'>".$this->getDay($this_value)."</th></tr>";
							
							} else {

								$meetings_header .= "<tr class='ui-state-default meeting-header'><th colspan='4'>NO MEETINGS</th></tr>";
								
							}
													
						}
						
						$meetings_header .= "</thead>";

						$meetings_header .= "</table>";

						if ( $x == 1 ) {
						
							$meetings_tab .= "<div id='ui-tabs-".$this_value."'>";
							
						}

						$this_meetings = "<table class='ui-bmlt-table'>";

						foreach ($the_meetings as $key => $value) {

							//if ( $view_by == 'city' ) {
							if ( $x == 0 ) {
							
								if ( ! isset($value['location_municipality']) || $this_value != $value['location_municipality']  ) { continue; }
								
							} else {
							
								if ( $this_value != $value['weekday_tinyint']  ) { continue; }
															
							}

							$duration = explode(':',$value['duration_time']);
							
							//print_r($duration);

							$minutes = intval($duration[0])*60 + intval((isset($duration[1]) ? $duration[1] : '0')) + intval((isset($duration[1]) ? $duration[1] : '0'));
							
							$addtime = '+ ' . $minutes . ' minutes';

							$end_time = date ('g:i A',strtotime($value['start_time'] . ' ' . $addtime));

							$value['start_time'] = date ('g:i A',strtotime($value['start_time']));

							$value['start_time'] = $value['start_time']." - ".$end_time;
							
							$location = '';

							if (isset($value['meeting_name'])) {

								$location .= "<div class='meeting-name'>".$value['meeting_name']."</div>";

							} else {
							
								$value['meeting_name'] = '';
								
							}

							if ( isset($value['location_text']) && $value['location_text'] != '' ) {

								$location .= '<div>' . $value['location_text'] . '</div>';

							} else {
							
								$value['location_text'] = '';
								
							}
							
							$address = '';

							if (isset($value['location_street'])) {

								$address .= $value['location_street'];

							} else {
							
								$value['location_street'] = '';
								
							}
							
							if (isset($value['location_municipality'])) {

								if ( $address != '' ) {
								
									$address .= ', ' . $value['location_municipality'];
									
								} else {
								
									$address .= $value['location_municipality'];
									
								}
								
							} else {
							
								$value['location_municipality'] = '';
								
							}

							if (isset($value['location_province'])) {

								if ( $address != '' ) {
								
									$address .= ', ' . $value['location_province'];
									
								} else {
								
									$address .= $value['location_province'];
									
								}
								
							} else {
							
								$value['location_province'] = '';
								
							}
							
							if ( isset($value['location_postal_code_1']) ) {

								if ( $address != '' ) {
								
									$address .= ', ' . $value['location_postal_code_1'];
									
								} else {
								
									$address .= $value['location_postal_code_1'];
									
								}

							} else {
							
								$value['location_postal_code_1'] = '';
								
							}
							
							$location .= '<div>' . $address . '</div>';
							
							if (isset($value['location_info'])) {

								$location_info = $value['location_info'];

								$location .= '<div><i>'.preg_replace('/(https?):\/\/([A-Za-z0-9\._\-\/\?=&;%,]+)/i', '<a href="$1://$2" target="_blank">$1://$2</a>', $location_info).'</i/></div>';

							} else {
							
								$value['location_info'] = '';
								
							}

							$area = '';

							if ( $template == 'btw' ) {

								$area = $unique_areas[$value['service_body_bigint']];

								if ( $area == '' ) {

									$area = '<br/>(Florida Region)';

								}

								$location .= '<div>('.$area.')</div>';

							}
							
							if (isset($value['comments'])) {

								$value['comments'] = $value['comments'];

								$value['comments'] = preg_replace('/(https?):\/\/([A-Za-z0-9\._\-\/\?=&;%,]+)/i', '<a href="$1://$2" target="_blank">$1://$2</a>', $value['comments']);
								
								$value['comments'] = "<div class='bmlt-comments'>".wordwrap($value['comments'],35,"\n")."</div>";

							} else { 

								$value['comments'] = '';

							}

							$map_location = urlencode('"') . urlencode($value['meeting_name']) . '+-+' . urlencode($value['location_street']) . '+' . urlencode($value['location_municipality']) . '+' . urlencode($value['location_province']) . '+' . urlencode($value['location_postal_code_1']) . urlencode('"');

							$map_location = str_replace("%28", "[", $map_location);

							$map_location = str_replace("%29", "]", $map_location);

							$map = "<div class='bmlt-button'><a target='_blank' href='http://maps.google.com/maps?q=$value[latitude],$value[longitude]($map_location)&z=18&iwloc=A'>Map</a></div>";
							
							$today = '';

							//if ( $view_by == 'city' ) {
							if ( $x == 0 ) {
							
								$today = "<div class='bmlt-day'>".$this->getDay($value['weekday_tinyint'])."</div>";
								
							}
							
							$a_format = '<table class="a_format">';

							$tvalue = explode(',',$value['formats']);

							foreach ($tvalue as $t_value) {

								foreach ($formats as $fkey => $fvalue) {

									$key_string = $fvalue['key_string'];

									$name_string = $fvalue['name_string'];

									if ( $t_value == $key_string ) {

										$a_format .= '<tr><td>'. $t_value . '</td><td>' . $fvalue['name_string'] . '</td><td>' . $fvalue['description_string'] . '</td></tr>';
										
									}

								}

							}
							
							$a_format .= '</table>';

							if ( $x == 0 ) {
							
								$class = 'bmlt-time';
																					
							} else {

								$class = 'bmlt-time-2';
								
							}

							if ( $value['formats'] ) {
							
								$column1 = "$today<div class=$class>".$value['start_time']."</div><div title='$a_format' class='bmlt-formats tooltip'><div class='dashicons dashicons-search'></div>".$value['formats']."</div>".$value['comments'];
							
							} else {
							
								$column1 = "$today<div class=$class>".$value['start_time']."</div>".$value['comments'];

							}					

							$this_meetings .= "<tr>";

							$this_meetings .= "<td class='bmlt-column1'>$column1</td>";

							$this_meetings .= "<td class='bmlt-column2'>$location</td>";

							$this_meetings .= "<td class='bmlt-column3'>$map</td>";

							$this_meetings .= "</tr>";

						}

						$this_meetings .= '</table>';
						
						$this_meetings .= '</div>';

						if ( $x == 0 ) {
						
							$meetings_cities .= $meetings_header;
							
							$meetings_cities .= $this_meetings;
													
						} else {

							$meetings_days .= $meetings_header;
							
							$meetings_days .= $this_meetings;
							
							$meetings_tab .= $this_meetings;
							
						}
						
					}
					
				}
				
				//var_dump($x);exit;

				If ( $header == '1' ) {
				
					$output .= '

					<div class="hide ui-bmlt-header ui-state-default">';
										
						if ( $view_by == 'weekday' ) {
										
							if ( $include_weekday_button == '1' ) {

								$output .= '<div class="bmlt-button-container"><a id="day" style="color: #FFF; background-color: #FF6B7F;" class="bmlt-button bmlt-button-weekdays">Weekday</a></div>';
								
							}
								
							if ( $include_city_button == '1' ) {
							
								$output .= '<div class="bmlt-button-container"><a id="city" style="color: #000; background-color: #63B8EE;" class="bmlt-button bmlt-button-cities">City</a></div>';
								
							}
							
						} else {
						
							if ( $include_weekday_button == '1' ) {

								$output .= '<div class="bmlt-button-container"><a id="day" style="color: #000; background-color: #63B8EE;" class="bmlt-button bmlt-button-weekdays">Weekday</a></div>';
								
							}
								
							if ( $include_city_button == '1' ) {
							
								$output .= '<div class="bmlt-button-container"><a id="city" style="color: #FFF; background-color: #FF6B7F;" class="bmlt-button bmlt-button-cities">City</a></div>';
								
							}
													
						}							

						if ( $has_cities == '1' ) {

							$output .= '

							<select style="width:'.$dropdown_width.';" data-placeholder="Cities" id="e2">
								<option></option>';
								foreach ($unique_city as $city_value) {
									$output .= "<option value=".preg_replace('/[\s\W]+/', '', $city_value).">$city_value</option>";
								}
								$output .= '
							</select>';
							
						}

						if ( $has_groups == '1' ) {

							$output .= '

							<select style="width:'.$dropdown_width.';" data-placeholder="Groups" id="e3">
								<option></option>';
								foreach ($unique_group as $group_value) {
									$output .= "<option value=".preg_replace('/[\s\W]+/', '', $group_value).">$group_value</option>";
								}
								$output .= '
							</select>';

						}

						if ( $has_locations == '1' ) {

							$output .= '

							<select style="width:'.$dropdown_width.';" data-placeholder="Locations" id="e4">
								<option></option>';
								foreach ($unique_location as $location_value) {
									$output .= "<option value=".preg_replace('/[\s\W]+/', '', $location_value).">$location_value</option>";
								}
								$output .= '
							</select>';
							
						}

						if ( $has_zip_codes == '1' ) {

							$output .= '

							<select style="width:'.$dropdown_width.';" data-placeholder="Zip Codes" id="e5">
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

				if ( $has_tabs == '1' && $has_meetings == '1' ) {

					if ( $view_by == 'weekday' ) {
					
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

				if ( $has_tabs == '0' && $has_meetings == '1' ) {

					//if ( $include_weekday_button == '1' ) {
					
						if ( $view_by == 'weekday' ) {
						
							$output .= '<div class="bmlt-page show" id="days">';
							
						} else {
						
							$output .= '<div class="bmlt-page hide" id="days">';
							
						}

						$output .= $meetings_days;

						$output .= '</div>';
						
					//}
						
				}

				if ( $has_meetings == '1' ) {
				
					//if ( $include_city_button == '1' ) {
					
						if ( $view_by == 'weekday' ) {
						
							$output .= '<div class="bmlt-page hide" id="cities">';
							
						} else {
						
							$output .= '<div class="bmlt-page show" id="cities">';
							
						}

						$output .= $meetings_cities;

						$output .= '</div>';
						
					//}

				}

				if ( $has_cities == '1' ) {
					$output .= $this->get_the_meetings($the_meetings, $unique_city, "location_municipality", $formats);
				}
				if ( $has_groups == '1' ) {
					$output .= $this->get_the_meetings($the_meetings, $unique_group, "meeting_name", $formats);
				}
				if ( $has_locations == '1' ) {
					$output .= $this->get_the_meetings($the_meetings, $unique_location, "location_text", $formats);
				}
				if ( $has_zip_codes == '1' ) {
					$output .= $this->get_the_meetings($the_meetings, $unique_zip, "location_postal_code_1", $formats);
				}
				if ( $has_formats == '1' ) {
					$output .= $this->get_the_meetings($the_meetings, $unique_format_name_string, "name_string", $formats);
				}
	
				$output = '<div id="bmlt-tabs" class="hide">'.$output.'</div>';

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

					if ($value['location_text']) {

						$location_text = $value['location_text'] . ',';

					} else { 

						$location_text = '';

					}

					if ($value['location_info']) {

						$location_info = '<br/>' . $value['location_info'];

						$location_info = '<i>'.preg_replace('/(https?):\/\/([A-Za-z0-9\._\-\/\?=&;%,]+)/i', '<a href="$1://$2" target="_blank">$1://$2</a>', $location_info).'</i/>';

					} else { 

						$location_info = '';

					}

					//$location = "$value['location_street'], $value['location_municipality']";

					$this_output .= "<tr>";

					$this_output .= "<td style='width:40%; font-size:20px;'>".$value['meeting_name']."</td>";

					$this_output .= "<td style='width:30%; font-size:20px; text-align: center;'>".$value[worldid_mixed]."</td>";

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
				
				$this_output = '';

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

						if ($unique_value=='name_string') {

							$good = False;

							foreach ($format_db as $key => $value1) {

								$key_string = $value1['key_string'];

								$name_string = $value1['name_string'];

								if ( $name_string == $this_value ) {

									$tvalue = explode(',',$value['formats']);

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

						} elseif ( ! isset($value[$unique_value]) ) {
						
							continue;
							
						} elseif ( $this_value != $value[$unique_value] ) {
						
							continue;
							
						}

						$duration = explode(':',$value['duration_time']);

						$minutes = intval($duration[0])*60 + intval((isset($duration[1]) ? $duration[1] : '0')) + intval((isset($duration[1]) ? $duration[1] : '0'));

						$addtime = '+ ' . $minutes . ' minutes';

						$end_time = date ('g:i A',strtotime($value['start_time'] . ' ' . $addtime));

						$value['start_time'] = date ('g:i A',strtotime($value['start_time']));

						$value['start_time'] = $value['start_time']." - ".$end_time;
						
						
						$location = '';
						

						if (isset($value['meeting_name'])) {

							$location .= "<div class='meeting-name'>".$value['meeting_name']."</div>";

						} else {
						
							$value['meeting_name'] = '';
							
						}

						if ( isset($value['location_text']) && $value['location_text'] != '' ) {

							$location .= '<div>' . $value['location_text'] . '</div>';

						} else {
						
							$value['location_text'] = '';
							
						}
						
						$address = '';

						if (isset($value['location_street'])) {

							$address .= $value['location_street'];

						} else {
						
							$value['location_street'] = '';
							
						}
						
						if (isset($value['location_municipality'])) {

							if ( $address != '' ) {
							
								$address .= ', ' . $value['location_municipality'];
								
							} else {
							
								$address .= $value['location_municipality'];
								
							}
							
						} else {
						
							$value['location_municipality'] = '';
							
						}

						if (isset($value['location_province'])) {

							if ( $address != '' ) {
							
								$address .= ', ' . $value['location_province'];
								
							} else {
							
								$address .= $value['location_province'];
								
							}
							
						} else {
						
							$value['location_province'] = '';
							
						}
						
						if ( isset($value['location_postal_code_1']) ) {

							if ( $address != '' ) {
							
								$address .= ', ' . $value['location_postal_code_1'];
								
							} else {
							
								$address .= $value['location_postal_code_1'];
								
							}

						} else {
						
							$value['location_postal_code_1'] = '';
							
						}
						
						$location .= '<div>' . $address . '</div>';
						
						if (isset($value['location_info'])) {

							$location_info = $value['location_info'];

							$location .= '<div><i>'.preg_replace('/(https?):\/\/([A-Za-z0-9\._\-\/\?=&;%,]+)/i', '<a href="$1://$2" target="_blank">$1://$2</a>', $location_info).'</i/></div>';

						} else {
						
							$value['location_info'] = '';
							
						}

						$area = '';

						if ( $template == 'btw' ) {

							$area = $unique_areas[$value['service_body_bigint']];

							if ( $area == '' ) {

								$area = '<br/>(Florida Region)';

							}

							$location .= '<div>('.$area.')</div>';

						}
						
						if (isset($value['comments'])) {

							$value['comments'] = $value['comments'];

							$value['comments'] = preg_replace('/(https?):\/\/([A-Za-z0-9\._\-\/\?=&;%,]+)/i', '<a href="$1://$2" target="_blank">$1://$2</a>', $value['comments']);
							
							$value['comments'] = "<div class='bmlt-comments'>".wordwrap($value['comments'],35,"\n")."</div>";

						} else { 

							$value['comments'] = '';

						}

						$map_location = urlencode('"') . urlencode($value['meeting_name']) . '+-+' . urlencode($value['location_street']) . '+' . urlencode($value['location_municipality']) . '+' . urlencode($value['location_province']) . '+' . urlencode($value['location_postal_code_1']) . urlencode('"');

						$map_location = str_replace("%28", "[", $map_location);

						$map_location = str_replace("%29", "]", $map_location);

						$map = "<div class='bmlt-button'><a target='_blank' href='http://maps.google.com/maps?q=$value[latitude],$value[longitude]($map_location)&z=18&iwloc=A'>Map</a></div>";

						$a_format = '<table class="a_format">';

						$tvalue = explode(',',$value['formats']);

						foreach ($tvalue as $t_value) {

							foreach ($format_db as $fkey => $fvalue) {

								$key_string = $fvalue['key_string'];

								$name_string = $fvalue['name_string'];

								if ( $t_value == $key_string ) {

									$a_format .= '<tr><td>'. $t_value . '</td><td>' . $fvalue['name_string'] . '</td><td>' . $fvalue['description_string'] . '</td></tr>';
									
								}

							}

						}
						
						$a_format .= '</table>';

						if ( $value['formats'] ) {
						
							$column1 = "<div class='bmlt-time-2'>".$value['start_time']."</div><div title='$a_format' class='bmlt-formats tooltip'><div class='dashicons dashicons-search'></div>".$value['formats']."</div>".$value['comments'];
						
						} else {
						
							$column1 = "<div class='bmlt-time-2'>".$value['start_time']."</div>".$value['comments'];

						}					
							
						if ( $value['weekday_tinyint'] == 1   ) {

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

						} elseif ( $value['weekday_tinyint'] == 2  ) {

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

						} elseif ( $value['weekday_tinyint'] == 3  ) {

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

						} elseif ( $value['weekday_tinyint'] == 4  ) {

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

						} elseif ( $value['weekday_tinyint'] == 5  ) {

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

						} elseif ( $value['weekday_tinyint'] == 6  ) {

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

						} elseif ( $value['weekday_tinyint'] == 7  ) {

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

					"this_root_server" => '',
					
					"subtract" => '',

					"service_body_parent" => ''

				), $atts));

				$root_server = ($this_root_server != '' ? $this_root_server : $this->options['root_server']);
				
				if ( $service_body_parent == Null && $service_body == Null) {

					$area_data = explode(',',$this->options['service_body_1']);
					$area = $area_data[0];
					$service_body_id = $area_data[1];
					$parent_body_id = $area_data[2];
					
					if ( $parent_body_id == '0' ) {
						$service_body_parent = '0';
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

					curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)"); 
					
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

					curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);

					$results = curl_exec($ch);

					curl_close($ch);

					$results = count(json_decode($results)) - $subtract;

				} else {

					$transient_key = 'bmlt_tabs_'.md5($root_server."/client_interface/json/index.php?switcher=GetSearchResults&formats[]=-47" . $services);

					if ( false === ( $results = get_transient( $transient_key ) ) || intval($this->options['cache_time']) == 0 ) {

						$ch      = curl_init();

						$timeout = 30; // set to zero for no timeout

						curl_setopt($ch, CURLOPT_URL, "$root_server/client_interface/json/index.php?switcher=GetSearchResults&formats[]=-47" . $services);

						curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)"); 
						
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

						curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);

						$results = curl_exec($ch);

						curl_close($ch);

						if ( intval($this->options['cache_time']) > 0 ) {

							set_transient( $transient_key, $results, intval($this->options['cache_time']) * HOUR_IN_SECONDS );
							
						}

					}

					$results = count(json_decode($results)) - $subtract;

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
					
					"this_root_server" => '',

					"service_body_parent" => ''

				), $atts));

				$root_server = ($this_root_server != '' ? $this_root_server : $this->options['root_server']);
				
				if ( $service_body_parent == Null && $service_body == Null) {

					$area_data = explode(',',$this->options['service_body_1']);
					$area = $area_data[0];
					$service_body_id = $area_data[1];
					$parent_body_id = $area_data[2];
					
					if ( $parent_body_id == '0' ) {
						$service_body_parent = '0';
					} else {
						$service_body = $service_body_id;
					}

				}

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
					
					curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)"); 

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

					$transient_key = 'bmlt_tabs_'.md5($root_server."/client_interface/json/index.php?switcher=GetSearchResults&data_field_key=meeting_name&formats[]=-47" . $services);

					if ( false === ( $result = get_transient( $transient_key ) ) || intval($this->options['cache_time']) == 0 ) {

						// It wasn't there, so regenerate the data and save the transient

						$ch      = curl_init();

						$timeout = 0; // set to zero for no time-out

						curl_setopt($ch, CURLOPT_URL, "$root_server/client_interface/json/index.php?switcher=GetSearchResults&data_field_key=meeting_name&formats[]=-47" . $services);

						curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)"); 
						
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

						if ( intval($this->options['cache_time']) > 0 ) {

							set_transient( $transient_key, $result, intval($this->options['cache_time']) * HOUR_IN_SECONDS );
							
						}

					}

				}

				return count($result);

			}

			/**

			* @desc Adds the options sub-panel

			*/

			function get_areas ( $root_server, $source ) {

				$transient_key = 'bmlt_tabs_'.md5("$root_server/client_interface/json/?switcher=GetServiceBodies");

				if ( false === ( $result = get_transient( $transient_key ) ) || intval($this->options['cache_time']) == 0 ) {
				
					$resource = curl_init();
					curl_setopt( $resource, CURLOPT_URL, "$root_server/client_interface/json/?switcher=GetServiceBodies" );			
					curl_setopt($resource, CURLOPT_RETURNTRANSFER, 1); 
					curl_setopt($resource, CURLOPT_CONNECTTIMEOUT, 10);
					curl_setopt($resource, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)"); 
					$results = curl_exec ( $resource );
					curl_close ( $resource );
					$result = json_decode($results,true);

					if ( intval($this->options['cache_time']) > 0 ) {

						set_transient( $transient_key, $result, intval($this->options['cache_time']) * HOUR_IN_SECONDS );
						
					}
					
				}

				if ( $source == 'dropdown') {
					$unique_areas = array();
					foreach ($result as $key => $value) {
						$unique_areas[] = $value['name'].','.$value['id'].','.$value['parent_id'];
					}
				} else {
					$unique_areas = array();
					foreach ($result as $key => $value) {
						$unique_areas[$value['id']] = $value['name'];
					}
				}
					
				return $unique_areas;
			}

			function admin_menu_link()

			{

				//If you change this from add_options_page, MAKE SURE you change the filter_plugin_actions function (below) to

				//reflect the page file name (i.e. - options-general.php) of the page your plugin is under!

				add_options_page('BMLT Tabs', 'BMLT Tabs', 'activate_plugins', basename(__FILE__), array(&$this, 'admin_options_page'));

				add_filter('plugin_action_links_' . plugin_basename(__FILE__), array(&$this, 'filter_plugin_actions'), 10, 2);

			}

			/**

			* Adds settings/options page

			*/

			function admin_options_page()

			{

				if ( !isset($_POST['bmlttabssave']) ) {
				
					$_POST['bmlttabssave'] = false;
					
				}
				
				if ( !isset($_POST['delete_cache_action']) ) {
				
					$_POST['delete_cache_action'] = false;
					
				}
				
				if ($_POST['bmlttabssave']) {

					if (!wp_verify_nonce($_POST['_wpnonce'], 'bmlttabsupdate-options'))

						die('Whoops! There was a problem with the data you posted. Please go back and try again.');

					$this->options['cache_time'] = $_POST['cache_time'];   
					$this->options['root_server'] = $_POST['root_server'];   
					$this->options['service_body_1'] = $_POST['service_body_1'];

					$this->save_admin_options();
					
					set_transient( 'admin_notice', 'Please put down your weapon. You have 20 seconds to comply.' );

					echo '<div class="updated"><p>Success! Your changes were successfully saved!</p></div>';
					
					if ( intval($this->options['cache_time']) == 0 ) {

						$num = $this->delete_transient_cache();

						if ( $num > 0 ) {
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

					set_transient( 'admin_notice', 'Please put down your weapon. You have 20 seconds to comply.' );

					if ( $num > 0 ) {
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

						<div style="margin-top: 20px; padding: 0 15px;" class="postbox">
								<h3>Default BMLT Root Server URL</span></h3>
								<p>Example: http://naflorida.org/bmlt_server</p>
								<ul>
									<li>
										<label for="root_server">Root Server: </label>
										<input class="bmlt-input" id="root_server" type="text" size="80" name="root_server" value="<?php echo $this->options['root_server'] ;?>" />
									</li>
								</ul>
						</div>
						
						<div style="padding: 0 15px;" class="postbox">
							
							<h3>Default Service Body</span></h3>
							<p>This service body will be used when no service body is defined in the shortcode.</p>
							<ul>
								<li>
									<label for="service_body_1">Service Body: </label>
									<select style="display:inline;" onchange="getValueSelected()" id="service_body_1" name="service_body_1">
									<? $unique_areas = $this->get_areas($this->options['root_server'], 'dropdown'); ?>
									<? asort($unique_areas); ?>
									<? foreach($unique_areas as $key=>$unique_area){ ?>

										<? $area_data = explode(',',$unique_area); ?>
										<? $area_name = $area_data[0]; ?>
										<? $area_id = $area_data[1]; ?>
										<? $area_parent = $area_data[2]; ?>
							
										<? if ( $unique_area == $this->options['service_body_1'] ) { ?>
											<option selected="selected" value="<?= $unique_area ?>"><?= $area_name ?></option>
										<? } else { ?>
											<option value="<?= $unique_area ?>"><?= $area_name ?></option>
										<? } ?>
									<? } ?>
									</select>							
									<div style="margin-left: 5px; display:inline;">Shortcode: <input class="bmlt-input" size="41" type="text" id="txtSelectedValues1" readonly></div>
									<div style="margin-left: 5px; display:inline;">Service Body Parent: <input class="bmlt-input" size="2" type="text" id="txtSelectedValues2" readonly></div>
								</li> 
							</ul>
							
						</div>

						<div style="padding: 0 15px;" class="postbox">
							
							<h3>Meeting Cache (<?= $this->count_transient_cache(); ?> Cached Entries)</h3>
							
							<? global $_wp_using_ext_object_cache; ?>

							<? if ( $_wp_using_ext_object_cache ) { ?>
								<p>This site is using an external object cache.</p>
							<? } ?>
							<p>Meeting data is cached (as database transient) to load BMLT Tabs faster.</p>
							<ul>
								<li>
									<label for="cache_time">Cache Time: </label>
									<input class="bmlt-input" id="cache_time" onKeyPress="return numbersonly(this, event)" type="number" min="0" max="999" size="3" maxlength="3" name="cache_time" value="<?php echo $this->options['cache_time'] ;?>" />&nbsp;&nbsp;<i>0 - 999 Hours (0 = disable and delete cache)</i>&nbsp;&nbsp;
								</li>
							</ul>
							<p><i>The DELETE CACHE button is useful for the following:
							<ol>
							<li>After updating meetings in BMLT.</li>
							<li>Meeting information is not correct on the website.</li>
							<li>Changing the Cache Time value.</li>
							</ol>
							</i>
							</p>
							
						</div>

						<input type="submit" value="SAVE CHANGES" name="bmlttabssave" class="button-primary" />					

						</form>
									
						<form style="display:inline!important;" method="post">
							<?php wp_nonce_field( 'delete_cache_nonce' ); ?>
							<input style="color: #000;" type="submit" value="DELETE CACHE" name="delete_cache_action" class="button-primary" />					
						</form>
						
						<SCRIPT TYPE="text/javascript">
						function numbersonly(myfield, e, dec)
						{
						var key;
						var keychar;

						if (window.event)
						   key = window.event.keyCode;
						else if (e)
						   key = e.which;
						else
						   return true;
						keychar = String.fromCharCode(key);

						// control keys
						if ((key==null) || (key==0) || (key==8) || 
							(key==9) || (key==13) || (key==27) )
						   return true;

						// numbers
						else if ((("0123456789").indexOf(keychar) > -1))
						   return true;

						// decimal point jump
						else if (dec && (keychar == "."))
						   {
						   myfield.form.elements[dec].focus();
						   return false;
						   }
						else
						   return false;
						}

						//-->
						</SCRIPT>

						<script>
						function getValueSelected(){
							var x = document.bmlt_tabs_options.service_body_1.selectedIndex;
							var res = document.bmlt_tabs_options.service_body_1.options[x].value.split(",");
							document.getElementById("txtSelectedValues1").value = '[bmlt_tabs service_body="' + res[1] + '"] or [bmlt_tabs]';
							document.getElementById("txtSelectedValues2").value = res[2];
						};
						getValueSelected();
						</script>
							
						<br/><br/>
						
						<div style="padding: 0 15px;" class="postbox">
						
							<h3>BMLT Tabs Shortcode Usage</h3>

							<p>Insert the following shortcodes into a page.</p>

							<p><strong>[bmlt_tabs]</strong></p>

							<p><strong>[count_meetings]</strong></p>

							<p><strong>[count_groups]</strong></p>
							
							<p><strong>Example: We now have [count_groups] groups with [count_meetings] per week.</strong></p>

							<p><i>Detailed instructions for each shortcode are provided as follows.</i></p>
							
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

							<p><i>You cannot combine the service_body and parent_service_body parameters.</i></p>
							
						</div>

						<div style="padding: 0 15px;" class="postbox">
						
							<h3>Root Server</h3>

							<p>Use a different Root Server.</p>

							<p><strong>[bmlt_tabs service_body="2" root_server="http://naflorida.org/bmlt_server"]</strong></p>

							<p>Useful for displaying meetings from a different root server.</p>
							
							<i><p>Hint: To find service body IDs enter the different root server into the "BMLT Root Server URL" box and save.</p>
							
							<p>Remember to enter your current Root Server back into the "BMLT Root Server URL".</p></i>
							
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

							<p><i>City button will be included when view_by = "city" (include_city_button will be set to "1").</i></p>

						</div>

						<div style="padding: 0 15px;" class="postbox">

							<h3>Exclude Weekday Button</h3>

							<p>With this parameter you can exclude the Weekday button.</p>

							<p><strong>[bmlt_tabs include_weekday_button="0|1"]</strong></p>

							<p>0 = exclude Weekday button</p>

							<p>1 = include Weekday button (default)</p>

							<p><i>Weekday button will be included when view_by = "weekday" (include_weekday_button will be set to "1").</i></p>

						</div>

						<div style="padding: 0 15px;" class="postbox">

							<h3>Tabs or No Tabs</h3>

							<p>With this parameter you can display meetings without weekday tabs.</p>

							<p><strong>[bmlt_tabs service_body="2" has_tabs="0|1"]</strong></p>

							<p>0 = display meetings without tabs</p>

							<p>1 = display meetings with tabs (default)</p>
							
							<p><i>Hiding weekday tabs is useful for smaller service bodies.</i></p>

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

							<p><strong>[meeting_count]</strong> <i>Will use the default service body (above).</i></p>					

							<p><strong>[meeting_count service_body="2,3,4"]</strong></p>

							<p><strong>[meeting_count service_body_parent="1,2,3"]</strong></p>

							<p>Will return the number of meetings in one or more BMLT parent service bodies.</p>

							<p><strong>[meeting_count service_body="2" subtract="3"]</strong></p>

							<p>subtract = number of meetings to subtract from total meetings (optional)</p>

							<p><i>Subtract is useful when you are using BMLT for subcommittee meetings and do want to count those meetings.</i></p>

						</div>

						<div style="padding: 0 15px;" class="postbox">

							<h3>Group Count</h3>

							<p>Will return the number of Groups for one or more BMLT service bodies.</p>

							<p><strong>[group_count]</strong> <i>Will use the default service body (above).</i></p>					
							
							<p><strong>[group_count service_body="2,3,4"]</strong></p>

							<p><strong>[group_count service_body_parent="1,2,3"]</strong></p>

							<p>Will return the number of Groups in one or more BMLT parent service bodies.</p>
					
						</div>
					
				</div>

				<?

			}

			/**
			 * Deletes transient cache
			 */
			function delete_transient_cache() {
					
				global $wpdb, $_wp_using_ext_object_cache;;
				
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
				
				return $num1 + $num2;
				
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