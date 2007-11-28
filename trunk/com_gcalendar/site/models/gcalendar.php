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
		$params = &$this->getState('parameters.menu');
		if($params){
			$calendarName=$params->get('name');
			$calendarType='htmlUrl';
		}else{
			$calendarName = &$this->getState('calendarName');
			$calendarType = &$this->getState('calendarType');
		}
		
		$db =& JFactory::getDBO();

		$query = 'SELECT '.$calendarType.' FROM #__gcalendar where name=\''.$calendarName.'\'';
		$db->setQuery( $query );
		return $db->loadResult();
	}
	
}
