<?php
class stringSwap {
  /*---------------------------------------------
  stringSwap class for PHP (1.0)
  Created: 2004 by Chris Bloom [ xangelusx@hotmail.com ]
  Last Updated: 2005 Chris Bloom [ xangelusx@hotmail.com ]
  http://www.csb7.com/
  
  stringSwap class for PHP
    Summary:
      Alternates two strings. Useful for swapping the color of rows in tabular data.
  
    Usage:
    	To create:
    	$SS = new stringSwap();        //Create a new stringSwap object
    	$SS->defaultString("#FFFFFF"); //Set your default string
    	$SS->swapString("#E7E7E7");    //Set your alternate string
    	$SS->reset();                  //Reset the strings so that the first call to swap returns the default string
    	
    	To use:
    	<tr bgcolor="<?php echo $SS->swap(); ?>">
    		<th>Foo</th>
    		<td>Bar</td>
    	</tr>
    	<tr bgcolor="<?php echo $SS->swap(); ?>">
    		<th>Ex</th>
    		<td>Lax</td>
    	</tr>
  
      Thats It!!
  
    Version History:  
      Version: 1.0
      Date: Circa 2004
      Author: Chris Bloom
      Version Notes:
        Original version for PHP
    
    To Do:
      - No improvments currently planned
                  
    Notes:
      Please feel free to email me with comments or suggestions:
        xangelusx@hotmail.com
      The code is provided free of charge for non-commercial use so long as you leave all comments intact.  
      Please get in touch for commercial licensing information.
  ---------------------------------------------*/
	
	var $_sDefault = "";
	var $_sSwap = "";
	var $_sCurrent = "";

	//Class Initialization
	function stringSwap() {
		$this->_sDefault = "#FFFFFF";
		$this->_sSwap = "#888888";
		$this->reset();
	}
	
	//Set or get the current string value
	function currentString() {
		if (func_num_args()) $this->_sCurrent = func_get_arg(0);
		else return $this->_sCurrent;
	}

	//Set or get the default string value
	function defaultString() {
		if (func_num_args()) $this->_sDefault = func_get_arg(0);
		else return $this->_sDefault;
	}

	//Set or get the alternate string value
	function swapString() {
		if (func_num_args()) $this->_sSwap = func_get_arg(0);
		else return $this->_sSwap;
	}

	//Swap the strings and return the result
	function swap() {
		if ($this->_sCurrent == $this->_sDefault) {
			$this->_sCurrent = $this->_sSwap;
		} else {
			$this->_sCurrent = $this->_sDefault;
		}

		return $this->_sCurrent;
	}

	//Reset the strings so that the next call to swap returns the default string
	function reset() {
		$this->_sCurrent = $this->_sSwap;
		return $this->_sDefault;
	}
}
?>