<?php
define('LOGIN_BAD_QUERY', -1);
define('LOGIN_BAD_USERNAME', -2);
define('LOGIN_BAD_PASSWORD', -3);
define('LOGIN_BAD_ACCOUNT', -4);
define('LOGIN_OK', 1);

function form_error($error_message, $dom_id, $keywords = false) {
	if (!$error_message || !strlen(trim($error_message))) return 'An unspecified error occurred.';
	if (!$dom_id || !strlen(trim($dom_id))) return $error_message;
	if (!$keywords || !strlen(trim($keywords))) $keywords = $dom_id;
	$dom_id = addslashes(htmlspecialchars($dom_id));
	return preg_replace('/('.preg_quote($keywords,'/').')/ie', "'<script type=\"text/javascript\"><!--\ndocument.write(\\'<strong><a href=\"javascript:void(0);\" style=\"color:#FF0000;\" onclick=\"setFocus(\\\\\\\\\'$dom_id\\\\\\\\\', 1);\">'.addslashes('\\1').'</a></strong>\\');\n//--></script><noscript><strong>\\1</strong></noscript>'", $error_message);
}

function init_user($username, $password, $pw_prehashed = false) {
	$check_pw = (($pw_prehashed) ? $password : md5($password));

	$db = &connectToDB();
	$rs = $db->SelectLimit('SELECT id, login, password, active, created_at FROM users WHERE login LIKE ?', 1, 0, array($username));
	if (!$rs) {
		if ($GLOBALS['gDebug']) printvar('could not query user table: '.$db->ErrorMsg(), basename(__FILE__).' ('.__LINE__.')');
		return LOGIN_BAD_QUERY;
	}
	elseif ($rs->EOF) {
		if ($GLOBALS['gDebug']) printvar('Username \''.$username.'\' not found in user table', basename(__FILE__).' ('.__LINE__.')');
		reset_session();
		return LOGIN_BAD_USERNAME;
	}
	elseif ($rs->fields['active'] != 1) {
		if ($GLOBALS['gDebug']) printvar('Username \''.$username.'\' is disabled', basename(__FILE__).' ('.__LINE__.')');
		reset_session();
		return LOGIN_BAD_ACCOUNT;
	}
	elseif (trim($check_pw) != trim($rs->fields['password'])) {
		if ($GLOBALS['gDebug']) printvar('Password specified does not match', basename(__FILE__).' ('.__LINE__.')');
		reset_session();
		return LOGIN_BAD_PASSWORD;
	}
	else {
		if ($GLOBALS['gDebug']) printvar('Login success', basename(__FILE__).' ('.__LINE__.')');
		if ($GLOBALS['gDebug']) printvar($_SESSION, 'Session just before login');
		$_SESSION[SESSION_NAME]['user'] = array(
			'logged_in' => true,
			'id' => $rs->fields['id'],
			'login' => $rs->fields['login'],
			'hash' => md5($rs->fields['id'].ARRAY_GLUE.$rs->fields['created_at']),
			'last_initialized' => time()
		);
		if($GLOBALS['gDebug']) printvar($_SESSION, 'Session just after login');

		return LOGIN_OK;
	}
}

function intercept_request($targetURL,$returnURL) {
	$targetURL = (($targetURL) ? $targetURL : 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF']);
	$returnURL = ((strlen($returnURL)) ? $returnURL : false);

	if($GLOBALS['gDebug']) printvar($targetURL, 'intercept_request targetURL:');
	if($GLOBALS['gDebug']) printvar($returnURL, 'intercept_request returnURL:');

	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		$dataArray = array_clean(array_merge($_GET, $_POST), POSTBACK_PARAMETER_PREFIX);
		$dataArray[POSTBACK_PARAMETER_PREFIX.'return_method'] = 'post';
		if ($returnURL) $dataArray[POSTBACK_PARAMETER_PREFIX.'return'] = $returnURL;
		if (
			strpos($_SERVER['CONTENT_TYPE'],'multipart/form-data') === 0
			&&
			isset($_FILES)
			&&
			sizeof($_FILES)
		) {
			//set error message to be displayed on the next page.
			$dataArray[POSTBACK_PARAMETER_PREFIX.'error'] = 'Your login expired before the form could be submitted. After signing in you will need to upload the file again.';
		}
		if ($GLOBALS['gDebug']) $dataArray['debug'] = 1;
		if ($GLOBALS['gDebug']) printvar($dataArray, 'intercept_request dataArray:');
		if ($GLOBALS['gDebug']) printvar('redirecting by post', 'intercept_request:');
		redirectByForm($targetURL,$dataArray,(!$GLOBALS['gDebug']));
	} else {
		$dataArray = $_GET;
		if ($returnURL) $dataArray[POSTBACK_PARAMETER_PREFIX.'return'] = $returnURL;
		if ($GLOBALS['gDebug']) $dataArray['debug'] = 1;
		if ($GLOBALS['gDebug']) printvar($dataArray, 'intercept_request dataArray:');
		if ($GLOBALS['gDebug']) printvar('normal redirect', 'intercept_request:');
		redirect($targetURL,$dataArray,$GLOBALS['gDebug']);
	}
}

