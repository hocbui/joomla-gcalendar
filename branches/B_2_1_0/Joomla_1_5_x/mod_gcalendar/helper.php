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
 * @version $Revision: 2.1.4 $
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

	function ModCalendar($params){
		$this->params = $params;
	}

	function getGoogleCalendarFeeds($start, $end) {
		GCalendarUtil::ensureSPIsLoaded();
		$condition = '';
		$params = $this->params;
		$calendarids = $params->get('calendarids');
		$results = GCalendarDBUtil::getCalendars($calendarids);
		if(empty($results))
		return array();

		//we always show the actual month
		$today = getdate();
		$start = mktime(0, 0, 0, $today["mon"], 1, $today["year"]);
		$end = strtotime( "+1 month", $start );

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
				if ($feed->error()){
					JError::raiseWarning( 500, 'Simplepie detected an error for the calendar '.$result->calendar_id.'. Please run the <a href="administrator/components/com_gcalendar/libraries/sp-gcalendar/sp_compatibility_test.php">compatibility utility</a>.<br>The following Simplepie error occurred:<br>'.$feed->error());
				}
				$feed->handle_content_type();
				$calendars[] = $feed;
			}
		}
		return $calendars;
	}

	function calculateDate() {
		return time();
	}

	function printToolBar(){
		echo "<div style=\"text-align:center;\"><b>".$this->getViewTitle($this->year, $this->month, $this->day, $this->getWeekStart(), $this->view)."</b></div>\n";
	}

	function createLink($year, $month, $day, $calids){
		$calids = $this->getIdString($calids);
		return JRoute::_("index.php?option=com_gcalendar&view=day&year=".$year."&month=".$month."&day=".$day.$calids);
	}
}
?>
