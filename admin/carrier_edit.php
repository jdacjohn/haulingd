<?php require_once('../_includes/project.inc.php'); ?>
<?php
require_login();
$gDebug = true;
$db = &connectToDB();
if($gDebug) $db->debug = true;

//define variables for form data
$arrVars = array(
	//array(THE_VALUE, THE_TYPE, THE_DEFINED_VALUE, THE_NOT_DEFINED_VALUE, FIELD_IS_ARRAY, ALLOW_NULL_VALUE)
	newFieldArray('id', 'int'),
	newFieldArray('company_name', 'string', '', '', false, false),
	newFieldArray('company_contact', 'string', '', '', false, false),
	newFieldArray('address_1', 'string', '', '', false, false),
	newFieldArray('address_2', 'string', '', '', false, false),
	newFieldArray('city', 'string', '', '', false, false),
	newFieldArray('state', 'string', '', '', false, false),
	newFieldArray('zip', 'string', '', '', false, false),
	newFieldArray('phone', 'string', '', '', false, false),
	newFieldArray('contact_email', 'string', '', '', false, false),
	newFieldArray('lead_email_1', 'string', '', '', false, false),
	newFieldArray('lead_email_2', 'string', '', '', false, false),
	newFieldArray('fax', 'string', '', '', false, false),
	newFieldArray('email_format', 'int'),
	newFieldArray('package_id', 'int'),
	newFieldArray('fourty_eight', 'int'),
	newFieldArray('alaska', 'int'),
	newFieldArray('hawaii', 'int'),
	newFieldArray('dayparting', 'int'),
	newFieldArray('open', 'int'),
	newFieldArray('covered', 'int'),
	newFieldArray('active', 'int'),
  newFieldArray('dist_type', 'string', '', '', false, false)
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
	if (strlen(trim($arrVals['company_name'])) == 0) {
		$arrErr[] = form_error('Please enter the carrier name.', 'company_name', 'name');
	}

	if (strlen(trim($arrVals['company_contact'])) == 0) {
		$arrErr[] = form_error('Please enter the carrier contact name.', 'company_contact', 'contact name');
	}

	if (strlen(trim($arrVals['contact_email'])) == 0) {
		$arrErr[] = form_error('Please enter the carrier contact email.', 'contact_email', 'contact email');
	}

	if (strlen(trim($arrVals['lead_email_1'])) == 0) {
		$arrErr[] = form_error('Please enter the carrier lead email.', 'lead_email_1', 'lead email');
	}

	if (intval($arrVals['id']) == 0 && intval($arrVals['package_id']) == 0) {
		$arrErr[] = form_error('Please select a package for the new carrier.', 'package_id', 'package');
	}

	if (!intval($arrVals['fourty_eight']) && !intval($arrVals['alaska']) && !intval($arrVals['hawaii'])) {
		$arrErr[] = form_error('The carrier must service at least one of 48', 'fourty_eight', '48') . form_error(', Alaska', 'alaska') . form_error(' or Hawaii.', 'hawaii');
	}

	if (!intval($arrVals['open']) && !intval($arrVals['covered'])) {
		$arrErr[] = form_error('The carrier must have either open', 'open') . form_error(' or closed carrier service, or both.', 'closed');
	}
} //end validation

