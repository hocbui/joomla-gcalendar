<?php
$url = $_GET["feedurl"];
$feed_type = $_GET["feedtype"];
?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Simplepie -- GGalendar
</head>
<body>
<h1>Simplepie Google Calendar demo web site</h1>
	<form name="input" action="index.php" method="get">
<table>
<tr>
<td>Feed url:</td>
<td><input type="text" name="feedurl" size="100"></td>
</tr>
<tr>
<td>Feed type:</td>
<td><SELECT NAME="feedtype">
	<OPTION VALUE="basic" selected>Basic
	<OPTION VALUE="full">Full	
</SELECT></td>
</tr>
<tr><td><input type="submit" value="Submit"></td></tr>
</table>
</form>
<?php
if(empty($url))return;
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
		echo '<p>'.$gCalDate.'<br>'.$item->get_title().'<br>'.$item->get_description().'<hr></p>';
	}
}else{
	//header("content-Type: text/text"); 
	$feed = file_get_contents($url);
	echo $feed;
}
?>
</body></html>
