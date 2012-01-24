<?php
/**
 * GCalendar is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * GCalendar is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with GCalendar.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * @author Allon Moritz
 * @copyright 2007-2009 Allon Moritz
 * @version $Revision: 2.0.1 $
 */

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
	require_once (JPATH_SITE.DS.'modules'.DS.'mod_gcalendar_upcoming'.DS.'tmpl'.DS.'simplepie-gcalendar.php');
}

// Get data from helper class
$returnValue = modGcalendarUpcomingHelper::getCalendarItems($params);
$error = $returnValue[0];
$gcalendar_data = $returnValue[1];
require( JModuleHelper::getLayoutPath( 'mod_gcalendar_upcoming' ) );
?>