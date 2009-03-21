<?php
defined( '_JEXEC' ) or die( 'Restricted access' );

/* iWebCal Version 2.0 beta
 * Copyright (C) 2003-2005 David A. Feldman.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of version 2 of the GNU General Public License 
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful, but 
 * WITHOUT ANY WARRANTY; without even the implied warranty of 
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU 
 * General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License 
 * along with this program; if not, write to the Free Software Foundation, 
 * Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA. Or, 
 * visit http://gnu.org.
 * 
 * This file is part of the iWebCal calendar-viewing service. The iWebCal
 * service is available on the Web at http://iWebCal.com, and does not
 * require any programming knowledge or Web server configuration to use.
 * Anyone with an iCal or other .ics calendar file and a place to post
 * it on the Web can view the calendar using iWebCal.
 */
 
 /*
  * CalItem.inc: Defines the CalItem class, which handles parsing and
  * display of individual calendar items (events). Relies on the Property 
  * class.
  *
  * File version 2.0b3, last modified April 29, 2005.
  */

class CalItem {
	var $properties;
	var $type;
	var $basicDates;
	
	var $startDateCache;
	var $endDateCache;
	var $isAllDayCache;
	var $config;
	
	function CalItem($type = NULL, $config) {
		$this->properties = array();
		$this->type = $type;
		$this->basicDates = NULL;
		$this->finiteRepeatDates = NULL;
		$this->repeats = NULL;
		$this->isInfiniteRepeating = false;
		
		$this->startDateCache = NULL;
		$this->isAllDayCache = NULL;
		$this->endDateCache = NULL;
		$this->config = $config;
	}
	
	function addProperty($prop) {
		$this->properties[] = $prop;
	}
	
	function propertyArray() {
		return $this->properties;
	}
	
	function dprint() {
		foreach($this->properties as $prop) {
			$prop->dprint();
		}
	}
	
	function getProperty($name) {
		$isMulti = in_array($name, $this->config['MULTI_INSTANCE_PROPERTIES']);

		$res = $isMulti ? array() : false;
		foreach($this->properties as $prop) {
			if($prop->name == $name) {
				if (!$isMulti) return $prop;
				$res[] = $prop;
			}
		}
		if (count($res)) return $res;
		return NULL;
	}
	
	function sortComparison($a, $b) {
		if ($a->isAllDay()) return -1;
		if ($b->isAllDay()) return 1;
		
		// if we're here both are timed
		if ($a->startDate() == $b->startDate()) {
			return 0;
		}
		return (($a->startDate() > $b->startDate()) ? 1 : -1);
	}
	
