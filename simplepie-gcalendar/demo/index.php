<?php
$url = $_GET["feedurl"];
$feed_type = $_GET["feedtype"];
?>
	<form name="input" action="index.php" method="get">
	Feed url: <input type="text" name="feedurl" size="100"><br>
	Feed type: <SELECT NAME="feedtype">
		<OPTION VALUE="basic" selected>Basic
		<OPTION VALUE="full">Full	
	</SELECT><br>

	<input type="submit" value="Submit">
	</form>
<?php

$content = FALSE;
require_once ('simplepie.inc');
require_once ('simplepie-gcalendar.php');

$feed = new SimplePie_GCalendar();
$feed->enable_cache('false');
$feed->set_calendar_type($feed_type);

$url = SimplePie_GCalendar::cfg_feed_without_past_events($url);
if($feed->get_calendar_type() == 'full')
	$url = SimplePie_GCalendar::ensure_feed_is_full($url);
$feed->set_feed_url($url);

if(!$content){
	$feed->enable_order_by_date(false);
	$feed->init();
	
	$feed->handle_content_type();
	$gcalendar_data = $feed->get_calendar_items();
	for ($i = 0; $i < sizeof($gcalendar_data) ; $i++){
		$item = $gcalendar_data[$i];
		$gCalDate = date("d.m.Y H:i", $item->get_publish_date());
		if($feed->get_calendar_type() == 'full'){
			$gCalDate = date("d.m.Y H:i", $item->get_start_time());
		}
		echo '<p>'.$gCalDate.'<br>'.$item->get_title().'<hr></p>';
	}
}else{
	//header("content-Type: text/text"); 
	$feed = file_get_contents($url);
	echo $feed;
}
?>