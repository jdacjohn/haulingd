<?php
/*---------------------------------------------
common functions file for PHP (1.5.3)
Created: 2004 by Chris Bloom [ xangelusx@hotmail.com ]
Last Updated: 2007-08-27 Chris Bloom [ xangelusx@hotmail.com ]
http://www.csb7.com/

common functions file for PHP
	Summary:
		Provides commonly used custom functions for use in PHP projects.

	Usage:
		Include the file using a php include() or require() call.

	Version History:
		Version: 1.5.3
		Date: 2007-08-27
		Author: Chris Bloom
		Version Notes:
			Added function w and w_self, shortcuts for outputting text

		Version: 1.5.2
		Date: 2007-06-26
		Author: Chris Bloom
		Version Notes:
			Added function param, which closely mimics getParam/getVar, but is a bit more robust.

		Version: 1.5
		Date: 2006-01-29
		Author: Chris Bloom
		Version Notes:
			Updated getSQLValueString and getVar functions to better handle arrays
			Updated regular expression in checkEmail function
			Added mytimestamp format validation in myts_date
			Changed myts_date to use mktime2 instead of mktime
			Added mktime2 function

		Version: 1.4
		Date: 2005-09-07
		Author: Chris Bloom
		Version Notes:
			Fixed bugs in several functions.

		Version: 1.3
		Date: 2005-08-19
		Author: Chris Bloom
		Version Notes:
			Updated printvar to include optional label text and styling.

		Version: 1.2
		Date: 2005-03-30
		Author: Chris Bloom
		Version Notes:
			Added some new functions for outputing text.
			Re-named some of the old output functions (but left original names as aliases)

		Version: 1.0
		Date: Circa 2004
		Author: Chris Bloom
		Version Notes:
			Original version for PHP

	To Do:
		- Clean up old output functions and bump version

	Notes:
		Please feel free to email me with comments or suggestions:
			xangelusx@hotmail.com
		The code is provided free of charge for non-commercial use so long as you leave all comments intact.
		Please get in touch for commercial licensing information.
---------------------------------------------*/

## Constants for newFieldArray()
define('THE_VALUE', 0);
define('THE_TYPE', 1);
define('THE_DEFINED_VALUE', 2);
define('THE_NOT_DEFINED_VALUE', 3);
define('FIELD_IS_ARRAY', 4);
define('ALLOW_NULL_VALUE', 5);

## Misc constants
if (!defined('ARRAY_GLUE')) define('ARRAY_GLUE', "||");
if (!defined('POSTBACK_PARAMETER_PREFIX')) define('','__postback__');

## Begin common functions

/**
 * Like the native array_unique, but does not maintain index values
 *
 * Returns a new array with unique values from the original array
 *
 * @param string Array to filter
 */
function array_unique_noindex($old){
	/*
	Summary:
		Like the native array_unique, but does not maintain index values

	Usage:
		array_unique_noindex(array old)

	Returns:
		old array with unique values
	*/

	$new = array();
	for($i=0;$i<count($old);++$i){
		if(in_array($old[$i], $new) != "true"){
			$new[] = $old[$i];
		}
	}
	return $new;
}

function br2nlX($input = "") {
	/*
	Summary:
		The reverse of nl2brX - replaces all BR tags (XHTML or HTML) with newlines. Accounts for any newlines that follow the BR tag

	Usage:
		br2nlX(string input)

	Returns:
		input with all BR tags replaced with newlines.
	*/
	return preg_replace("/<br( *\/)?".">(\r?\n)?/i", "\r\n",$input);
}

function checkEmail($sEmail) {
	return check_email($sEmail);
}

function check_email($email) {
	$email = preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/i", trim($email));
	return $email;
}

function check_phone($phone) {
	$phone = preg_replace('/[^0-9]/', '', $phone);
	$phone = preg_match("/^[0-9]{10,11}$/i", trim($phone));
	return $phone;
}

function fixNull($str2clean = "") {
	/*
	Summary:
		Changes NULL values to empty strings.

	Usage:
		fixNull(string str2clean)

	Returns:
		str2clean with "NULL" or NULL constant changed to an empty string ("")
	*/

	if (is_null($str2clean) || $str2clean == "NULL") {
		return "";
	} else {
		return $str2clean;
	}
}

function formOutput($str2clean = "") {
	/*
	Summary:
		Alias of outputFormData
	*/

	return outputFormData($str2clean);
}

function getMySQLTimestamp($unixTimestamp = 0) {
	/*
	Summary:
		Returns a valid MySQL timestamp (yyyymmddhhmmss) from $unixTimestamp

	Parameters:
		int unixTimestamp : a unix style timestamp (seconds since the epoch)

	Usage:
		getMySQLTimestamp(int unixTimestamp)

	Returns:
		A valid MySQL timestamp (yyyymmddhhmmss) from $unixTimestamp
	*/

	if (intval($unixTimestamp) == 0) $unixTimestamp = time();
	$myDate = date("YmdHis", $unixTimestamp);
	return $myDate;
}