	function repeatsOnDate($date) { // date should be in the form "YYYY-MM-DD"
		global $iWebCal_TIME_ZONES;
		// assumes that the event does repeat
	
		if (!$this->basicDates) {
			$this->calcDates();
		}
		// TO DO: Added the !$this->basicDates below on 3/21/05 in response
		// to a warning that was cropping up but without significant
		// research into why the check was here in the first place. Check.
		if (!$this->basicDates || in_array($date, $this->basicDates)) {
			return false;
		}
		
		// see if the date parameter is after the event's start date
		$tDate = strtotime($date);
		$tStart = strtotime($this->basicDates[0]);
		$diff = $tDate - $tStart;
		if ($diff < 0) {
			return false;
		}

		$rrule = $this->getProperty("RRULE");
		$until = $rrule->value("UNTIL");
		$count = $rrule->value("COUNT");
		
		if ($until) {
			$untilStr = substr($until, 0, 4) . "-" .
						substr($until, 4, 2) . "-" .
						substr($until, 6, 2);			
			if (strlen($until) > 8) {
				// TODO - iCal stores most date/times in and with a timezone, but not the UNTIL
				// value in GMT. To date, iWebCal has simply made the assumption that
				// displaying (and thus calculating) events in the current timezone
				// is fine, and has thus ignored the timezone value. In most cases this
				// should be fine - even if the Web server is in a different timezone
				// than the machine on which the calendar was created, it won't matter.
				// However, it becomes problematic when events are stored in multiple
				// time zones (though I don't know how common this is), and in interpreting
				// the UNTIL value. I can't find an "official" list of the format in which iCal 
				// stores timezones, so I'm gathering those I can locate and falling back on the 
				// server timezone when that fails. For iWebCal 2.0, I'm simply using that information from
				// the event in question to adjust the UNTIL value and continuing to assume that
				// all events are in one timezone. May be worth reexamining at some point.
				$untilStr .= " " . substr($until, 9, 2) . ":" . substr($until, 11, 2);
				$tUntil = strtotime($untilStr);
				if (strrpos($until, "Z") == strlen($until)-1) {
					// time is stored in Zulu (GMT) rather than local timezone
					$startProp = $this->getProperty("DTSTART");
					$startTZ = $startProp->parameter("TZID");
					if (isset($iWebCal_TIME_ZONES[$startTZ])) {
						$tUntil += $iWebCal_TIME_ZONES[$startTZ] * 3600;
					}
				}
			}
			else {
				$tUntil = strtotime($untilStr);
			}
		}
						
		// [TO DO](2): Is there anywhere below that the code can prematurely return false
		//  and thus save some execution time?
		$duration = (int)((strtotime($this->basicDates[count($this->basicDates)-1]) -
			strtotime($this->basicDates[0])) / 86400); // days
		for ($testIteration=0;$testIteration<=$duration;$testIteration++) {
			$testDate = strtotime("-${testIteration} days", $tDate);
			// if the event has a stop date, see if the date param is before it
			if ($until) {
				// Note: Changed from >= to > to support iCal 1.0.2, excluding iCal 1.0.
				if ($testDate > $tUntil) {
					continue;
				}
			}
			else {
				$tUntil = NULL;
			}
					
			switch($rrule->value("FREQ")) {
				case "DAILY":
					$interval = (int)$rrule->value("INTERVAL");
					$diff = $testDate - $tStart;
					// [TO DO](1): Is there any danger associated with this sort of date
					//  arithmetic? There does seem to be some sort of weird (roundoff?) 
					//  error when I use seconds instead of days here. Are there any
					//  other potential pitfalls?
					$dayDiff = $diff / 86400;
					if (($dayDiff % $interval) == 0) {
						// should return true unless the stop condition is met
						if (!$count || ($dayDiff < ($interval * $count))) {
							return true;
						}
					}
					break;
	
				case "WEEKLY":
					// startDay = day of week on which the event starts
					$startDay = strftime("%u", $tStart);

					// Some events repeat on a day other than the start day. This
					// is stored in the BYDAY value.
					if (($byDayValue = $rrule->value("BYDAY")) != "") {
						$days = explode(",", $rrule->value("BYDAY"));
						for ($i=0;$i<count($days);$i++) {
							$days[$i] = dayNumber($days[$i]);
						}
					}
					else {
						$days = array();
						$days[0] = $startDay;
					}
					
					// $days now contains all days of the week on which the event
					// repeats. (If the event does not repeat on its initial day
					// of the week that day is not included, but would still
					// be included in a count i.e. "repeat N times"
					
					// startSunday = Sunday beginning week in which event starts
					$startSunday = strtotime("-${startDay} days", $tStart);
					// repeat start day = first repeated day of week
					$repeatStartDay = $days[0];
					// repeatStart = actual date of first repeated day in first week
					$repeatStart = strtotime("+${repeatStartDay} day", $startSunday);
					
					$interval = (int)$rrule->value("INTERVAL");
					
					// Set myDiff to the difference (in days) between the date we're looking at
					//  and repeatStart
					$myDiff = (int)(($testDate - $repeatStart) / 86400); // days
					
					// Set testDayOfWeek to the test day of the week
					$testDayOfWeek = strftime("%u", $testDate); 

					if ($myDiff < 0) {
						continue;
					}
					if ((($whichDay = array_search($testDayOfWeek, $days)) !== false) && ($whichDay !== NULL)) {
						// we're on a repeated day
						// should return true unless the stop condition is met
						if (!$count) return true;
						$skippedInstances = 0;
						$extraInstances = 0;
						foreach ($days as $day) {
							if ($skippable = ($day <= $startDay)) {
								// This particular day is before the initial instance, so in counting the
								// total number of instances we should subtract one for it, since it wouldn't
								// be part of the first week of repeats.
								$skippedInstances++;
							}
							if ((($myDiff % 7) > ($day - $repeatStartDay)) && !(($myDiff < 7) && $skippable)) {
								$extraInstances++;
							}
						}
						$instancesToDate = ((int)($myDiff / (7 * $interval))		// whole weeks since first repeated instance
							* count($days))							// * instances/week = instances in whole weeks to date
							- $skippedInstances						// subtract anything prior to event start date
							+ $extraInstances						// add anything already in current week
							+ 1;									// add first (non-repeated) instance, since that
																	//  counts toward the total.
						
						if ($instancesToDate < $count) return true;
					}
					break;
				case "MONTHLY":
					// [TO DO](2): May be able to centralize more stuff for the two branches here at the top,
					//  and/or at the top of subsections.
					if ($repeatType = $rrule->value("BYMONTHDAY")) {
						// we're repeating on the nth day of every m months
						$repeatType = explode(",", $repeatType);
						if (in_array((int)strftime("%d", $testDate), $repeatType)) {
							// we're on a proper day of the month. Is it a right month?
							$interval = $rrule->value("INTERVAL");
							$firstOfThisMonth = strtotime(strftime("%Y-%m-", $testDate) . "01");
							$firstOfStartMonth = strtotime(substr($this->basicDates[0], 0, 8) . "01");
							// [TO DO](3): Is there a less brute-force way to do this?
							$intervalCount = 0; // used below for checking count stop condition
							for ($j=$firstOfStartMonth; $j<$firstOfThisMonth; $j=strtotime("+${interval} months", $j)) {
								$intervalCount++;
							}
							if ($j == $firstOfThisMonth) {
								// right month. exceeded count?
								if (!$count) return true;
								
								$numInstancesEachMonth = count($repeatType);
								list($thisDayOfMonth, $thisMonth) = explode(",", strftime("%d,%m", $testDate));
								$startDayOfMonth = strftime("%d", $tStart);
								
								// firstMonthAdjust will contain the difference between the number
								// of repeated instances in a normal repeat month and those in the
								// first month. It will always be zero or negative.
	
								// thisMonthAdjust will contain the difference between the number
								// of repeated instances _already_ in the month under current 
								// consideration and those in a normal repeat month.
								// It will always be zero or negative.
								$firstMonthAdjust = 0;
								$thisMonthAdjust = 0;
								foreach($repeatType as $repeatedDay) {
								
									if ($repeatedDay <= $startDayOfMonth) {
										$firstMonthAdjust--;
									}
									
									if ($repeatedDay >= $thisDayOfMonth) {
										$thisMonthAdjust--;
									}
								}
								
								// intervalCount already contains the number of elapsed intervals since
								// the start month (see above). if we're in the start month it's 0.
								// So intervalCount - 1 is the number of full months containing instances
								// that have elapsed so far.
								$instanceCountToDate =
									  ($intervalCount + 1) * $numInstancesEachMonth
										// # instances in all months including first if they were full months
									+ $firstMonthAdjust
									+ $thisMonthAdjust
									+ 1; // add 1 for the initial instance
									
								if ($instanceCountToDate < $count) return true;
							}
						}
					}
					else {
						$repeatType = $rrule->value("BYDAY");
						$whichDay = substr($repeatType, strlen($repeatType)-2, 2);
						$whichWeek = (int)substr($repeatType, 0, strlen($repeatType)-2);
						$whichDay = dayNumber($whichDay);
						$oneWeekLess = ($whichWeek > 0) ? $whichWeek - 1 : $whichWeek + 1;
						
						$thisDayNumber = strftime("%u", $testDate) % 7;;
						if ($thisDayNumber == $whichDay) {
							// right day of the week. right week?
							// [TO DO](3): Is this faster or slower than % arithmetic?
							list($thisYear, $thisMonth, $thisDay) = explode(",", strftime("%Y,%m,%e", $testDate));
							if (checkdate($thisMonth, $thisDay-(7 * $oneWeekLess), $thisYear) 
								&& !checkdate($thisMonth, $thisDay-(7 * $whichWeek), $thisYear)) {
								// [TO DO]: What would be the best order of checks speed-wise?
								// right week. right month?
								$interval = $rrule->value("INTERVAL");
								$firstOfThisMonth = strtotime(strftime("%Y-%m-", $testDate) . "01");
								$firstOfStartMonth = strtotime(substr($this->basicDates[0], 0, 8) . "01");
								$intervalCount = 0; // used below for checking count stop condition
								for ($j=$firstOfStartMonth; $j<$firstOfThisMonth; $j=strtotime("+${interval} months", $j)) {
									$intervalCount++;
								}
								if ($j == $firstOfThisMonth) {
									// right month. have we exceeded count?
									if (!$count) return true;
									list($startMonth, $startYear) = explode(",", strftime("%m,%Y", $tStart));
									if (($count > 0) && ($startMonth == $thisMonth) && ($startYear == $thisYear)) return true;
	
									$startDayOfMonth = strftime("%d", $tStart);
	
									// We need to know on what day the repeat date falls in the first month
									// so we can figure out if there's a repeated instance that month.
									// First, get the day of the week for the first of the start month:
									$firstOfStartMonth_dayOfWeek = strftime("%u", $firstOfStartMonth) % 7;									
									// Then, get the first instance of the day of the week we repeat on:
									$distanceToRepeatedDay = $whichDay - $firstOfStartMonth_dayOfWeek;
									if ($distanceToRepeatedDay < 0) $distanceToRepeatedDay += 7;
									$firstInstanceOfRepeatedDay = 1 + $distanceToRepeatedDay;
									// Now get the actual day of the month:
									if ($whichWeek > 0) {
										$firstMonthDayOfRepeatedInstance = $firstInstanceOfRepeatedDay
											+ (7 * ($whichWeek-1));
									}
									else {
										for ($k=5;$k>0;$k--) {
											$myDay = $firstInstanceOfRepeatedDay + (($k-1)*7);
											if (checkdate($thisMonth, $myDay, $thisYear)) {
												$lastInstanceOfRepeatedDay = $myDay;
												break;
											}
										}
										$firstMonthDayOfRepeatedInstance = $lastInstanceOfRepeatedDay
											+ (7 * ($whichWeek+1));
									}
									if ($firstMonthDayOfRepeatedInstance > $startDayOfMonth) {
										$repeatedInstancesInFirstMonth = 1;
									}
									else {
										$repeatedInstancesInFirstMonth = 0;
									}
										
									
									// intervalCount contains the number of elapsed intervals since
									// the start month (see above). If we're in the start month
									// it's 1 (since the first instance counts toward the total).
									
									$instanceCountToDate = $intervalCount + $repeatedInstancesInFirstMonth;
									if ($instanceCountToDate < $count) return true;
								}
							}								
						}
					}
					break;
				case "YEARLY":
					// are we in the right year?
					list($thisYear, $thisMonth, $thisDayNumber, $thisDay) = explode(",", strftime("%Y,%m,%u,%e", $testDate));
					$thisDayNumber = $thisDayNumber % 7;
					list ($startYear, $startMonth, $startDayOfMonth) = explode(",", strftime("%Y,%m,%e", $tStart));
					$yearDiff = $thisYear - $startYear;
					$interval = $rrule->value("INTERVAL");
					if (($yearDiff % $interval) == 0) {
						// we're in a right year. what about the month?
						$repeatMonths = explode(",", $rrule->value("BYMONTH"));
						if (in_array($thisMonth, $repeatMonths)) {
							// we're in a right month. what about the day?
							if ($repeatOnWeekDay = $rrule->value("BYDAY")) {
								// we're repeating on a specific day of a specific week
								// rather than the original day
								$whichDay = substr($repeatOnWeekDay, strlen($repeatOnWeekDay)-2, 2);
								$whichDay = dayNumber($whichDay);
								$whichWeek = substr($repeatOnWeekDay, 0, strlen($repeatOnWeekDay)-2);
								$oneWeekLess = ($whichWeek > 0) ? $whichWeek - 1 : $whichWeek + 1;
								if ($thisDayNumber == $whichDay) {
									// right day of week. right week?
									// [TO DO]: Is this faster or slower than % arithmetic?
									if (checkdate($thisMonth, $thisDay-(7 * $oneWeekLess), $thisYear)
										&& !checkdate($thisMonth, $thisDay-(7 * $whichWeek), $thisYear)) {
											// [TO DO]: What would be the best order of checks speed-wise?
											// right week. have we exceeded count?
											if (!$count) return true;
											// Are there any instances in the start month?
											if (in_array($startMonth, $repeatMonths)) {
												// We need to know on what day the repeat date falls in the first month
												// so we can figure out if there's a repeated instance that month.
												// First, get the day of the week for the first of the start month:
												$firstOfStartMonth = strtotime(substr($this->basicDates[0], 0, 8) . "01");											
												$firstOfStartMonth_dayOfWeek = strftime("%u", $firstOfStartMonth) % 7;									
												// Then, get the first instance of the day of the week we repeat on:
												$distanceToRepeatedDay = $whichDay - $firstOfStartMonth_dayOfWeek;
												if ($distanceToRepeatedDay < 0) $distanceToRepeatedDay += 7;
												$firstInstanceOfRepeatedDay = 1 + $distanceToRepeatedDay;
												// Now get the actual day of the month:
												if ($whichWeek > 0) {
													$firstMonthDayOfRepeatedInstance = $firstInstanceOfRepeatedDay
														+ (7 * ($whichWeek-1));
												}
												else {
													for ($k=5;$k>0;$k--) {
														$myDay = $firstInstanceOfRepeatedDay + (($k-1)*7);
														if (checkdate($thisMonth, $myDay, $thisYear)) {
															$lastInstanceOfRepeatedDay = $myDay;
															break;
														}
													}
													$firstMonthDayOfRepeatedInstance = $lastInstanceOfRepeatedDay
														+ (7 * ($whichWeek+1));
												}
												if ($firstMonthDayOfRepeatedInstance > $startDayOfMonth) {
													$repeatedInstancesInFirstMonth = 1;
												}
												else {
													$repeatedInstancesInFirstMonth = 0;
												}
											}
											else {
												$repeatedInstancesInFirstMonth = 0;
											}
											
											// Calc # instances in start year and this year
											$repeatedInstancesInFirstYear = $repeatedInstancesInFirstMonth;
											$repeatedInstancesInThisYear = 0;
											if ($startYear == $thisYear) {
												if ($repeatedInstancesInFirstMonth && ($thisMonth == $startMonth))
													return true;
													
												foreach($repeatMonths as $myRepeatMonth) {
													if (($myRepeatMonth > $startMonth) && ($myRepeatMonth < $thisMonth)) {
														$repeatedInstancesInFirstYear++;
													}
												}
												
												if ($repeatedInstancesInFirstYear < $count) return true;
											}
											else {
												foreach($repeatMonths as $myRepeatMonth) {
													if ($myRepeatMonth > $startMonth)
														$repeatedInstancesInFirstYear++;
													if ($myRepeatMonth < $thisMonth) {
														$repeatedInstancesInThisYear++;
													}
												}
												
												// Calc # instances in years between start and this year
												$numberOfInterveningRepeatYears = (int)(($thisYear - $startYear - 1) / $interval);
												$numberOfInterveningInstances = $numberOfInterveningRepeatYears * count($repeatMonths);
												
												$instanceCountToDate = $repeatedInstancesInFirstYear
																	 + $numberOfInterveningInstances
																	 + $repeatedInstancesInThisYear;
												if ($instanceCountToDate < $count) return true;
											}
									
									}		
								}
							}
							else {
								// we're repeating on the original day. still in right month...
								if ($thisDay == $startDayOfMonth) {
									// right day. have we exceeded count?
									if (!$count) return true;
									
									$instanceCountToDate = 0;
									if ($thisYear == $startYear) {
										foreach($repeatMonths as $myRepeatMonth) {
											if (($myRepeatMonth > $startMonth) && ($myRepeatMonth < $thisMonth)) {
												$instanceCountToDate++;
											}
										}
										if ($instanceCountToDate < $count) return true;
									}
									else {
										// calc # instances in first and current years
										$repeatedInstancesInFirstYear = 0;
										$repeatedInstancesThisYear = 0;
										foreach($repeatMonths as $myRepeatMonth) {
											if ($myRepeatMonth > $startMonth)
												$repeatedInstancesInFirstYear++;
											if ($myRepeatMonth < $thisMonth)
												$repeatedInstancesThisYear++;
										}
										
										// calc # intervening instances
										$numberOfInterveningRepeatYears = (int)(($thisYear - $startYear - 1) / $interval);
										$numberOfInterveningInstances = $numberOfInterveningRepeatYears * count($repeatMonths);
										
										$instanceCountToDate = $repeatedInstancesInFirstYear
															 + $numberOfInterveningInstances
															 + $repeatedInstancesThisYear;
															 
										if ($instanceCountToDate < $count) return true;
									}
								}
							}
						}
					}
					break;
			}
		}
		return false;	
	}
	
