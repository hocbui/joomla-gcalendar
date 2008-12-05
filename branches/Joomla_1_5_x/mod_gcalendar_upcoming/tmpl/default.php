<?php

/**
* Google calendar latest events module
* @author allon
* @version $Revision: 1.5.2 $
**/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

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

// ...and how many you want to display (leave at 999 for everything)
$items_to_show=$params->get( 'max', 5 );

// Date format you want your details to appear
$dateformat=$params->get('dateFormat', 'd.m.Y'); // 10 March 2009 - see http://www.php.net/date for details
$timeformat=$params->get('timeFormat', 'H:i');; // 12.15am

//Time offset - if times are appearing too early or too late on your website, change this.
$offset="now"; // you can use "+1 hour" here for example

// Loop through the (now sorted) array, and display what we wanted.
foreach ($gcalendar_data as $item) {
	// These are the dates we'll display
    $gCalDate = date($dateformat, $item['startdate']);
    $gCalStartTime = date($timeformat, $item['startdate']);
    $gCalEndTime = date($timeformat, $item['enddate']);
    
    //Make any URLs used in the description also clickable: thanks Adam
    $item['description'] = eregi_replace('(((f|ht){1}tp://)[-a-zA-Z0-9@:%_\+.~#?,&//=]+)','<a href="\\1">\\1</a>', $item['description']);

    // Now, let's run it through some str_replaces, and store it with the date for easy sorting later
    $temp_event=$event_display;
    $temp_dateheader=$event_dateheader;
    $temp_event=str_replace("###TITLE###",$item['title'],$temp_event);
    $temp_event=str_replace("###DESCRIPTION###",$item['description'],$temp_event);
    $temp_event=str_replace("###DATE###",$gCalDate,$temp_event);
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

    if (($items_to_show>0 AND $items_shown<$items_to_show)) {
                if ($GroupByDate) {if ($gCalDate!=$old_date) { echo $temp_dateheader; $old_date=$gCalDate;}}
        echo $temp_event;
        $items_shown++;
    }
}
?>