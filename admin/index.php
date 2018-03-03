<?php require_once('../_includes/project.inc.php'); ?>
<?php
require_login();

$db = &connectToDB();
if($gDebug) $db->debug = true;

$rs = $db->Execute('SELECT COUNT(l.id) as count FROM leads l WHERE UNIX_TIMESTAMP(l.created_at) >= UNIX_TIMESTAMP(?) AND UNIX_TIMESTAMP(l.created_at) <= UNIX_TIMESTAMP(?)', array(date('Y-m-d 00:00:00'), date('Y-m-d 23:59:59')));
if ($rs && !$rs->EOF)
	$todays_leads = $rs->fields['count'];
else
	$todays_leads = 0;

$rs = $db->Execute('SELECT COUNT(l.id) as count FROM leads l WHERE UNIX_TIMESTAMP(l.created_at) >= UNIX_TIMESTAMP(?) AND UNIX_TIMESTAMP(l.created_at) <= UNIX_TIMESTAMP(?)', array(date('Y-m-d 00:00:00', mktime(0,0,0,date('m'),date('d')-30,date('Y'))), date('Y-m-d 23:59:59')));
if ($rs && !$rs->EOF)
	$last_30_days = $rs->fields['count'];
else
	$last_30_days = 0;

$rs = $db->Execute('SELECT COUNT(c.id) as count FROM carriers c LEFT JOIN packages p ON c.package_id = p.id WHERE auto_leads_remaining <= (p.leads * 0.1) and c.active = 1');
if ($rs && !$rs->EOF)
	$buy_more = $rs->fields['count'];
else
	$buy_more = 0;
	
$rs = $db->Execute('SELECT COUNT(l.id) as count FROM leads l RIGHT JOIN quotes q ON l.id = q.lead_id WHERE authenticated = 1 AND UNIX_TIMESTAMP(l.created_at) >= UNIX_TIMESTAMP(?) AND UNIX_TIMESTAMP(l.created_at) <= UNIX_TIMESTAMP(?)', array(date('Y-m-d 00:00:00'), date('Y-m-d 23:59:59')));
if ($rs && !$rs->EOF)
	$from_public = $rs->fields['count'];
else
	$from_public = 0;	
	
$rs = $db->Execute('SELECT COUNT(l.id) as count FROM leads l RIGHT JOIN quotes q ON l.id = q.lead_id WHERE authenticated = 0 AND UNIX_TIMESTAMP(l.created_at) >= UNIX_TIMESTAMP(?) AND UNIX_TIMESTAMP(l.created_at) <= UNIX_TIMESTAMP(?)', array(date('Y-m-d 00:00:00'), date('Y-m-d 23:59:59')));
if ($rs && !$rs->EOF)
	$from_public_not_auth = $rs->fields['count'];
else
	$from_public_not_auth = 0;		
	
$rs = $db->Execute('SELECT COUNT(l.id) as count FROM leads l RIGHT JOIN quotes q ON l.id = q.lead_id WHERE authenticated = 1 AND UNIX_TIMESTAMP(l.created_at) >= UNIX_TIMESTAMP(?) AND UNIX_TIMESTAMP(l.created_at) <= UNIX_TIMESTAMP(?)', array(date('Y-m-d 00:00:00', mktime(0,0,0,date('m'),date('d')-30,date('Y'))), date('Y-m-d 23:59:59')));
if ($rs && !$rs->EOF)
	$from_public_30_days = $rs->fields['count'];
else
	$from_public_30_days = 0;
	
$rs = $db->Execute('SELECT COUNT(l.id) as count FROM leads l RIGHT JOIN quotes q ON l.id = q.lead_id WHERE authenticated = 0 AND UNIX_TIMESTAMP(l.created_at) >= UNIX_TIMESTAMP(?) AND UNIX_TIMESTAMP(l.created_at) <= UNIX_TIMESTAMP(?)', array(date('Y-m-d 00:00:00', mktime(0,0,0,date('m'),date('d')-30,date('Y'))), date('Y-m-d 23:59:59')));
if ($rs && !$rs->EOF)
	$from_public_30_days_not_auth = $rs->fields['count'];
