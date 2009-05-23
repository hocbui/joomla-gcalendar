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

class DefaultCalendarConfig{
	var $feedFetcher;
	var $defaultView = 'month';
	var $forceView = null;
	var $weekStart = '1';
	var $showSelectionList = true;
	var $showToolbar = true;
	var $dateFormat = 'dd/mm/yy';
	var $showEventTitle = true;
	var $shortDayNames = false;
	var $cellHeight = 90;
	var $printDayLink = true;
	var $feeds = null;

	function DefaultCalendarConfig($feedFetcher){
		$this->feedFetcher = $feedFetcher;
	}

	function getGoogleCalendarFeeds($start, $end){
		if($this->feeds == null){
			$feedFetcher = $this->feedFetcher;
			$this->feeds = $feedFetcher->getGoogleCalendarFeeds($start, $end);
		}
		return $this->feeds;
	}

	function getDefaultView(){
		return $this->defaultView;
	}

	function getForceView(){
		return $this->forceView;
	}

	function getWeekStart(){
		return $this->weekStart;
	}

	function getShowSelectionList(){
		return $this->showSelectionList;
	}

	function getShowToolbar(){
		return $this->showToolbar;
	}

	function getDateFormat(){
		return $this->dateFormat;
	}

	function getShowEventTitle(){
		return $this->showEventTitle;
	}

	function getShortDayNames(){
		return $this->shortDayNames;
	}

	function getCellHeight() {
		return $this->cellHeight;
	}

	function getPrintDayLink() {
		return $this->printDayLink;
	}
	
	function createLink($year, $month, $day, $calids){
		$calendars = '';
		if(!empty($calids)) $calendars = '&gcids='.implode(',',$calids);
		return JRoute::_("index.php?option=com_gcalendar&view=gcalendar&gcalendarview=day&year=".$year."&month=".$month."&day=".$day.$calendars);
	}
}
?>