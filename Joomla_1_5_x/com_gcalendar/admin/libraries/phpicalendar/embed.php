<?php
class PHPIcalendar {

	function load($configs){
		if (!defined('BASE')) define('BASE', JPATH_ADMINISTRATOR.DS.'components'.DS.'com_gcalendar'.DS.'libraries'.DS.'phpicalendar'.DS);
		// if (!defined('BASE')) define('BASE','./');
		include_once(BASE.'functions/init.inc.php');

		if ($phpiCal_config->printview_default == 'yes') {
			$theview ="print.php";
		} else {
			$check = array ('day', 'week', 'month', 'year');
			if (in_array($phpiCal_config->default_view, $check)) {
				include_once(BASE.$phpiCal_config->default_view . '.php');
			}
		}
	}
}
?>