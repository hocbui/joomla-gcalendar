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

$p= parse_url($this->gcalendar);

$tz = '';
if(!empty($this->timezone))$tz='&ctz='.$this->timezone;
$params   = JComponentHelper::getParams('com_languages');
$lg = $params->get('site', 'en-GB');
$lg = '&hl='.$lg;
$url = $p['scheme'] . '://' . $p['host'] . '/calendar/event?eid=' . $this->eventID . $tz.$lg;
?>

<iframe
id="gcalendarEvent"
name="iframe"
src="<?php echo $url; ?>"
width="100%"
height="700"
align="top"
frameborder="0">
<?php echo JText::_( 'NO_IFRAMES' ); ?>
</iframe>
