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
 * @version $Revision: 0.3.0 $
 */

require_once ('simplepie.inc');
require_once ('simplepie-ganalytics.php');

$uname = $_GET["uname"];
$passwd = $_GET["pwd"];
$dimensions = $_GET["dimensions"];
$metrics = $_GET["metrics"];
$sort = $_GET["sort"];
$max = $_GET["max"];
$profileID = $_GET["profileid"];

$uname = 'allon.moritz';
$passwd = '';
$dimensions = 'ga:country';
$metrics = 'ga:visits';
$sort = '-ga:visits';
$max = 10;
$profileID = 21342967;
?>
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<title>Simplepie -- GAnalytics</title>
<link rel="stylesheet" href="sp-ganalytics.css" type="text/css" />
</head>
<body>
<h1>Simplepie Google Analytics demo web site</h1>
<form name="input" action="index.php" method="get">
<table>
	<tr>
		<td>Username:</td>
		<td colspan="4"><input type="text" name="uname" size="100"
			value="<?php echo $uname; ?>"></td>
	</tr>
	<tr>
		<td>Password:</td>
		<td colspan="4"><input type="password" name="passwd" size="100"
			value="<?php echo $passwd; ?>"></td>
	</tr>
	<tr>
		<td colspan="5">
		<hr />
		</td>
	</tr>
	<tr>
		<td>Dimensions:</td>
		<td colspan="4"><input type="text" name="dimensions" size="100"
			value="<?php echo $dimensions; ?>"></td>
	</tr>
	<tr>
		<td>Metrics:</td>
		<td colspan="4"><input type="text" name="metrics" size="100"
			value="<?php echo $metrics; ?>"></td>
	</tr>
	<tr>
		<td>Sort:</td>
		<td colspan="4"><input type="text" name="sort" size="100"
			value="<?php echo $sort; ?>"></td>
	</tr>
	<tr>
		<td>Max:</td>
		<td colspan="4"><input type="text" name="max" size="100"
			value="<?php echo $max; ?>"></td>
	</tr>
	<tr>
		<td><input type="submit" value="Submit"></td>
	</tr>
</table>
</form>
<?php
if(!empty($uname)){
	$feed = new SimplePie_GAnalytics();
	$feed->set_login($uname, $passwd);
	$feed->set_parameters($dimensions, $metrics, $max, $sort);
	$feed->set_profile_id($profileID);
	$feed->set_start_date(strtotime('-1 month'));
	$feed->set_end_date(time());
	$feed->enable_cache(false);
	$feed->set_cache_duration(0);
	$feed->init();

	$feed->handle_content_type();
	$data = $feed->get_items();
	echo '<p><b>feed url: '.$feed->feed_url.'</b></p>';

	if($feed->error()){
		echo $feed->error();
	}

	for ($i = 0; $i < sizeof($data); $i++){
		$item = $data[$i];
		//Make any URLs used in the description also clickable
		$desc = eregi_replace('(((f|ht){1}tp://)[-a-zA-Z0-9@:%_\+.~#?,&//=]+)','<a href="\\1">\\1</a>', $item->get_description());
		echo $item->get_title()."<br/>\n".$desc;
	}
}
?>
</body>
</html>
