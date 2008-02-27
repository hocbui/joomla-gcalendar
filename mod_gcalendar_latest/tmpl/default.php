<?php

/**
* Google calendar latest events module
* @author allon
* @version $Revision: 1.5.0 $
**/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
?>

<div id="latest_events_content"></div>
<script language="JavaScript" type="text/javascript">
  // <![CDATA[
  var openInNewWindowl = '<?php echo $params->get('openWindow', 1);?>';
  var Backendl = '<?php echo JRoute::_("index.php?option=com_gcalendar&task=content&format=raw&calendarType=xmlUrl&xmlType=basic&calendarName=".urlencode($params->get('name_latest', ''))."&maxResults=".$params->get('max', 5),false);?>';
  var backLinkl = '<?php echo urldecode(JRoute::_("index.php?option=com_gcalendar&task=event&eventID={eventPlace}&calendarName=".$params->get('name_latest', '')."&ctz={ctzPlace}"));?>';
  var checkingtextl = '<?php echo JText::_( 'CHECK_EVENTS' );?>';
  var noEventsTextl = '<?php echo JText::_( 'NO_EVENTS' );?>';
  var busyTextl = '<?php echo JText::_( 'BUSY_EVENT' );?>';
  var publishedl = '<?php echo JText::_( 'PUBLISHED' );?>';
  var dfl = '<?php echo $params->get('dateFormat', 'dd.mm.yyyy HH:MM');?>';
  var dffl = '<?php echo $params->get('dateFormatFull', 'dd.mm.yyyy');?>';
  var showEndDatel = '<?php echo $params->get('showEndDate', 0);?>';
  // ]]>
</script>

<script src="<?php echo JURI::base()."modules/mod_gcalendar_latest/tmpl/date.format.js"?>" language="javascript" type="text/javascript">
</script>
<script src="<?php echo JURI::base()."modules/mod_gcalendar_latest/tmpl/gcalendar.js"?>" language="javascript" type="text/javascript">
</script>