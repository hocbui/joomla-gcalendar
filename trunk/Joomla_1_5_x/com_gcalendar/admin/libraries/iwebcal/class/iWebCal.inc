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
 * iWebCal.inc: Defines the iWebCal class, which read a calendar file;
 * creates a Calendar object for it; and displays the calendar and controls.
 * Relies on the Calendar, CallItem, and Property classes.
 *
 * File version 2.0b3, last modified April 29, 2005.
 */

class iWebCal {		
	var $cache_directory;
	var $cache_size;
	var $main_filename;
	var $error = false;
	var $config = array();
	
	var $cal;
	
	var $file, $today, $month, $year, $day, $view, $sort, $showCompleted;

	function iWebCal($config) {
		$this->config = $config;
		
		$this->limit_caching = isset($this->config['iWebCal_LIMIT_CACHE']) ? $this->config['iWebCal_LIMIT_CACHE'] : false;
		$this->cache_directory = $this->config['iWebCal_CACHE_DIRECTORY'];
		$this->cache_size = $this->config['iWebCal_CACHE_SIZE'];
		$this->main_filename = $this->config['iWebCal_MAIN_FILENAME'];

		// Globals, for convenience
		$this->file = $this->config['iWebCal_CALENDAR_FILE'];
		
		$this->today = getdate();
		if (isset($_GET["date"])) {
			$dateInfo = getdate(strtotime($_GET["date"]));
			$this->year = $dateInfo["year"];
			$this->month = $dateInfo["mon"];
			$this->day = $dateInfo["mday"];
		}
		else {
			$this->month = isset($_GET["month"]) ? $_GET["month"] : $this->today["mon"];
			$this->year = isset($_GET["year"]) ? $_GET["year"] : $this->today["year"];
			$this->day = isset($_GET["day"]) ? $_GET["day"] : $this->today["mday"];
			if (!checkdate($this->month, $this->day, $this->year)) {
				$this->day = 1;
			}
		}
		$this->view = isset($_GET["iwebcalview"]) ? $_GET["iwebcalview"] : "month";
		
		$this->year = (int)$this->year;
		$this->month = (int)$this->month;
		$this->day = (int)$this->day;
		
		$this->sort = isset($_GET["sort"]) ? $_GET["sort"] : "priority";
		$this->showCompleted = isset($_GET["showCompleted"]) ? $_GET["showCompleted"] : false;
		
		// Get user agent info
		if (isset($_SERVER['HTTP_USER_AGENT'])) {
			$uaRaw = strtolower($_SERVER['HTTP_USER_AGENT']);
			$this->uaVersion = "unk";
			if (strpos($uaRaw, "opera") !== false)
				$this->userAgent = "opera";
			elseif (strpos($uaRaw, "msie") !== false) {
				$this->userAgent = "ie";
				if (strpos($uaRaw, "msie 6") !== false) $this->uaVersion = 6;
			}
			else
				$this->userAgent = "other";
			
			if (strpos($uaRaw, "mac") !== false)
				$this->uaPlatform = "mac";
			else
				$this->uaPlatform = "other";
		}
		else {
			$this->uaVersion = "unk";
			$this->userAgent = "unk";
			$this->uaPlatform = "unk";
		}
		
/*		session_start();
		
		if ($new_session = !session_is_registered("stored_calendar")) {
			// Verify that there's room for a new session.
			if ($this->limit_caching && (dirSize($this->cache_directory) >= $this->cache_size * 1048576)) {
				$this->error = "<p>Sorry, there are too many people using the iWebCal service right now. " .
					"Please try back in a few minutes. We apologize for the inconvenience.</p>";

				if (isset($GLOBALS["iWebCal_ADMINISTRATOR_EMAIL"]) && $GLOBALS["iWebCal_ADMINISTRATOR_EMAIL"]) {
					$lockfile = $GLOBALS["iWebCal_CACHE_DIRECTORY"] . "/cache-warning-sent-" . date("Y-m-d");
					if (!file_exists($lockfile)) {
						if (touch($lockfile)) {
							$msg = "iWebCal has exceeded the maximum storage allotted to it on your Web server. "
								. "You can increase the amount by changing the value of \$iWebCal_CACHE_SIZE in config.inc.\n\n"
								. 'Each time a new user views a calendar in iWebCal, iWebCal temporarily stores information '
								. 'about that calendar on your Web server in the directory specified by $iWebCal_CACHE_DIRECTORY '
								. 'in config.inc. This information helps iWebCal display the calendar quickly and without using too '
								. "much bandwidth.\n\n"
								. 'However, your Web server has only so much space, and if your Web site is hosted on a shared Web '
								. 'server with other users you probably have a storage quota. So, iWebCal allows you to limit the '
								. 'amount of space used by it. This protects you from exceeding your available space and your '
								. "Web server from suffering an attack through iWebCal.\n\n"
								. 'If you can spare more space, increase $iWebCal_CACHE_SIZE to allow more simultaneous users. '
								. 'You can also decrease $iWebCal_SESSION_LENGTH so that this temporary information expires sooner, '
								. "allowing new connections to replace old ones more quickly.\n\n"
								. "You will only receive this message once per day.\n\n--iWebCal\n";
							if (!mail($GLOBALS["iWebCal_ADMINISTRATOR_EMAIL"], "iWebCal has exceeded its storage space", $msg)) {
								$this->error .= "<p>In addition, iWebCal was unable to send mail to the site administrator "
									. "regarding the problem. You may wish to contact the administrator directly.</p>";
							}
						}
						else {
							$this->error .= "<p>In addition, iWebCal encountered a problem sending mail to the administrator of this site "
								. "about the problem. You may wish to contact the administrator directly</p>";
						}
					}
				}
	
				return;
			}
			session_register("stored_calendar");
		} 
		
		$new_session = !session_is_registered("stored_calendar");
		
		if ($new_session || (isset($_REQUEST["act"]) && ($_REQUEST["act"] == "refresh"))) {
			$this->cal = new Calendar($this->file, $config); 
		}
		else {
			$this->cal = unserialize($_SESSION["stored_calendar"]);
			$this->cal->wake();
		} */
		
		$this->cal = new Calendar($this->file, $config); 
		$cal = &$this->cal;

		if ($cal->url != $this->file) {
			$cal = new Calendar($this->file, $config);
		}

		$cal->sortTasks($this->sort);
		
		$cal->showCompletedTasks($this->showCompleted);
	}
	
