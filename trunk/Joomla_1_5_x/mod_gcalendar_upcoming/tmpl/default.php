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
$dsplLink = "<a href='###BACKLINK###'>###TITLE###</a>";
if($params->get( 'openWindow', 0 )==1)
        $dsplLink = "<a href='###LINK###' target='_blank'>###TITLE###</a>";
$event_display="<p>###DATE### ###FROM###<br>".$dsplLink."</p>";

// smh 2009-01-08 Added params for underline between events and if link is active
$dsplUnderline = "";
if ($params->get( 'showUnderline', 0 ) == 1){
   $dsplUnderline = "<HR>";
}
//// dsplTitleLink determines if the Title will be clickable or not
$dsplTitleLink = "###TITLE###";
if ($params->get( 'showLink', 1 )==1) {
   if($params->get( 'openWindow', 0 )==1) {
        $dsplTitleLink = "<a href='###LINK###' target='_blank'>###TITLE###</a>";
   } else {
        $dsplTitleLink = "<a href='###BACKLINK###'>###TITLE###</a>";
   } 
}
// Added option for a bolded title
$dsplTitle = $dsplTitleLink;
if ($params->get( 'boldTitle', 0 )==1) {
   $dsplTitle = "<strong>".$dsplTitleLink."</strong>";  
}

$dsplFontPerc = 100;
if ($params->get( 'fontPerc', "" ) != "") {
   $dsplFontPerc = $params->get( 'fontPerc', "" );  
}
// /smh 2009-01-08

// Date format you want your details to appear
$dateformat=$params->get('dateFormat', 'd.m.Y'); // 10 March 2009 - see http://www.php.net/date for details
$timeformat=$params->get('timeFormat', 'H:i');; // 12.15am
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
    $startDate = date($dateformat, $item->get_start_time());
    $startTime = date($timeformat, $item->get_start_time());
    $endTime = date($timeformat, $item->get_end_time());
    
    // smh 2008-12-17
    //       - modification to allow flexible output for various event types viz:
    //       Part of a day
    //       Single Day - whole day
    //       Multiple days - whole day
    //   N.B.  This formatting is fixed for now.  We need to add params to allow configuration in future.
    
    $gCalDateEnd = date($dateformat, $item->get_end_time());
    
    // Now customise display format based on event as part of day, whole day or multiple days
    // Need to know if it is whole days or not.  Google reports this with end date > start date
    if ($item->get_start_time() < $item->get_end_time()) {
      // For a single whole of day, Google reports the end date as the next day
      //  So, we check to see if start date + 1 day = end day (i.e. a one day, whole day event)
      if (($item->get_start_time()+ $SECSINDAY) == $item->get_end_time()) {
         // Single day, whole day	
       // smh 2009-01-08
         //$event_display="<p style=\"font-size: 90%;\">###DATE###</p><div><strong>###TITLE###</strong>".$dsplUnderline."</div>";
         $event_display="<p style=\"font-size: ".$dsplFontPerc."%;\">###DATE###</p><p>".$dsplTitle.$dsplUnderline."</p>";
       // /smh 2009-01-08
      } else {
       // multiple days, whole day
       // So, bring end date back to real date. 
       $gCalDateEnd = date($dateformat, $item->get_end_time() - $SECSINDAY); 
       // smh 2009-01-08
       //$event_display="<p style=\"font-size: 90%;\">###DATE### to ###DATEEND###</p><div><strong>###TITLE###</strong>".$dsplUnderline."</div>";
       $event_display="<p style=\"font-size: ".$dsplFontPerc."%;\">###DATE### to ###DATEEND###</p><p>".$dsplTitle.$dsplUnderline."</p>";
       // /smh 2009-01-08
      }
    } else {
       //  Single day, part of day
       // smh 2009-01-08
       //$event_display="<p style=\"font-size: 90%;\">###DATE### ###FROM### - ###UNTIL###</p><div><strong>".$dsplTitleLink."</strong>".$dsplUnderline."</div>";
       $event_display="<p style=\"font-size: ".$dsplFontPerc."%;\">###DATE### ###FROM### - ###UNTIL###</p><p>".$dsplTitle.$dsplUnderline."</p>";
       // /smh 2009-01-08
    }
    // /smh 2008-12-17
    
    //Make any URLs used in the description also clickable: thanks Adam
    $desc = eregi_replace('(((f|ht){1}tp://)[-a-zA-Z0-9@:%_\+.~#?,&//=]+)','<a href="\\1">\\1</a>', $item->get_description());

    // Now, let's run it through some str_replaces, and store it with the date for easy sorting later
    $temp_event=$event_display;
    $temp_event=str_replace("###TITLE###",$item->get_title(),$temp_event);
    $temp_event=str_replace("###DESCRIPTION###",$desc,$temp_event);
    $temp_event=str_replace("###DATE###",$startDate,$temp_event);
    // smh: 2008-12-17 - We need to decode the End Date of an event sometimes..
    $temp_event=str_replace("###DATEEND###",$gCalDateEnd,$temp_event);
    // /smh: 2008-12-17
    $temp_event=str_replace("###FROM###",$startTime,$temp_event);
    $temp_event=str_replace("###UNTIL###",$endTime,$temp_event);
    $temp_event=str_replace("###WHERE###",$item->get_location(),$temp_event);
    $temp_event=str_replace("###BACKLINK###",urldecode(JURI::base().'index.php?option=com_gcalendar&task=event&eventID='.$item->get_id().'&calendarName='.$calName.'&ctz='.$tz),$temp_event);
    $temp_event=str_replace("###LINK###",$item->get_link(),$temp_event);
    $temp_event=str_replace("###MAPLINK###","http://maps.google.com/?q=".urlencode($item->get_location()),$temp_event);
    // Accept and translate HTML
    $temp_event=str_replace("&lt;","<",$temp_event);
    $temp_event=str_replace("&gt;",">",$temp_event);
    $temp_event=str_replace("&quot;","\"",$temp_event);

        echo $temp_event;
}
?>
