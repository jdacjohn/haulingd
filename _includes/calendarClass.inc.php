<?php
class Calendar {
  /*---------------------------------------------
  Calendar class for PHP (1.2.1)
  Created: 2005-02-23 by Chris Bloom [ xangelusx@hotmail.com ]
  Last Updated: 2005-05-01 Chris Bloom [ xangelusx@hotmail.com ]
  http://www.csb7.com/
  
  Calendar class for PHP
    Summary:
      Draws a calendar of a given month. Days can be set to display individual content.
  
    Usage:
      To create an empty calendar of the current month:
      $myCal = new Calendar(); //Create a new calendar object
      $myCal->Draw();          //That's it!
      
      To create a calendar of a specific month:
      $myCal = new Calendar();   //Create a new calendar object
      $myCal->currentMonth(6);   //Set the month
      $myCal->currentYear(2005); //Set the year
      $myCal->Draw();            //That's it!
      
			To set the content for a specific day of the month:
      $myCal = new Calendar();   //Create a new calendar object
      $myCal->currentMonth(2);   //Set the month
      $myCal->currentYear(2005); //Set the year
      $myCal->dateContent(14,"<strong>Today is %%month%% %%day%% - Happy Valentines' Day!!</strong>"); //Set the content
      $myCal->Draw();            //That's it!
  
      To set options at initialization time, pass an array of named-index/value pairs that map to public class functions and their arguments.
	  This is useful when taking advantage of the autoCollectDates feature:
	  $myCalOptions = array(
	    'submitMethod' => 'post',
	    'monthParamName' => 'M',
	    'yearParamName' => 'Y'
	    );
      $myCal = new Calendar($myCalOptions); //Create a new calendar object
			...
      $myCal->Draw();          //That's it!

      You can use the following variables in any of the built-in string property variables:
      %%month%%                 - Inserts the month formatted using the monthFormat property value
      %%year%%                  - Inserts the year formatted using the yearFormat property value
      %%day%%                   - Inserts the year formatted using the dayNumberFormat property value. If used outside of a date cell it uses the first day of the month
      %%daysInMonth%%           - Inserts the total number of days in the month
      %%daysLeftInMonth%%       - Inserts the number of days left in the month, including the current day. If used outside of a date cell it returns the number of days in the month
      %%daysInYear%%            - Inserts the total number of days in the year (365 or 366 for a leap year)
      %%daysSinceStartOfYear%%  - Inserts the number of days since the beginning of the year. If used outside of a date cell it counts from the first day of the month
      %%daysLeftInYear%%        - Inserts the number of days left in the year, including the current day. If used outside of a date cell it counts from the first day of the month

      Thats It!!
  
    Version History:  
      Version: 1.2.1
      Date: 2005-05-01
      Author: Chris Bloom
      Version Notes:
			  Fixed undefined offset error for _dateArray variable
				Added _monthParamName, _yearParamName & autoCollectDates variables and supporting get/set functions
				Support for setting variables at creation time by passing an array of named-index/value pairs to the constructor

      Version: 1.2
      Date: 2005-03-01
      Author: Chris Bloom
      Version Notes:
			  Added variable support.
				Added/Updated CSS classes and IDs.
				Added more public variables.

      Version: 1.0
      Date: 2005-02-23
      Author: Chris Bloom
      Version Notes:
        Original version for PHP
				Based loosely on the Calendar Tour Logic by Jason Johnson
    
    To Do:
      - Setup CSS file for different media types and include a default style
                  
    Notes:
		  PHP can only handle timestamps from -2147483648 to 2147483647 (1901-12-13 T15:45:52 to 2038-01-18 T22:14:07 UT).
			These are the minimum and maximum values for a 32-bit signed integer.
			
      Please feel free to email me with comments or suggestions:
        xangelusx@hotmail.com
      The code is provided free of charge for non-commercial use so long as you leave all comments intact.  
      Please get in touch for commercial licensing information.
  ---------------------------------------------*/

	var $_currentMonth = 0;
	var $_currentYear = 0;
	var $_monthTimestamp = 0;
	var $_dayTimestamp = 0;
	var $_monthNumDays = 0;
	var $_dateArray = array();
	var $_submitMethod = "get";
	var $_extraVars = array();
	
