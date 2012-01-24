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
class GCalendarViewGCalendar extends JView
{
	function display($tpl = null)
	{
		global $mainframe;
		
		$gcalendar = $this->get( 'GCalendar' );
		$this->assignRef( 'gcalendar',	$gcalendar );
		
		$params = &$mainframe->getParams();
		$this->assignRef('params'  , $params);

		parent::display($tpl);
	}
}
?>
