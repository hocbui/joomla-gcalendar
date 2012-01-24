<?php

/**
* Google calendar upcoming events module
* @author allon
* @version $Revision: 1.5.0 $
**/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
?>

<div id="upcoming_events_content"></div>
<script language="JavaScript" type="text/javascript">
  var calendarName='<?php echo $params->get('name', '')?>';
  var rootUrl = '<?php echo JURI::base();?>';
  var maxResults = '<?php echo $params->get('max', 5);?>';
  var openInNewWindow = '<?php echo $params->get('openWindow', 1);?>';
  var Backend = '<?php echo JURI::base()."index.php?option=com_gcalendar&task=content&format=raw&tmpl=component&calendarType=xmlUrl&calendarName=".$params->get('name', '')?>';
  var checkingtext = '<?php echo JText::_( 'CHECK_EVENTS' );?>';
  var noEventsText = '<?php echo JText::_( 'NO_EVENTS' );?>';
  var busyText = '<?php echo JText::_( 'BUSY_EVENT' );?>';
  var df = '<?php echo $params->get('dateFormat', 'dd.mm.yyyy HH:MM');?>';
  var dff = '<?php echo $params->get('dateFormatFull', 'dd.mm.yyyy');?>';
</script>

<script src="<?php echo JURI::base()."modules/mod_gcalendar_upcoming/tmpl/gcalendar.js"?>" language="javascript" type="text/javascript">
</script>