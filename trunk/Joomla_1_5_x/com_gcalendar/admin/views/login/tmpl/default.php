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

defined('_JEXEC') or die('Restricted access');

require_once 'Zend/Loader.php';
Zend_Loader::loadClass('Zend_Gdata_AuthSub');

$u = JFactory::getURI();
$next = JRoute::_( $u->toString().'?option=com_gcalendar&task='.JRequest::getVar('nextTask'));
$scope = 'http://www.google.com/calendar/feeds/';
$session = true;
$secure = false;
$authSubUrl = Zend_Gdata_AuthSub::getAuthSubTokenUri($next, $scope, $secure,
$session);
echo "<a href=\"{$authSubUrl}\">Please Login to access the calendar data.</a>";
?>
