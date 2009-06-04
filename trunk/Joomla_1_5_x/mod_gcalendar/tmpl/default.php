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

require_once (JPATH_ADMINISTRATOR.DS.'components'.DS.'com_gcalendar'.DS.'libraries'.DS.'rss-calendar'.DS.'gcalendar.php');

$calendarConfig->weekStart = $params->get('weekstart');
$calendarConfig->showSelectionList = false;
$calendarConfig->dateFormat = 'dd/mm/yy';
$calendarConfig->cellHeight = 15;
$calendarConfig->forceView = month;
$calendarConfig->printDayLink = false;
$calendarConfig->showEventTitle = false;
$calendarConfig->showToolbar = false;
$calendarConfig->shortDayNames = true;
$calendarConfig->forceView ='month';
$cal = new GCalendar($calendarConfig);
$cal->display();
?>