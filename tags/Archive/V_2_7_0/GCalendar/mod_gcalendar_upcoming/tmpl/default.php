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

defined( '_JEXEC' ) or die( 'Restricted access' );

$document = &JFactory::getDocument();
$document->addStyleSheet(JURI::base().'modules/mod_gcalendar_upcoming/tmpl/default.css');

$tmp = clone JComponentHelper::getParams('com_gcalendar');
$tmp->set('event_date_format', $params->get('date_format', 'm.d.Y'));
$tmp->set('event_time_format', $params->get('time_format', 'g:i a'));
$tmp->set('grouping', $params->get('output_grouping', ''));

// enable all params
$tmp->set('show_calendar_name', 1);
$tmp->set('show_event_title', 1);
$tmp->set('show_event_date', 1);
$tmp->set('show_event_attendees', 1);
$tmp->set('show_event_location', 1);
$tmp->set('show_event_location_map', 1);
$tmp->set('show_event_description', 1);
$tmp->set('show_event_author', 1);
$tmp->set('show_event_copy_info', 1);

$output = $params->get('output', '{{#events}}
{{#header}}<p style="clear: both;"><strong>{{header}}</strong></p>{{/header}}
<p style="clear: both;"/>
<div class="gc_up_mod_img">
	<div class="gc_up_mod_month_background" style="background-color: #{{calendarcolor}};"></div>
	<div class="gc_up_mod_month_text" style="color: #FFFFFF;">{{month}}</div>
	<div class="gc_up_mod_day" style="color: #{{calendarcolor}};">{{day}}</div>
</div>
<p>{{startDate}} {{startTime}} {{dateseparator}} {{endDate}} {{endTime}}<br/><a href="{{{backlink}}}">{{title}}</a></p>
<p style="clear: both;"/>
{{/events}}
{{^events}}
{{emptyText}}
{{/events}}');
echo GCalendarUtil::renderEvents($events, $output, $tmp);