	function calcDates() {
		// Private function.
		
		// This function returns all dates spanned by the event. This
		// does not include repeated instances governed by an RRULE.
		
		// At least in iCal, if an item has times included, it's one day only. 
		// If it's untimed, then its end date is the day after it ends.
		
		// [TO DO] If, in fact, getDates() is only going to be called once on
		// any item then this should be modified since it stores extra data
		// in the dates array for each item that's only needed on the Calendar
		// level. Wait and see though.
		
		// [TO DO] May want to redo the date storage. Ultimately just need start
		// and duration, rather than each day on which it occurs (as long as lookups
		// can remain fast).
		
		if ($this->type == "VEVENT") {
			// Calculate basic dates -- not taking repetition into account
			$start = $this->startDate();
			if (!$start) {
				return NULL;
			}
			$end = $this->endDate();
			if (!$end) {
				return array($start);
			}
 
			$this->basicDates = array();
			for ($i=$start; $i < $end; $i = strtotime("+1 day", $i)) {
				$this->basicDates[] = strftime("%Y-%m-%d", $i);
			}
		}
	}
	
	function getDates() {
		if (!$this->basicDates) {
			$this->calcDates();
		}
		return $this->basicDates;
	}
	
	function summary($maxlength = 0) {
		$sum = $this->getProperty("SUMMARY");
		if (!$sum) return NULL;
		$sum = stripslashes($sum->value());
		if (!$sum) return NULL;
		if ($maxlength) {
			if ($maxlength < strlen($sum)) {
				return substr($sum, 0, $maxlength-3) . "...";
			}
			return substr($sum, 0, $maxlength);
		}
		return $sum;
	}
	
