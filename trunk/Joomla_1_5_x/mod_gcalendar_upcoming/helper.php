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
 * @version $Revision: 2.0.1 $
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class modGcalendarUpcomingHelper {
	function getCalendarItems(&$params) {
		$calendarids = $params->get( 'calendarids', NULL );
		if(empty($calendarids)) return array(JText::_("CALENDAR_NO_DEFINED").$calendarids.'allon',NULL);

		if( is_array( $calendarids ) ) {
			$condition = 'id IN ( ' . implode( ',', $calendarids ) . ')';
		} else {
			$condition = 'id = '.$calendarids;
		}

		$db = &JFactory::getDBO();

		$query = "SELECT id,calendar_id, magic_cookie FROM #__gcalendar where ".$condition;
		$db->setQuery( $query );
		$results = $db->loadObjectList();
		if(empty($results))
		return array(JText::_("CALENDAR_NOT_FOUND"),NULL);

		$values = array();
		foreach ($results as $result) {
			if(!empty($result->calendar_id)){
				$feed = modGcalendarUpcomingHelper::create_gc_feed($params);
				$feed->put('gcid',$result->id);
				$url = SimplePie_GCalendar::create_feed_url($result->calendar_id, $result->magic_cookie);
				$feed->set_cal_language(GCalendarUtil::get_fr_language());

				$feed->set_feed_url($url);
					
				// Initialize the feed so that we can use it.
				$feed->init();

				if ($feed->error()){
					return array(JText::_("SP_LATEST_ERROR").$feed->error(),NULL);
				}

				// Make sure the content is being served out to the browser properly.
				$feed->handle_content_type();

				$values = array_merge($values, $feed->get_items());
			}
		}

		// we sort the array based on the event compare function
		usort($values, array("SimplePie_Item_GCalendar", "compare"));
		
		//return the feed data structure for the template
		return array(NULL,$values);
	}

	function create_gc_feed($params){
		$sortOrder = $params->get( 'order', NULL );

		$feed = new SimplePie_GCalendar();
		$feed->set_show_past_events(FALSE);
		$feed->set_sort_ascending(TRUE);
		$feed->set_orderby_by_start_date($sortOrder);
		$feed->set_expand_single_events(TRUE);
		$feed->enable_order_by_date(FALSE);

		// check if cache directory exists and is writeable
		$cacheDir =  JPATH_BASE.DS.'cache'.DS.'mod_gcalendar_upcoming';
		JFolder::create($cacheDir, 0755);
		if ( !is_writable( $cacheDir ) ) {
			$cache_exists = false;
		}else{
			$cache_exists = true;
		}

		//check and set caching
		if($cache_exists) {
			$feed->set_cache_location($cacheDir);
			$feed->enable_cache();
			$cache_time = (intval($params->get( 'cache', 3600 )));
			$feed->set_cache_duration($cache_time);
		}
		else {
			$feed->enable_cache(FALSE);
		}
		return $feed;
	}
}
?>
