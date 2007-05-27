<?php


/**
* Google calendar latest events module
* @author allon
* @version $Revision: 1.4.1 $
**/

// no direct access
defined('_VALID_MOS') or die('Restricted access');
global $mosConfig_lang;
?>

<div id="status" style="display:none"></div>
<div id="ajaxreader"></div>
<script language="JavaScript" type="text/javascript">
  var calendarName1 = '<?php echo $params->get('name_latest', ''); ?>';
  var rootUrl1 = '<?php echo $mosConfig_live_site;?>';
  var lang = '<?php echo $mosConfig_lang;?>';
</script>
<script src="<?php echo $mosConfig_live_site;?>/modules/gcalendar/gcalendar.js" language="javascript" type="text/javascript">
</script>

