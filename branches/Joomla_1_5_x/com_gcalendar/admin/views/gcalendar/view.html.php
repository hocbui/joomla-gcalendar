<?php
/**
 * Google calendar component
 * @author allon
 * @version $Revision: 2.0.0 $
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );

/**
 * GCalendar View
 *
 */
class GCalendarsViewGCalendar extends JView
{
	/**
	 * display method of GCalendar view
	 * @return void
	 **/
	function display($tpl = null)
	{
		//get the calendar
		$gcalendar	=& $this->get('Data');
		$isNew		= ($gcalendar->id < 1);

		$text = $isNew ? JText::_( 'New' ) : JText::_( 'Edit' );
		JToolBarHelper::title(   JText::_( 'GCALENDAR_MANAGER' ).': <small><small>[ ' . $text.' ]</small></small>' ,'calendar');
		JToolBarHelper::save();
		if ($isNew)  {
			JToolBarHelper::cancel();
		} else {
			// for existing items the button is renamed `close`
			JToolBarHelper::cancel( 'cancel', 'Close' );
		}

		$this->assignRef('gcalendar', $gcalendar);

		parent::display($tpl);
	}
}
