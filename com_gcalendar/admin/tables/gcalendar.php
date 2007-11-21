<?php
/**
 * Google calendar component
 * @author allon
 * @version $Revision: 1.5.0 $
 */

// no direct access
defined('_JEXEC') or die('Restricted access');


/**
 * GCalendar Table class
 *
 */
class TableGCalendar extends JTable
{
	/**
	 * Primary Key
	 *
	 * @var int
	 */
	var $id = null;

	/**
	 * @var string
	 */
	var $name = null;
	
	/**
	 * @var string
	 */
	var $htmlUrl = null;
	
	/**
	 * @var string
	 */
	var $xmlUrl = null;
	
	/**
	 * @var string
	 */
	var $icalUrl = null;

	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 */
	function Tablegcalendar(& $db) {
		parent::__construct('#__gcalendar', 'id', $db);
	}
}
?>
