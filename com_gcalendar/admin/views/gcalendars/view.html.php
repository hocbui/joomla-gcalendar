<?php
/**
 * Google calendar component
 * @author allon
 * @version $Revision: 1.5.0 $
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );

/**
 * GCalendars View
 *
 */
class GCalendarsViewGCalendars extends JView
{
	/**
	 * GCalendars view display method
	 * @return void
	 **/
	function display($tpl = null)
	{
		JToolBarHelper::title(   JText::_( 'GCALENDAR_MANAGER' ),  'calendar');
		if(JRequest::getVar( 'layout')!='calendar' && JRequest::getVar( 'layout')!='support'){
			JToolBarHelper::deleteList();
			JToolBarHelper::editListX();
			JToolBarHelper::addNewX();
		}

		// Get data from the model
		$items		= & $this->get( 'Data');

		$this->assignRef('items',		$items);

		parent::display($tpl);
	}
}
