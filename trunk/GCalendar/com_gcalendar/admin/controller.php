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

jimport('joomla.application.component.controller');

/**
 * GCalendar Component Controller
 *
 */
class GCalendarController extends JController
{

	function display()
	{
		JRequest::setVar('view', JRequest::getCmd('view', 'cpanel'));
		parent::display();
		$view = JRequest::getVar('view', 'cpanel');

		JSubMenuHelper::addEntry(JText::_('COM_GCALENDAR_SUBMENU_CPANEL'), 'index.php?option=com_gcalendar&view=cpanel', $view == 'cpanel');
		JSubMenuHelper::addEntry(JText::_('COM_GCALENDAR_SUBMENU_GCALENDARS'), 'index.php?option=com_gcalendar&view=gcalendars', $view == 'gcalendars');
		JSubMenuHelper::addEntry(JText::_('COM_GCALENDAR_SUBMENU_TOOLS'), 'index.php?option=com_gcalendar&view=tools', $view == 'tools');
		JSubMenuHelper::addEntry(JText::_('COM_GCALENDAR_SUBMENU_SUPPORT'), 'index.php?option=com_gcalendar&view=support', $view == 'support');
		$document = JFactory::getDocument();
		$document->addStyleDeclaration('.icon-48-calendar {background-image: url(../media/com_gcalendar/images/48-calendar.png);background-repeat: no-repeat;}');
	}

	function import(){
		if($this->isLoggedIn()){
			JRequest::setVar( 'view', 'import'  );
		}else{
			JRequest::setVar( 'nextTask', 'import'  );
			JRequest::setVar( 'view', 'login'  );
		}
		JRequest::setVar('hidemainmenu', 0);

		$this->display();
	}

	function isLoggedIn(){
		$user = JRequest::getVar('user', null);
		$pass = JRequest::getVar('pass', null);
		if(!empty($user)){
			Zend_Loader::loadClass('Zend_Gdata_AuthSub');
			Zend_Loader::loadClass('Zend_Gdata_HttpClient');
			Zend_Loader::loadClass('Zend_Gdata_ClientLogin');
			Zend_Loader::loadClass('Zend_Gdata_Calendar');
			
			$client = new Zend_Gdata_HttpClient();

			if (!in_array('ssl',stream_get_transports()) && function_exists('curl_init')  )
			$client->setConfig(array(
		        'strictredirects' => true,
		        'adapter' => 'Zend_Http_Client_Adapter_Curl',
		        'curloptions' => array(
					CURLOPT_FOLLOWLOCATION => true,
					CURLOPT_MAXREDIRS => 2,
					CURLOPT_SSL_VERIFYPEER => false,
					CURLOPT_COOKIEJAR => 'gcal_cookiejar.txt'
					)
				)
			);

			$client = Zend_Gdata_ClientLogin::getHttpClient($user, $pass, Zend_Gdata_Calendar::AUTH_SERVICE_NAME, $client);

			JRequest::setVar('authtoken', $client->getClientLoginToken());
		}

		if (JRequest::getVar('token', null) == null && JRequest::getVar('authtoken', null) == null) {
			return FALSE;
		}
		return TRUE;
	}

}
?>
