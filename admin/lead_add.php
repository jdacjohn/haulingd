<?php require_once('../_includes/project.inc.php'); ?>
<?php
require_login();

$db = &connectToDB();
if($gDebug) $db->debug = true;

$result = mysql_query("select rotationCap from options where id=1 LIMIT 1");
$row = mysql_fetch_array($result);
$currentNumber = $row[0];

//define variables for form data
$arrVars = array(
	//array(THE_VALUE, THE_TYPE, THE_DEFINED_VALUE, THE_NOT_DEFINED_VALUE, FIELD_IS_ARRAY, ALLOW_NULL_VALUE)
	newFieldArray('vehicle_year', 'text', '', '', false, false),
	newFieldArray('vehicle_make', 'text', '', '', false, false),
	newFieldArray('vehicle_model', 'text', '', '', false, false),
	newFieldArray('vehicle_condition1', 'text', '', '', false, false),
	newFieldArray('vehicle_year2', 'text', '', '', false, true),
	newFieldArray('vehicle_make2', 'text', '', '', false, true),
	newFieldArray('vehicle_model2', 'text', '', '', false, true),
	newFieldArray('vehicle_condition2', 'text', '', '', false, true),
	newFieldArray('vehicle_pickup_location', 'text', '', '', false, false),
	newFieldArray('vehicle_pickup_state', 'text', '', '', false, false),
	newFieldArray('vehicle_destination_city', 'text', '', '', false, false),
	newFieldArray('vehicle_destination_state', 'text', '', '', false, false),
	newFieldArray('flexable_move', 'text', '', '', false, false),
	newFieldArray('carrier_type', 'text', '', '', false, false),
	newFieldArray('carrier_type2', 'text', '', '', false, false),
	newFieldArray('customername', 'text', '', '', false, false),
	newFieldArray('phone', 'text', '', '', false, false),
	newFieldArray('email', 'text', '', '', false, false),
	newFieldArray('email2', 'text', '', '', false, false),
	newFieldArray('contactmethod', 'text', '', '', false, false),
	newFieldArray('comments', 'text', '', '', false, false),
	newFieldArray('companyname', 'text', '', '', false, false),
	newFieldArray('move_date', 'date', '', '', false, false)
);

//initialize values
if (!isset($arrVals)) $arrVals = array();
foreach ($arrVars as $var) {
	$arrVals[$var[THE_VALUE]] = getParam($var[THE_VALUE], (bool) $var[FIELD_IS_ARRAY]);
}
if($gDebug) printvar($arrVals, 'arrVals');

