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
 * @version $Revision: 2.1.0 $
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
	$tz = $this->params->get('timezone');
	if(!empty($tz))$tz='&ctz='.$tz;
	$variables = $variables.$tz;
	$variables = $variables.'&amp;height='.$this->params->get( 'height' );

	$domain = 'http://www.google.com/calendar/embed';
	$google_apps_domain = $this->params->get('google_apps_domain');
	if(!empty($google_apps_domain)){
		$domain = 'http://www.google.com/calendar/hosted/'.$google_apps_domain.'/embed';
	}

	$calendar_list = '<div id="gcalendar_list"><table >';
	foreach($this->calendars as $calendar) {
		$value = '&amp;src='.$calendar->calendar_id;

		$html_color = '';
		if(!empty($calendar->color)){
			$color = $calendar->color;
			if(strpos($calendar->color, '#') === 0){
				$color = str_replace("#","%23",$calendar->color);
				$html_color = $calendar->color;
			}
			else if(!(strpos($calendar->color, '%23') === 0)){
				$color = '%23'.$calendar->color;
				$html_color = '#'.$calendar->color;
			}
			$value = $value.'&amp;color='.$color;
		}

		if(!empty($calendar->magic_cookie)){
			$value = $value.'&amp;pvttk='.$calendar->magic_cookie;
		}

		$checked = '';
		if($calendar->selected){
			$variables = $variables.$value;
			$checked = 'checked';
		}

		$calendar_list .='<tr>';
		$calendar_list .='<td><input type="checkbox" name="'.$calendar->calendar_id.'" value="'.$value.'" '.$checked.' onclick="updateGCalendarFrame(this)"/></td>';
		$calendar_list .='<td><font color="'.$html_color.'">'.$calendar->name.'</font></td></tr>';
	}
	$calendar_list .='</table></div>';
	if($this->params->get('show_selection')==1){
		JHTML::_('behavior.mootools');
		$document = &JFactory::getDocument();
		$document->addScript( 'components/com_gcalendar/views/google/tmpl/gcalendar.js' );
		echo $calendar_list;
		echo '<div align="center" style="text-align:center"><a id="toggle_gc" name="toggle_gc" href="#"><img id="toggle_gc_status" name="toggle_gc_status" src="'.JURI::base().'components/com_gcalendar/views/google/tmpl/down.png"/></a></div>';
	}
	$calendar_url="";
	if ($this->params->get('use_custom_css')) {
		$calendar_url= JURI::base().'components/com_gcalendar/views/google/tmpl/googlecal/MyGoogleCal4.php'.$variables;
	} else {
		$calendar_url=$domain.$variables;
	}
	echo $this->params->get( 'textbefore' );

	?> <iframe id="gcalendar_frame" src="<?php echo $calendar_url; ?>"
	width="<?php echo $this->params->get( 'width' ); ?>"
	height="<?php echo $this->params->get( 'height' ); ?>" align="top"
	frameborder="0"
	class="gcalendar<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
	<?php echo JText::_( 'NO_IFRAMES' ); ?> </iframe></div>

	<?php
	echo $this->params->get( 'textafter' );
}
?>