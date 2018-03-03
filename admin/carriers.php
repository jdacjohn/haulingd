<?php require_once('../_includes/project.inc.php'); ?>
<?php require_once('../_includes/stringSwapClass.inc.php'); ?>
<?php
require_login();

$db = &connectToDB();
if($gDebug) $db->debug = true;

//define variables for form data
$arrVarsLogin = array(
	//array(THE_VALUE, THE_TYPE, THE_DEFINED_VALUE, THE_NOT_DEFINED_VALUE, FIELD_IS_ARRAY, ALLOW_NULL_VALUE)
	newFieldArray('id', 'int'),
	newFieldArray('formSubmit', 'text')
);

//initialize values
if (!isset($arrVals)) $arrVals = array();
foreach ($arrVarsLogin as $var) {
	$arrVals[$var[THE_VALUE]] = getParam($var[THE_VALUE], (bool) $var[FIELD_IS_ARRAY]);
}
if($gDebug) printvar($arrVals, 'arrVals');

if (!isset($arrErr)) $arrErr = array();
$sort_get = param('sort');
if (param('sort') == '') {
	$sort_get='1';
}
if (param('ac') == '') {
	$ascend='ASC';
}
else {
	$ascend = param('ac');
}

$filter = (param('filter') == 'renewals') ? ' WHERE auto_leads_remaining <= (p.leads * 0.1) ' : '';

$sql = 'SELECT c.*, p.leads, p.price, (p.leads * 0.1) as padding, (`auto_leads_remaining` * (p.price / p.leads)) as bank FROM carriers c LEFT JOIN packages p ON c.package_id = p.id '.$filter.'ORDER BY';

if ($ascend=='ASC') $asce='DESC';
else $asce='ASC';

if ($sort_get == '3') $sql .= "`company_name` $asce";
elseif ($sort_get == '4') $sql .= "`leads` $asce";
elseif ($sort_get == '5') $sql .= "`auto_leads_remaining` $asce";
elseif ($sort_get == '6') $sql .= "`purchase_date` $asce";
elseif ($sort_get == '7') $sql .= "`alaska` $asce";
elseif ($sort_get == '8') $sql .= "`hawaii` $asce";
elseif ($sort_get == '9') $sql .= "`active` $asce";
elseif ($sort_get == '10') $sql .= "`open` $asce";
elseif ($sort_get == '11') $sql .= "`covered` $asce";
elseif ($sort_get == '12') $sql .= "`fourty_eight` $asce";
elseif ($sort_get == '13') $sql .= "(`auto_leads_remaining` * (p.price / p.leads)) $asce";
else $sql .= "`id` $asce";

if ($gDebug) printvar($sql, 'sql');

