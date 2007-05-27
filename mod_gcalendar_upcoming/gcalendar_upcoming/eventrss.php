<?php

/**
* Google calendar upcoming events module
* @author allon
* @version $Revision: 1.4.1 $
**/

define('_VALID_MOS', 1);
require_once( '../../globals.php' );
require_once( '../../configuration.php' );
require_once( '../../includes/joomla.php' );

require_once('../../includes/database.php');

define ('HOSTNAME', 'http://www.google.com/'); //only allow google stuff to be grabbed
$timeLimit = 0; //default to no time limit
$maxResults = 5;

if (isset($_GET['timeLimit'])){$timeLimit = $_GET['timeLimit'];}
if (isset($_GET['gcal_feed'])){$cal_name = $_GET['gcal_feed'];}
if (isset($_GET['maxResults'])){$maxResults = $_GET['maxResults'];}

global $database,$url;

if (file_exists( '../../components/com_joomfish/joomfish.php' )) {
	require_once('../../administrator/components/com_joomfish/mldatabase.class.php' );
	require_once('../../administrator/components/com_joomfish/joomfish.class.php' );
	require_once('../../components/com_joomfish/includes/joomfish.class.php' );
	global $mosConfig_host, $mosConfig_user, $mosConfig_password, $mosConfig_db, $mosConfig_dbprefix,$mosConfig_debug;
	
	$GLOBALS[ '_JOOMFISH_MANAGER'] = new JoomFishManager();
	
	$database = new mlDatabase( $mosConfig_host, $mosConfig_user, $mosConfig_password, $mosConfig_db, $mosConfig_dbprefix );
}

$database->setQuery("select id,xmlUrl from #__gcalendar where name='$cal_name'");
$results = $database->loadObjectList('',true,$_GET['lang']);
$url = '';
foreach ($results as $result) {
	$path = $result->xmlUrl;
}
if(strpos($path,'public/full')===false){
	$path=substr($path,0,strpos($path,'public')).'public/full';
}

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