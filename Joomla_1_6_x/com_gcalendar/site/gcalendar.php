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
 * @copyright 2007-2011 Allon Moritz
 * @since 2.2.0
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

ini_set('display_errors',1);
error_reporting(E_ALL);

require_once (JPATH_ADMINISTRATOR.DS.'components'.DS.'com_gcalendar'.DS.'util.php');

ini_set("include_path", ini_get("include_path") . PATH_SEPARATOR . JPATH_ADMINISTRATOR . DS . 'components'. DS .'com_gcalendar' . DS . 'libraries');
if(!class_exists('Zend_Loader')){
	require_once 'Zend/Loader.php';
}
require_once 'GCalendar'.DS.'GCalendarZendHelper.php';

require_once (JPATH_COMPONENT.DS.'controller.php');

$controller = new GCalendarController( );
$controller->execute( JRequest::getCmd('task'));
$controller->redirect();
?>