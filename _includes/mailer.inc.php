<?php
$headers = "From: {$this->email_from}".EMAIL_SEPARATOR;

if($this->array['email'] > ''){
	$headers .= "Reply-To: {$this->array['email']}".EMAIL_SEPARATOR;
}
if ($this->debug || $this->test) $headers .= "BCC: {$this->email_debug}".EMAIL_SEPARATOR;

if (!isset($email_str)) $email_str = '';

if($EmailFormat==0){//if email format is text

	$email_str.=$this->title.' Quote'."\n";

	$email_str.="Reference ID: ".$this->lead_id."\n".
		"Name: ".$this->array['customername']."\n".
		"Phone: ".$this->array['phone']."\n".
		"Email: ".$this->array['email']."\n";
   	if(
		(strlen(trim($this->array['vehicle_year'])) && strlen(trim($this->array['vehicle_model'])))
	) {
		$email_str.="Year: ".$this->array['vehicle_year']."\n";
	$email_str.="Make: ".$this->array['vehicle_make']."\n";
	$email_str.="Model: ".$this->array['vehicle_model']."\n";

	$email_str.="Condition: ".$this->array['vehicle_condition1']."\n";
	}  else {
	  $email_str.="Rooms To Move: ".$this->array['vehicle_make']."\n";
	}



	if(
		(strlen(trim($this->array['vehicle_year2'])) && strlen(trim($this->array['vehicle_make2'])) && strlen(trim($this->array['vehicle_model2'])))
	) {
		$email_str.="Auto 2 Year: ".$this->array['vehicle_year2']."\n".
		"Auto 2 Make: ".$this->array['vehicle_make2']."\n".
		"Auto 2 Model: ".$this->array['vehicle_model2']."\n".
		"Auto 2 Condition: ".$this->array['vehicle_condition2']."\n";
	}
    if(
    (strlen(trim($this->array['vehicle_year'])) && strlen(trim($this->array['vehicle_model'])))
    )
    {
    $email_str.="Vehicle pick up city: ".$this->array['vehicle_pickup_location']."\n".
		"Vehicle pick up state: ".$this->array['vehicle_pickup_state']."\n".
		"Vehicle delivery city: ".$this->array['vehicle_destination_city']."\n".
		"Vehicle delivery state: ".$this->array['vehicle_destination_state']."\n";
        } else {
         $email_str.="Pick up city: ".$this->array['vehicle_pickup_location']."\n".
		"Pick up state: ".$this->array['vehicle_pickup_state']."\n".
		"Delivery city: ".$this->array['vehicle_destination_city']."\n".
		"Delivery state: ".$this->array['vehicle_destination_state']."\n";
        }
		$email_str.="Estimated move date: ".$date."\n";
    if(
		(strlen(trim($this->array['vehicle_year'])) && strlen(trim($this->array['vehicle_model'])))
	) {
    $email_str.="Carrier Type: ".(($this->array['carrier_type'] == "") ? "Any" : $this->array['carrier_type'])."\n";
     }
    $email_str.="Additional Comments:\n".
		$this->array['comments']."\n";
}else{
	$headers .= "MIME-Version: 1.0".EMAIL_SEPARATOR;
	$headers .= "Content-type: text/html; charset=iso-8859-1".EMAIL_SEPARATOR;

	$email_str.="<table width=\"550\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" align=\"CENTER\" bgcolor=\"#E3CD3E\">
	<tr>
		<td align=\"CENTER\">
		<span style='font-size: 20pt; color: black;'>".$this->title." Quote - Reference ID: ".$this->lead_id."</span>
		</td>
	</tr>
	</table>
	<table width=\"550\" border=\"0\" align=\"center\" bgcolor=\"#F0E497\">";
	$email_str.="<tr>
					<td width=\"250\" align=\"right\">*Name:</td>
					<td width=\"300\">".$this->array['customername']."</td>
				</tr>
				<tr>
					<td align=\"right\">Phone:</td>
					<td>".$this->array['phone']."</td>
				</tr>
				<tr>
					<td align=\"right\">*Email:</td>
					<td>".$this->array['email']."</td>
				</tr>";
                if(
		(strlen(trim($this->array['vehicle_year'])) && strlen(trim($this->array['vehicle_model'])))
	) {
	$email_str.="<tr>
					<td align=\"right\">*Year:</td>
					<td>".$this->array['vehicle_year']."</td>
				</tr>";
	$email_str.="<tr>
					<td align=\"right\">*Make:</td>
					<td>".$this->array['vehicle_make']."</td>
				</tr>";
	$email_str.="<tr>
					<td align=\"right\">*Model:</td>
					<td>".$this->array['vehicle_model']."</td>
				</tr>";
	$email_str.="<tr>
					<td align=\"right\">Condition:</td>
					<td>".$this->array['vehicle_condition1']."</td>
				</tr>";
                } else {
                  $email_str.="<tr>
					<td align=\"right\">*Rooms To Move:</td>
					<td>".$this->array['vehicle_make']."</td>
				</tr>";
                }
	if(
		(strlen(trim($this->array['vehicle_year2'])) && strlen(trim($this->array['vehicle_make2'])) && strlen(trim($this->array['vehicle_model2'])))
	) {
	$email_str.="<tr>
					<td align=\"right\">Auto 2 Year:</td>
					<td>".$this->array['vehicle_year2']."</td>
				</tr>
				<tr>
					<td align=\"right\">Auto 2 Make:</td>
					<td>".$this->array['vehicle_make2']."</td>
				</tr>
				<tr>
					<td align=\"right\">Auto 2 Model:</td>
					<td>".$this->array['vehicle_model2']."</td>
				</tr>
				<tr>
					<td align=\"right\">Auto 2 Condition:</td>
					<td>".$this->array['vehicle_condition2']."</td>
				</tr>";
	}
    if(
		(strlen(trim($this->array['vehicle_year'])) && strlen(trim($this->array['vehicle_model'])))
	) {
    $email_str.="<tr>
					<td align=\"right\">*Vehicle pick up/origin city:</td>
					<td>".$this->array['vehicle_pickup_location']."</td>
				</tr>
				<tr>
					<td align=\"right\">*Vehicle pick up/origin state:</td>
					<td>".$this->array['vehicle_pickup_state']."</td>
				</tr>
				<tr>
					<td align=\"right\">*Vehicle destination/delivery city:</td>
					<td>".$this->array['vehicle_destination_city']."</td>
				</tr>
				<tr>
					<td align=\"right\">*Vehicle destination/delivery state:</td>
					<td>".$this->array['vehicle_destination_state']."</td>
				</tr>";
                       } else {
                       $email_str.="<tr>
					<td align=\"right\">*Pick up/origin city:</td>
					<td>".$this->array['vehicle_pickup_location']."</td>
				</tr>
				<tr>
					<td align=\"right\">*Pick up/origin state:</td>
					<td>".$this->array['vehicle_pickup_state']."</td>
				</tr>
				<tr>
					<td align=\"right\">*Destination/delivery city:</td>
					<td>".$this->array['vehicle_destination_city']."</td>
				</tr>
				<tr>
					<td align=\"right\">*Destination/delivery state:</td>
					<td>".$this->array['vehicle_destination_state']."</td>
				</tr>";
                       }
                $email_str.="<tr>
					<td align=\"right\">*Estimated move date:</td>
					<td>
						<table>
						<tr>
							<td align=\"LEFT\" colspan=\"3\">

							".$date."

							</td>
						</tr>
						</table>
					</td>
				</tr>";
    if(
		(strlen(trim($this->array['vehicle_year'])) && strlen(trim($this->array['vehicle_model'])))
	) {
    $email_str.="<tr>
					<td align=\"right\">Carrier Type:</td>
					<td>
					".(($this->array['carrier_type'] == "") ? "Any" : $this->array['carrier_type'])."

				</tr>";
                }
				$email_str.="<tr>
					<td colspan=\"2\" align=\"CENTER\">

					Additional Comments:
					<br>
					".$this->array['comments']."

					</td>
				</table>";
}
?>