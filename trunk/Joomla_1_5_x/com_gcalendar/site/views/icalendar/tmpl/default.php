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
 * @version $Revision: 2.1.0 $
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

if(!is_array($this->calendars)){
	echo JText::_( 'NO_CALENDAR' );
}else{
	function getFiles($calendars){
		$filePath = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_gcalendar'.DS.'libraries'.DS.'iwebcal'.DS.'calendars';

		$googleHost = 'http://www.google.com/calendar/ical/';
		$files = array();
		foreach($calendars as $calendar) {
			if($calendar->selected){
				$calurl = $googleHost.$calendar->calendar_id;
				if(!empty($calendar->magic_cookie)){
					$calurl .= '/private-'.$calendar->magic_cookie;
				}else{
					$calurl .= '/public';
				}
				$calurl .= '/basic.ics';

				if(function_exists('curl_exec')){
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_HEADER, 0);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //Set curl to return the data instead of printing it to the browser.
					curl_setopt($ch, CURLOPT_URL, $calurl);
					$data = curl_exec($ch);
					curl_close($ch);
				}else{
					$data = file_get_contents($calurl);
				}
				$src = $filePath.DS.$calendar->id.'.ics';
				$fh = fopen($src, 'w');
				fwrite($fh, $data);
				fclose($fh);
				$files[] = $src;
			}
		}
		return $files;
	}

	$cache = & JFactory::getCache();
	$files = $cache->call('getFiles', $this->calendars );

	if(count($files)<1)return;
	include JPATH_ADMINISTRATOR.DS.'components'.DS.'com_gcalendar'.DS.'libraries'.DS.'iwebcal'.DS.'config.inc';
	
	$iWebConfig['iWebCal_CALENDAR_FILE'] = $files[0];
	$my_iWebCal = new iWebCal($iWebConfig); // Creates a new iWebCal viewer and calendar based on your settings in config.inc.
	$my_iWebCal->includes(); // Includes stylesheets and scripts needed by the calendar viewer.
	?>
<div class="iWebCal_Page">
<div class="componentheading"><?php 
echo $my_iWebCal->title(); // Gives the page a title based on the filename of the calendar.
?></div>
<?php $my_iWebCal->display(); // Displays the calendar and controls for it (i.e. most of the page).

// If you need to do debugging, you can use Calendar's dprint() method to print out raw Calendar
// object data in a reasonably readable form. Just comment out the call to printCal() above and
// uncomment the line below. For actual deployment, make sure the dprint() call is commented out
// again.
// $my_iWebCal->cal->dprint();
?></div>
<?php

function showDetails($path)
{
	$title = JRequest::getVar('title', 'Event Details');
	$content = $_GET['content'];
	$content = unserialize($content);

	?>
<html lang="en" xml:lang="en" xmlns="http://www.w3.org/1999/xhtml">

<head>
<title><?php echo $title; ?></title>
<link href="<?php echo $path; ?>/include/iWebCal.css" type="text/css"
	rel="stylesheet" media="all" />
<meta http-equiv="content-type" content="text/html;charset=utf-8" />
</head>

<body class="PopupEventInfo">
<h1><?php echo $title; ?></h1>
<div id="content"><?php 

$filter =& JInputFilter::getInstance();

echo "<p>" . $filter->clean($content["summ"]) . "</p>";
foreach ($content as $key => $item) {

	$key = $filter->clean($key);
	$item = $filter->clean($item, 'array');

	if ($key != "summ") {
		echo "<h3>${key}:</h3>";
		if (is_array($item)) {
			foreach ($item as $p) {
				echo "<p>" . urldecode(stripslashes($p)) . "</p>";
			}
		}
		else {
			echo "<p>" . urldecode(stripslashes($item)) . "</p>";
		}
}
}
?></div>
</body>

</html>
<?php
}
}
?>