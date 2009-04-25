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

defined('_JEXEC') or die('Restricted access');

$document =& JFactory::getDocument();
JHTML::_('behavior.modal');
$document->addScript('administrator/components/com_gcalendar/libraries/nifty/nifty.js');
$document->addStyleSheet('administrator/components/com_gcalendar/libraries/nifty/niftyCorners.css');
$document->addScript('administrator/components/com_gcalendar/libraries/datepicker/datepicker.js');
$document->addStyleSheet('administrator/components/com_gcalendar/libraries/datepicker/style.css');
$document->addStyleSheet('components/com_gcalendar/views/gcalendar/tmpl/gcalendar.css');
if ($this->userAgent == "ie") {
	$document->addStyleSheet('components/com_gcalendar/views/gcalendar/tmpl/gcalendar-ie6.css');
}
?>
<div
	class="contentpane<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
<?php
$model = &$this->getModel();
$cal = new GCalendar($model);
$cal->display();
?>
</div>