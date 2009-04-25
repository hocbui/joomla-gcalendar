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

defined( '_JEXEC' ) or die( 'Restricted access' );

class CalEvent {

	function display($displayType, $spItem) {
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
		echo "<a class=\"modal\" href=\"".JRoute::_('index.php?option=com_gcalendar&tmpl=component&view=event&eventID='.$spItem->get_id().'&gcid='.$feed->get('gcid'))."\" ";
		echo " rel=\"{handler: 'iframe', size: {x: 680, y: 600}}\" title=\"";
		echo CalEvent::summary($spItem);
		echo "\" >";
		echo CalEvent::summary($spItem,$summaryLength);
		echo "</a>\n";
		return true;
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