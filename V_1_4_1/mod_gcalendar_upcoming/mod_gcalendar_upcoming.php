<?php


/**
* Google calendar upcoming events module
* @author allon
* @version $Revision: 1.4.1 $
**/

// no direct access
defined('_VALID_MOS') or die('Restricted access');
global $mosConfig_lang;
?>

<div id="st"></div>
<div id="gcalajax"></div>
<script language="JavaScript" type="text/javascript">
  var calendarName = '<?php echo $params->get('name', '')?>';
  var rootUrl = '<?php echo $mosConfig_live_site;?>';
  var maxResults = '<?php echo $params->get('max', 5);?>';
  var openInNewWindow = '<?php echo $params->get('openWindow', 5);?>';
  var lang = '<?php echo $mosConfig_lang;?>';
</script>

<script src="<?php echo $mosConfig_live_site;?>/modules/gcalendar_upcoming/gcalajax.js" language="javascript" type="text/javascript">
</script>

