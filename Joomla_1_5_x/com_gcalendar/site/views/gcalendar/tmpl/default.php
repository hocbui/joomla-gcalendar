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
 * @version $Revision: 2.1.1 $
 */

defined('_JEXEC') or die('Restricted access');
global $Itemid;
JHTML::_('behavior.mootools');
JHTML::_('behavior.modal');
GCalendarUtil::loadJQuery();
$document = &JFactory::getDocument();
$document->addScript(JURI::base(). 'administrator/components/com_gcalendar/libraries/fullcalendar/fullcalendar.min.js' );
$document->addScript(JURI::base(). 'administrator/components/com_gcalendar/libraries/fullcalendar/gcal.js');
$document->addStyleSheet(JURI::base().'administrator/components/com_gcalendar/libraries/fullcalendar/fullcalendar.css');
$document->addScript(JURI::base().'administrator/components/com_gcalendar/libraries/jquery/ui/ui.core.js');
$document->addScript(JURI::base().'administrator/components/com_gcalendar/libraries/jquery/ui/ui.datepicker.js');
$document->addStyleSheet(JURI::base().'administrator/components/com_gcalendar/libraries/jquery/themes/redmond/ui.all.css');
$document->addStyleDeclaration("#ui-datepicker-div { z-index: 15; }");
$params = $this->params;


$calsSources = "       eventSources: [\n";
foreach($this->calendars as $calendar) {
	$cssClass = "gcal-event_gccal_".$calendar->id;
	$calsSources .= "				'".JRoute::_(JURI::base().'index.php?option=com_gcalendar&format=raw&gcid='.$calendar->id.'&Itemid='.$Itemid)."',\n";
	$color = GCalendarUtil::getFadedColor($calendar->color);
	$document->addStyleDeclaration(".".$cssClass.",.".$cssClass." a, .".$cssClass." span{background-color: ".$color." !important; border-color: #FFFFFF; color: white;}");
}
$calsSources = ltrim($calsSources, ',\n');
$calsSources .= "    ],\n";

$defaultView = 'month';
if($params->get('defaultView', 'month') == 'week')
$defaultView = 'agendaWeek';
else if($params->get('defaultView', 'month') == 'day')
$defaultView = 'agendaDay';

$daysLong = "[";
$daysShort = "[";
$daysMin = "[";
$monthsLong = "[";
$monthsShort = "[";
$dateObject = JFactory::getDate();
for ($i=0; $i<7; $i++) {
	$daysLong .= "'".$dateObject->_dayToString($i, false)."'";
	$daysShort .= "'".$dateObject->_dayToString($i, true)."'";
	$daysMin .= "'".substr($dateObject->_dayToString($i, true), 0, 2)."'";
	if($i < 6){
		$daysLong .= ",";
		$daysShort .= ",";
		$daysMin .= ",";
	}
}

for ($i=1; $i<=12; $i++) {
	$monthsLong .= "'".$dateObject->_monthToString($i, false)."'";
	$monthsShort .= "'".$dateObject->_monthToString($i, true)."'";
	if($i < 12){
		$monthsLong .= ",";
		$monthsShort .= ",";
	}
}
$daysLong .= "]";
$daysShort .= "]";
$daysMin .= "]";
$monthsLong .= "]";
$monthsShort .= "]";

