<?php // no direct access

/**
* Google calendar overview module
* @author allon
* @version $Revision: 1.5.0 $
**/

defined( '_JEXEC' ) or die( 'Restricted access' ); ?>

<script language="javascript" type="text/javascript">
function iFrameHeightX( iFrameId ) {
	var h = 0;
	if ( !document.all ) {
		h = document.getElementById(iFrameId).contentDocument.height;
		document.getElementById(iFrameId).style.height = h + 60 + 'px';
	} else if( document.all ) {
		h = document.frames(iFrameId).document.body.scrollHeight;
		document.all[iFrameId].style.height = h + 20 + 'px';
	}
}
</script>

<?php

// auto height control
if ( $params->def( 'height_auto' ) ) {
	$load = 'onload="iFrameHeightX(\'blockrandom' . $mod_wrapper_count . '\')" ';
} else {
	$load = '';
}
?>
<iframe
<?php echo $load; ?>
id="mod_gcalendar"
src="<?php echo $calendar; ?>"
width="<?php echo $params->get( 'width' ); ?>"
height="<?php echo $params->get( 'height' ); ?>"
scrolling="<?php echo $params->get( 'scrolling' ); ?>"
align="top"
frameborder="0"
class="mod_gcalendar<?php echo $params->get( 'moduleclass_sfx' ); ?>">
<?php echo JText::_( 'NO_IFRAMES' ); ?>
</iframe>