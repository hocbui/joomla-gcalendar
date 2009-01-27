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
defined( '_VALID_MOS' ) or die( 'Direct Access to this location is not allowed.' );

DEFINE("_GCALENDAR_LATEST_PUBLISHED","Published:");
DEFINE("_GCALENDAR_LATEST_CALENDAR_NO_DEFINED","There is no calendar specified!! Please set in the module parameter section a valid calendar name configured in the gcalendar component.");
DEFINE("_GCALENDAR_LATEST_CALENDAR_NOT_FOUND","The calendar was not found in the database. The calendar name is: <br>");
DEFINE("_GCALENDAR_LATEST_SP_ERROR","Simplepie detected an error. Please run the <a href=\"modules/mod_gcalendar_latest/sp_compatibility_test.php\">compatibility utility</a>.<br>The following Simplepie error occurred:<br>");
?>