<?php
/**
 * Google calendar component
 * @author allon
 * @version $Revision: 1.5.1 $
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * GCalendar Controller
 *
 */
class GCalendarsControllerGCalendar extends GCalendarsController
{
	/**
	 * constructor (registers additional tasks to methods)
	 * @return void
	 */
	function __construct()
	{
		parent::__construct();

		// Register Extra tasks
		$this->registerTask( 'add'  , 	'edit' );
	}
	
	/**
	 * display the google calendar
	 * @return void
	 */
	function showCalendar()
	{
		JRequest::setVar( 'layout', 'calendar'  );
		JRequest::setVar('hidemainmenu', 1);

		parent::display();
	}

	/**
	 * display the edit form
	 * @return void
	 */
	function edit()
	{
		JRequest::setVar( 'view', 'GCalendar' );
		JRequest::setVar( 'layout', 'form'  );
		JRequest::setVar('hidemainmenu', 1);

		parent::display();
	}

	/**
	 * save a record (and redirect to main page)
	 * @return void
	 */
	function save()
	{
		$model = $this->getModel('GCalendar');

		if ($model->store($post)) {
			$msg = JText::_( 'Calendar saved!' );
		} else {
			$msg = JText::_( 'Error saving calendar' );
		}

		// Check the table in so it can be edited.... we are done with it anyway
		$link = 'index.php?option=com_gcalendar';
		$this->setRedirect($link, $msg);
	}

	/**
	 * remove record(s)
	 * @return void
	 */
	function remove()
	{
		$model = $this->getModel('GCalendar');
		if(!$model->delete()) {
			$msg = JText::_( 'Error: One or more calendars could not be deleted' );
		} else {
			$msg = JText::_( 'Calendar(s) deleted' );
		}

		$this->setRedirect( 'index.php?option=com_gcalendar', $msg );
	}

	/**
	 * cancel editing a record
	 * @return void
	 */
	function cancel()
	{
		$msg = JText::_( 'Operation cancelled' );
		$this->setRedirect( 'index.php?option=com_gcalendar', $msg );
	}
}
?>
