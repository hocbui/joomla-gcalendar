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
 * This code was based on Allon Moritz's great work in the companion
 * upcoming module. 
 * 
 * @author Eric Horne
 * @copyright 2009 Eric Horne 
 * @version $Revision: 1.0.0 $
 */

defined('_JEXEC') or die('Restricted access');


require_once (JPATH_ADMINISTRATOR.DS.'components'.DS.'com_gcalendar'.DS.'util.php');
require_once (JPATH_ADMINISTRATOR.DS.'components'.DS.'com_gcalendar'.DS.'dbutil.php');

date_default_timezone_set(GCalendarUtil::getComponentParameter('timezone'));

class GCalendarNext {

	var $params = "";

	function GCalendarNext(&$params) {
		$this->params = $params;
	}


	function getCalendarItems() {
		$params = $this->params;

		GCalendarUtil::ensureSPIsLoaded();
		$calendarids = $params->get('calendarids');
		$results = GCalendarDBUtil::getCalendars($calendarids);
		if(empty($results))
			JError::raiseError(500, 'The selected calendar(s) were not found in the database.');
		$values = array();
		foreach ($results as $result) {
			if(!empty($result->calendar_id)){
				$sortOrder = $params->get( 'order', 1 )==1;

				$feed = new SimplePie_GCalendar();
				$feed->set_show_past_events($params->get('past_events', TRUE));
				$feed->set_start_date(strtotime($params->get('startdate', "-1 day")));
				$feed->set_end_date(strtotime($params->get('enddate', "+10 years")));
				$feed->set_sort_ascending(TRUE);
				$feed->set_orderby_by_start_date($sortOrder);
				$feed->set_expand_single_events(TRUE);
				$feed->enable_order_by_date(TRUE);
				$feed->enable_cache(FALSE);
				$feed->set_max_events($params->get("max_events", 10));
				$feed->set_timezone(GCalendarUtil::getComponentParameter('timezone'));
				$feed->set_cal_language(GCalendarUtil::getFrLanguage());
				$feed->put('gcid',$result->id);
				$feed->put('gcname',$result->name);
				$feed->put('gccolor',$result->color);
				$url = SimplePie_GCalendar::create_feed_url($result->calendar_id, $result->magic_cookie);

				$feed->set_feed_url($url);
					
				// Initialize the feed so that we can use it.
				$feed->init();

				if ($feed->error()){
					JError::raiseWarning(500, 'Simplepie detected an error. Please run the <a href=<"administrator/components/com_gcalendar/libraries/sp-gcalendar/sp_compatibility_test.php">compatibility utility</a>.', $feed->error());
				}

				// Make sure the content is being served out to the browser properly.
				$feed->handle_content_type();

				$values = array_merge($values, $feed->get_items());
			}
		}

		// we sort the array based on the event compare function
		usort($values, array("SimplePie_Item_GCalendar", "compare"));


		$events = array_filter($values, array($this, "filter"));

		$offset = $params->get('offset', 0);
		$numevents = $params->get('count', 1);

		$events = array_slice($events, $offset, $numevents, false);

		//return the feed data structure for the template
		return $events;
	}

	function filter($event) {
		$filter = $this->params->get('event_filter', '.*');

		if (!preg_match('/'.$filter.'/', $event->get_title())) {
			return false;
		}
		if ($event->get_end_date() > time()) {
			return true;
		}

		return false;
	}		
}
?>

