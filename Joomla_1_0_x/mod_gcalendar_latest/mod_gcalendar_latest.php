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
defined('_VALID_MOS') or die('Restricted access');

global $mosConfig_absolute_path, $database, $mosConfig_cachepath, $mosConfig_lang;

// Get the right language if it exists
if (file_exists($mosConfig_absolute_path."/modules/mod_gcalendar_latest/languages/".$mosConfig_lang.".php")){
	include_once($mosConfig_absolute_path."/modules/mod_gcalendar_latest/languages/".$mosConfig_lang.".php");
}else{
	include_once($mosConfig_absolute_path."/modules/mod_gcalendar_latest/languages/english.php");
}

// Include SimplePie RSS Parser, supports utf-8 and international character sets in newsfeeds
if(!class_exists('SimplePie')){
	include_once('mod_gcalendar_latest/simplepie.inc');
}

if(!class_exists('SimplePie_GCalendar')){
	//include Simple Pie processor class
	require_once ('mod_gcalendar_latest/simplepie-gcalendar.php');
}

$calName = $params->get('name', '');
if(empty($calName)){
	echo _GCALENDAR_LATEST_CALENDAR_NO_DEFINED;
	return;
}

$feed = new SimplePie_GCalendar();
$feed->set_show_past_events(TRUE);
$feed->set_sort_ascending(FALSE);
$feed->set_orderby_by_start_date(FALSE);
$feed->set_expand_single_events(TRUE);
$feed->enable_order_by_date(FALSE);

// check if cache directory exists and is writeable
$cacheDir = $mosConfig_cachepath .'/mod_gcalendar_latest/';
if(!file_exists($cacheDir))
	mkdir($cacheDir, 0755);
