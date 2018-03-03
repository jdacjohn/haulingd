<?php
/*---------------------------------------------
addURLParam function for PHP (1.1)
Created: 4/26/2005 by Chris Bloom [ xangelusx@hotmail.com ]
Last Updated: 1/26/2006 by Chris Bloom [ xangelusx@hotmail.com ]
Original JavaScript version Created: 12/01/2001 by Chris Bloom [ xangelusx@hotmail.com ]
http://www.csb7.com/

addURLParam for PHP
	Summary:
		addURLParam is useful for controlling the data in the search portion of a given
		URL. It is especially helpful when more than one rule is being used in the querystring
		to control information or elements on the page. Note that the function does not 
		interpret or act upon the passed parameters, it only evaluates the given URL and adds or
		removes the parameter and it's specified value 

	Usage:
		To Create:
			Include the script using the include() or require() functions
		
		To use with the current page's URL:
			- Assume the current URL is as follows:
				http://www.my-server.com/my-records.php
			- Specify one new parameter
				<a href="<?php echo addURLParam('category',1); ? >">Show only records from category 1</a>
			- URL is now:
				http://www.my-server.com/my-records.php?category=1
			- Specify several parameters
				<a href="<?php echo addURLParam('category',2,'sort','product_name ASC'); ? >">Show only records from category 2 sorted by product_name</a>
			- URL is now:
				http://www.my-server.com/my-records.php?category=2&sort=product_name+ASC
			- Remove category parameter
				<a href="<?php echo addURLParam('category',NULL); ? >">Show all records sorted by product_name</a>
			- URL is now:
				http://www.my-server.com/my-records.php?sort=product_name+ASC
			- Specify the actual value of 'NULL'
				<a href="<?php echo addURLParam('category','NULL'); ? >">Show all records where the category is NULL</a>
			- URL is now:
				http://www.my-server.com/my-records.php?sort=product_name+ASC&category=NULL
			
		To use with a specified URL:
			- Assume the variable $URL is set to the following string:
				http://www.my-server.com/my-table.php?lang=en-iso-8859-1&server=1&db=intra-action-logs&goto=db_details_structure.php&table=groups
			- Specify several parameters
				<a href="<?php echo addURLParam($URL,'table','partners','server',2); ? >">Show partners table on server 2</a>
			- Link becomes:
				http://www.my-server.com/my-table.php?lang=en-iso-8859-1&server=2&db=intra-action-logs&goto=db_details_structure.php&table=partners

			- Assume the variable $URL is set to the following string:
				http://www.my-server.com/admin.php?tab=1
			- Add one parameter and remove another
				<a href="<?php echo addURLParam($URL,'tab',NULL,'path','/nursingpd/'); ? >">Show details of record # 29</a>
			- Link becomes:
				http://www.my-server.com/admin.php?path=%2Fnursingpd%2F

	Return Value:
		Returns the given URL with the specified name/value pairs added to, updated in, or removed from the querystring

	Version History:  
		Version: 1.1
		Date: 1/26/2006
		Author: Chris Bloom
		Version Notes: 
			Adding mock http_build_query function (in case we are on PHP < 5 
			Updating the function to take existing URL parameters from the _GET collection rather than the QUERY_STRING value of
				the _SERVER collection. This allows us to unset _GET elements from anywhere in our code so they aren't included 
				in calls to this function. Useful when you only want to include a passed query parameter in a small portion of 
				your code, but ignore it throughout the rest of the page without explicitly setting the value to NULL

		Version: 1.0
		Date: 4/26/2005
		Author: Chris Bloom
		Version Notes: 
			Port from JavScript fnAddRule script
				
	To Do:
		- No improvments currently planned
								
	Notes:
		Please feel free to email me with comments or suggestions:
			xangelusx@hotmail.com
		The code is provided free of charge for non-commercial use so long as you leave all comments intact.  
		Please get in touch for commercial licensing information.
---------------------------------------------*/
function addURLParam() //strRule, strValue[,strRule, strValue...]
{
	/*
	addURLParam([string $URL,] string $paramName1, string $paramValue1 [, string $paramName2, string $paramValue2 [, ...]]):
		This function accepts a variable number of arguments. You must specify at least one pair of paramName and paramValue
		arguments. You do not need to specify a URL parameter if you are affecting the current page's URL (obtained using
		$_SERVER['PHP_SELF']). However, if you do want to pass in a URL to use instead it must be the first parameter.
		
		URL: Optional String. String containing the URL to add, update, remove parameters from. If not specified then the current pages
			URL is assumed (obtained using $_SERVER['PHP_SELF'])

		paramName: Required String. A string containing the name of the parameter that you wish to affect in the querystring of the given URL.
			Must be coupled with a paramValue.
		
		paramValue: Required Mixed. The value that you want to set the parameter to in the querystring. If the paramName parameter already 
			exists in the given querystring then the current value will be replaced by paramValue. paramValue will be interpreted in the following
			way:
				If paramValue is of type:          It will return:
				Array                              A URL-encoded serialized representation
				NULL                               Nothing. The paramName parameter will be removed from the querystring (NULL was introduced in PHP Version 4)
				Boolean TRUE or FALSE              The value TRUE or FALSE, respectively
				Object                             A byte-stream (serialized) representation (In PHP 3, serialized objects will lose their class association)
				String, Integer, Float, Etc.       The URL-encoded value
				
		As many paramName/paramValue pairs can be passed as requred, but you must always pass them as a name/value pair. An odd number of arguments will
		cause the first parameter to be interpreted as the URL and the rest to be interpreted as paramName/paramValue pairs. An even number of arguments 
		will be interpreted as only paramName/paramValue pairs and will assume the current URL.
	*/
	
	//Set this to true to enable debugging messages
	$blnTesting = false;
	
	$args = func_get_args();
	if ($blnTesting) echo ('<br>' . (sizeof($args)) . ' arguments');

	//Get URL and QueryString values	
	$URL = (((sizeof($args) % 2) == 1) 
		? /* an odd number of args - get URL from first */ array_shift($args) 
		: $_SERVER['PHP_SELF'] . "?" . ((sizeof($_GET)) ? rawurldecode(http_build_query($_GET)) : ""));
	$QS = "";
	if (strpos($URL, "?") !== FALSE) {
		list($URL, $QS) = explode("?", $URL, 2);
	}
	if ($blnTesting) echo ('<br>URL = ' . $URL);
	if ($blnTesting) echo ('<br>QS = ' . $QS);

	//Discover the servers preferred parameter delimiters. If empty, assume "&"
	$Delims = ((ini_get("arg_separator.input") == "") ? "&" : ini_get("arg_separator.input"));
	if ($blnTesting) echo ('<br>Delims = ' . $Delims);
	if (strlen($Delims) > 1) {
		$Delims = preg_split('//', $Delims, -1, PREG_SPLIT_NO_EMPTY);
	} else {
		$Delims = array($Delims);
	}
	$DelimsPregQuoted = array();
	foreach ($Delims as $Delim) {
		$DelimsPregQuoted[] = preg_quote($Delim);
	}
	if ($blnTesting) echo ('<br>DelimsPregQuoted = <pre>' . print_r($DelimsPregQuoted, TRUE) . '</pre>');
	
	//Explode QS into name=value pairs
	if (strlen($QS) > 0) {
		$QS = preg_split('/'.implode("|", $DelimsPregQuoted).'/', $QS, -1);
		
		//Now explode QS into name/value arrays (split on "=")
		$QSTemp = array();
		foreach ($QS as $index => $QSParamPair) {
			@list($QSParamName, $QSParamValue) = explode("=", $QSParamPair, 2);
			$QSTemp[$QSParamName] = $QSParamValue;
		}
		$QS = $QSTemp;
	} else {
		$QS = array();
	}
	if ($blnTesting) echo ('<br>QS = <pre>' . print_r($QS, TRUE) . '</pre>');
	
	//Loop through the paramName/paramValue argument pairs
	for ($i=0; $i<sizeof($args); $i+=2) {
		$paramName = $args[$i];
		switch (gettype($args[$i+1])) {
			case "array":
			case "object":
				$paramValue = serialize($args[$i+1]);
				break;
			case "boolean":
				$paramValue = (($args[$i+1]) ? "TRUE" : "FALSE");
				break;
			case "NULL":
				$paramValue = NULL;
				break;
			default:
				$paramValue = (string) $args[$i+1];
				break;
		} //END: switch gettype($args[$i+1])
		if ($blnTesting) echo ('<br>paramValue #' . $i . ' type = ' . gettype($args[$i+1]));
		if ($blnTesting) echo ('<br>paramName/paramValue pair #' . $i . ' = ' . $paramName . '/' . $paramValue);
		
		if ($paramValue == NULL) {
			if (array_key_exists($paramName, $QS)) {
				if ($blnTesting) echo ('<br>NULL value specified - key is present - remove old key rule');
				unset($QS[$paramName]);
			} else {
				if ($blnTesting) echo ('<br>NULL value specified - key is not present - do nothing');
			} //END: if (array_key_exists($paramName, $QS))
		} else {
			if (array_key_exists($paramName, $QS)) {
				if ($blnTesting) echo ('<br>NULL value not specified - key is present - replace current key value');
			} else {
				if ($blnTesting) echo ('<br>NULL value not specified - key is not present - add new rule');
			} //END: if (array_key_exists($paramName, $QS))
			$QS[$paramName] = $paramValue;
		} //END: if ($paramValue == NULL)
	} //END: for ($i=0; $i<sizeof($args); $i+=2)
	if ($blnTesting) echo ('<br>QS = <pre>' . print_r($QS, TRUE) . '</pre>');
	
	$QSTemp = "";
	if (sizeof($QS)) {
		$Delim = $Delims[0];
		foreach ($QS as $QSParamName => $QSParamValue) {
			if (strlen($QSTemp) > 0) $QSTemp .= $Delim;
			$QSTemp .= rawurlencode($QSParamName) . "=" . rawurlencode($QSParamValue);
		}
		$QSTemp = "?" . $QSTemp;
	}
	$QS = $QSTemp;

	if ($blnTesting) echo ('<br>Returning ' . $URL . $QS);
	return $URL . $QS;
}

