<?php

/**
* Google calendar latest events module
* @author allon
* @version $Revision: 2.0.0 $
**/

// no direct access
defined('_JEXEC') or die('Restricted access');

class modGcalendarLatestHelper{
	function getCalendarItems(&$params){
	
		$calName = $params->get( 'name', NULL );
		if(empty($calName)) return array(JText::_("CALENDAR_NO_DEFINED"),NULL);
		
		$feed = new SimplePie_GCalendar();
		$feed->set_show_past_events(TRUE);
		$feed->set_sort_ascending(FALSE);
		$feed->set_orderby_by_start_date(FALSE);
		$feed->set_expand_single_events(TRUE);
		$feed->enable_order_by_date(FALSE);
		
		// check if cache directory exists and is writeable
		$cacheDir =  JPATH_BASE.DS.'cache'.DS.'latest';
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
		$lg = '?hl='.$lg;

		$feed->set_feed_url($url.$lg);
		 
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
