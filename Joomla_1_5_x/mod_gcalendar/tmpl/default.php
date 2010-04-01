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

defined( '_JEXEC' ) or die( 'Restricted access' );

JHTML::_('behavior.mootools');
GCalendarUtil::loadJQuery();
$document = &JFactory::getDocument();
$document->addScript(JURI::base(). 'administrator/components/com_gcalendar/libraries/fullcalendar/fullcalendar.min.js' );
$document->addStyleSheet(JURI::base().'administrator/components/com_gcalendar/libraries/fullcalendar/fullcalendar.css');
$document->addScript(JURI::base().'administrator/components/com_gcalendar/libraries/jquery/util/jquery.qtip-1.0.0-rc3.min.js');

$cssClass = "gcal-module_event_gccal";
$document->addStyleDeclaration(".".$cssClass.",.".$cssClass." a, .".$cssClass." span{background-color: #CCC9C9 !important; border-color: #FFFFFF; color: white;} .fc-header-center{vertical-align: middle !important;} #gcalendar_module_1 .fc-state-default span, #gcalendar_module_1 .ui-state-default{padding:0px !important;}");

$theme = $params->get('theme', '');
if(!empty($theme))
$document->addStyleSheet(JURI::base().'administrator/components/com_gcalendar/libraries/jquery/themes/'.$theme.'/ui.all.css');

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

static $moduleID = 0;
$moduleID++;
$ids = '';
foreach($calendars as $calendar) {
	$ids .= $calendar->id.',';
}
$calCode = "window.addEvent(\"domready\", function(){\n";
$calCode .= "   jQuery('#gcalendar_module_".$moduleID."').fullCalendar({\n";
$calCode .= "		events: '".JRoute::_(JURI::base().'index.php?option=com_gcalendar&view=jsonfeed&layout=module&format=raw&gcids='.$ids)."',\n";
$calCode .= "       header: {\n";
$calCode .= "				left: 'prev,next ',\n";
$calCode .= "				center: 'title',\n";
$calCode .= "				right: ''\n";
$calCode .= "		},\n";
$calCode .= "		defaultView: 'month',\n";
$height = $params->get('calendar_height', null);
if(!empty($height))
$calCode .= "		contentHeight: ".$height.",\n";
$calCode .= "		editable: false, theme: ".(!empty($theme)?'true':'false').",\n";
$calCode .= "		titleFormat: { \n";
$calCode .= "		        month: '".$params->get('titleformat_month', 'MMMM yyyy')."'},\n";
$calCode .= "		firstDay: ".$params->get('weekstart', 0).",\n";
$calCode .= "		monthNames: ".$monthsLong.",\n";
$calCode .= "		monthNamesShort: ".$monthsShort.",\n";
$calCode .= "		dayNames: ".$daysLong.",\n";
$calCode .= "		dayNamesShort: ".$daysShort.",\n";
$calCode .= "		timeFormat: { \n";
$calCode .= "		        month: '".$params->get('timeformat_month', 'HH:mm')."'},\n";
$calCode .= "		columnFormat: { month: 'ddd', week: 'ddd d', day: 'dddd d'},\n";
$calCode .= "		buttonText: {\n";
$calCode .= "		    prev:     '&nbsp;&#9668;&nbsp;',\n";  // left triangle
$calCode .= "		    next:     '&nbsp;&#9658;&nbsp;',\n";  // right triangle
$calCode .= "		    prevYear: '&nbsp;&lt;&lt;&nbsp;',\n"; // <<
$calCode .= "		    nextYear: '&nbsp;&gt;&gt;&nbsp;',\n"; // >>
$calCode .= "		    month:    '".JText::_( 'VIEW_MONTH' )."',\n";
$calCode .= "		},\n";
$calCode .= "		eventRender: function(event, element) {\n";
$calCode .= "				jQuery(element).qtip({\n";
$calCode .= "					content: event.description,\n";
$calCode .= "					position: {\n";
$calCode .= "						corner: {\n";
$calCode .= "							target: 'topLeft',\n";
$calCode .= "							tooltip: 'bottomLefte'\n";
$calCode .= "						}\n";
$calCode .= "					},\n";
$calCode .= "					border: {\n";
$calCode .= "						radius: 4,\n";
$calCode .= "						width: 3\n";
$calCode .= "					},\n";
$calCode .= "					style: { name: 'cream', tip: 'bottomLeft' }\n";
$calCode .= "				});\n";
//$calCode .= "				alert(jQuery(element));\n";
$calCode .= "		},\n";
$calCode .= "		loading: function(bool) {\n";
$calCode .= "			if (bool) {\n";
$calCode .= "				jQuery('#gcalendar_module_".$moduleID."_loading').show();\n";
$calCode .= "			}else{\n";
$calCode .= "				jQuery('#gcalendar_module_".$moduleID."_loading').hide();\n";
$calCode .= "			}\n";
$calCode .= "		}\n";
$calCode .= "	});\n";
$calCode .= "});\n";
$document->addScriptDeclaration($calCode);

echo "<div id='gcalendar_module_".$moduleID."_loading' style=\"text-align: center;\"><img src=\"".JURI::base() . "modules/mod_gcalendar/tmpl/ajax-loader.gif\" /></div>";
echo "<div id='gcalendar_module_".$moduleID."'></div><div id='gcalendar_module_".$moduleID."_popup' style=\"visibility:hidden\" ></div>";
?>
