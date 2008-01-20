<?php

/**
* Google calendar upcoming events module
* @author allon
* @version $Revision: 1.4.2 $
**/

define('_VALID_MOS', 1);

class XML_gcalendar {
	function displayCalendar() {
		global $database;
		
		$timeLimit = 0; //default to no time limit
		$maxResults = 5;
		$xmlType = 'full';
		$calendarType = 'xmlUrl';
		
		if (isset($_GET['timeLimit'])){$timeLimit = $_GET['timeLimit'];}
		if (isset($_GET['calendarName'])){$cal_name = $_GET['calendarName'];}
		if (isset($_GET['maxResults'])){$maxResults = $_GET['maxResults'];}
		if (isset($_GET['calendarType'])){$calendarType = $_GET['calendarType'];}
		if (isset($_GET['xmlType'])){$xmlType = $_GET['xmlType'];}
		
		$database->setQuery("select id,xmlUrl from #__gcalendar where name='$cal_name'");
		$results = $database->loadObjectList('',true,$_GET['lang']);
		$url = '';
		foreach ($results as $result) {
			$path = $result->xmlUrl;
		}
		
		
		if($calendarType === 'xmlUrl' && $xmlType==='full'){
			if(strpos($path,'public/full')===false){
				$path=substr($path,0,strpos($path,'public')).'public/full';
			}
			
			$today = date('Y-m-d');
			$endDate = mktime() + ($timeLimit * 2592000);
			$endDate = date('Y-m-d', $endDate) ;
			$path = $path."?start-min=".$today;
			if ($timeLimit > 0) { $path .= "&start-max=".$endDate; }
			$path .= "&max-results=".$maxResults."&orderby=starttime&sortorder=ascending";
			$path .= "&singleevents=true";
		}
		
		$allow_url_fopen = (bool) ini_get('allow_url_fopen');
		if($allow_url_fopen){
		  	$feed = file_get_contents($path);
		} else {
			$feed = '<?xml version="1.0" encoding="utf-8"?><content><error>';
			$feed .= _GCALENDAR_READ_EVENTS_ERROR;
			$feed .= '</error></content>';
		}
		if($calendarType==='xmlUrl') header('Content-type: text/xml');
		else if($calendarType==='icalUrl') header('Content-type: text/calendar');
		else header('Content-type: text/html');
		echo $feed;
	}
}
?>