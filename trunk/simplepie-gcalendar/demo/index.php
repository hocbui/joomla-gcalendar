<?php
$url = $_POST["feedurl"];
$email = $_POST["email"];
?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Simplepie -- GGalendar
</head>
<body>
<h1>Simplepie Google Calendar demo web site</h1>
	<form name="input" action="index.php" method="post">
<table>
<tr>
<td>Feed url:</td>
<td><input type="text" name="feedurl" size="100"></td>
</tr>
<tr>
<td>OR</td>
<td></td>
</tr>
<tr>
<td>EMail address:</td>
<td><input type="text" name="email" size="100"></td>
</tr>
<tr><td><input type="submit" value="Submit"></td></tr>
</table>
</form>
<?php

require_once ('simplepie.inc');
require_once ('simplepie-gcalendar.php');

if(!empty($email))
	$url = SimplePie_GCalendar::create_feed_url($email);
if(empty($url))return;
$content = FALSE;

$feed = new SimplePie_GCalendar();
$feed->set_show_past_events(FALSE);
$feed->set_sort_ascending(TRUE);
$feed->set_orderby_by_start_date(TRUE);
$feed->set_expand_single_events(TRUE);

$feed->set_feed_url($url);

if(!$content){
	$feed->enable_order_by_date(FALSE);
	$feed->init();
	
	$feed->handle_content_type();
	$gcalendar_data = $feed->get_items();
	echo '<p>feed url: '.$feed->feed_url.'</p>';
	
	for ($i = 0; $i < sizeof($gcalendar_data) ; $i++){
		$item = $gcalendar_data[$i];
		$startDate = date("d.m.Y H:i", $item->get_start_time());
		$pubDate = date("d.m.Y H:i", $item->get_publish_date());
		echo '<p>'.$startDate.'<br>Published: '.$pubDate.'<br>'.$item->get_title().'<br>'.$item->get_description().'<hr></p>';
	}
}else{
	//header("content-Type: text/text");
	$content = '<font>';
	$content .= file_get_contents($url);
	$content .= '</font>';
	echo $content;
}
?>
</body></html>