	var $_monthParamName = "month";
	var $_yearParamName = "year";
	var $_autoCollectDates = true;
	
	var $_showPreWeekColumn = false;
	var $_preWeekColumnHeader = "";
	var $_preWeekArray = array();

	var $_postWeekColumnHeader = "";
	var $_showPostWeekColumn = false;
	var $_postWeekArray = array();

	var $_SundayColumnHeader = "Sunday";
	var $_MondayColumnHeader = "Monday";
	var $_TuesdayColumnHeader = "Tuesday";
	var $_WednesdayColumnHeader = "Wednesday";
	var $_ThursdayColumnHeader = "Thursday";
	var $_FridayColumnHeader = "Friday";
	var $_SaturdayColumnHeader = "Saturday";
	
	var $_calendarTitleCellContent = "%%month%% %%year%%";
		
	var $_monthFormat = "F";
	var $_yearFormat = "Y";
	var $_dayNumberFormat = "j";
		
	//Class Initialization
	function calendar() {
		//Get any options to be set at initialization
		if (func_num_args()) {
			if (is_array(func_get_arg(0))) { //only accept an array
				foreach (func_get_arg(0) as $key=>$value) { //the array should be pairs of named-index/value pairs that map to public methods and their arguments
					if (method_exists($this, $key) && strpos("key","_") !== 0) { //if method exists and is not private
						switch ($key) {
							case 'dateContent':
							case 'preWeekContent':
							case 'postWeekContent':
								//These methods link to variables that are reset after initialization
								user_error("You cannot call the $key method at initialization", E_USER_NOTICE);
								break;
							default:
								$this->$key($value);
								break;
						}
					} else {
						user_error("The $key method does not exists within the Calendar class or is a private method", E_USER_NOTICE);
					}
				}
			}
			else {
				user_error("You can only pass an array to the Calendar constructor", E_USER_NOTICE);
			}
		}
		
		//Do we need to auto collect the dates?
		if ($this->_autoCollectDates) {
			//make sure we only set them if they weren't set at initialization
			if ($this->_currentMonth == 0) {
				switch ($this->_submitMethod) {
					case 'post': 
						if (isset($_POST[$this->_monthParamName])) $this->_currentMonth = $_POST[$this->_monthParamName];
						break;
					default: 
						if (isset($_GET[$this->_monthParamName])) $this->_currentMonth = $_GET[$this->_monthParamName];
						break;
				}
			}
			if ($this->_currentYear == 0) {
				switch ($this->_submitMethod) {
					case 'post': 
						if (isset($_POST[$this->_yearParamName])) $this->_currentYear = $_POST[$this->_yearParamName];
						break;
					default: 
						if (isset($_GET[$this->_yearParamName])) $this->_currentYear = $_GET[$this->_yearParamName];
						break;
				}
			}
		}
		
		//Do one final check to see if either the month or year variable is empty (autoCollectDates was false or there was a problem collecting the value)
		if ($this->_currentMonth == 0) $this->_currentMonth = date('n');
		if ($this->_currentYear == 0) $this->_currentYear = date('Y');
		$this->reset();
	}

	//Reset the date array for the current month and year
	function reset() {
		$this->_monthTimestamp = $this->_dayTimestamp = mktime(0,0,0,$this->_currentMonth,1,$this->_currentYear);
		$this->_currentMonthNumDays = intval(date('t',$this->_monthTimestamp));
		$this->_dateArray = array_fill(1, 31, "");
		$this->_preWeekArray = array();
		$this->_postWeekArray = array();
	}
	
	//Set or get the current month
	function currentMonth() {
		if (func_num_args()) {
			if (func_get_arg(0) >= 1 && func_get_arg(0) <= 12) {
				$this->_currentMonth = intval(func_get_arg(0));
				$this->_monthTimestamp = $this->_dayTimestamp = mktime(0,0,0,$this->_currentMonth,1,$this->_currentYear);
				$this->_currentMonthNumDays = intval(date('t',$this->_monthTimestamp));
			}
			else {
				user_error("You can only set the currentMonth property of the Calendar class to a value between the 1 and 12", E_USER_NOTICE);
			}
		}
		else return $this->_currentMonth;
	}

