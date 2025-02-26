<?php
/*
Plugin Name: HD Weather Widget by The Waypoint
Plugin URI: http://consultants.thewaypoint.com
Description: A weather widget designed for 331dpi Screens, It's HD!
Author: John Grefe - The Waypoint
Author URI: http://www.twitter.com/johngrefe
Version: 1.0
Stable: 1.0
Based On: Awesome Weather Widget by Hal Gatewood


FILTERS AVAILABLE:
waypoint_weather_cache 						= How many ses to cache weather: default 3600 (one hour).
waypoint_weather_error 						= Error message if weather is not found.
waypoint_weather_sizes 						= array of sizes for widget
waypoint_weather_extended_forecast_text 		= Change text of footer link


SHORTCODE USAGE
[waypoint-weather location="Long Beach, CA" units="F"]
[waypoint-weather location="London, UK" units="C" width=220]
*/


// SETTINGS
$waypoint_weather_sizes = apply_filters( 'waypoint_weather_sizes' , array( 'tall', 'wide' ) );
        


// HAS SHORTCODE
function waypoint_weather_wp_head( $posts ) 
{
	wp_enqueue_style( 'waypoint-weather', plugins_url( '/waypoint-weather.css', __FILE__ ) );
}
add_action('wp_enqueue_scripts', 'waypoint_weather_wp_head');


//THE SHORTCODE
add_shortcode( 'waypoint-weather', 'waypoint_weather_shortcode' );
function waypoint_weather_shortcode( $atts )
{
	return waypoint_weather_logic( $atts );	
}