if ($arrVals['id'] > 0) {
	$sql = 'SELECT * FROM carriers WHERE id = ?';
	$rs = $db->SelectLimit($sql, 1, 0, array(intval($arrVals['id'])));

	if($gDebug) printvar($rs, 'rs');
	if (!$rs) {
		$arrErr[] = 'There was an error connecting to the database. Please try again.';
	} elseif ($rs->EOF) {
		$arrErr[] = 'The record is unavailable or you are not authorized to make changes to the record. Please return to the <a href="carriers.php">carriers list</a> and try again.';
	} //END: if (!$rs)

	if (getParam('frmSubmit') == 'true' && sizeof($arrErr) == 0) {
		//compare data (case-insensitive)
		$dataChanged = false;
		$changedFieldsArray = array();
		$updateSQL = '';
		$updateArr = array();
		foreach ($arrVars as $var) {
			if ($var[THE_VALUE] == 'id') continue;
			if ($var[FIELD_IS_ARRAY] == true && strcasecmp($rs->fields[$var[THE_VALUE]],join(ARRAY_GLUE,$arrVals[$var[THE_VALUE]])) != 0) {
				$dataChanged = true;
				$updateSQL .= (strlen($updateSQL) > 0) ? ', ' : ' SET ';
				$updateSQL .= $var[THE_VALUE].'=? ';
				$updateArr[] = getSQLValueString($arrVals[$var[THE_VALUE]], ((!in_array($var[THE_TYPE], array('long','int','double','float'))) ? 'raw' : $var[THE_TYPE]), $var[THE_DEFINED_VALUE], $var[THE_NOT_DEFINED_VALUE], ARRAY_GLUE, $var[ALLOW_NULL_VALUE]);
			} elseif ($var[FIELD_IS_ARRAY] != true && strcasecmp($rs->fields[$var[THE_VALUE]],$arrVals[$var[THE_VALUE]]) != 0) {
				$dataChanged = true;
				$updateSQL .= (strlen($updateSQL) > 0) ? ', ' : ' SET ';
				$updateSQL .= $var[THE_VALUE].'=? ';
				$updateArr[] = getSQLValueString($arrVals[$var[THE_VALUE]], ((!in_array($var[THE_TYPE], array('long','int','double','float'))) ? 'raw' : $var[THE_TYPE]), $var[THE_DEFINED_VALUE], $var[THE_NOT_DEFINED_VALUE], ARRAY_GLUE, $var[ALLOW_NULL_VALUE]);
			};
		} //END: foreach ($arrVars as $var)
		if($gDebug) printvar($changedFieldsArray, 'changedFieldsArray');
		if ($dataChanged) {
			$updateArr[] = $arrVals['id'];
			//data has changed in existing record, archive old version
			if (!$db->Execute('UPDATE carriers '.$updateSQL.' WHERE id = ?', $updateArr)) {
				$arrErr[] = 'An error occured while while updating your data. Please try again.';
			}
		}
		else {
			//no data has changed, so don't bother with the database operations
			//do nothing
		}

		if (sizeof($arrErr) == 0) {
			//redirect to next step
			redirect($_SERVER['PHP_SELF'], array('confirm'=>'edit'), $gDebug);
			exit();
		} //otherwise fall through to display errors
	} elseif (getParam('frmSubmit') != 'true') {
		//get existing values
		foreach ($arrVars as $var) {
			$arrVals[$var[THE_VALUE]] = getParamInArray($var[THE_VALUE], (bool) $var[FIELD_IS_ARRAY], "", $rs->fields);
		}
	} //END: if (getParam('frmSubmit') == 'true' && sizeof($arrErr) == 0)
} else {
	if (getParam('frmSubmit') == 'true' && sizeof($arrErr) == 0) {
		//insert new record
		$updateSQL['fields'] = "";
		$updateSQL['values'] = "";
		$newFieldsArray = array();
		foreach ($arrVars as $var) {
			if ($var[THE_VALUE] == 'id') continue;
			elseif ($var[THE_VALUE] == 'package_id') {
				$updateSQL['fields'] .= (strlen($updateSQL['fields']) > 0) ? ", " : "";
				$updateSQL['values'] .= (strlen($updateSQL['values']) > 0) ? ", " : "";
				$updateSQL['fields'] .= "`package_id`";
				$updateSQL['values'] .= getSQLValueString($arrVals[$var[THE_VALUE]], $var[THE_TYPE], $var[THE_DEFINED_VALUE], $var[THE_NOT_DEFINED_VALUE], ARRAY_GLUE, $var[ALLOW_NULL_VALUE]);
				$updateSQL['fields'] .= (strlen($updateSQL['fields']) > 0) ? ", " : "";
				$updateSQL['values'] .= (strlen($updateSQL['values']) > 0) ? ", " : "";
				$updateSQL['fields'] .= "`auto_leads_remaining`";
				$updateSQL['values'] .= package_leads($arrVals[$var[THE_VALUE]]);
			}
			else {
				$updateSQL['fields'] .= (strlen($updateSQL['fields']) > 0) ? ", " : "";
				$updateSQL['values'] .= (strlen($updateSQL['values']) > 0) ? ", " : "";
				$updateSQL['fields'] .= "`" . $var[THE_VALUE] . "`";
				$updateSQL['values'] .= getSQLValueString($arrVals[$var[THE_VALUE]], $var[THE_TYPE], $var[THE_DEFINED_VALUE], $var[THE_NOT_DEFINED_VALUE], ARRAY_GLUE, $var[ALLOW_NULL_VALUE]);
			}
		}

		if (!$db->Execute('INSERT INTO carriers ('.$updateSQL['fields'].', `purchase_date`, `updated_at`, `created_at`) VALUES ('.$updateSQL['values'].', NOW(), NOW(), NOW())')) {
			$arrErr[] = 'An error occured while while updating your data. Please try again.';
		} else {
			//redirect to next step
			redirect($_SERVER['PHP_SELF'], array('confirm'=>'add'), $gDebug);
			exit();
		}
	} elseif (getParam('frmSubmit') != 'true') {
		//set up for new record
		$arrVals['id'] = 0;
	} //END: if (getParam('frmSubmit') == 'true' && sizeof($arrErr) == 0))
} //END: if (getParam('frmSubmit') == 'true')
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<?php include(WEB_ROOT.PROJECT_DIR.'/_includes/default-page-content-headers.inc.php'); ?>
		<?php include(WEB_ROOT.PROJECT_DIR.'/_includes/default-page-cache.inc.php'); ?>

		<title><?php echo htmlentities(PROJECT_TITLE); ?>: Edit Carrier</title>

		<meta name="author" content="Chris Bloom" />
		<meta name="robots" content="noindex, nofollow, noarchive" />
		<meta name="MSSmartTagsPreventParsing" content="true" />
		<meta name="no-email-collection" content="http://www.unspam.com/noemailcollection/" />

		<meta name="DC.Creator" content="Chris Bloom" />
		<meta name="DC.Date" content="YYYY-MM-DD" />
		<meta name="DC.Format" content="text/html" />
		<meta name="DC.Language" content="en" />
		<meta name="DC.Title" content="Edit Carrier" />

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
						<?php if (getParam('confirm') == "edit") { ?>
						<h2>Edit Carrier</h2>
						<p>Your changes have been saved. <a href="carriers.php">Return to the carrier list.</a></p>
						<?php } elseif (getParam('confirm') == "add") { ?>
						<h2>Add carrier</h2>
						<p>The new record has been saved. <a href="carriers.php">Return to the carrier list.</a></p>
						<?php } else { ?>
						<?php if ($arrVals['id'] > 0) { ?>
						<h2>Edit Carrier</h2>
						<p>Use the form below to update the carrier info.</p>
						<?php } else { ?>
						<h2>Add Carrier</h2>
						<p>Use the form below to add a new carrier.</p>
						<?php } ?>
						<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
							<input name="id" type="hidden" id="id" value="<?php echo htmlspecialchars($arrVals['id']); ?>" />
							<div class="formElementSet formElementSetText">
								<label class="fieldlabel req" for="company_name">Carrier Name:</label>
								<input name="company_name" type="text" class="text" id="company_name" value="<?php echo htmlspecialchars($arrVals['company_name']); ?>" maxlength="25" />
							</div>
							<div class="formElementSet formElementSetText">
								<label class="fieldlabel req" for="company_contact">Contact Name:</label>
								<input name="company_contact" type="text" class="text" id="company_contact" value="<?php echo htmlspecialchars($arrVals['company_contact']); ?>" maxlength="25" />
							</div>
							<div class="formElementSet formElementSetText">
								<label class="fieldlabel" for="address_1">Address 1:</label>
								<input name="address_1" type="text" class="text" id="address_1" value="<?php echo htmlspecialchars($arrVals['address_1']); ?>" maxlength="255" />
							</div>
							<div class="formElementSet formElementSetText">
								<label class="fieldlabel" for="address_2">Address 2:</label>
								<input name="address_2" type="text" class="text" id="address_2" value="<?php echo htmlspecialchars($arrVals['address_2']); ?>" maxlength="255" />
							</div>
							<div class="formElementSet formElementSetText">
								<label class="fieldlabel" for="zip">Zip Code:</label>
								<input name="zip" type="text" class="text" id="zip" value="<?php echo htmlspecialchars($arrVals['zip']); ?>" maxlength="25" />
							</div>
							<div class="formElementSet formElementSetText">
								<label class="fieldlabel" for="city">City:</label>
								<input name="city" type="text" class="text" id="city" value="<?php echo htmlspecialchars($arrVals['city']); ?>" maxlength="100" />
							</div>
							<div class="formElementSet formElementSetText">
								<label class="fieldlabel" for="state">State:</label>
								<input name="state" type="text" class="text" id="state" value="<?php echo htmlspecialchars($arrVals['state']); ?>" maxlength="100" />
							</div>
							<div class="formElementSet formElementSetText">
								<label class="fieldlabel" for="phone">Phone:</label>
								<input name="phone" type="text" class="text" id="phone" value="<?php echo htmlspecialchars($arrVals['phone']); ?>" maxlength="10" />
							</div>
							<div class="formElementSet formElementSetText">
								<label class="fieldlabel" for="fax">Fax:</label>
								<input name="fax" type="text" class="text" id="fax" value="<?php echo htmlspecialchars($arrVals['fax']); ?>" maxlength="10" />
							</div>
							<div class="formElementSet formElementSetText">
								<label class="fieldlabel req" for="contact_email">Contact E-mail:</label>
								<input name="contact_email" type="text" class="text" id="contact_email" value="<?php echo htmlspecialchars($arrVals['contact_email']); ?>" maxlength="100" />
							</div>
							<div class="formElementSet formElementSetText">
								<label class="fieldlabel req" for="lead_email_1">Lead E-mail 1:</label>
								<input name="lead_email_1" type="text" class="text" id="lead_email_1" value="<?php echo htmlspecialchars($arrVals['lead_email_1']); ?>" maxlength="100" />
							</div>
							<div class="formElementSet formElementSetText">
								<label class="fieldlabel" for="lead_email_2">Lead E-mail 2:</label>
								<input name="lead_email_2" type="text" class="text" id="lead_email_2" value="<?php echo htmlspecialchars($arrVals['lead_email_2']); ?>" maxlength="100" />
							</div>
							<div class="formElementSet formElementSetText">
								<label class="fieldlabel req" for="email_format">Lead Format:</label>
								<select class="text" name="email_format" id="email_format">
									<option value="1" <?php echo (('1' == $arrVals['email_format']) ? 'selected="selected"' : ''); ?>>HTML E-mail</option>
									<option value="0" <?php echo (('0' == $arrVals['email_format']) ? 'selected="selected"' : ''); ?>>Text E-mail</option>
                  <option value="2" <?php echo (('2' == $arrVals['email_format']) ? 'selected="selected"' : ''); ?>>Granot XML</option>
								</select>
							</div>
							<div class="formElementSet formElementSetText">
								<label class="fieldlabel req" for="fourty_eight">Services to 48?:</label>
								<select class="text" name="fourty_eight" id="fourty_eight">
									<option value="1" <?php echo (('1' == $arrVals['fourty_eight']) ? 'selected="selected"' : ''); ?>>Yes</option>
									<option value="0" <?php echo (('0' == $arrVals['fourty_eight']) ? 'selected="selected"' : ''); ?>>No</option>
								</select>
							</div>
							<div class="formElementSet formElementSetText">
								<label class="fieldlabel req" for="alaska">Services to Alaska?:</label>
								<select class="text" name="alaska" id="alaska">
									<option value="1" <?php echo (('1' == $arrVals['alaska']) ? 'selected="selected"' : ''); ?>>Yes</option>
									<option value="0" <?php echo (('0' == $arrVals['alaska']) ? 'selected="selected"' : ''); ?>>No</option>
								</select>
							</div>
							<div class="formElementSet formElementSetText">
								<label class="fieldlabel req" for="hawaii">Services to Hawaii?:</label>
								<select class="text" name="hawaii" id="hawaii">
									<option value="1" <?php echo (('1' == $arrVals['hawaii']) ? 'selected="selected"' : ''); ?>>Yes</option>
									<option value="0" <?php echo (('0' == $arrVals['hawaii']) ? 'selected="selected"' : ''); ?>>No</option>
								</select>
							</div>
							<div class="formElementSet formElementSetText">
								<label class="fieldlabel req" for="dayparting">Service on Weekends?:</label>
								<select class="text" name="dayparting" id="dayparting">
									<option value="1" <?php echo (('1' == $arrVals['dayparting']) ? 'selected="selected"' : ''); ?>>Yes</option>
									<option value="0" <?php echo (('0' == $arrVals['dayparting']) ? 'selected="selected"' : ''); ?>>No</option>
								</select>
							</div>
							<div class="formElementSet formElementSetText">
								<label class="fieldlabel req" for="open">Open Carrier Service?:</label>
								<select class="text" name="open" id="open">
									<option value="1" <?php echo (('1' == $arrVals['open']) ? 'selected="selected"' : ''); ?>>Yes</option>
									<option value="0" <?php echo (('0' == $arrVals['open']) ? 'selected="selected"' : ''); ?>>No</option>
								</select>
							</div>
							<div class="formElementSet formElementSetText">
								<label class="fieldlabel req" for="covered">Enclosed Service?:</label>
								<select class="text" name="covered" id="covered">
									<option value="1" <?php echo (('1' == $arrVals['covered']) ? 'selected="selected"' : ''); ?>>Yes</option>
									<option value="0" <?php echo (('0' == $arrVals['covered']) ? 'selected="selected"' : ''); ?>>No</option>
								</select>
							</div>
							<div class="formElementSet formElementSetText">
								<label class="fieldlabel req" for="active">Active?:</label>
								<select class="text" name="active" id="active">
									<option value="1" <?php echo (('1' == $arrVals['active']) ? 'selected="selected"' : ''); ?>>Yes</option>
									<option value="0" <?php echo (('0' == $arrVals['active']) ? 'selected="selected"' : ''); ?>>No</option>
								</select>
							</div>
                            <div class="formElementSet formElementSetText">
								<label class="fieldlabel req" for="dist_type">Distribution?:</label>
								<select class="text" name="dist_type" id="dist_type">
									<option value="rotation" <?php echo (('rotation' == $arrVals['dist_type']) ? 'selected="selected"' : ''); ?>>Rotation</option>
									<option value="sendall" <?php echo (('sendall' == $arrVals['dist_type']) ? 'selected="selected"' : ''); ?>>Send All</option>
								</select>
							</div>

							<?php if (intval($arrVals['id']) == 0) { ?>
							<div class="formElementSet formElementSetText">
								<label class="fieldlabel req" for="package_id">Package:</label>
								<select class="text" name="package_id" id="package_id">
									<option value="0">Choose one</option>
									<?php
									$prs = $db->Execute('SELECT * FROM packages ORDER BY leads ASC');
									if ($prs) {
										while (!$prs->EOF) {
									?>
									<option value="<?php w((int) $prs->fields['id']); ?>" <?php echo (($prs->fields['id'] == $arrVals['package_id']) ? 'selected="selected"' : ''); ?>><?php w($prs->fields['leads']); ?> (<?php w($prs->fields['price']); ?>)</option>
									<?php
											$prs->MoveNext();
										}
									}
									?>
								</select>
							</div>
							<?php } else { ?>
							<div class="formElementSet formElementSetText">
								<div class="fieldnote">To modify a carrier's leads, use the Renew Leads link on the carrier list page.</div>
								<input type="hidden" name="package_id" value="<?php echo htmlspecialchars($arrVals['package_id']); ?>" />
							</div>
							<?php } ?>

							<div class="formElementSet formElementSetButton">
								<input style="color: #088C26; float: left; margin-left: 2em; margin-right: 2em;" type="submit" name="submit" id="submit" value="Save" />
								<script type="text/javascript" language="javascript">
								<!--
								document.write('<input style="color: #941000; margin-left: 2em; margin-right: 2em;" type="button" name="cancel" id="cancel" value="Cancel" onClick="document.location=\'carriers.php\'" />');
								//-->
								</script>
								<noscript>
									<a href="carriers.php" style="display: block; color: #941000 !important; padding: 1px 0.75em;">Cancel</a>
								</noscript>
								<input type="hidden" name="frmSubmit" value="true" />
								<?php
								$dataArray = array();
								if (!ARE_WE_LIVE) $dataArray['debug'] = $gDebug;
								writeHiddenFormFields($dataArray);
								?>
							</div>
						</form>
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
