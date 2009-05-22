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

defined('_JEXEC') or die('Restricted access');

require_once (JPATH_ADMINISTRATOR.DS.'components'.DS.'com_gcalendar'.DS.'libraries'.DS.'rss-calendar'.DS.'classes'.DS.'DefaultCalendarConfig.php');

class ModGCalendarHelper {

	function getCalendarConfig($calendarids) {
		$calendarConfig = new ModCalendarConfig($calendarids);
		return $calendarConfig;
	}
}

class ModCalendarConfig extends DefaultCalendarConfig{
	var $calendarids;

	function ModCalendarConfig($calendarids){
		$this->calendarids = $calendarids;
	}

	function getGoogleCalendarFeeds($start, $end) {
		$condition = '';
		$calendarids = $this->calendarids;
		if(!empty($calendarids)){
			if(is_array($calendarids)) {
				$condition = 'id IN ( ' . implode( ',', $calendarids ) . ')';
			} else {
				$condition = 'id = '.$calendarids;
			}
		}else
		return array();

		$db =& JFactory::getDBO();
		$query = "SELECT id, calendar_id, name, color, magic_cookie  FROM #__gcalendar where ".$condition;
		$db->setQuery( $query );
		$results = $db->loadObjectList();
		if(empty($results))
		return array();

		$calendars = array();
		foreach ($results as $result) {
			if(!empty($result->calendar_id)){
				$feed = new SimplePie_GCalendar();
				$feed->set_show_past_events(FALSE);
				$feed->set_sort_ascending(TRUE);
				$feed->set_orderby_by_start_date(TRUE);
				$feed->set_expand_single_events(TRUE);
				$feed->enable_order_by_date(FALSE);
				$feed->enable_cache(FALSE);
				$feed->set_start_date($start);
				$feed->set_end_date($end);
				$feed->set_max_events(100);
				$feed->put('gcid',$result->id);
				$feed->put('gccolor',$result->color);
				$feed->set_cal_language(GCalendarUtil::getFrLanguage());

				$url = SimplePie_GCalendar::create_feed_url($result->calendar_id, $result->magic_cookie);
				$feed->set_feed_url($url);
				$feed->init();
				$feed->handle_content_type();
				$calendars[] = $feed;
			}
		}
		return $calendars;
	}
}
?>
