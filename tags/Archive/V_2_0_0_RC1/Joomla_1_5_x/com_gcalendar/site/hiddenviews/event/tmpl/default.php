<?php
/**
 * Google calendar component
 * @author allon
 * @version $Revision: 2.0.0 $
 */

 // no direct access
defined('_JEXEC') or die('Restricted access');

$p= parse_url($this->gcalendar);
	
if($this->timezone != '')$this->timezone='&ctz='.$this->timezone;
$url = $p['scheme'] . '://' . $p['host'] . '/calendar/event?eid=' . $this->eventID . $this->timezone;

?>

<script language="javascript" type="text/javascript">
function iFrameHeightGCEvent() {
	var h = 0;
	if ( !document.all ) {
		h = document.getElementById('gcalendarEvent').contentDocument.height;
		document.getElementById('gcalendarEvent').style.height = h + 60 + 'px';
	} else if( document.all ) {
		h = document.frames('gcalendarEvent').document.body.scrollHeight;
		document.all.gcalendarEvent.style.height = h + 20 + 'px';
	}
}
</script>

<iframe onload="iFrameHeightGCEvent()"
id="gcalendarEvent"
name="iframe"
src="<?php echo $url; ?>"
width="100%"
height="700"
align="top"
frameborder="0">
<?php echo JText::_( 'NO_IFRAMES' ); ?>
</iframe>