function getSQLValueString($theValue, $theType = "text", $theDefinedValue = "", $theNotDefinedValue = "", $theArrayGlue = ARRAY_GLUE, $allowNullValues = true) {
	/*
	Summary:
		Formats a string for use in a SQL statement. Adjust below for MySQL or SQL Server

	Usage:
		getSQLValueString(string theValue, string theType, string theDefinedValue, string theNotDefinedValue, string theArrayGlue)
			theValue: The value to be formatted

			theType: a string representing the data type (text, long, int, double, float, date, defined)

			theDefinedValue: the value to use if theType == "defined" and theValue != ""

			theNotDefinedValue: the value to use if theType == "defined" and theValue == ""

			theArrayGlue: the string to join and split theValue with if it is an array

			allowNullValues: whether an empty value should return NULL or just an empty quoted string

	Returns:
		theValue formatted for use in a SQL statement
	*/

	//handle any multiple-item select fields
	if (is_array($theValue)) {
		foreach ($theValue as $key => $value) {
			$theValue[$key] = trim((get_magic_quotes_gpc()) ? stripslashes($theValue[$key]) : $theValue[$key]); //for MySQL
		}

		$theValue = join($theArrayGlue, $theValue);
	} else {
		$theValue = trim((get_magic_quotes_gpc()) ? stripslashes($theValue) : $theValue); //for MySQL
		//$theValue = str_replace("'", "''", (get_magic_quotes_gpc()) ? stripslashes($theValue) : $theValue); //for SQL Server
	}

	switch ($theType) {
		case "long":
		case "int":
			$theValue = ($theValue != "") ? intval($theValue) : (($allowNullValues) ? "NULL" : "0");
			break;
		case "double":
		case "float":
			$theValue = ($theValue != "") ? doubleval($theValue) : (($allowNullValues) ? "NULL" : "0");
			break;
		case "date":
			$theValue = ($theValue != "") ? "'" . mysql_real_escape_string($theValue) . "'" : (($allowNullValues) ? "NULL" : "''");
			break;
		case "defined":
			$theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;
			break;
		case "raw":
			//passthru
			break;
		default: //text
			$theValue = ($theValue != "") ? "'" . mysql_real_escape_string($theValue) . "'" : (($allowNullValues) ? "NULL" : "''");
			break;
	}

	return $theValue;
}

function getParam($paramName, $fieldIsArray = false, $theDefaultValue = "", $theArrayGlue = ARRAY_GLUE) {
	/*
	Summary:
		Checks the POST and GET collections for any values with the paramName key and returns the value.

	Usage:
		getParam(string paramName, boolean fieldIsArray, string theDefaultValue)
			paramName: The key to look for

			fieldIsArray: a boolean value indicating if the paramName key references a value that is an array

			theDefaultValue: the value to return if the paramName key was not found

	Returns:
		the value of the paramName key from the POST or GET collections, or theDefaultValue
	*/

	$theField = "";

	if (isset($_POST[$paramName])) {
		$theField = $_POST[$paramName];
	}
	elseif (isset($_GET[$paramName])) {
		$theField = $_GET[$paramName];
	}

	//printvar($theField,"getParam($paramName, $fieldIsArray, $theDefaultValue, $theArrayGlue)");

	if ($fieldIsArray) {
		//for multiple-item select fields
		if (!is_array($theField)) {
			if (trim($theField) == "") {
				$theField = (($theDefaultValue == "") ? array() : explode($theArrayGlue, $theDefaultValue));
			} else {
				$theField = explode($theArrayGlue, $theField);
			}
		}
	} elseif (is_array($theField)) {
		//the field that was requested is an array but was not requested as one, convert to string
		$theField = ((!sizeof($theField)) ? $theDefaultValue : explode($theArrayGlue, $theField));
	} elseif (trim($theField) == "") {
		$theField = $theDefaultValue;
	}

	return $theField;
}

function getParam2($paramName, $fieldIsArray = false, $theDefaultValue = "", $theArray = array(), $theArrayGlue = ARRAY_GLUE) {
	/*
	Summary:
		Like getParam, but can check a specified array in addition.

	Usage:
		getParam2(string paramName, boolean fieldIsArray, string theDefaultValue, array theArray)
			paramName: The key to look for

			fieldIsArray: a boolean value indicating if the paramName key references a value that is an array

			theDefaultValue: the value to return if the paramName key was not found

			theArray: the array to search for the paramName key in if it can't be found via getParam

	Returns:
		the value from a call to getParam, of the value of the paramName key from theArray, or theDefaultValue
	*/

	$tryGetParam = getParam($paramName, false, false);
	if ($tryGetParam === false) {
		if (isset($theArray[$paramName])) {
			if ($fieldIsArray) {
				//for multiple-item select fields
				return ($theArray[$paramName] == "") ? array() : explode($theArrayGlue, $theArray[$paramName]);
			} else {
				return $theArray[$paramName];
			}
		}
		else {
			if ($fieldIsArray) {
				//for multiple-item select fields
				return ($theDefaultValue == "") ? array() : explode($theArrayGlue, $theDefaultValue);
			} else {
				return $theDefaultValue;
			}
		}
	} else {
		return getParam($paramName, $fieldIsArray, $theDefaultValue);
	}
}

