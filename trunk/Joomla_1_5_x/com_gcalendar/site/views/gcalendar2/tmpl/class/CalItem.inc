<?php
defined( '_JEXEC' ) or die( 'Restricted access' );

class CalItem {
	
	function display($displayType, $spItem, $linkString="javascript:;") {
		switch ($displayType) {		
			case "month":
				$myClass = $this->isAllDay() ? "AllDayItem" : "TimedItem";		
				if ($linkString == "#"){
					$myClass .= " NoHREF";
				} else {
					$linkString = JRoute::_($linkString);
				}
					
				echo "<a class=\"${myClass}\" href=\"${linkString}\" ";
				echo "title=\"";
				echo $this->summary();
				echo "\">";
				echo $this->summary(22);
				echo "</a>";
				return true;
				break;
			case "week":
			case "day":
				$description = $this->descriptionPopup();
				$summaryLength = ($displayType == "week") ? ($description ? 23 : 24) : 0;
				if (!$this->isAllDay()) {
					?><div class="Header"><?php
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
					?></div><?php
				}
				// TODO - Any way to get a title without an anchor tag? Or, barring that, set the status bar in a more helpful manner.
				echo "<a href=\"javascript:;\" class=\"Summ\"";
				echo " title=\"";
				echo $this->summary();
				echo "\">";
				echo $this->summary($summaryLength);
				echo "</a>";
 				return true;
				break;
		}
		echo "<p><b>iWebCal Error:</b> Invalid calendar display type.</p>";
		return false;
	}			

	function descriptionPopup() {
		global $iWebCal_URL_PATH;
		
		$items = array();
		
		if ($prop = $this->getProperty("LOCATION"))
			$items["Location"] = $prop->value();
		if ($prop = $this->getProperty("ATTENDEE")) {
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
		if ($prop = $this->getProperty("URL")) {
			$val = $prop->value();
			$items["URL"] = '<a target="_iWebCal_ext_viewer" href="' . JRoute::_($val) . '">' . $val . '</a>';
		}
		if ($prop = $this->getProperty("DESCRIPTION")) {
			$desc = str_replace("\n\n", "[[BR]]", rtrim($prop->value()));
			$desc = explode("[[BR]]", $desc);
			$items["Notes"] = array();
			foreach ($desc as $p)
				if ($p != "")
					$items["Notes"][] = $p;
		}
		if (!count($items)) return "";
		$items["summ"] = $this->summary();
		
		$popupDocString = 'index.php?option=com_iwebcal&task=details&format=raw&title=Event+Details&content=' . urlencode(serialize($items));
		//$popupDocString = $iWebCal_URL_PATH . "/base/popup-event-info.php?title=Event+Details&content=" . urlencode(serialize($items));
		$result = "<a href=\"javascript:;\" onclick=\"javascript:myWin=window.open('" .
			$popupDocString .
			"', 'iwebcal_note_win', 'width=250,height=300,left=30,top=30');\">" .
			"<img src=\"" . $iWebCal_URL_PATH . "/img/note-button.gif\" " .
			"width=\"10\" height=\"9\" border=\"0\">" .
			"details</a>";
		return $result;
	}

}


?>
