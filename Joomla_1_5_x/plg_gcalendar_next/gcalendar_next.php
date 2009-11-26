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
 * This code was based on Allon Moritz great work in the companion
 * upcoming module. 
 * 
 * @author Eric Horne
 * @copyright 2009 Eric Horne 
 * @version $Revision: 1.0.0 $
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.plugin.plugin' );

require_once (JPATH_ADMINISTRATOR.DS.'components'.DS.'com_gcalendar'.DS.'nextevents.php');

/**
 * Constructor
 *
 * For php4 compatability we must not use the __constructor as a constructor for plugins
 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
 * This causes problems with cross-referencing necessary for the observer design pattern.
 *
 * @param plgContentEmbedReadMore $subject The object to observe
 * @param plgContentEmbedReadMore $params  The object that holds the plugin parameters
 * @since 1.5
 */

class plgContentgcalendar_next extends JPlugin {

	function plgContentgcalendar_next(&$subject, $params) {
		parent::__construct($subject, $params);
       	}

	function onPrepareContent( &$article, &$params ) {
		global $mainframe;

		if (JRequest::getCmd('option') != 'com_content') return;
		if (!$article->text) return;

		$text = preg_replace_callback('/{gcalnext\s+(.*?)}/', array($this, 'embedEvent'), $article->text);
		if ($text) {
			$article->text = $text;
		}
	}

	function embedEvent($gcalnext) {
		$text = $gcalnext[1];

		$matches = Array();
		preg_match_all('/\[\$\s*(.*?)\s*\$\]/', $text, $matches);
		$localparams = Array();

		foreach ($matches[1] as $match) {
			$kv = explode(' ', $match, 2);
			if (count($kv) == 1) {
				$kv[1] = "";
			}	
			$localparams[$kv[0]] = $kv[1];
		}

		$helper = new GCalendarNextHelper($this->params, $localparams);
		if (!$helper->event) {
			return $this->params->get('no_event');
		}

		return $helper->replace($text);
	}

	function replaceVar($var) {


	}
}

class GCalendarNextHelper {

	var $event = null;
	var $params;

	function GCalendarNextHelper($params, $localparams) {
		$this->params = $params;

		foreach ($localparams as $key => $value) {
			$this->params->set($key, $value);
		}


		$gcalnext = new GCalendarNext($this->params);
		$events = $gcalnext->getCalendarItems();
		if (count($events) > 0) {
			$this->event = $events[0];
		}
	}

	function replace($text) {
		return preg_replace_callback('/\[\$\s*(.*?)\s*\$\]/', array($this, 'replaceSingle'), $text);
	}

	function replaceSingle($val) {

		$kv = explode(' ', $val[1], 2);
		if (count($kv) == 1) {
			$kv[1] = "";
		}	
		if (is_callable(array($this, $kv[0]))) {
			return call_user_func(array($this, $kv[0]), $kv[1]);
		}

		return "";
	}

	function date($format, $time) {
		return strftime($format, $time);
	}

	function start($param) {
		return $this->date($param, $this->event->get_start_date());
	}

	function finish($param) {
		return $this->date($param, $this->event->get_end_date());
	}

	function title($param) {
		return $this->event->get_title();
	}

	function maplink($param) {
		return '<a class="gcalendar_location_link" href="' . $this->maphref($param) . '">' . $this->location($param) . '</a>';
	}

	function maphref($param) {
		return 'http://maps.google.com/?q=' . urlencode($this->location($param));
	}

	function location($param) {
		return $this->event->get_location();
	}

}
?>