function getParamInArray($paramName, $fieldIsArray = false, $theDefaultValue = "", $theArray = array(), $theArrayGlue = ARRAY_GLUE) {
	/*
	Summary:
		Like getParam2, but it does not rely on any calls to getParam.

	Usage:
		getParamInArray(string paramName, boolean fieldIsArray, string theDefaultValue, array theArray)
			paramName: The key to look for

			fieldIsArray: a boolean value indicating if the paramName key references a value that is an array

			theDefaultValue: the value to return if the paramName key was not found

			theArray: the array to search for the paramName key in if it can't be found via getParam

	Returns:
		the value of the paramName key from theArray, or theDefaultValue
	*/

	if (isset($theArray[$paramName])) {
		if ($fieldIsArray) {
			//for multiple-item select fields
			return ($theArray[$paramName] == "") ? array() : explode($theArrayGlue, $theArray[$paramName]);
		} else {
			return $theArray[$paramName];
		}
	} else {
		if ($fieldIsArray) {
			//for multiple-item select fields
			return ($theDefaultValue == "") ? array() : explode($theArrayGlue, $theDefaultValue);
		} else {
			return $theDefaultValue;
		}
	}
}

function HTMLOutput($str2clean = "") {
	/*
	Summary:
		Alias of outputFormattedText
	*/

	return outputFormattedText($str2clean);
}

function in_array_robust($needle = "", $haystack = "", $strict = false) {
	/*
	Summary:
		Like the native in_array() function but will also check the strpos of needle if haystack is a string

	Usage:
		in_array_robust(string needle, mixed haystack, boolean strict)
		  needle: the value to search for

			haystack: the array or string to search through

			strict: if strict is set to TRUE and haystack is an array, then the function will also compare the type of the needle in the haystack.

	Returns:
		TRUE or FALSE
	*/

	if (is_array($haystack)) {
		return in_array($needle, $haystack, $strict);
	}
	else {
		return ((strpos($haystack, $needle) !== false) ? true : false);
	}
}

function join_2d($glue = ARRAY_GLUE, $pieces = array(), $dimension = 0, $arrFunctions = "") {
	/*
	Summary:
	  Like the native join (aka implode) function, but joins the values of a single dimension in a 2-d array

	Usage:
		join_2d(string glue, boolean pieces, mixed dimension, array arrFunctions)
		  glue: the string to join the values with

		  pieces: the 2-dimensional array to look through

		  dimension: the dimension to join. Can be a positive integer or a named index

		  arrFunctions: An array of functions to apply to each element before the join
		    ("func(arg1, arg2, arg3, ...)", ...)
		    Use %val% to insert the element value as an argument

	Returns:
		Returns a string containing a string representation of all the array elements in the dimension dimension in the same
		order, with the glue string between each element.
	*/

	$rtn = array();
	foreach ($pieces as $value) {
		if (isset($value[$dimension])) {
			$tmp = $value[$dimension];
			if (is_array($arrFunctions) && sizeof($arrFunctions) > 0) {
				foreach ($arrFunctions as $func) {
					$sFuncName = trim(substr($func,0,strpos($func,"(")));
					$sFunc = "\$tmp = " . str_replace("%val%","\$tmp",$func).";";
					if ($sFuncName > "" && function_exists($sFuncName)) {
						eval($sFunc);
					}
				}
			}
			$rtn[] = $tmp;
		}
	}
	return join($glue, $rtn);
}