	//Set or get the current year
	function currentYear() {
		if (func_num_args()) {
			if (func_get_arg(0) >= 1901  && func_get_arg(0) <= 2037) { //See note section above
				$this->_currentYear = func_get_arg(0);
				$this->_monthTimestamp = $this->_dayTimestamp = mktime(0,0,0,$this->_currentMonth,1,$this->_currentYear);
				$this->_currentMonthNumDays = intval(date('t',$this->_monthTimestamp));
			}
			else {
				user_error("You can only set the currentYear property of the Calendar class to a value between 1901 and 2037", E_USER_NOTICE);
			}
		}
		else return $this->_currentYear;
	}
	
	//Set or get the submit method (either "get" or "post")
	function submitMethod() {
		if (func_num_args()) {
			switch (strtolower(trim(func_get_arg(0)))) {
				case 'post':
					$this->_submitMethod = "post";
					break;
				case 'get':
					$this->_submitMethod = "get";
					break;
				default:
					user_error("The submitMethod property of the Calendar class must be \"get\" or \"post\"", E_USER_NOTICE);
					break;
			}
		}
		else return $this->_submitMethod;
	}

	//Set or get the array of other variables to append as querystring arguments or hidden form fields. Set as array('name'=>'value'[, ...]) pairs
	function extraVars() {
		if (func_num_args()) {
			if (is_array(func_get_arg(0))) {
				$this->_extraVars = func_get_arg(0);
			}
			else {
				$this->_extraVars = array(func_get_arg(0)=>'');
			}			
		}
		else return $this->_extraVars;
	}

	//Set or get the $_monthParamName variable
	function monthParamName() {
		if (func_num_args()) {
			$this->_monthParamName = trim(func_get_arg(0));
		}
		else return $this->_monthParamName;
	}

	//Set or get the $_yearParamName variable
	function yearParamName() {
		if (func_num_args()) {
			$this->_yearParamName = trim(func_get_arg(0));
		}
		else return $this->_yearParamName;
	}

	//Set or get the $_autoCollectDates variable
	function autoCollectDates() {
		if (func_num_args()) {
			switch ((bool) func_get_arg(0)) {
				case true:
					$this->_autoCollectDates = true;
					break;
				default:
					$this->_autoCollectDates = false;
					break;
			}
		}
		else return (($this->_autoCollectDates) ? 1 : 0);
	}

	//Set or get the $_showPreWeekColumn variable
	function showPreWeekColumn() {
		if (func_num_args()) {
			switch ((bool) func_get_arg(0)) {
				case true:
					$this->_showPreWeekColumn = true;
					break;
				default:
					$this->_showPreWeekColumn = false;
					break;
			}
		}
		else return (($this->_showPreWeekColumn) ? 1 : 0);
	}

	//Set or get the $_preWeekColumnHeader variable
	function preWeekColumnHeader() {
		if (func_num_args()) {
			$this->_preWeekColumnHeader = trim(func_get_arg(0));
		}
		else return $this->_preWeekColumnHeader;
	}

	//Set or get the $_showPostWeekColumn variable
	function showPostWeekColumn() {
		if (func_num_args()) {
			switch ((bool) func_get_arg(0)) {
				case true:
					$this->_showPostWeekColumn = true;
					break;
				default:
					$this->_showPostWeekColumn = false;
					break;
			}
		}
		else return (($this->_showPostWeekColumn) ? 1 : 0);
	}

	//Set or get the $_postWeekColumnHeader variable
	function postWeekColumnHeader() {
		if (func_num_args()) {
			$this->_postWeekColumnHeader = trim(func_get_arg(0));
		}
		else return $this->_postWeekColumnHeader;
	}

	//Set or get the $_SundayColumnHeader variable
	function SundayColumnHeader() {
		if (func_num_args()) {
			$this->_SundayColumnHeader = trim(func_get_arg(0));
		}
		else return $this->_SundayColumnHeader;
	}
	