	function startDate() {
		if (!$this->startDateCache) {
			$prop = $this->getProperty("DTSTART");
			if ($prop->parameter("VALUE")) {
				$this->startDateCache = strtotime($prop->value());
			}
			else {
				$rawValue = $prop->value();
				$datePart = substr($rawValue, 0, 8);
				$timePart = substr($rawValue, 9, 4);
				$this->startDateCache = strtotime("${datePart} ${timePart}");
			}
		}
		return $this->startDateCache;
	}
	
	function _durationMatchError() {
		trigger_error('Your calendar file appears to have an event with unintelligible duration.', E_USER_WARNING);
	}
	
	function endDate() {
		if (!$this->endDateCache) {
			$prop = $this->getProperty("DTEND");
			if ($prop) {
				// DTEND is defined; could be either type of event
				if ($prop->parameter("VALUE")) {
					// iCal 1.0 stores all-day events by including a VALUE parameter
					// set to DATE for the DTEND property, and then setting the
					// property's value to a date stamp (no time). Timed events don't
					// have a VALUE parameter at all, so here it's sufficient to check
					// for the VALUE parameter's existence.
					$this->endDateCache = strtotime($prop->value());
					$this->isAllDayCache = 1;
				}
				else {
					$rawValue = $prop->value();
					$datePart = substr($rawValue, 0, 8);
					$timePart = substr($rawValue, 9, 4);
					$this->endDateCache = strtotime("${datePart} ${timePart}");
					$this->isAllDayCache = 0;
				}
			}
			else {
				// length is defined by a DURATION property
				$prop = $this->getProperty("DURATION");

				/* -----------
				[DAF-060426] Duration handling should now match the iCalendar spec. A few notes:
				
				iCal appears not to use duration properly (according to the specification), 
				but is getting closer. iCal 1.0.2 specifies all-day events without 
				DTEND, and with a DURATION of P1D.
				
				As in the previous version, I'm doing an explicit check for a duration of P1D for iCal
				all-day events, but in the case where there isn't an exact P1D match I'm now doing
				more robust parsing of duration values.
				-------------- */

				if ($prop->value() == "P1D") {
					// This is an all-day event (generated by iCal 1.0.2+)
					$this->isAllDayCache = 1;
					
					$startProp = $this->getProperty("DTSTART");
					$startTime = $startProp->value();
					$this->endDateCache = strtotime("+1 day", strtotime($startTime));
				}
				else {
					// [TO DO] optimize?
					// not an all-day iCal event. Parse duration.
					// First, get date and time components
					$parts = explode('T', substr($prop->value(), 1));
						
					$interval = 0;
						
					// Process date component, if present
					if ($parts[0]) {
						$submatch = array();
						if (preg_match('/(\d+)W/', $parts[0], $submatch)) {
							$interval = $submatch[1] * 604800;
							$submatch = array();
						}
						if (preg_match('/(\d+)D/', $parts[0], $submatch)) {
							$interval += $submatch[1] * 86400;
						}
					}
					
					// Process time component, if present
					if ((count($parts) > 1) && $parts[1]) { 
						$submatch = array();
						if (preg_match('/(\d+)H/', $parts[1], $submatch)) {
							$interval += $submatch[1] * 3600;
							$submatch = array();
						}
						if (preg_match('/(\d+)M/', $parts[1], $submatch)) {
							$interval += $submatch[1] * 60;
							$submatch = array();
						}
						if (preg_match('/(\d+)S/', $parts[1], $submatch)) {
							$interval += $submatch[1];
						}
					}
					
					// interval now contains the duration in seconds
					
					$this->isAllDayCache = 0;
					$this->endDateCache = $this->startDate() + $interval;
				}


			}
		}

		return $this->endDateCache;
	}
	
