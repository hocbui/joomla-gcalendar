<?php
/**
* @version	$Id: mod_slick_rss.php 9764 2008-03-22 17:32:11Z davidwhthomas $
* @package	Joomla 1.5
* @copyright	Copyright (C) 2008 David W.H Thomas. All rights reserved.
* @license	GNU/GPL, see LICENSE.php
* Parse and display XML news feeds with mootools DHTML tooltip
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

class modGcalendarLatestHelper
{
	function getCalendarItems(&$params)
	{
		$gcalendar_data = array(); //init feed array
		if(!class_exists('SimplePie')){
			//include Simple Pie processor class
			require_once (JPATH_SITE.DS.'libraries'.DS.'simplepie'.DS.'simplepie.php');
		}
		$calName = $params->get( 'name_latest', NULL );
		
		JModel::addIncludePath(JPATH_SITE.DS.'components'.DS.'com_gcalendar'.DS.'models');
		$model = JModel::getInstance('GCalendarModelGCalendar');
		$model->setState('calendarName',$calName);
		$model->setState('calendarType',JRequest::getVar('calendarType', 'xmlUrl'));
		
		// check if cache directory exists and is writeable
		$cacheDir =  JPATH_BASE.DS.'cache';	
		if ( !is_writable( $cacheDir ) ) {	
			$mod_error['error'][] = 'Cache folder is unwriteable. Solution: chmod 777 '.$cacheDir;
			$cache_exists = false;
		}else{
			$cache_exists = true;
		}
		
		//Load and build the feed array
		$feed = new SimplePie();
		
		//check and set caching
		if($cache_exists) {
			$feed->set_cache_location($cacheDir);
			$feed->enable_cache();
			$cache_time = (intval($params->get( 'latestcache', 3600 )));
			$feed->set_cache_duration($cache_time);
		}
		else {
			$feed->enable_cache('false');
		}
		
		// This is the feed we'll use
		$feed->set_feed_url($model->getGCalendar());
		 
		// Let's turn this off because we're just going to re-sort anyways, and there's no reason to waste CPU doing it twice.
		$feed->enable_order_by_date(false);
		 
		// Initialize the feed so that we can use it.
		$feed->init();
		 
		// Make sure the content is being served out to the browser properly.
		$feed->handle_content_type();
		 
		// We'll use this for re-sorting the items based on the new date.
		$temp = array();
		
		$dateformat=$params->get('dateFormat', 'd.m.Y H:i');
		 
		foreach ($feed->get_items() as $item) {
		    $location = $gd_where[0]['attribs']['']['valueString'];
		    //and the status tag too, come to that
		    $gd_status = $item->get_item_tags('http://schemas.google.com/g/2005', 'eventStatus');
		    $status = substr( $gd_status[0]['attribs']['']['value'], -8);
		 
		    $pubdate = $item->get_date($dateformat);
		    $where = $item->get_item_tags('http://schemas.google.com/g/2005', 'where'); 
		    $location = $where[0]['attribs']['']['valueString']; 

		    // If there's actually a title here (private events don't have titles) and it's not cancelled...
			if (strlen(trim($item->get_title()))>1 && $status != "canceled" && strlen(trim($pubdate)) > 0) {
				$id = substr($item->get_link(),stripos($item->get_link(),'eid=')+4);
		        $temp[] = array(
		         'id'=>$id,
		         'published'=>$pubdate,
		         'where'=>$location,
		         'title'=>$item->get_title(),
		         'description'=>$item->get_description(),
		         'backlink'=>urldecode(JURI::base().'index.php?option=com_gcalendar&task=event&eventID='.$id.'&calendarName='.$calName.'&ctz=America/Chicago'),
		         'link'=>$item->get_link());
		        if ($debug) { echo "Added ".$item->get_title();}
		    } 
		}
		
		//Sort this 
		sort($temp);

			
		//return the feed data structure for the template	
		return $temp;
	}
}