function mktime2 ($hour = false, $minute = false, $second = false, $month = false, $date = false, $year = false) {
	//This isn't working right, so I'm switching to the adodb_mktime implimentation. This function will merely
	//be a wrapper for existing calls.

	if (!function_exists('adodb_mktime')) {
		/**
			Return a timestamp given a local time. Originally by jackbbs.
			Note that $is_dst is not implemented and is ignored.

			Not a very fast algorithm - O(n) operation. Could be optimized to O(1).
		*/
		function adodb_mktime($hr,$min,$sec,$mon=false,$day=false,$year=false,$is_dst=false,$is_gmt=false)
		{
			if (!defined('ADODB_TEST_DATES')) {

				if ($mon === false) {
					return $is_gmt? @gmmktime($hr,$min,$sec): @mktime($hr,$min,$sec);
				}

				// for windows, we don't check 1970 because with timezone differences,
				// 1 Jan 1970 could generate negative timestamp, which is illegal
				if (1971 < $year && $year < 2038
					|| !defined('ADODB_NO_NEGATIVE_TS') && (1901 < $year && $year < 2038)
					) {
						return $is_gmt ?
							@gmmktime($hr,$min,$sec,$mon,$day,$year):
							@mktime($hr,$min,$sec,$mon,$day,$year);
					}
			}

			$gmt_different = ($is_gmt) ? 0 : adodb_get_gmt_diff();

			/*
			# disabled because some people place large values in $sec.
			# however we need it for $mon because we use an array...
			$hr = intval($hr);
			$min = intval($min);
			$sec = intval($sec);
			*/
			$mon = intval($mon);
			$day = intval($day);
			$year = intval($year);


			$year = adodb_year_digit_check($year);

			if ($mon > 12) {
				$y = floor($mon / 12);
				$year += $y;
				$mon -= $y*12;
			} else if ($mon < 1) {
				$y = ceil((1-$mon) / 12);
				$year -= $y;
				$mon += $y*12;
			}

			$_day_power = 86400;
			$_hour_power = 3600;
			$_min_power = 60;

			$_month_table_normal = array("",31,28,31,30,31,30,31,31,30,31,30,31);
			$_month_table_leaf = array("",31,29,31,30,31,30,31,31,30,31,30,31);

			$_total_date = 0;
			if ($year >= 1970) {
				for ($a = 1970 ; $a <= $year; $a++) {
					$leaf = _adodb_is_leap_year($a);
					if ($leaf == true) {
						$loop_table = $_month_table_leaf;
						$_add_date = 366;
					} else {
						$loop_table = $_month_table_normal;
						$_add_date = 365;
					}
					if ($a < $year) {
						$_total_date += $_add_date;
					} else {
						for($b=1;$b<$mon;$b++) {
							$_total_date += $loop_table[$b];
						}
					}
				}
				$_total_date +=$day-1;
				$ret = $_total_date * $_day_power + $hr * $_hour_power + $min * $_min_power + $sec + $gmt_different;

			} else {
				for ($a = 1969 ; $a >= $year; $a--) {
					$leaf = _adodb_is_leap_year($a);
					if ($leaf == true) {
						$loop_table = $_month_table_leaf;
						$_add_date = 366;
					} else {
						$loop_table = $_month_table_normal;
						$_add_date = 365;
					}
					if ($a > $year) { $_total_date += $_add_date;
					} else {
						for($b=12;$b>$mon;$b--) {
							$_total_date += $loop_table[$b];
						}
					}
				}
				$_total_date += $loop_table[$mon] - $day;

				$_day_time = $hr * $_hour_power + $min * $_min_power + $sec;
				$_day_time = $_day_power - $_day_time;
				$ret = -( $_total_date * $_day_power + $_day_time - $gmt_different);
				if ($ret < -12220185600) $ret += 10*86400; // if earlier than 5 Oct 1582 - gregorian correction
				else if ($ret < -12219321600) $ret = -12219321600; // if in limbo, reset to 15 Oct 1582.
			}
			//print " dmy=$day/$mon/$year $hr:$min:$sec => " .$ret;
			return $ret;
		}
	}

	return adodb_mktime($hour, $minute, $second, $month, $date, $year);

	/*
	Summary:
	  similar to the native mktime function except that it can handle dates prior to 1970

	Returns:
		number of seconds since the epoch (1970-01-01 00:00:00). Dates prior to the epoch will
		be expressed with a negative value
	* /

	// A note about leap years:
	// For centuries, the Egyptians used a (12 * 30 + 5)-day calendar
	// The Greek began using leap-years in around 400 BC
	// Ceasar adjusted the Roman calendar to start with Januari rather than March
	// All knowledge was passed on by the Arabians, who showed an error in leaping
	// In 1232 Sacrobosco (Eng.) calculated the error at 1 day per 288 years
	// In 1582, Pope Gregory XIII removed 10 days (Oct 15-24) to partially undo the
	// error, and he instituted the 400-year-exception in the 100-year-exception,
	// (notice 400 rather than 288 years) to undo the rest of the error
	// From about 2044, spring will again coincide with the tropic of Cancer
	// Around 4100, the calendar will need some adjusting again

	if ($hour === false)   $hour = date("G");
	if ($minute === false) $minute = date("i");
	if ($second === false) $second = date("s");
	if ($month === false)  $month = date("n");
	if ($date === false)   $date = date("j");
	if ($year === false)   $year = date("Y");

	//Send dates >= 1970 to the native mktime function
	//Note that mktime has an upper date limit somewhere in the year 2038.
	//This is reported to have been fixed in PHP 5.1.0
	if ($year >= 1970) return mktime($hour, $minute, $second, $month, $date, $year);

	// date before 1-1-1970 (Win32 Fix)
	$m_days = Array (31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
	if ($year % 4 == 0 && ($year % 100 > 0 || $year % 400 == 0))
	{
		 $m_days[1] = 29; // non leap-years can be: 1700, 1800, 1900, 2100, etc.
	}

	// go backward (-), based on $year
	$d_year = 1970 - $year;
	$days = 0 - $d_year * 365;
	$days -= floor ($d_year / 4);           // compensate for leap-years
	$days += floor (($d_year - 70) / 100);  // compensate for non-leap-years
	$days -= floor (($d_year - 370) / 400); // compensate again for giant leap-years

	// go forward (+), based on $month and $date
	for ($i = 1; $i < $month; $i++)
	{
		 $days += $m_days [$i - 1];
	}
	$days += $date - 1;

	// go forward (+) based on $hour, $minute and $second
	$stamp = $days * (24 * 60 * 60);
	$stamp += $hour * (60 * 60) - (date('Z') + (date('I') * 60 * 60));
	$stamp += $minute * 60;
	$stamp += $second;

	return $stamp;
	*/
}

