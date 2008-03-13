<?php

/**
* Google calendar component
* @author allon
* @version $Revision: 1.4.5 $
**/

// ensure this file is being included by a parent file
defined('_VALID_MOS') or die('Direct Access to this location is not allowed.');

class HTML_gcalendar {

	function showCalendars($rows, $option) {
?>
        <form action="index2.php" method="post" name="adminForm">
        <table cellpadding="4" cellspacing="0" border="0" width="100%">
                <tr> 
                        <td width="100%" class="sectionname">Google calendars</td>
                </tr>
        </table>
        <table cellpadding="4" cellspacing="0" border="0" width="100%" class="adminlist">
                <tr> 
                        <th width="20" nowrap="nowrap">#</th>
                        <th width="20" class="title" nowrap="nowrap"><input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($rows); ?>);" /></th>
                        <th width="20%" class="title" nowrap="nowrap">NAME</th>
                        <th width="40%" class="title" nowrap="nowrap">HTML URL</th>
                        <th width="40%" class="title" nowrap="nowrap">XML URL</th>
                </tr>
<?php

		$k = 0;
		for ($i = 0, $n = count($rows); $i < $n; $i++) {
			$row = $rows[$i];
			$pageNav = 0;
?>
                <tr class="<?php echo "row$k"; ?>"> 
                        <td width="20"><?php echo $i+1+$pageNav->limitstart; ?></td>
                        <td width="20"><input type="checkbox" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo $row->id; ?>" onclick="isChecked(this.checked);" /></td>
                        <td width="20%"><a href="#edit" onclick="return listItemTask('cb<?php echo $i;?>','edit')"><?php echo $row->name; ?></a></td>
                        <td width="20%"><?php echo $row->htmlUrl; ?></td>
                        <td width="20%"><?php echo $row->xmlUrl; ?></td>
                </tr>
    <?php

			$k = 1 - $k;
		}
?>

  </table>
  <input type="hidden" name="option" value="<?php echo $option; ?>" />
  <input type="hidden" name="task" value="" />
  <input type="hidden" name="boxchecked" value="0" />
</form>  
<div align="center">
<br><img src="components/com_gcalendar/images/gcalendar.gif" width="143" height="57"><br>
  &copy;&nbsp;&nbsp;2008 <a href="http://www.allon.ch" target="_blank">allon moritz</a>
  </div>
<?php

	}

	function editCalendar($row, $option) {
?>
        <script language="javascript" type="text/javascript">
                function submitbutton(pressbutton) {
                        var form = document.adminForm;
                        if (pressbutton == 'cancel') {
                                submitform( pressbutton );
                                return;
                        }

                        // do field validation
                        if (form.name.value == ""){
                                alert( "You must provide a name." );
                        } else if (form.htmlUrl.value == ""){
                                alert( "You must provide a URL address." );
                        } else {
                                submitform( pressbutton );
                        }
                }
        </script>
<form action="index2.php" method="POST" name="adminForm">
        <table cellpadding="4" cellspacing="0" border="0" width="100%">
                <tr>
                        <td width="100%"><span class="sectionname">Edit Google calendar</span></td>
                </tr>
        </table>
        <table cellpadding="4" cellspacing="1" border="0" width="100%" class="adminform">
                <tr>
                        <td width="20%" align="right">Name:</td>
                        <td width="20%"><input class="inputbox" type="text" name="name" size="170" maxlength="100" value="<?php echo $row->name; ?>" /></td>
                <tr>
                        <td align="right">HTML URL:</td>
                        <td><input class="inputbox" type="text" name="htmlUrl" size="170" maxlength="300" value="<?php echo $row->htmlUrl; ?>" />
        </td>
                </tr>
                <tr>
                        <td align="right">XML URL:</td>
                        <td><input class="inputbox" type="text" name="xmlUrl" size="170" maxlength="300" value="<?php echo $row->xmlUrl; ?>" />
        </td>
                </tr>
        </table>
        <input type="hidden" name="option" value="<?php echo $option; ?>" />
        <input type="hidden" name="id" value="<?php echo $row->id; ?>" />
        <input type="hidden" name="task" value="" />
        </form>
        <div align="center">
<br><img src="components/com_gcalendar/images/gcalendar.gif" width="143" height="57"><br>
  &copy;&nbsp;&nbsp;2008 <a href="http://www.allon.ch" target="_blank">allon moritz</a>
  </div>
<?php


	}
}