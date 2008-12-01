<?php

/**
* Google calendar latest events module
* @author allon
* @version $Revision: 1.5.2 $
**/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

// How you want each thing to display.
// By default, this contains all the bits you can grab. You can put ###DATE### in here too if
// you want to, and disable the 'group by date' below.
// $event_display="<P><B>###TITLE###</b> - published ###PUBLISHED### (<a href='###LINK###'>add this</a>)<BR>###WHERE### (<a href='###MAPLINK###'>map</a>)<br>###DESCRIPTION###</p>";
if($params->get( 'openWindow', 0 )==0)
	$event_display="<p>###PUBLISHED###<br><a href='###BACKLINK###'>###TITLE###</a></p>";
else
	$event_display="<p>###PUBLISHED###<br><a href='###LINK###'>###TITLE###</a></p>";

// The separate date header is here
$event_dateheader="<P><B>###DATE###</b></P>";
$GroupByDate=true;
// Change the above to 'false' if you don't want to group this by dates,
// but remember to add ###DATE### in the event_display if you do.

// ...and how many you want to display (leave at 999 for everything)
$items_to_show=$params->get( 'max', 5 );

// Loop through the (now sorted) array, and display what we wanted.
foreach ($gcalendar_data as $item) {
    //Make any URLs used in the description also clickable: thanks Adam
    $item['description'] = eregi_replace('(((f|ht){1}tp://)[-a-zA-Z0-9@:%_\+.~#?,&//=]+)','<a href="\\1">\\1</a>', $item['description']);

    // Now, let's run it through some str_replaces, and store it with the date for easy sorting later
    $temp_event=$event_display;
    $temp_dateheader=$event_dateheader;
    $temp_event=str_replace("###TITLE###",$item['title'],$temp_event);
    $temp_event=str_replace("###DESCRIPTION###",$item['description'],$temp_event);
    $temp_event=str_replace("###DATE###",$item['published'],$temp_event);
    $temp_dateheader=str_replace("###DATE###",$item['published'],$temp_dateheader);
    $temp_event=str_replace("###PUBLISHED###",$item['published'],$temp_event);
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