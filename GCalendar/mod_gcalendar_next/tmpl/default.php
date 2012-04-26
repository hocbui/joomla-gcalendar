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
 * @author Eric Horne
 * @copyright 2009-2011 Eric Horne
 * @since 2.2.0
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

if(!empty($error)){
	echo $error;
	return;
}

$targetDate = $gcalendar_item->getStartDate();
$now = false;
if ($targetDate < time()) {
	# Countdown to end of event, not currently implemented
	#$targetDate = $gcalendar_item->get_end_date();
	$now = true;
}

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

$output = $params->get('output', '{{#events}}<span class="countdown_row">{y<}<span class="countdown_section"><span class="countdown_amount">{yn}</span><br/>{yl}</span>{y>}{o<}<span class="countdown_section"><span class="countdown_amount">{on}</span><br/>{ol}</span>{o>}{w<}<span class="countdown_section"><span class="countdown_amount">{wn}</span><br/>{wl}</span>{w>}{d<}<span class="countdown_section"><span class="countdown_amount">{dn}</span><br/>{dl}</span>{d>}{h<}<span class="countdown_section"><span class="countdown_amount">{hn}</span><br/>{hl}</span>{h>}{m<}<span class="countdown_section"><span class="countdown_amount">{mn}</span><br/>{ml}</span>{m>}{s<}<span class="countdown_section"><span class="countdown_amount">{sn}</span><br/>{sl}</span>{s>}<div style="clear:both"><p><a href="{{{backlink}}}">{{title}}</a><br/>{{{description}}}</p></div></span>{{/events}}{{^events}}{{emptyText}}{{/events}}');
$layout = str_replace("\n", "", GCalendarUtil::renderEvents(array($gcalendar_item), $output, $tmp));

$output = $params->get('output_now', '{{#events}}<p>Event happening now:<br/>{{date}}<br/><a href="{{{backlink}}}">{{title}}</a>{{#maplink}}<br/>Join us at [<a href="{{{maplink}}}" target="_blank">map</a>]{{/maplink}}</p>{{/events}}{{^events}}{{emptyText}}{{/events}}');
$expiryText = str_replace("\n", "", GCalendarUtil::renderEvents(array($gcalendar_item), $output, $tmp));
$class = "countdown";
$class .= ($now) ? "now" : "";
$objid = "countdown-" . $module->id;

$document = JFactory::getDocument();
$document->addScript(JURI::base(). 'components/com_gcalendar/libraries/jquery/ext/jquery.countdown.min.js');
$document->addStyleSheet(JURI::base(). 'components/com_gcalendar/libraries/jquery/ext/jquery.countdown.css');


$calCode = "// <![CDATA[ \n";
$calCode .= "	jQuery(document).ready(function() {\n";
$calCode .= "	var targetDate; \n";
$calCode .= "	targetDate = new Date(".GCalendarUtil::formatDate("Y, m-1, d, H, i, 0", $targetDate).");\n";
$calCode .= "	jQuery('#".$objid."').countdown({until: targetDate, \n";
$calCode .= "				       description: '".str_replace('\'', '\\\'', $gcalendar_item->getTitle())."', \n";
$calCode .= " 				       layout: '".str_replace('\'', '\\\'',$layout)."', \n";
$calCode .= "				       alwaysExpire: true, expiryText: '".str_replace('\'', '\\\'',$expiryText)."', \n";
$calCode .= "				       ".$params->get('style_parameters', "format: 'dHMS'")."});\n";
$calCode .= "});\n";
$calCode .= "// ]]>\n";
$document->addScriptDeclaration($calCode);
?>
<div class="gcalendar_next">
	<div id="<?php echo $objid;?>" class="<?php echo $class;?>"><?php echo JText::_("MOD_GCALENDAR_NEXT_JSERR");?></div>
</div>