	//Set or get the $_MondayColumnHeader variable
	function MondayColumnHeader() {
		if (func_num_args()) {
			$this->_MondayColumnHeader = trim(func_get_arg(0));
		}
		else return $this->_MondayColumnHeader;
	}
	
	//Set or get the $_TuesdayColumnHeader variable
	function TuesdayColumnHeader() {
		if (func_num_args()) {
			$this->_TuesdayColumnHeader = trim(func_get_arg(0));
		}
		else return $this->_TuesdayColumnHeader;
	}
	
	//Set or get the $_WednesdayColumnHeader variable
	function WednesdayColumnHeader() {
		if (func_num_args()) {
			$this->_WednesdayColumnHeader = trim(func_get_arg(0));
		}
		else return $this->_WednesdayColumnHeader;
	}
	
	//Set or get the $_ThursdayColumnHeader variable
	function ThursdayColumnHeader() {
		if (func_num_args()) {
			$this->_ThursdayColumnHeader = trim(func_get_arg(0));
		}
		else return $this->_ThursdayColumnHeader;
	}
	
	//Set or get the $_FridayColumnHeader variable
	function FridayColumnHeader() {
		if (func_num_args()) {
			$this->_FridayColumnHeader = trim(func_get_arg(0));
		}
		else return $this->_FridayColumnHeader;
	}
	
	//Set or get the $_SaturdayColumnHeader variable
	function SaturdayColumnHeader() {
		if (func_num_args()) {
			$this->_SaturdayColumnHeader = trim(func_get_arg(0));
		}
		else return $this->_SaturdayColumnHeader;
	}

	//Set or get the $_calendarTitleCellContent variable
	function calendarTitleCellContent() {
		if (func_num_args()) {
			$this->_calendarTitleCellContent = trim(func_get_arg(0));
		}
		else return $this->_fillContent($this->_calendarTitleCellContent);
	}

	//Set or get the $_monthFormat variable
	function monthFormat() {
		if (func_num_args()) {
			$this->_monthFormat = trim(func_get_arg(0));
		}
		else return $this->_monthFormat;
	}
	
	//Set or get the $_yearFormat variable
	function yearFormat() {
		if (func_num_args()) {
			$this->_yearFormat = trim(func_get_arg(0));
		}
		else return $this->_yearFormat;
	}
	
	//Set or get the $_dayNumberFormat variable
	function dayNumberFormat() {
		if (func_num_args()) {
			$this->_dayNumberFormat = trim(func_get_arg(0));
		}
		else return $this->_dayNumberFormat;
	}

	//get the number of days in the current month
	function getCurrentMonthNumDays() {
		return $this->_currentMonthNumDays;
	}

	//Set or get the content for a specific date
	function dateContent() {
		//printvar(func_get_arg());
		if (func_num_args() >= 2) {
			$day = intval(func_get_arg(0));
			$content = trim(func_get_arg(1));
			
			if ($day >= 1 && $day <= 31) {
				$this->_dateArray[$day] = $content;
			}
			else {
				return false;
			}
		}
		elseif (func_num_args() == 1) {
			$day = intval(func_get_arg(0));
			
			if (array_key_exists($day,$this->_dateArray)) {
				return $this->_fillContent($this->_dateArray[$day]);
			}
			else {
				return "";
			}
		} 
		else {
			return $this->_dateArray;
			//user_error("You must pass at least one argument to the dateContent function of the Calendar class", E_NOTICE);
		}
	}
	
	//Set or get the content for a specific pre-week cell (the cell in a week just before the Sunday column)
	function preWeekContent() {
		if (func_num_args() >= 2) {
			$week = intval(func_get_arg(0));
			$content = trim(func_get_arg(1));
			
			if ($week >= 1 && $week <= 6) {
				$this->_preWeekArray[$week] = $content;
			}
			else {
				return false;
			}
		}
		elseif (func_num_args() == 1) {
			$day = intval(func_get_arg(0));
			
			if (array_key_exists($week,$this->_preWeekArray)) {
				return $this->_fillContent($this->_preWeekArray[$week]);
			}
			else {
				return "";
			}
		} 
		else {
			return $this->_preWeekArray;
			//user_error("You must pass at least one argument to the preWeekContent function of the Calendar class", E_NOTICE);
		}
	}
	
