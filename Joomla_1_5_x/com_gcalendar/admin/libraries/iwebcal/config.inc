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
 * config.inc: Edit this file to set up iWebCal. For a basic iWebCal setup
 * using the included index.php file, you only need to modify 
 * $iWebCal_CALENDAR_FILE to point to your calendar file. Modify additional
 * variables to customize iWebCal further. Each is preceded by a description.
 *
 * File version 2.0b3, last modified April 29, 2005.
 */

/* +------------------------------------------------------------------------------+
   | USER SETTINGS: Change these to set up iWebCal to show your calendar.         |
   +------------------------------------------------------------------------------+
*/

// Even if you don't modify anything, iWebCal should work when you point your
// browser to the iWebCal directory on a Web server with PHP. There you'll see the
// sample calendar contained in the file Sample.ics. To show your own calendar,
// set $iWebCal_CALENDAR_FILE to the location of your calendar (.ics) file. 
// It can be a local file on the server 
// (like "/Users/myusername/Library/Calendars/Calendar.ics"), a calendar
// accessible via the Web (like "http://mywebsite.com/calendars/Calendar.ics" - but this
// will only work if your PHP server is configured to allow remote fopen() access) or a local
// folder containing one or more calendars (like "/Users/myusername/Library/Calendars").
// You can also specify the name or path of a file relative to the main iWebCal source
// code directory. So, for example, the initial setting of "Sample.ics" works because there
// is a sample calendar file named Sample.ics inside the iWebCal directory.
//$iWebCal_CALENDAR_FILE = ''; 

// $iWebCal_MAIN_FILENAME specifies the filename of the main file (script) used for
// displaying calendar files. It's used as a target for forms and buttons that change
// the display. Generally it's just the filename of whatever PHP page you're using
// to display your calendar. In some cases you may want to specify some kind of
// relative path, but in general you're just re-loading whatever page you're currently
// displaying so a simple filename will suffice.

// NOTE: May need to pull Itemid from current menu item and route later
$iWebConfig['iWebCal_MAIN_FILENAME'] = "index.php?option=com_gcalendar&view=icalendar";

// Your email address, so iWebCal can email you if something goes wrong.
// Leave blank if you don't want iWebCal to email you. It will not be displayed
// to the user.
global $mainframe;
$iWebConfig['iWebCal_ADMINISTRATOR_EMAIL'] = $mainframe->getCfg('mailfrom');


/* +------------------------------------------------------------------------------+
   | Optional Settings: Most people will not need to do anything with these,      |
   | but they are available for advanced users who want to customize iWebCal      |
   | further. Again, these are OPTIONAL and most people can ignore them.          |
   +------------------------------------------------------------------------------+
*/

// Cache Settings: iWebCal's speed is due in part to the fact that it indexes your
// calendar and saves that information as the user moves from page to page. (So,
// for example, the calendar file itself is only read and processed once.) This
// is accomplished using PHP's session functionality. Session information is stored
// in a temporary file on the server. With iWebCal, these files can become fairly large
// (several hundred kilobytes). In most cases, this won't be a problem. However, if your
// site gets a lot of visitors, doesn't have a lot of disk space available on the server,
// or someone decides to attack it by creating a large number of sessions, the disk space
// on your server could get full. If your Web site is hosted on a server shared with other
// users, this might upset your hosting company. Accordingly, iWebCal allows you to limit
// the space used for temporary iWebCal files. Here's how to use it:
// 
// * Create an iWebCal cache directory to store the temporary files. The iWebCal
//   directory already contains a directory named cache that you can use for this.
// * Make sure the cache directory's permissions allow the Web server to write to it.
//   On many servers, this will mean any server user can write to it.
// * Uncomment (remove the two slashes at the beginning) the first line below so 
//   that $iWebCal_LIMIT_CACHE is set to true.
// * If necessary, change the second line below so $iWebCal_CACHE_DIRECTORY points to
//   your cache directory. If you used the included cache directory, you can leave it
//   alone - the default setting of "iWebCal_DEFAULT_CACHE_DIR" is automatically
//   replaced by iWebCal with the proper path.
// * If you like, change the $iWebCal_CACHE_SIZE line below to be the maximum space
//   you'd like the cache directory to use. The default is 20MB. (Note that it will
//   not usually use this much space - that's just the maximum.
// * If you like, change the $iWebCal_SESSION_LENGTH line below. This reflects how long
//   (in minutes) a session must be idle before the server will replace it with a new one.
//   so, a smaller number will mean less time before a full cache directory is no longer full.
// * Set $iWebCal_ADMINISTRATOR_EMAIL above if you'd like iWebCal to send you email notifications
//   when the cache directory is full.
//
// That's it. Now, if your cache directory is ever full, a new user will receive a message
// suggesting he try back later, and you'll receive an email (only one per day, the first time
// it happens) letting you know about it (assuming the mail() function works on your server
// and you've set $iWebCal_ADMINISTRATOR_EMAIL).
//$iWebCal_LIMIT_CACHE = true;

