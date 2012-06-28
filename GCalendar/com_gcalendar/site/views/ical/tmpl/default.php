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

defined('_JEXEC') or die();

include_once(JPATH_BASE.DS.'components'.DS.'com_gcalendar'.DS.'libraries'.DS.'ical'.DS.'iCalcreator.class.php');

$event = $this->event;

$config = array('unique_id' => $event->getGCalId());
$v = new vcalendar( $config );
$v->prodid = 'GCalendar';
$path = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_gcalendar'.DS.'gcalendar.xml';
if(file_exists($path)) {
	$manifest = simplexml_load_file($path);
	$v->version = $manifest->version;
}

$tz = GCalendarUtil::getComponentParameter('timezone');
$v->setProperty( 'method', 'PUBLISH' );
$v->setProperty( "x-wr-calname", $event->getParam('gcname'));
$v->setProperty( "X-WR-CALDESC", "" );
$v->setProperty( "X-WR-TIMEZONE", $tz);
$xprops = array( "X-LIC-LOCATION" => $tz);
if(version_compare(PHP_VERSION, '5.3.0') >= 0){
	iCalUtilityFunctions::createTimezone($v, $tz, $xprops);
}

$vevent = &$v->newComponent( 'vevent' );

if($event->getDayType() == GCalendar_Entry::SINGLE_WHOLE_DAY || $event->getDayType() == GCalendar_Entry::MULTIPLE_WHOLE_DAY) {
	$vevent->setProperty( 'dtstart', GCalendarUtil::formatDate('YmdHis', $event->getStartDate()));
	$vevent->setProperty( 'dtend', GCalendarUtil::formatDate('YmdHis', $event->getEndDate()));
} else {
	$vevent->setProperty( 'dtstart', GCalendarUtil::formatDate('Ymd', $event->getStartDate()));
	$vevent->setProperty( 'dtend', GCalendarUtil::formatDate('Ymd', $event->getEndDate()));
}
$vevent->setProperty( 'location', $event->getLocation() );
$vevent->setProperty( 'summary', $event->getTitle() );
$vevent->setProperty( 'description', $event->getContent());

// echo '<pre>'.$v->createCalendar().'</pre>';
$v->returnCalendar();