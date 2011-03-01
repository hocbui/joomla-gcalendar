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

defined('_JEXEC') or die('Restricted access');

$mainframe = &JFactory::getApplication();
$absolute_path = $mainframe->getCfg( 'absolute_path' );
ini_set("include_path", ini_get("include_path") . PATH_SEPARATOR . JPATH_COMPONENT . DS . 'libraries');

require_once('Zend' . DS . 'Loader.php');
Zend_Loader::loadClass('Zend_Gdata_AuthSub');
Zend_Loader::loadClass('Zend_Gdata_HttpClient');
Zend_Loader::loadClass('Zend_Gdata_Calendar');

$params = &JComponentHelper::getParams( 'com_gcalendar' );
$domain = $params->get('google_apps_domain');

$u = JFactory::getURI();
$next = JRoute::_( $u->toString().'?option=com_gcalendar&task='.JRequest::getCmd('nextTask'));
$scope = 'http://www.google.com/calendar/feeds/';
$session = true;
$secure = false;
$hd = '';
if(!empty($domain))
$hd = '&hd='.$domain;
$authSubUrl = Zend_Gdata_AuthSub::getAuthSubTokenUri($next, $scope, $secure,
$session,Zend_Gdata_AuthSub::AUTHSUB_REQUEST_URI);
?>
<p>If the imported calendars are not be shared with the public, use the <b>standard authentication</b> 
otherwise use the <b>google safe authentication</b>.</p>
<fieldset><legend>Google safe authentication</legend>
<a href="<?php echo $authSubUrl.$hd;?>">Please Login to access the calendar data.</a>
</fieldset>
<hr/>
<fieldset><legend>Standard authentication</legend>
<form action="<?php echo JRoute::_( $u->toString().'?option=com_gcalendar&view=import');?>" method="post">
	<table>
	<tr><td>Username:</td><td><input type="text" name="user" size="100"/></td></tr>
	<tr><td>Password:</td><td><input type="password"name="pass" size="100"/></td></tr>
	<tr><td><input type="submit" value="Login"/></td><td></td></tr>
	</table>
</form>
</fieldset>