if (!isset($arrErr)) $arrErr = array();
if (getParam('frmSubmit') == 'true') {
	//check req'd data
	if (strlen(trim($arrVals['customername'])) == 0) {
		$arrErr[] = form_error('Please enter the name of the customer.', 'customername', 'customer');
	}

	if (
		(
			//and both of these were left blank...
			!strlen(trim($arrVals['email']))
			&&
			!strlen(trim($arrVals['phone']))
		)
	) {
		$arrErr[] = form_error('Please enter either a valid email address ', 'email') .
		            form_error('or a valid, 10-digit phone number. (i.e. 123-456-7890)', 'phone');
}
	else {
		if (strlen(trim($arrVals['email'])) && !check_email(trim($arrVals['email']))) {
			$arrErr[] = form_error('Please enter a valid email address.', 'email');
		}

		if (strlen(trim($arrVals['phone'])) && !check_phone(trim($arrVals['phone']))) {
			$arrErr[] = form_error('Please enter a valid 10-digit phone number. (i.e. 123-456-7890)', 'phone');
		}
	}

	//if (!(strlen(trim($arrVals['vehicle_year'])) && is_numeric(trim($arrVals['vehicle_year'])))) {
	//	$arrErr[] = form_error('Please enter a valid year for the primary vehicle. (ex: 1997, 97, 07, etc.)', 'vehicle_year', 'year');
	//}

	if (!strlen(trim($arrVals['vehicle_make']))) {
		$arrErr[] = form_error('Please enter the make for the primary vehicle.', 'vehicle_make', 'make');
	}

	//if (!strlen(trim($arrVals['vehicle_model']))) {
	//	$arrErr[] = form_error('Please enter the model for the primary vehicle.', 'vehicle_model', 'model');
	//}

	if (
		(
			//if any of these are filled out...
			strlen(trim($arrVals['vehicle_year2']))
			||
			strlen(trim($arrVals['vehicle_make2']))
			||
			strlen(trim($arrVals['vehicle_model2']))
		)
		&&
		(
			//and any of these were left blank...
			!strlen(trim($arrVals['vehicle_year2']))
			||
			!strlen(trim($arrVals['vehicle_make2']))
			||
			!strlen(trim($arrVals['vehicle_model2']))
		)
	) {
		$arrErr[] = form_error('Please enter the year, ', 'vehicle_year2', 'year') . form_error('make and ', 'vehicle_make2', 'make') . form_error('model when listing a second vehicle.', 'vehicle_model2', 'model');
	}
	elseif (strlen(trim($arrVals['vehicle_year2'])) && !is_numeric(trim($arrVals['vehicle_year2']))) {
		$arrErr[] = form_error('Please enter a valid year for the second vehicle. (ex: 1997, 97, 07, etc.)', 'vehicle_year2', 'year');
	}

	if (strlen(trim($arrVals['vehicle_pickup_location'])) == 0) {
		$arrErr[] = form_error('Please enter the pickup city.', 'vehicle_pickup_location', 'city');
	}

	if (strlen(trim($arrVals['vehicle_pickup_state'])) == 0) {
		$arrErr[] = form_error('Please enter the pickup state.', 'vehicle_pickup_state', 'state');
	}

	if (strlen(trim($arrVals['vehicle_destination_city'])) == 0) {
		$arrErr[] = form_error('Please enter the destination city.', 'vehicle_destination_city', 'city');
	}

	if (strlen(trim($arrVals['vehicle_destination_state'])) == 0) {
		$arrErr[] = form_error('Please enter the destination state.', 'vehicle_destination_state', 'state');
	}

	if (strlen(trim($arrVals['comments'])) > 500) {
		$arrErr[] = form_error('Please ensure your comments are less than 500 characters in length.', 'comments');
	}

	if (!strlen(trim($arrVals['move_date'])) || strtotime(trim($arrVals['move_date'])) <= 0) {
		$arrErr[] = form_error('Please enter a valid move date. The format should be in <em>mm/dd/yyyy</em> format.', 'move_date', 'move date');
	}
	$phone_number_formatted = preg_replace("/([()-])/", "", $arrVals['phone']);
	//echo $phone_number_formatted;
	$area_code = substr($phone_number_formatted, 0, 3);
	$prefix = substr($phone_number_formatted, 3, 3);
	
	$sql = 'SELECT * FROM areacodes WHERE area_code = '.$area_code;
	$result = mysql_query($sql);
	$num_rows = mysql_num_rows($result);
	if($num_rows==0){
		$arrErr[] = form_error('Please ensure you entered a correct area code and prefix.', 'phone','area code and prefix');
	}
	
	//echo $sql;
	
} //end validation
if ($gDebug) printvar($arrVals, 'arrVals');