// TODO: address caching
$iWebConfig['iWebCal_CACHE_DIRECTORY'] = JPATH_SITE . DS . 'cache';
$iWebConfig['iWebCal_CACHE_SIZE'] = 40; // megabytes
$iWebConfig['iWebCal_SESSION_LENGTH'] = 5; // minutes



// ERROR REPORTING: The following lines set the PHP error reporting functions. By
// default error reporting is turned on. However, once you've got your site working, 
// you can uncomment the two lines below to prevent the user from seeing PHP errors.
//error_reporting(0);
//error_reporting(E_USER_WARNING | E_USER_ERROR | E_USER_NOTICE);


// $iWebCal_HOUR_HEIGHT is the height of an hour in pixels for the Day and Week views.
// If you change it you'll want to alter the background image img/day-bg.gif
// accordingly.
$iWebConfig['iWebCal_HOUR_HEIGHT'] = 64;


// Width (in percentage of total layout) of a day in Week view when that day contains
// no events.
$iWebConfig['iWebCal_EMPTY_WEEKDAY_PERCENT_WIDTH'] = 8;

// Width (in percentage of total layout) of the label column down the left side of 
// Week and Day views.
$iWebConfig['iWebCal_AXIS_PERCENT_WIDTH'] = 4;



// iWebCal displays a toolbar at the bottom of the page. Its contents are determined
// by this variable, $iWebCal_PAGE_TOOLBAR_ITEMS, which is an array of HTML items.
// If you leave this variable at its default value, the toolbar contains one item, a
// "reload calendar file" link that refreshes the calendar from the original file
// (instead of using cached information). You can add whatever HTML items you like to
// the array and they'll be included in the toolbar. For example, if, after the comma,
// you added the string, "<a href="mailto:myname@mydomain.com">email me</a>, the toolbar
// would include an "email me" item. If you don't want the toolbar at all, just remove
// its definition entirely.
$iWebConfig['iWebCal_PAGE_TOOLBAR_ITEMS'] = array(
	"<a href=\"#\" onclick=\"javacript:document.controlForm.act.value='refresh';document.controlForm.iwebcalview.value='" 
		. JRequest::getVar('iwebcalview', 'month')
		. "';document.controlForm.submit();\">reload calendar file</a>",
);	


// This is the local path to the iWebCal source directory. Leave off the
// trailing slash character. If this variable is not set here, iWebCal
// assumes its value to be the enclosing directory of the script file from
// which iWebCal is run. If you don't customize iWebCal, you don't need
// to worry about this, and should leave it commented out.
$iWebConfig['iWebCal_LOCAL_PATH'] = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_gcalendar'.DS.'libraries'.DS.'iwebcal';



// This should be the URL path (when accessed over the Web) of the iWebCal
// source directory. You don't need the "http://mydomain.com" . Do NOT include
// a trailing slash, even if you're specifying the root directory
// of the site. For example, if the iWebCal directory can be accessed at
// http://mydomain.com/iWebCal, set this variable to "/iWebCal".
// If you don't customize iWebCal, you don't need to worry about this variable:
// iWebCal will set it to the directory containing the script file from which
// iWebCal is run.
$iWebConfig['iWebCal_URL_PATH'] = JURI::base() . 'administrator/components/com_gcalendar/libraries/iwebcal';

// As discussed above under Cache Settings, iWebCal stores a lot of information
// about calendars so it doesn't have to recalculate every time the user moves
// to a new page. However, this means the calendar needs to be reloaded from 
// scratch to reflect changes, either to the calendar or to the iWebCal code.
// Normally this isn't a problem, but if you do update your calendar it could be:
// Users who haven't closed their browsers since the last time they viewed your
// calendar won't see the changes. $iWebCal_CALENDAR_LIFETIME is a fix for that.
// Its value is stored in minutes. When the user requests your calendar after
// more than that number of minutes of inactivity (i.e. no requests for new pages),
// the calendar is reloaded from scratch.
$iWebConfig['iWebCal_CALENDAR_LIFETIME'] = 15; // minutes

$iWebConfig['iwcint_PWRBY_IMG'] = $iWebConfig['iWebCal_URL_PATH'] . "/img/powered-by-logo.gif";
$iWebConfig['iwcint_PWRBY_WIDTH'] = "135";
$iWebConfig['iwcint_PWRBY_HEIGHT'] = "40";
$iWebConfig['iwcint_PWRBY_LINK'] = "http://iwebcal.com";

// --------------------------------------------------------------------------------
// DON'T CHANGE ANYTHING BELOW THIS LINE, PLEASE!

include $iWebConfig['iWebCal_LOCAL_PATH'] . '/base/main.inc';


?>
