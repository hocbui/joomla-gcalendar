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
 * @version $Revision: 2.1.1 $
 */

defined('_JEXEC') or die('Restricted access');
$data = array();
$params = $this->params;
$dateformat = $params->get('tooltipDateFormat', '%d.%m.%Y');
$timeformat = $params->get('tooltipTimeFormat', '%H:%M');
$event_display = $params->get('toolTipText', '');
$SECSINDAY=86400;

foreach ($this->calendars as $calendar){
	$items = $calendar->get_items();
	foreach ($items as $event) {
		$feed = $event->get_feed();
		$tz = GCalendarUtil::getComponentParameter('timezone');
		if($tz == ''){
			$tz = $feed->get_timezone();
		}

		$itemID = GCalendarUtil::getItemId($feed->get('gcid'));
		if(!empty($itemID)){
			$itemID = '&Itemid='.$itemID;
		}else{
			$menu=JSite::getMenu();
			$activemenu=$menu->getActive();
			if($activemenu != null)
			$itemID = '&Itemid='.$activemenu->id;
		}

		$startDate = strftime($dateformat, $event->get_start_date());
		$startTime = strftime($timeformat, $event->get_start_date());
		$endDate = strftime($dateformat, $event->get_end_date());
		$endTime = strftime($timeformat, $event->get_end_date());

		$temp_event=$event_display;

		switch($event->get_day_type()){
			case $event->SINGLE_WHOLE_DAY:
				$temp_event=str_replace("{startdate}",$startDate,$temp_event);
				$temp_event=str_replace("{starttime}","",$temp_event);
				$temp_event=str_replace("{dateseparator}","",$temp_event);
				$temp_event=str_replace("{enddate}","",$temp_event);
				$temp_event=str_replace("{endtime}","",$temp_event);
				break;
			case $event->SINGLE_PART_DAY:
				$temp_event=str_replace("{startdate}",$startDate,$temp_event);
				$temp_event=str_replace("{starttime}",$startTime,$temp_event);
				$temp_event=str_replace("{dateseparator}","-",$temp_event);
				$temp_event=str_replace("{enddate}","",$temp_event);
				$temp_event=str_replace("{endtime}",$endTime,$temp_event);
				break;
			case $event->MULTIPLE_WHOLE_DAY:
				$tmp = JFactory::getDate();
				$endDate = strftime($dateformat, $event->get_end_date() - $SECSINDAY);
				$temp_event=str_replace("{startdate}",$startDate,$temp_event);
				$temp_event=str_replace("{starttime}","",$temp_event);
				$temp_event=str_replace("{dateseparator}","-",$temp_event);
				$temp_event=str_replace("{enddate}",$endDate,$temp_event);
				$temp_event=str_replace("{endtime}","",$temp_event);
				break;
			case $event->MULTIPLE_PART_DAY:
				$temp_event=str_replace("{startdate}",$startDate,$temp_event);
				$temp_event=str_replace("{starttime}",$startTime,$temp_event);
				$temp_event=str_replace("{dateseparator}","-",$temp_event);
				$temp_event=str_replace("{enddate}",$endDate,$temp_event);
				$temp_event=str_replace("{endtime}",$endTime,$temp_event);
				break;
		}

		if (substr_count($temp_event, '"{description}"')){
			// If description is in html attribute
			$desc = htmlspecialchars(str_replace('"',"'",$event->get_description()));
		}else{
			//Make any URLs used in the description also clickable
			$desc = preg_replace('(((f|ht){1}tp://)[-a-zA-Z0-9@:%_\+.~#?,//=&;]+)','<a href="\\1">\\1</a>', $event->get_description());
		}

		$temp_event=str_replace("{title}",$event->get_title(),$temp_event);
		$temp_event=str_replace("{description}",$desc,$temp_event);
		$temp_event=str_replace("{where}",$event->get_location(),$temp_event);
		$temp_event=str_replace("{backlink}",JRoute::_('index.php?option=com_gcalendar&view=event&eventID='.$event->get_id().'&start='.$event->get_start_date().'&end='.$event->get_end_date().'&gcid='.$feed->get('gcid').$itemID),$temp_event);
		$temp_event=str_replace("{link}",$event->get_link().'&ctz='.$tz,$temp_event);
		$temp_event=str_replace("{maplink}","http://maps.google.com/?q=".urlencode($event->get_location()),$temp_event);
		$temp_event=str_replace("{calendarname}",$feed->get('gcname'),$temp_event);
		$temp_event=str_replace("{calendarcolor}",$feed->get('gccolor'),$temp_event);
		// Accept and translate HTML
		$temp_event = html_entity_decode($temp_event);

		$data[] = array(
			'id' => $event->get_id(),
			'title' => htmlspecialchars_decode($event->get_title()),
			'start' => $event->get_start_date(),
			'end' => $event->get_end_date(),
			'url' => JRoute::_(JURI::base().'index.php?option=com_gcalendar&view=event&eventID='.$event->get_id().'&start='.$event->get_start_date().'&end='.$event->get_end_date().'&gcid='.$calendar->get('gcid')).$itemID,
			'className' => "gcal-event_gccal_".$calendar->get('gcid'),
			'allDay' => $event->get_day_type() == $event->SINGLE_WHOLE_DAY || $event->get_day_type() == $event->MULTIPLE_WHOLE_DAY,
			'description' => $temp_event
		);
	}
}
echo json_encode($data);
?>
