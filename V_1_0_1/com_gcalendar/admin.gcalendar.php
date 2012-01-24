<?php

/**
* Google calendar component
* @author allon
* @version $Revision: 1.0.1 $
**/

// ensure this file is being included by a parent file
defined('_VALID_MOS') or die('Direct Access to this location is not allowed.');

require_once ($mainframe->getPath('admin_html'));
require_once ($mainframe->getPath('class'));

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
	global $database, $my;
	global $mosConfig_absolute_path;

	$row = new mosGcalendar($database);

	$row->load($id);

	$ipos[] = mosHTML :: makeOption('no');
	$ipos[] = mosHTML :: makeOption('yes');
	$ipos[] = mosHTML :: makeOption('auto');

	$iposlist = mosHTML :: selectList($ipos, 'scroll', 'class="inputbox" size="1"', 'value', 'text', $row->scroll);

	$imageFiles = mosReadDirectory("$mosConfig_absolute_path/images/stories");
	$images = array (
		mosHTML :: makeOption('',
		'Select Image'
	));
	foreach ($imageFiles as $file) {
		if (eregi("bmp|gif|jpg|png", $file)) {
			$images[] = mosHTML :: makeOption($file);
		}
	}

	if ($id) {
		// build the html select list for ordering
		$order = mosGetOrderingList("SELECT id AS value, name AS text" . "\nFROM #__gcalendar" . "\nWHERE id >= 0 ORDER BY id");

	} else {
		$olist = "<input type=\"hidden\" name=\"name\" value=\"$row->name\" />" . " New items default to the last place";
	}

	HTML_gcalendar :: editCalendar($row, $imagelist, $iposlist, $option, $olist);
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

/**
* Changes the state of one or more content pages
* @param array An array of unique category id numbers
* @param integer 0 if unpublishing, 1 if publishing
* @param string The current option
*/
function changeContact($cid = null, $htmlUrl = 0, $option) {
	global $database, $my;

	mosRedirect("index2.php?option=$option");
}

/** JJC
* Moves the order of a record
* @param integer The increment to reorder by
*/
function orderContacts($uid, $inc, $option) {
	global $database;
	$row = new mosGcalendar($database);
	$row->load($uid);
	$row->move($inc, "name != 0");

	mosRedirect("index2.php?option=$option");
}
?>