	function isAllDay() {
		if ($this->isAllDayCache === NULL) {
			$this->endDate();
		}
		return $this->isAllDayCache;
	}
	
	/*
	function description() {
		$prop = $this->getProperty("DESCRIPTION");
		if (!$prop) return "";
		
		return $prop->value();
	}
	
	function getInfo() {
		$result = array();
		$result["type"] = ($this->isAllDay() ? "all day" : "standard";
		$result["summary"] = $this->summary();
		$result["title"] = "iWebCal Event";
		$result["note"] = $this->description();
		$result["description"] = &$result["note"];
		
		return $result;
	}
	*/
	
	function display($displayType, $linkString="javascript:;") {
		switch ($displayType) {		
			case "month":
				$myClass = $this->isAllDay() ? "AllDayItem" : "TimedItem";		
				if ($linkString == "#"){
					$myClass .= " NoHREF";
				} else {
					$linkString = JRoute::_($linkString);
				}
					
				echo "<a class=\"${myClass}\" href=\"${linkString}\" ";
				echo "title=\"";
				echo $this->summary();
				echo "\">";
				echo $this->summary(22);
				echo "</a>";
				return true;
				break;
			case "week":
			case "day":
				$description = $this->descriptionPopup();
				$summaryLength = ($displayType == "week") ? ($description ? 23 : 24) : 0;
				if (!$this->isAllDay()) {
					?><div class="Header"><?php
						list($myStartHour, $myStartMinute, $myStartAMPM) = explode(",", strftime("%I,%M,%p", $this->startDate()));
						$myStartHour = (int)$myStartHour;
						$myStartAMPM = strtolower($myStartAMPM);
						if ($myStartMinute == "00")
							$myStartMinute = "";
						else
							$myStartMinute = ":" . $myStartMinute;
						if ($displayType == "day") echo "<strong>";
						echo "${myStartHour}${myStartMinute}${myStartAMPM}";
						if ($displayType == "day") echo "</strong>";
	
						if ($displayType == "day") {
							list($myEndHour, $myEndMinute, $myEndAMPM) = 
								explode(",", strftime("%I,%M,%p", $this->endDate()));
							$myEndHour = (int)$myEndHour;
							$myEndAMPM = strtolower($myEndAMPM);
							if (!(int)$myEndMinute)
								$myEndMinute = "";
							else
								$myEndMinute = ":" . $myEndMinute;
							echo " - ${myEndHour}${myEndMinute}${myEndAMPM}";
						}
						echo $description;
					?></div><?php
				}
				// TODO - Any way to get a title without an anchor tag? Or, barring that, set the status bar in a more helpful manner.
				echo "<a href=\"javascript:;\" class=\"Summ\"";
				echo " title=\"";
				echo $this->summary();
				echo "\">";
				echo $this->summary($summaryLength);
				echo "</a>";
 				return true;
				break;
		}
		echo "<p><b>iWebCal Error:</b> Invalid calendar display type.</p>";
		return false;
	}			

