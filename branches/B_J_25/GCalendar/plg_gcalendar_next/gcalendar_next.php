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
 * @copyright 2007-2011 Allon Moritz
 * @since 2.8.1
 */

defined('_JEXEC') or die();
jimport('joomla.plugin.plugin');

require_once (JPATH_ADMINISTRATOR.DS.'components'.DS.'com_gcalendar'.DS.'util.php');
require_once (JPATH_ADMINISTRATOR.DS.'components'.DS.'com_gcalendar'.DS.'libraries'.DS.'GCalendar'.DS.'GCalendarZendHelper.php');

class plgContentgcalendar_next extends JPlugin {

	public function onContentPrepare($context, &$article, &$params, $page = 0 ) {
		if (!$article->text) return;
		$calendarids = $this->params->get('calendarids');
		$results = GCalendarDBUtil::getCalendars($calendarids);
		if(empty($results)){
			return;
		}

		$maxEvents = $this->params->get('max_events', 10);
		$filter = $this->params->get('find', '');

		$values = array();
		foreach ($results as $result) {
			$events = GCalendarZendHelper::getEvents($result, null, null, $maxEvents, $filter);
			if(!empty($events)){
				foreach ($events as $event) {
					if(!($event instanceof GCalendar_Entry)){
						continue;
					}
					$values[] = $event;
				}
			}
		}

		usort($values, array("GCalendar_Entry", "compare"));
		$values = array_slice($values, 0, $maxEvents);

		$article->text = GCalendarUtil::renderEvents($values, $article->text, JComponentHelper::getParams('com_gcalendar'));
	}
}