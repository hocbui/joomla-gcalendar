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

defined('_JEXEC') or die('Restricted access');

require_once(JPATH_COMPONENT.DS.'libraries'.DS.'mustache'.DS.'Mustache.php');

$dispatcher = JDispatcher::getInstance();
JPluginHelper::importPlugin('gcalendar');

$document = JFactory::getDocument();
$document->addStyleSheet(JURI::base(). 'components/com_gcalendar/views/gcalendar/tmpl/gcalendar.css' );
$document->addStyleSheet(JURI::base().'components/com_gcalendar/views/event/tmpl/default.css');
$document->addScript(JURI::base().'components/com_gcalendar/views/event/tmpl/default.js');

$event = $this->event;

$m = new Mustache;

$variables = array();
$variables['event'] = $event != null;

$itemID = GCalendarUtil::getItemId(JRequest::getVar('gcid', null));
if(!empty($itemID) && JRequest::getVar('tmpl', null) != 'component' && $event != null){
	$component = JComponentHelper::getComponent('com_gcalendar');
	$menu = JFactory::getApplication()->getMenu();
	$item = $menu->getItem($itemID);
	if($item !=null){
		$backLinkView = $item->query['view'];
		$dateHash = '';
		if($backLinkView == 'gcalendar'){
			$day = strftime('%d', $event->getStartDate());
			$month = strftime('%m', $event->getStartDate());
			$year = strftime('%Y', $event->getStartDate());
			$dateHash = '#year='.$year.'&month='.$month.'&day='.$day;
		}
	}
	$variables['calendarLink'] = JRoute::_('index.php?option=com_gcalendar&Itemid='.$itemID.$dateHash);
}

// the date formats from http://php.net/date
$dateformat = GCalendarUtil::getComponentParameter('event_date_format', 'm.d.Y');
$timeformat = GCalendarUtil::getComponentParameter('event_time_format', 'g:i a');

// These are the dates we'll display
$startDate = GCalendarUtil::formatDate($dateformat, $event->getStartDate());
$startTime = GCalendarUtil::formatDate($timeformat, $event->getStartDate());
$endDate = GCalendarUtil::formatDate($dateformat, $event->getEndDate());
$endTime = GCalendarUtil::formatDate($timeformat, $event->getEndDate());
$dateSeparator = '-';

$timeString = $startTime.' '.$startDate.' '.$dateSeparator.' '.$endTime.' '.$endDate;
$copyDateTimeFormat = 'Ymd';
switch($event->getDayType()){
	case GCalendar_Entry::SINGLE_WHOLE_DAY:
		$timeString = $startDate;
		$copyDateTimeFormat = 'Ymd';
		break;
	case GCalendar_Entry::SINGLE_PART_DAY:
		$timeString = $startDate.' '.$startTime.' '.$dateSeparator.' '.$endTime;
		$copyDateTimeFormat = 'Ymd\THis';
		break;
	case GCalendar_Entry::MULTIPLE_WHOLE_DAY:
		$SECSINDAY=86400;
		$endDate = GCalendarUtil::formatDate($dateformat, $event->getEndDate()-$SECSINDAY);
		$timeString = $startDate.' '.$dateSeparator.' '.$endDate;
		$copyDateTimeFormat = 'Ymd';
		break;
	case GCalendar_Entry::MULTIPLE_PART_DAY:
		$timeString = $startTime.' '.$startDate.' '.$dateSeparator.' '.$endTime.' '.$endDate;
		$copyDateTimeFormat = 'Ymd\THis';
		break;
}
$variables['calendarName'] = GCalendarUtil::getComponentParameter('show_calendar_name', 1) == 1 ? $event->getParam('gcname') : null;
$variables['title'] = GCalendarUtil::getComponentParameter('show_event_title', 1) == 1 ? (string)$event->getTitle() : null;
$variables['date'] = GCalendarUtil::getComponentParameter('show_event_date', 1) == 1 ? $timeString : null;