	function title() {
		return $this->cal->title;
	}
	
	function includes() {
		$iWebCal_URL_PATH = $this->config['iWebCal_URL_PATH'];

		?>
		<link href="<?php echo $iWebCal_URL_PATH ?>/include/iWebCal.css" rel="stylesheet" />
		<?php 		if ($this->userAgent == "ie") {
			?>
			<link href="<?php echo $iWebCal_URL_PATH ?>/include/iWebCal-ie6.css" rel="stylesheet" />
			<?php 		}
		?>
		<script type="text/javascript" src="<?php echo $iWebCal_URL_PATH ?>/include/iWebCal.js"></script>
		<?php
	}
	
	function display() {
		global $mainframe;
		
		$params =& $mainframe->getPageParameters();
		$format = $params->get('calendardateformat');
		
		$iWebCal_URL_PATH = $this->config['iWebCal_URL_PATH'];
		if ($this->error) {
			?>
			<div class="iWebCal Err">
				<div class="Error">
					<h2>iWebCal Error</h2>
					<p><?php echo $this->error ?></p>
				</div>
			</div>
			<?php
		}
		else {
			$cal = &$this->cal;
		
			$this->todayURL = $this->main_filename . "&iwebcalview=" . (($this->view == "tasks") ? "day" : $this->view) . "&year=";
			$this->todayURL .= $this->today["year"] . "&month=";
			$this->todayURL .= $this->today["mon"] . "&day=";
			$this->todayURL .= $this->today["mday"];
			
			$year = &$this->year;
			$month = &$this->month;
			$day = &$this->day;
			$file = &$this->file;
			$view = &$this->view;
			$sort = &$this->sort;
			
			// Generate URLs for next/prev buttons
			switch($this->view) {
				case "month":
					$nextMonth = ($month == 12) ? 1 : $month+1;
					$prevMonth = ($month == 1) ? 12 : $month-1;
					$nextYear = ($month == 12) ? $year+1 : $year;
					$prevYear = ($month == 1) ? $year-1 : $year;
					$prevURL = $this->main_filename . "&iwebcalview=month&year=${prevYear}&month=${prevMonth}";
					$nextURL = $this->main_filename . "&iwebcalview=month&year=${nextYear}&month=${nextMonth}";
					break;
				case "week":
					list($nextYear, $nextMonth, $nextDay) = explode(",", date("Y,n,j", strtotime("+7 days", strtotime("${year}-${month}-${day}"))));
					list($prevYear, $prevMonth, $prevDay) = explode(",", date("Y,n,j", strtotime("-7 days", strtotime("${year}-${month}-${day}"))));
					
					$prevURL = $this->main_filename . "&iwebcalview=week&year=${prevYear}&month=${prevMonth}&day=${prevDay}";
					$nextURL = $this->main_filename . "&iwebcalview=week&year=${nextYear}&month=${nextMonth}&day=${nextDay}";
					
					break;
				case "day":
					list($nextYear, $nextMonth, $nextDay) = explode(",", date("Y,n,j", strtotime("+1 day", strtotime("${year}-${month}-${day}"))));
					list($prevYear, $prevMonth, $prevDay) = explode(",", date("Y,n,j", strtotime("-1 day", strtotime("${year}-${month}-${day}"))));
					
					$prevURL = $this->main_filename . "&iwebcalview=day&year=${prevYear}&month=${prevMonth}&day=${prevDay}";
					$nextURL = $this->main_filename . "&iwebcalview=day&year=${nextYear}&month=${nextMonth}&day=${nextDay}";
					
					break;
			}
			if ($cal->isValid) {
				?>
				<div class="iWebCal">
					<?php $linkToHere = "http://interfacethis.com/iwebcal/iwebcal.php"; ?>
					<div id="calToolbar">
						<?php 						if ($view == "tasks") {
							?>
							<form method="get" name="taskControlForm" action="." class="Item">
								<label>
									<input type="checkbox" name="showCompleted" value="1" onclick="toggleShowCompleted()" <?php if ($this->showCompleted) { ?>checked<?php } ?> />
									Show completed tasks
								</label>
							</form>
							<?php 						}
						else { 
							?>
							<div id="calPager" class="Item">
								<a class="Item" href="<?php echo JRoute::_($prevURL) ?>" title="<?php echo "previous ${view}"; ?>"
									onmouseover="imageSwap('prevBtn_img', '<?php echo $iWebCal_URL_PATH ?>/img/btn-prev-over.gif')"
									onmouseout="imageSwap('prevBtn_img', '<?php echo $iWebCal_URL_PATH ?>/img/btn-prev.gif')"><?php $this->image("btn-prev.gif", "previous ${view}", "prevBtn_img"); ?></a>
								<span class="ViewTitle Item">
									<?php $cal->printViewTitle($year, $month, $day, $view); ?>
								</span>
								<a class="Item" href="<?php echo JRoute::_($nextURL) ?>" title="<?php echo "next ${view}" ?>"
									onmouseover="imageSwap('nextBtn_img', '<?php echo $iWebCal_URL_PATH ?>/img/btn-next-over.gif')"
									onmouseout="imageSwap('nextBtn_img', '<?php echo $iWebCal_URL_PATH ?>/img/btn-next.gif')"><?php $this->image("btn-next.gif", "next ${view}", "nextBtn_img"); ?></a>
							</div>
							<?php 						}
						$this->button("Today", $this->todayURL, "Item");
						switch($month) {
							case "1":
								$monthName = "Jan";
								break;
							case "2":
								$monthName = "Feb";
								break;
							case "3":
								$monthName = "Mar";
								break;
							case "4":
								$monthName = "Apr";
								break;
							case "5":
								$monthName = "May";
								break;
							case "6":
								$monthName = "Jun";
								break;
							case "7":
								$monthName = "Jul";
								break;
							case "8":
								$monthName = "Aug";
								break;
							case "9":
								$monthName = "Sep";
								break;
							case "10":
								$monthName = "Oct";
								break;
							case "11":
								$monthName = "Nov";
								break;
							case "12":
								$monthName = "Dec";
								break;
						}
						
						switch ($format) {
							case 'day':
								$inputVal = "{$day} {$monthName} {$year}";
								break;
							
							case 'year':
								$inputVal = "{$year} {$monthName} {$day}";
								break;
							
							case 'month':
							default:
								$inputVal = "{$monthName} {$day}, {$year}";
								break;
						}
						?>
						<?php global $option, $Itemid; ?>
						<form action="<?php echo $this->main_filename ?>" method="get" name="controlForm" id="controlForm" class="Item">
							<input class="Item" type="text" name="date" value="<?php echo $inputVal; ?>" size="13" maxlength="20" title="jump to date: most standard formats accepted" /> 
							<input type="hidden" name="option" value="<?php echo $option; ?>" />
							<input type="hidden" name="Itemid" value="<?php echo $Itemid; ?>" />
							<input type="hidden" name="view" value="icalendar" />
							<input type="hidden" name="iwebcalview" value="<?php echo $view; ?>" />
							<input type="hidden" name="act" value="" />
							<input type="hidden" name="showCompleted" value="<?php echo (isset($_GET["showCompleted"]) ? $_GET["showCompleted"] : "1") ?>" />
							<input type="hidden" name="sort" value="<?php echo $sort ?>" />
							<?php $this->button("Go", "javascript:document.controlForm.submit();", "Item"); ?>
						</form>
						<div id="viewSelector" class="Item">
							<a class="Ft End<?php if ($view == "day") echo " Sel" ?>" href="javascript:document.controlForm.iwebcalview.value='day';document.controlForm.submit();">
								<span class="Lt"></span>
								<span class="Ctr">Day</span>
							</a>
							<a <?php if ($view == "week") echo "class=\"Sel\""; ?> href="javascript:document.controlForm.iwebcalview.value='week';document.controlForm.submit();">Week</a>
							<a <?php if ($view == "month") echo "class=\"Sel\""; ?> href="javascript:document.controlForm.iwebcalview.value='month';document.controlForm.submit();">Month</a>
							<a class="Lst End<?php if ($view == "tasks") echo " Sel" ?>" href="javascript:document.controlForm.iwebcalview.value='tasks';document.controlForm.submit();">
								<span class="Ctr">Tasks</span>
								<span class="Rt"></span>
							</a>
						</div>
					</div>
					<?php
					$cal->printCal($year, $month, $day, $view);
					if (isset($this->config['iWebCal_PAGE_TOOLBAR_ITEMS"]) && count($tbItems = $this->config["iWebCal_PAGE_TOOLBAR_ITEMS'])) {
						echo '<ul id="pageToolbar">';
						for ($i=0;$i<count($tbItems);$i++) {
							echo "<li";
							if ($i == 0) echo ' class="Ft"';
							echo '>';
							echo $tbItems[$i];
							echo "</li>";
						}
						echo '</ul>';
					}
					}
				else {
					include "error.php";
					printErrorPage($cal->errorString);
				}
				// $_SESSION["stored_calendar"] = serialize($cal);
				?>
			</div>
			<?php
		}
	}
	
