<?php


/**
* Google calendar latest events module
* @author allon
* @version $Revision: 1.3.0 $
**/

// Change this with your Google calendar feed
$calendarURL = urldecode($_GET['calendarUrl']);

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

// Nothing else to edit
header('Content-type: text/xml');
echo $feed;
?>