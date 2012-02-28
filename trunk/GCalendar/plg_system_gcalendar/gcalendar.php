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
 * @since 2.6.3
 */
defined('_JEXEC') or die();

jimport('joomla.plugin.plugin');

class plgSystemGCalendar extends JPlugin {

	function onAfterRoute() {
		if($this->params->get('load-jquery', 1) != 1 || JFactory::getDocument()->getType() != 'html'){
			return;
		}
		JHTML::_(' behavior.mootools');

		JFactory::getDocument()->addScript("/GCJQLIB");
		JFactory::getDocument()->addScriptDeclaration("GCJQNOCONFLICT");

		JFactory::getApplication()->set('jQuery', true);
		JFactory::getApplication()->set('jquery', true);
	}

	function onAfterRender() {
		if($this->params->get('load-jquery', 1) != 1 || JFactory::getDocument()->getType() != 'html'){
			return;
		}

		$body =& JResponse::getBody();

		$body = preg_replace("#([\\\/a-zA-Z0-9_:\.-]*)jquery([0-9\.-]|min|pack)*?.js#", "", $body);
		$body = preg_replace("#([\\\/a-zA-Z0-9_:\.-]*)jquery[.-]noconflict\.js#", "", $body);
		$body = str_ireplace('<script src="" type="text/javascript"></script>', "", $body);
		$body = preg_replace("#jQuery\.noConflict\(\);#", "", $body);
		$body = preg_replace("#/GCJQLIB#", JURI::root().'/components/com_gcalendar/libraries/jquery/jquery.min.js', $body);
		$body = preg_replace("#GCJQNOCONFLICT#", "jQuery.noConflict();", $body);

		JResponse::setBody($body);
	}
}