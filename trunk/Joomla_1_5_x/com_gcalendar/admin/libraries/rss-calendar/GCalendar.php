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

require_once (JPATH_ADMINISTRATOR.DS.'components'.DS.'com_gcalendar'.DS.'util.php');
require_once ('classes/EventRenderer.php');
require_once ('classes/CalendarRenderer.php');

class GCalendar {
	var $cal;
	var $today, $month, $year, $day, $view;
	var $mainFilename;
	var $calendarConfig;
	var $feeds;

	function GCalendar($calendarConfig) {
		GCalendarUtil::ensureSPIsLoaded();
		$this->config = $config;
		$this->calendarConfig = $calendarConfig;
		$this->mainFilename = "index.php?option=com_gcalendar&view=gcalendar";
		$this->today = getdate();

		$this->month = isset($_GET["month"]) ? $_GET["month"] : $this->today["mon"];
		$this->year = isset($_GET["year"]) ? $_GET["year"] : $this->today["year"];
		$this->day = isset($_GET["day"]) ? $_GET["day"] : $this->today["mday"];
		if (!checkdate($this->month, $this->day, $this->year)) {
			$this->day = 1;
		}

		$this->view = isset($_GET["gcalendarview"]) ? $_GET["gcalendarview"] : $calendarConfig->getDefaultView();
		if($calendarConfig->getForceView() != null){
			$this->view = $calendarConfig->getForceView();
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
		switch($this->view) {
			case "month":
				$start = mktime(0, 0, 0, $this->month, 1, $this->year);
				$end = strtotime( "+1 month", $start );
				break;
			case "day":
				$start = mktime(0, 0, 0, $this->month, $this->day, $this->year);
				$end = strtotime( "+1 day", $start );
				break;
			case "week":
				$start = CalendarRenderer::getFirstDayOfWeek($this->year, $this->month, $this->day, $calendarConfig->getWeekStart());
				$end = strtotime( "+1 week +1 day", $start );
		}
		$this->feeds = $calendarConfig->getGoogleCalendarFeeds($start, $end);
		$this->cal = new CalendarRenderer($this);
	}

	function getFeeds(){
		return $this->feeds;
	}

	function display() {
		$document =& JFactory::getDocument();
		JHTML::_('behavior.modal');
		$document->addScript('administrator/components/com_gcalendar/libraries/nifty/nifty.js');
		$document->addStyleSheet('administrator/components/com_gcalendar/libraries/nifty/niftyCorners.css');
		$document->addStyleSheet('administrator/components/com_gcalendar/libraries/rss-calendar/gcalendar.css');
		if ($this->userAgent == "ie") {
			$document->addStyleSheet('administrator/components/com_gcalendar/libraries/rss-calendar/gcalendar-ie6.css');
		}

		$cal = $this->cal;

		$year = &$this->year;
		$month = &$this->month;
		$day = &$this->day;
		$view = &$this->view;
		echo "<div class=\"gcalendar\">\n";

		$calendarConfig = $this->calendarConfig;
		if($calendarConfig->getShowSelectionList()){
			$this->printCalendarSelectionList($year, $month, $day, $view);
		}

		if($calendarConfig->getShowToolbar()){
			$this->printToolBar($year, $month, $day, $view);
		}
		$cal->printCal($year, $month, $day, $view);
		echo "</div>\n";
	}

	function printCalendarSelectionList(){
		JHTML::_('behavior.mootools');
		$document = &JFactory::getDocument();
		$document->addScript( 'administrator/components/com_gcalendar/libraries/rss-calendar/gcalendar.js' );
		$calendar_list = "<div id=\"gc_gcalendar_view_list\"><table>\n";
		$feeds = $this->feeds;
		if(!empty($feeds)){
			foreach($feeds as $feed){
				$calendar_list .="<tr>\n";
				$calendar_list .="<td><div class=\"gccal_".$feed->get('gcid')."\"><font color=\"#FFFFFF\">".$feed->get('gcname')."</font></div></td></tr>\n";
			}
		}
		$calendar_list .="</table></div>\n";
		echo $calendar_list;
		echo "<div align=\"center\" style=\"text-align:center\">\n";
		echo "<a id=\"gc_gcalendar_view_toggle\" name=\"gc_gcalendar_view_toggle\" href=\"#\">\n";
		echo "<img id=\"gc_gcalendar_view_toggle_status\" name=\"gc_gcalendar_view_toggle_status\" src=\"".JURI::base()."administrator/components/com_gcalendar/libraries/rss-calendar/img/btn-down.png\"/>\n";
		echo "</a></div>\n";
	}

	function printToolBar($year, $month, $day, $view){
		$cal = $this->cal;
		$calendarConfig = $this->calendarConfig;
		
		switch($view) {
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

			$document =& JFactory::getDocument();
			$calCode  = "function datePickerClosed(dateField){\n";
			$calCode .= "var gcdateValues = dateField.value.split('/');\n";
			$calCode .= "var gcformatValues = '".$calendarConfig->getDateFormat()."'.split('/');\n";
			$calCode .= "var gcday = '';\n";
			$calCode .= "var gcmonth = '';\n";
			$calCode .= "var gcyear = '';\n";
			$calCode .= "for(i = 0; i < gcformatValues.length; i++){\n";
			$calCode .= "if(gcformatValues[i]=='dd')\n";
			$calCode .= "gcday = gcdateValues[i];\n";
			$calCode .= "else if(gcformatValues[i]=='mm')\n";
			$calCode .= "gcmonth = gcdateValues[i];\n";
			$calCode .= "else if(gcformatValues[i]=='yy')\n";
			$calCode .= "gcyear = gcdateValues[i];\n";
			$calCode .= "}\n";
			$calCode .= "document.getElementById('gc_go_link').href = '".JRoute::_($this->mainFilename."&gcalendarview=".$view)."&day='+gcday+'&month='+gcmonth+'&year='+gcyear;\n";
			$calCode .= "}\n";
			$document->addScriptDeclaration($calCode);

			$document->addScript('administrator/components/com_gcalendar/libraries/jquery/jquery-1.3.2.js');
			$document->addScript('administrator/components/com_gcalendar/libraries/jquery/ui/ui.core.js');
			$document->addScript('administrator/components/com_gcalendar/libraries/jquery/ui/ui.datepicker.js');
			$document->addStyleSheet('administrator/components/com_gcalendar/libraries/jquery/themes/redmond/ui.all.css');

			$calCode = "jQuery.noConflict();\n";
			$calCode .= "jQuery(document).ready(function(){\n";
			$calCode .= "document.getElementById('gcdate').value = jQuery.datepicker.formatDate('".$calendarConfig->getDateFormat()."', new Date(".$year.", ".$month." - 1, ".$day."));\n";
			$calCode .= "jQuery(\"#gcdate\").datepicker({dateFormat: '".$calendarConfig->getDateFormat()."'});\n";
			$calCode .= "})\n";
			$document->addScriptDeclaration($calCode);

			echo "<div id=\"calToolbar\">\n";
			echo "<div id=\"calPager\" class=\"Item\">\n";
			echo "<a class=\"Item\" href=\"".JRoute::_($prevURL)."\" title=\"previous ".$view."\">\n";
			$this->image("btn-prev.gif", "previous ".$view, "prevBtn_img");
			echo "</a>\n";
			echo "<span class=\"ViewTitle Item\">\n";
			$cal->printViewTitle($year, $month, $day, $view);
			echo "</span>\n";
			echo "<a class=\"Item\" href=\"".JRoute::_($nextURL)."\" title=\"next ".$view."\">\n";
			$this->image("btn-next.gif", "next ".$view, "nextBtn_img");
			echo "</a></div>\n";
			echo "<a class=\"Item\" href=\"".JRoute::_($this->mainFilename."&gcalendarview=".$view."&year=".$this->today["year"]."&month=".$this->today["mon"]."&day=".$this->today["mday"])."\">\n";
			$this->image("btn-today.gif", "go to today", "", "today_img");
			echo "</a>\n";
			echo "<input class=\"Item\"	type=\"text\" name=\"gcdate\" id=\"gcdate\" \n";
			echo "onchange=\"datePickerClosed(this);\" \n";
			echo "size=\"10\" maxlength=\"10\" title=\"jump to date\" />";
			echo "<a class=\"Item\" id=\"gc_go_link\" href=\"".JRoute::_($this->mainFilename."&gcalendarview=".$view."&year=".$year."&month=".$month."&day=".$day)."\">\n";
			$this->image("btn-go.gif", "go to date", "gi_img");
			echo "</a>\n";

			echo "<div id=\"viewSelector\" class=\"Item\">\n";
			echo "<a href=\"".JRoute::_($this->mainFilename."&gcalendarview=day&year=".$year."&month=".$month."&day=".$day)."\">\n";
			$this->image("cal-day.gif", "day view", "calday_img");
			echo "</a>\n";

			echo "<a href=\"".JRoute::_($this->mainFilename."&gcalendarview=week&year=".$year."&month=".$month."&day=".$day)."\">\n";
			$this->image("cal-week.gif", "week view", "calweek_img");
			echo "</a>\n";

			echo "<a href=\"".JRoute::_($this->mainFilename."&gcalendarview=month&year=".$year."&month=".$month."&day=".$day)."\">\n";
			$this->image("cal-month.gif", "month view", "calmonth_img");
			echo "</a></div></div>\n";
	}

	function image($name, $alt = "[needs alt tag]", $id="", $attrs="") {
		list($width, $height, $d0, $d1) = getimagesize(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_gcalendar'.DS.'libraries'.DS.'rss-calendar'.DS.'img'.DS . $name);
		echo "<img src=\"".JURI::base() . "administrator/components/com_gcalendar/libraries/rss-calendar/img/" . $name."\"";
		echo " id=\"". $id."\" width=\"". $width."\" height=\"".$height."\" alt=\"".$alt."\" border=\"0\"";
		echo $attrs ."/>";
	}
}
?>