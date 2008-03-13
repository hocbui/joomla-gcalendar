<?php


/**
* Google calendar component
* @author allon
* @version $Revision: 1.4.5 $
**/

// ensure this file is being included by a parent file
defined('_VALID_MOS') or die('Direct Access to this location is not allowed.');

class menucontact {
	/**
	* Draws the menu for a New Contact
	*/
	function EDIT_MENU() {
		mosMenuBar :: startTable();
		mosMenuBar :: save();
		mosMenuBar :: cancel();
		mosMenuBar :: spacer();
		mosMenuBar :: endTable();
	}

	function DEFAULT_MENU() {
		mosMenuBar :: startTable();
		//mosMenuBar::publish();
		//mosMenuBar::unpublish();
		mosMenuBar :: divider();
		mosMenuBar :: addNew();
		mosMenuBar :: editList();
		mosMenuBar :: deleteList();
		mosMenuBar :: divider();
		mosMenuBar :: spacer();
		mosMenuBar :: endTable();
	}

}
?>
