<?php


/**
* Google calendar latest events module
* @author allon
* @version $Revision: 1.4.2 $
**/

// no direct access
defined('_VALID_MOS') or die('Restricted access');
global $mosConfig_absolute_path;

// Get the right language if it exists
if (file_exists($mosConfig_absolute_path."/modules/mod_gcalendar_latest/languages/".$mosConfig_lang.".php")){
	include_once($mosConfig_absolute_path."/modules/mod_gcalendar_latest/languages/".$mosConfig_lang.".php");
}else{
	include_once($mosConfig_absolute_path."/modules/mod_gcalendar_latest/languages/english.php");
}
?>

<div id="latest_events_content"></div>
<script language="JavaScript" type="text/javascript">
  var calendarNamel='<?php echo $params->get('name_latest', '')?>';
  var rootUrll = '<?php echo $mosConfig_live_site.'/';?>';
  var maxResultsl = '<?php echo $params->get('max', 5);?>';
  var openInNewWindowl = '<?php echo $params->get('openWindow', 0);?>';
  var Backendl = '<?php echo $mosConfig_live_site."/index2.php?option=com_gcalendar&task=content&no_html=1&calendarType=xmlUrl&xmlType=basic&calendarName=".$params->get('name_latest', '')?>';
  var checkingtextl = '<?php echo _GCALENDAR_LATEST_CHECK_EVENTS;?>';
  var noEventsTextl = '<?php echo _GCALENDAR_LATEST_NO_EVENTS;?>';
  var busyTextl = '<?php echo _GCALENDAR_LATEST_BUSY_EVENT;?>';
  var publishedl = '<?php _GCALENDAR_LATEST_PUBLISHED;?>';
  var dfl = '<?php echo $params->get('dateFormat', 'dd.mm.yyyy HH:MM');?>';
  var dffl = '<?php echo $params->get('dateFormatFull', 'dd.mm.yyyy');?>';
</script>

<script src="<?php echo $mosConfig_live_site."/modules/mod_gcalendar_latest/gcalendar.js"?>" language="javascript" type="text/javascript">
</script>
