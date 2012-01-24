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

defined( '_JEXEC' ) or die( 'Restricted access' ); 

if(empty($calendar)){
	echo JText::_( 'NO_CALENDAR' );
}else{
?>
	<iframe
	id="mod_gcalendar"
	src="<?php echo $calendar; ?>"
	width="<?php echo $params->get( 'width' ); ?>"
	height="<?php echo $params->get( 'height' ); ?>"
	scrolling="<?php echo $params->get( 'scrolling' ); ?>"
	align="top"
	frameborder="0"
	class="mod_gcalendar<?php echo $params->get( 'moduleclass_sfx' ); ?>">
	<?php echo JText::_( 'NO_IFRAMES' ); ?>
	</iframe>
	<?php
}
?>