// THE LOGIC
function waypoint_weather_logic( $atts )
{
	global $waypoint_weather_sizes;
	
	$rtn 				= "";
	$weather_data		= array();
	$location 			= isset($atts['location']) ? $atts['location'] : false;
	$size 				= (isset($atts['size']) AND $atts['size'] == "tall") ? 'tall' : 'wide';
	$units 				= (isset($atts['units']) AND strtoupper($atts['units']) == "C") ? "metric" : "imperial";
	$units_display		= $units == "metric" ? __('C', 'waypoint-weather') : __('F', 'waypoint-weather');
	$override_title 	= isset($atts['override_title']) ? $atts['override_title'] : false;
	$days_to_show 		= isset($atts['forecast_days']) ? $atts['forecast_days'] : 5;
	$show_stats 		= (isset($atts['hide_stats']) AND $atts['hide_stats'] == 1) ? 0 : 1;
	$show_link 			= (isset($atts['show_link']) AND $atts['show_link'] == 1) ? 1 : 0;
	$background			= isset($atts['background']) ? $atts['background'] : false;

	if( !$location ) { return waypoint_weather_error(); }
	
	
	//FIND AND CACHE CITY ID
	$city_id 						= false;
	$city_name_slug 				= sanitize_title( $location );
	$city_id_transient_name 		= 'waypoint-weather-cityid-' . $city_name_slug;
	$weather_transient_name 		= 'waypoint-weather-' . $units . '-' . $city_name_slug;

	if( get_transient( $city_id_transient_name ) )
	{
		$city_id = get_transient( $city_id_transient_name );
	}
	
	// NOT AN ElSE JUST IN CASE THE TRANSIENT 
	// HAS AN EMPTY CITY_ID FOR WHATEVER REASON
	if(!$city_id)
	{
		$city_ping = "http://api.openweathermap.org/data/2.1/find/name?q=" . $city_name_slug;
		$data = json_decode( file_get_contents( $city_ping ) );
	
		if( isset($data->message) AND $data->message == "not found" )
		{ 
			return waypoint_weather_error( __('City could not be found:' . $city_ping , 'waypoint-weather') ); 
		}
	
		if($data AND $data->list)
		{
		
			$city = $data->list[0];
			$city_id = $city->id;
		}
		
		if($city_id)
		{
			set_transient( $city_id_transient_name, $city_id, 2629743); // CACHE FOR A MONTH
		}		
	}
	
	// NO CITY ID
	if( !$city_id ) { return waypoint_weather_error( __('City could not be found', 'waypoint-weather') ); }
	
	if( get_transient( $weather_transient_name ) )
	{
		$weather_data = get_transient( $weather_transient_name );
	}

	
	if(!isset($weather_data['today']))
	{
		$weather_data['today'] 		= json_decode(file_get_contents("http://api.openweathermap.org/data/2.1/weather/city/" . $city_id . "?units=" . $units) );
		set_transient( $weather_transient_name, $weather_data, apply_filters( 'waypoint_weather_cache', 3600 ) ); // CACHE FOR AN HOUR
	}
	
	if(!isset($weather_data['forecast']) AND $days_to_show != "hide")
	{
		$weather_data['forecast'] 	= json_decode(file_get_contents("http://api.openweathermap.org/data/2.1/forecast/city/" . $city_id . "?mode=daily_compact&units=" . $units) );
		set_transient( $weather_transient_name, $weather_data, apply_filters( 'waypoint_weather_cache', 3600 ) ); // CACHE FOR AN HOUR
	}

	// NO WEATHER
	if( !$weather_data OR !$weather_data['today']) { return waypoint_weather_error(); }
	
	
	// TODAYS TEMPS
	$today 			= $weather_data['today'];
	$today_temp 	= (int) $today->main->temp;
	$today_high 	= (int) $today->main->temp_max;
	$today_low 		= (int) $today->main->temp_min;
/*start hacking*/
	$feels          = (int) $today->weather[0]->id;
	
	
	// LOADS BACKGROUND BASED ON FEELS, I KNOW THAT FEEL BRO

	$bg_color = "feel{$feels}";
	
	// DATA
	$header_title = $override_title ? $override_title : $today->name;
	
	$today->main->humidity = (int) $today->main->humidity;
	$today->wind->speed = (int) $today->wind->speed;
	
	$wind_label = array ( 
							__('N', 'waypoint-weather'),
							__('NNE', 'waypoint-weather'), 
							__('NE', 'waypoint-weather'),
							__('ENE', 'waypoint-weather'),
							__('E', 'waypoint-weather'),
							__('ESE', 'waypoint-weather'),
							__('SE', 'waypoint-weather'),
							__('SSE', 'waypoint-weather'),
							__('S', 'waypoint-weather'),
							__('SSW', 'waypoint-weather'),
							__('SW', 'waypoint-weather'),
							__('WSW', 'waypoint-weather'),
							__('W', 'waypoint-weather'),
							__('WNW', 'waypoint-weather'),
							__('NW', 'waypoint-weather'),
							__('NNW', 'waypoint-weather')
						);
						
	$wind_direction = $wind_label[ fmod((($today->wind->deg + 11) / 22.5),16) ];
	
	$show_stats_class = ($show_stats) ? "awe_with_stats" : "awe_without_stats";
	
	if($background) $bg_color = "darken";
	
	// DISPLAY WIDGET	
	$rtn .= "
	
		<div id=\"waypoint-weather-{$city_name_slug}\" class=\"waypoint-weather-wrap awecf {$bg_color} {$show_stats_class} awe_{$size}\">
	";


	if($background) 
	{ 
		$rtn .= "<div class=\"waypoint-weather-cover\" style='background: url($background) no-repeat;'>";
		$rtn .= "<div class=\"waypoint-weather-darken\">";
	}

	$rtn .= "
<br>
			<div class=\"waypoint-weather-header\">{$header_title}</div>
			
			<div class=\"waypoint-weather-current-temp\">
				$today_temp<sup>{$units_display}</sup>
			</div> <!-- /.waypoint-weather-current-temp -->
	";	
	
	if($show_stats)
	{
		$rtn .= "
				<div class=\"waypoint-weather-todays-stats\">
					<div class=\"awe_desc\">{$today->weather[0]->description}</div>
					<div class=\"awe_humidty\">humidity: {$today->main->humidity}% </div>
					<div class=\"awe_wind\">wind: {$today->wind->speed}mph {$wind_direction}</div>
					<div class=\"awe_highlow\"> H {$today_high} &bull; L {$today_low} </div>	
				</div> <!-- /.waypoint-weather-todays-stats -->
<br>
<br>
<br>
		";
	}

	if($days_to_show != "hide")
	{
		$rtn .= "<div class=\"waypoint-weather-forecast awe_days_{$days_to_show} awecf\">";
		$c = 1;
		$dt_today = date('Ymd');
		$forecast = $weather_data['forecast'];
		$days_to_show = (int) $days_to_show;
		
		foreach( (array) $forecast->list as $forecast )
		{
			if( $dt_today > date('Ymd', $forecast->dt)) continue;
			
			$forecast->temp = (int) $forecast->temp;
			$day_of_week = date('D', $forecast->dt);
			$rtn .= "
				<div class=\"waypoint-weather-forecast-day\">
					<div class=\"waypoint-weather-forecast-day-temp\">{$forecast->temp}<sup>{$units_display}</sup></div>
					<div class=\"waypoint-weather-forecast-day-abbr\">$day_of_week</div>
				</div>
			";
			if($c == $days_to_show) break;
			$c++;
		}
		$rtn .= " </div> <!-- /.waypoint-weather-forecast -->";
	}
	
	if($show_link AND $city_id)
	{
		$show_link_text = apply_filters('waypoint_weather_extended_forecast_text' , "extended forecast" );

		$rtn .= "<div class=\"waypoint-weather-more-weather-link\">";
		$rtn .= "<a href=\"http://openweathermap.org/city/{$city_id}\" target=\"_blank\">{$show_link_text}</a>";		
		$rtn .= "</div> <!-- /.waypoint-weather-more-weather-link -->";
	}
	
	if($background) 
	{ 
		$rtn .= "</div> <!-- /.waypoint-weather-cover -->";
		$rtn .= "</div> <!-- /.waypoint-weather-darken -->";
	}
	
	
	$rtn .= "</div> <!-- /.waypoint-weather-wrap -->";
	return $rtn;
}


