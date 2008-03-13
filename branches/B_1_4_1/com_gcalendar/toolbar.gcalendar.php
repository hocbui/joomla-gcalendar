<?php


/**
* Google calendar component
* @author allon
* @version $Revision: 1.4.5 $
**/

// ensure this file is being included by a parent file
defined('_VALID_MOS') or die('Direct Access to this location is not allowed.');

require_once ($mainframe->getPath('toolbar_html'));

switch ($task) {

	case "new" :
	case "edit" :
		menucontact :: EDIT_MENU();
		break;

	default :
		menucontact :: DEFAULT_MENU();
		break;
}
?>
