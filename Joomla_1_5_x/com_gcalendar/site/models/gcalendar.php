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

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.model' );

/**
 * GCalendar Model
 *
 */
class GCalendarModelGCalendar extends JModel
{

	/**
	 * Gets the calendar
	 * @return string The calendar to be displayed to the user
	 */
	function getGCalendar()
	{
		$params = $this->getState('parameters.menu');
		if($params==null)return;
		$calendarids=$params->get('calendarids');

		$db =& JFactory::getDBO();
		if ($calendarids){
			if( is_array( $calendarids ) ) {
				$calCondition = ' id IN ( ' . implode( ',', $calendarids ) . ')';
			} else {
				$calCondition = ' id = '.$calendarids;
			}
		}
		$query = "SELECT id, calendar_id, name, domaine, color, magic_cookie  FROM #__gcalendar where ".$calCondition;
		$db->setQuery( $query );
		$results = $db->loadObjectList();
		if(empty($results))
		return '';
		$calendars = array();
		foreach ($results as $result) {
			$calendars[] = array("id"=>$result->id,
			"calendar_id"=>$result->calendar_id,
			"name"=>$result->name,
			"domain"=>$result->domain,
			"color"=>$result->color,
			"magic_cookie"=>$result->magic_cookie);
		}
		return $calendars;
	}

}
