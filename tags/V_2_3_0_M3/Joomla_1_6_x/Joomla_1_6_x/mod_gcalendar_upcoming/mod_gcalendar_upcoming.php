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
 * @copyright 2007-2011 Allon Moritz
 * @since 2.2.0
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

require_once (dirname(__FILE__).DS.'helper.php');
require_once (JPATH_ADMINISTRATOR.DS.'components'.DS.'com_gcalendar'.DS.'util.php');

$params->set('past_events', 0);
$params->set('expand_events', 1);
$params->set('start_date', 'now');
$params->set('gc_cache_folder', 'mod_gcalendar_upcoming');

$gcalendar_data = ModGCalendarUpcomingHelper::getCalendarItems($params);
require( JModuleHelper::getLayoutPath( 'mod_gcalendar_upcoming' ) );
?>