<?php
/**
 * GAnalytics is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * GAnalytics is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GAnalytics.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Allon Moritz
 * @copyright 2007-2009 Allon Moritz
 * @version $Revision: 0.1.0 $
 */

require_once ('simplepie.inc');
require_once ('simplepie-ganalytics.php');

$uname = $_GET["uname"];
$passwd = $_GET["passwd"];
$dimensions = $_GET["dimensions"];
$metrics = $_GET["metrics"];
$sort = $_GET["sort"];
$max = $_GET["max"];
$profileID = $_GET["profileid"];
$auth = $_GET["auth"];

if($dimensions == null){
	$dimensions = 'ga:country';
	$metrics = 'ga:visits';
	$sort = '-ga:visits';
	$max = 10;
}
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
<?php if(($uname == null || $passwd == null) && $auth == null){?>
	<tr>
		<td>Google Account Username:</td>
		<td colspan="4"><input type="text" name="uname" size="50px"
			onfocus="value=''" value="something like demo@gmail.com"></td>
	</tr>
	<tr>
		<td>Password:</td>
		<td colspan="4"><input type="password" name="passwd" size="50px"
			value="<?php echo $passwd; ?>"></td>
	</tr>
	<?php }else{?>
	<tr>
		<td colspan="5"><a target="_blank"
			href="http://code.google.com/apis/analytics/docs/gdata/gdataExplorer.html">Google
		Analytis Query Explorer</a></td>
	</tr>
	<tr>
		<td>Dimensions:</td>
		<td colspan="4"><input type="text" name="dimensions" size="50px"
			value="<?php echo $dimensions; ?>"></td>
	</tr>
	<tr>
		<td>Metrics:</td>
		<td colspan="4"><input type="text" name="metrics" size="50px"
			value="<?php echo $metrics; ?>"></td>
	</tr>
	<tr>
		<td>Sort:</td>
		<td colspan="4"><input type="text" name="sort" size="50px"
			value="<?php echo $sort; ?>"></td>
	</tr>
	<tr>
		<td>Max:</td>
		<td colspan="4"><input type="text" name="max" size="50px"
			value="<?php echo $max; ?>"></td>
	</tr>
	<tr>
		<td>Profile</td>
		<td><?php
		$feed = new SimplePie_GAnalytics();
		$feed->set_login($uname, $passwd);
		$feed->set_authorization($auth);
		$feed->enable_cache(false);
		$feed->set_cache_duration(0);
		$feed->init();

		$feed->handle_content_type();
		$data = $feed->get_items();
		if($feed->error()){
			echo $feed->error();
		}else{
			$auth = $feed->get_authorization();
			echo "<select name=\"profileid\" style=\"width: 100%\">\n";
			foreach ($data as $item) {
				echo "\t\t\t<option value=\"".$item->get_profile_id()."\" ";
				if($item->get_profile_id() == $profileID)
				echo "selected";
				echo ">".$item->get_title().' ['.$item->get_profile_id()."]</option>\n";
			}
			echo "</select>\n";
		}
	}?></td>
	</tr>
	<tr>
		<td><input type="submit" value="Submit"></td>
	</tr>
</table>
<input type="hidden" name="auth" value="<?php echo $auth; ?>" /> <?php if($uname != null && $passwd != null){?>
<input type="hidden" name="uname" value="<?php echo $uname; ?>" /> <input
	type="hidden" name="passwd" value="<?php echo $auth; ?>" /> <?php }?></form>
	<?php
	if(!empty($profileID)){
		$feed = new SimplePie_GAnalytics();
		$feed->set_login($uname, $passwd);
		$feed->set_authorization($auth);
		$feed->set_parameters($dimensions, $metrics, $max, $sort);
		$feed->set_profile_id($profileID);
		$feed->set_start_date(strtotime('-1 month'));
		$feed->set_end_date(time());
		$feed->enable_order_by_date(false);
		$feed->enable_cache(false);
		$feed->set_cache_duration(0);
		$feed->init();

		$feed->handle_content_type();
		$data = $feed->get_items();
		echo '<p><b>feed url: '.$feed->feed_url.'</b></p>';

		if($feed->error()){
			echo $feed->error();
		}

		if(empty($data)){
			echo 'no data found';
		}else{
			$dimensions = $data[0]->get_available_dimension_names();
			$metrics = $data[0]->get_available_metric_names();

			echo "<table class=\"content_table\"><tr>\n";
			foreach ($dimensions as $dimension) {
				echo "<th>".substr($dimension, 3)."</th>";
			}
			foreach ($metrics as $metric) {
				echo "<th>".substr($metric, 3)."</th>";
			}
			echo "\n</tr>\n";
			foreach ($data as $item) {
				echo "<tr>\n";
				foreach ($dimensions as $dimension) {
					echo "<td>".$item->get_dimension($dimension)."</td>";
				}
				foreach ($metrics as $metric) {
					echo "<td>".$item->get_metric($metric)."</td>";
				}
				echo "\n</tr>\n";
			}
			echo "</table>";
		}
	}
	?>
</body>
</html>