if (getParam('frmSubmit') == 'true' && sizeof($arrErr) == 0) {
	//insert new record
	$updateSQL['fields'] = "";
	$updateSQL['values'] = "";
	$newFieldsArray = array();
	foreach ($arrVars as $var) {
		if ($var[THE_VALUE] == 'id') continue;
		else {
			$updateSQL['fields'] .= (strlen($updateSQL['fields']) > 0) ? ", " : "";
			$updateSQL['values'] .= (strlen($updateSQL['values']) > 0) ? ", " : "";
			$updateSQL['fields'] .= "`" . $var[THE_VALUE] . "`";
			if ($var[THE_VALUE] == 'move_date') {
				$updateSQL['values'] .= getSQLValueString(date('Y-m-d', strtotime(trim($arrVals[$var[THE_VALUE]]))), 'date');
			}
			else {
				$updateSQL['values'] .= getSQLValueString($arrVals[$var[THE_VALUE]], $var[THE_TYPE], $var[THE_DEFINED_VALUE], $var[THE_NOT_DEFINED_VALUE], ARRAY_GLUE, $var[ALLOW_NULL_VALUE]);
			}
		}
	}

	if (!$db->Execute('INSERT INTO leads ('.$updateSQL['fields'].', `updated_at`, `created_at`) VALUES ('.$updateSQL['values'].', NOW(), NOW())')) {
		$arrErr[] = 'An error occured while while updating your data. Please try again.';
	} else {
		require('../_includes/processClass.inc.php');
		$send = new send_mail($db->Insert_ID());
		//echo $this->arrVals['customername'];
		$lead_id = $send->build_array($_POST);
        $lead_id_all = $send->build_array_all($_POST);

		//redirect to next step
		redirect('lead_details.php', array('confirm'=>'add', 'id' => $lead_id), $gDebug);
		exit();
	}
}
elseif (getParam('frmSubmit') != 'true') {
	//set default date
	$arrVals['move_date'] = date('m/d/Y');
} //END: if (getParam('frmSubmit') == 'true' && sizeof($arrErr) == 0))

