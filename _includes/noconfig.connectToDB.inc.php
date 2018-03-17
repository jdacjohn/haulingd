<?php
require_once(WEB_ROOT.PROJECT_DIR.'/adodb_lite/adodb.inc.php');

function &connectToDB() {
	global $dbwrite;
	$db = &_connectToDBURL($dbwrite); //dbwrite defined in config file

    if (!$db) {
        $err = '<p>Sorry, the database is currently unavailable. Please try again later.</p>';
		if($GLOBALS['gDebug']) {
			if (is_object($db)) $err .= '<p>'.htmlentities($db->ErrorMsg()).'</p>';
			else $err .= '<p>_connectToDBURL returned false</p>';
		}
		die($err);
    }
    $db->fieldNameBeginQuote = '`';
    $db->fieldNameEndQuote = '`';
    return $db;
}

/*may not be used in production depending on how user accounts can be created
function &connectToDBReadOnly() {
	global $dbread;
    $db = & _connectToDBURL($dbread); //dbread defined in config file

    if (!$db) {
        $err = '<p>Sorry, the database is currently not available. Please try again later.</p>';
		if($GLOBALS['gDebug']) $err .= '<p>'.htmlentities($db->ErrorMsg()).'</p>';

		die($err);
    }
    $db->fieldNameBeginQuote = '`';
    $db->fieldNameEndQuote = '`';
    return $db;
}

function &connectToDBAsAdmin() {
	global $dbadmin;
    $db = & _connectToDBURL($dbadmin); //dbadmin defined in config file

    if (!$db) {
        $err = '<p>Sorry, the database is currently not available. Please try again later.</p>';
		if($GLOBALS['gDebug']) $err .= '<p>'.htmlentities($db->ErrorMsg()).'</p>';

		die($err);
    }
    $db->fieldNameBeginQuote = '`';
    $db->fieldNameEndQuote = '`';
    return $db;
}
*/

function &_connectToDBURL($urlList) {
	/**
	 * Note that the URL connection scheme treats the following characters as special:
	 *   @:/ (and possibly ? and & as well)
	 * As such, these characters cannot appear in user names, passwords or table names
	 */

	if (!defined('APP_LOADED')) return false;
    $urlList = is_array($urlList) ? $urlList : array($urlList);
    foreach ($urlList as $url) {
		if ($GLOBALS['gDebug']) {
			$url .= ((strpos($url, '?') === false) ? '?' : '&').'debug=1';
			printvar(preg_replace('|^([^:]+)://([^:]+):[^@]+@|i','$1://$2:********@',$url),'Attempting to connect to:');
		}
        //echo "\n" . $url . "\n";
        $db = &ADONewConnection($url);
        //echo "Return Value from ADONewConenction():  " . $db . "\n";
		if (is_object($db)) return $db;
    }

	$db = false;
	return $db;
}

$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
?>
