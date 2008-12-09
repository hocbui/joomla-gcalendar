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

// How you want each thing to display.
// All bits listed below which are available:
// ###TITLE###, ###DESCRIPTION###, ###DATE###, ###PUBLISHED###
// ###WHERE###, ###BACKLINK###, ###LINK###, ###MAPLINK###
$dsplLink = '###BACKLINK###';
if($params->get( 'openWindow', 0 )==1)
	$dsplLink = '###LINK###';
$event_display="<p>".JText::_("PUBLISHED")." ###PUBLISHED###<br><a href='".$dsplLink."'>###TITLE###</a></p>";

// ...and how many you want to display (leave at 999 for everything)
$items_to_show=$params->get( 'max', 5 );

// Date format you want your details to appear
$dateformat=$params->get('dateFormat', 'd.m.Y H:i');

// Loop through the (now sorted) array, and display what we wanted.
foreach ($gcalendar_data as $item) {
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

    if (($items_to_show>0 AND $items_shown<$items_to_show)) {
        echo $temp_event;
        $items_shown++;
    }
}
?>