if(GCalendarUtil::getComponentParameter('show_event_attendees', 2) == 1 && count($event->getWho()) > 0){
	$variables['hasAttendees'] = true;
	$variables['attendees'] = array();
	foreach ($event->getWho() as $a) {
		$variables['attendees'][] = array('name' => (string)$a->getValueString(), 'email' =>  base64_encode(str_replace('@','#',$a->getEmail())));
	}
}
$variables['location'] = GCalendarUtil::getComponentParameter('show_event_location', 1) == 1 ? $event->getLocation() : null;
$variables['map'] = GCalendarUtil::getComponentParameter('show_event_location_map', 1) == 1 ? urlencode($event->getLocation()) : null;

if(GCalendarUtil::getComponentParameter('show_event_description', 1) == 1) {
	$variables['description'] = $event->getContent();
	if(GCalendarUtil::getComponentParameter('event_description_format', 1) == 1) {
		$variables['description'] = preg_replace("@(src|href)=\"https?://@i",'\\1="',$event->getContent());
		$variables['description'] = nl2br(preg_replace("@(((f|ht)tp:\/\/)[^\"\'\>\s]+)@",'<a href="\\1" target="_blank">\\1</a>', $variables['description']));
	}
}
if(GCalendarUtil::getComponentParameter('show_event_author', 2) == 1){
	$variables['hasAuthor'] = true;
	$variables['author'] = array();
	foreach ($event->getAuthor() as $author) {
		$variables['author'][] = array('name' => (string)$author->getName(), 'email' =>  base64_encode(str_replace('@','#',$author->getEmail())));
	}
}

if(GCalendarUtil::getComponentParameter('show_event_copy_info', 1) == 1){
	$variables['copyGoogleUrl'] = 'http://www.google.com/calendar/render?action=TEMPLATE&amp;text='.urlencode($event->getTitle());
	$variables['copyGoogleUrl'] .= '&amp;dates='.GCalendarUtil::formatDate($copyDateTimeFormat, $event->getStartDate()).'%2F'.GCalendarUtil::formatDate($copyDateTimeFormat, $event->getEndDate());
	$variables['copyGoogleUrl'] .= '&amp;location='.urlencode($event->getLocation());
	$variables['copyGoogleUrl'] .= '&amp;details='.urlencode($event->getContent());
	$variables['copyGoogleUrl'] .= '&amp;hl='.GCalendarUtil::getFrLanguage().'&amp;ctz='.GCalendarUtil::getComponentParameter('timezone');
	$variables['copyGoogleUrl'] .= '&amp;sf=true&amp;output=xml';

	$ical_timeString_start =  $startTime.' '.$startDate;
	$ical_timeString_start = strtotime($ical_timeString_start);
	$ical_timeString_end =  $endTime.' '.$endDate;
	$ical_timeString_end = strtotime($ical_timeString_end);
	$loc = $event->getLocation();
	$variables['copyOutlookUrl'] = JRoute::_("index.php?option=com_gcalendar&view=ical&format=raw&start=".$ical_timeString_start."&end=".$ical_timeString_end."&title=".$event->getTitle()."&location=".$loc);
}

$variables['calendarLinkLabel'] = JText::_('COM_GCALENDAR_EVENT_VIEW_CALENDAR_BACK_LINK');
$variables['calendarNameLabel'] = JText::_('COM_GCALENDAR_EVENT_VIEW_CALENDAR_NAME');
$variables['titleLabel'] = JText::_('COM_GCALENDAR_EVENT_VIEW_EVENT_TITLE');
$variables['dateLabel'] = JText::_('COM_GCALENDAR_EVENT_VIEW_WHEN');
$variables['attendeesLabel'] = JText::_('COM_GCALENDAR_EVENT_VIEW_ATTENDEES');
$variables['locationLabel'] = JText::_('COM_GCALENDAR_EVENT_VIEW_LOCATION');
$variables['descriptionLabel'] = JText::_('COM_GCALENDAR_EVENT_VIEW_DESCRIPTION');
$variables['authorLabel'] = JText::_('COM_GCALENDAR_EVENT_VIEW_AUTHOR');
$variables['copyLabel'] = JText::_('COM_GCALENDAR_EVENT_VIEW_COPY');
$variables['copyGoogleLabel'] = JText::_('COM_GCALENDAR_EVENT_VIEW_COPY_TO_MY_CALENDAR');
$variables['copyOutlookLabel'] = JText::_('COM_GCALENDAR_EVENT_VIEW_COPY_TO_MY_CALENDAR_ICS');
$variables['language'] = substr(GCalendarUtil::getFrLanguage(),0,2);

