<?php if (!defined('APP_LOADED')) return; /* useful when including this page */ ?>
<?php if (isset($GLOBALS['arrErr']) && sizeof($GLOBALS['arrErr']) > 0) { ?>
<div id="error-handler" class="notice"><strong>The following errors were received:</strong>
	<ul>
		<?php foreach ($GLOBALS['arrErr'] as $strErr) { ?>
		<li class="error"><?php echo $strErr; ?></li>
		<?php } ?>
	</ul>
</div>
<?php } ?>