	function descriptionPopup() {
		global $iWebCal_URL_PATH;
		
		$items = array();
		
		if ($prop = $this->getProperty("LOCATION"))
			$items["Location"] = $prop->value();
		if ($prop = $this->getProperty("ATTENDEE")) {
			// This is a multi-instance property, so we get an array.
			$items["Attendees"] = array();
			foreach($prop as $p) {
				$thisAttendee = '<a href="' . JRoute::_($p->value()) . '">';
				if ($cn = $p->parameter("CN"))
					$thisAttendee .= str_replace('"', '', $cn);
				else
					$thisAttendee .= $p->value();
				$thisAttendee .= "</a>";
				$items["Attendees"][] = $thisAttendee;
			}
		}
		if ($prop = $this->getProperty("URL")) {
			$val = $prop->value();
			$items["URL"] = '<a target="_iWebCal_ext_viewer" href="' . JRoute::_($val) . '">' . $val . '</a>';
		}
		if ($prop = $this->getProperty("DESCRIPTION")) {
			$desc = str_replace("\n\n", "[[BR]]", rtrim($prop->value()));
			$desc = explode("[[BR]]", $desc);
			$items["Notes"] = array();
			foreach ($desc as $p)
				if ($p != "")
					$items["Notes"][] = $p;
		}
		if (!count($items)) return "";
		$items["summ"] = $this->summary();
		
		$popupDocString = 'index.php?option=com_iwebcal&task=details&format=raw&title=Event+Details&content=' . urlencode(serialize($items));
		//$popupDocString = $iWebCal_URL_PATH . "/base/popup-event-info.php?title=Event+Details&content=" . urlencode(serialize($items));
		$result = "<a href=\"javascript:;\" onclick=\"javascript:myWin=window.open('" .
			$popupDocString .
			"', 'iwebcal_note_win', 'width=250,height=300,left=30,top=30');\">" .
			"<img src=\"" . $iWebCal_URL_PATH . "/img/note-button.gif\" " .
			"width=\"10\" height=\"9\" border=\"0\">" .
			"details</a>";
		return $result;
	}

}


?>
