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
global $Itemid;
$data = array();
foreach ($this->calendars as $calendar){
	$items = $calendar->get_items();
	foreach ($items as $event) {
		$data[] = array(
			'id' => $event->get_id(),
			'title' => htmlspecialchars_decode($event->get_title()),
			'start' => $event->get_start_date(),
			'end' => $event->get_end_date(),
			'url' => JRoute::_(JURI::base().'index.php?option=com_gcalendar&view=event&eventID='.$event->get_id().'&start='.$event->get_start_date().'&end='.$event->get_end_date().'&gcid='.$calendar->get('gcid')).'&Itemid='.$Itemid,
			'className' => "gcal-event_gccal_".$calendar->get('gcid'),
			'allDay' => $event->get_day_type() == $event->SINGLE_WHOLE_DAY || $event->get_day_type() == $event->MULTIPLE_WHOLE_DAY
		);
	}
}
echo json_encode($data);
?>
