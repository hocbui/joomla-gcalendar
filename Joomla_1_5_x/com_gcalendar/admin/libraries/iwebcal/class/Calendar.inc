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
  * Calendar.inc: Defines the Calendar class, which handles parsing and
  * display of calendars (but not calendar controls). Relies on the
  * Property and CalItem classes.
  *
  * File version 2.0b3, last modified April 29, 2005.
  */

$sortInProgress = NULL;

class Calendar {
	var $items;
	var $dateIndex;
	var $repeatingEvents;
	var $discardedProperties;
	var $repeatingInstances; // this variable's use only works because calendar isn't editable
	var $url;
	var $internalUrl;
	var $title;
	var $isValid;
	var $folderContents;
	var $errorString;
	var $taskIndex;
	var $taskShowCompleted;
	var $taskSort;
	var $extraURLVariables;
	var $lastAccess;
	var $config = array();
	
	// --------------------------------------------------
	
	function Calendar($url = NULL, $config) {
		$this->config = $config;
		$this->specifiedUrl = $url;
		$this->initialize();
	}

	function initialize() {
		//$USER_PREFS = $this->config['USER_PREFS'];
		//$iWebCal_Prefs = $this->config['iWebCal_Prefs'];
	
		$this->items = array();
		$this->dateIndex = array();
		$this->taskIndex = array();
		$this->discardedProperties = array();
		$this->repeatingEvents = array();
		$this->repeatingInstances = array();
		$this->title = "Untitled";
		$this->url = NULL;
		$this->internalUrl = NULL;
		$this->isValid = true;
		$this->folderContents = false;
		$this->taskShowCompleted = false;
		$this->taskSort = "";
		$this->extraURLVariables = array();
		$this->lastAccess = 0;
		
		if ($this->specifiedUrl) {
			$this->url = trim($this->specifiedUrl);
			$this->internalUrl = trim($this->specifiedUrl);
			
			
			// See if the file is a directory.
			if (is_dir($this->internalUrl)) {
				// It's a folder. Invalid calendar but it's a folder.
				$this->isValid = false;
				$this->errorString = "Folder specified rather than file. Its contents are available from the Calendar object (see iWebCal documentation).";
				
				// remove trailing slash (if any) from URL
				$this->url = preg_replace("/\/$/", "", $this->url);
				$this->internalUrl = preg_replace("/\/$/", "", $this->internalUrl);
				
				// Now add a trailing slash so we know there's one.
				$this->url = $this->url . "/";
				$this->internalUrl = $this->internalUrl . "/";
				
				$myDirHandle = opendir($this->internalUrl);
				if ($myDirHandle) {
					$this->folderContents = array();
					while ($thisFile = readdir($myDirHandle)) {
						if (preg_match("/\.ics$/", $thisFile)) {
							$this->folderContents[] = $thisFile;
						}
					}
					closedir($myDirHandle);
					if (!sizeof($this->folderContents)) {
						$this->errorString = "Folder specified rather than file, but it contains no calendar files.";
					}
				}
				else {
					$this->errorString = "Folder specified rather than file, but iWebCal was unable to read its contents.";
				}
				
				return NULL;
			}
			
			// [DAF-060430] Moved file-opening to a separate method
			$cal_data = $this->readCalendarFile();
			if (!$cal_data && $this->errorString)
				return NULL;
			
			preg_match("/\/([^\/]*)\.ics/", $this->internalUrl, $matches);
			$this->title = $this->internalUrl;			
			
			if (!$cal_data || !count($cal_data)) {
				$this->isValid = false;
				$this->errorString = "Calendar file is empty.";
				return NULL;
			}
			$firstProp = new Property($cal_data[0], $this->config);
			if (!$firstProp->match("BEGIN", "VCALENDAR")) {
				$this->isValid = false;
				$this->errorString = "Not a valid iCal calendar file.";
				return NULL;
			}
						
			$item = NULL;
			for ($index=1;$index<count($cal_data);$index++) {
				$prop = new Property($cal_data[$index], $this->config);
				
				if ($prop) {
					if ($item) {
						// an item of some type is open
						
						if ($prop->match("BEGIN")) {
							// It's a sub-item (like an alarm). right now we don't handle them.
							// We also assume only one level of nested data.
							// [TO DO] For a future version: Handle this in a more robust manner
							// by allowing CalItems to store a list of sub-items, themselves
							// CalItems.
							while (1) {
								$prop = new Property($cal_data[$index], $this->config);
								$index++;
								if ($prop->match("END")) {
									break;
								}
							}
						}
						if ($prop->match("END")) {
							// something is ending. figure it's our item
							$this->addItem($item);
							$item = NULL;
						}
						else {
							// add property to item
							if (in_array($prop->name, $this->config["ACCEPTED_PROPERTIES"])) {
								$item->addProperty($prop);
							}
							else {
								if (!(in_array($prop->name, $this->discardedProperties))) {
									$this->discardProperty($prop->name);
								}
							}
						}
					}
					else {
						// no current item
						
						$propVal = $prop->value();
						if ($prop->match("BEGIN") && in_array($propVal, $this->config["ACCEPTED_ITEM_TYPES"])) {
							// open an event
							$item = new CalItem($propVal, $this->config);
						}
						elseif ($prop->match("X-WR-CALNAME")) {
							$this->title = $prop->value();
						}
						// [DAF-060426] Added to support Google Calendar better
						elseif ($prop->match('PRODID')) {
							$this->source = $prop->value();
						}
						
						// if not a recognized item begin line, ignore until
						// one is found
					}
				}
						
			}
						
			// [DAF-060426] added: Google Calendars don't have titles. If this is a Google Calendar, create a title.
			if ($this->source && (strpos($this->source, 'Google Calendar') !== FALSE) && ($this->title == $this->internalUrl)) {
				$matches = array();
				if (preg_match('/google.com\/calendar\/ical\/(.*?)@/', $this->title, $matches) && $matches[1]) {
					$this->title = $matches[1] . "'s calendar";
				}
			}
						
			// So OK, we've loaded the file. Store it in user prefs
			$exp = strtotime("+3 months");
			if (!isset($iWebCal_Prefs)) {
				$USER_PREFS = array();
				$USER_PREFS["cal urls"] = array();
				$USER_PREFS["cal titles"] = array();
				setcookie("iWebCal_Prefs", serialize($USER_PREFS), $exp);
			}
			else {
				$USER_PREFS = unserialize(stripslashes($iWebCal_Prefs));
			}
			if (!in_array($this->url, $USER_PREFS["cal urls"])) {
				$USER_PREFS["cal urls"][] = $this->url;
				$USER_PREFS["cal titles"][] = $this->title;
			}
			
			setcookie("iWebCal_Prefs", serialize($USER_PREFS), $exp);
			
			if (count($this->taskIndex)) {
				$this->sortTasks("priority");
			}
		}
	}
	
