<?php
/**
 * Google calendar component
 * @author allon
 * @version $Revision: 1.5.1 $
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

// Require the base controller
require_once (JPATH_COMPONENT.DS.'controller.php');

// Require specific controller if requested
if($controller = JRequest::getVar('controller')) {
	require_once (JPATH_COMPONENT.DS.'controllers'.DS.$controller.'.php');
}

$document =& JFactory::getDocument();
$document->addStyleSheet($mainframe->getSiteURL() . 'administrator/components/com_gcalendar/css/gcalendar.css'); 		

// Create the controller
$classname	= 'GCalendarsController'.$controller;
$controller = new $classname( );

// Perform the Request task
$controller->execute( JRequest::getVar('task'));

// Redirect if set by the controller
$controller->redirect();

?>
