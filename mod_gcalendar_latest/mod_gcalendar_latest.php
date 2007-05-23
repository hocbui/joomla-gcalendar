<?php


/**
* Google calendar latest events module
* @author allon
* @version $Revision: 1.4.0 $
**/

// no direct access
defined('_VALID_MOS') or die('Restricted access');
?>

<div id="status" style="display:none"></div>
<div id="ajaxreader"></div>
<script language="JavaScript" type="text/javascript">
  var calendarName = '<?php echo $params->get('name', ''); ?>';
  var rootUrl = '<?php echo $mosConfig_live_site;?>';
</script>
<script src="<?php echo $mosConfig_live_site;?>/modules/gcalendar/gcalendar.js" language="javascript" type="text/javascript">
</script>