else
	$from_public_30_days_not_auth = 0;

$saved = 0;
if(isset($_GET['Submit1'])) {
mysql_query("UPDATE options SET rotationCap = '".$_GET['maximumNumber']."'");
$saved = 1;
}

$result = mysql_query("select rotationCap from options where id=1 LIMIT 1");
$row = mysql_fetch_array($result);
$currentNumber = $row[0];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<?php include(WEB_ROOT.PROJECT_DIR.'/_includes/default-page-content-headers.inc.php'); ?>
		<?php include(WEB_ROOT.PROJECT_DIR.'/_includes/default-page-cache.inc.php'); ?>

		<title><?php echo htmlentities(PROJECT_TITLE); ?>: Recent Activity</title>

		<meta name="author" content="Chris Bloom" />
		<meta name="robots" content="noindex, nofollow, noarchive" />
		<meta name="MSSmartTagsPreventParsing" content="true" />
		<meta name="no-email-collection" content="http://www.unspam.com/noemailcollection/" />

		<meta name="DC.Creator" content="Chris Bloom" />
		<meta name="DC.Date" content="YYYY-MM-DD" />
		<meta name="DC.Format" content="text/html" />
		<meta name="DC.Language" content="en" />
		<meta name="DC.Title" content="Recent Activity" />

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
						<h2>Recent Activity</h2>

						<div>
							<a href="leads.php?start_date=<?php w(date("m/d/Y")); ?>"><?php w($todays_leads); w(($todays_leads == 1) ? ' lead' : ' leads'); ?> today</a><br>
							<a href="leads_public.php?start_date=<?php w(date("m/d/Y")); ?>"><?php w($from_public); w(($from_public == 1) ? ' publically generated lead' : ' publically generated leads'); ?> today</a><br/>
							<a href="leads_public_not_auth.php?start_date=<?php w(date("m/d/Y")); ?>"><?php w($from_public_not_auth); w(($from_public_not_auth == 1) ? ' publically generated lead not authenticated' : ' publically generated leads not authenticated'); ?> today</a><br/>
							<a href="leads.php?end_date=<?php w(date("m/d/Y")); ?>&start_date=<?php w(date('m/d/Y', mktime(0,0,0,date("m"),date("d")-30,date("Y")))); ?>"><?php w($last_30_days); w(($last_30_days == 1) ? ' lead' : ' leads'); ?> in the last 30 days</a><br>
							 <a href="leads_public.php?end_date=<?php w(date("m/d/Y")); ?>&start_date=<?php w(date('m/d/Y', mktime(0,0,0,date("m"),date("d")-30,date("Y")))); ?>"><?php w($from_public_30_days); w(($from_public_30_days == 1) ? ' lead' : ' leads'); ?> publically generated leads in the last 30 days<br/>
							 <a href="leads_public_not_auth.php?end_date=<?php w(date("m/d/Y")); ?>&start_date=<?php w(date('m/d/Y', mktime(0,0,0,date("m"),date("d")-30,date("Y")))); ?>"><?php w($from_public_30_days_not_auth); w(($from_public_30_days_not_auth == 1) ? ' lead' : ' leads'); ?> publically generated leads not authenticated in the last 30 days<br/>
							<a href="carriers.php?filter=renewals"><?php w($buy_more); w(($buy_more == 1) ? ' company needs' : ' companies need'); ?> to purchase more leads</a><br><br><br>
                            <h2>Options</h2><br>
                            <u>Rotation</u><br>
                            Current: <?php echo $currentNumber; ?>
                            <form action="index.php" method="get">Rotation Maximum Leads: <input type="text" style="width:30px" name="maximumNumber" /> <input type="Submit" name="Submit1" value="Update" /> </form><?php if ($saved==1){
                              echo " Updated Successfuly!";} ?>

						</div>
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
