<?php
/**
 * Google calendar component
 * @author allon
 * @version $Revision: 1.5.0 $
 */

 // no direct access
defined('_JEXEC') or die('Restricted access'); 
$path= $this->path;
$timeLimit = JRequest::getVar('timeLimit',0); //default to no time limit
$maxResults = JRequest::getVar('maxResults',5);

if($this->calendarType==='xmlUrl'){
	if(strpos($path,'public/full')===false){
		$path=substr($path,0,strpos($path,'public')).'public/full';
	}
	
	$today = date('Y-m-d');
	$endDate = mktime() + ($timeLimit * 2592000);
	$endDate = date('Y-m-d', $endDate) ;
	$path = $path."?start-min=".$today;
	if ($timeLimit > 0) { $path .= "&start-max=".$endDate; }
	$path .= "&max-results=".$maxResults."&orderby=starttime&sortorder=ascending";
	$path .= "&singleevents=true";
}
if(function_exists('curl_init')){
  $ch = curl_init();
  if(!$ch){
    $feed = file_get_contents($path);
  }else{
    $timeout = 5;
    curl_setopt($ch, CURLOPT_URL, $path);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    $feed = curl_exec($ch);
    
    if(empty($feed))$feed = file_get_contents($path);
    else{
      $info = curl_getinfo($ch);
      if(empty($info['http_code'])){
        $feed = file_get_contents($path);
      }else if(strstr($info['http_code'],4)||strstr($info['http_code'],5)){
        //we have an error
	$feed = file_get_contents($path);
      }
    }
    
    curl_close($ch);
  }
}
else{
  $feed = file_get_contents($path);
}
if($this->calendarType==='xmlUrl') header('Content-type: text/xml');
else if($this->calendarType==='icalUrl') header('Content-type: text/calendar');
else header('Content-type: text/html');
echo $feed;

?>