function myts_date($mytimestamp, $format = "M d, Y") {
	/*
	Summary:
	  like the native date function except that it takes a mysql timestamp (yyyymmddhhiiss) or
		datetime (yyyy-mm-dd hh:ii:ss) string instead of epoch seconds for an argument

	Usage:
	  myts_date(string mytimestamp, string format)
	    mytimestamp: the timestamp or datetime string to format

	    format: the format to apply mytimestamp (same as date() function format argument)

	Returns:

		mytimestamp formatted using the format string
	*/

	$arrFind = array('-', ' ', ':');
	$mytimestamp = str_replace($arrFind, '', $mytimestamp);

	if (!preg_match("/^[0-9]{4}[0-1][0-9][0-3][0-9]([0-2][0-9][0-5][0-9][0-5][0-9])?$/i", $mytimestamp)) {
		return false;
	}
	elseif (trim($mytimestamp) == '' || !is_numeric($mytimestamp)) {
		return false;
	}
	else {
		$month = intval(substr($mytimestamp,4,2));
		$day   = intval(substr($mytimestamp,6,2));
		$year  = intval(substr($mytimestamp,0,4));
		if (strlen($mytimestamp) == 14) {
			$hour  = intval(substr($mytimestamp,8,2));
			$min   = intval(substr($mytimestamp,10,2));
			$sec   = intval(substr($mytimestamp,12,2));
		} else {
			$hour = date("G");
			$min = date("i");
			$sec = date("s");
		}

		if (
			!checkdate($month,$day,$year)
			|| $hour < 0 || $hour > 23
			|| $min < 0 || $min > 59
			|| $sec < 0 || $sec > 59
		) {
			return false;
		} else {
			//use custom mktime2 as it handles dates before 1970
			$epoch = mktime2($hour,$min,$sec,$month,$day,$year);
			$date = date($format, $epoch);
			return $date;
		}
	}
}

function newFieldArray($theValue, $theType = "text", $theDefinedValue = "", $theNotDefinedValue = "", $fieldIsArray = false, $allowNullValues = true) {
	/*
	Summary:
	  creates a new field array. For use in insert/update forms for collecting form or database data

	Usage:
	  newFieldArray(string theValue, string theType, string theDefinedValue, string theNotDefinedValue, boolean fieldIsArray)
			theValue: The value of the field

			theType: a string representing the data type (same as those used in getSQLValueString)

			theDefinedValue: the value to use if theType == "defined" and theValue != ""

			theNotDefinedValue: the value to use if theType == "defined" and theValue == ""

			fieldIsArray: a boolean value indicating if the field uses an array of values

			allowNullValues: whether an empty value should return NULL or just an empty quoted string

	Returns:
		an array with the following indexes: THE_VALUE, THE_TYPE, THE_DEFINED_VALUE, THE_NOT_DEFINED_VALUE, FIELD_IS_ARRAY, ALLOW_NULL_VALUES
	*/

	return array($theValue, $theType, $theDefinedValue, $theNotDefinedValue, $fieldIsArray, $allowNullValues);
}

function nl2brX($input = "") {
	/*
	Summary:
	  Like nl2br but returns an XHTML compatible BR

	Usage:
	  nl2brX(string input)

	Returns:
		input with all newlines prefixed with an XHTML BR tag.
	*/
	return preg_replace("/\r?\n/", "<br />\r\n",$input);
}


function outputHTML($str2clean = ""){
	/*
	Summary:
	  Outputs the contents of str2clean as-is. Use only on "safe" strings. Avoid using when the str2clean
		contains input from anonymous users as it could lead to cross-site scripting vulnerabilities.

	Usage:
	  outputHTML(string str2clean)

	Returns:
		str2clean as-is, or &nbsp; if str2clean is an empty string.
	*/

	$str2clean = fixNull($str2clean);
	if ($str2clean == "") {
		return "&nbsp;";
	} else {
		$str2clean = stripslashes($str2clean);
	}

	return $str2clean;
}

