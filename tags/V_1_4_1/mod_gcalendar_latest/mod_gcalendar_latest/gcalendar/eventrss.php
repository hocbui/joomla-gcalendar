<?php


/**
* Google calendar latest events module
* @author allon
* @version $Revision: 1.4.1 $
**/

define('_VALID_MOS', 1);
require_once( '../../globals.php' );
require_once( '../../configuration.php' );
require_once( '../../includes/joomla.php' );

require_once('../../includes/database.php');

if (isset($_GET['gcal_feed'])){$cal_name = $_GET['gcal_feed'];}

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

if(strpos($path,'public/basic')===false){
	$path=substr($path,0,strpos($path,'public')).'public/basic';
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

// Nothing else to edit
header('Content-type: text/xml');
echo $feed;
?>