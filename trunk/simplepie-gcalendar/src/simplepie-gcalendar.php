<?php

/**
* Google calendar simplepie feed.
* 
* @author allon
* @version $Revision: 0.1.0 $
**/
class SimplePie_GCalendar extends SimplePie {
	
	var $calendar_type = 'basic';
	
	/**
	* Sets the feed type. Default is basic.
	*/
	function set_calendar_type($value = 'basic'){
		$calendar_type = $value;
	}
	
	/**
	* Returns the feed type. Default is basic.
	*/
	function get_calendar_type(){
		return $calendar_type;
	}
	
	/**
	* Overrides the default ini method and sets automatically 
	* the as item class SimplePie_Item_GCalendar.
	*/
	function init(){
		$this->set_item_class('SimplePie_Item_GCalendar');
		parent::init();
	}
	
	/**
	* Returns the timezone of the feed.
	*/
	public function get_timezone(){
		$tzvalue = $this->get_feed_tags('SIMPLEPIE_NAMESPACE_GOOGLE_CALENDAR_FEED', 'timezone');
		return $tzvalue[0]['attribs']['']['value'];
	}
	
	/**
	* Returns the same array as the method get_items() returns,
	* but sorted as their publish date or if the calendar is of type
	* full the start date.
	* So it makes sense to call enable_order_by_date(false) before fetching
	* the data to prevent from sorting twice.
	*/
	function get_calendar_items() {
		$values = $this->get_items();
		
		SimplePie_GCalendar::sortItems($values);
		return $values;
	}
	
	/**
	* Static method to configure the feed to show just events in the future.
	* So makes just sense if set_calendar_type('full') is called.
	*/
	function configure_feed_as_full($url){
		$url = str_replace("basic","full",$url);
		$today = date('Y-m-d');
		$url = $url."?start-min=".$today;
		$url .= "&orderby=starttime&sortorder=ascending";
		$url .= "&singleevents=true";
		return $url;
	}

	function sortItems($data) {
		for ($i = count($data) - 1; $i >= 0; $i--) {
			$swapped = false;
			for ($j = 0; $j < $i; $j++) {
				$time1 = $data[$j]->get_publish_date();
				$time2 = $data[$j+ 1]->get_publish_date();
				if($data[$j]->is_full() && $data[$j+1]->is_full()){
					$time1 = $data[$j]->get_start_time();
					$time2 = $data[$j+ 1]->get_start_time();
				}
			
				if ( $time1 < $time2 ) {
					$tmp = $data[$j];
	                $data[$j] = $data[$j + 1];
	                $data[$j + 1] = $tmp;
	                $swapped = true;
				}
			}
			if (!$swapped) {
				return $data;
			}
		}
	}

}

if (!defined('SIMPLEPIE_NAMESPACE_GOOGLE_CALENDAR_ITEM')) {
	define('SIMPLEPIE_NAMESPACE_GOOGLE_CALENDAR_ITEM', 'http://schemas.google.com/g/2005');
}

if (!defined('SIMPLEPIE_NAMESPACE_GOOGLE_CALENDAR_FEED')) {
	define('SIMPLEPIE_NAMESPACE_GOOGLE_CALENDAR_FEED', 'http://schemas.google.com/gCal/2005');
}

class SimplePie_Item_GCalendar extends SimplePie_Item {

	public function get_publish_date(){
		$pubdate = $this->get_date('Y-m-d\TH:i:s\Z');
		return SimplePie_Item_GCalendar::tstamptotime($pubdate);
	}
	
	public function get_location(){
		$gd_where = $this->get_item_tags('SIMPLEPIE_NAMESPACE_GOOGLE_CALENDAR_ITEM', 'where');
		return $gd_where[0]['attribs']['']['valueString'];
	}
	
	public function get_status(){
		$gd_where = $this->get_item_tags('SIMPLEPIE_NAMESPACE_GOOGLE_CALENDAR_ITEM', 'eventStatus');
		return substr( $gd_status[0]['attribs']['']['value'], -8);
	}
	
	public function get_start_time($as_timestamp = 'true'){
		$when = $this->get_item_tags('SIMPLEPIE_NAMESPACE_GOOGLE_CALENDAR_ITEM', 'when');
		$startdate = $when[0]['attribs']['']['startTime'];
		if($as_timestamp)
			return SimplePie_Item_GCalendar::tstamptotime($startdate);
		return $startdate;
	}
	
	public function get_end_time($as_timestamp = 'true'){
		$when = $this->get_item_tags('SIMPLEPIE_NAMESPACE_GOOGLE_CALENDAR_ITEM', 'when');
		$enddate = $when[0]['attribs']['']['endTime'];
		if($as_timestamp)
			return SimplePie_Item_GCalendar::tstamptotime($enddate);
		return $enddate;
	}
	
	function is_full(){
		return $this->getFeed()->get_calendar_type() == 'full';
	}
	
	function tstamptotime($tstamp) {
        // converts ISODATE to unix date
        // 1984-09-01T14:21:31Z
		sscanf($tstamp,"%u-%u-%uT%u:%u:%uZ",$year,$month,$day,$hour,$min,$sec);
		$newtstamp=mktime($hour,$min,$sec,$month,$day,$year);
		return $newtstamp;
    }
}