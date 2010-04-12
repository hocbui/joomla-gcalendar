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
 * @version $Revision: 2.2.0 $
 */

require_once (JPATH_ADMINISTRATOR.DS.'components'.DS.'com_gcalendar'.DS.'dbutil.php');

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
			$param   = GCalendarUtil::getComponentParameter('loadJQuery');
			if($param == 'yes' || empty($param)){
				$document =& JFactory::getDocument();
				$document->addScript(JURI::base().'administrator/components/com_gcalendar/libraries/jquery/jquery-1.3.2.min.js');
				$document->addScriptDeclaration("jQuery.noConflict();");
			}
			$jQueryloaded = 'loaded';
		}
	}

	function getComponentParameter($key, $defaultValue = null){
		$params   = JComponentHelper::getParams('com_gcalendar');
		return $params->get($key, $defaultValue);
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
			foreach($items as $item) {
				$paramsItem	=& $menu->getParams($item->id);
				$calendarids = $paramsItem->get('calendarids');
				if(empty($calendarids)){
					$results = GCalendarDBUtil::getAllCalendars();
					if($results){
						$calendarids = array();
						foreach ($results as $result) {
							$calendarids[] = $result->id;
						}
					}
				}
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

	function renderEvent($event, $format, $dateformat, $timeformat){
		$feed = $event->get_feed();
		$tz = GCalendarUtil::getComponentParameter('timezone');
		if($tz == ''){
			$tz = $feed->get_timezone();
		}

		$itemID = GCalendarUtil::getItemId($feed->get('gcid'));
		if(!empty($itemID)){
			$itemID = '&Itemid='.$itemID;
		}else{
			$menu=JSite::getMenu();
			$activemenu=$menu->getActive();
			if($activemenu != null)
			$itemID = '&Itemid='.$activemenu->id;
		}

		// These are the dates we'll display
		$startDate = strftime($dateformat, $event->get_start_date());
		$startTime = strftime($timeformat, $event->get_start_date());
		$endDate = strftime($dateformat, $event->get_end_date());
		$endTime = strftime($timeformat, $event->get_end_date());

		$temp_event = $format;

		switch($event->get_day_type()){
			case $event->SINGLE_WHOLE_DAY:
				$temp_event=str_replace("{startdate}",$startDate,$temp_event);
				$temp_event=str_replace("{starttime}","",$temp_event);
				$temp_event=str_replace("{dateseparator}","",$temp_event);
				$temp_event=str_replace("{enddate}","",$temp_event);
				$temp_event=str_replace("{endtime}","",$temp_event);
				break;
			case $event->SINGLE_PART_DAY:
				$temp_event=str_replace("{startdate}",$startDate,$temp_event);
				$temp_event=str_replace("{starttime}",$startTime,$temp_event);
				$temp_event=str_replace("{dateseparator}","-",$temp_event);
				$temp_event=str_replace("{enddate}","",$temp_event);
				$temp_event=str_replace("{endtime}",$endTime,$temp_event);
				break;
			case $event->MULTIPLE_WHOLE_DAY:
				$SECSINDAY=86400;
				$endDate = strftime($timeformat, $event->get_end_date()-$SECSINDAY);
				$temp_event=str_replace("{startdate}",$startDate,$temp_event);
				$temp_event=str_replace("{starttime}","",$temp_event);
				$temp_event=str_replace("{dateseparator}","-",$temp_event);
				$temp_event=str_replace("{enddate}",$endDate,$temp_event);
				$temp_event=str_replace("{endtime}","",$temp_event);
				break;
			case $event->MULTIPLE_PART_DAY:
				$temp_event=str_replace("{startdate}",$startDate,$temp_event);
				$temp_event=str_replace("{starttime}",$startTime,$temp_event);
				$temp_event=str_replace("{dateseparator}","-",$temp_event);
				$temp_event=str_replace("{enddate}",$endDate,$temp_event);
				$temp_event=str_replace("{endtime}",$endTime,$temp_event);
				break;
		}

		if (substr_count($temp_event, '"{description}"')){
			// If description is in html attribute
			$desc = htmlspecialchars(str_replace('"',"'",$event->get_description()));
		}else{
			//Make any URLs used in the description also clickable
			$desc = preg_replace('(((f|ht){1}tp://)[-a-zA-Z0-9@:%_\+.~#?,//=&;]+)','<a href="\\1">\\1</a>', $event->get_description());
		}

		$temp_event=str_replace("{title}",$event->get_title(),$temp_event);
		$temp_event=str_replace("{description}",$desc,$temp_event);
		$temp_event=str_replace("{where}",$event->get_location(),$temp_event);
		$temp_event=str_replace("{backlink}",JRoute::_('index.php?option=com_gcalendar&view=event&eventID='.$event->get_id().'&start='.$event->get_start_date().'&end='.$event->get_end_date().'&gcid='.$feed->get('gcid').$itemID),$temp_event);
		$temp_event=str_replace("{link}",$event->get_link().'&ctz='.$tz,$temp_event);
		$temp_event=str_replace("{maplink}","http://maps.google.com/?q=".urlencode($event->get_location()),$temp_event);
		$temp_event=str_replace("{calendarname}",$feed->get('gcname'),$temp_event);
		$temp_event=str_replace("{calendarcolor}",$feed->get('gccolor'),$temp_event);
		// Accept and translate HTML
		$temp_event = html_entity_decode($temp_event);
		return $temp_event;
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

}
?>