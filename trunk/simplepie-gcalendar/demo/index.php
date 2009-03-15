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
 * @author Allon Moritz
 * @copyright 2007-2009 Allon Moritz
 * @version $Revision: 0.2.0 $
 */
 
$url = $_POST["feedurl"];
$email = $_POST["email"];
$show_past_events = $_POST["past"];
$sort_ascending = $_POST["asc"];
$order_by = $_POST["order"];
$expand_single_events = $_POST["expand"];
$query = $_POST["query"];
?>
<html>
<head>
<title>Simplepie -- GGalendar</title>
</head>
<body>
<h1>Simplepie Google Calendar demo web site</h1>
	<form name="input" action="index.php" method="post">
<table>
<tr>
<td>Feed url:</td>
<td><input type="text" name="feedurl" size="100" value="<?php echo $url; ?>"></td>
</tr>
<tr>
<td>OR</td>
<td></td>
</tr>
<tr>
<td>EMail address:</td>
<td><input type="text" name="email" size="100" value="<?php echo $email; ?>"></td>
</tr>
<tr>
<tr>
<td>Query:</td>
<td><input type="text" name="query" size="100" value="<?php echo $query; ?>"></td>
</tr>
<tr>
<td>Show past events:</td>
<td><input type="radio" name="past" value="1">True<br>
<input type="radio" name="past" value="0" checked>False</td>
</tr>
<tr>
<td>Sort ascending:</td>
<td><input type="radio" name="asc" value="1" checked>True<br>
<input type="radio" name="asc" value="0">False</td>
</tr>
<tr>
<td>Order by start date:</td>
<td><input type="radio" name="order" value="1" checked>True<br>
<input type="radio" name="order" value="0">False</td>
</tr>
<tr>
<td>Expand single events:</td>
<td><input type="radio" name="expand" value="1" checked>True<br>
<input type="radio" name="expand" value="0">False</td>
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
$feed->set_show_past_events($show_past_events==1);
$feed->set_sort_ascending($sort_ascending==1);
$feed->set_orderby_by_start_date($order_by==1);
$feed->set_expand_single_events($expand_single_events==1);
$feed->set_cal_query($query);

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
	$content = '<font>THIS<br>';
	$content .= file_get_contents($url);
	$content .= '</font>';
	echo $content;
}
?>
</body></html>
