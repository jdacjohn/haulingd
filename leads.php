<?php require_once('_includes/project.inc.php'); ?>
<?php require_once('_includes/stringSwapClass.inc.php'); ?>
<?php
require_login();

$db = &connectToDB();
if($gDebug) $db->debug = true;

//define variables for form data
$arrVarsLogin = array(
	//array(THE_VALUE, THE_TYPE, THE_DEFINED_VALUE, THE_NOT_DEFINED_VALUE, FIELD_IS_ARRAY, ALLOW_NULL_VALUE)
	newFieldArray('carrier_id', 'int'),
	newFieldArray('start_date', 'date'),
	newFieldArray('end_date', 'date'),
	newFieldArray('range', 'text')
);

//initialize values
if (!isset($arrVals)) $arrVals = array();
foreach ($arrVarsLogin as $var) {
	$arrVals[$var[THE_VALUE]] = getParam($var[THE_VALUE], (bool) $var[FIELD_IS_ARRAY]);
}
if($gDebug) printvar($arrVals, 'arrVals');

if (!isset($arrErr)) $arrErr = array();
$start_date = $end_date = false;
if (strlen(trim($arrVals['range']))) {
	switch (trim($arrVals['range'])) {
		case 'yest':
			$_start = mktime(0,0,0,date('m'),date('d')-1,date('Y'));
			$_end = mktime(0,0,0,date('m'),date('d')-1,date('Y'));
			$start_date = date('Y-m-d', $_start);
			$end_date = date('Y-m-d', $_end);
			$arrVals['start_date'] = '';
			$arrVals['end_date'] = '';
			break;
		case 'week':
			$_start = mktime(0,0,0,date('m'),date('d')-7,date('Y'));
			$_end = mktime(0,0,0,date('m'),date('d'),date('Y'));
			$start_date = date('Y-m-d', $_start);
			$end_date = date('Y-m-d', $_end);
			$arrVals['start_date'] = '';
			$arrVals['end_date'] = '';
			break;
		case 'this':
			$_start = mktime(0,0,0,date('m'),1,date('Y'));
			$_end = mktime(0,0,0,date('m'),date('d'),date('Y'));
			$start_date = date('Y-m-d', $_start);
			$end_date = date('Y-m-d', $_end);
			$arrVals['start_date'] = '';
			$arrVals['end_date'] = '';
			break;
		case 'last':
			$_start = mktime(0,0,0,date('m')-1,1,date('Y'));
			$_end = mktime(0,0,0,date('m'),0,date('Y'));
			$start_date = date('Y-m-d', $_start);
			$end_date = date('Y-m-d', $_end);
			$arrVals['start_date'] = '';
			$arrVals['end_date'] = '';
			break;
	}
}
else {
	if (strlen(trim($arrVals['start_date'])) && strlen(trim($arrVals['end_date']))) {
		if (
			!strtotime(trim($arrVals['start_date'])) > 0
			||
			!strtotime(trim($arrVals['end_date'])) > 0
			||
			strtotime(trim($arrVals['start_date'])) > strtotime(trim($arrVals['end_date']))
		) {
			$arrErr[] = form_error('Please enter a valid end date that is later than the start date. The format should be in <em>mm/dd/yyyy</em> format.', 'end_date', 'end date');
		}
		else {
			$start_date = date('Y-m-d', strtotime(trim($arrVals['start_date'])));
			$end_date = date('Y-m-d', strtotime(trim($arrVals['end_date'])));
		}
	}
	elseif (strlen(trim($arrVals['start_date']))) {
		if (!strtotime(trim($arrVals['start_date'])) > 0) {
			$arrErr[] = form_error('Please enter a valid start date. The format should be in <em>mm/dd/yyyy</em> format.', 'start_date', 'start date');
		}
		else {
			$start_date = date('Y-m-d', strtotime(trim($arrVals['start_date'])));
		}
	}
	elseif (strlen(trim($arrVals['end_date']))) {
		if (!strtotime(trim($arrVals['end_date'])) > 0) {
			$arrErr[] = form_error('Please enter a valid end date. The format should be in <em>mm/dd/yyyy</em> format.', 'end_date', 'end date');
		}
		else {
			$end_date = date('Y-m-d', strtotime(trim($arrVals['end_date'])));
		}
	}
}

if ($gDebug) printvar($start_date, 'start date');
if ($gDebug) printvar($end_date, 'end date');

$sql = 'SELECT l.* FROM leads l';