// RETURN ERROR
function waypoint_weather_error( $msg = false )
{
	if(!$msg) $msg = __('No weather information available', 'waypoint-weather');
	return apply_filters( 'waypoint_weather_error', "<!-- AWESOME WEATHER ERROR: " . $msg . " -->" );
}



// TEXT BLOCK WIDGET
class WaypointWeatherWidget extends WP_Widget 
{
	function WaypointWeatherWidget() { parent::WP_Widget(false, $name = 'HD Weather Widget by The Waypoint'); }

    function widget($args, $instance) 
    {	
        extract( $args );
        
        $location 			= isset($instance['location']) ? $instance['location'] : false;
        $override_title 	= isset($instance['override_title']) ? $instance['override_title'] : false;
        $units 				= isset($instance['units']) ? $instance['units'] : false;
        $size 				= isset($instance['size']) ? $instance['size'] : false;
        $forecast_days 		= isset($instance['forecast_days']) ? $instance['forecast_days'] : false;
        $hide_stats 		= (isset($instance['hide_stats']) AND $instance['hide_stats'] == 1) ? 1 : 0;
        $show_link 			= (isset($instance['show_link']) AND $instance['show_link'] == 1) ? 1 : 0;
        $background			= isset($instance['background']) ? $instance['background'] : false;

		echo $before_widget;
		echo waypoint_weather_logic( array( 'location' => $location, 'override_title' => $override_title, 'size' => $size, 'units' => $units, 'forecast_days' => $forecast_days, 'hide_stats' => $hide_stats, 'show_link' => $show_link, 'background' => $background ));
		echo $after_widget;
    }
 
    function update($new_instance, $old_instance) 
    {		
		$instance = $old_instance;
		$instance['location'] 			= strip_tags($new_instance['location']);
		$instance['override_title'] 	= strip_tags($new_instance['override_title']);
		$instance['units'] 				= strip_tags($new_instance['units']);
		$instance['size'] 				= strip_tags($new_instance['size']);
		$instance['forecast_days'] 		= strip_tags($new_instance['forecast_days']);
		$instance['hide_stats'] 		= strip_tags($new_instance['hide_stats']);
		$instance['show_link'] 			= strip_tags($new_instance['show_link']);
		$instance['background'] 		= strip_tags($new_instance['background']);
        return $instance;
    }
 
