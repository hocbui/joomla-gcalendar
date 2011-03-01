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
 * @copyright 2007-2011 Allon Moritz
 * @since 2.2.0
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.model');

/**
 * GCalendar Model
 *
 */
class GCalendarsModelImport extends JModel
{
	/**
	 * Constructor that retrieves the ID from the request
	 *
	 * @access	public
	 * @return	void
	 */
	function __construct()
	{
		parent::__construct();

		$array = JRequest::getVar('cid',  0, '', 'array');
		$this->setId((int)$array[0]);
	}

	/**
	 * Method to set the calendar identifier
	 *
	 * @access	public
	 * @param	int Calendar identifier
	 * @return	void
	 */
	function setId($id)
	{
		// Set id and wipe data
		$this->_id		= $id;
		$this->_data	= null;
	}

	/**
	 * Returns a HTTP client object with the appropriate headers for communicating
	 * with Google using AuthSub authentication.
	 *
	 * Uses the $_SESSION['sessionToken'] to store the AuthSub session token after
	 * it is obtained.  The single use token supplied in the URL when redirected
	 * after the user succesfully authenticated to Google is retrieved from the
	 * $_GET['token'] variable.
	 *
	 * @return Zend_Http_Client
	 */
	function getAuthSubHttpClient()
	{
		global $_SESSION, $_GET;
		$client = new Zend_Gdata_HttpClient();

		//use curl if ssl protocol is not a registered transport protocol (need extension php_openssl)
		if (!in_array('ssl',stream_get_transports()) && function_exists('curl_init')  )
		$client->setConfig(array(
                'strictredirects' => true,
                'adapter' => 'Zend_Http_Client_Adapter_Curl',
                'curloptions' => array(
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_MAXREDIRS => 2,
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_COOKIEJAR => 'gcal_cookiejar.txt'
		)
		)
		);

		if (!isset($_SESSION['sessionToken']) && JRequest::getVar('token', null) != null) {
			$_SESSION['sessionToken'] =
			Zend_Gdata_AuthSub::getAuthSubSessionToken(JRequest::getVar('token', null), $client);
		}
		if(empty($_SESSION['sessionToken']) && isset($_GET['authtoken'])){
			$client->setClientLoginToken($_GET['authtoken']);
			$_SESSION['sessionAuthToken'] = $_GET['authtoken'];
			return $client;
		}
		if(empty($_SESSION['sessionToken']))return null;
		$client->setAuthSubToken($_SESSION['sessionToken']);
		return $client;
	}

	/**
	 * Method to get a calendar
	 * @return object with data
	 */
	function getOnlineData() {
		$this->loadZendClasses();

		$client = $this->getAuthSubHttpClient();
		if (!$client) {
			$this->_data = array();
		}else{
			$user = JRequest::getVar('user', null);
			$pass = JRequest::getVar('pass', null);
			if(!empty($user)){
				$client = Zend_Gdata_ClientLogin::getHttpClient($user, $pass, Zend_Gdata_Calendar::AUTH_SERVICE_NAME, $client);

				//ClientLogin Auth Token
				$AuthToken = $client->getClientLoginToken();

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

				if($tCalendars == null){
					$tCalendars = array();
				}
				
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
			}

			$gdataCal = new Zend_Gdata_Calendar($client);
			$calFeed = $gdataCal->getCalendarListFeed();

			$tmp = array();
			JTable::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_gcalendar'.DS.'tables');
			foreach ($calFeed as $calendar) {
				$table_instance = & $this->getTable('import');
				$table_instance->id = 0;
				$cal_id = substr($calendar->id->text,strripos($calendar->id->text,'/')+1);
				$table_instance->calendar_id = $cal_id;
				$table_instance->name = $calendar->title->text;
				if(strpos($calendar->color->value, '#') === 0)
				$color = str_replace("#","",$calendar->color->value);
				$table_instance->color = $color;

				if ($gcal_magics && isset($gcal_magics[$cal_id])){
					$table_instance->magic_cookie = $gcal_magics[$cal_id];
				}
				$tmp[] = $table_instance;
			}
			$this->_data = $tmp;
		}

		return $this->_data;
	}

	/**
	 * Method to get a calendar
	 * @return object with data
	 */
	function getDBData()
	{
		$query = " SELECT * FROM #__gcalendar";
		$this->_db->setQuery( $query );
		return $this->_db->loadObjectList();
	}

	/**
	 * Method to store a record
	 *
	 * @access	public
	 * @return	boolean	True on success
	 */
	function store()	{
		$row =& $this->getTable();

		$cids = JRequest::getVar( 'cid', array(0), 'post', 'array' );
		if (count($cids)>0) {
			foreach ($cids as $cid) {
				$row = & $this->getTable('import');
				$row->id = 0;
				$row->calendar_id = strtok($cid, ',');
				$row->color = strtok(',');
				$row->name = strtok(',');
				$row->magic_cookie = strtok(',');
				if($row->magic_cookie === false){
					$row->magic_cookie = null;
				}

				// Make sure the calendar record is valid
				if (!$row->check()) {
					JError::raiseWarning( 500, $row->getError() );
					return false;
				}

				// Store the calendar table to the database
				if (!$row->store()) {
					JError::raiseWarning( 500, $row->getError() );
					return false;
				}
			}
		}
		return true;
	}

	function loadZendClasses() {
		$mainframe = &JFactory::getApplication();
		$absolute_path = $mainframe->getCfg( 'absolute_path' );
		ini_set("include_path", ini_get("include_path") . PATH_SEPARATOR . JPATH_COMPONENT . DS . 'libraries');

		require_once('Zend' . DS . 'Loader.php');
		Zend_Loader::loadClass('Zend_Gdata_AuthSub');
		Zend_Loader::loadClass('Zend_Gdata_HttpClient');
		Zend_Loader::loadClass('Zend_Gdata_Calendar');
		Zend_Loader::loadClass('Zend_Gdata_ClientLogin');
	}
}
?>
