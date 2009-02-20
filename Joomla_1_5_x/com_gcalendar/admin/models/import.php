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
 * @copyright 2007-2009 Allon Moritz
 * @version $Revision: 2.0.1 $
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.model');

require_once 'Zend/Loader.php';
Zend_Loader::loadClass('Zend_Gdata_AuthSub');
Zend_Loader::loadClass('Zend_Gdata_HttpClient');
Zend_Loader::loadClass('Zend_Gdata_Calendar');

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
		if (!isset($_SESSION['sessionToken']) && isset($_GET['token'])) {
			$_SESSION['sessionToken'] =
			Zend_Gdata_AuthSub::getAuthSubSessionToken($_GET['token'], $client);
		}else {
			return null;
		}
		$client->setAuthSubToken($_SESSION['sessionToken']);
		return $client;
	}

	/**
	 * Method to get a calendar
	 * @return object with data
	 */
	function getData() {
		$client = $this->getAuthSubHttpClient();

		if (!$client) {
			$this->_data = new stdClass();
			$this->_data->id = 0;
			$this->_data->calendar = null;
		}else{
		$gdataCal = new Zend_Gdata_Calendar($client);
		$calFeed = $gdataCal->getCalendarListFeed();
		$tmp = array();
		foreach ($calFeed as $calendar) {
			$table_instance = new TableGCalendar();
			$cal_id = substr($calendar->getId(),strripos($calendar->getId(),'/')+1);
			$table_instance->calendar_id = $cal_id;
			$table_instance->name = $calendar->getTitle();
			$table_instance->color = $calendar->getColor();
			$tmp[] = $table_instance;
		}
		$this->_data = $tmp;
		}

		return $this->_data;
	}

	/**
	 * Method to store a record
	 *
	 * @access	public
	 * @return	boolean	True on success
	 */
	function store()	{
		$row =& $this->getTable();

		$data = JRequest::get( 'post' );

		// Bind the form fields to the calendar table
		if (!$row->bind($data)) {
			JError::raiseWarning( 500, $row->getError() );
			return false;
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

		return true;
	}
}
?>
