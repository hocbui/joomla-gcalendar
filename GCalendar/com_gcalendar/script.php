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
 * @copyright 2007-2012 Allon Moritz
 * @since 2.6.0
 */

defined('_JEXEC') or die('Restricted access');

class Com_GCalendarInstallerScript{
	
	public function install(){
	?>
	    <div style="margin:0px auto; text-align:center; width:360px;">
	      <img src="../media/com_gcalendar/images/48-calendar.png" alt="GCalendar Logo" />
	      <h3 class="headline oktext">GCalendar was installed successfully.</h3>
	      <p>Open GCalendar manager:</p>
	      <div class="button2-left" style="margin-left:70px;">
	        <div class="blank">
	          <a title="Start" onclick="location.href='index.php?option=com_gcalendar';" href="#">Start now!</a>
	        </div>
	      </div>
	      <div class="button2-left jg_floatright" style="margin-right:70px;">
	        <div class="blank">
	          <a title="Languages" onclick="location.href='index.php?option=com_gcalendar&view=support';" href="#">Support</a>
	        </div>
	      </div>
	      <div style="clear:both;"></div>
	    </div>
	 <?php
	}
	
	public function update(){
	?>
	    <div style="margin:0px auto; text-align:center; width:360px;">
	      <img src="../media/com_gcalendar/images/48-calendar.png" alt="GCalendar Logo" />
	      <h3 class="headline oktext">GCalendar was updated successfully.</h3>
	      <p>Open GCalendar manager:</p>
	      <div class="button2-left" style="margin-left:70px;">
	        <div class="blank">
	          <a title="Start" onclick="location.href='index.php?option=com_gcalendar';" href="#">Start now!</a>
	        </div>
	      </div>
	      <div class="button2-left jg_floatright" style="margin-right:70px;">
	        <div class="blank">
	          <a title="Languages" onclick="location.href='index.php?option=com_gcalendar&view=support';" href="#">Support</a>
	        </div>
	      </div>
	      <div style="clear:both;"></div>
	    </div>
	 <?php
	}
}