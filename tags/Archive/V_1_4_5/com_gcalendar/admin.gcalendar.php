<?php


/**
* Google calendar component
* @author allon
* @version $Revision: 1.4.5 $
**/

// ensure this file is being included by a parent file
defined('_VALID_MOS') or die('Direct Access to this location is not allowed.');

require_once ($mainframe->getPath('admin_html'));
require_once ($mainframe->getPath('class'));

$task = mosGetParam( $_REQUEST, 'task' );
$cid  = mosGetParam( $_REQUEST, 'cid', array( 0 ) );
if (!is_array( $cid )) {
 $cid = array ( 0 );
}

switch ($task) {

	case "new" :
		editContact('0', $option);
		break;

	case "edit" :
		editContact($cid[0], $option);
		break;

	case "save" :
		saveContact($option);
		break;

	case "remove" :
		removeContacts($cid, $option);
		break;

	default :
		showContacts($option);
		break;
}

/**
* List the calendars
* @param string The current GET/POST option
*/
function showContacts($option) {
	global $database, $mainframe;

	$database->setQuery("SELECT * FROM #__gcalendar");
	$rows = $database->loadObjectList();

	HTML_gcalendar :: showCalendars($rows, $option);
}

/**
* Creates a new or edits and existing user record
* @param int The id of the record, 0 if a new entry
* @param string The current GET/POST option
*/
function editContact($id, $option) {
	global $database;

	$row = new mosGcalendar($database);

	if ($id) {
		$database->setQuery("SELECT * FROM #__gcalendar WHERE id = $id");
		$rows = $database->loadObjectList();
		$row = $rows[0];
	}

	HTML_gcalendar :: editCalendar($row, $option);
}

/**
* Saves the record from an edit form submit
* @param string The current GET/POST option
*/
function saveContact($option) {
	global $database, $my;

	$row = new mosGcalendar($database);
	if (!$row->bind($_POST)) {
		echo "<script> alert('" . $row->getError() . "'); window.history.go(-1); </script>\n";
		exit ();
	}

	// pre-save checks
	if (!$row->check()) {
		echo "<script> alert('" . $row->getError() . "'); window.history.go(-1); </script>\n";
		exit ();
	}

	// save the changes
	if (!$row->store()) {
		echo "<script> alert('" . $row->getError() . "'); window.history.go(-1); </script>\n";
		exit ();
	}
	$row->checkin();
	$row->updateOrder();

	mosRedirect("index2.php?option=$option");
}

/**
* Removes records
* @param array An array of id keys to remove
* @param string The current GET/POST option
*/
function removeContacts(& $cid, $option) {
	global $database;

	if (count($cid)) {
		$cids = implode(',', $cid);
		$database->setQuery("DELETE FROM #__gcalendar WHERE id IN ($cids)");
		if (!$database->query()) {
			echo "<script> alert('" . $database->getErrorMsg() . "'); window.history.go(-1); </script>\n";
		}
	}

	mosRedirect("index2.php?option=$option");
}
?>
