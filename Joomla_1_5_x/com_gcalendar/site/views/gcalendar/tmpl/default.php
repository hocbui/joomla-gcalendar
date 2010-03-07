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

$calsSources = "eventSources: [\n";
foreach($this->calendars as $calendar) {
	$cssClass = "gcal-event_gccal_".$calendar->id;
	$calsSources .= "'".JRoute::_(JURI::base().'index.php?option=com_gcalendar&format=raw&gcid='.$calendar->id.'&Itemid='.$Itemid)."',\n";
	$color = GCalendarUtil::getFadedColor($calendar->color);
	$document->addStyleDeclaration(".".$cssClass.",.".$cssClass." a, .".$cssClass." span{background-color: ".$color." !important; border-color: #FFFFFF; color: white;}");
}
$calsSources = rtrim($calsSources, ',\n');
$calsSources .= "    ],\n";

$calCode = "window.addEvent(\"domready\", function(){\n";
$calCode .= "jQuery('#calendar').fullCalendar({\n";
$calCode .= "       header: {\n";
$calCode .= "				left: 'prev,next today',\n";
$calCode .= "				center: 'title',\n";
$calCode .= "				right: 'month,agendaWeek,agendaDay'\n";
$calCode .= "		},\n";
$calCode .= "		editable: false,firstDay: 1,\n";
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
$calCode .= "});\n";
$document->addScriptDeclaration($calCode);

echo "<div id='loading' style='display:none'>loading...</div>";
echo "<div id='calendar'></div>";

echo "<div style=\"text-align:center;margin-top:10px\" id=\"gcalendar_powered\"><a href=\"http://g4j.laoneo.net\">Powered by GCalendar</a></div>\n";
?>
