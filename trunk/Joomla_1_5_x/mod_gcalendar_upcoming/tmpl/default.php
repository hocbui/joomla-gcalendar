<?php

/**
* Google calendar latest events module
* @author allon
* @version $Revision: 2.0.0 $
**/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

if(!empty($error)){
	echo $error;
	return;
}

//  smh - 2008-12-17 - integer count of seconds in a day
$SECSINDAY=86400;
//  /smh  2008-12-17
//
// How you want each thing to display.
// All bits listed below which are available:
// ###TITLE###, ###DESCRIPTION###, ###DATE###, ###FROM###, ###UNTIL###,
// ###WHERE###, ###BACKLINK###, ###LINK###, ###MAPLINK###
// You can put ###DATE### in here too if you want to, and disable the 'group by date' below.
$dsplLink = '###BACKLINK###';
if($params->get( 'openWindow', 0 )==1)
	$dsplLink = '###LINK###';
$event_display="<p>###DATE### ###FROM###<br><a href='".$dsplLink."'>###TITLE###</a></p>";

// The separate date header is here
$event_dateheader="<P><B>###DATE###</b></P>";
$GroupByDate=false;
// Change the above to 'false' if you don't want to group this by dates,
// but remember to add ###DATE### in the event_display if you do.

// Date format you want your details to appear
$dateformat=$params->get('dateFormat', 'd.m.Y'); // 10 March 2009 - see http://www.php.net/date for details
$timeformat=$params->get('timeFormat', 'H:i');; // 12.15am

// Loop through the array, and display what we wanted.
for ($i = 0; $i < sizeof($gcalendar_data) && $i <$params->get( 'max', 5 ); $i++){
	$item = $gcalendar_data[$i];
	
	// These are the dates we'll display
    $gCalDate = date($dateformat, $item['startdate']);
    $gCalStartTime = date($timeformat, $item['startdate']);
    $gCalEndTime = date($timeformat, $item['enddate']);
    //
    // smh 2008-12-17
    //       - modification to allow flexible output for various event types viz:
    //       Part of a day
    //       Single Day - whole day
    //       Multiple days - whole day
    //   N.B.  This formatting is fixed for now.  We need to add params to allow configuration in future.
    
    $gCalDateEnd = date($dateformat, $item['enddate']);
    
    // Now customise display format based on event as part of day, whole day or multiple days
    // Need to know if it is whole days or not.  Google reports this with end date > start date
    if ($item['startdate'] < $item['enddate']) {
      // For a single whole of day, Google reports the end date as the next day
      //  So, we check to see if start date + 1 day = end day (i.e. a one day, whole day event)
      if (($item['startdate']+ $SECSINDAY) == $item['enddate']) {
         // Single day, whole day	
         $event_display="<p style=\"font-size: 90%;\">###DATE###</p><div><strong>###TITLE###</strong><hr></div>";
      } else {
       // multiple days, whole day
       // So, bring end date back to real date. 
       $gCalDateEnd = date($dateformat, $item['enddate'] - $SECSINDAY); 
       $event_display="<p style=\"font-size: 90%;\">###DATE### to ###DATEEND###</p><div><strong>###TITLE###</strong><hr></div>";
      }
    } else {
       //  Single day, part of day
       $event_display="<p style=\"font-size: 90%;\">###DATE### ###FROM### - ###UNTIL###</p><div><strong>###TITLE###</strong><hr></div>";
    }
    // /smh 2008-12-17
    
    //Make any URLs used in the description also clickable: thanks Adam
    $item['description'] = eregi_replace('(((f|ht){1}tp://)[-a-zA-Z0-9@:%_\+.~#?,&//=]+)','<a href="\\1">\\1</a>', $item['description']);

    // Now, let's run it through some str_replaces, and store it with the date for easy sorting later
    $temp_event=$event_display;
    $temp_dateheader=$event_dateheader;
    $temp_event=str_replace("###TITLE###",$item['title'],$temp_event);
    $temp_event=str_replace("###DESCRIPTION###",$item['description'],$temp_event);
    $temp_event=str_replace("###DATE###",$gCalDate,$temp_event);
    // smh: 2008-12-17 - We need to decode the End Date of an event sometimes..
    $temp_event=str_replace("###DATEEND###",$gCalDateEnd,$temp_event);
    // /smh: 2008-12-17
    $temp_dateheader=str_replace("###DATE###",$gCalDate,$temp_dateheader);
    $temp_event=str_replace("###FROM###",$gCalStartTime,$temp_event);
    $temp_event=str_replace("###UNTIL###",$gCalEndTime,$temp_event);
    $temp_event=str_replace("###WHERE###",$item['where'],$temp_event);
    $temp_event=str_replace("###BACKLINK###",$item['backlink'],$temp_event);
    $temp_event=str_replace("###LINK###",$item['link'],$temp_event);
    $temp_event=str_replace("###MAPLINK###","http://maps.google.com/?q=".urlencode($item['where']),$temp_event);
    // Accept and translate HTML
    $temp_event=str_replace("&lt;","<",$temp_event);
    $temp_event=str_replace("&gt;",">",$temp_event);
    $temp_event=str_replace("&quot;","\"",$temp_event);

	if ($GroupByDate) {if ($gCalDate!=$old_date) { echo $temp_dateheader; $old_date=$gCalDate;}}
	echo $temp_event;
}
?>
