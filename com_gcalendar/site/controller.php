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
class GCalendarController extends JController
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
	
	function content()
	{
		$document =& JFactory::getDocument();
		
		$viewType	= $document->getType();
		$viewName	= JRequest::getCmd( 'view', 'Content' );
		$viewLayout	= JRequest::getCmd( 'layout', 'default' );

		$view = & $this->getView( $viewName, $viewType, '', array( 'base_path'=>$this->_basePath));

		// Get/Create the model
		if ($model = & $this->getModel('gcalendar')) {
			// Push the model into the view (as default)
			$model->setState('calendarName',JRequest::getVar('calendarName', null));
			$model->setState('calendarType',JRequest::getVar('calendarType', null));
			
			$view->setModel($model, true);
		}

		// Set the layout
		$view->setLayout($viewLayout);

		// Display the view
		if ($cachable) {
			global $option;
			$cache =& JFactory::getCache($option, 'view');
			$cache->get($view, 'display');
		} else {
			$view->display();
		}
	}

}
?>
