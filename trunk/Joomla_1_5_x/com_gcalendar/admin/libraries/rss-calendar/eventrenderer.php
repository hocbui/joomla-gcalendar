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
 * @version $Revision: 2.1.2 $
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

require_once (JPATH_ADMINISTRATOR.DS.'components'.DS.'com_gcalendar'.DS.'util.php');

class EventRenderer {

	function display($displayType, $spItem, $calendar) {
		global $Itemid;
		$feed = $spItem->get_feed();
		$summaryLength = 0;
		switch ($displayType) {
			case "month":
				$summaryLength = 22;
				break;
			case "week":
				$summaryLength = 23;
				break;
			case "day":
				$summaryLength = 0;
				break;
		}
		JHTML::_('behavior.modal');
		$toolTipArray = array(
   			'onShow'=>"function(tip) {tip.effect('opacity', 
      			{duration: 500, wait: false}).start(0,1)}", 
   			'onHide'=>"function(tip) {tip.effect('opacity', 
      			{duration: 500, wait: false}).start(1,0)}");
		JHTML::_('behavior.tooltip', '.gcalendar_daylink', $toolTipArray);

		$document =& JFactory::getDocument();
		$document->addScript(JURI::base().'components/com_gcalendar/hiddenviews/event/tmpl/default.js');
		echo "<a class=\"gcalendar_daylink modal\" href=\"".JRoute::_('index.php?option=com_gcalendar&tmpl=component&view=event&eventID='.$spItem->get_id().'&start='.$spItem->get_start_date().'&end='.$spItem->get_end_date().'&gcid='.$feed->get('gcid')).'&Itemid='.$Itemid."\" ";
		echo " title=\"";
		echo $spItem->get_title().' :: '.EventRenderer::createToolTip($spItem, $calendar);
		echo "\" >";
		echo EventRenderer::trim($spItem->get_title(),$summaryLength);
		echo "</a>\n";
	}

	function trim($sum, $maxlength = 0) {
		if (!$sum) return NULL;
		$sum = stripslashes($sum);
		if (!$sum) return NULL;
		if ($maxlength) {
			if ($maxlength < strlen($sum)) {
				return substr($sum, 0, $maxlength-3) . "...";
			}
			return substr($sum, 0, $maxlength);
		}
		return $sum;
	}
	
	function createToolTip($simplepieItem, $calendar) {
		$feed = $simplepieItem->get_feed();
		$tooltip = $calendar->toolTipText;

		$tz = GCalendarUtil::getComponentParameter('timezone');
		if($tz == '') $tz = $feed->get_timezone();
		$itemID = GCalendarUtil::getItemId($feed->get('gcid'));
		if(!empty($itemID))$itemID = '&Itemid='.$itemID;

		// These are the dates we'll display
		$dateformat = '%d.%m.%Y';
		$timeformat = '%H:%M';

		$startDate = strftime($dateformat, $simplepieItem->get_start_date());
		$startTime = strftime($timeformat, $simplepieItem->get_start_date());
		$endDate = strftime($dateformat, $simplepieItem->get_end_date());
		$endTime = strftime($timeformat, $simplepieItem->get_end_date());

		switch($simplepieItem->get_day_type()){
			case $simplepieItem->SINGLE_WHOLE_DAY:
				$tooltip=str_replace("{startdate}",$startDate,$tooltip);
				$tooltip=str_replace("{starttime}","",$tooltip);
				$tooltip=str_replace("{dateseparator}","",$tooltip);
				$tooltip=str_replace("{enddate}","",$tooltip);
				$tooltip=str_replace("{endtime}","",$tooltip);
				break;
			case $simplepieItem->SINGLE_PART_DAY:
				$tooltip=str_replace("{startdate}",$startDate,$tooltip);
				$tooltip=str_replace("{starttime}",$startTime,$tooltip);
				$tooltip=str_replace("{dateseparator}","-",$tooltip);
				$tooltip=str_replace("{enddate}","",$tooltip);
				$tooltip=str_replace("{endtime}",$endTime,$tooltip);
				break;
			case $simplepieItem->MULTIPLE_WHOLE_DAY:
				$SECSINDAY=86400;
				$endDate = strftime($dateformat, $simplepieItem->get_end_date() - $SECSINDAY);
				$tooltip=str_replace("{startdate}",$startDate,$tooltip);
				$tooltip=str_replace("{starttime}","",$tooltip);
				$tooltip=str_replace("{dateseparator}","-",$tooltip);
				$tooltip=str_replace("{enddate}",$endDate,$tooltip);
				$tooltip=str_replace("{endtime}","",$tooltip);
				break;
			case $simplepieItem->MULTIPLE_PART_DAY:
				$tooltip=str_replace("{startdate}",$startDate,$tooltip);
				$tooltip=str_replace("{starttime}",$startTime,$tooltip);
				$tooltip=str_replace("{dateseparator}","-",$tooltip);
				$tooltip=str_replace("{enddate}",$endDate,$tooltip);
				$tooltip=str_replace("{endtime}",$endTime,$tooltip);
				break;
		}

		$tooltip=str_replace("{title}",$simplepieItem->get_title(),$tooltip);
		$tooltip=str_replace("{description}",$simplepieItem->get_description(),$tooltip);
		$tooltip=str_replace("{where}",$simplepieItem->get_location(),$tooltip);
		$tooltip=str_replace("{backlink}",JRoute::_('index.php?option=com_gcalendar&view=event&eventID='.$simplepieItem->get_id().'&gcid='.$feed->get('gcid').$itemID),$tooltip);
		$tooltip=str_replace("{link}",$simplepieItem->get_link().'&ctz='.$tz,$tooltip);
		$tooltip=str_replace("{maplink}","http://maps.google.com/?q=".urlencode($simplepieItem->get_location()),$tooltip);
		$tooltip=str_replace("{calendarname}",$feed->get('gcname'),$tooltip);
		$tooltip=str_replace("{calendarcolor}",$feed->get('gccolor'),$tooltip);
		// Accept and translate HTML
		$tooltip=str_replace("&lt;","<",$tooltip);
		$tooltip=str_replace("&gt;",">",$tooltip);
		$tooltip=str_replace("&quot;","\"",$tooltip);

		return $tooltip;
	}
}
?>