$page_size = 30;
$total_pages = intval(param('total_pages'));
if ($gDebug) printvar(intval(param('total_pages')), "intval(param('total_pages'))");
if (!$total_pages) {
	$rs = $db->Execute($sql);
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

$rs = $db->SelectLimit($sql, $page_size, $page_size*($page-1));
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<?php include(WEB_ROOT.PROJECT_DIR.'/_includes/default-page-content-headers.inc.php'); ?>
		<?php include(WEB_ROOT.PROJECT_DIR.'/_includes/default-page-cache.inc.php'); ?>

		<title><?php echo htmlentities(PROJECT_TITLE); ?>: Carriers</title>

		<meta name="author" content="Chris Bloom" />
		<meta name="robots" content="noindex, nofollow, noarchive" />
		<meta name="MSSmartTagsPreventParsing" content="true" />
		<meta name="no-email-collection" content="http://www.unspam.com/noemailcollection/" />

		<meta name="DC.Creator" content="Chris Bloom" />
		<meta name="DC.Date" content="YYYY-MM-DD" />
		<meta name="DC.Format" content="text/html" />
		<meta name="DC.Language" content="en" />
		<meta name="DC.Title" content="Carriers" />

		<meta name="Description" content="Information about this web page" />
		<meta name="Keywords" content="keyword1, keyword2, keyword3" />

		<meta name="geo.region" content="" />
		<meta name="geo.placename" content="" />

		<link rel="start" href="<?php echo htmlentities(PROJECT_URL); ?>/" title="Home" />

		<?php include(WEB_ROOT.PROJECT_DIR.'/_includes/default-page-style.inc.php'); ?>
		<?php include(WEB_ROOT.PROJECT_DIR.'/_includes/default-page-script.inc.php'); ?>

		<!--[if lt IE 7]>
		<style type="text/css" media="all">
			table.dataSet tr.recordsetPaging {
				background-color: #dcdcdc;
			}
			table.dataSet tr.recordsetPaging .recordsetPagingComponent span {
				border: 1px solid #dcdcdc;
			}
		</style>
		<![endif]-->

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
						<h2>Carriers</h2>

						<?php if (param('filter') == 'renewals') { ?>
							<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="get" style="margin-bottom: 1em">
								<div class="formElementSet formElementSetButton">
									<label class="req">A filter has been applied to the recordset to show only those carriers in need of renewal.</label>
									<script type="text/javascript" language="javascript">
									<!--
									document.write('<input style="color: #941000; margin-left: 0; margin-right: 0" type="button" name="cancel" id="cancel" value="Clear Filter" onClick="document.location=\'carriers.php\'" />');
									//-->
									</script>
									<noscript>
										<a href="carriers.php" style="display: block; color: #941000 !important; padding: 1px 0.75em;">Clear Filter</a>
									</noscript>
									<?php
									$dataArray = array();
									if (!ARE_WE_LIVE) $dataArray['debug'] = $gDebug;
									writeHiddenFormFields($dataArray);
									?>
								</div>
							</form>
						<?php } ?>

						<?php if ($rs && !$rs->EOF) { ?>
						<table class="dataSet">
							<tr class="recordsetPaging">
								<td colspan="14">
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
								<th valign="bottom"><a href="<?php w(addURLParam('page', NULL, "sort", 3, "ac", $asce)); ?>">Name</a></th>
								<th valign="bottom" align="center"><a href="<?php w(addURLParam('page', NULL, "sort", 4, "ac", $asce)); ?>" title="The currently assigned package/level">Package</a></th>
								<th valign="bottom" align="center"><a href="<?php w(addURLParam('page', NULL, "sort", 5, "ac", $asce)); ?>" title="The number of leads available to this carrier">Remain</a></th>
								<th valign="bottom" align="center"><a href="<?php w(addURLParam('page', NULL, "sort", 6, "ac", $asce)); ?>" title="Date leads were last added to the carrier">Bought</a></th>
								<th valign="bottom" align="center"><a href="<?php w(addURLParam('page', NULL, "sort", 12, "ac", $asce)); ?>" title="Service to Lower 48?">48</a></th>
								<th valign="bottom" align="center"><a href="<?php w(addURLParam('page', NULL, "sort", 7, "ac", $asce)); ?>" title="Service to Alaska?">AK</a></th>
								<th valign="bottom" align="center"><a href="<?php w(addURLParam('page', NULL, "sort", 8, "ac", $asce)); ?>" title="Service to Hawaii?">HI</a></th>
								<th valign="bottom" align="center"><a href="<?php w(addURLParam('page', NULL, "sort", 13, "ac", $asce)); ?>" title="Service on Weekends?">W</a></th>
								<th valign="bottom" align="center"><a href="<?php w(addURLParam('page', NULL, "sort", 10, "ac", $asce)); ?>" title="Open Carrier Service?">Op</a></th>
								<th valign="bottom" align="center"><a href="<?php w(addURLParam('page', NULL, "sort", 11, "ac", $asce)); ?>" title="Closed Carrier Service?">Cl</a></th>
								<th valign="bottom" align="center"><a href="<?php w(addURLParam('page', NULL, "sort", 9, "ac", $asce)); ?>" title="Carrier Active in System?">A</a></th>
								<th valign="bottom" align="center" colspan="3">Actions</th>
							</tr>
							<?php
							$SS = new stringSwap();        //Create a new stringSwap object
							$SS->defaultString("#FFFFFF"); //Set your default string
							$SS->swapString("#E7E7E7");    //Set your alternate string
							$SS->reset();                  //Reset the strings so that the first call to swap returns the default string

							while (!$rs->EOF) {
							?>
							<tr bgcolor="<?php echo $SS->swap(); ?>">
								<td valign="top"><a href="leads.php?carrier_id=<?php w((int) $rs->fields['id']); ?>" title="View carrier leads"><?php w($rs->fields['company_name']); ?></a></td>
								<td valign="top" align="right"><?php w((int) $rs->fields['leads']); ?></td>
								<td valign="top" align="right" class="<?php echo (intval($rs->fields['auto_leads_remaining']) <= intval($rs->fields['padding'])) ? 'error' : ''; ?>"><?php w((int) $rs->fields['auto_leads_remaining']); ?></td>
								<td valign="top" align="center"><?php w(myts_date($rs->fields['purchase_date'], 'Y-m-d')); ?></td>
								<td valign="top" align="center"><?php w($rs->fields['fourty_eight']); ?></td>
								<td valign="top" align="center"><?php w($rs->fields['alaska']); ?></td>
								<td valign="top" align="center"><?php w($rs->fields['hawaii']); ?></td>
								<td valign="top" align="center"><?php w($rs->fields['dayparting']); ?></td>
								<td valign="top" align="center"><?php w($rs->fields['open']); ?></td>
								<td valign="top" align="center"><?php w($rs->fields['covered']); ?></td>
								<td valign="top" align="center"><?php w($rs->fields['active']); ?></td>
								<td valign="top" align="center" nowrap="nowrap">
									<form id="carrier_e_<?php echo intval($rs->fields['id']); ?>" action="carrier_edit.php" method="get">
										<input type="hidden" name="id" value="<?php echo intval($rs->fields['id']); ?>" id="carrier_e_<?php echo intval($rs->fields['id']); ?>_id" />
										<?php
										$dataArray = array();
										if (!ARE_WE_LIVE) $dataArray['debug'] = $gDebug;
										writeHiddenFormFields($dataArray, false, 'carrier_e_'.intval($rs->fields['id']).'_');
										?>
										<input type="submit" id="carrier_e_<?php echo intval($rs->fields['id']); ?>_submit" class="button" name="" value="Edit" title="Edit Carrier" />
									</form>
								</td>
								<td valign="top" align="center" nowrap="nowrap">
									<form id="carrier_r_<?php echo intval($rs->fields['id']); ?>" action="carrier_renew.php" method="get">
										<input type="hidden" name="id" value="<?php echo intval($rs->fields['id']); ?>" id="carrier_r_<?php echo intval($rs->fields['id']); ?>_id" />
										<?php
										$dataArray = array();
										if (!ARE_WE_LIVE) $dataArray['debug'] = $gDebug;
										writeHiddenFormFields($dataArray, false, 'carrier_r_'.intval($rs->fields['id']).'_');
										?>
										<input type="submit" id="carrier_r_<?php echo intval($rs->fields['id']); ?>_submit" class="button" name="" value="Renew" title="Renew Carrier" />
									</form>
								</td>
								<td valign="top" align="center" nowrap="nowrap">
									<form id="carrier_d_<?php echo intval($rs->fields['id']); ?>" action="carrier_delete.php" method="post">
										<input type="hidden" name="id" value="<?php echo intval($rs->fields['id']); ?>" id="carrier_d_<?php echo intval($rs->fields['id']); ?>_id" />
										<input type="hidden" name="action" value="delete" />
										<?php
										$dataArray = array();
										if (!ARE_WE_LIVE) $dataArray['debug'] = $gDebug;
										writeHiddenFormFields($dataArray, false, 'carrier_d_'.intval($rs->fields['id']).'_');
										?>
										<input type="submit" id="carrier_d_<?php echo intval($rs->fields['id']); ?>_submit" class="button" name="submit" value="Delete" title="Delete Carrier" />
									</form>
									<script type="text/javascript">
										addEvent(window, 'load', function () { textify('e',<?php echo intval($rs->fields['id']); ?>); } );
										addEvent(window, 'load', function () { textify('r',<?php echo intval($rs->fields['id']); ?>); } );
										addEvent(window, 'load', function () { textify('d',<?php echo intval($rs->fields['id']); ?>); } );
									</script>
								</td>
							</tr>
							<?php
								$rs->MoveNext();
							}
							?>
							<tr class="recordsetPaging">
								<td colspan="14">
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
						<script type="text/javascript">
							function textify(qualifier, i) {
								/*@cc_on
								/*@if (@_jscript_version <= 5.6) //IE6 supports JScript 5.6
								//I can't get this to work in IE6, not sure why and no use messing with it.
								return;
								/*@end
								@*/

								var f = document.getElementById('carrier_'+qualifier+'_'+i+'_submit');
								var p = f.parentNode;

								if (f && p) {
									var a = document.createElement('A');
									a.setAttribute('href', 'javascript:void(0)');
									a.setAttribute('title', f.getAttribute('title'));
									a.appendChild(document.createTextNode(f.getAttribute('value')));
									addEvent(a, 'click', function() { document.getElementById('carrier_'+qualifier+'_'+i).submit(); } );
									p.removeChild(f);
									p.appendChild(a);
								}
							}
						</script>
						<?php } else { ?>
						<p class="error">No carriers matched your request.</p>
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
