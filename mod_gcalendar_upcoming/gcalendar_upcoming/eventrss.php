<?php

/**
* Google calendar upcoming events module
* @author allon
* @version $Revision: 1.2.0 $
**/


define ('HOSTNAME', 'http://www.google.com/'); //only allow google stuff to be grabbed
$timeLimit = 0; //default to no time limit
$maxResults = 5;

if (isset($_GET['timeLimit'])){$timeLimit = $_GET['timeLimit'];}
if (isset($_GET['gcal_feed'])){$path = $_GET['gcal_feed'];}
if (isset($_GET['maxResults'])){$maxResults = $_GET['maxResults'];}


$path = substr($path, 22, strlen($path)-22); //strip off the Http google.com part
$calendarURL = HOSTNAME.$path;
$today = date('Y-m-d');
$endDate = mktime() + ($timeLimit * 2592000);
$endDate = date('Y-m-d', $endDate) ;
$calendarURL = $calendarURL."?start-min=".$today;
if ($timeLimit > 0) { $calendarURL .= "&start-max=".$endDate; }
$calendarURL .= "&max-results=".$maxResults."&orderby=starttime&sortorder=ascending";
$calendarURL .= "&singleevents=true";

$ch = curl_init();
$timeout = 5;
curl_setopt($ch, CURLOPT_URL, $calendarURL);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
$feed = curl_exec($ch);
curl_close($ch);

header('Content-type: text/xml'); 
echo $feed;

?>