//create a mock http_build_query function if we are on PHP < 5
//from vlad_mustafin via http://www.php.net/manual/en/function.http-build-query.php#57480
if(!function_exists('http_build_query')) {
	function http_build_query( $formdata, $numeric_prefix = null, $key = null ) {
		$res = array();
		foreach ((array)$formdata as $k=>$v) {
			$tmp_key = rawurlencode(is_int($k) ? $numeric_prefix.$k : $k);
			if ($key) $tmp_key = $key.'['.$tmp_key.']';
			if ( is_array($v) || is_object($v) ) {
				$res[] = http_build_query($v, null /* or $numeric_prefix if you want to add numeric_prefix to all indexes in array*/, $tmp_key);
			} else {
				$res[] = $tmp_key."=".rawurlencode($v);
			}
			/*
			If you want, you can write this as one string:
			$res[] = ( ( is_array($v) || is_object($v) ) ? http_build_query($v, null, $tmp_key) : $tmp_key."=".urlencode($v) );
			*/
		}
		$Delims = ((ini_get("arg_separator.input") == "") ? "&" : ini_get("arg_separator.input"));
		if (strlen($Delims) > 1) {
			$Delims = preg_split('//', $Delims, -1, PREG_SPLIT_NO_EMPTY);
		} else {
			$Delims = array($Delims);
		}
		$separator = $Delims[0];
		return implode($separator, $res);
	}
}
?>