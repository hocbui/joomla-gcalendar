<?php


/**
* Google calendar overview module
* @author allon
* @version $Revision: 1.4.0 $
**/

// no direct access
defined('_VALID_MOS') or die('Restricted access');
?>
<?php

global $database,$url;
global $mod_gcalendar_ov_count;

$params->def( 'url', '' );
$params->def( 'scrolling', 'auto' );
$params->def( 'height', '300' );
$params->def( 'height_auto', '1' );
$params->def( 'width', '100%' );

$name = $params->get('name', '');
$database->setQuery("select id,htmlUrl from #__gcalendar where name='$name'");
$results = $database->loadObjectList();
$url = '';
foreach ($results as $result) {
	$url= $result->htmlUrl;
}

// Create a unique ID for the IFrame, output the javascript function only once
if (!isset( $mod_gcalendar_ov_count )) {
	$mod_gcalendar_ov_count = 0;
?>

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
}

// auto height control
if ( $params->def( 'height_auto' ) ) {
	$load = 'onload="iFrameHeightX(\'blockrandom' . $mod_wrapper_count . '\')" ';
} else {
	$load = '';
}
?>
<iframe
<?php echo $load; ?>
id="blockrandom<?php echo $mod_wrapper_count++; ?>"
src="<?php echo $url; ?>"
width="<?php echo $params->get( 'width' ); ?>"
height="<?php echo $params->get( 'height' ); ?>"
scrolling="<?php echo $params->get( 'scrolling' ); ?>"
align="top"
frameborder="0"
class="wrapper<?php echo $params->get( 'moduleclass_sfx' ); ?>">
<?php echo _CMN_IFRAMES; ?>
</iframe>
