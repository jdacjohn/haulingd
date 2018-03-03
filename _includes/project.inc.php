<?php
//Get configuration variables used throughout project
require_once(dirname(__FILE__).'/../_config/config.inc.php');

//redirect to offline message if performing updates
/*if (strpos($_SERVER['PHP_SELF'],'offline.php') === false && !isset($_REQUEST['debug'])) {
	header(PROJECT_URL.'/offline.php');
}*/

//turn off debugging for production
if (ARE_WE_LIVE) {
	unset($_REQUEST['debug']);
	$gDebug = false;
}
else {
	$gDebug = ((isset($_REQUEST['debug'])) ? (bool) trim($_REQUEST['debug']) : false);
	if ($gDebug) {
		ob_start();
	}
}

//Include common functions
require_once(WEB_ROOT.PROJECT_DIR.'/_includes/common.inc.php');
require_once(WEB_ROOT.PROJECT_DIR.'/_includes/connectToDB.inc.php');
require_once(WEB_ROOT.PROJECT_DIR.'/_includes/addURLParamFunction.inc.php');
# require_once(WEB_ROOT.PROJECT_DIR.'/_includes/stringSwapClass.inc.php');
require_once(WEB_ROOT.PROJECT_DIR.'/_includes/project-functions.inc.php');

//Some application level variables
$arrErr = getParam(POSTBACK_PARAMETER_PREFIX.'error', true);

//To make self referrencing easy
$gFullSelfRequest = 'http'.((isset($_SERVER['HTTPS'])) ? 's' : '').'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']; //With QS
$gQualifiedSelfRequest = 'http'.((isset($_SERVER['HTTPS'])) ? 's' : '').'://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']; //Without QS

//fire up that session, baby!
//session_cache_expire(60); //60 minute page expires
ini_set('session.gc_maxlifetime', 3600); //60 minute session expires
session_start(); //start the session

if (!isset($_SESSION[SESSION_NAME])) {
	if($GLOBALS['gDebug']) printvar('clearing $_SESSION[SESSION_NAME] as part of startup procedure in project.inc.php.');
	reset_session();
}
if($GLOBALS['gDebug']) printvar($_SESSION[SESSION_NAME], 'Session after init');

define('APP_LOADED',1);
?>