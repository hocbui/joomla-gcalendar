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
defined( '_JEXEC' ) or die( 'Restricted access' );

if(!empty($error)){
	echo $error;
	return;
}

$SECSINDAY=86400;

// How you want each thing to display.
// All bits listed below which are available:
// ###TITLE###, ###DESCRIPTION###, ###PUBLISHEDDATE###, ###PUBLISHEDTIME###, 
// ###STARTDATE###, ###STARTTIME###, ###DATESEPARATOR###, ###ENDDATE###, ###ENDTIME###
// ###WHERE###, ###BACKLINK###, ###LINK###, ###MAPLINK###
$dsplLink = "<a href='###BACKLINK###'>###TITLE###</a>";
if($params->get( 'openWindow', 0 )==1)
	$dsplLink = "<a href='###LINK###' target='_blank'>###TITLE###</a>";
$event_display="<div id=\"gc_latest_published\">".JText::_("PUBLISHED")." ###PUBLISHEDDATE### ###PUBLISHEDTIME###</div><div id=\"gc_latest_date\">###STARTDATE### ###STARTTIME### ###DATESEPARATOR### ###ENDDATE### ###ENDTIME###</div><div id=\"gc_upcoming_event\">".$dsplLink."</div><br>";

// Date format you want your details to appear
$dateformat=$params->get('dateFormat', '%d.%m.%Y');
$timeformat=$params->get('timeFormat', '%H:%M');
$calName = $params->get( 'name', NULL );

// Loop through the array, and display what we wanted.
for ($i = 0; $i < sizeof($gcalendar_data) && $i <$params->get( 'max', 5 ); $i++){
	$item = $gcalendar_data[$i];
	
	$tz = $params->get('timezone', '');
	if($tz == ''){
		$feed = $item->get_feed();
		$tz = $feed->get_timezone();
	}

	// These are the dates we'll display
    $startDate = strftime($dateformat, $item->get_start_time());
    $startTime = strftime($timeformat, $item->get_start_time());
    $endDate = strftime($dateformat, $item->get_end_time());
    $endTime = strftime($timeformat, $item->get_end_time());
    $pubDate = strftime($dateformat, $item->get_publish_date());
    $pubTime = strftime($timeformat, $item->get_publish_date());
    
    $temp_event=$event_display;
    
    // smh 2008-12-17

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
	// /smh 2008-12-17
    
    
    //Make any URLs used in the description also clickable
    $desc = eregi_replace('(((f|ht){1}tp://)[-a-zA-Z0-9@:%_\+.~#?,&//=]+)','<a href="\\1">\\1</a>', $item->get_description());

    $temp_event=str_replace("###TITLE###",$item->get_title(),$temp_event);
    $temp_event=str_replace("###DESCRIPTION###",$desc,$temp_event);
    $temp_event=str_replace("###PUBLISHEDDATE###",$pubDate,$temp_event);
    $temp_event=str_replace("###PUBLISHEDTIME###",$pubTime,$temp_event);
    $temp_event=str_replace("###WHERE###",$item->get_location(),$temp_event);
    $temp_event=str_replace("###BACKLINK###",urldecode(JURI::base().'index.php?option=com_gcalendar&task=event&eventID='.$item->get_id().'&calendarName='.$calName.'&ctz='.$tz),$temp_event);
    $temp_event=str_replace("###LINK###",$item->get_link().'&ctz='.$tz,$temp_event);
    $temp_event=str_replace("###MAPLINK###","http://maps.google.com/?q=".urlencode($item->get_location()),$temp_event);
    // Accept and translate HTML
    $temp_event=str_replace("&lt;","<",$temp_event);
    $temp_event=str_replace("&gt;",">",$temp_event);
    $temp_event=str_replace("&quot;","\"",$temp_event);

	echo $temp_event;
}
?>