	//Set or get the content for a specific post-week cell (the cell in a week just after the Saturday column)
	function postWeekContent() {
		if (func_num_args() >= 2) {
			$week = intval(func_get_arg(0));
			$content = trim(func_get_arg(1));
			
			if ($week >= 1 && $week <= 6) {
				$this->_postWeekArray[$week] = $content;
			}
			else {
				return false;
			}
		}
		elseif (func_num_args() == 1) {
			$day = intval(func_get_arg(0));
			
			if (array_key_exists($week,$this->_postWeekArray)) {
				return $this->_fillContent($this->_postWeekArray[$week]);
			}
			else {
				return "";
			}
		} 
		else {
			return $this->_postWeekArray;
			//user_error("You must pass at least one argument to the preWeekContent function of the Calendar class", E_NOTICE);
		}
	}

	//substitutes %%varname%% style strings with calendar variables
	function _fillContent($stringToParse="") {
		$arrayFind = array (
			"%%month%%",
			"%%year%%",
			"%%day%%",
			"%%daysInMonth%%",
			"%%daysLeftInMonth%%",
			"%%daysInYear%%",
			"%%daysSinceStartOfYearYear%%",
			"%%daysLeftInYear%%"
		);
		$arrayReplace = array (
			date($this->_monthFormat,$this->_monthTimestamp),
			date($this->_yearFormat,$this->_monthTimestamp),
			date($this->_dayNumberFormat,$this->_dayTimestamp),
			date('t',$this->_monthTimestamp),
			date('t',$this->_monthTimestamp) - date('j',$this->_dayTimestamp) + 1,
			((date('L',$this->_monthTimestamp) == 1) ? 366 : 365),
			((date('L',$this->_monthTimestamp) == 1) ? 366 : 365) - date('z',$this->_dayTimestamp),
			date('z',$this->_monthTimestamp) - date('j',$this->_dayTimestamp)
		);
		return str_replace($arrayFind,$arrayReplace,(string) $stringToParse);
	}


	//Get the timestamp for the next month from the current month and year
	function _nextMonthTimestamp() {
		return mktime(0,0,0,$this->_currentMonth+1,1,$this->_currentYear);
	}

	//Get the timestamp for the previous month from the current month and year
	function _previousMonthTimestamp() {
		return mktime(0,0,0,$this->_currentMonth-1,1,$this->_currentYear);
	}

