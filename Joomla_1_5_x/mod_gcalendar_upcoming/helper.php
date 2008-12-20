<?php

/**
* Google calendar upcoming events module
* @author allon
* @version $Revision: 2.0.0 $
**/

// no direct access
defined('_JEXEC') or die('Restricted access');

class modGcalendarUpcomingHelper {
	function getCalendarItems(&$params) {
	
		$calName = $params->get( 'name', NULL );
		if(empty($calName)) return array(JText::_("CALENDAR_NO_DEFINED"),NULL);
		
		// check if cache directory exists and is writeable
		$cacheDir =  JPATH_BASE.DS.'cache';	
		if ( !is_writable( $cacheDir ) ) {	
			$mod_error['error'][] = 'Cache folder is unwriteable. Solution: chmod 777 '.$cacheDir;
			$cache_exists = false;
		}else{
			$cache_exists = true;
		}
		
		//Load and build the feed array
		$feed = new SimplePie_GCalendar();
		$feed->set_calendar_type('full');
		
		//check and set caching
		if($cache_exists) {
			$feed->set_cache_location($cacheDir);
			$feed->enable_cache();
			$cache_time = (intval($params->get( 'upcomingcache', 3600 )));
			$feed->set_cache_duration($cache_time);
		}
		else {
			$feed->enable_cache('false');
		}
		
		$db = &JFactory::getDBO();

		$query = "SELECT id,xmlUrl FROM #__gcalendar where name='".$calName."'";
		$db->setQuery( $query );
		$results = $db->loadObjectList();
		if(empty($results))
			return array(JText::_("CALENDAR_NOT_FOUND").$calName,NULL);
		$url = '';
		foreach ($results as $result) {
			$url = $result->xmlUrl;
		}
		$url = SimplePie_GCalendar::cfg_feed_without_past_events($url);
		$url = SimplePie_GCalendar::ensure_feed_is_full($url);
		$feed->set_feed_url($url);
		 
		// Let's turn this off because we're just going to re-sort anyways, and there's no reason to waste CPU doing it twice.
		$feed->enable_order_by_date(false);
		 
		// Initialize the feed so that we can use it.
		$feed->init();
		 
		if ($feed->error()){
			return array(JText::_("SP_LATEST_ERROR").$feed->error(),NULL);
		}
		
		// Make sure the content is being served out to the browser properly.
		$feed->handle_content_type();
		
		$values = $feed->get_calendar_items();
		
		//return the feed data structure for the template	
		return array(NULL,$values);
	}
}
?>