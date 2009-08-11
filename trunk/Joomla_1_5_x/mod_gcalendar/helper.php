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
 * @version $Revision: 2.1.1 $
 */

defined('_JEXEC') or die('Restricted access');

require_once (JPATH_ADMINISTRATOR.DS.'components'.DS.'com_gcalendar'.DS.'libraries'.DS.'rss-calendar'.DS.'defaultcalendar.php');
require_once (JPATH_ADMINISTRATOR.DS.'components'.DS.'com_gcalendar'.DS.'util.php');
require_once (JPATH_ADMINISTRATOR.DS.'components'.DS.'com_gcalendar'.DS.'dbutil.php');

class ModGCalendarHelper {

	function getCalendar($calendarids) {
		$calendar = new ModCalendar($calendarids);
		return $calendar;
	}
}

class ModCalendar extends DefaultCalendar{
	var $params;
	var $moduleTitle;

	function ModCalendar($params){
		$this->params = $params;
		$this->id = '_mod_gcalendar';
	}

	function getGoogleCalendarFeeds($start, $end) {
		GCalendarUtil::ensureSPIsLoaded();
		$condition = '';
		$params = $this->params;
		$calendarids = $params->get('calendarids');
		$results = GCalendarDBUtil::getCalendars($calendarids);
		if(empty($results))
		return array();

		$calendars = array();
		foreach ($results as $result) {
			if(!empty($result->calendar_id)){
				$feed = new SimplePie_GCalendar();
				$feed->set_show_past_events(FALSE);
				$feed->set_sort_ascending(TRUE);
				$feed->set_orderby_by_start_date(TRUE);
				$feed->set_expand_single_events(TRUE);
				$feed->enable_order_by_date(FALSE);
				$feed->enable_cache(FALSE);
				$feed->set_start_date($start);
				$feed->set_end_date($end);
				$feed->set_max_events(100);
				$feed->put('gcid',$result->id);
				$feed->put('gccolor',$result->color);
				$feed->set_cal_language(GCalendarUtil::getFrLanguage());
				$feed->set_timezone(GCalendarUtil::getComponentParameter('timezone'));

				$conf =& JFactory::getConfig();
				if ($params->get('gccache', 0) && $conf->getValue( 'config.caching' )){
					// check if cache directory exists and is writeable
					$cacheDir =  JPATH_BASE.DS.'cache'.DS.'mod_gcalendar';
					JFolder::create($cacheDir, 0755);
					if ( !is_writable( $cacheDir ) ) {
						JError::raiseWarning( 500, "Created cache at ".$cacheDir." is not writable, disabling cache.");
						$cache_exists = false;
					}else{
						$cache_exists = true;
					}

					//check and set caching
					$feed->enable_cache($cache_exists);
					if($cache_exists) {
						$feed->set_cache_location($cacheDir);
						$cache_time = (intval($params->get( 'gccache_time', $conf->getValue( 'config.cachetime' ) * 60 )));
						$feed->set_cache_duration($cache_time);
					}
				}

				$url = SimplePie_GCalendar::create_feed_url($result->calendar_id, $result->magic_cookie);
				$feed->set_feed_url($url);
				$feed->init();
				$feed->handle_content_type();
				$calendars[] = $feed;
			}
		}
		return $calendars;
	}

	function calculateDate() {
		//we always show the actual month
		$today = getdate();
		$month = $today["mon"];
		$year = $today["year"];
		if(JRequest::getVar('modulemonth', null)){
			$month = JRequest::getVar('modulemonth', null);
		}
		if(JRequest::getVar('moduleyear', null)){
			$year = JRequest::getVar('moduleyear', null);
		}
		return mktime(0, 0, 0, $month, 1, $year);
	}

