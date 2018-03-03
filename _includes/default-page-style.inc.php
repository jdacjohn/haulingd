<link rel="stylesheet" type="text/css" media="all" href="<?php echo htmlentities(PROJECT_URL); ?>/styles/base.css" />
<?php if (isset($_REQUEST['print']) && $_REQUEST['print'] == 1) { ?>
<link rel="stylesheet" type="text/css" media="all" href="<?php echo htmlentities(PROJECT_URL); ?>/styles/print.css" />
<?php } else { ?>
<style type="text/css" media="all">
	@import url(<?php echo htmlentities(PROJECT_URL); ?>/styles/layout.css);
</style>
<!--[if lt IE 7]>
<link rel="stylesheet" type="text/css" href="<?php echo htmlentities(PROJECT_URL); ?>/styles/layout-ie.css" />
<![endif]-->
<!--[if IE 7]>
<link rel="stylesheet" type="text/css" href="<?php echo htmlentities(PROJECT_URL); ?>/styles/layout-ie7.css" />
<![endif]-->
<?php } //END: if (isset($_REQUEST['print']) && $_REQUEST['print'] == 1) ?>
