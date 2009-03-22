<?php
defined( '_JEXEC' ) or die( 'Restricted access' );

/* iWebCal Version 2.0 beta
 * Copyright (C) 2003-2005 David A. Feldman.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of version 2 of the GNU General Public License 
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful, but 
 * WITHOUT ANY WARRANTY; without even the implied warranty of 
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU 
 * General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License 
 * along with this program; if not, write to the Free Software Foundation, 
 * Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA. Or, 
 * visit http://gnu.org.
 * 
 * This file is part of the iWebCal calendar-viewing service. The iWebCal
 * service is available on the Web at http://iWebCal.com, and does not
 * require any programming knowledge or Web server configuration to use.
 * Anyone with an iCal or other .ics calendar file and a place to post
 * it on the Web can view the calendar using iWebCal.
 */
 
/*
 * main.inc: Global variables and functions used by iWebCal, but not
 * appropriate for config.inc. iWebCal's main source file outside of
 * config.inc.
 *
 * File version 2.0b4, last modified September 12, 2005.
 */

// Basic globals, may be overridden in config.inc
if (!isset($iWebCal_URL_PATH))
	$iWebCal_URL_PATH = dirname($_SERVER["SCRIPT_NAME"]);


$iWebConfig['baseBrowserInfo'] = explode(" ", $_SERVER["HTTP_USER_AGENT"]);

$iWebConfig['ACCEPTED_ITEM_TYPES'] = array(
	"VEVENT",
	"VTODO"
	);
	
$iWebConfig['ACCEPTED_PROPERTIES'] = array(
	"SUMMARY",
	"RRULE",
	"DTSTART",
	"DTEND",
	"PRIORITY",
	"DUE",
	"STATUS",
	"DURATION",
	"DESCRIPTION",
	"ATTENDEE",
	"URL",
	"LOCATION"
);
$iWebConfig['MULTI_VALUE_PROPERTIES'] = array(
	"RRULE"
);

// $MULTI_INSTANCE_PROPERTIES is a list of properties that are allowed to appear
// more than once in an event. It is used to be tolerant of problems in calendar
// files without a lot of code. When a property in the list is requested and more
// than one instance exists, an array of properties is returned. When a property
// not in the list is requested, the first matching property is returned. TODO -
// this may be worth revisiting for a more elegant solution at some point.
$iWebConfig['MULTI_INSTANCE_PROPERTIES'] = array(
	"ATTENDEE"
);

// [TO DO](1) - Can replace some explode/strftime calls with getdate(), might be quicker

$iWebConfig['TYPE_DAY'] = 0;
$iWebConfig['TYPE_WEEK'] = 1;
$iWebConfig['TYPE_MONTH'] = 2;

// [TO DO](1) - maybe add this to session so it doesn't get calc'ed every time.
//  Also consider other variables to which this might apply.
$topTmp = strtotime("Sunday");
$iWebConfig['WEEKDAY_FULL_NAMES'] = array();
$iWebConfig['WEEKDAY_FULL_NAMES'][0] = strftime("%A", $topTmp);
for ($i=1;$i<7;$i++) {
	$iWebConfig['WEEKDAY_FULL_NAMES'][] = strftime("%A", strtotime("+${i} days", $topTmp));
}
$iWebConfig['MONTH_FULL_NAMES'] = array(
	"January",
	"February",
	"March",
	"April",
	"May",
	"June",
	"July",
	"August",
	"September",
	"October",
	"November",
	"December"
);

// Centralized style info (in addition to CSS):
$iWebConfig['monthCellColor'] = "#ffffff";
$iWebConfig['monthTodayCellColor'] = "#eeeeee";
$iWebConfig['monthEmptyCellColor'] = $iWebConfig['monthCellColor'];
$iWebConfig['monthSelectedDayNumberColor'] = "#660000";

$iWebConfig['iWebCal_ENABLE_TASK_SORTING'] = true; 
$iWebConfig['iWebCal_TASK_SORT_DESTINATION'] = "javascript:document.controlForm.sort.value='[[NEW_SORT]]';document.controlForm.submit()";

// Get timezone info
include "time-zones.inc";

// ----------------------------------------------------------------------
// ENVIRONMENT

if (isset($iWebConfig['iWebCal_LIMIT_CACHE']) && $iWebConfig['iWebCal_LIMIT_CACHE']) {
	if ($iWebConfig["iWebCal_CACHE_DIRECTORY"] == "iWebCal_DEFAULT_CACHE_DIR")
		$iWebConfig["iWebCal_CACHE_DIRECTORY"] = $iWebConfig["iWebCal_LOCAL_PATH"] . "/cache";
//	ini_set("session.gc_maxlifetime", $iWebConfig['iWebCal_SESSION_LENGTH'] * 60);
//	ini_set("session.save_path", $iWebConfig['iWebCal_CACHE_DIRECTORY']);
	// [DAF-0912] added to increase probability that old session files will be garbage-collected
//	ini_set("session.gc_probability", 20);
}

// ----------------------------------------------------------------------
// CLASSES

include $iWebConfig['iWebCal_LOCAL_PATH'] . "/class/CalItem.inc";
include $iWebConfig['iWebCal_LOCAL_PATH'] . "/class/Calendar.inc";
include $iWebConfig['iWebCal_LOCAL_PATH'] . "/class/GCalendar2.php";


// ----------------------------------------------------------------------


function fatalError($errString) {
	trigger_error("iWebCal Error: " . $errString);
}

function monthName($monthNum) {
	return strftime("%B", strtotime("2002-$monthNum-06"));
}

function dayNumber($day) {
	switch($day) {
		case "SU":
			return 0;
			break;
		case "MO":
			return 1;
			break;
		case "TU":
			return 2;
			break;
		case "WE":
			return 3;
			break;
		case "TH":
			return 4;
			break;
		case "FR":
			return 5;
			break;
		case "SA":
			return 6;
			break;
	}
	return -999;
}

// [DAF-060426] offsetByDuration() might be obsolete now
/*
function offsetByDuration($startdate, $duration) {
	$timeStart = strpos($duration, "T");
	$timestr = substr($duration, $timeStart+1);
	$matches = array();
	
	$hours = preg_match('/(\d+)H/', $timestr, $matches) ? $matches[1] : 0;
	$minutes = preg_match('/(\d+)M/', $timestr, $matches) ? $matches[1] : 0;
		
	$result = strtotime("+${hours} hours", $startdate);
	$result = strtotime("+${minutes} minutes", $result);
	
	return $result;
}
*/

function dirSize($dir) {
	if (is_dir($dir)) {
		$mySize = 0;
		if ($h = opendir($dir)) {
			while (($thisFile = readdir($h)) !== false) {
				if (($thisFile != ".") && ($thisFile != "..")) {
					$mySize += dirSize($dir . "/" . $thisFile);
				}
			}
			closedir($h);
			return $mySize;
		}
		else {
			die("Error opening directory $dir to check size.");
		}
	}
	else {
		// It's a file
		$mySize = filesize($dir);
		if ($mySize === false) {
			die("Error calculating size of $dir.");
		}
		else {
			return $mySize;
		}
	}
}

// ----------------------------------------------------------------------


?>
