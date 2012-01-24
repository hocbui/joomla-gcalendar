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
	
		$calName = $params->get( 'name', NULL );
		if(empty($calName)) return array(JText::_("CALENDAR_NO_DEFINED"),NULL);
		
		$feed = new SimplePie_GCalendar();
		$feed->set_show_past_events(FALSE);
		$feed->set_sort_ascending(TRUE);
		$feed->set_orderby_by_start_date(TRUE);
		$feed->set_expand_single_events(TRUE);
		$feed->enable_order_by_date(FALSE);

		// check if cache directory exists and is writeable
		$cacheDir =  JPATH_BASE.DS.'cache'.DS.'mod_gcalendar_upcoming';
		JFolder::create($cacheDir, 0755);
		if ( !is_writable( $cacheDir ) ) {	
			$mod_error['error'][] = 'Cache folder is unwriteable. Solution: chmod 777 '.$cacheDir;
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
		
		$db = &JFactory::getDBO();

		$query = "SELECT id,xmlUrl FROM #__gcalendar where name='".$calName."'";
		$db->setQuery( $query );
		$results = $db->loadObjectList();
		if(empty($results))
			return array(JText::_("CALENDAR_NOT_FOUND").$calName,NULL);
		$url = '';
		foreach ($results as $result) {
			if(!empty($result->xmlUrl))
				$url = $result->xmlUrl;
		}
		
		// Use temp variable to get language
		// Need to preserve the $params array
		$tmpparams   = JComponentHelper::getParams('com_languages');
		$lg = $tmpparams->get('site', 'en-GB');
		$feed->set_cal_language($lg);

		$feed->set_feed_url($url);
		 
		// Initialize the feed so that we can use it.
		$feed->init();
		
		if ($feed->error()){
			return array(JText::_("SP_LATEST_ERROR").$feed->error(),NULL);
		}
		
		// Make sure the content is being served out to the browser properly.
		$feed->handle_content_type();
		
		$values = $feed->get_items();
		
		//return the feed data structure for the template	
		return array(NULL,$values);
	}
}
?>