function outputFormattedText($str2clean = "") {
	/*
	Summary:
		Formats a string for safe display in HTML by encoding (but not removing) inline markup and providing
		formatting for multiple spaces, tabs and newlines. An empty or Null $str2clean value will return &nbsp;
		Useful for displaying content exactly as it would have appeared in a TEXTAREA form element.

	Usage:
		outputFormattedText(string str2clean)

	Returns:
		str2clean with HTML elements encoded
	*/

	$str2clean = fixNull($str2clean);
	if ($str2clean == "") {
		return "&nbsp;";
	} else {
		$str2clean = stripslashes($str2clean);
		$str2clean = htmlentities($str2clean); //QUOTE, LESS THAN, GREATER THAN...
		$str2clean = nl2brX($str2clean); //NEW LINE
		$str2clean = str_replace("\t", "&nbsp;&nbsp;&nbsp;&nbsp;", $str2clean); //TAB
		$str2clean = str_replace("  ", "&nbsp;&nbsp;", $str2clean); //2 SPACES
	}

	return $str2clean;
}

function outputStripHTML($str2clean = ""){
	/*
	Summary:
	  Strips HMTL code from a string to present in e-mail or other HTML-sensitive environment

	Usage:
	  outputStripHTML(string str2clean)

	Returns:
		str2clean with all HTML elements stripped out.
	*/

	$str2clean = fixNull($str2clean);

	$str2clean = stripslashes($str2clean);

	return strip_tags($str2clean);
}

function outputFormData($str2clean = "") {
	/*
	Summary:
		Encodes common HTML elements in a string. Useful for inserting text from a form element back into the form element


	Usage:
		outputFormData(string str2clean)

	Returns:
		str2clean HTML elements encoded
	*/

	$str2clean = fixNull($str2clean);

	$str2clean = stripslashes($str2clean);

	$str2clean = htmlentities($str2clean); //Maybe use htmlentities() instead?

	//Originally, only:
	//$str2clean = str_replace("\"", "&quot;", $str2clean); //QUOTE
	//$str2clean = str_replace("<", "&lt;", $str2clean); //LESS THAN
	//$str2clean = str_replace(">", "&gt;", $str2clean); //GREATER THAN

	return $str2clean;
}

function printvar($var, $label="") {
	/*
	Summary:
		Prints the output of the native print_r() function wrapped in PRE tags

	Usage:
		printvar(mixed var)

	Returns:
		The output of the native print_r() function wrapped in fancy-shmancy PRE tags
	*/

	print "<pre style=\"border: 1px solid #999; background-color: #f7f7f7; color: #000; overflow: auto; width: auto; text-align: left; padding: 1em;\">" .
		(
			(
				strlen(
					trim($label)
				)
			) ? htmlentities($label)."\n===================\n" : ""
		) .
		htmlentities(print_r($var, TRUE)) . "</pre>";
}

function safeOutput($str2clean = ""){
	/*
	Summary:
	  alias of outputStripHTML
	*/

	return outputStripHTML($str2clean);
}

function param ($param, $in_array = false, $is_array = false, $default_value = '', $array_glue = ARRAY_GLUE, $case_insensitive = true) {
	$param = "$param";
	if ($in_array == false) {
		$in_array = array();
		$variables_order = preg_split('//', trim(ini_get('variables_order')), -1, PREG_SPLIT_NO_EMPTY); //http://us.php.net/manual/en/ini.core.php#ini.variables-order
		if (!sizeof($variables_order)) $variables_order = preg_split('//', trim(ini_get('gpc_order')), -1, PREG_SPLIT_NO_EMPTY); //http://us.php.net/manual/en/ini.core.php#ini.gpc-order
		if (!sizeof($variables_order)) $variables_order = array(/*'E',*/'G','P','C'/*,'S'*/); //Environment and Server vars should not be used by default.
		foreach ($variables_order as $variable) {
			/**
			 * Limit to GPC variables
			 */
			switch (strtoupper($variable)) {
				case 'E';
					$in_array = array_merge($in_array, $_ENV);
					break;
				case 'G';
					$in_array = array_merge($in_array, $_GET);
					break;
				case 'P';
					$in_array = array_merge($in_array, $_POST);
					break;
				case 'C';
					$in_array = array_merge($in_array, $_COOKIE);
					break;
				case 'S';
					$in_array = array_merge($in_array, $_SERVER);
					break;
			}
		}
	}
	elseif (!is_array($in_array)) {
		$in_array = array($in_array);
	}

	if ($case_insensitive) {
		$temp_array = $in_array;
		foreach ($temp_array as $key => $val) {
			if (preg_match('/[a-z]/', $key)) {
				unset($in_array[$key]);
				$in_array[strtoupper($key)] = $val; }
		}
		$param = strtoupper($param);
		unset($temp_array);
	}

	$value = '';
	if (array_key_exists($param, $in_array)) {
		$value = $in_array[$param];
		if (!is_array($value)) $value = trim($value);
		else {
			array_walk($value, create_function('&$elem, $key','$elem = trim($elem);'));
		}
	}
	else {
		$value = $default_value;
	}

	if ($is_array && !is_array($value)) {
		$value = explode($array_glue, $value);
	}
	elseif (!$is_array && is_array($value)) {
		$value = implode($array_glue, $value);
	}

	return $value;
}

