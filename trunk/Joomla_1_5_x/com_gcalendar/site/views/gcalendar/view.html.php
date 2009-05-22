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
 * @version $Revision: 2.1.0 $
 */

jimport( 'joomla.application.component.view');

require_once (JPATH_ADMINISTRATOR.DS.'components'.DS.'com_gcalendar'.DS.'util.php');
require_once (JPATH_ADMINISTRATOR.DS.'components'.DS.'com_gcalendar'.DS.'libraries'.DS.'rss-calendar'.DS.'GCalendar.php');
require_once (JPATH_ADMINISTRATOR.DS.'components'.DS.'com_gcalendar'.DS.'libraries'.DS.'rss-calendar'.DS.'classes'.DS.'DefaultCalendarConfig.php');

/**
 * HTML View class for the GCalendar Component
 *
 */
class GCalendarViewGCalendar extends JView
{
	function display($tpl = null)
	{
		global $mainframe;

		$params = &$mainframe->getParams();
		$this->assignRef('params'  , $params);

		if(JRequest::getVar('gcids', null) != null){
			$calendarids = explode(',', JRequest::getVar('gcids', null));
			$model = &$this->getModel();
			$model->setState('gcids',$calendarids);
		}
		parent::display($tpl);
	}
}
?>
