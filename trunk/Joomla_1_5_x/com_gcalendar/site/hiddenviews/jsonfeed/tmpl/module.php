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
$requestedDayStart = $startDate;
$requestedDayEnd = $requestedDayStart + $SECSINDAY;
$days = ($endDate - $startDate)/$SECSINDAY;

while ($days > 0) {
	$result = array();
	$linkID = '';

	foreach ($this->calendars as $calendar){
		$items = $calendar->get_items();
		foreach ($items as $item) {
			if($requestedDayStart <= $item->get_start_date()
			&& $item->get_start_date() < $requestedDayEnd){
				$result[] = $item;
				$linkID = GCalendarUtil::getItemId($calendar->get('gcid'));
			}else if($requestedDayStart < $item->get_end_date()
			&& $item->get_end_date() <= $requestedDayEnd){
				$result[] = $item;
				$linkID = GCalendarUtil::getItemId($calendar->get('gcid'));
			}else if($item->get_start_date() <= $requestedDayStart
			&& $requestedDayEnd <= $item->get_end_date()){
				$result[] = $item;
				$linkID = GCalendarUtil::getItemId($calendar->get('gcid'));
			}
		}
	}
	if(!empty($result)){
		$component	= &JComponentHelper::getComponent('com_gcalendar');
		$menu = &JSite::getMenu();
		$item = $menu->getItem($linkID);
		$url = '';
		if($item !=null){
			$backLinkView = $item->query['view'];
			$day = strftime('%d', $requestedDayStart);
			$month = strftime('%m', $requestedDayStart);
			$year = strftime('%Y', $requestedDayStart);
			$url = JRoute::_('index.php?option=com_gcalendar&view='.$backLinkView.'&Itemid='.$linkID.'#year='.$year.'&month='.$month.'&day='.$day.'&view=agendaDay');
		}
		$data[] = array(
			'id' => time(),
			'title' => '',
			'start' => $requestedDayStart,
		//			'end' => $requestedDayEnd - 10,
			'url' => $url,
			'className' => "gcal-module_event_gccal",
			'allDay' => true,
			'description' => sprintf(JText::_('MODULE_TEXT'), count($result))
		);
	}
	$requestedDayStart += $SECSINDAY;
	$requestedDayEnd = $requestedDayStart + $SECSINDAY;
	$days--;
}
echo json_encode($data);
?>