	// --------------------------------------------------

	function readCalendarFile() {
		// [DAF-060426] If it's a Google username, set internal URL to the appropriate one
		if (preg_match('/^[\w\.]+@[\w\.]+$/', urldecode($this->internalUrl))) {
			$this->internalUrl = 'http://www.google.com/calendar/ical/' . $this->internalUrl . '/public/basic';
		}
						
		// Next, try to open the url as a local file, unchanged
		if (file_exists($this->internalUrl)) {
			$fp = fopen($this->internalUrl, "r");			
		}
		elseif (file_exists($this->config["iWebCal_LOCAL_PATH"] . "/" . $this->internalUrl)) {
			$fp = fopen($this->config["iWebCal_LOCAL_PATH"] . "/" . $this->internalUrl, "r");
		}
		
		else {
			// Not a valid local file as submitted.
			
			// Assume it's remote, and get file piece.
			if (preg_match("/^webcal:\/\//", $this->internalUrl)) {
				$this->internalUrl = substr($this->internalUrl, 9);
			}
			elseif (preg_match("/^http:\/\//", $this->internalUrl)) {
				$this->internalUrl = substr($this->internalUrl, 7);
			}
			
			// [DAF-060430] Updated this area to add CURL support

			// Create a standard HTTP URL
			$this->internalUrl = "http://" . preg_replace("/ /", "%20", $this->internalUrl);
			$this->internalUrl = preg_replace("/ /", "%20", $this->internalUrl);
			
			if (extension_loaded('curl')) {
				$ch = curl_init($this->internalUrl);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_CRLF, 1);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
				$cont = curl_exec($ch);
				if (curl_errno($ch)) {
					$this->errorString = "Calendar file couldn't be opened: " . curl_error($ch);
					$this->isValid = false;
					if (is_dir($this->internalUrl)) {
						$this->isFolder = true;
					}
					return NULL;
				}
				curl_close($ch);
			}
			else {
				$fp = fopen($this->internalUrl, "r");
				if (!$fp) {
					$this->isValid = false;
					if (is_dir($this->internalUrl)) {
						$this->isFolder = true;
					}
					$this->errorString = "Calendar file couldn't be opened.";
					return NULL;
				}
			}
		}

		if (isset($fp)) {
			// data was obtained by opening a file pointer
			$lineCount = 0;
			$cal_data = array();
			while ($line = fgets($fp, 4096)) {
				$lineCount++;
				if ($lineCount > 24000) {
					$this->isValid = false;
					$this->errorString = "Sorry, iWebCal cannot display iCal files this large.";
					return NULL;
				}
				$line = rtrim($line, "\n\r\0"
				);
				$line_nospace = ltrim($line);
				if ($line_nospace != $line) {
					// this is part of the previous line
					$cal_data[count($cal_data)-1] .= $line_nospace;
				}
				else {
					$cal_data[] = $line_nospace;
				}		
			}
			fclose($fp);
		}
		else {
			// data was obtained from CURL
			$cal_data = split("\r\n", $cont);
		}
		
