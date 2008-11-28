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
	function getFeed(&$params)
	{
		// Date format you want your details to appear
		$dateformat="j F Y"; // 10 March 2009 - see http://www.php.net/date for details

		//global $mainframe;
		$gcalendar_data = array(); //init feed array
		if(!class_exists('SimplePie')){
			//include Simple Pie processor class
			require_once (JPATH_SITE.DS.'libraries'.DS.'simplepie'.DS.'simplepie.php');
		}
		
		JModel::addIncludePath(JPATH_SITE.DS.'components'.DS.'com_gcalendar'.DS.'models');
		$model = JModel::getInstance('GCalendarModelGCalendar');
		$model->setState('calendarName',$params->get( 'name_latest', NULL ));
		$model->setState('calendarType',JRequest::getVar('calendarType', 'xmlUrl'));
		
		// check if cache directory exists and is writeable
		$cacheDir =  JPATH_BASE.DS.'cache';	
		if ( !is_writable( $cacheDir ) ) {	
			$slick_rss['error'][] = 'Cache folder is unwriteable. Solution: chmod 777 '.$cacheDir;
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
			$cache_time = (intval($rsscache));
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
		        //http://docs.joomla.org/Why_do_you_get_a_%22Fatal_error:_Call_to_undefined_function:_stripos()%22_when_editing_Joomla!_1.5.7_Articles_from_the_frontend_when_using_PHP_4%3F
		        $temp[] = array('id'=>substr($item->get_link(),strpos($item->get_link(),'eid=')+4),
		         'published'=>$pubdate,
		         'where'=>$location,
		         'title'=>$item->get_title(),
		         'description'=>$item->get_description(),
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