<?php
/**
 * Google calendar component
 * @author allon
 * @version $Revision: 1.5.0 $
 */

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the GCalendar Component
 *
 */
class GCalendarViewEvent extends JView
{
	function display($tpl = null)
	{
		$gcalendar = $this->get( 'GCalendar' );
		$this->assignRef( 'gcalendar',	$gcalendar );
		
		$this->assignRef( 'eventID', JRequest::getVar('eventID', null));
		$this->assignRef( 'timezone', JRequest::getVar('ctz', null));
		
		parent::display($tpl);
	}
}
?>
