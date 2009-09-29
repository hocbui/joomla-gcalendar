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
 * @version $Revision: 0.1.0 $
 */

if (!defined('SIMPLEPIE_NAMESPACE_GOOGLE_CALENDAR_ITEM')) {
	define('SIMPLEPIE_NAMESPACE_GOOGLE_CALENDAR_ITEM', 'http://schemas.google.com/g/2005');
}

if (!defined('SIMPLEPIE_NAMESPACE_GOOGLE_CALENDAR_FEED')) {
	define('SIMPLEPIE_NAMESPACE_GOOGLE_CALENDAR_FEED', 'http://schemas.google.com/gCal/2005');
}

/**
 * SimplePie_GCalendar is the SimplePie extension which provides some
 * helper methods.
 *
 * @see http://code.google.com/apis/calendar/docs/2.0/reference.html
 */
class SimplePie_GAnalytics extends SimplePie {

	var $user_name = null;
	var $password = null;
	var $profile_id;
	var $parameters;
	var $start_date;
	var $end_date;
	var $authorization = null;

	/**
	 * Sets the timezone of this feed.
	 *
	 * @param $value
	 */
	function set_login($uname = null, $passwd = null) {
		if(!empty($uname))
		$this->user_name = $uname;
		if(!empty($passwd))
		$this->password = $passwd;
	}

	function get_start_date() {
		return $this->start_date;
	}

	function set_start_date($value = null) {
		if(!empty($value))
		return $this->start_date = $value;
	}

	function get_end_date() {
		return $this->end_date;
	}

	function set_end_date($value = null) {
		if(!empty($value))
		return $this->end_date = $value;
	}

	function get_parameters(){
		return $this->parameters;
	}

	function set_parameters($dimension, $metrics, $max_results, $sort){
		$this->data = null;
		$parameters = array('dimensions' 	=> $dimension,
						'metrics'    	=> $metrics,
						'max-results'   => $max_results);
		if(!empty($sort))
		$parameters['sort'] = $sort;
		$this->parameters = $parameters;
	}

	public function set_profile_id($value = null){
		if(strpos($value, 'ga:') !== 0)
		$value = 'ga:'.$value;
		$this->profile_id = $value;
	}

	/**
	 * Overrides the default ini method and sets automatically
	 * SimplePie_Item_GCalendar as item class.
	 * It also sets the variables specified in this feed as query
	 * parameters.
	 */
	function init(){
		$this->set_item_class('SimplePie_Item_GAnalytics');
		$this->set_file_class('SimplePie_File_GAnalytics');

		$file = new SimplePie_File_GAnalytics(
		'https://www.google.com/accounts/ClientLogin?accountType=HOSTED_OR_GOOGLE&Email='.$this->user_name.'&Passwd='.$this->password.'&service=analytics&source=GAnalytics-com_ganalytics-0.5.1',
		10, 5, null, null, false);
		$content = $file->body;
		if (strpos($content, "\n") !== false){
			$responses = explode("\n", $content);
			foreach ($responses as $response){
				if (substr($response, 0, 4) == 'Auth'){
					$this->authorization = trim(substr($response, 5));
				}
			}
		}
		if(empty($this->authorization)){
			$this->error = 'Error authenticating user '.$this->user_name.'. Erro response was '.$content;
			return;
		}
		$params = array();
		foreach($this->parameters as $key => $property){
			$params[] = $key . '=' . urlencode($property);
		}

		$url = 'https://www.google.com/analytics/feeds/data?ids=' . $this->profile_id .
                                                        '&start-date=' . date('Y-m-d', $this->start_date). 
                                                        '&end-date=' . date('Y-m-d', $this->end_date). '&' . 
		implode('&', $params).'&auth='.$this->authorization;
		$this->set_feed_url($url);

		parent::init();
	}


	/**
	 * Sets the given value for the given key which is accessible in the get(...) method.
	 * @param $key
	 * @param $value
	 */
	function put($key, $value){
		$this->meta_data[$key] = $value;
	}

	/**
	 * Returns the value for the given key which is set in the set(...) method.
	 * @param $key
	 * @return the value
	 */
	function get($key){
		return $this->meta_data[$key];
	}
}

class SimplePie_File_GAnalytics extends SimplePie_File{

	function SimplePie_File_GAnalytics($url, $timeout = 10, $redirects = 5, $headers = null, $useragent = null, $force_fsockopen = false){
		$parts = explode('&auth=', $url);
		if(is_array($parts) && count($parts) > 1){
			$auth = $parts[1];
//			$url = $parts[0];
		}
		parent::SimplePie_File($url, $timeout = 10, $redirects = 5, array('Authorization' => 'GoogleLogin auth=' . $auth), $useragent = null, $force_fsockopen = false);
	}
}

/**
 * The GCalendar Item which provides more google calendar specific
 * functions like the location of the event, etc.
 */
class SimplePie_Item_GAnalytics extends SimplePie_Item {

	//internal cache variables
	var $gc_metrics;
	var $gc_pub_date;
	var $gc_location;
	var $gc_status;
	var $gc_start_date;
	var $gc_end_date;
	var $gc_day_type;

	function get_available_dimension_names(){
		return array_keys($this->dimensions);
	}

	function getDimension($dimensionName){
		return $this->dimensions[$dimensionName];
	}

	function addDimension($dimensionName, $dimensionValue){
		$this->dimensions[$dimensionName] = $dimensionValue;
	}

	function getAvailableMetricNames(){
		return array_keys($this->metrics);
	}

	function getMetric($metricName){
		return $this->metrics[$metricName];
	}

	function addMetric($metricName, $metricValue){
		$this->metrics[$metricName] = $metricValue;
	}

