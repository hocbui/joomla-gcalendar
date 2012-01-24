<?php

/**
* Google calendar component
* @author allon
* @version $Revision: 2.0.0 $
**/

// ensure this file is being included by a parent file
defined( '_VALID_MOS' ) or die( 'Direct Access to this location is not allowed.' );

DEFINE("_GCALENDAR_UPCOMING_CALENDAR_NO_DEFINED","There is no calendar specified!! Please set in the module parameter section a valid calendar name configured in the gcalendar component.");
DEFINE("_GCALENDAR_UPCOMING_CALENDAR_NOT_FOUND","The calendar was not found in the database. The calendar name is: <br>");
DEFINE("_GCALENDAR_UPCOMING_SP_ERROR","Simplepie detected an error. Please run the <a href=\"modules/mod_gcalendar_upcoming/sp_compatibility_test.php\">compatibility utility</a>.<br>The following Simplepie error occurred:<br>");
?>