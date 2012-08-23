<?php
/**
* @package		DPCalendar
* @author		Digital Peak http://www.digital-peak.com
* @copyright	Copyright (C) 2012 Digital Peak, Inc. All rights reserved.
* @license		http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

defined('_JEXEC') or die();

$params = $this->params;

$content = $params->get('list_output', '<form action="{{{siteUrl}}}" method="post" name="adminForm" id="adminForm">
<fieldset class="filters">
<div class="filter-search">
	<label for="filter-search" class="filter-search-lbl">{{filterLabel}}</label>
	<input type="text" onchange="document.adminForm.submit();" class="inputbox" value="{{filterValue}}" id="filter-search" name="filter-search"/>
</div>
<div class="display-limit">{{limitLabel}}{{{limitBox}}}</div>
</fieldset>
<table class="category">
<thead>
	<th>{{dateLabel}}</th>
	<th>{{titleLabel}}</th>
	<th>{{calendarNameLabel}}</th>
	<th>{{locationLabel}}</th>
</thead>
<tbody>
	{{#events}}
	<tr>
		<td>{{date}}</td>
		<td><a href="{{{backLink}}}">{{title}}</a></td>
		<td>{{calendarName}}</td>
		<td>{{location}}</td>
	</tr>
	{{/events}}
</tbody>
<tfoot>
	<tr><td colspan="4"><div class="pagination">
		<p class="counter">{{{listCounter}}}</p>
		{{{pagination}}}</div>
	</td></tr>
</tfoot>
</table></form>');

$variables = array();
$variables['limitLabel'] = JText::_('JGLOBAL_DISPLAY_NUM');
$variables['limitBox'] = $this->pagination->getLimitBox();
$variables['listCounter'] = $this->pagination->getPagesCounter();
$variables['pagination'] = $this->pagination->getPagesLinks();
$variables['filterLabel'] = JText::_('JGLOBAL_FILTER_LABEL');
$variables['filterValue'] = $this->escape($this->state->get('filter.search'));
$variables['siteUrl'] = JRoute::_('index.php?option=com_gcalendar&view=list&Itemid='.JRequest::getInt('Itemid'));

echo GCalendarUtil::renderEvents($this->events, $content, JFactory::getApplication()->getParams(), $variables);