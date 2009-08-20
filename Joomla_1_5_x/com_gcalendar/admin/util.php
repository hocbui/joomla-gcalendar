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

/**
 * Util class.
 *
 */
class GCalendarUtil{

	function ensureSPIsLoaded(){
		jimport('simplepie.simplepie');

		if(!class_exists('SimplePie_GCalendar')){
			require_once (JPATH_ADMINISTRATOR.DS.'components'.DS.'com_gcalendar'.DS.'libraries'.DS.'sp-gcalendar'.DS.'simplepie-gcalendar.php');
		}
	}

	function loadJQuery(){
		static $jQueryloaded;
		if($jQueryloaded == null){
			$params   = JComponentHelper::getParams('com_languages');
			if($params->get('loadJQuery', 'yes') == 'yes'){
				$document =& JFactory::getDocument();
				$document->addScript('administrator/components/com_gcalendar/libraries/jquery/jquery-1.3.2.js');
				$document->addScriptDeclaration("jQuery.noConflict();");
			}
			$jQueryloaded = 'loaded';
		}
	}

	function getComponentParameter($key){
		$params   = JComponentHelper::getParams('com_gcalendar');
		return $params->get($key);
	}

	function getFrLanguage(){
		$conf	=& JFactory::getConfig();
		return $conf->getValue('config.language');
		//		$params   = JComponentHelper::getParams('com_languages');
		//		return $params->get('site', 'en-GB');
	}

	function getItemId($cal_id){
		$component	= &JComponentHelper::getComponent('com_gcalendar');
		$menu = &JSite::getMenu();
		$items		= $menu->getItems('componentid', $component->id);

		if (is_array($items)){
			global $mainframe;
			$pathway	= &$mainframe->getPathway();
			foreach($items as $item) {
				$paramsItem	=& $menu->getParams($item->id);
				$calendarids = $paramsItem->get('calendarids');
				$contains_gc_id = FALSE;
				if ($calendarids){
					if( is_array( $calendarids ) ) {
						$contains_gc_id = in_array($cal_id,$calendarids);
					} else {
						$contains_gc_id = $cal_id == $calendarids;
					}
				}
				if($contains_gc_id){
					return $item->id;
				}
			}
		}
		return null;
	}

	function getFadedColor($pCol, $pPercentage = 85) {
		$pPercentage = 100 - $pPercentage;
		$rgbValues = array_map( 'hexDec', GCalendarUtil::str_split( ltrim($pCol, '#'), 2 ) );

		for ($i = 0, $len = count($rgbValues); $i < $len; $i++) {
			$rgbValues[$i] = decHex( floor($rgbValues[$i] + (255 - $rgbValues[$i]) * ($pPercentage / 100) ) );
		}

		return '#'.implode('', $rgbValues);
	}

	/**
	 * The php string split method for beeing php 4 compatible.
	 *
	 */
	function str_split($string,$string_length=1) {
		if(strlen($string)>$string_length || !$string_length) {
			do {
				$c = strlen($string);
				$parts[] = substr($string,0,$string_length);
				$string = substr($string,$string_length);
			} while($string !== false);
		} else {
			$parts = array($string);
		}
		return $parts;
	}

	function createToolTip($simplepieItem) {
		$feed = $simplepieItem->get_feed();
		$tooltip = GCalendarUtil::getComponentParameter('toolTipText');

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