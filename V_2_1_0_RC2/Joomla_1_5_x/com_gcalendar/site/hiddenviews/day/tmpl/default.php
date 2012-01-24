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

defined('_JEXEC') or die('Restricted access');

require_once ('daycalendar.php');

$model = &$this->getModel();
$gcids = $model->getState('gcids');
if(!empty($gcids)){
	foreach ($gcids as $cal) {
		$itemID = GCalendarUtil::getItemId($cal);
		if(!empty($itemID) && JRequest::getVar('tmpl', null) != 'component'){
			$backLinkView = 'google';
			$component	= &JComponentHelper::getComponent('com_gcalendar');
			$menu = &JSite::getMenu();
			$items		= $menu->getItems('componentid', $component->id);

			if (is_array($items)){
				global $mainframe;
				$pathway	= &$mainframe->getPathway();
				foreach($items as $item) {
					$paramsItem	=& $menu->getParams($item->id);
					$calendarids = $paramsItem->get('calendarids');
					$contains_gc_id = FALSE;
					if ($calendarids){
						if( is_array( $calendarids ) ) {
							$contains_gc_id = in_array($cal,$calendarids);
						} else {
							$contains_gc_id = $cal == $calendarids;
						}
					}
					if($contains_gc_id){
						$backLinkView = $item->query['view'];
					}
				}
			}
			echo '<a href="'.JRoute::_('index.php?option=com_gcalendar&view='.$backLinkView.'&Itemid='.$itemID).'">'.JText::_( 'CALENDAR_BACK_LINK' ).'</a>';
			break;
		}
	}
}
echo "<div class=\"gcalendarDaySingleView\">\n";

$calendar = new DayCalendar($model);
$calendar->weekStart = 1;
$calendar->showSelectionList = false;
$calendar->showToolbar = false;
$calendar->forceView ='day';
$calendar->display();

echo "</div>\n";
?>