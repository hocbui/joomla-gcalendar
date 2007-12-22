<?php
/**
 * Google calendar component
 * @author allon
 * @version $Revision: 1.5.0 $
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
<br><img src="components/com_gcalendar/views/images/gcalendar.gif" width="143" height="57"><br>
  &copy;&nbsp;&nbsp;2007 <a href="http://gcalendar.allon.ch" target="_blank">allon moritz</a>
</div>