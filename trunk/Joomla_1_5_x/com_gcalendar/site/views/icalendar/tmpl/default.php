<?php
/**
 * GCalendar is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * GCalendar is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GCalendar.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Allon Moritz
 * @copyright 2007-2009 Allon Moritz
 * @version $Revision: 2.1.0 $
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

if(!is_array($this->calendars)){
	echo JText::_( 'NO_CALENDAR' );
}else{
	function getFiles($calendars){
		$filePath = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_gcalendar'.DS.'libraries'.DS.'phpicalendar'.DS.'calendars';

		$googleHost = 'http://www.google.com/calendar/ical/';
		foreach($calendars as $calendar) {
			if($calendar->selected){
				$calurl = $googleHost.$calendar->calendar_id;
				if(!empty($calendar->magic_cookie)){
					$calurl .= '/private-'.$calendar->magic_cookie;
				}else{
					$calurl .= '/public';
				}
				$calurl .= '/basic.ics';

				if(function_exists('curl_exec')){
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_HEADER, 0);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //Set curl to return the data instead of printing it to the browser.
					curl_setopt($ch, CURLOPT_URL, $calurl);
					$data = curl_exec($ch);
					curl_close($ch);
				}else{
					$data = file_get_contents($calurl);
				}
				$fh = fopen($filePath.DS.$calendar->calendar_id.'basic.ics', 'w');
				fwrite($fh, $data);
				fclose($fh);
			}
		}
	}

	$cache = & JFactory::getCache();
	$cache->call('getFiles', $this->calendars );

	$configs = array(
	#     'language'             => 'Spanish',
	#     'default_cal'          => 'US Holidays',	   // Exact filename of calendar without .ics.
	#     'template'             => 'green',           // Template support: change this to have a different "skin" for your installation.
   'default_view'           => $this->params->get( 'default_view' ),           // Default view for calendars'     => 'day', 'week', 'month', 'year'
	#      'printview_default'    => 'yes',	           // Set print view as the default view. Uses'default_view (listed above).
	#     'gridLength'           => 10,                // Grid size in day and week views. Allowed values are 1,2,3,4,10,12,15,20,30,60. Default is 15
	#     'minical_view'         => 'current',	       // Where do the mini-calendars go when clicked?'     => 'day', 'week', 'month', 'current'
#     'allow_preferences'    => 'no', 
#     'month_locations'      => 'no',
#     'show_search'          => 'no',
#     'show_todos'           => 'no',
#     'show_completed'       => 'no',
	#     'allow_login'          => 'yes',	           // Set to yes to prompt for login to unlock calendars.
	#     'week_start_day'       => 'Monday',          // Day of the week your week starts on
	#     'week_length'          => '5',	           // Number of days to display in the week view
	#     'day_start'            => '0600',	           // Start time for day grid
	#     'day_end'              => '2000',	           // End time for day grid
	#      'event_download' => 'yes',
	'phpicalendar_publishing'=> 1,
	);
	?> 
	
	<iframe id="gcalendar_frame" src="administrator/components/com_gcalendar/libraries/phpicalendar/index.php"
	width="100%"
	height="500" align="top"
	frameborder="0"
	class="gcalendar<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
	<?php echo JText::_( 'NO_IFRAMES' ); ?> </iframe></div>

	<?php
}
?>