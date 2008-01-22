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
$xmlType = JRequest::getVar('xmlType','full');

if(!empty($path) && $this->calendarType === 'xmlUrl' && $xmlType==='full'){
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
	
$allow_url_fopen = (bool) ini_get('allow_url_fopen');
if(!$allow_url_fopen){
	$feed = '<?xml version="1.0" encoding="utf-8"?><content><error>';
	$feed .= JText::_( 'READ_EVENTS_ERROR' );
	$feed .= '</error></content>';
} else if(empty($path)){
	$feed = '<?xml version="1.0" encoding="utf-8"?><content><error>';
	$feed .= JText::_( 'NO_CALENDAR_SPECIFIED' );
	$feed .= '</error></content>';
} else {
  	$feed = file_get_contents($path);
}
if($this->calendarType==='xmlUrl') header('Content-type: text/xml');
else if($this->calendarType==='icalUrl') header('Content-type: text/calendar');
else header('Content-type: text/html');
echo $feed;

?>