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
 * @copyright 2007-2011 Allon Moritz
 * @since 2.2.0
 */

defined('_JEXEC') or die('Restricted access');

require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_gcalendar'.DS.'dbutil.php');
require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_gcalendar'.DS.'libraries'.DS.'mustache'.DS.'Mustache.php');

/**
 * A util class with some static helper methodes used in GCalendar.
 *
 * @author allon
 */
class GCalendarUtil{

	/**
	 * Returns the component parameter for the given key.
	 *
	 * @param $key
	 * @param $defaultValue
	 * @return the component parameter
	 */
	public static function getComponentParameter($key, $defaultValue = null){
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
	public static function getFrLanguage(){
		$conf = JFactory::getConfig();
		return $conf->getValue('config.language');
	}

	/**
	 * Returns a valid Item ID for the given calendar id. If none is found
	 * NULL is returned.
	 *
	 * @param $cal_id
	 * @return the item id
	 */
	public static function getItemId($cal_id){
		$component = JComponentHelper::getComponent('com_gcalendar');
		$menu = JFactory::getApplication()->getMenu();
		$items = $menu->getItems('component_id', $component->id);

		$default = null;
		if (is_array($items)){
			foreach($items as $item) {
				$default = $item;
				$paramsItem	= $menu->getParams($item->id);
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
		return $default;
	}

	public static function renderEvents(array $events = null, $output, $params, $configuration = array()){
		if($events === null){
			$events = array();
		}

		JFactory::getLanguage()->load('com_gcalendar', JPATH_ADMINISTRATOR);

		$lastHeading = '';

		$configuration = array();
		$configuration['events'] = array();
		foreach ($events as $event) {
			$variables = array();

			$itemID = GCalendarUtil::getItemId($event->getParam('gcid', null));
			if(!empty($itemID) && JRequest::getVar('tmpl', null) != 'component' && $event != null){
				$component = JComponentHelper::getComponent('com_gcalendar');
				$menu = JFactory::getApplication()->getMenu();
				$item = $menu->getItem($itemID);
				if($item !=null){
					$backLinkView = $item->query['view'];
					$dateHash = '';
					if($backLinkView == 'gcalendar'){
						$day = strftime('%d', $event->getStartDate());
						$month = strftime('%m', $event->getStartDate());
						$year = strftime('%Y', $event->getStartDate());
						$dateHash = '#year='.$year.'&month='.$month.'&day='.$day;
					}
				}
				$variables['calendarLink'] = JRoute::_('index.php?option=com_gcalendar&Itemid='.$itemID.$dateHash);
			}

			$itemID = GCalendarUtil::getItemId($event->getParam('gcid'));
			if(!empty($itemID)){
				$itemID = '&Itemid='.$itemID;
			}else{
				$menu = JFactory::getApplication()->getMenu();
				$activemenu = $menu->getActive();
				if($activemenu != null){
					$itemID = '&Itemid='.$activemenu->id;
				}
			}

			$variables['backlink'] = htmlentities(JRoute::_('index.php?option=com_gcalendar&view=event&eventID='.$event->getGCalId().'&gcid='.$event->getParam('gcid').$itemID));

			$tz = GCalendarUtil::getComponentParameter('timezone');
			if($tz == ''){
				$tz = $event->getTimezone();
			}
			$variables['link'] = $event->getLink('alternate')->getHref().'&ctz='.$tz;
			$variables['calendarcolor'] = $event->getParam('gccolor');

			// the date formats from http://php.net/date
			$dateformat = $params->get('event_date_format', 'm.d.Y');
			$timeformat = $params->get('event_time_format', 'g:i a');

			// These are the dates we'll display
			$startDate = GCalendarUtil::formatDate($dateformat, $event->getStartDate());
			$startTime = GCalendarUtil::formatDate($timeformat, $event->getStartDate());
			$endDate = GCalendarUtil::formatDate($dateformat, $event->getEndDate());
			$endTime = GCalendarUtil::formatDate($timeformat, $event->getEndDate());
			$dateSeparator = '-';

			$timeString = $startTime.' '.$startDate.' '.$dateSeparator.' '.$endTime.' '.$endDate;
			$copyDateTimeFormat = 'Ymd';
			switch($event->getDayType()){
				case GCalendar_Entry::SINGLE_WHOLE_DAY:
					$timeString = $startDate;
					$copyDateTimeFormat = 'Ymd';

					$startTime = '';
					$endTime = '';
					break;
				case GCalendar_Entry::SINGLE_PART_DAY:
					$timeString = $startDate.' '.$startTime.' '.$dateSeparator.' '.$endTime;
					$copyDateTimeFormat = 'Ymd\THis';
					break;
				case GCalendar_Entry::MULTIPLE_WHOLE_DAY:
					$SECSINDAY=86400;
					$endDate = GCalendarUtil::formatDate($dateformat, $event->getEndDate()-$SECSINDAY);
					$timeString = $startDate.' '.$dateSeparator.' '.$endDate;
					$copyDateTimeFormat = 'Ymd';

					$startTime = '';
					$endTime = '';
					break;
				case GCalendar_Entry::MULTIPLE_PART_DAY:
					$timeString = $startTime.' '.$startDate.' '.$dateSeparator.' '.$endTime.' '.$endDate;
					$copyDateTimeFormat = 'Ymd\THis';
					break;
			}
			$variables['calendarName'] = $params->get('show_calendar_name', 1) == 1 ? $event->getParam('gcname') : null;
			$variables['title'] = $params->get('show_event_title', 1) == 1 ? (string)$event->getTitle() : null;
			if($params->get('show_event_date', 1) == 1){
				$variables['date'] = $timeString;
				$variables['startDate'] = $startDate;
				$variables['startTime'] = $startTime;
				$variables['endDate'] = $endDate;
				$variables['endTime'] = $endTime;
				$variables['dateseparator'] = $dateSeparator;

				$variables['month'] = strtoupper(GCalendarUtil::formatDate('M', $event->getStartDate()));
				$variables['day'] = GCalendarUtil::formatDate('d', $event->getStartDate());
			}
			$variables['modifieddate'] = $params->get('show_event_modified_date', 1) == 1 ? GCalendarUtil::formatDate($timeformat, $event->getModifiedDate()).' '.GCalendarUtil::formatDate($dateformat, $event->getModifiedDate()) : null;

			if($params->get('show_event_attendees', 2) == 1 && count($event->getWho()) > 0){
				$variables['hasAttendees'] = true;
				$variables['attendees'] = array();
				foreach ($event->getWho() as $a) {
					$variables['attendees'][] = array('name' => (string)$a->getValueString(), 'email' =>  base64_encode(str_replace('@','#',$a->getEmail())));
				}
			}
			$variables['location'] = $params->get('show_event_location', 1) == 1 ? $event->getLocation() : null;
			$variables['maplink'] = $params->get('show_event_location_map', 1) == 1 ? "http://maps.google.com/?q=".urlencode($event->getLocation()).'&hl='.substr(GCalendarUtil::getFrLanguage(),0,2).'&output=embed' : null;

			if($params->get('show_event_description', 1) == 1) {
				$variables['description'] = $event->getContent();
				if($params->get('event_description_format', 1) == 1) {
					$variables['description'] = preg_replace("@(src|href)=\"https?://@i",'\\1="', nl2br($event->getContent()));
					$variables['description'] = preg_replace("@(((f|ht)tp:\/\/)[^\"\'\>\s]+)@",'<a href="\\1" target="_blank">\\1</a>', $variables['description']);
				}
			}
			if($params->get('show_event_author', 2) == 1){
				$variables['hasAuthor'] = true;
				$variables['author'] = array();
				foreach ($event->getAuthor() as $author) {
					$variables['author'][] = array('name' => (string)$author->getName(), 'email' =>  base64_encode(str_replace('@','#',$author->getEmail())));
				}
			}

			if($params->get('show_event_copy_info', 1) == 1){
				$variables['copyGoogleUrl'] = 'http://www.google.com/calendar/render?action=TEMPLATE&amp;text='.urlencode($event->getTitle());
				$variables['copyGoogleUrl'] .= '&amp;dates='.GCalendarUtil::formatDate($copyDateTimeFormat, $event->getStartDate()).'%2F'.GCalendarUtil::formatDate($copyDateTimeFormat, $event->getEndDate());
				$variables['copyGoogleUrl'] .= '&amp;location='.urlencode($event->getLocation());
				$variables['copyGoogleUrl'] .= '&amp;details='.urlencode($event->getContent());
				$variables['copyGoogleUrl'] .= '&amp;hl='.GCalendarUtil::getFrLanguage().'&amp;ctz='.GCalendarUtil::getComponentParameter('timezone');
				$variables['copyGoogleUrl'] .= '&amp;sf=true&amp;output=xml';

				$ical_timeString_start =  $startTime.' '.$startDate;
				$ical_timeString_start = strtotime($ical_timeString_start);
				$ical_timeString_end =  $endTime.' '.$endDate;
				$ical_timeString_end = strtotime($ical_timeString_end);
				$loc = $event->getLocation();
				$variables['copyOutlookUrl'] = JRoute::_("index.php?option=com_gcalendar&view=ical&format=raw&start=".$ical_timeString_start."&end=".$ical_timeString_end."&title=".$event->getTitle()."&location=".$loc);
			}

			$groupHeading = GCalendarUtil::formatDate($params->get('grouping', ''), $event->getStartDate());
			if ($groupHeading != $lastHeading) {
				$lastHeading = $groupHeading;
				$variables['header'] =  $groupHeading;
			}

			$variables['calendarLinkLabel'] = JText::_('COM_GCALENDAR_FIELD_CONFIG_EVENT_LABEL_CALENDAR_BACK_LINK');
			$variables['calendarNameLabel'] = JText::_('COM_GCALENDAR_FIELD_CONFIG_EVENT_LABEL_CALENDAR_NAME');
			$variables['titleLabel'] = JText::_('COM_GCALENDAR_FIELD_CONFIG_EVENT_LABEL_EVENT_TITLE');
			$variables['dateLabel'] = JText::_('COM_GCALENDAR_FIELD_CONFIG_EVENT_LABEL_WHEN');
			$variables['attendeesLabel'] = JText::_('COM_GCALENDAR_FIELD_CONFIG_EVENT_LABEL_ATTENDEES');
			$variables['locationLabel'] = JText::_('COM_GCALENDAR_FIELD_CONFIG_EVENT_LABEL_LOCATION');
			$variables['descriptionLabel'] = JText::_('COM_GCALENDAR_FIELD_CONFIG_EVENT_LABEL_DESCRIPTION');
			$variables['authorLabel'] = JText::_('COM_GCALENDAR_FIELD_CONFIG_EVENT_LABEL_AUTHOR');
			$variables['copyLabel'] = JText::_('COM_GCALENDAR_FIELD_CONFIG_EVENT_LABEL_COPY');
			$variables['copyGoogleLabel'] = JText::_('COM_GCALENDAR_FIELD_CONFIG_EVENT_LABEL_COPY_TO_MY_CALENDAR');
			$variables['copyOutlookLabel'] = JText::_('COM_GCALENDAR_FIELD_CONFIG_EVENT_LABEL_COPY_TO_MY_CALENDAR_ICS');
			$variables['language'] = substr(GCalendarUtil::getFrLanguage(),0,2);

			$configuration['events'][] = $variables;
		}

		$configuration['emptyText'] = JText::_('COM_GCALENDAR_FIELD_CONFIG_EVENT_LABEL_NO_EVENT_TEXT');

		try{
			$m = new Mustache;
			return $m->render($output, $configuration);
		}catch(Exception $e){
			echo $e->getMessage();
		}
	}

	/**
	 * Returns the faded color for the given color.
	 *
	 * @param $color
	 * @param $percentage
	 * @return the faded color
	 */
	public static function getFadedColor($color, $percentage = 85) {
		$percentage = 100 - $percentage;
		$rgbValues = array_map( 'hexDec', str_split( ltrim($color, '#'), 2 ) );

		for ($i = 0, $len = count($rgbValues); $i < $len; $i++) {
			$rgbValues[$i] = decHex( floor($rgbValues[$i] + (255 - $rgbValues[$i]) * ($percentage / 100) ) );
		}

		return '#'.implode('', $rgbValues);
	}

	/**
	 * Translates day of week number to a string.
	 *
	 * @param	integer	The numeric day of the week.
	 * @param	boolean	Return the abreviated day string?
	 * @return	string	The day of the week.
	 */
	public static function dayToString($day, $abbr = false)
	{
		$name = '';
		switch ($day) {
			case 0:
				$name = $abbr ? JText::_('SUN') : JText::_('SUNDAY');
				break;
			case 1:
				$name = $abbr ? JText::_('MON') : JText::_('MONDAY');
				break;
			case 2:
				$name = $abbr ? JText::_('TUE') : JText::_('TUESDAY');
				break;
			case 3:
				$name = $abbr ? JText::_('WED') : JText::_('WEDNESDAY');
				break;
			case 4:
				$name = $abbr ? JText::_('THU') : JText::_('THURSDAY');
				break;
			case 5:
				$name = $abbr ? JText::_('FRI') : JText::_('FRIDAY');
				break;
			case 6:
				$name = $abbr ? JText::_('SAT') : JText::_('SATURDAY');
				break;
		}
		return addslashes($name);
	}

	/**
	 * Translates month number to a string.
	 *
	 * @param	integer	The numeric month of the year.
	 * @param	boolean	Return the abreviated month string?
	 * @return	string	The month of the year.
	 */
	public static function monthToString($month, $abbr = false)
	{
		$name = '';
		switch ($month) {
			case 1:
				$name = $abbr ? JText::_('JANUARY_SHORT')	: JText::_('JANUARY');
				break;
			case 2:
				$name = $abbr ? JText::_('FEBRUARY_SHORT')	: JText::_('FEBRUARY');
				break;
			case 3:
				$name = $abbr ? JText::_('MARCH_SHORT')		: JText::_('MARCH');
				break;
			case 4:
				$name = $abbr ? JText::_('APRIL_SHORT')		: JText::_('APRIL');
				break;
			case 5:
				$name = $abbr ? JText::_('MAY_SHORT')		: JText::_('MAY');
				break;
			case 6:
				$name = $abbr ? JText::_('JUNE_SHORT')		: JText::_('JUNE');
				break;
			case 7:
				$name = $abbr ? JText::_('JULY_SHORT')		: JText::_('JULY');
				break;
			case 8:
				$name = $abbr ? JText::_('AUGUST_SHORT')	: JText::_('AUGUST');
				break;
			case 9:
				$name = $abbr ? JText::_('SEPTEMBER_SHORT')	: JText::_('SEPTEMBER');
				break;
			case 10:
				$name = $abbr ? JText::_('OCTOBER_SHORT')	: JText::_('OCTOBER');
				break;
			case 11:
				$name = $abbr ? JText::_('NOVEMBER_SHORT')	: JText::_('NOVEMBER');
				break;
			case 12:
				$name = $abbr ? JText::_('DECEMBER_SHORT')	: JText::_('DECEMBER');
				break;
		}
		return addslashes($name);
	}

	public static function formatDate($dateFormat,$date,$strf = false){
		$dateObj = JFactory::getDate($date);

		$gcTz = GCalendarUtil::getComponentParameter('timezone');
		if(!empty($gcTz)){
			$tz = new DateTimeZone($gcTz);
			$dateObj->setTimezone($tz);
		}
		if ($strf) {
			return $dateObj->toFormat($dateFormat, true);
		}

		return $dateObj->format($dateFormat, true);
	}

	public static function getActions($calendarId = 0){
		$user  = JFactory::getUser();
		$result  = new JObject;

		if (empty($calendarId)) {
			$assetName = 'com_gcalendar';
		}
		else {
			$assetName = 'com_gcalendar.calendar.'.(int) $calendarId;
		}

		$actions = array('core.admin', 'core.manage', 'core.create', 'core.edit', 'core.delete');

		foreach ($actions as $action) {
			$result->set($action, $user->authorise($action, $assetName));
		}

		return $result;
	}

	// http://core.trac.wordpress.org/browser/trunk/wp-includes/formatting.php#L1461
	public static function transformUrl( $text ) {
		$ret = ' ' . $ret;
		// in testing, using arrays here was found to be faster
		$ret = preg_replace_callback('#([\s>])([\w]+?://[\w\#$%&~/.\-;:=,?@\[\]+]*)#is', '_make_url_clickable_cb', $ret);
		$ret = preg_replace_callback('#([\s>])((www|ftp)\.[\w\#$%&~/.\-;:=,?@\[\]+]*)#is', '_make_web_ftp_clickable_cb', $ret);
		$ret = preg_replace_callback('#([\s>])([.0-9a-z_+-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,})#i', '_make_email_clickable_cb', $ret);
		// this one is not in an array because we need it to run last, for cleanup of accidental links within links
		$ret = preg_replace("#(<a( [^>]+?>|>))<a [^>]+?>([^>]+?)</a></a>#i", "$1$3</a>", $ret);
		$ret = trim($ret);
		return $ret;
	}
}