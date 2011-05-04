<?php
defined('_JEXEC') or die('Restricted Access');
?>
<?php foreach($this->items as $i => $item){?>
	<tr class="row<?php echo $i % 2; ?>">
		<td><?php echo $row->id; ?></td>
		<td>
			<?php echo JHtml::_('grid.id', $i, $item->id); ?>
		</td>
		<td>
			<a href="<?php echo JRoute::_( 'index.php?option=com_gcalendar&task=gcalendar.edit&id='. $row->id ); ?>">
				<?php echo $row->name; ?>
			</a>
		</td>
		<td width="40px"><div style="background-color: <?php echo GCalendarUtil::getFadedColor($row->color);?>;width: 100%;height: 100%;"></div></td>
		<td>
		<table>
			<tr>
				<td><b><?php echo JText::_( 'COM_GCALENDAR_FIELD_CALENDAR_ID_LABEL' ); ?>:</b></td>
				<td><?php echo $row->calendar_id; ?></td>
			</tr>
			<tr>
				<td><b><?php echo JText::_( 'COM_GCALENDAR_FIELD_MAGIC_COOKIE_LABEL' ); ?>:</b></td>
				<td><?php echo $row->magic_cookie; ?></td>
			</tr>
		</table>
		</td>
	</tr>
<?php } ?>