<?php

/**
* Google calendar latest events module
* @author allon
* @version $Revision: 2.0.0 $
**/

// no direct access
defined('_VALID_MOS') or die('Restricted access');

global $mosConfig_absolute_path, $database, $mosConfig_cachepath;

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

$calName = $params->get('name_latest', '');
if(empty($calName)){
	echo _GCALENDAR_LATEST_CALENDAR_NO_DEFINED;
	return;
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
	$url = $result->xmlUrl;
}
$gcalendar_data = array(); //init feed array

// check if cache directory exists and is writeable
$cacheDir 		= $mosConfig_cachepath .'/';	
if ( !is_writable( $cacheDir ) ) {	
	return 'Cache Directory Unwriteable';
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
$feed->set_feed_url($url);
 
// Let's turn this off because we're just going to re-sort anyways, and there's no reason to waste CPU doing it twice.
$feed->enable_order_by_date(false);
 
// Initialize the feed so that we can use it.
$feed->init();
 
// Make sure the content is being served out to the browser properly.
$feed->handle_content_type();

$tz = $params->get('timezone', '');
if(empty($tz)){
	$tzvalue = $feed->get_feed_tags('http://schemas.google.com/gCal/2005', 'timezone');
	$tz = $tzvalue[0]['attribs']['']['value'];
}

$values = $feed->get_items();

if ($feed->error()){
	echo _GCALENDAR_LATEST_SP_ERROR.$feed->error();
	return;
}

foreach ($values as $item) {
    // Now, let's grab the Google-namespaced <gd:where> tag.
	$gd_where = $item->get_item_tags('http://schemas.google.com/g/2005', 'where');
    $location = $gd_where[0]['attribs']['']['valueString'];
    //and the status tag too, come to that
    $gd_status = $item->get_item_tags('http://schemas.google.com/g/2005', 'eventStatus');
    $status = substr( $gd_status[0]['attribs']['']['value'], -8);
 
    $pubdate = $item->get_date('Y-m-d\TH:i:s\Z');
     // converts ISODATE to unix date
    // 1984-09-01T14:21:31Z
	sscanf($pubdate,"%u-%u-%uT%u:%u:%uZ",$year,$month,$day,$hour,$min,$sec);
	$unixpubdate=mktime($hour,$min,$sec,$month,$day,$year);
    $where = $item->get_item_tags('http://schemas.google.com/g/2005', 'where'); 
    $location = $where[0]['attribs']['']['valueString']; 

    // If there's actually a title here (private events don't have titles) and it's not cancelled...
	if (strlen(trim($item->get_title()))>1 && $status != "canceled" && strlen(trim($pubdate)) > 0) {
		$id = substr($item->get_link(),strpos(strtolower($item->get_link()),'eid=')+4);
        $gcalendar_data[] = array(
         'published'=>$unixpubdate,
         'id'=>$id,
         'where'=>$location,
         'title'=>$item->get_title(),
         'description'=>$item->get_description(),
         'backlink'=>urldecode('index.php?option=com_gcalendar&task=event&eventID='.$id.'&calendarName='.$calName.'&ctz='.$tz),
         'link'=>$item->get_link());
    }
}
sort($gcalendar_data);

// How you want each thing to display.
// All bits listed below which are available:
// ###TITLE###, ###DESCRIPTION###, ###DATE###, ###PUBLISHED###
// ###WHERE###, ###BACKLINK###, ###LINK###, ###MAPLINK###
$dsplLink = "<a href='###BACKLINK###'>###TITLE###</a>";
if($params->get( 'openWindow', 0 )==1)
	$dsplLink = "<a href='###LINK###' target='_blank'>###TITLE###</a>";
$event_display="<p>"._GCALENDAR_LATEST_PUBLISHED." ###PUBLISHED###<br>".$dsplLink."</p>";

// Date format you want your details to appear
$dateformat=$params->get('dateFormat', 'd.m.Y H:i');

$counter = 0;
// Loop through the array, and display what we wanted.
for ($i = sizeof($gcalendar_data)-1; $i >=0 && $counter < $params->get( 'max', 5 ); $i--){
	$item = $gcalendar_data[$i];
	// These are the dates we'll display
    $gCalDate = date($dateformat, $item['published']);
    
    //Make any URLs used in the description also clickable: thanks Adam
    $item['description'] = eregi_replace('(((f|ht){1}tp://)[-a-zA-Z0-9@:%_\+.~#?,&//=]+)','<a href="\\1">\\1</a>', $item['description']);

    // Now, let's run it through some str_replaces, and store it with the date for easy sorting later
    $temp_event=$event_display;
    $temp_event=str_replace("###TITLE###",$item['title'],$temp_event);
    $temp_event=str_replace("###DESCRIPTION###",$item['description'],$temp_event);
    $temp_event=str_replace("###PUBLISHED###",$gCalDate,$temp_event);
    $temp_event=str_replace("###WHERE###",$item['where'],$temp_event);
    $temp_event=str_replace("###BACKLINK###",$item['backlink'],$temp_event);
    $temp_event=str_replace("###LINK###",$item['link'],$temp_event);
    $temp_event=str_replace("###MAPLINK###","http://maps.google.com/?q=".urlencode($item['where']),$temp_event);
    // Accept and translate HTML
    $temp_event=str_replace("&lt;","<",$temp_event);
    $temp_event=str_replace("&gt;",">",$temp_event);
    $temp_event=str_replace("&quot;","\"",$temp_event);

	echo $temp_event;
	$counter++;
}

?>