    function form($instance) 
    {	
    	global $waypoint_weather_sizes;
    	
        $location 			= isset($instance['location']) ? esc_attr($instance['location']) : "";
        $override_title 	= isset($instance['override_title']) ? esc_attr($instance['override_title']) : "";
        $selected_size 		= isset($instance['size']) ? esc_attr($instance['size']) : "wide";
        $units 				= isset($instance['units']) ? esc_attr($instance['units']) : "imperial";
        $forecast_days 		= isset($instance['forecast_days']) ? esc_attr($instance['forecast_days']) : 5;
        $hide_stats 		= (isset($instance['hide_stats']) AND $instance['hide_stats'] == 1) ? 1 : 0;
        $show_link 			= (isset($instance['show_link']) AND $instance['show_link'] == 1) ? 1 : 0;
        $background			= isset($instance['background']) ? esc_attr($instance['background']) : "";
        
        ?>
        <p>
          <label for="<?php echo $this->get_field_id('location'); ?>"><?php _e('Location:'); ?></label> 
          <input class="widefat" id="<?php echo $this->get_field_id('location'); ?>" name="<?php echo $this->get_field_name('location'); ?>" type="text" value="<?php echo $location; ?>" /><br /><a href="http://openweathermap.org/" target="_blank">Use this link to find your city FIRST.</a>
        </p>
                
        <p>
          <label for="<?php echo $this->get_field_id('override_title'); ?>"><?php _e('Override Title:'); ?></label> 
          <input class="widefat" id="<?php echo $this->get_field_id('override_title'); ?>" name="<?php echo $this->get_field_name('override_title'); ?>" type="text" value="<?php echo $override_title; ?>" />
        </p>
                
        <p>
          <label for="<?php echo $this->get_field_id('units'); ?>"><?php _e('Units:'); ?></label>  &nbsp;
          <input id="<?php echo $this->get_field_id('units'); ?>" name="<?php echo $this->get_field_name('units'); ?>" type="radio" value="imperial" <?php if($units == "imperial") echo ' checked="checked"'; ?> /> F &nbsp; &nbsp;
          <input id="<?php echo $this->get_field_id('units'); ?>" name="<?php echo $this->get_field_name('units'); ?>" type="radio" value="metric" <?php if($units == "metric") echo ' checked="checked"'; ?> /> C <br />This feature doesn't work, use the shortcode please <a href="http://wordpress.org/plugins/waypoint-hd-weather-widget/">- Documentation -</a>
        </p>
        
		<p>
          <label for="<?php echo $this->get_field_id('size'); ?>"><?php _e('Size:'); ?></label> 
          <select class="widefat" id="<?php echo $this->get_field_id('size'); ?>" name="<?php echo $this->get_field_name('size'); ?>">
          	<?php foreach($waypoint_weather_sizes as $size) { ?>
          	<option value="<?php echo $size; ?>"<?php if($selected_size == $size) echo " selected=\"selected\""; ?>><?php echo $size; ?></option>
          	<?php } ?>
          </select>
		</p>
        
		<p>
          <label for="<?php echo $this->get_field_id('forecast_days'); ?>"><?php _e('Forecast:'); ?></label> 
          <select class="widefat" id="<?php echo $this->get_field_id('forecast_days'); ?>" name="<?php echo $this->get_field_name('forecast_days'); ?>">
          	<option value="5"<?php if($forecast_days == 5) echo " selected=\"selected\""; ?>>5 Days</option>
          	<option value="4"<?php if($forecast_days == 4) echo " selected=\"selected\""; ?>>4 Days</option>
          	<option value="3"<?php if($forecast_days == 3) echo " selected=\"selected\""; ?>>3 Days</option>
          	<option value="2"<?php if($forecast_days == 2) echo " selected=\"selected\""; ?>>2 Days</option>
          	<option value="1"<?php if($forecast_days == 1) echo " selected=\"selected\""; ?>>1 Days</option>
          	<option value="hide"<?php if($forecast_days == 'hide') echo " selected=\"selected\""; ?>>Don't Show</option>
          </select>
		</p>
		
        <p>
          <label for="<?php echo $this->get_field_id('background'); ?>"><?php _e('Background Image:'); ?></label> 
          <input class="widefat" id="<?php echo $this->get_field_id('background'); ?>" name="<?php echo $this->get_field_name('background'); ?>" type="text" value="<?php echo $background; ?>" />
        </p>
		
        <p>
          <label for="<?php echo $this->get_field_id('hide_stats'); ?>"><?php _e('Hide Stats:'); ?></label>  &nbsp;
          <input id="<?php echo $this->get_field_id('hide_stats'); ?>" name="<?php echo $this->get_field_name('hide_stats'); ?>" type="checkbox" value="1" <?php if($hide_stats) echo ' checked="checked"'; ?> />
        </p>
		
        <p>
          <label for="<?php echo $this->get_field_id('show_link'); ?>"><?php _e('Link to OpenWeatherMap:'); ?></label>  &nbsp;
          <input id="<?php echo $this->get_field_id('show_link'); ?>" name="<?php echo $this->get_field_name('show_link'); ?>" type="checkbox" value="1" <?php if($show_link) echo ' checked="checked"'; ?> />
        </p>  
	
        <?php 
    }
}

add_action( 'widgets_init', create_function('', 'return register_widget("WaypointWeatherWidget");') );



