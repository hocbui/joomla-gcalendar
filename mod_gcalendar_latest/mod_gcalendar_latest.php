<?php


/**
* Google calendar latest events module
* @author allon
* @version $Revision: 1.3.0 $
**/

// no direct access
defined('_VALID_MOS') or die('Restricted access');
?>

<div id="status" style="display:none"></div>
<div id="ajaxreader"></div>
<script language="JavaScript" type="text/javascript">
<?php

global $database,$url;
$name = $params->get('name', '');
$database->setQuery("select id,xmlUrl from #__gcalendar where name='$name'");
$results = $database->loadObjectList();
$url = '';
foreach ($results as $result) {
	$url= $result->xmlUrl;
}
?>
  var calendarUrl = '<?php echo $url;?>';
  var calendarName = '<?php echo $name;?>';
  var rootUrl = '<?php echo $mosConfig_live_site;?>';
</script>
<script src="<?php echo $mosConfig_live_site;?>/modules/gcalendar/gcalendar.js" language="javascript" type="text/javascript">
</script>

