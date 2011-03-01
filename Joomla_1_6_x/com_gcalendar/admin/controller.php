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
class GCalendarsController extends JController
{

	function display()
	{
		parent::display();
		$view = JRequest::getVar('view', 'gcalendars');

		JSubMenuHelper::addEntry(JText::_('COM_GCALENDAR_SUBMENU_GCALENDARS'), 'index.php?option=com_gcalendar', $view == 'gcalendars');
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
		global $_SESSION, $_GET;
		if (!isset($_SESSION['sessionToken']) && !isset($_GET['token'])
		&& !isset($_SESSION['authToken']) && !isset($_GET['authtoken']) ) {
			return FALSE;
		}
		return TRUE;
	}

}
?>