	//Draw the calendar using the current month and year, filling the dates with the contents from _dateArray
	function draw() {
		$currentMonthInfo = getdate($this->_monthTimestamp);
		$nextMonthInfo = getdate($this->_nextMonthTimestamp());
		$prevMonthInfo = getdate($this->_previousMonthTimestamp());
		
		$prevLinkTop = "";
		$nextLinkTop = "";
		$prevLinkBottom = "";
		$nextLinkBottom = "";
		$extraVars = "";
		$varBase = "";
		if ($this->_submitMethod == "post") {
			if (sizeof($this->_extraVars) > 0) {
				foreach ($this->_extraVars as $extraVar=>$extraVal) {
					$extraVars .= "<input type=\"hidden\" name=\"".htmlentities($extraVar)."\" value=\"".htmlentities($extraVal)."\" />\n";
				}
			}
			$varBase = "<a href=\"javascript: document.forms['%s'].submit()\"><form name=\"%s\" action=\"".$_SERVER['PHP_SELF']."\" method=\"post\" style=\"display: inline; margin: 0; padding: 0\"><input type=\"hidden\" name=\"".$this->_monthParamName."\" value=\"%s\" /><input type=\"hidden\" name=\"".$this->_yearParamName."\" value=\"%s\" />".$extraVars."</form>%s</a>";
			$prevLinkTop = sprintf($varBase, "prevLinkTop", "prevLinkTop", $prevMonthInfo['mon'], $prevMonthInfo['year'], "<span class=\"link_arrows\">&lt;&lt;</span> <span class=\"link_month\">".$prevMonthInfo['month']."</span> <span class=\"link_year\">".$prevMonthInfo['year']."</span>");
			$nextLinkTop = sprintf($varBase, "nextLinkTop", "nextLinkTop", $nextMonthInfo['mon'], $nextMonthInfo['year'], "<span class=\"link_month\">".$nextMonthInfo['month']."</span> <span class=\"link_year\">".$nextMonthInfo['year']."</span> <span class=\"link_arrows\">&gt;&gt;</span>");
			$prevLinkBottom = sprintf($varBase, "prevLinkBottom", "prevLinkBottom", $prevMonthInfo['mon'], $prevMonthInfo['year'], "<span class=\"link_arrows\">&lt;&lt;</span> <span class=\"link_month\">".$prevMonthInfo['month']."</span> <span class=\"link_year\">".$prevMonthInfo['year']."</span>");
			$nextLinkBottom = sprintf($varBase, "nextLinkBottom", "nextLinkBottom", $nextMonthInfo['mon'], $nextMonthInfo['year'], "<span class=\"link_month\">".$nextMonthInfo['month']."</span> <span class=\"link_year\">".$nextMonthInfo['year']."</span> <span class=\"link_arrows\">&gt;&gt;</span>");
		}
		else {
			if (sizeof($this->_extraVars) > 0) {
				foreach ($this->_extraVars as $extraVar=>$extraVal) {
					$extraVars .= "&amp;".urlencode($extraVar)."=".urlencode($extraVal);
				}
			}
			$varBase = "<a href=\"".$_SERVER['PHP_SELF']."?".$this->_monthParamName."=%s&amp;".$this->_yearParamName."=%s".$extraVars."\">%s</a>";
			$prevLinkTop = $prevLinkBottom = sprintf($varBase, $prevMonthInfo['mon'], $prevMonthInfo['year'], "<span class=\"link_arrows\">&lt;&lt;</span> <span class=\"link_month\">".$prevMonthInfo['month']."</span> <span class=\"link_year\">".$prevMonthInfo['year']."</span>");
			$nextLinkTop = $nextLinkBottom = sprintf($varBase, $nextMonthInfo['mon'], $nextMonthInfo['year'], "<span class=\"link_month\">".$nextMonthInfo['month']."</span> <span class=\"link_year\">".$nextMonthInfo['year']."</span> <span class=\"link_arrows\">&gt;&gt;</span>");
		}
		
		print '<div class="calendar_container">
	<table class="calendar">
		<tr class="calendar_title_row_top">
			<td class="calendar_cell calendar_previous_link" colspan="'.(($this->_showPreWeekColumn) ? '3' : '2').'">'.$prevLinkTop.'</td>
			<td class="calendar_cell calendar_title" colspan="3"><span>'.$this->calendarTitleCellContent().'</span></td>
			<td class="calendar_cell calendar_next_link" colspan="'.(($this->_showPostWeekColumn) ? '3' : '2').'">'.$nextLinkTop.'</td>
		</tr>
		<tr class="calendar_header_row_top">'."\n";
		if ($this->_showPreWeekColumn) print '			<th class="calendar_cell calendar_column_0" width="0"><span>'.$this->_fillContent($this->preWeekColumnHeader()).'</span></th>'."\n";
		print '			<th class="calendar_cell calendar_column_1" width="14%"><span>'.$this->_fillContent($this->SundayColumnHeader()).'</span></th>
			<th class="calendar_cell calendar_column_2" width="14%"><span>'.$this->_fillContent($this->MondayColumnHeader()).'</span></th>
			<th class="calendar_cell calendar_column_3" width="14%"><span>'.$this->_fillContent($this->TuesdayColumnHeader()).'</span></th>
			<th class="calendar_cell calendar_column_4" width="14%"><span>'.$this->_fillContent($this->WednesdayColumnHeader()).'</span></th>
			<th class="calendar_cell calendar_column_5" width="14%"><span>'.$this->_fillContent($this->ThursdayColumnHeader()).'</span></th>
			<th class="calendar_cell calendar_column_6" width="14%"><span>'.$this->_fillContent($this->FridayColumnHeader()).'</span></th>
			<th class="calendar_cell calendar_column_7" width="14%"><span>'.$this->_fillContent($this->SaturdayColumnHeader()).'</span></th>'."\n";
		if ($this->_showPostWeekColumn) print '			<th class="calendar_cell calendar_column_8" width="0"><span>'.$this->_fillContent($this->postWeekColumnHeader()).'</span></th>'."\n";
		print '		</tr>'."\n";
			
		$iDaysPerWeek = 7;
		$iCurrDay = 0;
		$iCurrWeek = 1;
		$iCounter = 0;

		//Write any initial blank days
		if ($currentMonthInfo['wday'] > 0) {
			print '		<tr class="calendar_week_row" id="calendar_week_'.$iCurrWeek.'">'."\n";
			if ($this->_showPreWeekColumn) print '			<td class="calendar_cell calendar_column_0 calendar_day calendar_day_0" id="calendar_week_'.$iCurrWeek.'_column_0"><div class="calendar_day_date"><span>&nbsp;</span></div><div class="calendar_day_content"><span>'.((array_key_exists($iCurrWeek,$this->_preWeekArray)) ? $this->_fillContent($this->_preWeekArray[($iCurrWeek)]) : "").'</span></div></td>'."\n";
			while ($iCounter < $currentMonthInfo['wday'] && $iCounter < $iDaysPerWeek) {
				print '			<td class="calendar_cell calendar_column_'.(($iCounter % $iDaysPerWeek) + 1).' calendar_day calendar_day_0">
				<div class="calendar_day_container">
					<div class="calendar_day_date"><span>&nbsp;</span></div>
					<div class="calendar_day_content"><span>&nbsp;</span></div>
				</div>
			</td>'."\n";
				$iCounter++;
			}
			if ($iCounter % $iDaysPerWeek == $iDaysPerWeek) {
				if ($this->_showPostWeekColumn) print '			<td class="calendar_cell calendar_column_8 calendar_day calendar_day_0" id="calendar_week_'.$iCurrWeek.'_column_8"><div class="calendar_day_date"><span>&nbsp;</span></div><div class="calendar_day_content"><span>'.((array_key_exists($iCurrWeek,$this->_postWeekArray)) ? $this->_fillContent($this->_postWeekArray[($iCurrWeek)]) : "").'</span></div></td>'."\n";
				print '		</tr>'."\n";
				$iCurrWeek++;
			}
		}
		
		
		//Pick up at the first day and continue through end of month
		while ($iCurrDay < $this->_currentMonthNumDays) {
			$this->_dayTimestamp = mktime(0,0,0,$this->_currentMonth,($iCurrDay + 1),$this->_currentYear);
			
			if ($iCounter % $iDaysPerWeek == 0) {
				print '		<tr class="calendar_week_row" id="calendar_week_'.$iCurrWeek.'">'."\n";
				if ($this->_showPreWeekColumn) print '			<td class="calendar_cell calendar_column_0 calendar_day calendar_day_0" id="calendar_week_'.$iCurrWeek.'_column_0"><div class="calendar_day_date"><span>&nbsp;</span></div><div class="calendar_day_content"><span>'.((array_key_exists($iCurrWeek,$this->_preWeekArray)) ? $this->_fillContent($this->_preWeekArray[($iCurrWeek)]) : "").'</span></div></td>'."\n";
			}
			
			print '			<td class="calendar_cell calendar_column_'.(($iCounter % $iDaysPerWeek) + 1).' calendar_day '.((trim($this->_dateArray[($iCurrDay + 1)]) == "") ? 'calendar_day_no_content' : 'calendar_day_with_content').((date('Ymd') == date('Ymd',mktime(0,0,0,$this->_currentMonth,$iCurrDay + 1,$this->_currentYear))) ? ' calendar_day_today' : '').'" id="calendar_date_'.($iCurrDay + 1).'">
				<div class="calendar_day_container">
					<div class="calendar_day_date"><span>'.($iCurrDay + 1).'</span></div>
					<div class="calendar_day_content"><span>'.((array_key_exists($iCurrDay + 1,$this->_dateArray)) ? $this->_fillContent($this->_dateArray[($iCurrDay + 1)]) : "").'</span></div>
				</div>
			</td>'."\n";
			
			if ($iCounter % $iDaysPerWeek == $iDaysPerWeek - 1) {
				if ($this->_showPostWeekColumn) print '			<td class="calendar_cell calendar_column_8 calendar_day calendar_day_0" id="calendar_week_'.$iCurrWeek.'_column_8"><div class="calendar_day_date"><span>&nbsp;</span></div><div class="calendar_day_content"><span>'.((array_key_exists($iCurrWeek,$this->_postWeekArray)) ? $this->_fillContent($this->_postWeekArray[($iCurrWeek)]) : "").'</span></div></td>'."\n";
				print '		</tr>'."\n";
				$iCurrWeek++;
			}
			
			$iCurrDay++;
			$iCounter++;
		}
		
		//Write any remaining TD's
		if (($iCounter % $iDaysPerWeek) > 0 && ($iCounter % $iDaysPerWeek) <= ($iDaysPerWeek - 1)) {
			while (($iCounter % $iDaysPerWeek) !== 0) {
				print '			<td class="calendar_cell calendar_column_'.(($iCounter % $iDaysPerWeek) + 1).' calendar_day calendar_day_0">
				<div class="calendar_day_container">
					<div class="calendar_day_date"><span>&nbsp;</span></div>
					<div class="calendar_day_content"><span>&nbsp;</span></div>
				</div>
			</td>'."\n";
				$iCounter++;
			}
			if ($this->_showPostWeekColumn) print '			<td class="calendar_cell calendar_column_8 calendar_day calendar_day_0" id="calendar_week_'.$iCurrWeek.'_column_8"><div class="calendar_day_date"><span>&nbsp;</span></div><div class="calendar_day_content"><span>'.((array_key_exists($iCurrWeek,$this->_postWeekArray)) ? $this->_fillContent($this->_postWeekArray[($iCurrWeek)]) : "").'</span></div></td>'."\n";
			print '		</tr>'."\n";
		}
		
		//Reset dayTimestamp to beginning of month
		$this->_dayTimestamp = $this->_monthTimestamp;
		
		//Finish writing table elements
		print '		<tr class="calendar_header_row_bottom">'."\n";
		if ($this->_showPreWeekColumn) print '			<th class="calendar_cell calendar_column_0" width="0"><span>'.$this->_fillContent($this->preWeekColumnHeader()).'</span></th>'."\n";
		print '			<th class="calendar_cell calendar_column_1" width="14%"><span>'.$this->_fillContent($this->SundayColumnHeader()).'</span></th>
			<th class="calendar_cell calendar_column_2" width="14%"><span>'.$this->_fillContent($this->MondayColumnHeader()).'</span></th>
			<th class="calendar_cell calendar_column_3" width="14%"><span>'.$this->_fillContent($this->TuesdayColumnHeader()).'</span></th>
			<th class="calendar_cell calendar_column_4" width="14%"><span>'.$this->_fillContent($this->WednesdayColumnHeader()).'</span></th>
			<th class="calendar_cell calendar_column_5" width="14%"><span>'.$this->_fillContent($this->ThursdayColumnHeader()).'</span></th>
			<th class="calendar_cell calendar_column_6" width="14%"><span>'.$this->_fillContent($this->FridayColumnHeader()).'</span></th>
			<th class="calendar_cell calendar_column_7" width="14%"><span>'.$this->_fillContent($this->SaturdayColumnHeader()).'</span></th>'."\n";
		if ($this->_showPostWeekColumn) print '			<th class="calendar_cell calendar_column_8" width="0"><span>'.$this->_fillContent($this->postWeekColumnHeader()).'</span></th>'."\n";
		print '		</tr>
		<tr class="calendar_title_row_bottom">
			<td class="calendar_cell calendar_previous_link" colspan="'.(($this->_showPreWeekColumn) ? '3' : '2').'">'.$prevLinkBottom.'</td>
			<td class="calendar_cell calendar_title" colspan="3"><span>'.$this->calendarTitleCellContent().'</span></td>
			<td class="calendar_cell calendar_next_link" colspan="'.(($this->_showPostWeekColumn) ? '3' : '2').'">'.$nextLinkBottom.'</td>
		</tr>
	</table>
</div>'."\n";
	}
}
?>