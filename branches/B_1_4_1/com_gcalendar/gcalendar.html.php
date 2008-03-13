<?php


/**
* Google calendar component
* @author allon
* @version $Revision: 1.4.5 $
**/

// no direct access
defined('_VALID_MOS') or die('Restricted access');

class HTML_gcalendar {

	function displayCalendar(& $params, & $menu) {
?>
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

		// displays back button
		mosHTML :: BackButton($params);
	}
}
?>