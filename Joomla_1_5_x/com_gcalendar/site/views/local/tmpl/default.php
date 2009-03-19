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
	$calendarUrl = 'administrator/components/com_gcalendar/libraries/phpicalendar/week.php?cal=';
	$googleHost = 'http://www.google.com/calendar/ical/';
	foreach($this->calendars as $calendar) {
		if($calendar->selected){
			$calendarUrl = $calendarUrl.$googleHost.$calendar->calendar_id;
			if(!empty($calendar->magic_cookie)){
				$calendarUrl = $calendarUrl.'/private-'.$calendar->magic_cookie;
			}else{
				$calendarUrl = $calendarUrl.'/public';
			}
			$calendarUrl = $calendarUrl.'/basic.ics,';
		}
	}
	if(strrpos($calendarUrl, ',') === (strlen($calendarUrl)-1)){
		$calendarUrl = substr($calendarUrl,0,strlen($calendarUrl)-1);
	}
	?>
<iframe id="gcalendar_frame" src="<?php echo $calendarUrl; ?>"
	width="100%" height="500" align="top" frameborder="0"
	class="gcalendar<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
	<?php echo JText::_( 'NO_IFRAMES' ); ?> </iframe>

	<?php
}
?>