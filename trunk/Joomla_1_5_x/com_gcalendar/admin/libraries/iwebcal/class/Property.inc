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
 * Property.inc: Defines the Property class, which handles parsing and
 * storage of event properties.
 *
 * File version 2.0b1, last modified April 11, 2005.
 */

class Property {
	var $name;
	var $parameters;
	var $values;
	var $config = array();
	
	function Property($inputLine = NULL, $config) {
		global $MULTI_VALUE_PROPERTIES;
		$this->name = NULL;
		$this->parameters = array();
		$this->value = array();
		
		if ($inputLine) {
			// [TO DO] The 1.1 modifications are coded a bit sloppily. They could probably be more efficient.
			// This includes avoidance of backslashed semicolons and distinguishing between multi-value
			// and single-value properties. The former is less-than-perfect because there seems to be a
			// PCRE bug that prevents proper recognition of the pattern /[^\\];/: The escaped backslash
			// causes PHP to think the right bracket is escaped too.
			
			// remove trailing newline and whitespace
			$inputLine = rtrim($inputLine);
			$inputLine = str_replace("\\n", "\n", $inputLine);
			$halves = explode(":", $inputLine, 2);
			
			$firstHalf = rtrim(array_shift($halves));
			$secondHalf = rtrim(array_shift($halves));
			
			// separate out parameter and value components
			$firstHalf = str_replace("\\;", "iWebCal_ACTUAL_SEMICOLON_INSTANCE", $firstHalf);
			$raw_params = explode(";", $firstHalf);
			for ($i=0;$i<sizeof($raw_params);$i++) {
				$raw_params[$i] = str_replace("iWebCal_ACTUAL_SEMICOLON_INSTANCE", ";", $raw_params[$i]);
			}
			
			$secondHalf = str_replace("\\;", "iWebCal_ACTUAL_SEMICOLON_INSTANCE", $secondHalf);
			// iCal seems to backslash commas. But since I see no indication of an actual use for non-backslashed
			// commas, and since the vCalendar specification makes no mention of one either, for now we just
			// get rid of the backslash in values.
			$secondHalf = str_replace("\\,", ",", $secondHalf);
			
			
			$raw_values = explode(";", $secondHalf);
			for ($i=0;$i<sizeof($raw_values);$i++) {
				$raw_values[$i] = str_replace("iWebCal_ACTUAL_SEMICOLON_INSTANCE", ";", $raw_values[$i]);
			}
			
			// get property name and remove from param list
			/*
			 * 4/8/05: Added patch 813763 supplied by friggo to trim
			 * whitespace found in Mozilla Calendar files. Note that the
			 * patch was originally provided for Calendar.inc and has been
			 * moved here for object-oriented reasons. The patch itself
			 * is simply the addition of the trim() call. --df
			 */
			$this->name = trim(array_shift($raw_params));
			
			// separate param and value names from their values
			if (in_array($this->name, $config['MULTI_VALUE_PROPERTIES'])) {
				for ($i=0;$i<count($raw_values);$i++) {
					$mySplit = explode("=", $raw_values[$i]);
					if (count($mySplit) == 1) {
						$this->values[0] = $mySplit[0];
					}
					else {
						$this->values[$mySplit[0]] = $mySplit[1];
					}
				}
			}
			else {
				$this->values[0] = implode("", $raw_values);
			}
			for ($i=0;$i<count($raw_params);$i++) {
				$mySplit = explode("=", $raw_params[$i]);
				if (count($mySplit) == 1) {
					$this->parameters[0] = $mySplit[0];
				}
				else {
					$this->parameters[$mySplit[0]] = $mySplit[1];
				}
			}
		}
	}
	
	function match($mName="any", $mVals="any", $mProps="any") {
		// mVals and mProps should be associative arrays
		$result = true;
		if (!(($mName == "any") || ($mName == $this->name))) {
			$result = false;
		}
		if ($result && !($mVals == "any")) {
			if (is_string($mVals)) {
				if ($mVals != $this->values[0]) {
					$result = false;
				}
			}
			elseif (count($mVals) != count($this->values)) {
				$result = false;
			}
			else {
				foreach($mVals as $key => $value) {
					if ($this->values[$key] != $value) {
						$result = false;
						break;
					}
				}
			}
		}
		if ($result && !($mProps == "any")) {
			if (is_string($mProps)) {
				if ($mProps != $this->properties[0]) {
					$result = false;
				}
			}
			elseif (count($mProps) != count($this->values)) {
				$result = false;
			}
			else {
				foreach($mProps as $key => $value) {
					if ($this->properties[$key] != $value) {
						$result = false;
						break;
					}
				}
			}
		}
						
		return $result;
	}
	
	function parameter($name) {
		if (isset($this->parameters[$name])) {
			return $this->parameters[$name];
		}
		return "";
	}
	
	function value($name = 0) {
		if (isset($this->values[$name])) {
			return $this->values[$name];
		}
		return "";
	}
	
	function dprint() {
		echo "<p>";
		echo "<b>$this->name</b><br>";
		
		$count = count($this->parameters);
		if ($count) {
			if (($count == 1) && (isset($this->parameters[0]))) {
				echo "Parameter: ";
				echo $this->parameters[0];
				echo "<br>";
			}
			else {			
				echo "Parameters:<br>";
				foreach($this->parameters as $key => $value) {
					echo "&nbsp;&nbsp;&nbsp;&nbsp;$key : $value<br>";
				}
			}
		}

		$count = count($this->values);
		if ($count) {
			if (($count == 1) && ($this->values[0])) {
				echo "Value: ";
				echo $this->values[0];
				echo "<br>";
			}
			else {			
				echo "Values:<br>";
				foreach($this->values as $key => $value) {
					echo "&nbsp;&nbsp;&nbsp;&nbsp;$key : $value<br>";
				}
			}
		}

		echo "</p>";
	}
		
}


?>
