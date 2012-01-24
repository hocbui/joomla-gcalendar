<?php
/**
 * Google calendar component
 * @author allon
 * @version $Revision: 1.5.1 $
 */

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the GCalendar Component
 *
 */
class GCalendarViewContent extends JView
{
	function display($tpl = null)
	{
		$this->assignRef( 'path', $this->get( 'GCalendar' ));
		$model=$this->getModel();
		$this->assignRef('calendarType',$model->getState('calendarType'));
		
		parent::display($tpl);
	}
}
?>
