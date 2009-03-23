<?php
defined( '_JEXEC' ) or die( 'Restricted access' );
class GCalendar2 {
	var $config = array();
	var $cal;
	var $today, $month, $year, $day, $view, $sort;
	var $model;

	function GCalendar2(&$model, $config) {
		$this->config = $config;
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
		//2005-08-09T10:57:00-08:00 alway catch the full month
		$start = mktime(0, 0, 0, $this->month, 1, $this->year);
		$end    = strtotime( "+1 months", $start );
		$month_before              = (int) date( "m", $start ) + 12 * (int) date( "Y", $start );
		$month_after               = (int) date( "m", $end ) + 12 * (int) date( "Y", $end );
		if ($month_after > $months + $month_before)
		$end = strtotime( date("Ym01His", $end) . " -1 day" );
		$this->feeds = &$model->getGoogleCalendarEvents($start, $end);
		$this->cal = new Calendar(&$this);
	}

	function getFeeds(){
		return $this->feeds;
	}

	function title() {
		return $this->cal->title;
	}

	function includes() {
		$iWebCal_URL_PATH = $this->config['iWebCal_URL_PATH'];
		$document =& JFactory::getDocument();
		$document->addScript($iWebCal_URL_PATH.'/include/iWebCal.js');
		$document->addStyleSheet($iWebCal_URL_PATH.'/include/iWebCal.css');
		if ($this->userAgent == "ie") {
			$document->addStyleSheet($iWebCal_URL_PATH.'/include/iWebCal-ie6.css');
		}
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
			?>
<div class="iWebCal"><?php $linkToHere = "http://interfacethis.com/iwebcal/iwebcal.php"; ?>
<div id="calToolbar">
<div id="calPager" class="Item"><a class="Item"
	href="<?php echo JRoute::_($prevURL) ?>"
	title="<?php echo "previous ${view}"; ?>"
	onmouseover="imageSwap('prevBtn_img', '<?php echo $iWebCal_URL_PATH ?>/img/btn-prev-over.gif')"
	onmouseout="imageSwap('prevBtn_img', '<?php echo $iWebCal_URL_PATH ?>/img/btn-prev.gif')"><?php $this->image("btn-prev.gif", "previous ${view}", "prevBtn_img"); ?></a>
<span class="ViewTitle Item"> <?php $cal->printViewTitle($year, $month, $day, $view); ?>
</span> <a class="Item" href="<?php echo JRoute::_($nextURL) ?>"
	title="<?php echo "next ${view}" ?>"
	onmouseover="imageSwap('nextBtn_img', '<?php echo $iWebCal_URL_PATH ?>/img/btn-next-over.gif')"
	onmouseout="imageSwap('nextBtn_img', '<?php echo $iWebCal_URL_PATH ?>/img/btn-next.gif')"><?php $this->image("btn-next.gif", "next ${view}", "nextBtn_img"); ?></a>
</div>
			<?php
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
			?> <?php global $option, $Itemid; ?>
<form action="<?php echo $this->main_filename ?>" method="get"
	name="controlForm" id="controlForm" class="Item"><input class="Item"
	type="text" name="date" value="<?php echo $inputVal; ?>" size="13"
	maxlength="20" title="jump to date: most standard formats accepted" />
<input type="hidden" name="option" value="<?php echo $option; ?>" /> <input
	type="hidden" name="Itemid" value="<?php echo $Itemid; ?>" /> <input
	type="hidden" name="view" value="icalendar" /> <input type="hidden"
	name="iwebcalview" value="<?php echo $view; ?>" /> <input type="hidden"
	name="act" value="" /> <input type="hidden" name="showCompleted"
	value="<?php echo (isset($_GET["showCompleted"]) ? $_GET["showCompleted"] : "1") ?>" />
<input type="hidden" name="sort" value="<?php echo $sort ?>" /> <?php $this->button("Go", "javascript:document.controlForm.submit();", "Item"); ?>
</form>
<div id="viewSelector" class="Item"><a
	class="Ft End<?php if ($view == "day") echo " Sel" ?>"
	href="javascript:document.controlForm.iwebcalview.value='day';document.controlForm.submit();">
<span class="Lt"></span> <span class="Ctr">Day</span> </a> <a
<?php if ($view == "week") echo "class=\"Sel\""; ?>
	href="javascript:document.controlForm.iwebcalview.value='week';document.controlForm.submit();">Week</a>
<a class="Lst End<?php if ($view == "month") echo " Sel"; ?>"
	href="javascript:document.controlForm.iwebcalview.value='month';document.controlForm.submit();">
<span class="Ctr">Month</span> <span class="Rt"></span></a> <!-- a class="Lst End<?php if ($view == "tasks") echo " Sel" ?>" href="javascript:document.controlForm.iwebcalview.value='tasks';document.controlForm.submit();">
								<span class="Ctr">Tasks</span>
								<span class="Rt"></span>
							</a --></div>
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
// $_SESSION["stored_calendar"] = serialize($cal);
?></div>
<?php
		}
	}

	function button($label, $url, $class="") {
		echo '<a href="'. JRoute::_($url).'" class="Btn'.($class).' ' . $class.'"> <span class="Lt"></span>';
		echo '<span class="Ctr">'.$label .'</span> <span class="Rt"></span> </a>';
	}

	function image($name, $alt = "[needs alt tag]", $id="", $attrs="") {
		list($width, $height, $d0, $d1) = getimagesize($this->config['iWebCal_LOCAL_PATH'] . "/img/" . $name);
		echo '<img src="'.$this->config['iWebCal_URL_PATH'] . '/img/' . $name.'"';
		echo 'id="'. $id.'" width="'. $width.'"height="'.$height.'" alt="'.$alt.'" border="0"';
		echo $attrs .'/>';
	}
}
?>