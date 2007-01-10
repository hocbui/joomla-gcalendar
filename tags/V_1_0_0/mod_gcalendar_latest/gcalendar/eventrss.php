<?php


/**
* Google calendar latest events module
* @author allon
* @version $Revision: 1.0 $
**/

// Change this with your Google calendar feed
$calendarURL = urldecode($_GET['calendarUrl']);

$ch = curl_init();
$timeout = 5;
curl_setopt($ch, CURLOPT_URL, $calendarURL);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
$feed = curl_exec($ch);
curl_close($ch);

// Nothing else to edit
header('Content-type: text/xml');
echo $feed;
?>