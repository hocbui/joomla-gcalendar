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

// no direct access
defined('_JEXEC') or die('Restricted access');

if(!is_array($this->calendars)){
	echo JText::_( 'NO_CALENDAR' );
}else{
	$component	= &JComponentHelper::getComponent('com_gcalendar');
	$menu = &JSite::getMenu();
	$items		= $menu->getItems('componentid', $component->id);

	$model = & $this->getModel();
	if (is_array($items)){
		global $mainframe;
		$pathway	= &$mainframe->getPathway();
		foreach($items as $item) {
			$paramsItem	=& $menu->getParams($item->id);
			//if($paramsItem->get('calendars')===$this->params->get('calendars')){
			//	$pathway->addItem($this->params->get('name'), '');
			//}
		}
	}
	?>

<div
	class="contentpane<?php echo $this->params->get( 'pageclass_sfx' ); ?>">

	<?php

	$document = &JFactory::getDocument();
	$document->addScript( 'components/com_gcalendar/views/gcalendar/tmpl/update_calendars.js' );
	JHTML::_('behavior.mootools');

	$variables = '';
	$variables = $variables.'?showTitle='.$this->params->get( 'title' );
	$variables = $variables.'&amp;showNav='.$this->params->get( 'navigation' );
	$variables = $variables.'&amp;showDate='.$this->params->get( 'date' );
	$variables = $variables.'&amp;showPrint='.$this->params->get( 'print' );
	$variables = $variables.'&amp;showTabs='.$this->params->get( 'tabs' );
	$variables = $variables.'&amp;showCalendars=0';
	$variables = $variables.'&amp;showTz='.$this->params->get( 'tz' );
	$variables = $variables.'&amp;mode='.$this->params->get( 'view' );
	$variables = $variables.'&amp;wkst='.$this->params->get( 'weekstart' );
	$variables = $variables.'&amp;bgcolor=%23'.$this->params->get( 'bgcolor' );
	$variables = $variables.'&amp;ctz='.$this->params->get( 'timezone' );
	$variables = $variables.'&amp;height='.$this->params->get( 'height' );

	$domaine = 'http://www.google.com/calendar/embed';
	
	
	echo '<div class="marginbottom">'; 		
	echo '<a id="v_toggle" href="#">toggle</a> 		| <strong>status</strong>: <span id="vertical_status">open</span> 	</div>';  
	
	$calendar_list = '<div id="gcalendar_list"><table>';
	foreach($this->calendars as $calendar) {
		$color = $calendar['color'];
		if(!empty($color) && !(strpos($calendar['color'], '%23') === 0))
		$color = '%23$'.$calendar['color'];
		if(strpos($calendar['color'], '#') === 0)
		$color = str_replace("#","%23",$calendar['color']);

		if($calendar['selected']){
			$variables = $variables.'&amp;src='.$calendar['calendar_id'];
			if(!empty($color))
			$variables = $variables.'&amp;color='.$color;
		}

		$checked = '';
		if($calendar['selected']) $checked = 'checked';
		$html_color = '#FFFFFF';
		$html_color = str_replace("%23","#",$calendar['color']);
		$calendar_list = $calendar_list.'<tr>';
		//<input type="checkbox" value="&src=calendar%40joomla.org&color=%235A6986" checked onclick="updateFrame(this)" name="gcalendar1"/>
		$calendar_list = $calendar_list.'<td><input type="checkbox" name="'.$calendar['calendar_id'].'" value="&src='.$calendar['calendar_id'].'&color='.$color.'" '.$checked.' onclick="updateFrame(this)"/></td>';
		$calendar_list = $calendar_list.'<td><font color="'.$html_color.'">'.$calendar['name'].'</font></td><td><font color="'.$html_color.'">'.$calendar['calendar_id'].'</font></td></tr>';
	}
	$calendar_list = $calendar_list.'</table></div>';

	$calendar_url="";
	if ($this->params->get('use_custom_css')) {
		$calendar_url= JURI::base().'components/com_gcalendar/views/gcalendar/tmpl/googlecal/MyGoogleCal4.php'.$variables;
	} else {
		$calendar_url=$domaine.$variables;
	}

	echo $calendar_list;

	?> <iframe id="gcalendar_frame" 
	src="<?php echo $calendar_url; ?>"
	width="<?php echo $this->params->get( 'width' ); ?>"
	height="<?php echo $this->params->get( 'height' ); ?>" align="top"
	frameborder="0"
	class="gcalendar<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
	<?php echo JText::_( 'NO_IFRAMES' ); ?> </iframe></div>

	<?php
}
?>