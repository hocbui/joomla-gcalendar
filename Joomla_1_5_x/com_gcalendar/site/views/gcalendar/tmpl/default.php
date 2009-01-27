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

if(empty($this->gcalendar)){
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
			if($paramsItem->get('name')===$this->params->get('name')){
				$pathway->addItem($this->params->get('name'), '');
			}
		}
	}
?>

<div class="contentpane<?php echo $this->params->get( 'pageclass_sfx' ); ?>">

<?php
	$calendar_url="";
	if ($this->params->get('use_custom_css')) {
		$calendar_url= JURI::base().'components/com_gcalendar/views/gcalendar/tmpl/googlecal/MyGoogleCal4.php?'.str_replace("http://www.google.com/calendar/embed?","",$this->gcalendar);
	} else {
		$calendar_url=$this->gcalendar;
	}
?>
		<iframe
		id="gcalendar"
		name="iframe"
		src="<?php echo $calendar_url; ?>"
		width="<?php echo $this->params->get( 'width' ); ?>"
		height="<?php echo $this->params->get( 'height' ); ?>"
		scrolling="<?php echo $this->params->get( 'scrolling' ); ?>"
		align="top"
		frameborder="0"
		class="gcalendar<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
		<?php echo JText::_( 'NO_IFRAMES' ); ?>
		</iframe>
		
		</div>
	
<?php
}
?>