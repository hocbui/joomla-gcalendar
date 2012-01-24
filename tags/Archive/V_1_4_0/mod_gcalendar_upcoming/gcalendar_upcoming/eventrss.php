<?php

/**
* Google calendar upcoming events module
* @author allon
* @version $Revision: 1.4.0 $
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

if(function_exists('curl_init')){
  $ch = curl_init();
  if(!$ch){
    $feed = file_get_contents($calendarURL);
  }else{
    $timeout = 5;
    curl_setopt($ch, CURLOPT_URL, $calendarURL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    $feed = curl_exec($ch);
    
    if(empty($feed))$feed = file_get_contents($calendarURL);
    else{
      $info = curl_getinfo($ch);
      if(empty($info['http_code'])){
        $feed = file_get_contents($calendarURL);
      }else if(strstr($info['http_code'],4)||strstr($info['http_code'],5)){
        //we have an error
	$feed = file_get_contents($calendarURL);
      }
    }
    
    curl_close($ch);
  }
}
else{
  $feed = file_get_contents($calendarURL);
}

header('Content-type: text/xml'); 
echo $feed;

?>