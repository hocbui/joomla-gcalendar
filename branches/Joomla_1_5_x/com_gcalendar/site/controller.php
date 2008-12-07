<?php
/**
 * Google calendar component
 * @author allon
 * @version $Revision: 2.0.0 $
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

		$this->addViewPath($this->_basePath.DS.'hiddenviews');
		$view = & $this->getView( $viewName, $viewType, '', array( 'base_path'=>$this->_basePath));
		$view->addTemplatePath($this->_basePath.DS.'hiddenviews'.DS.strtolower($viewName).DS.'tmpl');

		// Get/Create the model
		if ($model = & $this->getModel('gcalendar')) {
			$model->setState('calendarName',JRequest::getVar('calendarName', null));
			$model->setState('calendarType',JRequest::getVar('calendarType', null));
			
			// Push the model into the view (as default)
			$view->setModel($model, true);
		}

		// Set the layout
		$view->setLayout($viewLayout);

		$view->display();
	}
	
	function event()
	{
		$document =& JFactory::getDocument();
		
		$viewType	= $document->getType();
		$viewName	= JRequest::getCmd( 'view', 'Event' );
		$viewLayout	= JRequest::getCmd( 'layout', 'default' );
		
		$this->addViewPath($this->_basePath.DS.'hiddenviews');
		$view = & $this->getView( $viewName, $viewType, '', array( 'base_path'=>$this->_basePath));
		$view->addTemplatePath($this->_basePath.DS.'hiddenviews'.DS.strtolower($viewName).DS.'tmpl');
		
		// Get/Create the model
		if ($model = & $this->getModel('gcalendar')) {
			$model->setState('calendarName',JRequest::getVar('calendarName', null));
			$model->setState('calendarType',JRequest::getVar('calendarType', 'htmlUrl'));
			
			// Push the model into the view (as default)
			$view->setModel($model, true);
		}

		// Set the layout
		$view->setLayout($viewLayout);

		$view->display();
	}

}
?>
