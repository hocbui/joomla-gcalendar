<?php

/**
* Google calendar upcoming events module
* @author allon
* @version $Revision: 1.4.5 $
**/

// no direct access
defined('_VALID_MOS') or die('Restricted access');
global $mosConfig_absolute_path;

// Get the right language if it exists
if (file_exists($mosConfig_absolute_path."/modules/mod_gcalendar_upcoming/languages/".$mosConfig_lang.".php")){
	include_once($mosConfig_absolute_path."/modules/mod_gcalendar_upcoming/languages/".$mosConfig_lang.".php");
}else{
	include_once($mosConfig_absolute_path."/modules/mod_gcalendar_upcoming/languages/english.php");
}
?>

<div id="upcoming_events_content"></div>
<script language="JavaScript" type="text/javascript">
  // <![CDATA[
  var openInNewWindow = '<?php echo $params->get('openWindow', 0);?>';
  var Backend = '<?php echo "index2.php?option=com_gcalendar&task=content&no_html=1&calendarType=xmlUrl&calendarName=".urlencode($params->get('name', ''))."&maxResults=".$params->get('max', 5);?>';
  var backLink = '<?php echo urldecode("index.php?option=com_gcalendar&eventID={eventPlace}&name=".$params->get('name', '')."&ctz={ctzPlace}");?>';
  var checkingtext = '<?php echo _GCALENDAR_UPCOMING_CHECK_EVENTS;?>';
  var noEventsText = '<?php echo _GCALENDAR_UPCOMING_NO_EVENTS;?>';
  var busyText = '<?php echo _GCALENDAR_UPCOMING_BUSY_EVENT;?>';
  var df = '<?php echo $params->get('dateFormat', 'dd.mm.yyyy HH:MM');?>';
  var dff = '<?php echo $params->get('dateFormatFull', 'dd.mm.yyyy');?>';
  var showEndDate = '<?php echo $params->get('showEndDate', 0);?>';
  // ]]>
</script>
<script src="<?php echo $mosConfig_live_site."/modules/mod_gcalendar_upcoming/date.format.js"?>" language="javascript" type="text/javascript">
</script>
<script src="<?php echo $mosConfig_live_site;?>/modules/mod_gcalendar_upcoming/gcalendar.js" language="javascript" type="text/javascript">
</script>
