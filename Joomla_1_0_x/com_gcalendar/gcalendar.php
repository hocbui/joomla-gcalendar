<?php

/**
* Google calendar component
* @author allon
* @version $Revision: 2.0.0 $
**/

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
			$params->set('htmlUrl', $result->htmlUrl);
		}
		
		$mainframe->SetPageTitle($menu->name);
		HTML_gcalendar :: displayCalendar($params, $menu);
	}else{
		echo _GCALENDAR_COMPONENT_NO_CALENDAR;
	}
}
?>