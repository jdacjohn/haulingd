<?php require_once('../_includes/project.inc.php'); ?>
<?php
require_login();

if (htmlentities(PROJECT_TITLE) == 'Moving Leads'){
  $ML = 1;
} else {
  $ML = 0;
}

$db = &connectToDB();
if($gDebug) $db->debug = true;

$rs = false;
if (getParam('id') > 0) {
	$sql = 'SELECT * FROM leads WHERE id = ?';
	$rs = $db->SelectLimit($sql, 1, 0, array(intval(getParam('id'))));
}

if ($rs && !$rs->EOF) {
?>
<h2>Lead Details</h2>
<div class="formElementSet formElementSetText">
	<label class="fieldlabel">Name</label>
	<span class="text"><?php echo htmlspecialchars($rs->fields['customername']); ?> &nbsp;</span>
</div>
<div class="formElementSet formElementSetText">
	<label class="fieldlabel">Phone</label>
	<span class="text"><?php echo htmlspecialchars($rs->fields['phone']); ?> &nbsp;</span>
</div>
<div class="formElementSet formElementSetText">
	<label class="fieldlabel">E-mail</label>
	<span class="text"><?php echo htmlspecialchars($rs->fields['email']); ?> &nbsp;</span>
</div>
<?php if ($ML != 1){ echo '<div class="formElementSet formElementSetText">
	<label class="fieldlabel">Vehicle Year</label>
	<span class="text">'; echo htmlspecialchars($rs->fields['vehicle_year']); echo  '&nbsp;</span>
</div>';}?>
<div class="formElementSet formElementSetText">
	<label class="fieldlabel"><?php if ($ML != 1){ echo 'Vehicle Make';} else{echo 'Rooms To Move';} ?></label>
	<span class="text"><?php echo htmlspecialchars($rs->fields['vehicle_make']); ?> &nbsp;</span>
</div>
<?php if ($ML != 1){ echo '<div class="formElementSet formElementSetText">
	<label class="fieldlabel">Vehicle Model</label>
	<span class="text">'; echo htmlspecialchars($rs->fields['vehicle_model']); echo  '&nbsp;</span>
</div>
<div class="formElementSet formElementSetText">
	<label class="fieldlabel">Vehicle Condition1</label>
	<span class="text">'; echo htmlspecialchars($rs->fields['vehicle_condition1']); echo '&nbsp;</span>
</div>
<div class="formElementSet formElementSetText">
	<label class="fieldlabel">Vehicle Year 2</label>
	<span class="text">'; echo htmlspecialchars($rs->fields['vehicle_year2']); echo '&nbsp;</span>
</div>
<div class="formElementSet formElementSetText">
	<label class="fieldlabel">Vehicle Make 2</label>
	<span class="text">'; echo htmlspecialchars($rs->fields['vehicle_make2']); echo '&nbsp;</span>
</div>
<div class="formElementSet formElementSetText">
	<label class="fieldlabel">Vehicle Model 2</label>
	<span class="text">'; echo htmlspecialchars($rs->fields['vehicle_model2']); echo '&nbsp;</span>
</div>
<div class="formElementSet formElementSetText">
	<label class="fieldlabel">Vehicle Condition 2</label>
	<span class="text">'; echo htmlspecialchars($rs->fields['vehicle_condition2']); echo '&nbsp;</span>
</div>';} ?>
<div class="formElementSet formElementSetText">
	<label class="fieldlabel">Pickup Location</label>
	<span class="text"><?php echo htmlspecialchars($rs->fields['vehicle_pickup_location']); ?> &nbsp;</span>
</div>
<div class="formElementSet formElementSetText">
	<label class="fieldlabel">Pickup State</label>
	<span class="text"><?php echo htmlspecialchars($rs->fields['vehicle_pickup_state']); ?> &nbsp;</span>
</div>
<div class="formElementSet formElementSetText">
	<label class="fieldlabel">Destination City</label>
	<span class="text"><?php echo htmlspecialchars($rs->fields['vehicle_destination_city']); ?> &nbsp;</span>
</div>
<div class="formElementSet formElementSetText">
	<label class="fieldlabel">Destination State</label>
	<span class="text"><?php echo htmlspecialchars($rs->fields['vehicle_destination_state']); ?> &nbsp;</span>
</div>
<!--<?php if ($ML != 1){ echo '<div class="formElementSet formElementSetText">
	<label class="fieldlabel">Carrier Type</label>
	<span class="text">'; echo htmlspecialchars($rs->fields['carrier_type']); echo '&nbsp;</span>
</div>';} ?>-->
<div class="formElementSet formElementSetText">
	<label class="fieldlabel">Comments</label>
	<span class="text"><?php echo htmlspecialchars($rs->fields['comments']); ?> &nbsp;</span>
</div>
<div class="formElementSet formElementSetText">
	<label class="fieldlabel">Move Date</label>
	<span class="text"><?php echo htmlspecialchars(myts_date($rs->fields['move_date'], 'Y-m-d')); ?> &nbsp;</span>
</div>
<div class="formElementSet formElementSetText">
	<label class="fieldlabel">Submitted Date</label>
	<span class="text"><?php echo htmlspecialchars(myts_date($rs->fields['created_at'], 'Y-m-d')); ?> &nbsp;</span>
</div>
<h3>Lead Sent To:</h3>
<?php
	$crs = $db->Execute('SELECT company_name, c.id FROM carriers_leads cl LEFT JOIN carriers c ON cl.carrier_id = c.id WHERE cl.lead_id = ?', array(intval(getParam('id'))));

	if ($crs && !$crs->EOF) {
		$i = 0;
		while (!$crs->EOF) {
			$i++;
?>
<div class="formElementSet formElementSetText">
	<label class="fieldlabel">Carrier #<?php echo $i; ?></label>
	<span class="text"><a href="leads.php?carrier_id=<?php echo htmlspecialchars($crs->fields['id']); ?>" title="View all leads sent to this company"><?php echo htmlspecialchars($crs->fields['company_name']); ?></a> &nbsp;</span>
</div>
<?php
			$crs->MoveNext();
		}
	}
	else {
?>
<p>This lead was not sent to any carriers, or all of the underlying carriers have been deleted from the system.</p>
<?php
	}
}
else {
?>
<p class="error">The record is unavailable or you are not authorized to view it. Please return to the <a href="leads.php">leads list</a> and try again.</p>
<?php
}
?>