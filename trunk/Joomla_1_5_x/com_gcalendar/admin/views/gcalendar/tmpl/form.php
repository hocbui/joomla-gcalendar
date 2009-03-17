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

defined('_JEXEC') or die('Restricted access'); ?>

<form action="index.php" method="post" name="adminForm" id="adminForm">
<div class="col100">
<fieldset class="adminform"><legend><?php echo JText::_( 'CALENDAR_DETAILS' ); ?></legend>

<table class="admintable">
	<tr>
		<td width="100%" align="right" class="key"><label for="gcalendar"> <?php echo JText::_( 'CALENDAR_NAME' ); ?>:
		</label></td>
		<td><input class="text_area" type="text" name="name" id="name"
			size="100%" maxlength="250"
			value="<?php echo $this->gcalendar->name;?>" /></td>
	</tr>
	<tr>
		<td width="100%" align="right" class="key"><label for="gcalendar"> <?php echo JText::_( 'Calendar ID' ); ?>:
		</label></td>
		<td><input class="text_area" type="text" name="calendar_id"
			id="calendar_id" size="100%"
			value="<?php echo $this->gcalendar->calendar_id;?>" /></td>
	</tr>
	<tr>
		<td width="100%" align="right" class="key"><label for="gcalendar"> <?php echo JText::_( 'Magic Cookie' ); ?>:
		</label></td>
		<td><input class="text_area" type="text" name="magic_cookie"
			id="magic_cookie" size="100%"
			value="<?php echo $this->gcalendar->magic_cookie;?>" /></td>
	</tr>
	<tr>
		<td width="100%" align="right" class="key"><label for="gcalendar"> <?php echo JText::_( 'Color' ); ?>:
		</label></td>
		<td><input class="text_area" type="text" name="color" id="color"
			size="100%" value="<?php echo $this->gcalendar->color;?>" /></td>
	</tr>
</table>
</fieldset>
</div>
<div class="clr"></div>

<input type="hidden" name="option" value="com_gcalendar" /> <input
	type="hidden" name="id" value="<?php echo $this->gcalendar->id; ?>" />
<input type="hidden" name="task" value="" /> <input type="hidden"
	name="controller" value="gcalendar" /></form>

<div align="center"><br>
<img src="components/com_gcalendar/images/gcalendar.gif" width="143"
	height="57"><br>
&copy;&nbsp;&nbsp;2009 <a href="http://gcalendar.allon.ch"
	target="_blank">allon moritz</a></div>