$calCode = "window.addEvent(\"domready\", function(){\n";
$calCode .= "jQuery('#calendar').fullCalendar({\n";
$calCode .= "       header: {\n";
$calCode .= "				left: 'prev,next today',\n";
$calCode .= "				center: 'title',\n";
$calCode .= "				right: 'month,agendaWeek,agendaDay'\n";
$calCode .= "		},\n";
$calCode .= "		editable: false, theme: false,\n";
$calCode .= "		titleFormat: { \n";
$calCode .= "		        month: 'MMMM yyyy',\n";
$calCode .= "		        week: \"MMM d[ yyyy]{ '&#8212;'[ MMM] d yyyy}\",\n";
$calCode .= "		        day: 'dddd, MMM d, yyyy'},\n";
$calCode .= "		firstDay: ".$params->get('weekstart', 0).",\n";
$calCode .= "		defaultView: '".$defaultView."',\n";
$calCode .= "		monthNames: ".$monthsLong.",\n";
$calCode .= "		monthNamesShort: ".$monthsShort.",\n";
$calCode .= "		dayNames: ".$daysLong.",\n";
$calCode .= "		dayNamesShort: ".$daysShort.",\n";
$calCode .= "		timeFormat: {'': 'h(:mm)t'},\n";
$calCode .= "		columnFormat: { month: 'ddd', week: 'ddd d', day: 'dddd d'},\n";
$calCode .= "		axisFormat: 'H:mm',\n";
$calCode .= "		allDayText: '".JText::_( 'CALENDAR_VIEW_ALL_DAY' )."',\n";
$calCode .= "		buttonText: {\n";
$calCode .= "		    prev:     '&nbsp;&#9668;&nbsp;',\n";  // left triangle
$calCode .= "		    next:     '&nbsp;&#9658;&nbsp;',\n";  // right triangle
$calCode .= "		    prevYear: '&nbsp;&lt;&lt;&nbsp;',\n"; // <<
$calCode .= "		    nextYear: '&nbsp;&gt;&gt;&nbsp;',\n"; // >>
$calCode .= "		    today:    '".JText::_( 'TOOLBAR_TODAY' )."',\n";
$calCode .= "		    month:    '".JText::_( 'VIEW_MONTH' )."',\n";
$calCode .= "		    week:     '".JText::_( 'VIEW_WEEK' )."',\n";
$calCode .= "		    day:      '".JText::_( 'VIEW_DAY' )."'\n";
$calCode .= "		},\n";
$calCode .= $calsSources;
$calCode .= "		eventRender: function(event, element) {\n";
$calCode .= "			element.find('a').addClass('modal');\n";
$calCode .= "		},\n";
$calCode .= "		eventClick: function(event) {\n";
//$calCode .= "		    if (event.url) {return false;}\n";
$calCode .= "		},\n";
$calCode .= "       dayClick: function(date, allDay, jsEvent, view) {\n";
$calCode .= "           jQuery('#calendar').fullCalendar('gotoDate', date).fullCalendar('changeView', 'agendaDay');\n";
$calCode .= "       },\n";
$calCode .= "		loading: function(bool) {\n";
$calCode .= "			if (bool) {\n";
$calCode .= "				jQuery('#loading').show();\n";
$calCode .= "			}else{\n";
$calCode .= "				jQuery('#loading').hide();\n";
$calCode .= "			}\n";
$calCode .= "		}\n";
$calCode .= "	});\n";
$calCode .= "	var custom_buttons = 	'<td style=\"padding-left:10px\">'+\n";
$calCode .= "									'<div class=\"fc-state-default fc-corner-left fc-corner-right fc-state-enabled\">'+\n";
$calCode .= "										'<a onClick=\"jQuery(\'#date_picker\').datepicker(\'show\');\"><span>Jump'+\n";
$calCode .= "											'<input type=\"hidden\" id=\"date_picker\" value=\"\">'+\n";
$calCode .= "										'</span></a>'+\n";
$calCode .= "									'</div>'+\n";
$calCode .= "								'</td>';\n";
$calCode .= "		jQuery('div.fc-button-today').parent('td').after(custom_buttons);\n";
$calCode .= "		jQuery(\"#date_picker\").datepicker({\n";
$calCode .= "			dateFormat: 'dd-mm-yy',\n";
$calCode .= "			clickInput: true, \n";
	//$calCode .= "			showOn: 'button',\n";
	//$calCode .= "			buttonImage: 'images/datepicker.png',\n";
	//$calCode .= "			buttonImageOnly: true,     \n";
$calCode .= "			dayNames: ".$daysLong.",\n";
$calCode .= "			dayNamesShort: ".$daysShort.",\n";
$calCode .= "			dayNamesMin: ".$daysMin.",\n";
$calCode .= "			monthNames: ".$monthsLong.",\n";
$calCode .= "			monthNamesShort: ".$monthsShort.",\n";
$calCode .= "			onSelect: function(dateText, inst) {\n";
$calCode .= "				var d = jQuery('#date_picker').datepicker('getDate');\n";
$calCode .= "				jQuery('#calendar').fullCalendar('gotoDate', d);\n";
$calCode .= "			}\n";
$calCode .= "		});\n";
$calCode .= "});\n";
$document->addScriptDeclaration($calCode);

echo $params->get( 'textbefore' );
echo "<div id='loading' style='display:none'>loading...</div>";
echo "<div id='calendar'></div>";
echo $params->get( 'textafter' );
echo "<div style=\"text-align:center;margin-top:10px\" id=\"gcalendar_powered\"><a href=\"http://g4j.laoneo.net\">Powered by GCalendar</a></div>\n";
?>
