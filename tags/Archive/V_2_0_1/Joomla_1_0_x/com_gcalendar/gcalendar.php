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

// no direct access
defined('_VALID_MOS') or die('Restricted access');

/** load the html drawing class */
require_once ($mainframe->getPath('front_html'));

// Get the right language if it exists
if (file_exists($mosConfig_absolute_path."/components/com_gcalendar/languages/".$mosConfig_lang.".php")){
	include_once($mosConfig_absolute_path."/components/com_gcalendar/languages/".$mosConfig_lang.".php");
}else{
	include_once($mosConfig_absolute_path."/components/com_gcalendar/languages/english.php");
}

switch ( $task ) {
	case 'view':
		showCalendar($option);
		break;
	case 'event':
		showEvent($option);
		break;
	default:
		showCalendar($option);
		break;
}

function showEvent($option){
	global $database, $mainframe, $mosConfig_lang;

	$menu = $mainframe->get('menu');
	$params = new mosParameters($menu->params);
	$params->def('scrolling', 'auto');
	$params->def('pageclass_sfx', '');
	$params->def('height', '500');
	$params->def('width', '100%');

	//if we are called from a link in a gcalendar module
	$eventID = mosGetParam($_REQUEST, 'eventID', '');
	if (!empty($eventID)) {
		$query = "select id,htmlUrl from #__gcalendar where name='.$name.'";
		$database->setQuery($query);
		$results = $database->loadObjectList();
		if(!empty($reults)){
			foreach ($results as $result) {
				$params->set('htmlUrl', $result->htmlUrl);
			}
		}
		$htmlUrl = $params->get('htmlUrl','');
		if(empty($htmlUrl))
			$params->set('htmlUrl', 'http://www.google.com');
		$p= parse_url($params->get('htmlUrl',''));
		
		$timezone = mosGetParam($_REQUEST, 'ctz', '');
		$lg = _LANGUAGE;
		if(!empty($timezone))$timezone='&ctz='.$timezone;
		if(!empty($lg))$lg='&hl='.$lg;
		$params->set('htmlUrl', $p['scheme'].'://'.$p['host'].'/calendar/event?eid='.$eventID.$timezone.$lg);
	}

	HTML_gcalendar :: displayCalendar($params, $menu);
}


function showCalendar($option) {
	global $database, $mainframe, $mosConfig_lang;

	$menu = $mainframe->get('menu');
	$params = new mosParameters($menu->params);
	$params->def('scrolling', 'auto');
	$params->def('pageclass_sfx', '');
	$params->def('height', '500');
	$params->def('width', '100%');

	$name = $params->def('name', '');
	$query = "select id,htmlUrl from #__gcalendar where name='$name'";
	$database->setQuery($query);
	$results = $database->loadObjectList();
	if(!empty($results)){
		foreach ($results as $result) {
			if ($params->get('use_custom_css')) {
				$params->set('htmlUrl', 'components/com_gcalendar/googlecal/MyGoogleCal4.php?'.str_replace("http://www.google.com/calendar/embed?","",$result->htmlUrl));
			}else{
				$params->set('htmlUrl', $result->htmlUrl);
			}
		}
		
		$mainframe->SetPageTitle($menu->name);
		
		HTML_gcalendar :: displayCalendar($params, $menu);
	}else{
		echo _GCALENDAR_COMPONENT_NO_CALENDAR;
	}
}
?>