	function printToolBar(){
		$year = (int)$this->year;
		$month = (int)$this->month;
		$day = (int)$this->day;
		$view = 'month';

		$mainFilename = "index.php?option=com_gcalendar&view=module&format=raw&modulename=".$this->moduleTitle;
		$nextMonth = ($month == 12) ? 1 : $month+1;
		$prevMonth = ($month == 1) ? 12 : $month-1;
		$nextYear = ($month == 12) ? $year+1 : $year;
		$prevYear = ($month == 1) ? $year-1 : $year;
		$prevURL = $mainFilename . "&moduleyear=".$prevYear."&modulemonth=".$prevMonth;
		$nextURL = $mainFilename . "&moduleyear=".$nextYear."&modulemonth=".$nextMonth;

		$document =& JFactory::getDocument();
		GCalendarUtil::loadJQuery();
		
		//		$scripCode = "window.addEvent('domready', function(){\n";
		//		$scripCode .= " $('gc_module_next_btn').addEvent('click', function(e) {\n";
		//		$scripCode .= "  e = new Event(e).stop();\n";
		//        $scripCode .= "  $('gc_month_table_mod_gcalendar').remove();\n";
		//        $scripCode .= "  var cont = new Element('div',{'styles': {'text-align': 'center'}});\n";
		//        $scripCode .= "  cont.injectInside(new Element('img',{'src':'".JURI::base()."modules/mod_gcalendar/tmpl/img/ajax-loader.gif'}));\n";
		//        $scripCode .= "  $$('gcalendar_mod_gcalendar').injectInside(cont);\n";
		//		$scripCode .= "  new Ajax('".JRoute::_($prevURL)."', {\n";
		//		$scripCode .= " 	method: 'get',\n";
		//		$scripCode .= " 	update: $$('gcalendar_mod_gcalendar')\n";
		//		$scripCode .= "  }).request();\n";
		//		$scripCode .= " });\n";
		//		$scripCode .= "});\n";


		$scripCode = "function loadCalendar(url){\n";
		$scripCode .= " jQuery(\"#gc_month_table_mod_gcalendar\").after('<div style=\"text-align: center;\"><img src=\"".JURI::base() . "modules/mod_gcalendar/tmpl/img/ajax-loader.gif\" /></div>').remove();\n";
		$scripCode .= "	jQuery(\".gcalendar_mod_gcalendar\").load(url);\n";
		$scripCode .= "};\n";
		$document->addScriptDeclaration($scripCode);

		echo "<div style=\"text-align: center;\"><table style=\" margin: 0 auto;\"><tr>\n";
		echo " <td valign=\"middle\">\n";
		$this->image("btn-prev.gif", JText::_('TOOLBAR_PREVIOUS').$this->getTranslatedViewName(), JRoute::_($prevURL));
		echo "</td>\n";
		echo " <td valign=\"middle\"><span class=\"ViewTitle\">\n";
		echo $this->getViewTitle($year, $month, $day, $this->getWeekStart(), $view);
		echo "</span></td>\n";
		echo " <td valign=\"middle\">\n";
		$this->image("btn-next.gif", JText::_('TOOLBAR_NEXT').$this->getTranslatedViewName(), JRoute::_($nextURL));
		echo "</td></tr></table></div>\n";
	}

	function createLink($year, $month, $day, $calids){
		$calids = $this->getIdString($calids);
		return JRoute::_("index.php?option=com_gcalendar&view=day&year=".$year."&month=".$month."&day=".$day.$calids);
	}

	/**
	 * This is an internal helper method and should not be called from outside of the class
	 * otherwise you know what you do.
	 *
	 */
	function image($name, $alt = "[needs alt tag]", $url) {
		list($width, $height, $d0, $d1) = getimagesize(JPATH_SITE.DS.'components'.DS.'com_gcalendar'.DS.'views'.DS.'gcalendar'.DS.'tmpl'.DS.'img'.DS . $name);
		echo "<img src=\"".JURI::base()."modules/mod_gcalendar/tmpl/img/".$name."\"";
		echo " width=\"". $width."\" height=\"".$height."\" alt=\"".$alt."\" title=\"".$alt."\" border=\"0\" onclick=\"loadCalendar('".$url."');\" style=\"cursor: pointer; cursor: hand; \" onmouseover=\"this.style.cursor = 'hand';\"/>";
	}
}
?>
