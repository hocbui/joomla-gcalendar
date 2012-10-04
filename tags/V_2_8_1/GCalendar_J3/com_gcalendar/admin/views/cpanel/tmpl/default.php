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

defined('_JEXEC') or die();

JHtml::_('jquery.framework');
?>
<div style="width:500px;">
<h2><?php echo JText::_('COM_GCALENDAR_VIEW_CPANEL_WELCOME'); ?></h2>
<p>
<?php echo JText::_('COM_GCALENDAR_VIEW_CPANEL_INTRO'); ?>
</p>
<br>

<div id="cpanel" style="float:left">
    <div style="float:left;margin-right: 20px">
            <div class="icon">
                <a href="index.php?option=com_gcalendar&view=gcalendars" >
                <img src="<?php echo JURI::base(true);?>/../media/com_gcalendar/images/48-calendar.png" height="50px" width="50px">
                <span><?php echo JText::_('COM_GCALENDAR_VIEW_CPANEL_GCALENDARS'); ?></span>
                </a>
            </div>
            <div class="icon">
                <a href="index.php?option=com_gcalendar&task=import" >
                <img src="<?php echo JURI::base(true);?>/../media/com_gcalendar/images/admin/import.png" height="50px" width="50px">
                <span><?php echo JText::_('COM_GCALENDAR_VIEW_CPANEL_IMPORT'); ?></span>
                </a>
            </div>
            <div class="icon">
                <a href="index.php?option=com_gcalendar&view=gcalendar&layout=edit" >
                <img src="<?php echo JURI::base(true);?>/../media/com_gcalendar/images/admin/add.png" height="50px" width="50px">
                <span><?php echo JText::_('COM_GCALENDAR_VIEW_CPANEL_ADD'); ?></span>
                </a>
            </div>
            <div class="icon">
                <a href="index.php?option=com_gcalendar&view=tools" >
                <img src="<?php echo JURI::base(true);?>/../media/com_gcalendar/images/admin/tools.png" height="50px" width="50px">
                <span><?php echo JText::_('COM_GCALENDAR_SUBMENU_TOOLS'); ?></span>
                </a>
            </div>
            <div class="icon">
                <a href="index.php?option=com_gcalendar&view=support" >
                <img src="<?php echo JURI::base(true);?>/../media/com_gcalendar/images/admin/support.png" height="50px" width="50px">
                <span><?php echo JText::_('COM_GCALENDAR_SUBMENU_SUPPORT'); ?></span>
                </a>
            </div>
    </div>
</div>
</div>
<div id="twitter_div" style="float:left"></div>
<script src="<?php echo JBrowser::getInstance()->isSSLConnection() ? 'https' : 'http'?>://widgets.twimg.com/j/2/widget.js"></script>
<script>
jQuery(document).ready(function() {
	new TWTR.Widget({
		  id: 'twitter_div',
		  version: 2,
		  type: 'profile',
		  rpp: 4,
		  interval: 30000,
		  width: 300,
		  height: 300,
		  theme: {
		    shell: {
		      background: '#CCCCCC',
		      color: '#000000'
		    },
		    tweets: {
		      background: '#FFFFFF',
		      color: '#000000',
		      links: '#0726eb'
		    }
		  },
		  features: {
		    scrollbar: true,
		    loop: true,
		    live: true,
		    behavior: 'all'
		  }
	}).render().setUser('g4joomla').start();
});
</script>

<div align="center" style="clear: both">
	<br>
	<?php echo sprintf(JText::_('COM_GCALENDAR_FOOTER'), JRequest::getVar('GCALENDAR_VERSION'));?>
</div>