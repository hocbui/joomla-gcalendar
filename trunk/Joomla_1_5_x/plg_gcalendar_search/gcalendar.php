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
 * @version $Revision: 2.0.1 $
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

$mainframe->registerEvent( 'onSearch', 'plgSearchGCalendar' );
$mainframe->registerEvent( 'onSearchAreas', 'plgSearchGCalendarAreas' );

JPlugin::loadLanguage( 'plg_search_gcalendar' );

/**
 * @return array An array of search areas
 */
function &plgSearchWeblinksAreas() {
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
function plgSearchGCalendar( $text, $phrase='', $ordering='', $areas=null )
{
	$db		=& JFactory::getDBO();
	$user	=& JFactory::getUser();

	$searchText = $text;

	require_once(JPATH_SITE.DS.'components'.DS.'com_gcalendar'.DS.'helpers'.DS.'route.php');

	if(!class_exists('SimplePie')){
		//include Simple Pie processor class
		require_once (JPATH_SITE.DS.'libraries'.DS.'simplepie'.DS.'simplepie.php');
	}

	if(!class_exists('SimplePie_GCalendar')){
		//include Simple Pie processor class
		require_once (JPATH_SITE.DS.'plugins'.DS.'search'.DS.'simplepie-gcalendar.php');
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

	$text = trim( $text );
	if ($text == '') {
		return array();
	}
	$section 	= JText::_( 'GCalendar' );

	$wheres 	= array();
	switch ($phrase)
	{
		case 'exact':
			$text		= $db->Quote( '%'.$db->getEscaped( $text, true ).'%', false );
			$wheres2 	= array();
			$wheres2[] 	= 'a.url LIKE '.$text;
			$wheres2[] 	= 'a.description LIKE '.$text;
			$wheres2[] 	= 'a.title LIKE '.$text;
			$where 		= '(' . implode( ') OR (', $wheres2 ) . ')';
			break;

		case 'all':
		case 'any':
		default:
			$words 	= explode( ' ', $text );
			$wheres = array();
			foreach ($words as $word)
			{
				$word		= $db->Quote( '%'.$db->getEscaped( $word, true ).'%', false );
				$wheres2 	= array();
				$wheres2[] 	= 'a.url LIKE '.$word;
				$wheres2[] 	= 'a.description LIKE '.$word;
				$wheres2[] 	= 'a.title LIKE '.$word;
				$wheres[] 	= implode( ' OR ', $wheres2 );
			}
			$where 	= '(' . implode( ($phrase == 'all' ? ') AND (' : ') OR ('), $wheres ) . ')';
			break;
	}

	switch ( $ordering )
	{
		case 'oldest':
			$order = 'a.date ASC';
			break;

		case 'popular':
			$order = 'a.hits DESC';
			break;

		case 'alpha':
			$order = 'a.title ASC';
			break;

		case 'category':
			$order = 'b.title ASC, a.title ASC';
			break;

		case 'newest':
		default:
			$order = 'a.date DESC';
	}

	$query = 'SELECT a.title AS title, a.description AS text, a.date AS created, a.url, '
	. ' CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(\':\', a.id, a.alias) ELSE a.id END as slug, '
	. ' CASE WHEN CHAR_LENGTH(b.alias) THEN CONCAT_WS(\':\', b.id, b.alias) ELSE b.id END as catslug, '
	. ' CONCAT_WS( " / ", '.$db->Quote($section).', b.title ) AS section,'
	. ' "1" AS browsernav'
	. ' FROM #__weblinks AS a'
	. ' INNER JOIN #__categories AS b ON b.id = a.catid'
	. ' WHERE ('. $where .')'
	. ' AND a.published = 1'
	. ' AND b.published = 1'
	. ' AND b.access <= '.(int) $user->get( 'aid' )
	. ' ORDER BY '. $order
	;
	$db->setQuery( $query, 0, $limit );
	$rows = $db->loadObjectList();

	foreach($rows as $key => $row) {
		$rows[$key]->href = WeblinksHelperRoute::getWeblinkRoute($row->slug, $row->catslug);
	}

	$return = array();
	foreach($rows AS $key => $weblink) {
		if(searchHelper::checkNoHTML($weblink, $searchText, array('url', 'text', 'title'))) {
			$return[] = $weblink;
		}
	}

	return $return;
}

function create_gc_feed(){
		$feed = new SimplePie_GCalendar();
		$feed->set_show_past_events(TRUE);
		$feed->set_sort_ascending(TRUE);
		$feed->set_orderby_by_start_date(TRUE);
		$feed->set_expand_single_events(TRUE);
		$feed->enable_order_by_date(FALSE);

		// check if cache directory exists and is writeable
		$cacheDir =  JPATH_BASE.DS.'cache'.DS.'plg_gcalendar_search';
		JFolder::create($cacheDir, 0755);
		if ( !is_writable( $cacheDir ) ) {
			$cache_exists = false;
		}else{
			$cache_exists = true;
		}

		//check and set caching
		if($cache_exists) {
			$feed->set_cache_location($cacheDir);
			$feed->enable_cache();
			$cache_time = (intval($params->get( 'cache', 3600 )));
			$feed->set_cache_duration($cache_time);
		}
		else {
			$feed->enable_cache(FALSE);
		}
		return $feed;
	}
