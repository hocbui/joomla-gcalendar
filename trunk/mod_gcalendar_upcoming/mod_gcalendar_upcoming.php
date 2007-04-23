<?php


/**
* Google calendar upcoming events module
* @author allon
* @version $Revision: 1.3.0 $
**/

// no direct access
defined('_VALID_MOS') or die('Restricted access');
?>

<div id="status"></div>
<div id="gcalajax"></div>
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
  var maxResults = '<?php echo $params->get('max', 5);?>';
  var openInNewWindow = '<?php echo $params->get('openWindow', 5);?>';
</script>

<script src="<?php echo $mosConfig_live_site;?>/modules/gcalendar_upcoming/gcalajax.js" language="javascript" type="text/javascript">
</script>

