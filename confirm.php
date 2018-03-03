<?php require_once('_includes/project.inc.php'); ?>

<?
$db = &connectToDB();
if($gDebug) $db->debug = true;
//echo $_GET[passcode];



$sql = "SELECT * FROM quotes WHERE passcode = \"$_GET[passcode]\" AND email = \"$_GET[email]\"";
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
		$sql_update_quote = "UPDATE quotes SET authenticated = '1' WHERE passcode = \"$_GET[passcode]\" AND email = \"$_GET[email]\"";
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
	echo $row_get_lead[1];

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
        $arrVars['moving'] = $row_get_lead[26];
	
}

/*
	require('_includes/processClass.inc.php');
		$send = new send_mail($db->Insert_ID());
		$lead_id = $send->build_array($_POST);
		*/
		

$Name = "Hauling Depot Leads Request"; //senders name
$recipient = $arrVars['email']; //recipient
$email = "leads@haulingdepot.com"; //senders e-mail adress
$subject = "";
$header = "From: ". $Name . " <" . $email . ">\r\n";
$header  .= 'MIME-Version: 1.0' . "\r\n";
$header .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
	

	$email_str ="<table width=\"550\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" align=\"CENTER\" bgcolor=\"#E3CD3E\">
	<tr>
		<td align=\"CENTER\">
		<span style='font-size: 20pt; color: black;'>Quote - Reference ID: ".$arrVars['leadid']."</span>
		</td>
	</tr>
	</table>
	<table width=\"550\" border=\"0\" align=\"center\" bgcolor=\"#F0E497\">";
	$email_str.="<tr>
					<td width=\"250\" align=\"right\">*Name:</td>
					<td width=\"300\">".$arrVars['customername']."</td>
				</tr>
				<tr>
					<td align=\"right\">Phone:</td>
					<td>".$arrVars['phone']."</td>
				</tr>
				<tr>
					<td align=\"right\">Email:</td>
					<td><a href=\"mailto:".$arrVars['email']."\">".$arrVars['email']."</a></td>
				</tr>";
	$email_str.="<tr>
					<td align=\"right\">*Year:</td>
					<td>".$arrVars['vehicle_year']."</td>
				</tr>";
	$email_str.="<tr>
					<td align=\"right\">*Make:</td>
					<td>".$arrVars['vehicle_make']."</td>
				</tr>";
	$email_str.="<tr>
					<td align=\"right\">*Model:</td>
					<td>".$arrVars['vehicle_model']."</td>
				</tr>";
	$email_str.="<tr>
					<td align=\"right\">Condition:</td>
					<td>".$arrVars['vehicle_condition1']."</td>
				</tr>";
	if(
		(strlen(trim($arrVars['vehicle_year2'])) && strlen(trim($arrVars['vehicle_make2'])) && strlen(trim($arrVars['vehicle_model2'])))
	) {
	$email_str.="<tr>
					<td align=\"right\">Auto 2 Year:</td>
					<td>".$arrVars['vehicle_year2']."</td>
				</tr>
				<tr>
					<td align=\"right\">Auto 2 Make:</td>
					<td>".$arrVars['vehicle_make2']."</td>
				</tr>
				<tr>
					<td align=\"right\">Auto 2 Model:</td>
					<td>".$arrVars['vehicle_model2']."</td>
				</tr>
				<tr>
					<td align=\"right\">Auto 2 Condition:</td>
					<td>".$arrVars['vehicle_condition2']."</td>
				</tr>";
	}
	$email_str.="<tr>
					<td align=\"right\">*Vehicle pick up city:</td>
					<td>".$arrVars['vehicle_pickup_location']."</td>
				</tr>
				<tr>
					<td align=\"right\">*Vehicle pick up state:</td>
					<td>".$arrVars['vehicle_pickup_state']."</td>
				</tr>
				<tr>
					<td align=\"right\">*Vehicle delivery city:</td>
					<td>".$arrVars['vehicle_destination_city']."</td>
				</tr>
				<tr>
					<td align=\"right\">*Vehicle delivery state:</td>
					<td>".$arrVars['vehicle_destination_state']."</td>
				</tr>
				<tr>
					<td align=\"right\">*Estimated move date:</td>
					<td>
						<table>
						<tr>
							<td align=\"LEFT\" colspan=\"3\">

							".$arrVars['move_date']."

							</td>
						</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td align=\"right\">Carrier Type:</td>
					<td>
					".(($arrVars['carrier_type'] == "") ? "Any" : $arrVars['carrier_type'])."

				</tr>
				<tr>
					<td colspan=\"2\" align=\"CENTER\">

					Additional Comments:
					<br>
					".$arrVars['comments']."

					</td>
				</table>";
		mail($recipient, $subject, $email_str, $header);

	require('_includes/processClass.inc.php');

		$send = new send_mail($lead_id_is_auth);
		$lead_id = $send->build_array($arrVars);
        $lead_id_all = $send->build_array_all($arrVars);
		/*print("<br>Lead ID:");
		echo $lead_id;*/
        //unlink('_config/config.inc.php');
        if ($arrVars['moving'] == "checked"){
         rename('_config/temp.inc.php', '_config/config.inc.php');
          $murl= "http://www.haulingdepot.com/contactform_movingc.php";
          $fields = array (


		'vehicle_make'=>"4",
		'vehicle_pickup_location'=>$arrVars['vehicle_pickup_location'],
		'vehicle_pickup_state'=>$arrVars['vehicle_pickup_state'],
		'vehicle_destination_city'=>$arrVars['vehicle_destination_city'],
		'vehicle_destination_state'=>$arrVars['vehicle_destination_state'],
		'email'=>$arrVars['email'],
		'comments'=>$arrVars['comments'],
		'move_date'=>$arrVars['move_date'],
		'phone'=>$arrVars['phone'],
		'customername'=>$arrVars['customername'],
        'jstSubmit'=>"true"
        );
         foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
rtrim($fields_string,'&');
$ch = curl_init();

//set the url, number of POST vars, POST data
curl_setopt($ch,CURLOPT_URL,$murl);
curl_setopt($ch,CURLOPT_POST,count($fields));
curl_setopt($ch,CURLOPT_POSTFIELDS,$fields_string);

//execute post
$result = curl_exec($ch);

//close connection
curl_close($ch);
        } else{redirect('thank-you.php');
		exit();}

	}else{
		redirect('thank-you.php');
		exit();
	}
	?>