if (htmlentities(PROJECT_TITLE) == 'Moving Leads'){
  $ML = 1;
} else {
  $ML = 0;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<?php include(WEB_ROOT.PROJECT_DIR.'/_includes/default-page-content-headers.inc.php'); ?>
		<?php include(WEB_ROOT.PROJECT_DIR.'/_includes/default-page-cache.inc.php'); ?>

		<title><?php echo htmlentities(PROJECT_TITLE); ?>: New Lead</title>

		<meta name="author" content="Chris Bloom" />
		<meta name="robots" content="noindex, nofollow, noarchive" />
		<meta name="MSSmartTagsPreventParsing" content="true" />
		<meta name="no-email-collection" content="http://www.unspam.com/noemailcollection/" />

		<meta name="DC.Creator" content="Chris Bloom" />
		<meta name="DC.Date" content="YYYY-MM-DD" />
		<meta name="DC.Format" content="text/html" />
		<meta name="DC.Language" content="en" />
		<meta name="DC.Title" content="New Lead" />

		<meta name="Description" content="Information about this web page" />
		<meta name="Keywords" content="keyword1, keyword2, keyword3" />

		<meta name="geo.region" content="" />
		<meta name="geo.placename" content="" />

		<link rel="start" href="<?php echo htmlentities(PROJECT_URL); ?>/" title="Home" />

		<?php include(WEB_ROOT.PROJECT_DIR.'/_includes/default-page-style.inc.php'); ?>
		<?php include(WEB_ROOT.PROJECT_DIR.'/_includes/default-page-script.inc.php'); ?>

		<script type="text/javascript" src="../scripts/jscalendar-1.0/calendar.js"></script>
		<script type="text/javascript" src="../scripts/jscalendar-1.0/lang/calendar-en.js"></script>
		<script type="text/javascript" src="../scripts/jscalendar-1.0/calendar-setup.js"></script>
		<script type="text/javascript">
		/**
		 * COMMON FUNCTIONS
		 * These functions are used throughout this page. They could eventually
		 * be moved into the common.js file, but so far this is the only page
		 * in the application that uses them.
		 */

		function setCSS(object, css) {
			if (typeof object != 'object' || object.nodeType != 1)
				var object = document.getElementById(object.toString());
			if (css.substring(css.length-1,css.length) == ';')
				css = css.substring(0, css.length-1);
			if (object.style) object = object.style;
			object.cssText = css + ';';
		}

		/**
		 * DATE SELECTOR FUNCTIONS
		 *
		 */

		addEvent(window, 'load', initDateSelectors);

		var move_date         = null;
		var move_date_trigger = null;

		function initDateSelectors() {
			move_date = document.getElementById('move_date');

			if (
				!move_date
			) {
				return;
			}

			//set up the triggers for the calendar buttons
			move_date_trigger = document.createElement('img');
			move_date_trigger.setAttribute('id', 'move_date_trigger');
			setCSS(move_date_trigger, 'vertical-align: middle; border-style: none; margin: 0 1em 0 0;');
			move_date_trigger.setAttribute('src', '../scripts/jscalendar-1.0/calendar-icon-03.gif');
			move_date_trigger.setAttribute('width', '30');
			move_date_trigger.setAttribute('height', '19');
			move_date.parentNode.appendChild(move_date_trigger);

			//create calendar widgets, triggered by new image elements and bound to date fields
			setupCalendar("move_date", "move_date_trigger", null, null);
		}

		var gDateFormat = '%m/%d/%Y';

		function setupCalendar (boundFieldId, triggerFieldId, fnOnUpdate, fnDateStatusFunc) {
			Calendar.setup({
				align          : "Bl",
				button         : triggerFieldId,
				cache          : false,
				dateStatusFunc : fnDateStatusFunc,
				electric       : false,
				ifFormat       : gDateFormat,
				inputField     : boundFieldId,
				onUpdate       : fnOnUpdate,
				range          : Array(1976, 2038),
				singleClick    : true,
				step           : 1,
				weekNumbers    : false
			});
		}
		</script>
		<style type="text/css">@import url(../scripts/jscalendar-1.0/calendar-win2k-1.css);</style>
	</head>
	<body>
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
					<a name="nav_start" id="nav_start"></a>
					<h2>Sections</h2>
					<div class="hidden">
						<a href="#content_start">Jump to content</a>
					</div>
					<?php include(WEB_ROOT.PROJECT_DIR.'/_includes/navigation-main.inc.php'); ?>
				</div>
<!-- END: div id=navigation -->
				<div id="content">
					<a name="content_start" id="content_start"></a>

					<div id="breadcrumb"></div>

					<?php include(WEB_ROOT.PROJECT_DIR.'/_includes/default-page-error-handler.inc.php'); ?>

					<div id="column1">
						<h2>New Lead</h2>
						<p>Use the form below to add a new lead.</p>
						<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
							<div class="formElementSet formElementSetText">
								<label class="fieldlabel req" for="customername">Name</label>
								<input name="customername" type="text" class="text" id="customername" value="<?php echo htmlspecialchars($arrVals['customername']); ?>" maxlength="100" />
							</div>
							<div class="formElementSet formElementSetText">
								<label class="fieldlabel" for="phone">Phone</label>
								<input name="phone" type="text" class="text" id="phone" value="<?php echo htmlspecialchars($arrVals['phone']); ?>" maxlength="100" />
							</div>
							<div class="formElementSet formElementSetText">
								<label class="fieldlabel" for="email">E-mail</label>
								<input name="email" type="text" class="text" id="email" value="<?php echo htmlspecialchars($arrVals['email']); ?>" maxlength="100" />
							</div>
							<?php if ($ML != 1){ echo '<div class="formElementSet formElementSetText">
								<label class="fieldlabel req" for="vehicle_year">Vehicle Year</label>
								<input name="vehicle_year" type="text" class="text" id="vehicle_year" value="';
                                echo htmlspecialchars($arrVals['vehicle_year']);
                                echo '" maxlength="100" /></div>'; } else { } ?>
							<div class="formElementSet formElementSetText">
								<label class="fieldlabel req" for="vehicle_make"><?php if ($ML == 1){echo 'Rooms To Move';} else {echo 'Vehicle Make';}; ?></label>
								<input name="vehicle_make" type="text" class="text" id="vehicle_make" value="<?php echo htmlspecialchars($arrVals['vehicle_make']); ?>" maxlength="100" />
							</div>
							<?php if ($ML != 1){ echo '<div class="formElementSet formElementSetText">
								<label class="fieldlabel req" for="vehicle_model">Vehicle Model</label>
								<input name="vehicle_model" type="text" class="text" id="vehicle_model" value="'; echo htmlspecialchars($arrVals['vehicle_model']); echo '" maxlength="100" />
							</div>
							<div class="formElementSet formElementSetText">
								<label class="fieldlabel req" for="vehicle_condition1">Vehicle Condition1</label>
								<select class="text" name="vehicle_condition1" id="vehicle_condition1">
									<option value=""'; echo (('' == $arrVals['vehicle_condition1']) ? 'selected="selected"' : ''); echo '>Choose One</option>
									<option value="Operational"'; echo (('Operational' == $arrVals['vehicle_condition1']) ? 'selected="selected"' : ''); echo '>Operational</option>
									<option value="Rolling, Non-Operational"'; echo (('Rolling, Non-Operational' == $arrVals['vehicle_condition1']) ? 'selected="selected"' : ''); echo '>Rolling, Non-Operational</option>
									<option value="Non-Operational"'; echo (('Non-Operational' == $arrVals['vehicle_condition1']) ? 'selected="selected"' : ''); echo '>Non-Operational</option>
								</select>
							</div>
							<div class="formElementSet formElementSetText">
								<label class="fieldlabel" for="vehicle_year2">Vehicle Year 2</label>
								<input name="vehicle_year2" type="text" class="text" id="vehicle_year2" value="'; echo htmlspecialchars($arrVals['vehicle_year2']); echo'" maxlength="100" />
							</div>
							<div class="formElementSet formElementSetText">
								<label class="fieldlabel" for="vehicle_make2">Vehicle Make 2</label>
								<input name="vehicle_make2" type="text" class="text" id="vehicle_make2" value="'; echo htmlspecialchars($arrVals['vehicle_make2']); echo '" maxlength="100" />
							</div>
							<div class="formElementSet formElementSetText">
								<label class="fieldlabel" for="vehicle_model2">Vehicle Model 2</label>
								<input name="vehicle_model2" type="text" class="text" id="vehicle_model2" value="'; echo htmlspecialchars($arrVals['vehicle_model2']); echo '" maxlength="100" />
							</div>
							<div class="formElementSet formElementSetText">
								<label class="fieldlabel" for="vehicle_condition2">Vehicle Condition 2</label>
								<select class="text" name="vehicle_condition2" id="vehicle_condition2">
									<option value=""'; echo (('' == $arrVals['vehicle_condition2']) ? 'selected="selected"' : ''); echo '>Choose One</option>
									<option value="Operational"'; echo (('Operational' == $arrVals['vehicle_condition2']) ? 'selected="selected"' : ''); echo '>Operational</option>
									<option value="Rolling, Non-Operational"'; echo (('Rolling, Non-Operational' == $arrVals['vehicle_condition2']) ? 'selected="selected"' : ''); echo '>Rolling, Non-Operational</option>
									<option value="Non-Operational"'; echo (('Non-Operational' == $arrVals['vehicle_condition2']) ? 'selected="selected"' : ''); echo '>Non-Operational</option>
								</select>
							</div>';} else { } ?>
							<div class="formElementSet formElementSetText">
								<label class="fieldlabel req" for="vehicle_pickup_location">Pickup Location</label>
								<input name="vehicle_pickup_location" type="text" class="text" id="vehicle_pickup_location" value="<?php echo htmlspecialchars($arrVals['vehicle_pickup_location']); ?>" maxlength="100" />
							</div>
							<div class="formElementSet formElementSetText">
								<label class="fieldlabel req" for="vehicle_pickup_state">Pickup State</label>
								<select class="text" name="vehicle_pickup_state" id="vehicle_pickup_state">
									<option value="">Choose one</option>
									<?php
									$srs = $db->Execute('SELECT * FROM states ORDER BY full_name ASC');
									if ($srs) {
										while (!$srs->EOF) {
                                    ?>
									<option value="<?php w($srs->fields['abbriv']); ?>" <?php echo (($srs->fields['abbriv'] == $arrVals['vehicle_pickup_state']) ? 'selected="selected"' : ''); ?>><?php w($srs->fields['full_name']); ?></option>
                                    <?php
											$srs->MoveNext();
										}
									}
									?>
								</select>
							</div>
							<div class="formElementSet formElementSetText">
								<label class="fieldlabel req" for="vehicle_destination_city">Destination City</label>
								<input name="vehicle_destination_city" type="text" class="text" id="vehicle_destination_city" value="<?php echo htmlspecialchars($arrVals['vehicle_destination_city']); ?>" maxlength="100" />
							</div>
							<div class="formElementSet formElementSetText">
								<label class="fieldlabel req" for="vehicle_destination_state">Destination State</label>
								<select class="text" name="vehicle_destination_state" id="vehicle_destination_state">
									<option value="">Choose one</option>
									<?php
									$srs = $db->Execute('SELECT * FROM states ORDER BY full_name ASC');
									if ($srs) {
										while (!$srs->EOF) {
									?>
									<option value="<?php w($srs->fields['abbriv']); ?>" <?php echo (($srs->fields['abbriv'] == $arrVals['vehicle_destination_state']) ? 'selected="selected"' : ''); ?>><?php w($srs->fields['full_name']); ?></option>
									<?php
											$srs->MoveNext();
										}
									}
									?>
								</select>
							</div>
							<?php if ($ML != 1){ echo '<div class="formElementSet formElementSetText">
								<label class="fieldlabel req" for="carrier_type">Carrier Type</label>
								<select class="text" name="carrier_type" id="carrier_type">
									<option value=""'; echo (('' == $arrVals['carrier_type']) ? 'selected="selected"' : ''); echo '>Any</option>
									<option value="open"'; echo (('open' == $arrVals['carrier_type']) ? 'selected="selected"' : ''); echo '>Open</option>
									<option value="enclosed"'; echo (('enclosed' == $arrVals['carrier_type']) ? 'selected="selected"' : ''); echo '>Enclosed</option>
								</select>
							</div>'; } else {} ?>
							<div class="formElementSet formElementSetText">
								<label class="fieldlabel" for="comments">Comments</label>
								<textarea class="textarea" name="comments" id="comments"><?php echo htmlspecialchars($arrVals['comments']); ?></textarea>
							</div>
							<div class="formElementSet formElementSetText">
								<label for="move_date" class="fieldlabel req">Move Date:</label>
								<input name="move_date" type="text" class="text" id="move_date" value="<?php echo htmlspecialchars($arrVals['move_date']); ?>" maxlength="10" />
							</div>
							<div class="">&nbsp;</div>
							<div class="formElementSet formElementSetButton">
								<input style="color: #088C26; float: left; margin-left: 2em; margin-right: 2em;" type="submit" name="submit" id="submit" value="Save" />
								<script type="text/javascript" language="javascript">
								<!--
								document.write('<input style="color: #941000; margin-left: 2em; margin-right: 2em;" type="button" name="cancel" id="cancel" value="Cancel" onClick="document.location=\'leads.php\'" />');
								//-->
								</script>
								<noscript>
									<a href="leads.php" style="display: block; color: #941000 !important; padding: 1px 0.75em;">Cancel</a>
								</noscript>
								<input type="hidden" name="frmSubmit" value="true" />
								<?php
								$dataArray = array();
								if (!ARE_WE_LIVE) $dataArray['debug'] = $gDebug;
								writeHiddenFormFields($dataArray);
								?>
							</div>
						</form>
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
