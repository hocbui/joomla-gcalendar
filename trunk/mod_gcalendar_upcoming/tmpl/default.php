<?php

/**
* Google calendar overview module
* @author allon
* @version $Revision: 1.5.0 $
**/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' ); ?>

?>

<div id="st"></div>
<div id="gcalajax"></div>
<script language="JavaScript" type="text/javascript">
  var calendarName = '<?php echo $params->get('name', '')?>';
  var rootUrl = '<?php echo $mainframe->getCfg( 'live_site' );?>';
  var maxResults = '<?php echo $params->get('max', 5);?>';
  var openInNewWindow = '<?php echo $params->get('openWindow', 5);?>';
  var lang = '<?php echo $mosConfig_lang;?>';
</script>

<script src="<?php echo dirname(__FILE__).DS."tmpl/gcalajax.js" language="javascript" type="text/javascript">
</script>