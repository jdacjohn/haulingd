/*---------------------------------------------
common functions file for JavaScript (1.0)
Created: 2005 by Chris Bloom [ xangelusx@hotmail.com ]
Last Updated: 2005 Chris Bloom [ xangelusx@hotmail.com ]
http://www.csb7.com/

common functions file for JavaScript
	Summary:
		Provides commonly used custom functions for use in JavaScript projects.

	Usage:
		Include the file using a SCRIPT tag.

	Version History:  
		Version: 1.0
		Date: 2005-03-29
		Author: Chris Bloom
		Version Notes:
			Original version for JavaScript
	
	To Do:
		- No improvments currently planned
								
	Notes:
		Please feel free to email me with comments or suggestions:
			xangelusx@hotmail.com
		The code is provided free of charge for non-commercial use so long as you leave all comments intact.  
		Please get in touch for commercial licensing information.
---------------------------------------------*/

String.prototype.trim = new Function(
  /*
	Summary:
		Adds Trim() functionality to string objects

	Usage:
		string.Trim()
			
	Returns:
		string with white space stripped from left and right
	*/

	"return this.replace(/^\\s+|\\s+$/g,'')"
);

String.prototype.padLeft = function(chr, maxlen) {
  var result=this;
  while(result.length<maxlen) {
    result=chr+result;
  }
  return result;
}

String.prototype.padRight = function(chr, maxlen) {
  var result=this;
  while(result.length<maxlen) {
    result+=chr;
  }
  return result;
}

function checkInArray(sVal, arr) {
  /*
	Summary:
		looks for sVal in arr

	Usage:
		checkInArray(string sVal, array arr)
			sVal: the string to search for
			
			arr: the array to look through
			
	Returns:
		TRUE or FALSE
	*/

	if (arr.length > 0) {
		var strJoin = "||" + arr.join("||") + "||";
		if (sVal.Trim().length > 0 && strJoin.indexOf("||" + sVal.Trim().toUpperCase() + "||") > -1) {
			return true;
		} else {
			return false;
		}
	}
	return false;
}

function setFocus(elID, blnHighlight) {
  /*
	Summary:
		Sets the focus on the given element

	Usage:
		setFocus(string elID)
			elID: the ID of the element to set the focus on
			
	Returns:
		TRUE or FALSE
	*/
	if (!blnHighlight) blnHighlight = false; //set the default on the optional argument

	var el = document.getElementById(elID);
	if (el) {
		if (blnHighlight) Highlight(elID);
		if (el.select) {
			el.select();
		}
		el.focus();
		return true;
	}
	else {
		return false;
	}
}

function openWindow(sURL, sName, sFeatures) {
  /*
	Summary:
		Creates a new pop-up window

	Usage:
		openWindow(string sURL, string sName, string sFeatures)
			sURL: the URL to open the window with
			
			sName: the name of the window object
			
			sFeatures: a list of features to open the window with
			
	Returns:
		A window object on sucess
	*/

	return window.open(sURL, sName, sFeatures);
}

function goToURL(sWinName, sURL) { 
  /*
	Summary:
		Navigates the sWinName window to the given URL

	Usage:
		goToURL(string sWinName, string sURL)
			sWinName: the name of the window object
			
			sURL: the URL to open the window with
			
	Returns:
		Nothing
	*/

   eval(sWinName+".location='"+sURL+"'");
}