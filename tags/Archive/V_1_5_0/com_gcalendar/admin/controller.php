<?php
/**
 * Google calendar component
 * @author allon
 * @version $Revision: 1.5.0 $
 */

jimport('joomla.application.component.controller');

/**
 * GCalendar Component Controller
 *
 */
class GCalendarsController extends JController
{
	/**
	 * Method to display the view
	 *
	 * @access	public
	 */
	function display()
	{
		parent::display();
	}
	
	/**
	 * display the google calendar
	 * @return void
	 */
	function googleCalendar()
	{
		JRequest::setVar( 'layout', 'calendar'  );
		JRequest::setVar('hidemainmenu', 0);

		parent::display();
	}
	
	/**
	 * display the google calendar
	 * @return void
	 */
	function support()
	{
		JRequest::setVar( 'layout', 'support'  );
		JRequest::setVar('hidemainmenu', 0);

		parent::display();
	}

}
?>
