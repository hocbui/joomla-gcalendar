<?php


/**
* Google calendar component
* @author allon
* @version $Revision: 2.0.0 $
**/

// no direct access
defined('_VALID_MOS') or die('Restricted access');

class HTML_gcalendar {

	function displayCalendar(& $params, & $menu) {
?>
		<div class="contentpane<?php echo $params->get( 'pageclass_sfx' ); ?>">

		<iframe
		id="gcalendar_content"
		src="<?php echo $params->get( 'htmlUrl' ); ?>"
		width="<?php echo $params->get( 'width' ); ?>"
		height="<?php echo $params->get( 'height' ); ?>"
		scrolling="<?php echo $params->get( 'scrolling' ); ?>"
		align="top"
		frameborder="0"
		class="gcalendar<?php echo $params->get( 'pageclass_sfx' ); ?>">
		<?php echo _CMN_IFRAMES; ?>
		</iframe>

		</div>
		<?php
	}
}
?>