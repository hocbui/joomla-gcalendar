<?php

/**
* Google calendar overview module
* @author allon
* @version $Revision: 1.5.1 $
**/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );


// Include the syndicate functions only once
require_once( dirname(__FILE__).DS.'helper.php' );

$calendar = modGCalendarHelper::getCalendarUrl( $params );
require( JModuleHelper::getLayoutPath( 'mod_gcalendar' ) );
?>
