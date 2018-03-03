<?php require_once('_includes/project.inc.php'); ?>
<?php
//define variables for form data
$arrVars = array(
	//array(THE_VALUE, THE_TYPE, THE_DEFINED_VALUE, THE_NOT_DEFINED_VALUE, FIELD_IS_ARRAY, ALLOW_NULL_VALUE)
	newFieldArray('__login__username'),
	newFieldArray('__login__password'),
	newFieldArray('__login__formSubmit')
);

if($gDebug) printvar($_GET, '_GET');
if($gDebug) printvar($_POST, '_POST');
if($gDebug) printvar($_REQUEST, '_REQUEST');

//initialize values
if (!isset($arrVals)) $arrVals = array();
foreach ($arrVars as $var) {
	$arrVals[$var[THE_VALUE]] = getParam($var[THE_VALUE], (bool) $var[FIELD_IS_ARRAY]);
}
if($gDebug) printvar($arrVals, 'arrVals');

$url = false;
if (isset($_REQUEST[POSTBACK_PARAMETER_PREFIX.'return']) && strlen(trim($_REQUEST[POSTBACK_PARAMETER_PREFIX.'return']))) {
	$url = $_REQUEST[POSTBACK_PARAMETER_PREFIX.'return'];
	if($gDebug) printvar($url, 'setting URL to value of $_REQUEST[POSTBACK_PARAMETER_PREFIX.\'return\']');
}
elseif (isset($_REQUEST['return']) && strlen(trim($_REQUEST['return']))) {
	$url = $_REQUEST['return'];
	if($gDebug) printvar($url, 'setting URL to value of $_REQUEST[\'return\']');
}
elseif ($arrVals['__login__formSubmit'] != 'true' && isset($_SERVER['HTTP_REFERER']) && strlen(trim($_SERVER['HTTP_REFERER']))) {
	$url = $_SERVER['HTTP_REFERER'];
	if($gDebug) printvar($url, 'setting URL to value of $_SERVER[\'HTTP_REFERER\']');
}
//exclusion list:
if (
	!$url
	||
	strpos($url, $_SERVER['PHP_SELF']) !== false
	||
	strpos($url, '/signup/') !== false
) {
	$url = false;
	if($gDebug) printvar('setting URL to false');
}

if (!isset($arrErr)) $arrErr = array();
if (getParam('confirm') == 'logout' && (is_logged_in())) {
	if($gDebug) printvar($_SESSION[SESSION_NAME], 'Session before logout block');
	reset_session();
	if($gDebug) printvar($_SESSION[SESSION_NAME], 'Session after logout block');
}
elseif (getParam('__login__formSubmit') == 'true') {
	//the form was submitted
	//check req'd data
	if (strlen(trim($arrVals['__login__username'])) == 0) {
		$arrErr[] = form_error('Please enter your username.', 'username');
	}

	if (strlen(trim($arrVals['__login__password'])) == 0) {
		$arrErr[] = form_error('Please enter your password.', 'password');
	}

	if (sizeof($arrErr) == 0) {
	//no errors returned

		//check login
		$login_check = init_user(trim($arrVals['__login__username']), trim($arrVals['__login__password']));
		switch ($login_check) {
			case LOGIN_OK:
				if($gDebug) printvar($_SESSION, 'Session before redirect');

				$dataArray = array_clean(array_clean($_POST,POSTBACK_PARAMETER_PREFIX),'__login__');
				if ($gDebug) $dataArray['debug'] = 1;
				if ($url && isset($_REQUEST[POSTBACK_PARAMETER_PREFIX.'return_method']) && $_REQUEST[POSTBACK_PARAMETER_PREFIX.'return_method'] == 'post') {
					if($gDebug) printvar($dataArray, 'posting to '.$url);
					redirectByForm($url, $dataArray, (!$gDebug));
				}
				else {
					if (!$url) $url = 'http://'.$_SERVER['SERVER_NAME'].PROJECT_URL.'/';
					if($gDebug) printvar($url, 'redirecting to ');
					$dataArray = array(
						'confirm' => 'login',
						'return' => $url
					);
					if ($gDebug) $dataArray['debug'] = 1;
					redirect($_SERVER['PHP_SELF'],$dataArray,$gDebug);
					exit();
				}
				break;
			case LOGIN_BAD_USERNAME:
			case LOGIN_BAD_PASSWORD:
			case LOGIN_BAD_ACCOUNT:
				$arrErr[] = form_error('The specified username or ', 'username').form_error('password is invalid. Please try again.', 'password');
				reset_session();
				break;
			case LOGIN_BAD_QUERY:
			default:
				$arrErr[] = 'An error was encountered while trying to log in. Please try again. If the problem persists please <a href="'.PROJECT_URL.'/contact/">let us know</a>.';
				break;
		}
	}
	if($gDebug) printvar($arrErr, 'arrErr');
}
else {
	//overrides for any default values
	//n/a

} //END: if (getParam('formSubmit') == 'true')
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<?php include(WEB_ROOT.PROJECT_DIR.'/_includes/default-page-content-headers.inc.php'); ?>
		<?php include(WEB_ROOT.PROJECT_DIR.'/_includes/default-page-cache.inc.php'); ?>

		<title><?php echo htmlentities(PROJECT_TITLE); ?>: Page Title</title>

		<meta name="author" content="Chris Bloom" />
		<meta name="robots" content="noindex, nofollow, noarchive" />
		<meta name="MSSmartTagsPreventParsing" content="true" />
		<meta name="no-email-collection" content="http://www.unspam.com/noemailcollection/" />

		<meta name="DC.Creator" content="Chris Bloom" />
		<meta name="DC.Date" content="YYYY-MM-DD" />
		<meta name="DC.Format" content="text/html" />
		<meta name="DC.Language" content="en" />
		<meta name="DC.Title" content="Page Title" />

		<meta name="Description" content="Information about this web page" />
		<meta name="Keywords" content="keyword1, keyword2, keyword3" />

		<meta name="geo.region" content="" />
		<meta name="geo.placename" content="" />

		<link rel="start" href="<?php echo htmlentities(PROJECT_URL); ?>/" title="Home" />

		<?php include(WEB_ROOT.PROJECT_DIR.'/_includes/default-page-style.inc.php'); ?>
		<?php include(WEB_ROOT.PROJECT_DIR.'/_includes/default-page-script.inc.php'); ?>

		<script type="text/javascript"></script>
		<style type="text/css" media="all"></style>
	</head>
	<body onload="setFocus('username')">
		<div id="box">
			<div id="header">
				<?php include(WEB_ROOT.PROJECT_DIR.'/_includes/header.inc.php'); ?>
			</div>
