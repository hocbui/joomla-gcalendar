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

defined( '_JEXEC' ) or die( 'Restricted access' ); 

if(empty($calendars)){
	echo JText::_( 'NO_CALENDAR' );
}else{
?>
<div
	class="contentpane<?php echo $params->get( 'moduleclass_sfx' ); ?>">

	<?php
	$variables = '';
	$variables = $variables.'?showTitle='.$params->get( 'title' );
	$variables = $variables.'&amp;showNav='.$params->get( 'navigation' );
	$variables = $variables.'&amp;showDate='.$params->get( 'date' );
	$variables = $variables.'&amp;showPrint='.$params->get( 'print' );
	$variables = $variables.'&amp;showTabs='.$params->get( 'tabs' );
	$variables = $variables.'&amp;showCalendars=0';
	$variables = $variables.'&amp;showTz='.$params->get( 'tz' );
	$variables = $variables.'&amp;mode='.$params->get( 'view' );
	$variables = $variables.'&amp;wkst='.$params->get( 'weekstart' );
	$variables = $variables.'&amp;bgcolor=%23'.$params->get( 'bgcolor' );
	$tz = GCalendarUtil::getComponentParameter('timezone');
	if(!empty($tz))$tz='&ctz='.$tz;
	$variables = $variables.$tz;
	$variables = $variables.'&amp;height='.$params->get( 'height' );

	$domain = 'http://www.google.com/calendar/embed';
	$google_apps_domain = GCalendarUtil::getComponentParameter('google_apps_domain');
	if(!empty($google_apps_domain)){
		$domain = 'http://www.google.com/calendar/hosted/'.$google_apps_domain.'/embed';
	}

	foreach($calendars as $calendar) {
		$value = '&amp;src='.$calendar->calendar_id;

		if(!empty($calendar->color)){
			$color = $calendar->color;
			if(strpos($calendar->color, '#') === 0)
			$color = str_replace("#","%23",$calendar->color);
			else if(!(strpos($calendar->color, '%23') === 0))
			$color = '%23$'.$calendar->color;
			$value = $value.'&amp;color='.$color;
		}

		if(!empty($calendar->magic_cookie)){
			$value = $value.'&amp;pvttk='.$calendar->magic_cookie;
		}

		if($calendar->selected){
			$variables = $variables.$value;
		}
	}
	
	$calendar_url="";
	if ($params->get('use_custom_css')) {
		$calendar_url= JURI::base().'components/com_gcalendar/views/gcalendar/tmpl/googlecal/MyGoogleCal4.php'.$variables;
	} else {
		$calendar_url=$domain.$variables;
	}
	echo $params->get( 'textbefore' );

	?> <iframe id="mod_gcalendar_frame" src="<?php echo $calendar_url; ?>"
	width="<?php echo $params->get( 'width' ); ?>"
	height="<?php echo $params->get( 'height' ); ?>" align="top"
	frameborder="0"
	class="gcalendar<?php echo $params->get( 'pageclass_sfx' ); ?>">
	<?php echo JText::_( 'NO_IFRAMES' ); ?> </iframe></div>

	<?php
	echo $params->get( 'textafter' );
}
?>