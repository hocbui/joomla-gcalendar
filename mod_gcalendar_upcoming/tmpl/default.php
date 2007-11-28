<?php

/**
* Google calendar overview module
* @author allon
* @version $Revision: 1.5.0 $
**/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
?>

<div id="st"></div>
<div id="gcalajax"></div>
<script language="JavaScript" type="text/javascript">
  var rootUrl = '<?php echo JURI::base();?>';
  var maxResults = '<?php echo $params->get('max', 5);?>';
  var openInNewWindow = '<?php echo $params->get('openWindow', 1);?>';
  var Backend = '<?php echo JURI::base()."index.php?option=com_gcalendar&task=content&format=raw&calendarType=xmlUrl&calendarName=".$params->get('name', '')?>';
</script>

<script src="<?php echo JURI::base()."modules/mod_gcalendar_upcoming/tmpl/gcalendar.js"?>" language="javascript" type="text/javascript">
</script>