	function button($label, $url, $class="") {
		?>
		<a href="<?php echo JRoute::_($url) ?>" class="Btn<?php if ($class) echo " " . $class; ?>">
			<span class="Lt"></span>
			<span class="Ctr"><?php echo $label ?></span>
			<span class="Rt"></span>
		</a>
		<?php 	}
	
	function image($name, $alt = "[needs alt tag]", $id="", $attrs="") {
	
		list($width, $height, $d0, $d1) = getimagesize($this->config['iWebCal_LOCAL_PATH'] . "/img/" . $name);
		?><img src="<?php echo $this->config['iWebCal_URL_PATH'] . "/img/" . $name ?>" id="<?php echo $id ?>" width="<?php echo $width ?>" height="<?php echo $height ?>" alt="<?php echo $alt ?>" border="0" <?php echo $attrs ?> /><?php 	}
	
	/*
	function imageButton($src, $url, $id, $tip = "[needs tool tip]", $class="", $overExtension="-over") {
		$attrs = "";
		if ($overExtension) {
			$srcParts = pathinfo($src);
			$overSrc = $srcParts["dirname"] . "/" . substr($srcParts["basename"], -1, strlen($srcParts["extension"])+1)
				. $overExtension . "." . $srcParts["extension"];
			$attrs .= "onmouseover=\"swapImage('${id}_img', '${overSrc}')\" ";
			$attrs .= "onmouseout=\"swapImage('${id}_img', '${src}')\" ";
			$attrs .= "class=\"ImgBtn";
			if ($class)
				$attrs .= " $class";
			$attrs .= "\"";
		}
		echo "<a href=\"${url}\" id=\"${id}\" title=\"${tip}\" $attrs>";
		$this->image($src, $tip, "id=\"${id}_img\""); 
	*/
}

?>