$search = array();
$params = array();
if (intval($arrVals['carrier_id']) > 0) {
	$search[] = 'carrier_id = ?';
	$params[] = intval($arrVals['carrier_id']);
	$sql .= ' LEFT JOIN carriers_leads cl ON l.id = cl.lead_id';
}

if ($start_date && $end_date) {
	$search[] = 'UNIX_TIMESTAMP(l.created_at) >= UNIX_TIMESTAMP(?) AND UNIX_TIMESTAMP(l.created_at) <= UNIX_TIMESTAMP(?)';
	$params[] = $start_date.' 00:00:00';
	$params[] = $end_date.' 23:59:59';
}
elseif ($start_date) {
	$search[] = 'UNIX_TIMESTAMP(l.created_at) >= UNIX_TIMESTAMP(?)';
	$params[] = $start_date.' 00:00:00';
}
elseif ($end_date) {
	$search[] = 'UNIX_TIMESTAMP(l.created_at) <= UNIX_TIMESTAMP(?)';
	$params[] = $end_date.' 23:59:59';
}

if (sizeof($search)) {
	$sql .= ' WHERE (' . implode(') AND (', $search) . ')';
}

$sql .= ' ORDER BY created_at DESC';

if ($gDebug) printvar($sql, 'sql');

$page_size = 30;
$total_pages = intval(param('total_pages'));
if ($gDebug) printvar(intval(param('total_pages')), "intval(param('total_pages'))");
if (!$total_pages) {
	$rs = $db->Execute($sql, $params);
	if (!$rs || $rs->EOF)
		$total_records = -1;
	else
		$total_records = intval($rs->RecordCount());
	if ($gDebug) printvar($total_records, '$total_records');
	$total_pages = ceil($total_records / $page_size);
}
$page = intval(param('page'));
if ($gDebug) printvar(intval(param('page')), "intval(param('page'))");
if ($page <= 0)
	$page = 1;
elseif ($page > $total_pages)
	$page = $total_pages;

$next_page = ($page == $total_pages) ? false : $page+1;
$prev_page = ($page == 1) ? false : $page-1;

if ($gDebug) printvar($total_pages, '$total_pages');
if ($gDebug) printvar($page, '$page');
if ($gDebug) printvar($next_page, '$next_page');
if ($gDebug) printvar($prev_page, '$prev_page');

