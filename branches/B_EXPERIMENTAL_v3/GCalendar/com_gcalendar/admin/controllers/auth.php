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
 * @since 3.0.0
 */

defined('_JEXEC') or die();

jimport('joomla.application.component.controllerform');

class GCalendarControllerAuth extends JControllerForm {

	public function request() {
		JFactory::getSession()->set('gcalendar_authorized_id', JRequest::getInt('id'));

		$client = GCalendarZendHelper::getClient();
		$service = new apiCalendarService($client);

		$client->authenticate();
	}

	public function store() {
		$client = GCalendarZendHelper::getClient();
		$service = new apiCalendarService($client);
		$client->authenticate();

		$id = JFactory::getSession()->set('gcalendar_authorized_id');

		$calendar = GCalendarDBUtil::getCalendar($id);
		if($calendar == null) {
			return;
		}
		$calendar->token = json_decode($client->getAccessToken())->refresh_token;
		$calendar->store();

		$this->setRedirect(JRoute::_('index.php?option=com_gcalendar&task=gcalendar.edit&id='.$id));
	}
}