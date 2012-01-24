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

 defined('_JEXEC') or die('Restricted access'); ?>


<form action="index.php" method="post" name="adminForm">
<div id="editcell">
	<table class="adminlist">
	<thead>
		<tr>
			<th width="5">
				<?php echo JText::_( 'ID' ); ?>
			</th>
			<th width="20">
				<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $this->items ); ?>);" />
			</th>			
			<th>
				<?php echo JText::_( 'CALENDAR_NAME' ); ?>
			</th>
			<th align="left">
				<?php echo JText::_( 'CALENDAR_DETAILS' ); ?>
			</th>
		</tr>			
	</thead>
	<?php
	$k = 0;
	for ($i=0, $n=count( $this->items ); $i < $n; $i++)
	{
		$row = &$this->items[$i];
		$checked 	= JHTML::_('grid.id',   $i, $row->id );
		$link 		= JRoute::_( 'index.php?option=com_gcalendar&controller=gcalendar&task=edit&cid[]='. $row->id );

		?>
		<tr class="<?php echo "row$k"; ?>">
			<td>
				<?php echo $row->id; ?>
			</td>
			<td>
				<?php echo $checked; ?>
			</td>
			<td>
				<a href="<?php echo $link; ?>"><?php echo $row->name; ?></a>
			</td>
			<td>
				<table>
				<tr>
					<td><b><?php echo JText::_( 'HTML_URL' ); ?>:<b></td>
					<td><a href="<?php echo $row->htmlUrl; ?>" target="_blank"><?php echo $row->htmlUrl; ?></a></td>
				</tr>
				<tr>
					<td><b><?php echo JText::_( 'XML_URL' ); ?>:</b></td>
					<td><a href="<?php echo $row->xmlUrl; ?>" target="_blank"><?php echo $row->xmlUrl; ?></a></td>
				</tr>
				<tr>
					<td><b><?php echo JText::_( 'ICAL_URL' ); ?>:</b></td>
					<td><a href="<?php echo $row->icalUrl; ?>" target="_blank"><?php echo $row->icalUrl; ?></a></td>
				</tr>
				</table>
			</td>
		</tr>
		<?php
		$k = 1 - $k;
	}
	?>
	</table>
</div>

<input type="hidden" name="option" value="com_gcalendar" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="controller" value="gcalendar" />
</form>

<div align="center">
<br><img src="components/com_gcalendar/images/gcalendar.gif" width="143" height="57"><br>
  &copy;&nbsp;&nbsp;2009 <a href="http://gcalendar.allon.ch" target="_blank">allon moritz</a>
</div>