/* ADDED 2006-03-30 */
function array_clean ($array, $todelete = false, $caseSensitive = false) {
	//removes elements from an array by comparing the value of each key
	foreach($array as $key => $value) {
		if(is_array($value)) {
			$array[$key] = array_clean($array[$key], $todelete, $caseSensitive);
		}
		else {
			if($todelete) {
				if($caseSensitive) {
					if(strstr($key ,$todelete) !== false) {
						unset($array[$key]);
					}
				}
				else {
					if(stristr($key, $todelete) !== false) {
						unset($array[$key]);
					}
				}
			}
			elseif (empty($key)) {
				unset($array[$key]);
			} //END: if($todelete)
		} //END: if(is_array($value))
	} //END: foreach
	return $array;
}

function formatQuerystringParams($dataArray, $firstChar, $clean_array = false) {
	if ($clean_array) {
		$dataArray = array_clean($dataArray, $clean_array);
	}
	$return = '';
	$iCnt = sizeof($dataArray);
	if ($iCnt) $return = substr($firstChar, 0, 1);
	foreach ($dataArray as $name => $value) {
		$return .= rawurlencode($name).'='.rawurlencode($value);
		if ($iCnt > 1) $return .= '&';
		$iCnt--;
	}
	return $return;
}

function prepForEmail($text,$wrapAt=false,$indentWrap=false,$indentFirst=false,$makeHTMLsafe=false,$strict=false,$wrapWith="\n") {
	/**
	 * replace CR characters
	 */
	$text = preg_replace("/\r/","\n",$text); //replace single CR characters with new lines
	$text = preg_replace("/\n{3,}/","\n\n",$text); //replace any LF sequences greater than 3 with 2

	if ($strict) {
		$text = stripControlCharsSingleLine($text);
	} else {
		$text = stripControlCharsMultiLine($text);
	}

	if ($makeHTMLsafe == 1) {
		//encode any HTML entities in message text
		$text = htmlentities($text,ENT_NOQUOTES);
	} elseif ($makeHTMLsafe == 2) {
		//strip HTML tags and encode any remaining HTML entities
		$text = htmlentities(strip_tags($text),ENT_NOQUOTES);
	}

	$tab = (($indentWrap) ? (string) $indentWrap : '');
	$tabFirst = (($indentFirst) ? (string) $indentFirst : '');
	if (intval($wrapAt)) {
		if (strpos($text, "\n") !== false) {
			$text = split("\n",$text);
			foreach ($text as $key => $line) {
				$text[$key] = $tab.wordwrap($line,$wrapAt,"$wrapWith$tab",1);
			}
			$text = join("\n",$text);
		} else {
			$text = wordwrap($text,$wrapAt,"$wrapWith$tab",1);
		}
	}
	return $tabFirst.$text;
}

/**
 * Generates a redirect statement based on current state of output/headers
 *
 * @access private
 * @param mixed $targetURL Optional complete URL to redirect to. If not specified, returns false.
 * @param mixed $dataArray Optional array of name=>value parameters to pass along.
 * @param boolean $pauseBefore Optional flag. Useful for debugging - will force to redirect by manual form/POST.
 * @return null Result dependant on redirect method. May be a JavaScript redirect string if output has already started.
 *   Otherwise, PHP headers will be added directly. Processing will halt directly after in either case.
 */
function redirect($targetURL = false, $dataArray = false, $pauseBefore = false) {
	if (!strlen($targetURL)) return false;

	$search = '';
	if (strrpos($targetURL,'#') !== false) {
		list($targetURL,$search) = explode('#',$targetURL);
	}
	if (strlen($search)) $search = '#'.rawurlencode($search);

	if (strrpos($targetURL,'?') !== false) {
		list($targetURL,$extraParams) = explode('?',$targetURL);
		$extraParams = explode('&',$extraParams);
		foreach ($extraParam as $name => $value) {
			$dataArray[$name] = $value;
		}
	}
	if (is_array($dataArray)) $dataArray = array_merge($dataArray);

	if ($pauseBefore !== false) {
		redirectByForm($targetURL.$search,$dataArray,true,false);
	}
	else {
		$sep = '?';
        if ($dataArray !== false){
		foreach ($dataArray as $name => $value) {
			$targetURL .= $sep.rawurlencode($name).'='.rawurlencode($value);
			$sep = '&';
		}}
		if (!headers_sent()) {
			session_write_close();
			header('Location: '.$targetURL.$search);
			exit();
		}
		else {
			echo "<script type=\"text/javascript\" language=\"javascript\">window.location.replace('".addslashes(htmlentities($targetURL.$search))."');</script>";
			session_write_close();
			exit;
		}
    }
}

/**
 * Outputs a form to use in request redirection. May submit automatically if browser allows.
 *
 * @access private
 * @param mixed $targetURL Complete URL to redirect to.
 * @param mixed $dataArray Optional array of name=>value parameters to write as input fields.
 * @param boolean $redirectByPost Optional flag. Useful for debugging - will force to redirect by manual form/POST instead of form/GET.
 * @param boolean $autoSubmit Optional flag. Adds an onload javascript directive to submit form automatically.
 * @return null Outputs an HTML form set and terminates script execution.
 */
