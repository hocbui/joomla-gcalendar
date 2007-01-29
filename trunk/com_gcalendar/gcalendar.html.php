<?php


/**
* Google calendar component
* @author allon
* @version $Revision: 1.2.0 $
**/

// no direct access
defined('_VALID_MOS') or die('Restricted access');

class HTML_gcalendar {

	function displayCalendar(& $params, & $menu) {
?>
		<script language="javascript" type="text/javascript">
		function iFrameHeight() {
			var h = 0;
			if ( !document.all ) {
				h = document.getElementById('blockrandom').contentDocument.height;
				document.getElementById('blockrandom').style.height = h + 60 + 'px';
			} else if( document.all ) {
				h = document.frames('blockrandom').document.body.scrollHeight;
				document.all.blockrandom.style.height = h + 20 + 'px';
			}
		}
		</script>
		<div class="contentpane<?php echo $params->get( 'pageclass_sfx' ); ?>">

		<?php

		if ($params->get('page_title')) {
?>
			<div class="componentheading<?php echo $params->get( 'pageclass_sfx' ); ?>">
			<?php echo $params->get( 'header' ); ?>
			</div>
			<?php

		}
?>
		<iframe
		<?php echo $row->load; ?>
		id="blockrandom"
		name="iframe"
		src="<?php echo $params->get( 'htmlUrl' ); ?>"
		width="<?php echo $params->get( 'width' ); ?>"
		height="<?php echo $params->get( 'height' ); ?>"
		scrolling="<?php echo $params->get( 'scrolling' ); ?>"
		align="top"
		frameborder="0"
		class="wrapper<?php echo $params->get( 'pageclass_sfx' ); ?>">
		<?php echo _CMN_IFRAMES; ?>
		</iframe>

		</div>
		<?php

		// displays back button
		mosHTML :: BackButton($params);
	}
}
?>