	/**
	 * Returns the id of the event.
	 *
	 * @return the id of the event
	 */
	function get_id(){
		if(!$this->gc_id){
			$this->gc_id = substr($this->get_link(),strpos(strtolower($this->get_link()),'eid=')+4);
		}
		return $this->gc_id;
	}

	/**
	 * Returns the publish date as unix timestamp of the event.
	 *
	 * @return the publish date of the event
	 */
	function get_publish_date(){
		if(!$this->gc_pub_date){
			$pubdate = $this->get_date('Y-m-d\TH:i:s\Z');
			$this->gc_pub_date = SimplePie_Item_GCalendar::tstamptotime($pubdate);
		}
		return $this->gc_pub_date;
	}

	/**
	 * Returns the location of the event.
	 *
	 * @return the location of the event
	 */
	function get_location(){
		if(!$this->gc_location){
			$gd_where = $this->get_item_tags(SIMPLEPIE_NAMESPACE_GOOGLE_CALENDAR_ITEM, 'where');
			if(isset($gd_where[0]) &&
			isset($gd_where[0]['attribs']) &&
			isset($gd_where[0]['attribs']['']) &&
			isset($gd_where[0]['attribs']['']['valueString']))
			$this->gc_location = $gd_where[0]['attribs']['']['valueString'];
		}
		return $this->gc_location;
	}

	/**
	 * Returns the status of the event.
	 *
	 * @return the status of the event
	 */
	function get_status(){
		if(!$this->gc_status){
			$gd_where = $this->get_item_tags(SIMPLEPIE_NAMESPACE_GOOGLE_CALENDAR_ITEM, 'eventStatus');
			$this->gc_status = substr( $gd_status[0]['attribs']['']['value'], -8);
		}
		return $this->gc_status;
	}

	/**
	 * If the given format (must match the criterias of strftime)
	 * is not null a string is returned otherwise a unix timestamp.
	 *
	 * @see http://www.php.net/mktime
	 * @see http://www.php.net/strftime
	 * @param $format
	 * @return the start date of the event
	 */
	function get_start_date($format = null){
		if(!$this->gc_start_date){
			$when = $this->get_item_tags(SIMPLEPIE_NAMESPACE_GOOGLE_CALENDAR_ITEM, 'when');
			$startdate = $when[0]['attribs']['']['startTime'];
			$this->gc_start_date = SimplePie_Item_GCalendar::tstamptotime($startdate);
		}
		if($format != null)
		return strftime($format, $this->gc_start_date);
		return $this->gc_start_date;
	}

	/**
	 * If the given format (must match the criterias of strftime)
	 * is not null a string is returned otherwise a unix timestamp.
	 *
	 * @see http://www.php.net/mktime
	 * @see http://www.php.net/strftime
	 * @param $format
	 * @return the end date of the event
	 */
	function get_end_date($format = null){
		if(!$this->gc_end_date){
			$when = $this->get_item_tags(SIMPLEPIE_NAMESPACE_GOOGLE_CALENDAR_ITEM, 'when');
			$enddate = $when[0]['attribs']['']['endTime'];
			$this->gc_end_date = SimplePie_Item_GCalendar::tstamptotime($enddate);
		}
		if($format != null)
		return strftime($format, $this->gc_end_date);
		return $this->gc_end_date;
	}

	/**
	 * Returns the event type. One of the following constants:
	 *  - SINGLE_WHOLE_DAY
	 *  - SINGLE_PART_DAY
	 *  - MULTIPLE_WHOLE_DAY
	 *  - MULTIPLE_PART_DAY
	 *
	 * @return the event type
	 */
	function get_day_type(){
		if(!$this->gc_day_type){
			$SECSINDAY=86400;

			if (($this->get_start_date()+ $SECSINDAY) <= $this->get_end_date()) {
				if (($this->get_start_date()+ $SECSINDAY) == $this->get_end_date()) {
					$this->gc_day_type =  $this->SINGLE_WHOLE_DAY;
				} else {
					if ((date('g:i a',$this->get_start_date())=='12:00 am')&&(date('g:i a',$this->get_end_date())=='12:00 am')){
						$this->gc_day_type =  $this->MULTIPLE_WHOLE_DAY;
					}else{
						$this->gc_day_type =  $this->MULTIPLE_PART_DAY;
					}
				}
			}else
			$this->gc_day_type = $this->SINGLE_PART_DAY;
		}
		return $this->gc_day_type;
	}

	/**
	 * Returns a unix timestamp of the given iso date.
	 *
	 * @param $iso_date
	 * @return unix timestamp
	 */
	function tstamptotime($iso_date) {
		// converts ISODATE to unix date
		// 1984-09-01T14:21:31Z
		sscanf($iso_date,"%u-%u-%uT%u:%u:%uZ",$year,$month,$day,$hour,$min,$sec);
		$newtstamp = mktime($hour,$min,$sec,$month,$day,$year);
		return $newtstamp;
	}

	/**
	 * Returns an integer less than, equal to, or greater than zero if
	 * the first argument is considered to be respectively less than,
	 * equal to, or greater than the second.
	 * This function can be used to sort an array of SimplePie_Item_GCalendar
	 * items with usort.
	 *
	 * @see http://www.php.net/usort
	 * @param $gc_sp_item1
	 * @param $gc_sp_item2
	 * @return the comparison integer
	 */
	function compare($gc_sp_item1, $gc_sp_item2){
		$time1 = $gc_sp_item1->get_start_date();
		$time2 = $gc_sp_item2->get_start_date();
		$feed = $gc_sp_item1->get_feed();
		if(!$feed->orderby_by_start_date){
			$time1 = $gc_sp_item1->get_publish_date();
			$time2 = $gc_sp_item2->get_publish_date();
		}
		return $time1-$time2;
	}
}
?>