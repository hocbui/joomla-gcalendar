<?php

/**
* Google calendar upcoming events module
* @author allon
* @version $Revision: 2.0.0 $
**/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

// Include the helper functions only once
require_once (dirname(__FILE__).DS.'helper.php');
// Get data from helper class
$returnValue = modGcalendarUpcomingHelper::getCalendarItems($params);
$error = $returnValue[0];
$gcalendar_data = $returnValue[1];
require( JModuleHelper::getLayoutPath( 'mod_gcalendar_upcoming' ) );
?>