$content = '
{{#calendarLink}}
<table class="gcalendar-table">
	<tr>
		<td valign="middle">
			<a href="{{calendarLink}}">
				<img id="prevBtn_img" height="16" border="0" width="16" alt="backlink" src="media/com_gcalendar/images/back.png"/>
			</a>
		</td>
		<td valign="middle">
			<a href="{{calendarLink}}">{{{calendarLinkLabel}}}</a>
		</td>
	</tr>
</table>
{{/calendarLink}}
{{#event}}
<div class="event_content">
<table id="content_table">
	<tr><td colspan="2">{{#pluginsBefore}} {{{.}}} {{/pluginsBefore}}</td></tr>
	{{#calendarName}}
	<tr><td class="event_content_key">{{calendarNameLabel}}: </td><td>{{calendarName}}</td></tr>
	{{/calendarName}}
	{{#title}}
	<tr><td class="event_content_key">{{titleLabel}}: </td><td>{{title}}</td></tr>
	{{/title}}
	{{#date}}
	<tr><td class="event_content_key">{{dateLabel}}: </td><td>{{date}}</td></tr>
	{{/date}}
	{{#hasAttendees}}
	<tr>
		<td class="event_content_key">{{attendeesLabel}}: </td>
		<td>
			{{#attendees}}{{name}} <a href="javascript:sdafgkl437jeeee(\'{{email}}\')"><img height="11" border="0" width="16" alt="email" src="media/com_gcalendar/images/mail.png"/></a>, {{/attendees}}
		</td>
	</tr>
	{{/hasAttendees}}
	{{#location}}
	<tr><td class="event_content_key">{{locationLabel}}: </td><td>{{location}}</td></tr>
	{{/location}}
	{{#map}}
	<tr><td colspan="2"><iframe width="100%" height="300px" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="http://maps.google.com/maps?q={{map}}&hl={{language}}&output=embed"></iframe></td></tr>
	{{/map}}
	{{#description}}
	<tr><td class="event_content_key">{{descriptionLabel}}: </td><td>{{{description}}}</td></tr>
	{{/description}}
	{{#hasAuthor}}
	<tr>
		<td class="event_content_key">{{authorLabel}}: </td>
		<td>
			{{#author}}{{name}} <a href="javascript:sdafgkl437jeeee(\'{{email}}\')"><img height="11" border="0" width="16" alt="email" src="media/com_gcalendar/images/mail.png"/></a>, {{/author}}
		</td>
	</tr>
	{{/hasAuthor}}
	{{#copyGoogleUrl}}
	<tr>
		<td class="event_content_key">{{copyLabel}}: </td>
		<td>
			<a target="_blank" href="{{copyGoogleUrl}}">{{copyGoogleLabel}}</a>
		</td>
	</tr>
	{{/copyGoogleUrl}}
	{{#copyOutlookUrl}}
	<tr>
		<td class="event_content_key"></td>
		<td>
			<a target="_blank" href="{{copyOutlookUrl}}">{{copyOutlookLabel}}</a>
		</td>
	</tr>
	{{/copyOutlookUrl}}
	<tr><td colspan="2">{{#pluginsAfter}} {{{.}}} {{/pluginsAfter}}</td></tr>
</table>
</div>
{{/event}}
{{^event}}
no event found
{{/event}}
';

$variables['pluginsBefore'] = array();
$variables['pluginsAfter'] = array();
 $dispatcher->trigger('onBeforeDisplayEvent', array($event,  &$content, &$variables));
// $dispatcher->trigger('onAfterDisplayEvent', array($event,  &$content, &$variables));

try{
	echo $m->render($content, $variables);
}catch(Exception $e){
	echo $e->getMessage();
}
?>
<div style="text-align:center;margin-top:10px" id="gcalendar_powered"><a href="http://g4j.laoneo.net/">Powered by GCalendar</a></div>