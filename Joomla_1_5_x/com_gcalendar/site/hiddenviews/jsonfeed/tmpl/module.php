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
 * @copyright 2007-2010 Allon Moritz
 * @since 2.2.0
 */

defined('_JEXEC') or die('Restricted access');
$data = array();
$SECSINDAY=86400;

$startDate = JRequest::getVar('start', null);
$endDate = JRequest::getVar('end', null);
$requestedDayStart = $startDate;
$requestedDayEnd = $requestedDayStart + $SECSINDAY;

while ($requestedDayStart < $endDate) {
	$result = array();
	$linkIDs = '';

	foreach ($this->calendars as $calendar){
		$calID = null;
		$items = $calendar->get_items();
		foreach ($items as $item) {
			if(($requestedDayStart <= $item->get_start_date() && $item->get_start_date() < $requestedDayEnd)
			|| ($requestedDayStart < $item->get_end_date() && $item->get_end_date() <= $requestedDayEnd)
			|| ($item->get_start_date() <= $requestedDayStart && $requestedDayEnd <= $item->get_end_date())){
				$result[] = $item;
				$calID = $calendar->get('gcid').',';
			}
		}
		if($calID != null)
		$linkIDs .= $calID;
	}
	if(!empty($result)){
		$linkIDs = trim($linkIDs, ",");
		$day = strftime('%d', $requestedDayStart);
		$month = strftime('%m', $requestedDayStart);
		$year = strftime('%Y', $requestedDayStart);
		$url = JRoute::_('index.php?option=com_gcalendar&view=day&gcids='.$linkIDs.'#year='.$year.'&month='.$month.'&day='.$day);
		$data[] = array(
			'id' => time(),
			'title' => '',
			'start' => $requestedDayStart,
		//			'end' => $requestedDayEnd - 10,
			'url' => $url,
		//			'className' => "gcal-module_event_gccal",
			'allDay' => true,
			'description' => sprintf(JText::_('MODULE_TEXT'), count($result))
		);
	}
	$requestedDayStart += $SECSINDAY;
	$requestedDayEnd = $requestedDayStart + $SECSINDAY;
}
echo json_encode($data);
?>
