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
 * @copyright 2007-2010 Allon Moritz
 * @version $Revision: 2.1.6 $
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

class EventRenderer {

	function display($displayType, $spItem) {
		global $Itemid;
		$feed = $spItem->get_feed();
		$summaryLength = 0;
		switch ($displayType) {
			case "month":
				$summaryLength = 22;
				break;
			case "week":
				$summaryLength = 23;
				break;
			case "day":
				$summaryLength = 0;
				break;
		}
		JHTML::_('behavior.modal');
		$document =& JFactory::getDocument();
		$document->addScript(JURI::base().'components/com_gcalendar/hiddenviews/event/tmpl/default.js');
		echo "<a class=\"gcalendar_daylink modal\" href=\"".JRoute::_('index.php?option=com_gcalendar&amp;tmpl=component&amp;view=event&amp;eventID='.$spItem->get_id().'&amp;start='.$spItem->get_start_date().'&amp;end='.$spItem->get_end_date().'&amp;gcid='.$feed->get('gcid')).'&amp;Itemid='.$Itemid."\" ";
		echo " title=\"";
		echo EventRenderer::summary($spItem);
		echo "\" >";
		echo EventRenderer::summary($spItem,$summaryLength);
		echo "</a>\n";
	}

	function summary($spItem, $maxlength = 0) {
		$sum = $spItem->get_title();
		if (!$sum) return NULL;
		$sum = stripslashes($sum);
		if (!$sum) return NULL;
		if ($maxlength) {
			if ($maxlength < strlen($sum)) {
				return substr($sum, 0, $maxlength-3) . "...";
			}
			return substr($sum, 0, $maxlength);
		}
		return $sum;
	}
}
?>