<!-- END: div id=header -->
			<div id="bulletin">
				<?php include(WEB_ROOT.PROJECT_DIR.'/_includes/bulletin.inc.php'); ?>
			</div>
<!-- END: div id=bulletin -->
			<div id="body">
				<div id="navigation" class="topicArea-none">
					<h2>Sections</h2>
					<div class="hidden"><a href="#content">Skip to content</a></div>

					<?php include(WEB_ROOT.PROJECT_DIR.'/_includes/navigation-main.inc.php'); ?>
				</div>
<!-- END: div id=navigation -->
				<div id="content">
					<div class="hidden"><a name="content"></a></div>

					<div id="breadcrumb">
						<span class="breadcrumb_label">You are here:</span>
						<span class="breadcrumb_home"><a href="<?php echo htmlentities(PROJECT_URL); ?>">Job Summary</a></span>
						<span class="breadcrumb_seperator">&rarr;</span>
						<span class="breadcrumb_currentpage">Sign In</span>
					</div>

					<?php include(WEB_ROOT.PROJECT_DIR.'/_includes/default-page-error-handler.inc.php'); ?>

					<div id="column1">
						<?php if (getParam('confirm') == "login" && is_logged_in()) { ?>
						<h2>Welcome</h2>
						<p>You are now logged in. <a href="<?php echo htmlentities($url); ?>">Continue</a>.</p>
						<?php } elseif (getParam('confirm') == "" && is_logged_in()) { ?>
						<h2>Welcome</h2>
						<p>You are already logged in. <a href="<?php echo htmlentities(PROJECT_URL); ?>/">Return to the home page.</a></p>
						<?php } elseif (getParam('confirm') == "logout" && !is_logged_in()) { ?>
						<h2>Goodbye</h2>
						<p>You are now logged out. <a href="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>">Log back in</a>.</p>
						<?php } else { ?>
						<h2>Please Sign In</h2>
						<p>Please enter your username and password into the form below.</p>

						<form id="login_f" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="post">
							<input type="hidden" name="__login__formSubmit" value="true" />
							<?php
							$dataArray = array_merge($_GET, $_POST, array('debug'=>$gDebug));
							$dataArray[POSTBACK_PARAMETER_PREFIX.'return'] = $url;
							writeHiddenFormFields($dataArray,'__login__');
							?>

							<div class="formElementSet formElementSetText" id="username_d">
								<label for="username" class="fieldlabel req">Username:</label>
								<input type="text" class="text" name="__login__username" id="username" value="<?php echo htmlentities($arrVals['__login__username']); ?>" />
							</div>
							<div class="formElementSet formElementSetText" id="password_d">
								<label for="password" class="fieldlabel req">Password:</label>
								<input type="password" class="text" name="__login__password" id="password" value="" />
							</div>
							<div class="formElementSet formElementSetButtons" id="buttons_d">
								<input type="submit" class="button" name="login" id="login" value="Login" />
								<script type="text/javascript"><!--
									document.write('<input type="reset" class="button" name="cancel" id="cancel" value="Cancel" onclick="document.location=\'<?php echo htmlentities(addslashes($url)); ?>\'" />');
								//--></script>
								<noscript>
									<a href="<?php echo htmlentities($url); ?>" class="button" id="cancel">Cancel</a>
								</noscript>
							</div>
						</form>
						<p class="footnote req">All fields are required.</p>
						<?php } ?>
					</div>
<!-- END: div id=column1 -->
				</div>
<!-- END: div id=content -->
			</div>
<!-- END: div id=body -->
			<div id="footer">
				<?php include(WEB_ROOT.PROJECT_DIR.'/_includes/footer.inc.php'); ?>
			</div>
<!-- END: div id=footer -->
		</div>
<!-- END: div id=box -->
		<div id="toolbar">
			<?php include(WEB_ROOT.PROJECT_DIR.'/_includes/toolbar.inc.php'); ?>
		</div>
<!-- END: div id=toolbar -->
	</body>
</html>
