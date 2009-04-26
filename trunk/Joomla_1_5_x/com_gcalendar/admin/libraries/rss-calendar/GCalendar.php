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

defined( '_JEXEC' ) or die( 'Restricted access' );


if(!class_exists('SimplePie')){
	require_once (JPATH_SITE.DS.'libraries'.DS.'simplepie'.DS.'simplepie.php');
}

if(!class_exists('SimplePie_GCalendar')){
	require_once (JPATH_ADMINISTRATOR.DS.'components'.DS.'com_gcalendar'.DS.'libraries'.DS.'sp-gcalendar'.DS.'simplepie-gcalendar.php');
}


require_once ('classes/EventRenderer.php');
require_once ('classes/CalendarRenderer.php');

class GCalendar {
	var $cal;
	var $today, $month, $year, $day, $view;
	var $mainFilename;
	var $config;

	function GCalendar(&$model, $config) {
		$this->config = $config;
		$this->mainFilename = "index.php?option=com_gcalendar&view=gcalendar";
		$this->today = getdate();
		if (isset($_GET["date"])) {
			$dateInfo = explode("/", $_GET["date"]);
			$this->year = $dateInfo[2];
			$this->month = $dateInfo[1];
			$this->day = $dateInfo[0];
		}
		else {
			$this->month = isset($_GET["month"]) ? $_GET["month"] : $this->today["mon"];
			$this->year = isset($_GET["year"]) ? $_GET["year"] : $this->today["year"];
			$this->day = isset($_GET["day"]) ? $_GET["day"] : $this->today["mday"];
			if (!checkdate($this->month, $this->day, $this->year)) {
				$this->day = 1;
			}
		}
		$this->view = isset($_GET["gcalendarview"]) ? $_GET["gcalendarview"] : $config['defaultView'];
		if(isset($config['forceView'])){
			$this->view = $config['forceView'];
		}

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
		$this->cal = new CalendarRenderer(&$this);
	}

	function getFeeds(){
		return $this->feeds;
	}