		return $cal_data;
	}
	
	function addItem($item) {
		// Add item to main array
		$this->items[] = &$item;
		if ($item->type == "VEVENT") {
			// Index item by date(s) if appropriate
			$itemDates = $item->getDates();
			if ($itemDates) {
				foreach($itemDates as $thisDate) {
					$parts = explode("-", $thisDate);
					
					$this->dateIndex[(int)$parts[0]][(int)$parts[1]][(int)$parts[2]][] = &$item;
				}
			}
			
			// Index item by repeating schedule if appropriate.
			if ($item->getProperty("RRULE")) {
				$this->repeatingEvents[] = &$item;
			}
		}
		elseif ($item->type == "VTODO") {
			// Add to task index
			$this->taskIndex[] = &$item;
		}				
	}
	
	// --------------------------------------------------

	function discardProperty($name) {
		if (!(in_array($name, $this->discardedProperties))) {
			$this->discardedProperties[] = $name;
		}
	}
	
	// --------------------------------------------------

	function dprint() {
		echo "<div class=\"Debug\" style=\"clear:both;margin-top: 40px\">";
		
		/*
		if (isset($this->indexedDates)) {
			echo "<ul>";
			foreach($this->indexedDates as $i) {
				echo "<li>$i</li>";
			}
			echo "</ul>";
		}
		*/
		
		echo "<h3>All Repeating Events</h3>";
		echo "<ul>";
		foreach ($this->repeatingEvents as $evt) {
			echo "<li>" . $evt->summary() . "</li>";
		}
		echo "</ul>";
	
		echo "<h3>Non-Repeating Events (2005)</h3>";
		echo "<ul>";
		foreach($this->dateIndex as $y => $i) {
			if ($y == 2005) {
				foreach($i as $m => $j) {
					foreach($j as $d => $k) {
						echo "<li>$m-$d-$y";
						if (is_array($k)) {
							echo "<ul>";
							foreach($k as $evt) {
								echo "<li>" . $evt->summary() . "; " . $evt->isAllDay() . "</li>";
							}
							echo "</ul>";
						}
						echo "</li>";
					}
				}
			}
		}
		echo "</ul>";		
		
		echo "<h3>Indexed Repeating Instances</h3>";
		echo "<ul>";
		foreach($this->repeatingInstances as $y => $i) {
			foreach($i as $m => $j) {
				foreach($j as $d => $k) {
					echo "<li>$m-$d-$y";
					if (is_array($k)) {
						echo "<ul>";
						foreach($k as $evt) {
							echo "<li>" . $evt->summary() . "</li>";
						}
						echo "</ul>";
					}
					echo "</li>";
				}
			}
		}
		echo "</ul>";
		
		echo "<p><b>Discarded Properties:</b><br>";
		foreach($this->discardedProperties as $dp) {
			echo $dp;
			echo "<br>";
		}
		echo "</p>";
		?>
		<table cellspacing="1" cellpadding="6" bgcolor="#999999">
		<?php 		
		$i=0;
		foreach($this->items as $item) {
			echo "<tr><td bgcolor=#ffffff valign=top>$i</td><td bgcolor=#ffffff>";
			$item->dprint();
			echo "</td></tr>";
			$i++;
		}
		echo "</table>";
		
		echo "</div>";
	}
	
	// --------------------------------------------------
	
	function itemsForDate($year, $month, $day) {
		if (isset($this->dateIndex[$year][$month][$day])) {
			$staticInstances = &$this->dateIndex[$year][$month][$day];
		}
			
		if (isset($this->repeatingInstances[$year][$month][$day])) {
			// we've calculated this day before and cached it. Hooray!										
			$cachedInstances = &$this->repeatingInstances[$year][$month][$day];
			$result = array();
			if ($cachedInstances == -1) {
				if (isset($staticInstances)) {
					// TODO: May wish to eliminate this sort and instead insert new items at the proper place when adding.
					 usort($staticInstances, array("CalItem", "sortComparison"));
					 return $staticInstances;
				}
				else
					return array();
			}
			if (isset($staticInstances)) {
				$result = array_merge($cachedInstances, $staticInstances);
			}
			else {
				$result = &$cachedInstances;
			}
			if (sizeof($result)) {
				usort($result, array("CalItem", "sortComparison"));
			}
			return $result;
		}		

		// For the moment this is for debug purposes only
		if (!isset($this->indexedDates)) $this->indexedDates = array();
		$this->indexedDates[] = "$year-$month-$day";
	
		$result = array();
		foreach ($this->repeatingEvents as $evt) {
			if ($evt->repeatsOnDate(strftime("%Y-%m-%d", strtotime("${year}-${month}-${day}")))) {
				$result[] = $evt;
			}
		}
		if (count($result)) {
			$this->repeatingInstances[$year][$month][$day] = $result;
		}
		else {
			$this->repeatingInstances[$year][$month][$day] = -1;
		}
		if (isset($staticInstances)) {
			$result = array_merge($result, $staticInstances);
		}
	    usort($result, array("CalItem", "sortComparison"));
		return $result;
	}

	// --------------------------------------------------

	function printMonth($year, $month, $day) {
		$WEEKDAY_FULL_NAMES = $this->config['WEEKDAY_FULL_NAMES'];
		$iWebCal_MAIN_FILENAME = $this->config['iWebCal_MAIN_FILENAME'];
		$today = getdate();
		
		// 0 is Sunday
		// TODO - Solaris may label Sunday as 1; investigate
		$startDayOfWeek = strftime("%u", strtotime("${year}-${month}-01")) % 7;
		
		?>
		<table class="iWebCalendar CalMonth" width="100%" cellspacing="0" cellpadding="0">
			<tr>
				<?php 
				// print days of the week at the top
				for ($i=0; $i<7; $i++) {
					?>
					<th><?php echo $WEEKDAY_FULL_NAMES[$i];?></th>
					<?php 				}
				?>
			</tr>
			<?php
			for ($i=28; $i<33; $i++) {
				if (!checkdate($month, $i, $year)) {
					$lastDay = $i-1;
					$lastDaySlot = ($lastDay - 1) + $startDayOfWeek;
					$lastDayOfWeek = $lastDaySlot % 7;
					$numberOfSlotsNeeded = $startDayOfWeek + $lastDay;
					$numRows = floor($numberOfSlotsNeeded / 7) + 1;
					break;
				}
			}
			
			$colWidth = "14%";
			$rowHeight = (int)round(100 / $numRows) . "%";
			for ($i=0;$i<$startDayOfWeek;$i++) {
				?><td class="EmptyCell"></td><?php		
			}
			for ($i=0;$i< $lastDay;$i++) {
				if (($i + $startDayOfWeek) % 7 == 0) {
					// start a new row
					?><tr><?php
				}
				$thisDay = $i + 1; 
				$thisFile = $this->url;
				$thisLink = "${iWebCal_MAIN_FILENAME}&iwebcalview=day&year=${year}&month=${month}&day=${thisDay}";
				foreach ($this->extraURLVariables as $varName => $varVal) {
					$thisLink .= "&${varName}=${varVal}";
				}
				?>
				<td<?php if (($thisDay == $today["mday"]) && ($month == $today["mon"])) { ?> class="Today"<?php } ?>>
						<a class="DayNum" href="<?php echo JRoute::_($thisLink); ?>"><?php echo $thisDay ?></a>
						<?php
						$myItems = $this->itemsForDate($year, $month, $i+1);
						if ($myItems) {
							foreach($myItems as $item) {
								$item->display("month", $thisLink);
							}
						}
						?>
				</td>
				<?php
				if (($i + $startDayOfWeek) % 7 == 6) {
					?></tr><?php 				}
			}
			for ($i=$lastDay + $startDayOfWeek;$i<($numRows * 7);$i++) {
				// [DAF-060426] fixed typo
				?><td class="EmptyCell"></td><?php 				
			}
			?>
		</table>
		<?php
	}
	
	function getDayLayout($year, $month, $day) {
		// A layout is a 2D array representation of a day's events, ready
		// to be written to HTML. The array contains zero or more columns
		// of timed events, followed by zero or one column of all-day
		// events. In either case, zero events means zero columns. In the
		// case of timed events, the number of columns depends on the
		// number of overlapping events. If no events overlap there will
		// be one column. For each event that overlaps events in other
		// columns, a new column is created. These columns translate
		// directly to screen.
	
		$items = $this->itemsForDate($year, $month, $day);
		if (!$items || !count($items)) return NULL;
		// from here on out we can assume that $items contains at least one item
		
		usort($items, "startTimeComparison");
		// items is now sorted by start date ascending			
		
		$columnCount = 1;
		$result = NULL;
		$openItems = array();
		$openItems[0] = array();
		$untimedItems = array();
				
		foreach($items as $item) {
			if ($item->isAllDay()) {
				$untimedItems[] = $item;
			}
			else {
				$itemStart = $item->startDate();
				// remove closed items, and determine the lowest column
				// with no overlap
				
				// $lowestColumn is 1-indexed.
				$lowestColumn = 0;
				for ($i=$columnCount-1;$i>=0;$i--) {
					$overlap = false;
					foreach ($openItems[$i] as $thisKey => $thisOpenItem) {
						if ($thisOpenItem->endDate() <= $itemStart) {
							unset($openItems[$i][$thisKey]);
						}
						else {
							$overlap = true;
						}
					}
					if (!$overlap) $lowestColumn = $i + 1;
				}
				
				if ($lowestColumn) {
					// an existing column has room for this item
					$openItems[$lowestColumn-1][] = $item;
					$result[$lowestColumn-1][] = $item;
				}
				else {
					// we need a new column
					$openItems[$columnCount][] = $item;
					$result[$columnCount][] = $item;
					$columnCount++;
				}
			}
		}
		$result[] = $untimedItems;
		
		return $result;
		
	}
	
	function printUntimedEventsForDay($layout, $view) {
		$iWebCal_URL_PATH = $this->config['iWebCal_URL_PATH'];
		if ($layout) {
			$classString = ($view == "week") ? "Week" : "Day";
			$items = array_pop($layout);
			
			if ($items && count($items)) {
				for ($i=0;$i<count($items);$i++) {
					echo "<div class=\"Event\">";
					$items[$i]->display($view);
					echo "</div>";
				}
			}
		}
	}
		
	function printTimedEventsForDay($layout, $view, $initialMinute) {
		$iWebCal_HOUR_HEIGHT = $this->config['iWebCal_HOUR_HEIGHT'];
		$iWebCal_URL_PATH = $this->config['iWebCal_URL_PATH'];
		
		if ($layout && (count($layout) > 1)) {
			// remove untimed? events from layout
			array_pop($layout);

			$whichCol = 0;
			$colWidth = (int)floor(100 / count($layout));
			foreach($layout as $col) {
				?><div class="Col" style="width:<?php echo $colWidth-0.5 ?>%; left: <?php echo $colWidth * $whichCol ?>%"><?php
					$currentOffset = $initialMinute;
					foreach($col as $item) {
						$myStart = $item->startDate();
						$myEnd = $item->endDate();
					
						// [TO DO] Do we need a more robust way to do this? Timed events are generally considered
						// to be confined to one day right now. 
					
						// The fix here for midnight helps users who set their
						// end times to midnight, and in addition works with an iCal bug that sets a midnight end time
						// to the wrong day. 
						if (!((int)strftime("%H%M", $myEnd))) {
							// End date is midnight.
						
							// iCal handles this wrong and sets the end date prior to the
							// start date. Fix this.
						
							// Now decrement end date just slightly so it's on the same day as start
							$myEnd--;
						}								
					
						// $myStart and $myEnd are UNIX timestamps representing the start and
						// end time of the event
						
						// Get the number of minutes (past midnight) of the start and end times
						$myStartOffset = ((int)strftime("%H", $myStart) * 60) + (int)strftime("%M", $myStart);
						$myEndOffset = ((int)strftime("%H", $myEnd) * 60) + (int)strftime("%M", $myEnd);
						
						// Get the duration in minutes
						$myDuration = $myEndOffset - $myStartOffset;
						
						// Subtract initial minute from start to get actual offset
						$myStartOffset = $myStartOffset - $initialMinute;
						
						// Now, convert minute values to pixels.
						$myStartOffset = $myStartOffset * $iWebCal_HOUR_HEIGHT / 60;
						$myDuration = $myDuration * $iWebCal_HOUR_HEIGHT / 60;
						?>
						<div class="Event" style="height:<?php echo $myDuration; ?>px; top:<?php echo $myStartOffset ?>px" 
							onmouseover="eventOver(this)"
							onmouseout="eventOut(this)">
							<?php
							$item->display($view); 
							?>
						</div>
						<?php 								
					}
					$whichCol++;
				?></div><?php
			}
		}
	}
	
	function printDay($year, $month, $day) {
		$iWebCal_HOUR_HEIGHT = $this->config['iWebCal_HOUR_HEIGHT'];
		$iWebCal_URL_PATH = $this->config['iWebCal_URL_PATH'];
		$dayLayout = $this->getDayLayout($year, $month, $day);
		
		// get start time for the first event
		if (count($dayLayout) > 1) {
			$firstStart = $dayLayout[0][0]->startDate();
			$initialMinuteOffset = (int)strftime("%H", $firstStart) * 60;
		}
		else {
			$initialMinuteOffset = 540; // 9am
		}
		
		// prepare to print hour marks
		$firstHour = (int)($initialMinuteOffset / 60);
		
		// get end time for the last event
		$lastEnd = 0;
		for ($i=0;$i<count($dayLayout)-1;$i++) {
			if (count($dayLayout[$i])) {
				$thisEnd = $dayLayout[$i][count($dayLayout[$i])-1]->endDate();
				if ($thisEnd > $lastEnd) $lastEnd = $thisEnd;
			}
		}
		if ($lastEnd == 0) {
			$lastHour = 17;
		}
		else {
			$lastHour = (int)strftime("%H", $lastEnd) + 1;
		}
		
		// TODO - Is there a way to avoid the amount of nesting used below?
		?>
		<div class="iWebCalendar CalDay">
			<div class="UntimedEvents">
				<?php $this->printUntimedEventsForDay($dayLayout, "day"); ?>
			</div>
			<table class="TimedArea" cellspacing="0" cellpadding="0">
				<tr>
					<td class="DayAxis" >
						<?php $this->printDayAxis($firstHour, $lastHour); ?>
					</td>
					<td class="TimedEvents">
						<div class="Inner">
							<?php $this->printTimedEventsForDay($dayLayout, "day", $initialMinuteOffset); ?>
						</div>
						&nbsp;
					</td>
				</tr>
			</table>
		</div>
		<!-- TODO - Better way? -->
		<div class="Clr"></div>
		<?php
	}
	
	function printDayAxis($startHr, $endHr) {
		for ($hour=$startHr; $hour<=$endHr; $hour++) {
			?><div><?php
				if (($hour == 0) || ($hour == 24)) echo "mid";
				elseif ($hour == 12) echo "noon";
				else {
					echo $hour % 12;
					echo ":00";
				}
			?></div><?php
		}
	}
	
	function printWeek($year, $month, $day) {
		$iWebCal_HOUR_HEIGHT = $this->config['iWebCal_HOUR_HEIGHT'];
		$iWebCal_MAIN_FILENAME = $this->config['iWebCal_MAIN_FILENAME'];
		$iWebCal_URL_PATH = $this->config['iWebCal_URL_PATH'];
		
		$tDate = strtotime("${year}-${month}-${day}");
		if (strftime("%u", $tDate) == 7) {
			$firstDisplayedDate = $tDate;
		}
		else {
			$firstDisplayedDate = strtotime("last Sunday", strtotime("${year}-${month}-${day}"));
		}
		$dayLayouts = array();
		$lastHour = 0;
		$firstHour = 24;
		for ($j=0;$j<7;$j++) {
			$thisDate = strtotime("+${j} days", $firstDisplayedDate);
			$displayedDates[] = getdate($thisDate);
		}
		foreach ($displayedDates as $dInfo) {
			$thisLayout = $this->getDayLayout($dInfo["year"], $dInfo["mon"], $dInfo["mday"]);
			if (count($thisLayout) > 1) {
				for ($i=0;$i<count($thisLayout)-1;$i++) {
					if (count($thisLayout[$i])) {
						$thisEndHour = (int)strftime("%H", $thisLayout[$i][count($thisLayout[$i])-1]->endDate());
						if ($thisEndHour > $lastHour) $lastHour = $thisEndHour;
						
						$thisStartHour = (int)strftime("%H", $thisLayout[0][0]->startDate());
						if ($thisStartHour < $firstHour) $firstHour = $thisStartHour;
					}
				}
			}
			
			$dayLayouts[] = $thisLayout;
		}

		// get start time for the first event
		if ($firstHour == 24) $firstHour = 9;

		$initialMinuteOffset = $firstHour * 60;

		// get end time for the last event
		if (!$lastHour) $lastHour = 17;
			
		?>
		<table class="iWebCalendar CalWeek" cellspacing="0" cellpadding="0">
			<tr>
				<td class="Empty"></td>
				<?php	// Possibly for absolute positioning: calculate column widths based on # sub-cols
				$totalSubCols = 0;
				$totalEmptyCols = 0;
				$dayIndex = 0;
				$subColCounts = array();
				foreach($dayLayouts as $layout) {
					if (count($layout)) {
						$thisSubCount = ((count($layout) > 2) ? count($layout)-1 : 1);
						$subColCounts[$dayIndex] = $thisSubCount;
						$totalSubCols += $thisSubCount;
					}
					else {
						$thisSubCount = 0;
						$subColCounts[$dayIndex] = 0;
						$totalEmptyCols++;
					}
					$dayIndex++;
				}
				$dayIndex = 0;
				$totalNonEmptyWidth = 100 - $this->config["iWebCal_AXIS_PERCENT_WIDTH"] 
					- ($totalEmptyCols * $this->config["iWebCal_EMPTY_WEEKDAY_PERCENT_WIDTH"]);
				foreach ($displayedDates as $dInfo) {
					$myColWidth = ($subColCounts[$dayIndex] ? (int)floor($subColCounts[$dayIndex] / $totalSubCols * $totalNonEmptyWidth)
						: $this->config["iWebCal_EMPTY_WEEKDAY_PERCENT_WIDTH"]);
					?><th style="width: <?php echo $myColWidth; ?>%"><?php
						$myURL = $this->url;
						$thisLink = "${iWebCal_MAIN_FILENAME}&iwebcalview=day&year=" . 
							$dInfo["year"] . 
							"&month=" . $dInfo["mon"] . 
							"&day=" . $dInfo["mday"];
						foreach ($this->extraURLVariables as $varName => $varVal) {
							$thisLink .= "&${varName}=${varVal}";
						}
						echo "<a href=\"" . JRoute::_($thisLink) . "\">";
						echo substr($dInfo["weekday"], 0, 3);
						echo " ";
						echo $dInfo["mday"];
						echo "</a>";
					?></th><?php 			 		$dayIndex++;
				}
				?>
			</tr>
			<tr class="UntimedEvents">
				<td class="Empty"></td>
				<?php 		 		
				foreach ($dayLayouts as $thisLayout) {
					?>
					<td><?php $this->printUntimedEventsForDay($thisLayout, "week"); ?></td>
					<?php 				
				}
				?>
			</tr>
			<tr class="TimedEvents">
				<td class="DayAxis">
					<?php $this->printDayAxis($firstHour, $lastHour); ?>
				</td>
				<?php 	
				$dayIndex = 0;
				foreach ($dayLayouts as $thisLayout) {
					?>
					<td>
						<div class="Inner">
							<?php $this->printTimedEventsForDay($thisLayout, "week", $initialMinuteOffset); ?>
						</div>
					</td>
					<?php 					$dayIndex++;		
				}
				?>
			</tr>
		</table>
		<?php
	}
	
	function taskSortHeader($content, $replace) {
		$iWebCal_ENABLE_TASK_SORTING = $this->config['iWebCal_ENABLE_TASK_SORTING'];
		$iWebCal_TASK_SORT_DESTINATION = $this->config['iWebCal_TASK_SORT_DESTINATION'];
		
		echo "<th>";
		if ($iWebCal_ENABLE_TASK_SORTING && ($this->taskSort != $replace)) {
			echo "<a href=\"" . preg_replace("/\[\[NEW_SORT\]\]/", $replace, $iWebCal_TASK_SORT_DESTINATION) . "\">";
			echo $content;
			echo "</a>";
		}
		else {
			echo $content;
		}
		echo "</th>\n";
	}

	function printTasks($year, $month, $day) {
		$iWebCal_URL_PATH = $this->config['iWebCal_URL_PATH'];
		$iWebCal_TASK_SORT_DESTINATION = $this->config['iWebCal_TASK_SORT_DESTINATION'];
		$iWebCal_ENABLE_TASK_SORTING = $this->config['iWebCal_ENABLE_TASK_SORTING'];
		?>
		<table class="iWebCalendar CalTasks" cellspacing="0" cellpadding="0">
			<tr>
				<?php 				$this->taskSortHeader("<img src=\"" . $iWebCal_URL_PATH . "/img/completed-header.gif\" height=\"12\" width=\"15\" border=\"0\">", "completed");
				$this->taskSortHeader("Description", "summary");
				$this->taskSortHeader("Due Date", "due");
				$this->taskSortHeader("Priority", "priority");
				?>
			</tr>
			<?php
			foreach ($this->taskIndex as $thisTask) {
				if ($prop = $thisTask->getProperty("STATUS")) {
					$thisCompleted = $prop->value();
				}
				else {
					$thisCompleted = 0;
				}
				
				if ($this->taskShowCompleted || !$thisCompleted) {
					if ($prop = $thisTask->getProperty("PRIORITY")) {
						switch ($prop->value()) {
							case 1:
								$prio = "Very&nbsp;Important";
								$cellStyle = "HighPriority";
								break;
							case 5:
								$prio = "Important";
								$cellStyle = "MedPriority";
								break;
							case 9:
								$prio = "Not&nbsp;Important";
								$cellStyle = "LowPriority";
								break;
							default:
								$prio = "None";
								$cellStyle = "";
								break;
						}
					}
					else {
						$prio = "None";
						$cellStyle = "";
					}
					if ($thisCompleted) {
						$cellStyle = "Completed";
						$completedImg = "<img src=\"${iWebCal_URL_PATH}/img/completed-check.gif\" width=\"15\" height=\"12\" border=\"0\" />";
					}
					else {
						$completedImg = "";
					}
	
					echo "<tr class=\"${cellStyle}\">";
					
					echo "<td>${completedImg}</td>";
					echo "<td>" . $thisTask->summary() . "</td>";
					if ($prop = $thisTask->getProperty("DUE")) {
						$dueDate = strftime("%m.%d.%Y", strtotime(substr($prop->value(), 0, 8)));
					}
					else {
						$dueDate = "";
					}
					echo "<td>${dueDate}</td>";
					echo "<td>${prio}</td>";	
					echo "</tr>";
				}	
			}
			?>
		</table>
		<?php
	}


	function printCal($year, $month, $day, $view) {
		$year = (int)$year;
		$month = (int)$month;
		$day = (int)$day;
		switch($view) {
			case "month":
				$this->printMonth($year, $month, $day);
				break;
			case "day":
				$this->printDay($year, $month, $day);
				break;
			case "week":
				$this->printWeek($year, $month, $day);
				break;
			case "tasks":
				$this->printTasks($year, $month, $day);
				break;
		}
		$this->lastAccess = time();
	}
	
	function printViewTitle($year, $month, $day, $view, $suppressLogo = "false") {
		global $mainframe;
		
		$params =& $mainframe->getPageParameters();
		$format = $params->get('calendardateformat');
		
		$year = (int)$year;
		$month = (int)$month;
		$day = (int)$day;
		switch($view) {
			case "month":
				if ($format == 'year') {
					echo $year;
					echo " ";
					echo $this->config["MONTH_FULL_NAMES"][$month-1];
				} else {
					echo $this->config["MONTH_FULL_NAMES"][$month-1];
					echo " ";
					echo $year;
				}
				break;
			case "week":
				$tDate = strtotime("${year}-${month}-${day}");
				if (strftime("%u", $tDate) == 7) {
					$firstDisplayedDate = $tDate;
				}
				else {
					$firstDisplayedDate = strtotime("last Sunday", $tDate);
				}
				$lastDisplayedDate = strtotime("+6 days", $firstDisplayedDate);
				$infoS = getdate($firstDisplayedDate);
				$infoF = getdate($lastDisplayedDate);
				
				if ($infoS["year"] != $infoF["year"]) {
					$m1 = substr($infoS["month"], 0, 3);
					$m2 = substr($infoF["month"], 0, 3);
					
					if ($format == 'month') {
						echo "${m1} " . $infoS["mday"] . ", " . $infoS["year"] . " - ${m2} " . $infoF["mday"] . ", " . $infoF["year"];
					} else if ($format == 'day') {
						echo $infoS["mday"] . " ${m1} " . $infoS["year"] . " - "  . $infoF["mday"] . " ${m2} ". $infoF["year"];
					} else {
						echo $infoS["year"] . " ${m1} " . $infoS["mday"] . " - " . $infoF["year"] . " ${m2} " . $infoF["mday"];
					}
					
					
				}
				elseif ($infoS["mon"] != $infoF["mon"]) {
					$m1 = substr($infoS["month"], 0, 3);
					$m2 = substr($infoF["month"], 0, 3);
					
					if ($format == 'month') {
						echo "${m1} " . $infoS["mday"] . " - ${m2} " . $infoF["mday"] . ", " . $infoS["year"];
					} else if ($format == 'day') {
						echo $infoS["mday"] . " ${m1} - " . $infoF["mday"] . " ${m2} " . $infoS["year"];
					} else {
						echo $infoS["year"] . " ${m1} " . $infoS["mday"] . " - ${m2} " . $infoF["mday"];
					}
					
				}
				else {
					if ($format == 'month') {
						echo $infoS["month"] . " " . $infoS["mday"] . " - " . $infoF["mday"] . ", " . $infoS["year"];
					} else if ($format == 'day') {
						echo $infoS["mday"] . " - " . $infoF["mday"] . " " . $infoS["month"] . " " . $infoS["year"];
					} else {
						echo $infoS["year"] . " " . $infoS["month"] . " ". $infoS["mday"] . " - " . $infoF["mday"];
					}
					
				}
				break;
			case "day":
				$tDate = strtotime("${year}-${month}-${day}");
				if ($format == 'month') {
					echo strftime("%A, %b %e, %Y", $tDate);
				} else if ($format == 'day') {
					echo strftime("%A, %e %b %Y", $tDate);
				} else {
					echo strftime("%A, %Y %b %e", $tDate);
				}
				
				break;
			case "tasks":
				echo "Tasks";
				break;
		}
				
				
	}
	
	function wake() {
		if (($this->lastAccess - time()) > ($this->config['iWebCal_CALENDAR_LIFETIME'] * 60))
			$this->initialize();		
	}
	
	function addURLVariable($name, $value) {
		$this->extraURLVariables[$name] = $value;
	}
	
	function showCompletedTasks($newSetting) {
		if ($newSetting) {
			$this->taskShowCompleted = true;
		}
		else {
			$this->taskShowCompleted = false;
		}
	}
	
	function sortTasks($column) {
		global $sortInProgress;
		if ($column != $this->taskSort) {
			$sortInProgress = $column;
			usort($this->taskIndex, "taskCompare");
			$this->taskSort = $column;
			$sortInProgress = NULL;
		}
	}
}