function redirectByForm($targetURL, $dataArray = false, $redirectByPost = true, $autoSubmit = true) {
	if (!strlen($targetURL)) return false;
	$method = (($redirectByPost === true) ? 'post' : 'get');

	$search = '';
	if (strrpos($targetURL,'#') !== false) {
		list($targetURL,$search) = explode('#',$targetURL);
	}
	if (strlen($search)) $search = '#'.rawurlencode($search);

	if (strrpos($targetURL,'?') !== false) {
		list($targetURL,$extraParams) = explode('?',$targetURL);
		$extraParams = explode('&',$extraParams);
		foreach ($extraParam as $name => $value) {
			$dataArray[$name] = $value;
		}
	}
	if (is_array($dataArray)) $dataArray = array_merge($dataArray);
	echo '<html><body'.(($autoSubmit == true) ? ' onload="document.forms[0].submit()"' : '').'><form method="'.$method.'"'.
		' action="'.htmlentities($targetURL.$search).'">';
	writeHiddenFormFields($dataArray);
	echo '<input type="submit" name="'.POSTBACK_PARAMETER_PREFIX.'submit" value="Continue" /></form></body></html>';
	session_write_close();
	exit;
}

function return_bytes($val) {
	$val = trim($val);
	$last = strtolower($val{strlen($val)-1});
	switch($last) {
		// The 'G' modifier is available since PHP 5.1.0
		case 'g':
			$val *= 1024;
		case 'm':
			$val *= 1024;
		case 'k':
			$val *= 1024;
	}

	return floatval($val);
}

function stripControlCharsMultiLine($text) {
	/**
	 * Remove control chars, including DEL and everything under ASCII 32
	 * as well as tab chars. Leave LF and CR chars alone.
	 */
	$invalidChars = "\001\002\003\004\005\006\007\010\013\014\016\017\020\021\022\023\024\025\026\027\030\031\032\033\034\035\036\037\177";
	$replacementChars = "\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0";
	$text = strtr($text, $invalidChars, $replacementChars);
	if (strpos($text, "\0") !== false) {
		// String contains NUL--all control chars have been replaced with NUL now.
		// Delete NUL, and we're done.
		$text = str_replace("\0", '', $text);
	}
	return $text;
}

function stripControlCharsSingleLine($text) {
	/**
	 * like stripControlCharsMultiLine(), but also removes LF and CR
	 */
	$invalidChars = "\001\002\003\004\005\006\007\010\011\012\013\014\015\016\017\020\021\022\023\024\025\026\027\030\031\032\033\034\035\036\037\177";
	$replacementChars = "\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0";
	$text = strtr($text, $invalidChars, $replacementChars);
	if (strpos($text, "\0") !== FALSE) {
		// String contains NUL--all control chars have been replaced with NUL now.
		// Delete NUL, and we're done.
		$text = str_replace("\0", '', $text);
	}
	return $text;
}

function w($str2write) {
	echo htmlentities($str2write);
}

function w_self($str2write = '') {
	echo htmlentities($_SERVER['PHP_SELF'] . $str2write);
}

/**
 * Outputs values from the dataArray as hidden form field elements.
 *
 * @access private
 * @param array $dataArray Array of name=>value pairs to output. Nested arrays are processed recursively.
 * @param mixed $clean_array Optional parameter used to trim off array elements that start with specified string. Ignored if false.
 * @param string $id_prefix Optional string to append to beginning of element names when used as element ID attribute
 * @return null Outputs hidden HTML <input> fields directly
 */
function writeHiddenFormFields($dataArray, $clean_array = false, $id_prefix = '') {
	if (!is_array($dataArray)) return false;
	if (!sizeof($dataArray)) return true;
	if ($clean_array) {
		$dataArray = array_clean($dataArray, $clean_array);
	}
	foreach ($dataArray as $name => $value) {
		// repeat any POST params verbatim (except for the login page's internal POST params)
		// If this page is included by another page as a result of password timeout,
		// we want to preserve the GET or POST in progress

		// POST param name doesn't begin with $loginParamPrefix? Include it as a hidden form item.
		if (is_array($value)) {
			foreach ($value as $name2 => $value2) {
				writeHiddenFormFields(array("{$name}[{$name2}]" => $value2), $clean_array, $id_prefix);
			}
		}
		else {
			echo '<input type="hidden" name="'.htmlentities($name).'" id="'.htmlentities($id_prefix.preg_replace('/[^0-9a-z\-_]/i','_',$name)).'" value="'.htmlentities($value).'" />'."\n";
		}
	}
}

function writeQuerystringParams($dataArray, $firstChar, $clean_array = false) {
	if (sizeof($dataArray)) {
		echo formatQuerystringParams($dataArray, $firstChar, $clean_array);
	}
}
?>
