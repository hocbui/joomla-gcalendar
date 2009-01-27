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
