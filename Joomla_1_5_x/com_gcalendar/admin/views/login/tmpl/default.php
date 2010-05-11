<?php
/**
 * GCalendar is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * GCalendar is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GCalendar.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Allon Moritz
 * @copyright 2007-2010 Allon Moritz
 * @since 2.2.0
 */

defined('_JEXEC') or die('Restricted access');

global $mainframe;
$absolute_path = $mainframe->getCfg( 'absolute_path' );
ini_set("include_path", ini_get("include_path") . PATH_SEPARATOR . JPATH_COMPONENT . DS . 'libraries');

require_once('Zend' . DS . 'Loader.php');
Zend_Loader::loadClass('Zend_Gdata_AuthSub');
Zend_Loader::loadClass('Zend_Gdata_HttpClient');
Zend_Loader::loadClass('Zend_Gdata_Calendar');
Zend_Loader::loadClass('Zend_Gdata_ClientLogin');

$params = &JComponentHelper::getParams( 'com_gcalendar' );
$domain = $params->get('google_apps_domain');

$u = JFactory::getURI();
$next = JRoute::_( $u->toString().'?option=com_gcalendar&task='.JRequest::getCmd('nextTask'));
$scope = 'http://www.google.com/calendar/feeds/';
$session = true;
$secure = false;
$hd = '';
if(!empty($domain))
$hd = '&hd='.$domain;
$authSubUrl = Zend_Gdata_AuthSub::getAuthSubTokenUri($next, $scope, $secure,
$session,Zend_Gdata_AuthSub::AUTHSUB_REQUEST_URI);

$user = $params->get('google_login');
$pass = $params->get('google_pass');

//if no password set, or a bad authtoken in url... abort with manual login link 
if (empty($pass) || strstr($next,'authtoken')) {
	echo "<a href=\"".$authSubUrl.$hd."\">Please Login to access the calendar data.</a>";
	exit(); 
}

//google calendar service = "cl"
$service = Zend_Gdata_Calendar::AUTH_SERVICE_NAME;

$client = new Zend_Gdata_HttpClient();
$client->setConfig(array(
        'strictredirects' => true,
        'adapter' => 'Zend_Http_Client_Adapter_Curl',
        'curloptions' => array(
        	CURLOPT_FOLLOWLOCATION => true,
        	CURLOPT_MAXREDIRS => 2,
        	CURLOPT_SSL_VERIFYPEER => false,
        	CURLOPT_COOKIEJAR => 'gcal_cookiejar.txt',
        ),
        'useragent' => $useragent
    )
);

$client = Zend_Gdata_ClientLogin::getHttpClient($user, $pass, $service, $client);

//ClientLogin Auth Token
$AuthToken = $client->getClientLoginToken();

if (!empty($AuthToken)) 
	$next .= "&authtoken=".$AuthToken; 
else 
	$next = ""; 


$extraHeaders=array('Authorization: GoogleLogin auth=' . $AuthToken);

$client->resetParameters(true);
$client->setMethod('GET');
$client->setCookieJar(true);
$client->setHeaders($extraHeaders);
$client->setUri("http://www.google.com/calendar/render");
$response = $client->request();

$redirUri = $client->getLastRequest();
$gsid = strstr($redirUri,"?gsessionid");
$gsid = substr($gsid,0,strpos($gsid," "));

$cookies = $client->getCookieJar();
$secid = $cookies->getCookie("http://www.google.com/calendar/","secid")->getValue();

// GRAB dtid calendar identifiers (only available in /render)
preg_match_all("#([0-9a-zA-Z_]+)\/color#",$response,$matches);
$dtid=array();
foreach($matches[1] as $caldtid) {
	$dtid[] = $caldtid;
}

$uri = "http://www.google.com/calendar/caldetails".$gsid;
$postdata = "init=true&secid=".$secid.'&dtid='.implode('&dtid=',$dtid);

$client->setUri($uri);
$client->setMethod('POST');
$client->setRawData($postdata);

$response = $client->request();

$response = strstr($response,"while(1);");
$response = substr($response,strlen("while(1);"));
$response = str_replace("'",'"',$response);
$response = str_replace("\\47","'",$response);
$response = preg_replace("#\\\\([0-9a-f]{2})[^0-9a-f]#","",$response);
//$response = str_replace("\\74","",$response);
//$response = str_replace("\\76","",$response);
//$response = utf8_decode($response);
$tCalendars = json_decode($response); //DOESNT WORKS IF SPECIAL \xx CHARS, need \uuuu transformation

$gcal_magics = array();
foreach ($tCalendars as $c) {
	//$c[5] -> Title
	//$c[6] -> "Europe/Paris"
	//$c[7] -> Location
	//$c[8] -> Description
	//$c[15] -> "FR" (country)
	//$c[19] -> "(GMT+01:00) Paris"
	$dtid  = $c[1];
	$mcook = $c[10];
	$url   = $c[14];
	$gcal_magics[urlencode($url)] = $mcook;
}

if (count($gcal_magics) > 0) {
	$_SESSION['gcal_magics'] = serialize($gcal_magics);
}

//could be better, please fix redirection...
if (!empty($next))
echo '
<a href="'.$next."\">Logged on Google Calendar, please wait...</a>".'
<script type="text/javascript">
location.href = "'.$next.'";
</script>
';
?>