// ------

function startTimeComparison($item1, $item2) {
	$date1 = (int)strftime("%H%M", $item1->startDate());
	$date2 = (int)strftime("%H%M", $item2->startDate());
	
	if ($date1 == $date2) return 0;
	return ($date1 > $date2) ? 1 : -1;
}

function taskdueCompare($item1, $item2) {
	$prop1 = $item1->getProperty("DUE");
	$prop2 = $item2->getProperty("DUE");
	if ($prop1 && $prop2) {
		$due1 = $prop1->value();
		$due2 = $prop2->value();
		if ($due1 < $due2) return -1;
		return ($due1 > $due2);
	}
	elseif ($prop1) return -1;
	else return 1;
}

function taskpriorityCompare($item1, $item2) {
	$prop1 = $item1->getProperty("PRIORITY");
	$prop2 = $item2->getProperty("PRIORITY");
	if ($prop1 && $prop2) {
		$pri1 = $prop1->value();
		$pri2 = $prop2->value();
		if ($pri1 < $pri2) return -1;
		return ($pri1 > $pri2);
	}
	elseif ($prop1) return -1;
	else return 1;
}

function taskcompletedCompare($item1, $item2) {
	$prop1 = $item1->getProperty("STATUS");
	$prop2 = $item2->getProperty("STATUS");
	if ($prop1 && $prop2) return 0;
	elseif ($prop1) return -1;
	else return 1;
}

function tasksummaryCompare($item1, $item2) {
	$prop1 = $item1->summary();
	$prop2 = $item2->summary();
	return strcasecmp($prop1, $prop2);
}

function taskCompare($item1, $item2) {
	global $sortInProgress;
	eval("\$result = task${sortInProgress}Compare(\$item1, \$item2);");
	return $result;
	return 0;
}


?>