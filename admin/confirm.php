<?php require_once('../_includes/project.inc.php'); ?>

<?
$db = &connectToDB();
if($gDebug) $db->debug = true;
//echo $_GET[passcode];



$sql = "SELECT * FROM quotes WHERE lead_id = \"$_GET[auth_id]\"";
//echo $sql;
	$result = mysql_query($sql) or die("Couldn't execute sql query.".mysql_error());
	$num_rows = mysql_num_rows($result);
	while($row_quote = mysql_fetch_array($result)){
		$lead_id_is_auth = $row_quote[1];
		$is_authenticated = $row_quote[3];
	}
	if($is_authenticated != '1'){
	if($num_rows==0){
		$arrErr[] = form_error('Please ensure you entered a correct area code and prefix.', 'phone','area code and prefix');
	}else{
		$sql_update_quote = "UPDATE quotes SET authenticated = '1' WHERE lead_id = \"$_GET[auth_id]\"";
		$result_update_quote = mysql_query($sql_update_quote) or die("Couldn't execute sql_update_quote query.".mysql_error());
	}
while($row_get_lead_id = mysql_fetch_array($result)){
	$leadid = $row_get_lead_id[1];
	
	//echo $lead_id;
}
$sql_get_lead = "SELECT * FROM leads WHERE id = $lead_id_is_auth";

//echo $sql_get_lead;

$result_get_lead = mysql_query($sql_get_lead) or die("Couldn't execute sql_get_lead query.".mysql_error());

while($row_get_lead = mysql_fetch_array($result_get_lead)){
	//echo $row_get_lead[1];

	$arrVars = array();
	//array(THE_VALUE, THE_TYPE, THE_DEFINED_VALUE, THE_NOT_DEFINED_VALUE, FIELD_IS_ARRAY, ALLOW_NULL_VALUE)
		$arrVars['leadid'] = $row_get_lead[0];
		$arrVars['vehicle_year'] = $row_get_lead[1];
		$arrVars['vehicle_make'] = $row_get_lead[2];
		$arrVars['vehicle_model'] = $row_get_lead[3];
		$arrVars['vehicle_condition1'] = $row_get_lead[4];
		$arrVars['vehicle_year2'] = $row_get_lead[5];
		$arrVars['vehicle_make2'] = $row_get_lead[6];
		$arrVars['vehicle_model2'] = $row_get_lead[7];
		$arrVars['vehicle_condition2'] = $row_get_lead[8];
		$arrVars['vehicle_pickup_location'] = $row_get_lead[9];
		$arrVars['vehicle_pickup_state'] = $row_get_lead[10];
		$arrVars['vehicle_destination_city'] = $row_get_lead[11];
		$arrVars['vehicle_destination_state'] = $row_get_lead[12];
		$arrVars['carrier_type'] = $row_get_lead[14];
		$arrVars['email'] = $row_get_lead[18];
		$arrVars['comments'] = $row_get_lead[21];
		$arrVars['move_date'] = $row_get_lead[23];
		$arrVars['phone'] = $row_get_lead[17];
		$arrVars['customername'] = $row_get_lead[16];
	
}



	require('../_includes/processClass.inc.php');
		$send = new send_mail($lead_id_is_auth);
		$lead_id = $send->build_array($arrVars);
		print("Lead ID:");
		echo $lead_id;
		redirect('lead_details.php', array('confirm'=>'add', 'id' => $lead_id_is_auth), $gDebug);
		exit();
	}else{
		redirect('lead_details.php', array('id' => $lead_id_is_auth), $gDebug);
		exit();
	}
	?>