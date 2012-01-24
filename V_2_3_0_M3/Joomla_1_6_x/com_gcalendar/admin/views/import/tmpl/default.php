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

defined('_JEXEC') or die('Restricted access');

if(!is_array($this->online_items)){
	echo 'No data found!';
}else{
	function print_line($row, $checked, $k){
		?>
<tr class="<?php echo "row$k"; ?>">
	<td><?php echo $checked; ?></td>
	<td><?php echo $row->name; ?></td>
	<td><?php echo urldecode($row->calendar_id); ?></td>
	<td><?php echo '#'.$row->color; ?></td>
	<td><?php echo $row->magic_cookie; ?></td>
</tr>
		<?php
	}
	?>
<form action="index.php" method="post" name="adminForm">
<div id="editcell">
<table class="adminlist">
	<thead>
		<tr>
			<th width="20"><input type="checkbox" name="toggle" value=""
				onclick="checkAll(<?php echo count( $this->online_items ); ?>);" /></th>
			<th><?php echo JText::_( 'CALENDAR_NAME' ); ?></th>
			<th align="left"><?php echo JText::_( 'CALENDAR_DETAILS' ); ?></th>
			<th><?php echo JText::_( 'Color' ); ?></th>
			<th align="left"><?php echo JText::_( 'Magic Cookie' ); ?></th>
		</tr>
	</thead>
	<?php
	$k = 0;
	$containing_items = array();
	for ($i=0, $n=count( $this->online_items ); $i < $n; $i++)
	{
		$row = &$this->online_items[$i];
		$checked 	= JHTML::_('grid.id',   $i, $row->calendar_id.','.$row->color.','.$row->name.','.$row->magic_cookie );
		$is_included = FALSE;
		if($this->db_items){
			foreach($this->db_items as $db_item){
				if($db_item->calendar_id == $row->calendar_id){
					$containing_items[] = $row;
					$is_included = TRUE;
				}
			}
		}
		if(!$is_included){
			print_line($row,$checked,$k);
		}
		$k = 1 - $k;
	}
	if(!empty($containing_items)){
		echo '<tr><td colspan="5"><b>'.JText::_( 'Already added calendars:' ).'</b></td></tr>';
		$k = 0;
		for ($i=0, $n=count($containing_items); $i < $n; $i++)
		{
			$row = $containing_items[$i];
			print_line($row,'',$k);
			$k = 1 - $k;
		}
	}
	?>
</table>
	<input type="hidden" name="option" value="com_gcalendar" /> 
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</div>
</form>
<?php } ?>

<div align="center"><br>
<img src="media/com_gcalendar/images/gcalendar.gif" width="156"
	height="30"><br>
&copy;&nbsp;&nbsp;2011 <a href="http://g4j.laoneo.net"
	target="_blank">allon moritz</a></div>
