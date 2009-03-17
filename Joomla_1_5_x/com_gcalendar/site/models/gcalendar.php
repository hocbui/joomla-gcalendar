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
 * @version $Revision: 2.1.0 $
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
		$query = "SELECT id, calendar_id, name, color, magic_cookie  FROM #__gcalendar";
		$db->setQuery( $query );
		$results = $db->loadObjectList();
		if(empty($results))
		return '';
		$calendars = array();
		foreach ($results as $result) {
			$is_selected = FALSE;
			if ($calendarids){
				if( is_array( $calendarids ) ) {
					$result->selected = in_array($result->id,$calendarids);
				} else {
					$result->selected = $result->id === $calendarids;
				}
			}
			$calendars[] = $result;
		}
		return $calendars;
	}

}
