<?php
/**
 * @package		com_dpcalendar
 * @author		Digital Peak http://www.digital-peak.com
 * @copyright	Copyright (C) 2012 Digital Peak, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
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