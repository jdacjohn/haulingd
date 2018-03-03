<?php require_once('../_includes/project.inc.php'); ?>
<?php
require_login();

$db = &connectToDB();
if($gDebug) $db->debug = true;

//define variables for form data
$arrVars = array(
	//array(THE_VALUE, THE_TYPE, THE_DEFINED_VALUE, THE_NOT_DEFINED_VALUE, FIELD_IS_ARRAY, ALLOW_NULL_VALUE)
	newFieldArray('id', 'int'),
);

//initialize values
if (!isset($arrVals)) $arrVals = array();
foreach ($arrVars as $var) {
	$arrVals[$var[THE_VALUE]] = getParam($var[THE_VALUE], (bool) $var[FIELD_IS_ARRAY]);
}
if($gDebug) printvar($arrVals, 'arrVals');

if (!isset($arrErr)) $arrErr = array();
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
		if ($db->Execute('DELETE FROM carriers WHERE id = ?', array(intval($arrVals['id']))) && $db->Execute('DELETE FROM carriers_leads WHERE carrier_id = ?', array(intval($arrVals['id'])))) {
			//redirect to next step
			redirect($_SERVER['PHP_SELF'], array('confirm'=>'delete'), $gDebug);
			exit();
		}
		else {
			$arrErr[] = 'There was an error removing the data. Please try again.';
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

		<title><?php echo htmlentities(PROJECT_TITLE); ?>: Delete Carrier</title>

		<meta name="author" content="Chris Bloom" />
		<meta name="robots" content="noindex, nofollow, noarchive" />
		<meta name="MSSmartTagsPreventParsing" content="true" />
		<meta name="no-email-collection" content="http://www.unspam.com/noemailcollection/" />

		<meta name="DC.Creator" content="Chris Bloom" />
		<meta name="DC.Date" content="YYYY-MM-DD" />
		<meta name="DC.Format" content="text/html" />
		<meta name="DC.Language" content="en" />
		<meta name="DC.Title" content="Delete Carrier" />

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
						<h2>Delete Carrier</h2>
						<?php if (getParam('confirm') == "delete") { ?>
						<p>The carrier has been deleted. <a href="carriers.php">Return to the carrier list.</a></p>
						<?php } elseif ($rs && !$rs->EOF) { ?>
						<p>Are you sure you want to <strong>delete</strong> this carrier? Carriers can be deactivated instead using the Edit Carrier link on the carriers list.</p>
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
							<div class="formElementSet formElementSetButton">
								<input style="color: #088C26; float: left; margin-left: 2em; margin-right: 2em;" type="submit" name="submit" value="Delete Carrier" />
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
							<div class="fieldnote">Note: Deleting this carrier will also delete all records of any leads sent to it.</div>
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