if ( !is_writable( $cacheDir ) ) {	
	return 'Cache Directory Unwriteable';
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

$query = "SELECT id,xmlUrl FROM #__gcalendar where name='".$calName."'";
$database->setQuery( $query );
$results = $database->loadObjectList();
if(empty($results)){
	echo _GCALENDAR_LATEST_CALENDAR_NOT_FOUND.$calName;
	return;
}
	
$url = '';
foreach ($results as $result) {
	if(!empty($result->xmlUrl))
		$url = $result->xmlUrl;
}

$feed->set_cal_language(_LANGUAGE);
$feed->set_feed_url($url);
 
// Initialize the feed so that we can use it.
$feed->init();
 
if ($feed->error()){
	echo _GCALENDAR_LATEST_SP_ERROR.$feed->error();
	return;
}

// Make sure the content is being served out to the browser properly.
$feed->handle_content_type();

$SECSINDAY=86400;

// How you want each thing to display.
// All bits listed below which are available:
// ###TITLE###, ###DESCRIPTION###, ###PUBLISHEDDATE###, ###PUBLISHEDTIME###, 
// ###STARTDATE###, ###STARTTIME###, ###DATESEPARATOR###, ###ENDDATE###, ###ENDTIME###
// ###WHERE###, ###BACKLINK###, ###LINK###, ###MAPLINK###
$dsplLink = "<a href='###BACKLINK###'>###TITLE###</a>";
if($params->get( 'openWindow', 0 )==1)
	$dsplLink = "<a href='###LINK###' target='_blank'>###TITLE###</a>";
$event_display="<div id=\"gc_latest_date".$MODCLASS_SUFFIX."\">"._GCALENDAR_LATEST_PUBLISHED." ###PUBLISHEDDATE### ###PUBLISHEDTIME###<br>###STARTDATE### ###STARTTIME### ###DATESEPARATOR### ###ENDDATE### ###ENDTIME###</div><div id=\"gc_upcoming_event".$MODCLASS_SUFFIX."\">".$dsplLink."</div><br>";

// Date format you want your details to appear
$dateformat=$params->get('dateFormat', '%d.%m.%Y'); // 10 March 2009 - see http://www.php.net/date for details
$timeformat=$params->get('timeFormat', '%H:%M');; // 12.15am

$tz = $params->get('timezone', '');
if($tz == '')
	$tz = $feed->get_timezone();
		
$gcalendar_data = $feed->get_items();
// Loop through the array, and display what we wanted.
for ($i = 0; $i < sizeof($gcalendar_data) && $i <$params->get( 'max', 5 ); $i++){
	$item = $gcalendar_data[$i];
	
	// These are the dates we'll display
    $startDate = strftime($dateformat, $item->get_start_time());
    $startTime = strftime($timeformat, $item->get_start_time());
    $endDate = strftime($dateformat, $item->get_end_time());
    $endTime = strftime($timeformat, $item->get_end_time());
    $pubDate = strftime($dateformat, $item->get_publish_date());
    $pubTime = strftime($timeformat, $item->get_publish_date());
    
    $temp_event=$event_display;
    
    // smh 2008-12-17
    //       - modification to allow flexible output for various event types viz:
    //       Part of a day
    //       Single Day - whole day
    //       Multiple days - whole day
    //   N.B.  This formatting is fixed for now.  We need to add params to allow configuration in future.
    // Now customise display format based on event as part of day, whole day or multiple days
    // Need to know if it is whole days or not.  Google reports this with end date > start date
	if (($item->get_start_time()+ $SECSINDAY) <= $item->get_end_time()) {
      // For a single whole of day, Google reports the end date as the next day
      //  So, we check to see if start date + 1 day = end day (i.e. a one day, whole day event)
      if (($item->get_start_time()+ $SECSINDAY) == $item->get_end_time()) {
        // Single day, whole day	
        $temp_event=str_replace("###STARTDATE###",$startDate,$temp_event);
		$temp_event=str_replace("###STARTTIME###","",$temp_event);
		$temp_event=str_replace("###DATESEPARATOR###","",$temp_event);
		$temp_event=str_replace("###ENDDATE###","",$temp_event);
		$temp_event=str_replace("###ENDTIME###","",$temp_event);
      } else {
       if ((date('g:i a',$item->get_start_time())=='12:00 am')&&
			(date('g:i a',$item->get_end_time())=='12:00 am')){
			// multiple days, whole day
			// So, bring end date back to real date. 
			$endDate = strftime($dateformat, $item->get_end_time() - $SECSINDAY);
			$temp_event=str_replace("###STARTDATE###",$startDate,$temp_event);
			$temp_event=str_replace("###STARTTIME###","",$temp_event);
			$temp_event=str_replace("###DATESEPARATOR###","-",$temp_event); 
			$temp_event=str_replace("###ENDDATE###",$endDate,$temp_event);
			$temp_event=str_replace("###ENDTIME###","",$temp_event);
		}else{
			//  multiple day, part of day
			$temp_event=str_replace("###STARTDATE###",$startDate,$temp_event);
			$temp_event=str_replace("###STARTTIME###",$startTime,$temp_event);
			$temp_event=str_replace("###DATESEPARATOR###","-",$temp_event); 
			$temp_event=str_replace("###ENDDATE###",$endDate,$temp_event);
			$temp_event=str_replace("###ENDTIME###",$endTime,$temp_event);
		}
      }
    } else {
       	//  Single day, part of day
       	$temp_event=str_replace("###STARTDATE###",$startDate,$temp_event);
		$temp_event=str_replace("###STARTTIME###",$startTime,$temp_event);
		$temp_event=str_replace("###DATESEPARATOR###","-",$temp_event); 
		$temp_event=str_replace("###ENDDATE###","",$temp_event);
		$temp_event=str_replace("###ENDTIME###",$endTime,$temp_event);
    }
    
    //Make any URLs used in the description also clickable
    $desc = eregi_replace('(((f|ht){1}tp://)[-a-zA-Z0-9@:%_\+.~#?,&//=]+)','<a href="\\1">\\1</a>', $item->get_description());

    // Now, let's run it through some str_replaces, and store it with the date for easy sorting later
    $temp_event=str_replace("###TITLE###",$item->get_title(),$temp_event);
    $temp_event=str_replace("###DESCRIPTION###",$desc,$temp_event);
    $temp_event=str_replace("###PUBLISHEDDATE###",$pubDate,$temp_event);
    $temp_event=str_replace("###PUBLISHEDTIME###",$pubTime,$temp_event);
    $temp_event=str_replace("###WHERE###",$item->get_location(),$temp_event);
    $temp_event=str_replace("###BACKLINK###",urldecode('index.php?option=com_gcalendar&task=event&eventID='.$item->get_id().'&calendarName='.$calName.'&ctz='.$tz),$temp_event);
    $temp_event=str_replace("###LINK###",$item->get_link().'&ctz='.$tz,$temp_event);
    $temp_event=str_replace("###MAPLINK###","http://maps.google.com/?q=".urlencode($item->get_location()),$temp_event);
    // Accept and translate HTML
    $temp_event=str_replace("&lt;","<",$temp_event);
    $temp_event=str_replace("&gt;",">",$temp_event);
    $temp_event=str_replace("&quot;","\"",$temp_event);

	echo $temp_event;
}
?>
