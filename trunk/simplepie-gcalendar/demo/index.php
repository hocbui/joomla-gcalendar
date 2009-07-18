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

$url = $_POST["feedurl"];
$email = $_POST["email"];
$show_past_events = $_POST["past"];
$sort_ascending = $_POST["asc"];
$order_by = $_POST["order"];
$expand_single_events = $_POST["expand"];
$language = $_POST["lang"];
$query = $_POST["query"];
$start = $_POST["start"];
$end = $_POST["end"];
$max = $_POST["max"];
$projection = $_POST["projection"];
$timezone = $_POST["tz"];
?>
<html>
<head>
<title>Simplepie -- GGalendar</title>
<link rel="stylesheet" href="sp-gcalendar.css" type="text/css" />
</head>
<body>
<h1>Simplepie Google Calendar demo web site</h1>
<form name="input" action="index.php" method="post">
<table>
	<tr>
		<td>Feed url:</td>
		<td colspan="4"><input type="text" name="feedurl" size="100"
			value="<?php echo $url; ?>"></td>
	</tr>
	<tr>
		<td>OR</td>
		<td colspan="4"></td>
	</tr>
	<tr>
		<td>EMail address:</td>
		<td colspan="4"><input type="text" name="email" size="100"
			value="<?php echo $email; ?>"></td>
	</tr>
	<tr>
		<td colspan="5">
		<hr />
		</td>
	</tr>
	<tr>
		<td>Filter (Query):</td>
		<td><input type="text" name="query" size="50"
			value="<?php echo $query; ?>"></td>
		<td>Max events:</td>
		<td><input type="text" name="max" size="10"
			value="<?php echo empty($max) ? 5 : $max; ?>"></td>
	</tr>
	<tr>
	<?php printCheckBox('Show past events:', 'past', 0); ?>
	<?php printCheckBox('Sort ascending:', 'asc', 1); ?>
	</tr>
	<tr>
	<?php printCheckBox('Order by start date:', 'order', 1); ?>
	<?php printCheckBox('Expand single events:', 'expand', 1); ?>
	</tr>
	<tr>
	</tr>
	<tr>
	<?php printList('Language:', 'lang', 'en', array('en', 'de', 'fr')); ?>
	</tr>
	<tr>
	<?php printList('Projection:', 'projection', 'full', array('full',
					'full-noattendees', 'composite', 'attendees-only', 'free-busy', 'basic')); ?>
	</tr>
	<tr>
	<?php printTimezones(); ?>
	</tr>
	<tr>
		<td><input type="submit" value="Submit"></td>
	</tr>
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
	$feed->set_max_events($max);
	$feed->set_cal_language($language);
	$feed->set_projection($projection);
	$feed->set_timezone($timezone);
	$feed->enable_cache(false);
	$feed->set_cache_duration(0);
	$feed->set_cal_query($query);

	$feed->set_feed_url($url);

	if(!$content){
		$feed->enable_order_by_date(FALSE);
		$feed->init();

		$feed->handle_content_type();
		$gcalendar_data = $feed->get_items();
		echo '<p><b>feed url: '.$feed->feed_url.'</b></p>';

		for ($i = 0; $i < sizeof($gcalendar_data) ; $i++){
			$item = $gcalendar_data[$i];
			$startDate = date("d.m.Y H:i", $item->get_start_date());
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

	function printList($title, $name, $defaultValue, $values) {
		echo "\t<td align=\"left\">".$title."</td>\n";
		echo "\t\t<td><select name=\"".$name."\" style=\"width:100%\">\n";
		$selected = $_POST[$name];
		if(empty($selected))
		$selected = $defaultValue;
		foreach ($values as $value) {
			echo "\t\t\t<option value=\"".$value."\" ";
			if($value == $selected)
			echo "selected";
			echo ">".$value."</option>\n";
		}
		echo "\t\t</select></td>\n";
	}

	function printCheckBox($title, $name, $defaultValue) {
		$value = $_POST[$name];
		if(empty($value))
		$value = $defaultValue;
		echo "\t<td align=\"left\">".$title."</td>\n";
		echo "\t\t<td align=\"left\"><input type=\"radio\" name=\"".$name."\" value=\"1\" ";
		if($value == '1')
		echo "checked";
		echo ">yes\n";
		echo "\t\t<input type=\"radio\" name=\"".$name."\" value=\"0\" ";
		if($value == '0')
		echo "checked";
		echo ">no</td>\n";

	}

	function printTimezones() {
		printList('Timezone:', 'tz', 'Europe/London', array(
	'Pacific/Apia',//(GMT-11:00) Apia
	'Pacific/Midway',//(GMT-11:00) Midway
	'Pacific/Niue',//(GMT-11:00) Niue
	'Pacific/Pago_Pago',//(GMT-11:00) Pago Pago
	'Pacific/Fakaofo',//(GMT-10:00) Fakaofo
	'Pacific/Honolulu',//(GMT-10:00) Hawaii Time
	'Pacific/Johnston',//(GMT-10:00) Johnston
	'Pacific/Rarotonga',//(GMT-10:00) Rarotonga
	'Pacific/Tahiti',//(GMT-10:00) Tahiti
	'Pacific/Marquesas',//(GMT-09:30) Marquesas
	'America/Anchorage',//(GMT-09:00) Alaska Time
	'Pacific/Gambier',//(GMT-09:00) Gambier
	'America/Los_Angeles',//(GMT-08:00) Pacific Time
	'America/Tijuana',//(GMT-08:00) Pacific Time - Tijuana
	'America/Vancouver',//(GMT-08:00) Pacific Time - Vancouver
	'America/Whitehorse',//(GMT-08:00) Pacific Time - Whitehorse
	'Pacific/Pitcairn',//(GMT-08:00) Pitcairn
	'America/Dawson_Creek',//(GMT-07:00) Mountain Time - Dawson	Creek
	'America/Denver',//(GMT-07:00) Mountain Time (America/Denver)
	'America/Edmonton',//(GMT-07:00) Mountain Time - Edmonton
	'America/Hermosillo',//(GMT-07:00) Mountain Time -	Hermosillo
	'America/Mazatlan',//(GMT-07:00) Mountain Time - Chihuahua,	Mazatlan
	'America/Phoenix',//(GMT-07:00) Mountain Time - Arizona
	'America/Yellowknife',//(GMT-07:00) Mountain Time -	Yellowknife
	'America/Belize',//(GMT-06:00) Belize
	'America/Chicago',//(GMT-06:00) Central Time
	'America/Costa_Rica',//(GMT-06:00) Costa Rica
	'America/El_Salvador',//(GMT-06:00) El Salvador
	'America/Guatemala',//(GMT-06:00) Guatemala
	'America/Managua',//(GMT-06:00) Managua
	'America/Mexico_City',//(GMT-06:00) Central Time - Mexico	City
	'America/Regina',//(GMT-06:00) Central Time - Regina
	'America/Tegucigalpa',//(GMT-06:00) Central Time	(America/Tegucigalpa)
	'America/Winnipeg',//(GMT-06:00) Central Time - Winnipeg
	'Pacific/Easter',//(GMT-06:00) Easter Island
	'Pacific/Galapagos',//(GMT-06:00) Galapagos
	'America/Bogota',//(GMT-05:00) Bogota
	'America/Cayman',//(GMT-05:00) Cayman
	'America/Grand_Turk',//(GMT-05:00) Grand Turk
	'America/Guayaquil',//(GMT-05:00) Guayaquil
	'America/Havana',//(GMT-05:00) Havana
	'America/Iqaluit',//(GMT-05:00) Eastern Time - Iqaluit
	'America/Jamaica',//(GMT-05:00) Jamaica
	'America/Lima',//(GMT-05:00) Lima
	'America/Montreal',//(GMT-05:00) Eastern Time - Montreal
	'America/Nassau',//(GMT-05:00) Nassau
	'America/New_York',//(GMT-05:00) Eastern Time
	'America/Panama',//(GMT-05:00) Panama
	'America/Port-au-Prince',//(GMT-05:00) Port-au-Prince
	'America/Toronto',//(GMT-05:00) Eastern Time - Toronto
	'America/Caracas',//(GMT-04:30) Caracas
	'America/Anguilla',//(GMT-04:00) Anguilla
	'America/Antigua',//(GMT-04:00) Antigua
	'America/Aruba',//(GMT-04:00) Aruba
	'America/Asuncion',//(GMT-04:00) Asuncion
	'America/Barbados',//(GMT-04:00) Barbados
	'America/Boa_Vista',//(GMT-04:00) Boa Vista
	'America/Campo_Grande',//(GMT-04:00) Campo Grande
	'America/Cuiaba',//(GMT-04:00) Cuiaba
	'America/Curacao',//(GMT-04:00) Curacao
	'America/Dominica',//(GMT-04:00) Dominica
	'America/Grenada',//(GMT-04:00) Grenada
	'America/Guadeloupe',//(GMT-04:00) Guadeloupe
	'America/Guyana',//(GMT-04:00) Guyana
	'America/Halifax',//(GMT-04:00) Atlantic Time - Halifax
	'America/La_Paz',//(GMT-04:00) La Paz
	'America/Manaus',//(GMT-04:00) Manaus
	'America/Martinique',//(GMT-04:00) Martinique
	'America/Montserrat',//(GMT-04:00) Montserrat
	'America/Port_of_Spain',//(GMT-04:00) Port of Spain
	'America/Porto_Velho',//(GMT-04:00) Porto Velho
	'America/Puerto_Rico',//(GMT-04:00) Puerto Rico
	'America/Rio_Branco',//(GMT-04:00) Rio Branco
	'America/Santiago',//(GMT-04:00) Santiago
	'America/Santo_Domingo',//(GMT-04:00) Santo Domingo
	'America/St_Kitts',//(GMT-04:00) St. Kitts
	'America/St_Lucia',//(GMT-04:00) St. Lucia
	'America/St_Thomas',//(GMT-04:00) St. Thomas
	'America/St_Vincent',//(GMT-04:00) St. Vincent
	'America/Thule',//(GMT-04:00) Thule
	'America/Tortola',//(GMT-04:00) Tortola
	'Antarctica/Palmer',//(GMT-04:00) Palmer
	'Atlantic/Bermuda',//(GMT-04:00) Bermuda
	'Atlantic/Stanley',//(GMT-04:00) Stanley
	'America/St_Johns',//(GMT-03:30) Newfoundland Time - St.	Johns
	'America/Araguaina',//(GMT-03:00) Araguaina
	'America/Argentina/Buenos_Aires',//(GMT-03:00) Buenos Aires
	'America/Bahia',//(GMT-03:00) Salvador
	'America/Belem',//(GMT-03:00) Belem
	'America/Cayenne',//(GMT-03:00) Cayenne
	'America/Fortaleza',//(GMT-03:00) Fortaleza
	'America/Godthab',//(GMT-03:00) Godthab
	'America/Maceio',//(GMT-03:00) Maceio
	'America/Miquelon',//(GMT-03:00) Miquelon
	'America/Montevideo',//(GMT-03:00) Montevideo
	'America/Paramaribo',//(GMT-03:00) Paramaribo
	'America/Recife',//(GMT-03:00) Recife
	'America/Sao_Paulo',//(GMT-03:00) Sao Paulo
	'Antarctica/Rothera',//(GMT-03:00) Rothera
	'America/Noronha',//(GMT-02:00) Noronha
	'Atlantic/South_Georgia',//(GMT-02:00) South Georgia
	'America/Scoresbysund',//(GMT-01:00) Scoresbysund
	'Atlantic/Azores',//(GMT-01:00) Azores
	'Atlantic/Cape_Verde',//(GMT-01:00) Cape Verde
	'Africa/Abidjan',//(GMT+00:00) Abidjan
	'Africa/Accra',//(GMT+00:00) Accra
	'Africa/Bamako',//(GMT+00:00) Bamako
	'Africa/Banjul',//(GMT+00:00) Banjul
	'Africa/Bissau',//(GMT+00:00) Bissau
	'Africa/Casablanca',//(GMT+00:00) Casablanca
	'Africa/Conakry',//(GMT+00:00) Conakry
	'Africa/Dakar',//(GMT+00:00) Dakar
	'Africa/El_Aaiun',//(GMT+00:00) El Aaiun
	'Africa/Freetown',//(GMT+00:00) Freetown
	'Africa/Lome',//(GMT+00:00) Lome
	'Africa/Monrovia',//(GMT+00:00) Monrovia
	'Africa/Nouakchott',//(GMT+00:00) Nouakchott
	'Africa/Ouagadougou',//(GMT+00:00) Ouagadougou
	'Africa/Sao_Tome',//(GMT+00:00) Sao Tome
	'America/Danmarkshavn',//(GMT+00:00) Danmarkshavn
	'Atlantic/Canary',//(GMT+00:00) Canary Islands
	'Atlantic/Faroe',//(GMT+00:00) Faeroe
	'Atlantic/Reykjavik',//(GMT+00:00) Reykjavik
	'Atlantic/St_Helena',//(GMT+00:00) St Helena
	'Etc/GMT',//(GMT+00:00) GMT (no daylight saving)
	'Europe/Dublin',//(GMT+00:00) Dublin
	'Europe/Lisbon',//(GMT+00:00) Lisbon
	'Europe/London',//(GMT+00:00) London
	'Africa/Algiers',//(GMT+01:00) Algiers
	'Africa/Bangui',//(GMT+01:00) Bangui
	'Africa/Brazzaville',//(GMT+01:00) Brazzaville
	'Africa/Ceuta',//(GMT+01:00) Ceuta
	'Africa/Douala',//(GMT+01:00) Douala
	'Africa/Kinshasa',//(GMT+01:00) Kinshasa
	'Africa/Lagos',//(GMT+01:00) Lagos
	'Africa/Libreville',//(GMT+01:00) Libreville
	'Africa/Luanda',//(GMT+01:00) Luanda
	'Africa/Malabo',//(GMT+01:00) Malabo
	'Africa/Ndjamena',//(GMT+01:00) Ndjamena
	'Africa/Niamey',//(GMT+01:00) Niamey
	'Africa/Porto-Novo',//(GMT+01:00) Porto-Novo
	'Africa/Tunis',//(GMT+01:00) Tunis
	'Africa/Windhoek',//(GMT+01:00) Windhoek
	'Europe/Amsterdam',//(GMT+01:00) Amsterdam
	'Europe/Andorra',//(GMT+01:00) Andorra
	'Europe/Belgrade',//(GMT+01:00) Central European Time	(Europe/Belgrade)
	'Europe/Berlin',//(GMT+01:00) Berlin
	'Europe/Brussels',//(GMT+01:00) Brussels
	'Europe/Budapest',//(GMT+01:00) Budapest
	'Europe/Copenhagen',//(GMT+01:00) Copenhagen
	'Europe/Gibraltar',//(GMT+01:00) Gibraltar
	'Europe/Luxembourg',//(GMT+01:00) Luxembourg
	'Europe/Madrid',//(GMT+01:00) Madrid
	'Europe/Malta',//(GMT+01:00) Malta
	'Europe/Monaco',//(GMT+01:00) Monaco
	'Europe/Oslo',//(GMT+01:00) Oslo
	'Europe/Paris',//(GMT+01:00) Paris
	'Europe/Prague',//(GMT+01:00) Central European Time	(Europe/Prague)
	'Europe/Rome',//(GMT+01:00) Rome
	'Europe/Stockholm',//(GMT+01:00) Stockholm
	'Europe/Tirane',//(GMT+01:00) Tirane
	'Europe/Vaduz',//(GMT+01:00) Vaduz
	'Europe/Vienna',//(GMT+01:00) Vienna
	'Europe/Warsaw',//(GMT+01:00) Warsaw
	'Europe/Zurich',//(GMT+01:00) Zurich
	'Africa/Blantyre',//(GMT+02:00) Blantyre
	'Africa/Bujumbura',//(GMT+02:00) Bujumbura
	'Africa/Cairo',//(GMT+02:00) Cairo
	'Africa/Gaborone',//(GMT+02:00) Gaborone
	'Africa/Harare',//(GMT+02:00) Harare
	'Africa/Johannesburg',//(GMT+02:00) Johannesburg
	'Africa/Kigali',//(GMT+02:00) Kigali
	'Africa/Lubumbashi',//(GMT+02:00) Lubumbashi
	'Africa/Lusaka',//(GMT+02:00) Lusaka
	'Africa/Maputo',//(GMT+02:00) Maputo
	'Africa/Maseru',//(GMT+02:00) Maseru
	'Africa/Mbabane',//(GMT+02:00) Mbabane
	'Africa/Tripoli',//(GMT+02:00) Tripoli
	'Asia/Amman',//(GMT+02:00) Amman
	'Asia/Beirut',//(GMT+02:00) Beirut
	'Asia/Damascus',//(GMT+02:00) Damascus
	'Asia/Gaza',//(GMT+02:00) Gaza
	'Asia/Jerusalem',//(GMT+02:00) Jerusalem
	'Asia/Nicosia',//(GMT+02:00) Nicosia
	'Europe/Athens',//(GMT+02:00) Athens
	'Europe/Bucharest',//(GMT+02:00) Bucharest
	'Europe/Chisinau',//(GMT+02:00) Chisinau
	'Europe/Helsinki',//(GMT+02:00) Helsinki
	'Europe/Istanbul',//(GMT+02:00) Istanbul
	'Europe/Kaliningrad',//(GMT+02:00) Moscow-01 - Kaliningrad
	'Europe/Kiev',//(GMT+02:00) Kiev
	'Europe/Minsk',//(GMT+02:00) Minsk
	'Europe/Riga',//(GMT+02:00) Riga
	'Europe/Sofia',//(GMT+02:00) Sofia
	'Europe/Tallinn',//(GMT+02:00) Tallinn
	'Europe/Vilnius',//(GMT+02:00) Vilnius
	'Africa/Addis_Ababa',//(GMT+03:00) Addis Ababa
	'Africa/Asmara',//(GMT+03:00) Asmera
	'Africa/Dar_es_Salaam',//(GMT+03:00) Dar es Salaam
	'Africa/Djibouti',//(GMT+03:00) Djibouti
	'Africa/Kampala',//(GMT+03:00) Kampala
	'Africa/Khartoum',//(GMT+03:00) Khartoum
	'Africa/Mogadishu',//(GMT+03:00) Mogadishu
	'Africa/Nairobi',//(GMT+03:00) Nairobi
	'Antarctica/Syowa',//(GMT+03:00) Syowa
	'Asia/Aden',//(GMT+03:00) Aden
	'Asia/Baghdad',//(GMT+03:00) Baghdad
	'Asia/Bahrain',//(GMT+03:00) Bahrain
	'Asia/Kuwait',//(GMT+03:00) Kuwait
	'Asia/Qatar',//(GMT+03:00) Qatar
	'Asia/Riyadh',//(GMT+03:00) Riyadh
	'Europe/Moscow',//(GMT+03:00) Moscow+00
	'Indian/Antananarivo',//(GMT+03:00) Antananarivo
	'Indian/Comoro',//(GMT+03:00) Comoro
	'Indian/Mayotte',//(GMT+03:00) Mayotte
	'Asia/Tehran',//(GMT+03:30) Tehran
	'Asia/Baku',//(GMT+04:00) Baku
	'Asia/Dubai',//(GMT+04:00) Dubai
	'Asia/Muscat',//(GMT+04:00) Muscat
	'Asia/Tbilisi',//(GMT+04:00) Tbilisi
	'Asia/Yerevan',//(GMT+04:00) Yerevan
	'Europe/Samara',//(GMT+04:00) Moscow+01 - Samara
	'Indian/Mahe',//(GMT+04:00) Mahe
	'Indian/Mauritius',//(GMT+04:00) Mauritius
	'Indian/Reunion',//(GMT+04:00) Reunion
	'Asia/Kabul',//(GMT+04:30) Kabul
	'Asia/Aqtau',//(GMT+05:00) Aqtau
	'Asia/Aqtobe',//(GMT+05:00) Aqtobe
	'Asia/Ashgabat',//(GMT+05:00) Ashgabat
	'Asia/Dushanbe',//(GMT+05:00) Dushanbe
	'Asia/Karachi',//(GMT+05:00) Karachi
	'Asia/Tashkent',//(GMT+05:00) Tashkent
	'Asia/Yekaterinburg',//(GMT+05:00) Moscow+02 -	Yekaterinburg
	'Indian/Kerguelen',//(GMT+05:00) Kerguelen
	'Indian/Maldives',//(GMT+05:00) Maldives
	'Asia/Calcutta',//(GMT+05:30) India Standard Time
	'Asia/Colombo',//(GMT+05:30) Colombo
	'Asia/Katmandu',//(GMT+05:45) Katmandu
	'Antarctica/Mawson',//(GMT+06:00) Mawson
	'Antarctica/Vostok',//(GMT+06:00) Vostok
	'Asia/Almaty',//(GMT+06:00) Almaty
	'Asia/Bishkek',//(GMT+06:00) Bishkek
	'Asia/Dhaka',//(GMT+06:00) Dhaka
	'Asia/Omsk',//(GMT+06:00) Moscow+03 - Omsk, Novosibirsk
	'Asia/Thimphu',//(GMT+06:00) Thimphu
	'Indian/Chagos',//(GMT+06:00) Chagos
	'Asia/Rangoon',//(GMT+06:30) Rangoon
	'Indian/Cocos',//(GMT+06:30) Cocos
	'Antarctica/Davis',//(GMT+07:00) Davis
	'Asia/Bangkok',//(GMT+07:00) Bangkok
	'Asia/Hovd',//(GMT+07:00) Hovd
	'Asia/Jakarta',//(GMT+07:00) Jakarta
	'Asia/Krasnoyarsk',//(GMT+07:00) Moscow+04 - Krasnoyarsk
	'Asia/Phnom_Penh',//(GMT+07:00) Phnom Penh
	'Asia/Saigon',//(GMT+07:00) Hanoi
	'Asia/Vientiane',//(GMT+07:00) Vientiane
	'Indian/Christmas',//(GMT+07:00) Christmas
	'Antarctica/Casey',//(GMT+08:00) Casey
	'Asia/Brunei',//(GMT+08:00) Brunei
	'Asia/Choibalsan',//(GMT+08:00) Choibalsan
	'Asia/Hong_Kong',//(GMT+08:00) Hong Kong
	'Asia/Irkutsk',//(GMT+08:00) Moscow+05 - Irkutsk
	'Asia/Kuala_Lumpur',//(GMT+08:00) Kuala Lumpur
	'Asia/Macau',//(GMT+08:00) Macau
	'Asia/Makassar',//(GMT+08:00) Makassar
	'Asia/Manila',//(GMT+08:00) Manila
	'Asia/Shanghai',//(GMT+08:00) China Time - Beijing
	'Asia/Singapore',//(GMT+08:00) Singapore
	'Asia/Taipei',//(GMT+08:00) Taipei
	'Asia/Ulaanbaatar',//(GMT+08:00) Ulaanbaatar
	'Australia/Perth',//(GMT+08:00) Western Time - Perth
	'Asia/Dili',//(GMT+09:00) Dili
	'Asia/Jayapura',//(GMT+09:00) Jayapura
	'Asia/Pyongyang',//(GMT+09:00) Pyongyang
	'Asia/Seoul',//(GMT+09:00) Seoul
	'Asia/Tokyo',//(GMT+09:00) Tokyo
	'Asia/Yakutsk',//(GMT+09:00) Moscow+06 - Yakutsk
	'Pacific/Palau',//(GMT+09:00) Palau
	'Australia/Adelaide',//(GMT+09:30) Central Time - Adelaide
	'Australia/Darwin',//(GMT+09:30) Central Time - Darwin
	'Antarctica/DumontDUrville',//(GMT+10:00) Dumont D'Urville
	'Asia/Vladivostok',//(GMT+10:00) Moscow+07 -	Yuzhno-Sakhalinsk
	'Australia/Brisbane',//(GMT+10:00) Eastern Time - Brisbane
	'Australia/Hobart',//(GMT+10:00) Eastern Time - Hobart
	'Australia/Sydney',//(GMT+10:00) Eastern Time - Melbourne,	Sydney
	'Pacific/Guam',//(GMT+10:00) Guam
	'Pacific/Port_Moresby',//(GMT+10:00) Port Moresby
	'Pacific/Saipan',//(GMT+10:00) Saipan
	'Pacific/Truk',//(GMT+10:00) Truk
	'Asia/Magadan',//(GMT+11:00) Moscow+08 - Magadan
	'Pacific/Efate',//(GMT+11:00) Efate
	'Pacific/Guadalcanal',//(GMT+11:00) Guadalcanal
	'Pacific/Kosrae',//(GMT+11:00) Kosrae
	'Pacific/Noumea',//(GMT+11:00) Noumea
	'Pacific/Ponape',//(GMT+11:00) Ponape
	'Pacific/Norfolk',//(GMT+11:30) Norfolk
	'Asia/Kamchatka',//(GMT+12:00) Moscow+09 -	Petropavlovsk-Kamchatskiy
	'Pacific/Auckland',//(GMT+12:00) Auckland
	'Pacific/Fiji',//(GMT+12:00) Fiji
	'Pacific/Funafuti',//(GMT+12:00) Funafuti
	'Pacific/Kwajalein',//(GMT+12:00) Kwajalein
	'Pacific/Majuro',//(GMT+12:00) Majuro
	'Pacific/Nauru',//(GMT+12:00) Nauru
	'Pacific/Tarawa',//(GMT+12:00) Tarawa
	'Pacific/Wake',//(GMT+12:00) Wake
	'Pacific/Wallis',//(GMT+12:00) Wallis
	'Pacific/Enderbury',//(GMT+13:00) Enderbury
	'Pacific/Tongatapu',//(GMT+13:00) Tongatapu
	'Pacific/Kiritimati'//(GMT+14:00) Kiritimati
		));
	}
	?>
</body>
</html>
