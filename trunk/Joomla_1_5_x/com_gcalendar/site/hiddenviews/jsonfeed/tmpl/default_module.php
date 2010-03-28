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
$SECSINDAY=86400;

$startDate = JRequest::getVar('start', null);
$endDate = JRequest::getVar('end', null);

$days = ($endDate - $startDate)/$SECSINDAY;
$counter = 0;
$day = strftime('%d', $startDate);
$month = strftime('%m', $startDate);
$year = strftime('%Y', $startDate);
while ($days > 0) {
	$result = array();
	$requestedDayStart = mktime(0, 0, 0, $month, $day, $year);
	$requestedDayEnd = $requestedDayStart + $SECSINDAY;
	foreach ($this->calendars as $calendar){
		$items = $calendar->get_items();
		foreach ($items as $item) {
			if($requestedDayStart <= $item->get_start_date()
			&& $item->get_start_date() < $requestedDayEnd){
				$result[] = $item;
			}else if($requestedDayStart < $item->get_end_date()
			&& $item->get_end_date() <= $requestedDayEnd){
				$result[] = $item;
			}else if($item->get_start_date() <= $requestedDayStart
			&& $requestedDayEnd <= $item->get_end_date()){
				$result[] = $item;
			}
		}
	}
	$data[] = array(
			'id' => time(),
			'title' => count($result).'events',
			'start' => $event->get_start_date(),
			'end' => $event->get_end_date(),
			'url' => JRoute::_(JURI::base().'index.php?option=com_gcalendar&view=event&eventID='.$event->get_id().'&start='.$event->get_start_date().'&end='.$event->get_end_date().'&gcid='.$calendar->get('gcid')).$itemID,
			'className' => "gcal-event_gccal_".$calendar->get('gcid'),
			'allDay' => $event->get_day_type() == $event->SINGLE_WHOLE_DAY || $event->get_day_type() == $event->MULTIPLE_WHOLE_DAY,
			'description' => $temp_event
	);
	$days--;
	$day++;
}
echo json_encode($data);
?>