function is_logged_in() {
	/**
	 * Checks to see if a user has logged in to a valid profile.
	 */
	if (
		isset($_SESSION[SESSION_NAME]['user']['logged_in'])
		&&
		$_SESSION[SESSION_NAME]['user']['logged_in'] == true
	) {
		if($GLOBALS['gDebug']) printvar('true', 'is_logged_in returning:');
		return true;
	}
	else {
		if($GLOBALS['gDebug']) printvar('false', 'is_logged_in returning:');
		return false;
	}
}

function is_logged_in_user($user_id) {
	return (is_logged_in() && $user_id == $_SESSION[SESSION_NAME]['user']['id']);
}

function is_valid_date($month,$date,$year,$limitCurDate = false) {
	/**
	 * Checks for a valid date within the acceptable range of MySQL (years 1000-9999)
	 * Set $limitCurYear = true to limit max date to current year
	 */

	$minYear = abs(date('Y')) - 80; //limit past dates to 80 years
	$maxYear = abs(date('Y')) + 10; //limit future dates to 10 years

	if ($limitCurDate) {
		if (mktime(0,0,0,intval($month),intval($date),intval($year)) > mktime(0,0,0,date('n'),date('j'),date('Y'))) return false;
	}

	if ($year>=$minYear && $year<=$maxYear) {
		if (checkdate($month, $date, $year)) {
			return true;
		}
		else {
			return false;
		}
	}
	else {
		return false;
	}
}

function package_leads($package_id) {
	$db = &connectToDB();
	if($GLOBALS['gDebug']) $db->debug = true;
	$rs = $db->SelectLimit('SELECT leads FROM packages WHERE id = ?', 1, 0, array(intval($package_id)));
	if($GLOBALS['gDebug']) printvar($rs, 'package_leads rs:');
	if ($rs && !$rs->EOF)
		return intval($rs->fields['leads']);
	else
		return 0;
}

function require_login($returnURL = false) {
	if (!is_logged_in()) {
		$returnURL = (($returnURL) ? $returnURL : 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF']);
		//printvar($returnURL)
		intercept_request(PROJECT_URL.'/login.php',$returnURL);
	}
	else {
		//user is logged in -> reinitialize every few minutes just in case any user data has changed since
		if ((time() - $_SESSION[SESSION_NAME]['user']['last_initialized']) > (60*5) || getParam('refresh') == 1) { //check at least once every 5 minutes
			return reinit_user();
		}
	}
}

function reinit_user() {
	if (!is_logged_in()) return false;

	//check login
	$db = &connectToDB();
	if($GLOBALS['gDebug']) $db->debug = true;
	$sql = 'SELECT id, login, password, created_at FROM users WHERE id = ?';
	$rs = $db->SelectLimit($sql, 1, 0, array($_SESSION[SESSION_NAME]['user']['id']));

	if($GLOBALS['gDebug']) printvar($rs, 'rs');
	if (!$rs) {
		if ($GLOBALS['gDebug']) printvar('could not query user table: '.$db->ErrorMsg(), basename(__FILE__).' ('.__LINE__.')');
		reset_session();
		return false;
	}
	elseif ($rs->EOF) {
		if ($GLOBALS['gDebug']) printvar('Username \''.$username.'\' not found in user table', basename(__FILE__).' ('.__LINE__.')');
		reset_session();
		return false;
	}
	elseif ($_SESSION[SESSION_NAME]['user']['hash'] != md5($rs->fields['id'].ARRAY_GLUE.$rs->fields['created_at'])) {
		if ($GLOBALS['gDebug']) printvar('User hash does not match', basename(__FILE__).' ('.__LINE__.')');
		reset_session();
		return false;
	}
	else {
		return (init_user($rs->fields['login'], $rs->fields['password'], true) === LOGIN_OK);
	}
}

function reset_session($limit_to_index = false) {
	switch (strtolower($limit_to_index)) {
		case 'user':
			$_SESSION[SESSION_NAME]['user'] = array();
			break;
		case 'admin':
			$_SESSION[SESSION_NAME]['admin'] = array();
			break;
		default:
			$_SESSION[SESSION_NAME]['user'] = array();
			$_SESSION[SESSION_NAME]['admin'] = array();
			break;
	}
}

function sendmail($to, $subject, $message, $reply_to=false, $headers=false) {
	if ($headers) {
		if (!is_array($headers)) $headers = array($headers);
		foreach($headers as $k=>$v) {
			$headers[$k] = prepForEmail(trim($v),false,false,false,false,true);
		}
	}

	if($GLOBALS['gDebug']) printvar($to, 'sendmail to:');
	if($GLOBALS['gDebug']) printvar($subject, 'sendmail subject:');
	if($GLOBALS['gDebug']) printvar($message, 'sendmail message:');
	if($GLOBALS['gDebug']) printvar($replyTo, 'sendmail reply to:');
	if($GLOBALS['gDebug']) printvar($headers, 'sendmail headers:');

	return mail($to,
		prepForEmail(PROJECT_TITLE_SHORT.': '.$subject,false,false,false,false,true),
		$message,
		'From: '.prepForEmail(PROJECT_TITLE_SHORT.' <'.EMAIL_ADMIN.'>',false,false,false,false,true).EMAIL_SEPARATOR .
		((strlen($reply_to)) ? 'Reply-To: '.prepForEmail($reply_to,false,false,false,false,true).EMAIL_SEPARATOR : '') .
		((is_array($headers) && sizeof($headers)) ? join(EMAIL_SEPARATOR,$headers).EMAIL_SEPARATOR : '').
		'X-Mailer: PHP/' . phpversion().EMAIL_SEPARATOR
	);
}
?>