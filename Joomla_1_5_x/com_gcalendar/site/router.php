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

/**
 * @param	array
 * @return	array
 */
function GCalendarBuildRoute( &$query )
{
	$segments = array();
	$task = null;
	if(isset($query['view']))
	{
		$segments[] = $query['view'];
		unset( $query['view'] );
	}
	if(isset($query['task']))
	{
		$segments[] = $query['task'];
		$task=$query['task'];
		unset( $query['task'] );
	}
	if($task === 'event'){
		if(isset($query['eventID']))
		{
			$segments[] = $query['eventID'];
			unset( $query['eventID'] );
		}
		if(isset($query['Itemid']))
		{
			$segments[] = $query['Itemid'];
			unset( $query['Itemid'] );
		}
		if(isset($query['gcid']))
		{
			$segments[] = $query['gcid'];
			unset( $query['gcid'] );
		}
	}else{
		if (isset($query['Itemid'])){
			$itemid = (int) $query['Itemid'];

			$menu = &JSite::getMenu();
			$params	=& $menu->getParams($itemid);
			if($params->get('calendarids')){
				$segments[] = 'calendars';
				$segments[] = implode("-", $params->get('calendarids'));
			}
		}
	}
	return $segments;
}

/**
 * @param	array
 * @return	array
 */
function GCalendarParseRoute( $segments )
{
	$vars = array();
	$count = count( $segments );
	switch($segments[0])
	{
		case 'event':
			$vars['task'] = 'event';
			$vars['eventID'] = $segments[1];
			$vars['Itemid'] = $segments[2];
			$vars['gcid'] = $segments[3];
			break;
		case 'gcalendar':
			$vars['task'] = 'gcalendar';
			$vars['calendarids'] = explode("-",$segments[2]);
			break;

	}
	return $vars;
}