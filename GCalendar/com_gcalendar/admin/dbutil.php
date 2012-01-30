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

class GCalendarDBUtil{

	public static function getCalendars($calendarIDs) {
		$condition = '';
		if(!empty($calendarIDs)){
			if(is_array($calendarIDs)) {
				$condition = 'id IN ( ' . rtrim(implode( ',', $calendarIDs ), ',') . ')';
			} else {
				$condition = 'id = '.(int)rtrim($calendarIDs, ',');
			}
		}else
		return GCalendarDBUtil::getAllCalendars();

		$db =& JFactory::getDBO();
		$query = "SELECT *  FROM #__gcalendar where ".$condition;
		
		// Implement View Level Access
		$user	= JFactory::getUser();
		if (!$user->authorise('core.admin'))
		{
			$groups	= implode(',', $user->getAuthorisedViewLevels());
			$query .= ' and access IN ('.$groups.')';
		}
		
		$db->setQuery( $query );
		$results = $db->loadObjectList();
		return $results;
	}

	public static function getAllCalendars() {
		$db =& JFactory::getDBO();
		$query = "SELECT *  FROM #__gcalendar";
		
		// Implement View Level Access
		$user	= JFactory::getUser();
		if (!$user->authorise('core.admin'))
		{
			$groups	= implode(',', $user->getAuthorisedViewLevels());
			$query .= ' where access IN ('.$groups.')';
		}
		
		$db->setQuery( $query );
		return $db->loadObjectList();
	}
}
?>