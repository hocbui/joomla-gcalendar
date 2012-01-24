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

/**
 * A util class with some static helper methodes used in GCalendar.
 *
 * @author allon
 */
class GCalendarUtil{

	/**
	 * Loads the simplepie Libraries the correct way.
	 */
	function ensureSPIsLoaded(){
		jimport('simplepie.simplepie');

		if(!class_exists('SimplePie_GCalendar')){
			require_once (JPATH_ADMINISTRATOR.DS.'components'.DS.'com_gcalendar'.DS.'libraries'.DS.'sp-gcalendar'.DS.'simplepie-gcalendar.php');
		}
	}

	/**
	 * Loads JQuery if the component parameter is set to yes.
	 */
	function loadJQuery(){
		static $jQueryloaded;
		if($jQueryloaded == null){
			$param   = GCalendarUtil::getComponentParameter('loadJQuery');
			if($param == 'yes' || empty($param)){
				$document =& JFactory::getDocument();
				$document->addScript(JURI::base().'components/com_gcalendar/libraries/jquery/jquery-1.3.2.min.js');
				$document->addScriptDeclaration("jQuery.noConflict();");
			}
			$jQueryloaded = 'loaded';
		}
	}

	/**
	 * Returns the component parameter for the given key.
	 *
	 * @param $key
	 * @param $defaultValue
	 * @return the component parameter
	 */
	function getComponentParameter($key, $defaultValue = null){
		$params   = JComponentHelper::getParams('com_gcalendar');
		return $params->get($key, $defaultValue);
	}

	/**
	 * Returns the correct configured frontend language for the
	 * joomla web site.
	 * The format is something like de-DE which can be passed to google.
	 *
	 * @return the frontend language
	 */
	function getFrLanguage(){
		$conf	=& JFactory::getConfig();
		return $conf->getValue('config.language');
		//		$params   = JComponentHelper::getParams('com_languages');
		//		return $params->get('site', 'en-GB');
	}

	/**
	 *Returns a valid Item ID for the given calendar id. If none is found
	 *NULL is returned.
	 *
	 * @param $cal_id
	 * @return the item id
	 */
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

	/**
	 * The simplepie event is rendered for the given formats and
	 * returned as HTML code.
	 *
	 * @param $event
	 * @param $format
	 * @param $dateformat
	 * @param $timeformat
	 * @return the HTML code of the efent
	 */
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

	function getCalendarItems($params) {
		GCalendarUtil::ensureSPIsLoaded();
		$calendarids = $params->get('calendarids');
		$results = GCalendarDBUtil::getCalendars($calendarids);
		if(empty($results)){
			JError::raiseWarning( 500, 'The selected calendar(s) were not found in the database.');
			return array();
		}
		$values = array();
		foreach ($results as $result) {
			if(!empty($result->calendar_id)){
				$sortOrder = $params->get( 'order', 1 )==1;
				$maxEvents = $params->get('max_events', 10);

				$feed = new SimplePie_GCalendar();
				$feed->set_show_past_events($params->get('past_events', TRUE));
				$startDate = $params->get('start_date', '');
				$endDate = $params->get('end_date', '');
				if(!empty($startDate) && !empty($endDate)){
					$feed->set_start_date(strtotime($startDate));
					$feed->set_end_date(strtotime($endDate));
				}
				$feed->set_sort_ascending(TRUE);
				$feed->set_orderby_by_start_date($sortOrder);
				$feed->set_expand_single_events($params->get('expand_events', TRUE));
				$feed->enable_order_by_date(false);

				$conf =& JFactory::getConfig();
				if ($params != null && ($params->get('gc_cache', 0) == 2 || ($params->get('gc_cache', 0) == 1 && $conf->getValue( 'config.caching' )))){
					$cacheTime = $params->get( 'gccache_time', $conf->getValue( 'config.cachetime' ) * 60 );
					// check if cache directory exists and is writeable
					$cacheDir =  JPATH_BASE.DS.'cache'.DS.$params->get('gc_cache_folder', '');
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
						$feed->set_cache_duration($cacheTime);
					}
				} else {
					$feed->enable_cache(false);
					$feed->set_cache_duration(-1);
				}

				$feed->set_max_events($maxEvents);
				$feed->set_timezone(GCalendarUtil::getComponentParameter('timezone'));
				$feed->set_cal_language(GCalendarUtil::getFrLanguage());
				$feed->set_cal_query($params->get('find', ''));
				$feed->put('gcid',$result->id);
				$feed->put('gcname',$result->name);
				$feed->put('gccolor',$result->color);
				$url = SimplePie_GCalendar::create_feed_url($result->calendar_id, $result->magic_cookie);

				$feed->set_feed_url($url);
				// Initialize the feed so that we can use it.
				$feed->init();
				//				echo $feed->feed_url;

				if ($feed->error()){
					JError::raiseWarning(500, 'Simplepie detected an error. Please run the <a href=<"administrator/components/com_gcalendar/libraries/sp-gcalendar/sp_compatibility_test.php">compatibility utility</a>.', $feed->error());
				}

				// Make sure the content is being served out to the browser properly.
				$feed->handle_content_type();

				$values = array_merge($values, $feed->get_items());
			}
		}

		// we sort the array based on the event compare function
		usort($values, array("SimplePie_Item_GCalendar", "compare"));

		$filterText = $params->get('title_filter', '');
		if(!empty($filterText) && $filterText != '.*'){
			$values = array_filter($values, array($this, "filter"));
		}

		$offset = $params->get('offset', 0);
		$numevents = $params->get('count', $maxEvents);

		$events = array_slice($values, $offset, $numevents);

		//return the feed data structure for the template
		return $events;
	}

	function filter($event) {
		$params = $this->params;
		$filter = $params->get('title_filter', '.*');

		if (!preg_match('/'.$filter.'/', $event->get_title())) {
			return false;
		}
		if ($event->get_end_date() > time()) {
			return true;
		}

		return false;
	}

	/**
	 * Returns the faded color for the given color.
	 *
	 * @param $pCol
	 * @param $pPercentage
	 * @return the faded color
	 */
	function getFadedColor($color, $percentage = 85) {
		$percentage = 100 - $percentage;
		$rgbValues = array_map( 'hexDec', GCalendarUtil::str_split( ltrim($color, '#'), 2 ) );

		for ($i = 0, $len = count($rgbValues); $i < $len; $i++) {
			$rgbValues[$i] = decHex( floor($rgbValues[$i] + (255 - $rgbValues[$i]) * ($percentage / 100) ) );
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