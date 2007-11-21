<?php
/**
 * Google calendar component
 * @author allon
 * @version $Revision: 1.5.0 $
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
		$db =& JFactory::getDBO();
		$params = &$this->getState('parameters.menu');
		$calendarName=$params->get('name');

		$query = 'SELECT htmlUrl FROM #__gcalendar where name=\''.$calendarName.'\'';
		$db->setQuery( $query );
		$gcalendar = $db->loadResult();
		
		return $gcalendar;
	}
	
}