	function display() {

		$document =& JFactory::getDocument();
		JHTML::_('behavior.modal');
		$document->addScript('administrator/components/com_gcalendar/libraries/nifty/nifty.js');
		$document->addStyleSheet('administrator/components/com_gcalendar/libraries/nifty/niftyCorners.css');
		$document->addScript('administrator/components/com_gcalendar/libraries/datepicker/datepicker.js');
		$document->addStyleSheet('administrator/components/com_gcalendar/libraries/datepicker/style.css');
		$document->addStyleSheet('administrator/components/com_gcalendar/libraries/rss-calendar/gcalendar.css');
		if ($this->userAgent == "ie") {
			$document->addStyleSheet('administrator/components/com_gcalendar/libraries/rss-calendar/gcalendar-ie6.css');
		}

		$urlPath = JURI::base() . 'administrator/components/com_gcalendar/libraries/rss-calendar';
		$cal = &$this->cal;

		$this->todayURL = $this->mainFilename . "&gcalendarview=" .  $this->view . "&year=";
		$this->todayURL .= $this->today["year"] . "&month=";
		$this->todayURL .= $this->today["mon"] . "&day=";
		$this->todayURL .= $this->today["mday"];

		$year = &$this->year;
		$month = &$this->month;
		$day = &$this->day;
		$view = &$this->view;

		// Generate URLs for next/prev buttons
		switch($this->view) {
			case "month":
				$nextMonth = ($month == 12) ? 1 : $month+1;
				$prevMonth = ($month == 1) ? 12 : $month-1;
				$nextYear = ($month == 12) ? $year+1 : $year;
				$prevYear = ($month == 1) ? $year-1 : $year;
				$prevURL = $this->mainFilename . "&gcalendarview=month&year=${prevYear}&month=${prevMonth}";
				$nextURL = $this->mainFilename . "&gcalendarview=month&year=${nextYear}&month=${nextMonth}";
				break;
			case "week":
				list($nextYear, $nextMonth, $nextDay) = explode(",", date("Y,n,j", strtotime("+7 days", strtotime("${year}-${month}-${day}"))));
				list($prevYear, $prevMonth, $prevDay) = explode(",", date("Y,n,j", strtotime("-7 days", strtotime("${year}-${month}-${day}"))));

				$prevURL = $this->mainFilename . "&gcalendarview=week&year=${prevYear}&month=${prevMonth}&day=${prevDay}";
				$nextURL = $this->mainFilename . "&gcalendarview=week&year=${nextYear}&month=${nextMonth}&day=${nextDay}";

				break;
			case "day":
				list($nextYear, $nextMonth, $nextDay) = explode(",", date("Y,n,j", strtotime("+1 day", strtotime("${year}-${month}-${day}"))));
				list($prevYear, $prevMonth, $prevDay) = explode(",", date("Y,n,j", strtotime("-1 day", strtotime("${year}-${month}-${day}"))));

				$prevURL = $this->mainFilename . "&gcalendarview=day&year=${prevYear}&month=${prevMonth}&day=${prevDay}";
				$nextURL = $this->mainFilename . "&gcalendarview=day&year=${nextYear}&month=${nextMonth}&day=${nextDay}";

				break;
			}
			?>
<div class="gcalendar"><?php
if($this->config['showToolbar'] == 'yes'){
	?>
<div id="calToolbar">
<div id="calPager" class="Item"><a class="Item"
	href="<?php echo JRoute::_($prevURL) ?>"
	title="<?php echo "previous ${view}"; ?>"> <?php $this->image("btn-prev.gif", "previous ${view}", "prevBtn_img"); ?></a>
<span class="ViewTitle Item"> <?php $cal->printViewTitle($year, $month, $day, $view); ?>
</span> <a class="Item" href="<?php echo JRoute::_($nextURL) ?>"
	title="<?php echo "next ${view}" ?>"> <?php $this->image("btn-next.gif", "next ${view}", "nextBtn_img"); ?></a>
</div>
<form action="<?php echo $this->mainFilename ?>" method="get"
	name="controlForm" id="controlForm" class="Item"><a class="Item"
	href="javascript:document.controlForm.date.value='<?php echo $this->today["mday"].'/'.$this->today["mon"].'/'.$this->today["year"]; ?>';document.controlForm.submit();">
	<?php $this->image("btn-today.gif", "go to today", "", "today_img"); ?></a>
<input class="Item" type="text" name="date"
	onclick="displayDatePicker('date', false, 'dmy', '/');"
	value="<?php echo date('d/m/Y',mktime(0,0,0,$month,$day,$year)); ?>"
	size="10" maxlength="10"
	title="jump to date use the format day/month/year" /> <input
	type="hidden" name="gcalendarview" value="<?php echo $view; ?>" /> <a
	class="Item" href="javascript:document.controlForm.submit();"> <?php $this->image("btn-go.gif", "go to date", "gi_img"); ?></a>
</form>
<div id="viewSelector" class="Item"><a
	href="javascript:document.controlForm.gcalendarview.value='day';document.controlForm.submit();">
	<?php $this->image("cal-day.gif", "day view", "calday_img"); ?></a> <a
	href="javascript:document.controlForm.gcalendarview.value='week';document.controlForm.submit();">
	<?php $this->image("cal-week.gif", "week view", "calweek_img"); ?></a>
<a
	href="javascript:document.controlForm.gcalendarview.value='month';document.controlForm.submit();">
	<?php $this->image("cal-month.gif", "month view", "calmonth_img"); ?></a>
</div>
</div>
	<?php
			}
			$cal->printCal($year, $month, $day, $view);
			?></div>
			<?php
	}
	function image($name, $alt = "[needs alt tag]", $id="", $attrs="") {
		list($width, $height, $d0, $d1) = getimagesize(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_gcalendar'.DS.'libraries'.DS.'rss-calendar'.DS.'img'.DS . $name);
		echo "<img src=\"".JURI::base() . "administrator/components/com_gcalendar/libraries/rss-calendar/img/" . $name."\"";
		echo "id=\"". $id."\" width=\"". $width."\" height=\"".$height."\" alt=\"".$alt."\" border=\"0\"";
		echo $attrs ."/>";
	}
}
?>