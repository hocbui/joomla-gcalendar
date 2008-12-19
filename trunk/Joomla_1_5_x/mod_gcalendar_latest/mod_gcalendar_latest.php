<?php

/**
* Google calendar latest events module
* @author allon
* @version $Revision: 2.0.0 $
**/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

// Include the helper functions only once
require_once (dirname(__FILE__).DS.'helper.php');

if(!class_exists('SimplePie')){
	//include Simple Pie processor class
	require_once (JPATH_SITE.DS.'libraries'.DS.'simplepie'.DS.'simplepie.php');
}

if(!class_exists('SimplePie_GCalendar')){
	//include Simple Pie processor class
	require_once (JPATH_SITE.DS.'modules'.DS.'mod_gcalendar_latest'.DS.'tmpl'.DS.'simplepie-gcalendar.php');
}

// Get data from helper class
$returnValue = modGcalendarLatestHelper::getCalendarItems($params);
$error = $returnValue[0];
$gcalendar_data = $returnValue[1];
require( JModuleHelper::getLayoutPath( 'mod_gcalendar_latest' ) );
?>