$rs = $db->SelectLimit($sql, $page_size, $page_size*($page-1), $params);
if (!$rs || $rs->EOF)
	$total_records = $total_pages = $page = $next_page = $prev_page = 0;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<?php include(WEB_ROOT.PROJECT_DIR.'/_includes/default-page-content-headers.inc.php'); ?>
		<?php include(WEB_ROOT.PROJECT_DIR.'/_includes/default-page-cache.inc.php'); ?>

		<title><?php echo htmlentities(PROJECT_TITLE); ?>: Leads</title>

		<meta name="author" content="Chris Bloom" />
		<meta name="robots" content="noindex, nofollow, noarchive" />
		<meta name="MSSmartTagsPreventParsing" content="true" />
		<meta name="no-email-collection" content="http://www.unspam.com/noemailcollection/" />

		<meta name="DC.Creator" content="Chris Bloom" />
		<meta name="DC.Date" content="YYYY-MM-DD" />
		<meta name="DC.Format" content="text/html" />
		<meta name="DC.Language" content="en" />
		<meta name="DC.Title" content="Leads" />

		<meta name="Description" content="Information about this web page" />
		<meta name="Keywords" content="keyword1, keyword2, keyword3" />

		<meta name="geo.region" content="" />
		<meta name="geo.placename" content="" />

		<link rel="start" href="<?php echo htmlentities(PROJECT_URL); ?>/" title="Home" />

		<?php include(WEB_ROOT.PROJECT_DIR.'/_includes/default-page-style.inc.php'); ?>
		<?php include(WEB_ROOT.PROJECT_DIR.'/_includes/default-page-script.inc.php'); ?>

		<script type="text/javascript" src="scripts/jscalendar-1.0/calendar.js"></script>
		<script type="text/javascript" src="scripts/jscalendar-1.0/lang/calendar-en.js"></script>
		<script type="text/javascript" src="scripts/jscalendar-1.0/calendar-setup.js"></script>
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

		var start_date         = null;
		var start_date_trigger = null;
		var end_date           = null;
		var end_date_trigger   = null;
		var range              = null;

		function initDateSelectors() {
			start_date = document.getElementById('start_date');
			end_date   = document.getElementById('end_date');
			range      = document.getElementById('range');

			if (
				!start_date ||
				!end_date
			) {
				return;
			}

			//set up the triggers for the calendar buttons
			start_date_trigger = document.createElement('img');
			start_date_trigger.setAttribute('id', 'start_date_trigger');
			setCSS(start_date_trigger, 'vertical-align: middle; border-style: none; margin: 0 1em 0 0;');
			start_date_trigger.setAttribute('src', 'scripts/jscalendar-1.0/calendar-icon-03.gif');
			start_date_trigger.setAttribute('width', '30');
			start_date_trigger.setAttribute('height', '19');
			start_date.parentNode.appendChild(start_date_trigger);

			end_date_trigger = start_date_trigger.cloneNode(false);
			end_date_trigger.setAttribute('id', 'end_date_trigger');
			end_date.parentNode.appendChild(end_date_trigger);

			//create calendar widgets, triggered by new image elements and bound to date fields
			setupCalendar("start_date", "start_date_trigger", resetRange, null);
			setupCalendar("end_date",   "end_date_trigger",   resetRange, toggleDateAvailability);

			addEvent(range, 'change', resetDates);
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

		function resetRange() {
			range.selectedIndex = -1;
		}

		function resetDates() {
			start_date.value = '';
			end_date.value = '';
		}

		function toggleDateAvailability(date) {
			date.setHours(0, 0, 0, 0);

			var compDate = new Date(Date.parse(start_date.value));
			compDate.setHours(0, 0, 0, 0);
			//alert(date.getTime() + ' :: ' + compDate.getTime());

			if (date.getTime() < compDate.getTime()) {
				return true; // disable earlier dates
			}
			else {
				return false; // enable other dates
			}
		}
		</script>
		<style type="text/css">@import url(scripts/jscalendar-1.0/calendar-win2k-1.css);</style>
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
						<h2>Leads</h2>

						<div id="search_opts">
							<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="get" style="margin-bottom: 1em">
								<div class="formElementSet formElementSetText">
									<label for="start_date" class="fieldlabel">Start Date:</label>
									<input name="start_date" type="text" class="text" id="start_date" value="<?php echo htmlspecialchars($arrVals['start_date']); ?>" maxlength="10" />
								</div>
								<div class="formElementSet formElementSetText">
									<label for="end_date" class="fieldlabel">End Date:</label>
									<input name="end_date" type="text" class="text" id="end_date" value="<?php echo htmlspecialchars($arrVals['end_date']); ?>" maxlength="10" />
								</div>
								<div class="formElementSet formElementSetText">
									<label class="fieldlabel" for="carrier_id">Show only leads from:</label>
									<select class="text" name="carrier_id" id="carrier_id">
										<option value=""></option>
										<?php
										$prs = $db->Execute('SELECT id, company_name FROM carriers ORDER BY company_name ASC');
										if ($prs) {
											while (!$prs->EOF) {
										?>
										<option value="<?php w((int) $prs->fields['id']); ?>" <?php echo (($prs->fields['id'] == $arrVals['carrier_id']) ? 'selected="selected"' : ''); ?>><?php w($prs->fields['company_name']); ?></option>
										<?php
												$prs->MoveNext();
											}
										}
										?>
									</select>
								</div>
								<div class="formElementSet formElementSetText">
									<label class="fieldlabel" for="carrier_id">Select Date Range:</label>
									<select class="text" name="range" id="range">
										<option value=""></option>
										<option value="yest" <?php echo (('yest' == $arrVals['range']) ? 'selected="selected"' : ''); ?>>Yesterday</option>
										<option value="week" <?php echo (('week' == $arrVals['range']) ? 'selected="selected"' : ''); ?>>Last 7 Days</option>
										<option value="this" <?php echo (('this' == $arrVals['range']) ? 'selected="selected"' : ''); ?>>This month to date</option>
										<option value="last" <?php echo (('last' == $arrVals['range']) ? 'selected="selected"' : ''); ?>>Last Month</option>
									</select>
								</div>
								<div class="formElementSet formElementSetButton">
									<input style="color: #088C26; float: left; margin-left: 2em; margin-right: 2em;" type="submit" name="submit" id="submit" value="Search" />
									<script type="text/javascript" language="javascript">
									<!--
									document.write('<input style="color: #941000; margin-left: 2em; margin-right: 2em;" type="button" name="cancel" id="cancel" value="Clear Search" onClick="document.location=\'leads.php\'" />');
									//-->
									</script>
									<noscript>
										<a href="leads.php" style="display: block; color: #941000 !important; padding: 1px 0.75em;">Clear Search</a>
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

						<?php if ($rs && !$rs->EOF) { ?>
						<table class="dataSet">
							<tr class="recordsetPaging">
								<td colspan="5">
									<?php
									echo (($prev_page) ? '<span class="recordsetPagingComponent"><a href="'.addURLParam('page', NULL, 'total_pages', $total_pages).'" title="Go to first page of results">&laquo;</a></span>' : '<span class="recordsetPagingComponent dim"><span>&laquo;</span></span>');

									echo (($prev_page) ? '<span class="recordsetPagingComponent"><a href="'.addURLParam('page', $prev_page, 'total_pages', $total_pages).'" title="Go to previous page of results">&lsaquo;</a></span>' : '<span class="recordsetPagingComponent dim"><span>&lsaquo;</span></span>');

									echo "<span class=\"recordsetPagingComponent dim\">Page $page of $total_pages</span>";

									echo (($next_page) ? '<span class="recordsetPagingComponent"><a href="'.addURLParam('page', $next_page, 'total_pages', $total_pages).'" title="Go to next page of results">&rsaquo;</a></span>' : '<span class="recordsetPagingComponent dim"><span>&rsaquo;</span></span>');

									echo (($next_page) ? '<span class="recordsetPagingComponent"><a href="'.addURLParam('page', $total_pages, 'total_pages', $total_pages).'" title="Go to last page of results">&raquo;</a></span>' : '<span class="recordsetPagingComponent dim"><span>&raquo;</span></span>');
									?>
								</td>
							</tr>
							<tr class="headerRow">
								<th valign="bottom">Name</th>
								<th valign="bottom">E-mail</th>
								<th valign="bottom">Phone</th>
								<th valign="bottom" align="center">Submitted</th>
								<th valign="bottom" align="center">Details</th>
							</tr>
							<?php
							$SS = new stringSwap();        //Create a new stringSwap object
							$SS->defaultString("#FFFFFF"); //Set your default string
							$SS->swapString("#E7E7E7");    //Set your alternate string
							$SS->reset();                  //Reset the strings so that the first call to swap returns the default string

							while (!$rs->EOF) {
							?>
							<tr bgcolor="<?php echo $SS->swap(); ?>">
								<td valign="top"><a href="lead_details.php?id=<?php w((int) $rs->fields['id']); ?>" title="View Lead Details"><?php w($rs->fields['customername']); ?></a></td>
								<td valign="top" align="right"><?php w($rs->fields['email']); ?></td>
								<td valign="top" align="right"><?php w($rs->fields['phone']); ?></td>
								<td valign="top" align="center"><?php w(myts_date($rs->fields['created_at'], 'Y-m-d')); ?></td>
								<td valign="top" align="center" nowrap="nowrap"><a href="lead_details.php?id=<?php w((int) $rs->fields['id']); ?>" id="leads_d_<?php w((int) $rs->fields['id']); ?>_link" title="View Lead Details">Details</a></td>
							</tr>
							<?php
								$rs->MoveNext();
							}
							?>
							<tr class="recordsetPaging">
								<td colspan="5">
									<?php
									echo (($prev_page) ? '<span class="recordsetPagingComponent"><a href="'.addURLParam('page', NULL, 'total_pages', $total_pages).'" title="Go to first page of results">&laquo;</a></span>' : '<span class="recordsetPagingComponent dim"><span>&laquo;</span></span>');

									echo (($prev_page) ? '<span class="recordsetPagingComponent"><a href="'.addURLParam('page', $prev_page, 'total_pages', $total_pages).'" title="Go to previous page of results">&lsaquo;</a></span>' : '<span class="recordsetPagingComponent dim"><span>&lsaquo;</span></span>');

									echo "<span class=\"recordsetPagingComponent dim\">Page $page of $total_pages</span>";

									echo (($next_page) ? '<span class="recordsetPagingComponent"><a href="'.addURLParam('page', $next_page, 'total_pages', $total_pages).'" title="Go to next page of results">&rsaquo;</a></span>' : '<span class="recordsetPagingComponent dim"><span>&rsaquo;</span></span>');

									echo (($next_page) ? '<span class="recordsetPagingComponent"><a href="'.addURLParam('page', $total_pages, 'total_pages', $total_pages).'" title="Go to last page of results">&raquo;</a></span>' : '<span class="recordsetPagingComponent dim"><span>&raquo;</span></span>');
									?>
								</td>
							</tr>
						</table>
						<?php }	else { ?>
						<p class="error">No leads matched your request.</p>
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
