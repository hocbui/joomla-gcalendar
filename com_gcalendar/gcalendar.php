<?php

/**
* Google calendar component
* @author allon
* @version $Revision: 1.2.0 $
**/

// no direct access
defined('_VALID_MOS') or die('Restricted access');

/** load the html drawing class */
require_once ($mainframe->getPath('front_html'));

showCalendar($option);

function showCalendar($option) {
	global $database, $Itemid, $mainframe, $mosConfig_lang;

	$menu = $mainframe->get('menu');
	$params = new mosParameters($menu->params);
	$params->def('back_button', $mainframe->getCfg('back_button'));
	$params->def('scrolling', 'auto');
	$params->def('page_title', '1');
	$params->def('pageclass_sfx', '');
	$params->def('header', $menu->name);
	$params->def('height', '500');
	$params->def('height_auto', '0');
	$params->def('width', '100%');
	$params->def('add', '1');
	$params->def('htmlUrl', mosGetParam($_REQUEST, 'page', ''));

	$name = $params->def('name', '');
	if ($params->get('htmlUrl', '') == '') {
		$database->setQuery("select id,htmlUrl from #__gcalendar where name='$name'");
		$results = $database->loadObjectList();
		foreach ($results as $result) {
			$params->set('htmlUrl', $result->htmlUrl);
		}

		$eventID = mosGetParam($_REQUEST, 'eventID', '');
		if ($eventID != '') {
			$p= parse_url($params->get('htmlUrl',''));
			$params->set('htmlUrl', $p['scheme'] . '://' . $p['host'] . '/calendar/event?eid=' . $eventID);
		}
	}

	// auto height control
	if ($params->def('height_auto')) {
		$row->load = 'onload="iFrameHeight()"';
	} else {
		$row->load = '';
	}

	$mainframe->SetPageTitle($menu->name);

	HTML_gcalendar :: displayCalendar($params, $menu);
}
?>