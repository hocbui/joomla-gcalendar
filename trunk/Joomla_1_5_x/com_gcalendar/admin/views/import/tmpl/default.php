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

defined('_JEXEC') or die('Restricted access');

require_once 'Zend/Loader.php';
Zend_Loader::loadClass('Zend_Gdata_AuthSub');

if(JRequest::getVar('isLogin')==='FALSE'){
	$u = JFactory::getURI();
	$next = JRoute::_( $u->toString().'?option=com_gcalendar&task=import');
	$scope = 'http://www.google.com/calendar/feeds/';
	$session = true;
	$secure = false;
	$authSubUrl = Zend_Gdata_AuthSub::getAuthSubTokenUri($next, $scope, $secure,
	$session);
	echo "<a href=\"{$authSubUrl}\">Please Login to access the calendar data.</a>";
}else{
	/**
	 * Generates a HTML check box or boxes
	 * @param array An array of objects
	 * @param string The value of the HTML name attribute
	 * @param string Additional HTML attributes for the <select> tag
	 * @param mixed The key that is selected. Can be array of keys or just one key
	 * @param string The name of the object variable for the option value
	 * @param string The name of the object variable for the option text
	 * @param boolean The porlinea of the object variable for the option text   *
	 * @returns string HTML for the select list
	 */
	function checkBoxList( &$arr, $tag_name, $tag_attribs, $selected=null, $key='value', $text='text', $porlinea=true ) {
		reset( $arr );
		$html = "";
		for ($i=0, $n=count( $arr ); $i < $n; $i++ ) {
			$k = $arr[$i]->$key;
			$t = $arr[$i]->$text;
			$id = @$arr[$i]->id;

			$extra = '';
			$extra .= $id ? " id=\"" . $arr[$i]->id . "\"" : '';
			if (is_array( $selected )) {
				foreach ($selected as $obj) {
					$k2 = $obj;
					if ($k == $k2) {
						$extra .= " checked ";
						break;
					}
				}
			} else {
				$extra .= ($k == $selected ? " checked " : '');
			}
			$html .= "\n\t<input type=\"checkbox\" name=\"$tag_name\" value=\"".$k."\"$extra $tag_attribs />" . $t;
			if ($porlinea) {
				$html .= "<br>";
			}
		}
		$html .= "\n";
		return $html;
	}
	if(!is_array($this->items)){
		echo 'No data found!';
	}else{
		//echo checkBoxList($this->items, 'calendars[]','id="calendars[]"',null,'id','calendar_id',true);
	
	
	 ?>
	 <form action="index.php" method="post" name="adminForm">
	 <div id="editcell">
	 <table class="adminlist">
	 <thead>
		<tr>
		<th width="5"><?php echo JText::_( 'ID' ); ?></th>
		<th width="20"><input type="checkbox" name="toggle" value=""
		onclick="checkAll(<?php echo count( $this->items ); ?>);" /></th>
		<th><?php echo JText::_( 'CALENDAR_NAME' ); ?></th>
		<th align="left"><?php echo JText::_( 'CALENDAR_DETAILS' ); ?></th>
		</tr>
		</thead>
		<?php
		$k = 0;
		for ($i=0, $n=count( $this->items ); $i < $n; $i++)
		{
		$row = &$this->items[$i];
		$checked 	= JHTML::_('grid.id',   $i, $row->calendar_id );
		$link 		= JRoute::_( 'index.php?option=com_gcalendar&controller=gcalendar&task=edit&cid[]='. $row->id );

		?>
		<tr class="<?php echo "row$k"; ?>">
		<td><?php echo $row->calendar_id; ?></td>
		<td><?php echo $checked; ?></td>
		<td><a href="<?php echo $link; ?>"><?php echo $row->name; ?></a></td>
		<td>
		<table>
		<tr>
		<td><b><?php echo JText::_( 'Calendar ID' ); ?>:</b></td>
		<td><?php echo $row->calendar_id; ?></td>
		</tr>
		<tr>
		<td><b><?php echo JText::_( 'Domaine' ); ?>:<b></td>
		<td><?php echo $row->domaine; ?></td>
		</tr>
		<tr>
		<td><b><?php echo JText::_( 'Magic Cookie' ); ?>:</b></td>
		<td><?php echo $row->magic_cookie; ?></td>
		</tr>
		<tr>
		<td><b><?php echo JText::_( 'Color' ); ?>:</b></td>
		<td><?php echo $row->color; ?></td>
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

		<input type="hidden" name="option" value="com_gcalendar" /> <input
		type="hidden" name="task" value="" /> <input type="hidden"
		name="boxchecked" value="0" /> <input type="hidden" name="controller"
		value="import" /></form>
		<?php
	}
	?>
<div align="center"><br>
<img src="components/com_gcalendar/images/gcalendar.gif" width="143"
	height="57"><br>
&copy;&nbsp;&nbsp;2009 <a href="http://gcalendar.allon.ch"
	target="_blank">allon moritz</a></div>

	<?php
}
?>
