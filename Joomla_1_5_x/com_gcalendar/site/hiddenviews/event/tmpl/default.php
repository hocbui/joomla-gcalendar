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
 * @version $Revision: 2.1.1 $
 */

defined('_JEXEC') or die('Restricted access');

require_once (JPATH_ADMINISTRATOR.DS.'components'.DS.'com_gcalendar'.DS.'util.php');

$itemID = GCalendarUtil::getItemId(JRequest::getVar('gcid', null));
if(!empty($itemID) && JRequest::getVar('tmpl', null) != 'component'){
	$component	= &JComponentHelper::getComponent('com_gcalendar');
	$menu = &JSite::getMenu();
	$item = $menu->getItem($itemID);
	if($item !=null){
		$backLinkView = $item->query['view'];
		echo "<table><tr><td valign=\"middle\">\n";
		echo '<a href="'.JRoute::_('index.php?option=com_gcalendar&view='.$backLinkView.'&Itemid='.$itemID)."\">\n";
		echo "<img id=\"prevBtn_img\" height=\"16\" border=\"0\" width=\"16\" alt=\"backlink\" src=\"components/com_gcalendar/hiddenviews/event/tmpl/back.png\"/>\n";
		echo "</a></td><td valign=\"middle\">\n";
		echo '<a href="'.JRoute::_('index.php?option=com_gcalendar&view='.$backLinkView.'&Itemid='.$itemID).'">'.JText::_( 'CALENDAR_BACK_LINK' )."</a>\n";
		echo "</td></tr></table>\n";
	}
}
$event = $this->event;
if($event == null){
	echo "no event found";
}else{
	// the date formats from http://php.net/strftime
	$dateformat = '%d.%m.%Y';
	$timeformat = '%H:%M';

	// These are the dates we'll display
	$tmp = JFactory::getDate($event->get_start_date());
	$startDate = $tmp->toFormat($dateformat);
	$tmp = JFactory::getDate($event->get_start_date());
	$startTime = $tmp->toFormat($timeformat);
	$tmp = JFactory::getDate($event->get_end_date());
	$endDate = $tmp->toFormat($dateformat);
	$tmp = JFactory::getDate($event->get_end_date());
	$endTime = $tmp->toFormat($timeformat);
	$dateSeparator = '-';

	$timeString = $startTime.' '.$startDate.' '.$dateSeparator.' '.$endTime.' '.$endDate;
	switch($event->get_day_type()){
		case $event->SINGLE_WHOLE_DAY:
			$timeString = $startDate;
			break;
		case $event->SINGLE_PART_DAY:
			$timeString = $startDate.' '.$startTime.' '.$dateSeparator.' '.$endTime;
			break;
		case $event->MULTIPLE_WHOLE_DAY:
			$SECSINDAY=86400;
			$tmp = JFactory::getDate($event->get_end_date() - $SECSINDAY);
			$endDate = $tmp->toFormat($timeformat);
			$timeString = $startDate.' '.$dateSeparator.' '.$endDate;
			break;
		case $event->MULTIPLE_PART_DAY:
			$timeString = $startTime.' '.$startDate.' '.$dateSeparator.' '.$endTime.' '.$endDate;
			break;
	}

	$document =& JFactory::getDocument();
	$document->addScript(JURI::base().'administrator/components/com_gcalendar/libraries/nifty/nifty.js');
	$document->addStyleSheet(JURI::base().'administrator/components/com_gcalendar/libraries/nifty/niftyCorners.css');
	$document->addStyleSheet(JURI::base().'components/com_gcalendar/hiddenviews/event/tmpl/default.css');
	$calCode = "window.addEvent(\"domready\", function(){\n";
	$calCode .= "Nifty(\"div.event_content\",\"big\");\n";
	$calCode .= "});";
	$document->addScriptDeclaration($calCode);

	$feed = $event->get_feed();
	echo "<div class=\"event_content\"><table id=\"content_table\">\n";
	echo "<tr><td class=\"event_content_key\">".JText::_( 'CALENDAR_NAME' ).": </td><td>".$feed->get('gcname')."</td></tr>\n";
	echo "<tr><td class=\"event_content_key\">".JText::_( 'EVENT_TITLE' ).": </td><td>".$event->get_title()."</td></tr>\n";
	echo "<tr><td class=\"event_content_key\">".JText::_( 'WHEN' ).": </td><td>".$timeString."</td></tr>\n";
	echo "<tr><td class=\"event_content_key\">".JText::_( 'DESCRIPTION' ).": </td><td>". htmlspecialchars_decode(nl2br(preg_replace('(((f|ht){1}tp://)[-a-zA-Z0-9@:%_\+.~#?,//=&;]+)','<a href="\\1" target="_top">\\1</a>', $event->get_description())))."</td></tr>\n";
	$loc = $event->get_location();
	if(!empty($loc)){
		echo "<tr><td class=\"event_content_key\">".JText::_( 'LOCATION' ).": </td><td>".$loc."</td></tr>\n";
		echo "<tr><td colspan=\"2\"><iframe width=\"100%\" height=\"300px\" frameborder=\"no\" scrolling=\"no\" marginheight=\"0\" marginwidth=\"0\" src=\"http://maps.google.com/maps?q=".urlencode($loc)."&output=embed\"></iframe></td></tr>\n";
	}
	$authors = $event->get_authors();
	if(count($authors)>0){
		$document->addScript(JURI::base().'components/com_gcalendar/hiddenviews/event/tmpl/default.js');
		echo "<tr><td class=\"event_content_key\">".JText::_( 'AUTHOR' ).": </td><td style=\"valign:top\">".$authors[0]->get_name()." <a href=\"javascript:sdafgkl437jeeee('".base64_encode(str_replace('@','#',$authors[0]->get_email()))."')\"><img height=\"11\" border=\"0\" width=\"16\" alt=\"email\" src=\"components/com_gcalendar/hiddenviews/event/tmpl/mail.png\"/></a></td></tr>\n";
	}
	echo "</table></div>\n";
}
echo "<div style=\"text-align:center;margin-top:10px\" id=\"gcalendar_powered\"><a href=\"http://g4j.laoneo.net\">Powered by GCalendar</a></div>\n";
?>