<?php require_once('../_includes/project.inc.php'); ?>
<?php
require_login();

$db = &connectToDB();
if($gDebug) $db->debug = true;

//define variables for form data
$arrVars = array(
	//array(THE_VALUE, THE_TYPE, THE_DEFINED_VALUE, THE_NOT_DEFINED_VALUE, FIELD_IS_ARRAY, ALLOW_NULL_VALUE)
	newFieldArray('id', 'int'),
	newFieldArray('package_id', 'int')
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
	if (intval($arrVals['id']) == 0 && intval($arrVals['package_id']) == 0) {
		$arrErr[] = form_error('Please select a package for the carrier.', 'package_id', 'package');
	}
} //end validation

$rs = false;
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
		$leads = intval(package_leads($arrVals['package_id']));
		if ($gDebug) printvar($leads, '$leads');

		$sql = "UPDATE carriers SET ";
		if (isset($_POST['action_increase'])) {
			$sql .= "auto_leads_remaining=(auto_leads_remaining+$leads), purchase_date=NOW(), reminder=0, ";
		}
		elseif (isset($_POST['action_increment'])) {
			$sql .= "auto_leads_remaining=(auto_leads_remaining + 1), ";
		}
		elseif (isset($_POST['action_decrease'])) {
			if ($rs->fields['auto_leads_remaining'] - $leads > 0) {
				$sql .= "auto_leads_remaining=(auto_leads_remaining-$leads), ";
			}
			else {
				$sql .= "auto_leads_remaining=0, ";
			}
			$sql .= "purchase_date=NOW(), reminder=0, ";
		}
		elseif (isset($_POST['action_decrement'])) {
			if ($rs->fields['auto_leads_remaining'] - 1 > 0) {
				$sql .= "auto_leads_remaining=(auto_leads_remaining - 1), ";
			}
			else {
				$sql .= "auto_leads_remaining=0, ";
			}
		}

		$sql .= "package_id = ? WHERE id = ?";

		if ($gDebug) printvar($sql, '$sql');

		if ($db->Execute($sql, array(intval($arrVals['package_id']), intval($arrVals['id'])))) {
			//redirect to next step
			redirect($_SERVER['PHP_SELF'], array('confirm'=>'renew'), $gDebug);
			exit();
		}
		else {
			$arrErr[] = 'There was an error saving the data. Please try again.';
		} //ofall through to display errors
	} //END: if (getParam('frmSubmit') == 'true' && sizeof($arrErr) == 0)
} elseif (!getParam('confirm')) {
	$arrErr[] = 'The record is unavailable or you are not authorized to make changes to the record. Please return to the <a href="carriers.php">carriers list</a> and try again.';
} //END: if (getParam('frmSubmit') == 'true')
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<?php include(WEB_ROOT.PROJECT_DIR.'/_includes/default-page-content-headers.inc.php'); ?>
		<?php include(WEB_ROOT.PROJECT_DIR.'/_includes/default-page-cache.inc.php'); ?>

		<title><?php echo htmlentities(PROJECT_TITLE); ?>: Renew Carrier</title>

		<meta name="author" content="Chris Bloom" />
		<meta name="robots" content="noindex, nofollow, noarchive" />
		<meta name="MSSmartTagsPreventParsing" content="true" />
		<meta name="no-email-collection" content="http://www.unspam.com/noemailcollection/" />

		<meta name="DC.Creator" content="Chris Bloom" />
		<meta name="DC.Date" content="YYYY-MM-DD" />
		<meta name="DC.Format" content="text/html" />
		<meta name="DC.Language" content="en" />
		<meta name="DC.Title" content="Renew Carrier" />

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
						<h2>Renew Carrier</h2>
						<?php if (getParam('confirm') == "renew") { ?>
						<p>Your changes have been saved. <a href="carriers.php">Return to the carrier list.</a></p>
						<?php } elseif ($rs && !$rs->EOF) { ?>
						<p>Use the form below to adjust this carrier's leads.</p>
						<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
							<input name="id" type="hidden" id="id" value="<?php echo htmlspecialchars($arrVals['id']); ?>" />
							<div class="formElementSet formElementSetText">
								<label class="fieldlabel">Carrier Name:</label>
								<span class="text"><?php echo htmlspecialchars($rs->fields['company_name']); ?></span>
							</div>
							<div class="formElementSet formElementSetText">
								<label class="fieldlabel">Leads Remaining:</label>
								<span class="text"><?php echo htmlspecialchars($rs->fields['auto_leads_remaining']); ?></span>
							</div>
							<div class="formElementSet formElementSetText">
								<label class="fieldlabel">Last Purchase Date:</label>
								<span class="text"><?php echo htmlspecialchars(myts_date($rs->fields['purchase_date'], 'Y-m-d')); ?></span>
							</div>
							<div class="formElementSet formElementSetText">
								<label class="fieldlabel">Reminder Sent?:</label>
								<span class="text"><?php echo htmlspecialchars(($rs->fields['reminder']) ? 'Yes' : 'No'); ?></span>
							</div>
							<div class="formElementSet formElementSetText">
								<label class="fieldlabel req" for="package_id">Package:</label>
								<select class="text" name="package_id" id="package_id">
									<option value="0">Choose one</option>
									<?php
									$prs = $db->Execute('SELECT * FROM packages ORDER BY leads ASC');
									if ($prs) {
										while (!$prs->EOF) {
									?>
									<option value="<?php w((int) $prs->fields['id']); ?>" <?php echo (($prs->fields['id'] == $rs->fields['package_id']) ? 'selected="selected"' : ''); ?>><?php w($prs->fields['leads']); ?> (<?php w($prs->fields['price']); ?>)</option>
									<?php
											$prs->MoveNext();
										}
									}
									?>
								</select>
							</div>
							<div class="formElementSet formElementSetButton">
								<input style="color: #088C26; margin-left: 2em; margin-right: 2em;" type="submit" name="action_increment" value="+ Increase Leads By One (1)" />
								<input style="color: #088C26; margin-left: 2em; margin-right: 2em;" type="submit" name="action_increase" value="+ + Increase Leads By Package Size" />
								<input style="color: #088C26; margin-left: 2em; margin-right: 2em;" type="submit" name="action_decrease" value="- - Decrease Leads By Package Size" />
								<input style="color: #088C26; margin-left: 2em; margin-right: 2em;" type="submit" name="action_decrement" value="- Decrease Leads By One (1)" />
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
							<div class="fieldnote">Note: Modifying the leads (up or down) will reset the Purchase Date and the Reminder flag.</div>
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
