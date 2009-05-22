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

defined('_JEXEC') or die();

jimport( 'joomla.application.component.model' );

/**
 * GCalendar Model
 *
 */
class GCalendarModelGCalendar extends JModel {

	var $cached_data = null;

	/**
	 * Returns all calendars in the database. The returned
	 * rows contain an additional attribute selected which is set
	 * to true when the specific calendar is mentioned in the
	 * parameters property calendarids.
	 *
	 * @return the calendars specified in the database
	 */
	function getDBCalendars(){
		if($cached_data == null){
			$params = $this->getState('parameters.menu');
			$calendarids = null;
			if($params != null)
			$calendarids=$params->get('calendarids');
			$gcids = $this->getState('gcids');
			if(!empty($gcids))
			$calendarids = $this->getState('gcids');

			$db =& JFactory::getDBO();
			$query = "SELECT id, calendar_id, name, color, magic_cookie  FROM #__gcalendar";
			$db->setQuery( $query );
			$results = $db->loadObjectList();
			if(empty($results))
			return '';
			$calendars = array();
			foreach ($results as $result) {
				$is_selected = FALSE;
				if ($calendarids){
					if( is_array( $calendarids ) ) {
						$result->selected = in_array($result->id,$calendarids);
					} else {
						$result->selected = $result->id == $calendarids;
					}
				}
				$calendars[] = $result;
			}
			$cached_data = $calendars;
		}
		return $cached_data;
	}

	function getGoogleCalendarFeeds($startDate, $endDate, $projection = null) {
		$results = $this->getDBCalendars();
		if(empty($results))
		return null;

		$calendars = array();
		foreach ($results as $result) {
			if(!empty($result->calendar_id) && $result->selected){
				$feed = new SimplePie_GCalendar();
				$feed->set_show_past_events(FALSE);
				$feed->set_sort_ascending(TRUE);
				$feed->set_orderby_by_start_date(TRUE);
				$feed->set_expand_single_events(TRUE);
				$feed->enable_order_by_date(FALSE);
				$feed->enable_cache(FALSE);
				$feed->set_projection($projection);
				$feed->set_start_date($startDate);
				$feed->set_end_date($endDate);
				$feed->set_max_events(100);
				$feed->put('gcid',$result->id);
				$feed->put('gccolor',$result->color);
				$feed->put('gcname',$result->name);
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
