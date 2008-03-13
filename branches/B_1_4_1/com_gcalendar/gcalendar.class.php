<?php


/**
* Google calendar component
* @author allon
* @version $Revision: 1.4.5 $
**/

// ensure this file is being included by a parent file
defined('_VALID_MOS') or die('Direct Access to this location is not allowed.');

/**
* Google calendar table class
*/
class mosGcalendar extends mosDBTable {

	/** @var int Primary key */
	var $id = null;
	/** @var string */
	var $name = null;
	/** @var string */
	var $htmlUrl = null;
	/** @var string */
	var $xmlUrl = null;

	/**
	* @param database A database connector object
	*/
	function mosGcalendar(& $db) {
		$this->mosDBTable('#__gcalendar', 'id', $db);
	}

}
?>
