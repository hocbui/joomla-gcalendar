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

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

$mainframe->registerEvent( 'onSearch', 'plgSearchGCalendar' );
$mainframe->registerEvent( 'onSearchAreas', 'plgSearchGCalendarAreas' );

JPlugin::loadLanguage( 'plg_search_gcalendar' );

/**
 * @return array An array of search areas
 */
function &plgSearchGCalendarAreas() {
	static $areas = array(
		'gcalendar' => 'GCalendar'
		);
		return $areas;
}

/**
 * Weblink Search method
 *
 * The sql must return the following fields that are used in a common display
 * routine: href, title, section, created, text, browsernav
 * @param string Target search string
 * @param string mathcing option, exact|any|all
 * @param string ordering option, newest|oldest|popular|alpha|category
 * @param mixed An array if the search it to be restricted to areas, null if search all
 */
function plgSearchGCalendar( $text, $phrase='', $ordering='', $areas=null ){
	require_once (JPATH_ADMINISTRATOR.DS.'components'.DS.'com_gcalendar'.DS.'util.php');
	require_once (JPATH_ADMINISTRATOR.DS.'components'.DS.'com_gcalendar'.DS.'dbutil.php');
	GCalendarUtil::ensureSPIsLoaded();
	
	$user	=& JFactory::getUser();

	$text = trim( $text );
	if ($text == '') {
		return array();
	}

	switch ( $ordering )
	{
		case 'oldest':
			$orderasc = TRUE;
			break;

		case 'newest':
		default:
			$orderasc = FALSE;
	}

	if (is_array( $areas )) {
		if (!array_intersect( $areas, array_keys( plgSearchGCalendarAreas() ) )) {
			return array();
		}
	}

	// load plugin params info
	$plugin =& JPluginHelper::getPlugin('search', 'gcalendar');
	$pluginParams = new JParameter( $plugin->params );

	$limit = $pluginParams->def( 'search_limit', 50 );

	$calendarids = $pluginParams->get( 'calendarids', NULL );
	$results = GCalendarDBUtil::getCalendars($calendarids);
	if(empty($results))
	return array();

	$events = array();
	foreach ($results as $result) {
		$feed = new SimplePie_GCalendar();
		$feed->set_show_past_events(TRUE);
		$feed->set_sort_ascending($orderasc);
		$feed->set_orderby_by_start_date(TRUE);
		$feed->set_expand_single_events(TRUE);
		$feed->enable_order_by_date(FALSE);
		$feed->enable_cache(FALSE);
		$feed->set_cache_duration(1);
		$feed->set_cal_query($text);
		$feed->put('gcid',$result->id);
		$feed->set_cal_language(GCalendarUtil::getFrLanguage());
		$feed->set_timezone(GCalendarUtil::getComponentParameter('timezone'));

		$url = SimplePie_GCalendar::create_feed_url($result->calendar_id, $result->magic_cookie);
		$feed->set_feed_url($url);
		$feed->init();
			
		$feed->handle_content_type();
		$events = array_merge($events, $feed->get_items());
	}

	usort($events, array("SimplePie_Item_GCalendar", "compare"));
	array_splice($events, $limit);

	$return = array();
	foreach($events as $event){
		$feed = $event->get_feed();

		$itemID = GCalendarUtil::getItemId($feed->get('gcid'));
		if(!empty($itemID))$itemID = '&Itemid='.$itemID;
		$row->href = JRoute::_('index.php?option=com_gcalendar&view=event&eventID='.$event->get_id().'&gcid='.$feed->get('gcid').$itemID);
		$row->title = $event->get_title();
		$row->text = $event->get_description();
		$row->section = JText::_('GCalendar');
		$row->category = $feed->get('gcid');
		$row->created = $event->get_publish_date();
		$row->browsernav = '';
		$return[] = $row;
		$row = null;
	}
	return $return;
}
