<?php
/**
 * GCalendar is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * GCalendar is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GCalendar.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Eric Horne
 * @copyright 2009 Eric Horne 
 * @version $Revision: 2.1.5 $
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

if(!empty($error)){
	echo $error;
	return;
}

if (!$gcalendar_item) {
	echo $params->get("no_event", "No events found.");
	return;
}

$modtmplpath = "/modules/mod_gcalendar_next/tmpl/";

$targetDate = $gcalendar_item->get_start_date();
$now = false;
if ($targetDate < time()) {
	$targetDate = $gcalendar_item->get_end_date();
	$now = true;
}

$layout = $params->get(($now) ? 'output_now' : 'output');
$class = ($now) ? "countdown now" : "countdown";
$mapREs = array();
$mapValues = array();

if (preg_match_all('/{{([^}]+)}}/', $layout, $mapREs)) {
	foreach ($mapREs[1] as $mapRE) {
		array_push($mapValues, call_user_func(array($gcalendar_item, 'get_' . $mapRE)));
	}
	
	$layout = str_replace($mapREs[0], $mapValues, $layout);
}

$objid = "countdown-" . $module->id;

GCalendarUtil::loadJQuery();
$document->addScript($modtmplpath . 'jquery.countdown.js');
$document->addStyleSheet($modtmplpath . 'jquery.countdown.css');

?>

<div class="gcalendar_next">

<script type="text/javascript">
jQuery(function() {
	var targetDate; 
	targetDate = new Date("<?php print date("D,d M Y H:i:s", $targetDate);?>"); 
	jQuery('#<?php print $objid; ?>').countdown({until: targetDate, 
				       description: '<?php print $gcalendar_item->get_title();?>', 
 				       layout: '<?php print $layout; ?>', 
				       <?php print $params->get('style_parameters', "format: 'dHMS'"); ?>});
});
</script>
	<div id="<?php print $objid; ?>" class="<?php print $class; ?>">you have javascript disabled</div>
</div>
