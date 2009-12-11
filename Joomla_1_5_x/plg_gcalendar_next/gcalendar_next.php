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

		$text = preg_replace_callback('/{gcalnext\s+(.*?)\s*}/', array($this, 'embedEvent'), $article->text);

		if ($text) {
			$article->text = $text;
		}
	}

	function embedEvent($gcalnext) {
		$embedded_str = $gcalnext[1];

		$helper = new GCalendarKeywordsHelper($this->params, $embedded_str);

		if (!$helper->event()) {
			return $this->params->get('no_event');
		}

		return $helper->replace();
	}
}

class PluginKeywordsHelper {

	var $params;
	var $argre;
	var $plgText;
	var $dataobj;
	var $plgParams = Array();
	var $fmtParams = Array();

	function PluginKeywordsHelper($params, $plgText, $argre = '/(?:\[\$)\s*(.*?)\s*(?:\$\])/') {
		$this->params = $params;
		$this->plgText = $plgText;
		$this->argre = $argre;

		$matches = Array();
		preg_match_all($this->argre, $this->plgText, $matches);

		foreach ($matches[1] as $match) {
			list($key, $value) = explode(' ', $match, 2) + Array("", "");
			$value = str_replace("\\",'',$value);
			if  (strpos($key, '+') === 0) {
				$key = substr($key, 1);
				$this->params->set($key, $value);
				$this->plgParams[$key] = $value;
			}
			else {
				$this->fmtParams[$key] = $value;
			}
		}
		
		$this->dataobj = $this->setDataObj();
	}

	function setDataObj() {
		return "";
	}
	
	function dataobj() {
		return $this->dataobj;
	}

	function plgText() {
		return $plgText;
	}

	function replace() {
		return preg_replace_callback($this->argre, array($this, 'replaceSingle'), $this->plgText);
	}

	function replaceSingle($val) {
		list($func, $arg) = explode(' ', $val[1], 2) + Array("", "");

		if (is_callable(array($this, $func))) {
			return call_user_func(array($this, $func), $arg);
		}

		return "";
	}
}

class GCalendarKeywordsHelper extends PluginKeywordsHelper {
	
	function replace() {
		$start = $this->dataobj->get_start_date();
		$end = $this->dataobj->get_end_date();
		$now = time();
		$start_soon = date($this->params->get('start_soon', '-4 hours', $start));
		$end_soon = date($this->params->get('end_soon', '-2 hours', $end));
		$plgText = preg_replace($this->argre, "", $this->plgText);
		$plgText = preg_replace('/\s+/', "", $plgText);

		
		if ($plgText) {
			$this->params->set('output', $this->plgText);
		}
		
			if ($end <= $now) { // AND it hasn't ended		
				if ($start >= $now) { // If it has started
					$text = $this->params->get('output_now');
				}
				elseif ($start_soon >= $now) {
					$text = $this->params->get('output_start_soon', 'starting soon');
				}
				elseif ($end_soon >= $now ) {
					$text = $this->params->get('output_end_soon', 'ending soon');
				}
			}

		if ($text == "" or $text == null) {
			$text = $this->params->get('output');
		}

		print "using ($text) \n";
		
		return preg_replace_callback($this->argre, array($this, 'replaceSingle'), $text);
	}
	

	function setDataObj() {
		$gcalnext = new GCalendarNext($this->params);
		$events = $gcalnext->getCalendarItems();
		$event = null;
		if (count($events) > 0) {
			$event = $events[0];
		}	
		return $event;	
	}
	
	function event() {
		return $this->dataobj();
	}


	function date($format, $time) {
		if ($format == "") {
			$format = $this->params->get("dateformat", "%B %d, %Y @ %I:%M%P");
		}
		return strftime($format, $time);
	}

	function startdate($param) {
		return $this->start($param);
	}

	function start($param) {
		return $this->date($param, $this->event()->get_start_date());
	}

	function finishdate($param) {
		return $this->finish($param);
	}

	function finish($param) {
		$ftime = $this->event()->get_end_date();
		$daytype = $this->event()->get_day_type();
		if ($daytype == $this->event()->MULTIPLE_WHOLE_DAY) {
			$ftime = $ftime - 1; // to account for midnight
		}

		return $this->date($param, $ftime);
	}

	function range($param) {
		$event = $this->event();

		if ($param) {
			$fmt = $param;
		}
		else {
			switch($event->get_day_type()) {
			case $event->SINGLE_WHOLE_DAY:
				$fmt = $this->params->get("only-whole_day", "%A, %B %d, %Y all day");
				break;
			case $event->SINGLE_PART_DAY:
				$fmt = $this->params->get("only-part_day", "%A, %B %d, %Y %I:%M%P until %%I:%%M%%P");
				break;
			case $event->MULTIPLE_WHOLE_DAY:
				$fmt = $this->params->get("multi-whole_day", "%B %d - %%d, %%Y all day");
				break;
			case $event->MULTIPLE_PART_DAY:
				$fmt = $this->params->get("multi-part_day", "%A, %B %d, %Y %I:%M%P until %%A, %%B %%d, %%Y %%I:%%M%%P");
				break;			
			}
		}
		
		$str = $this->start($fmt);
		$str = $this->finish($str);

		return $str;
	}

	function duration($param) {

	}

	function title($param) {
		return $this->event()->get_title();
	}

	function description($param) {
		$desc = $this->event()->get_description();
		return eregi_replace('(((f|ht){1}tp://)[-a-zA-Z0-9@:%_\+.~#?,//=&;]+)','<a href="\\1">\\1</a>', $desc);
	}

	function backlink($param) {
		$gcid = $this->event()->get_feed()->get('gcid');
		$itemID = GCalendarUtil::getItemID($gcid);
		if (!empty($itemID)) $itemID = '&Itemid='.$itemID;
		return JRoute::_('index.php?option=com_gcalendar&view=event()&event()ID='.$this->event()->get_id().'&gcid='.$gcid.$itemID);
	}

	function link($param) {
		$timezone = GCalendarUtil::getComponentParameter('timezone');
		if ($timezone == ''){
			$timezone = $feed->get_timezone();
		}
		
		return $this->event()->get_link() . '&ctz=' . $timezone;
	}

	function maplink($param) {
		return '<a class="gcalendar_location_link" href="' . $this->maphref($param) . '">' . $this->location($param) . '</a>';
	}

	function maphref($param) {
		return 'http://maps.google.com/?q=' . urlencode($this->location($param));
	}

	function location($param) {
		return $this->where($param);
	}

	function where($param) {
		return $this->event()->get_location();
	}

	function calendarname($param) {
		return $this->event()->get_feed()->get('gcname');
	}

	function calendarcolor($param) {
		return $this->event()->get_feed()->get('gccolor');
	}
}
?>
