<?php
/**
 * Google calendar component
 * @author allon
 * @version $Revision: 1.5.1 $
 */

 defined('_JEXEC') or die('Restricted access'); ?>

<form action="index.php" method="post" name="adminForm" id="adminForm">
<div class="col100">
	<fieldset class="adminform">
		<legend><?php echo JText::_( 'CALENDAR_DETAILS' ); ?></legend>

		<table class="admintable">
		<tr>
			<td width="100%" align="right" class="key">
				<label for="gcalendar">
					<?php echo JText::_( 'CALENDAR_NAME' ); ?>:
				</label>
			</td>
			<td>
				<input class="text_area" type="text" name="name" id="name" size="100%" maxlength="250" value="<?php echo $this->gcalendar->name;?>" />
			</td>
		</tr>
		<tr>
			<td width="100%" align="right" class="key">
				<label for="gcalendar">
					<?php echo JText::_( 'HTML_URL' ); ?>:
				</label>
			</td>
			<td>
				<input class="text_area" type="text" name="htmlUrl" id="htmlUrl" size="100%" value="<?php echo $this->gcalendar->htmlUrl;?>" />
			</td>
		</tr>
		<tr>
			<td width="100%" align="right" class="key">
				<label for="gcalendar">
					<?php echo JText::_( 'XML_URL' ); ?>:
				</label>
			</td>
			<td>
				<input class="text_area" type="text" name="xmlUrl" id="xmlUrl" size="100%" value="<?php echo $this->gcalendar->xmlUrl;?>" />
			</td>
		</tr>
		<tr>
			<td width="100%" align="right" class="key">
				<label for="gcalendar">
					<?php echo JText::_( 'ICAL_URL' ); ?>:
				</label>
			</td>
			<td>
				<input class="text_area" type="text" name="icalUrl" id="icalUrl" size="100%" value="<?php echo $this->gcalendar->icalUrl;?>" />
			</td>
		</tr>
	</table>
	</fieldset>
</div>
<div class="clr"></div>

<input type="hidden" name="option" value="com_gcalendar" />
<input type="hidden" name="id" value="<?php echo $this->gcalendar->id; ?>" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="controller" value="gcalendar" />
</form>

<div align="center">
<br><img src="components/com_gcalendar/images/gcalendar.gif" width="143" height="57"><br>
  &copy;&nbsp;&nbsp;2008 <a href="http://gcalendar.allon.ch" target="_blank">allon moritz</a>
</div>
