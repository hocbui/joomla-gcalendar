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
		switch ($displayType) {
			case "month":
				$myClass = $spItem->get_day_type()== $spItem->SINGLE_WHOLE_DAY ? "AllDayItem" : "TimedItem";
					
				echo "<a class=\"modal\" href=\"".JRoute::_('index.php?option=com_gcalendar&tmpl=component&view=event&eventID='.$spItem->get_id().'&gcid='.$feed->get('gcid'))."\" ";
				echo " rel=\"{handler: 'iframe', size: {x: 680, y: 600}}\" title=\"";
				echo CalEvent::summary($spItem);
				echo "\" >";
				echo CalEvent::summary($spItem,22);
				echo "</a>";
				return true;
				break;
			case "week":
			case "day":
				$description = CalEvent::descriptionPopup($spItem);
				$summaryLength = ($displayType == "week") ? ($description ? 23 : 24) : 0;
				if (!$spItem->get_day_type() == $spItem->SINGLE_WHOLE_DAY) {
					echo '<div class="Header">';
					list($myStartHour, $myStartMinute, $myStartAMPM) = explode(",", strftime("%I,%M,%p", $this->startDate()));
					$myStartHour = (int)$myStartHour;
					$myStartAMPM = strtolower($myStartAMPM);
					if ($myStartMinute == "00")
					$myStartMinute = "";
					else
					$myStartMinute = ":" . $myStartMinute;
					if ($displayType == "day") echo "<strong>";
					echo "${myStartHour}${myStartMinute}${myStartAMPM}";
					if ($displayType == "day") echo "</strong>";

					if ($displayType == "day") {
						list($myEndHour, $myEndMinute, $myEndAMPM) =
						explode(",", strftime("%I,%M,%p", $this->endDate()));
						$myEndHour = (int)$myEndHour;
						$myEndAMPM = strtolower($myEndAMPM);
						if (!(int)$myEndMinute)
						$myEndMinute = "";
						else
						$myEndMinute = ":" . $myEndMinute;
						echo " - ${myEndHour}${myEndMinute}${myEndAMPM}";
						}
						echo $description;
						echo '</div>';
				}
				// TODO - Any way to get a title without an anchor tag? Or, barring that, set the status bar in a more helpful manner.
				echo "<a href=\"".JRoute::_('index.php?option=com_gcalendar&tmpl=component&view=event&eventID='.$spItem->get_id().'&gcid='.$feed->get('gcid'))."\" class=\"modal\"";
				echo " rel=\"{handler: 'iframe', size: {x: 680, y: 600}}\" title=\"";
				echo CalEvent::summary($spItem);
				echo "\">";
				echo CalEvent::summary($spItem,$summaryLength);
				echo "</a>";
				return true;
				break;
		}
		return false;
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

	function descriptionPopup($spItem) {
		$items = array();

		$items["Location"] = $spItem->get_location();
		/** if ($prop = $this->getProperty("ATTENDEE")) {
			// This is a multi-instance property, so we get an array.
			$items["Attendees"] = array();
			foreach($prop as $p) {
			$thisAttendee = '<a href="' . JRoute::_($p->value()) . '">';
			if ($cn = $p->parameter("CN"))
			$thisAttendee .= str_replace('"', '', $cn);
			else
			$thisAttendee .= $p->value();
			$thisAttendee .= "</a>";
			$items["Attendees"][] = $thisAttendee;
			}
			}
			**/
		$val = $spItem->get_link();
		$items["URL"] = '<a target="_iWebCal_ext_viewer" href="' . JRoute::_($val) . '">' . $val . '</a>';

		$desc = str_replace("\n\n", "[[BR]]", rtrim($spItem->get_description()));
		$desc = explode("[[BR]]", $desc);
		$items["Notes"] = array();
		foreach ($desc as $p){
			if ($p != "")
			$items["Notes"][] = $p;
		}
			
		$items["summ"] = CalEvent::summary($spItem);

		$popupDocString = 'index.php?option=com_iwebcal&task=details&format=raw&title=Event+Details&content=' . urlencode(serialize($items));
		$result = "<a href=\"javascript:;\" onclick=\"javascript:myWin=window.open('" .
		$popupDocString .
			"', 'iwebcal_note_win', 'width=250,height=300,left=30,top=30');\">" .
			"<img src=\"" . JURI::base() . 'components/com_gcalendar/views/gcalendar/tmpl/img/note-button.gif\" '.
			"width=\"10\" height=\"9\" border=\"0\">" .
			"details</a>";
		return $result;
	}
}
?>