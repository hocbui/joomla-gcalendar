<?php
/**
 * Google calendar component
 * @author allon
 * @version $Revision: 2.0.0 $
 */

 // no direct access
defined('_JEXEC') or die('Restricted access');

$p= parse_url($this->gcalendar);

$tz = '';
if(!empty($this->timezone))$tz='&ctz='.$this->timezone;
$params   = JComponentHelper::getParams('com_languages');
$lg = $params->get('site', 'en-GB');
$lg = '&hl='.$lg;
$url = $p['scheme'] . '://' . $p['host'] . '/calendar/event?eid=' . $this->eventID . $tz.$lg;
?>

<iframe
id="gcalendarEvent"
name="iframe"
src="<?php echo $url; ?>"
width="100%"
height="700"
align="top"
frameborder="0">
<?php echo JText::_( 'NO_IFRAMES' ); ?>
</iframe>
