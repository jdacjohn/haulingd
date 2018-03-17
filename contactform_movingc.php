<?php
ob_start();
require_once('configs/moving/config.inc.php');
require_once('_includes/noconfig.project.inc.php');
?>
<?php
//require_login();
$db = &connectToDB();
if($gDebug) $db->debug = true;

//define variables for form data
$arrVars = array(
	//array(THE_VALUE, THE_TYPE, THE_DEFINED_VALUE, THE_NOT_DEFINED_VALUE, FIELD_IS_ARRAY, ALLOW_NULL_VALUE)
	newFieldArray('vehicle_year', 'text', '', '', false, false),
	newFieldArray('vehicle_make', 'text', '', '', false, false),
	newFieldArray('vehicle_model', 'text', '', '', false, false),
	newFieldArray('vehicle_condition1', 'text', '', '', false, false),
	newFieldArray('vehicle_year2', 'text', '', '', false, true),
	newFieldArray('vehicle_make2', 'text', '', '', false, true),
	newFieldArray('vehicle_model2', 'text', '', '', false, true),
	newFieldArray('vehicle_condition2', 'text', '', '', false, true),
	newFieldArray('vehicle_pickup_location', 'text', '', '', false, false),
	newFieldArray('vehicle_pickup_state', 'text', '', '', false, false),
	newFieldArray('vehicle_destination_city', 'text', '', '', false, false),
	newFieldArray('vehicle_destination_state', 'text', '', '', false, false),
	newFieldArray('flexable_move', 'text', '', '', false, false),
	newFieldArray('carrier_type', 'text', '', '', false, false),
	newFieldArray('carrier_type2', 'text', '', '', false, false),
	newFieldArray('customername', 'text', '', '', false, false),
	newFieldArray('phone', 'text', '', '', false, false),
	newFieldArray('email', 'text', '', '', false, false),
	newFieldArray('email2', 'text', '', '', false, false),
	newFieldArray('contactmethod', 'text', '', '', false, false),
	newFieldArray('comments', 'text', '', '', false, false),
	newFieldArray('companyname', 'text', '', '', false, false),
	newFieldArray('move_date', 'date', '', '', false, false)
);

//initialize values
if (!isset($arrVals)) $arrVals = array();
foreach ($arrVars as $var) {
	$arrVals[$var[THE_VALUE]] = getParam($var[THE_VALUE], (bool) $var[FIELD_IS_ARRAY]);
}
if($gDebug) printvar($arrVals, 'arrVals');

if (!isset($arrErr)) $arrErr = array();
if (getParam('frmSubmit') == 'true') {
	//check req'd data
	if (strlen(trim($arrVals['customername'])) == 0) {
		$arrErr[] = form_error('Please enter the name of the customer.', 'customername', 'customer');
	}


		if (!(strlen(trim($arrVals['email']))) && !check_email(trim($arrVals['email']))) {
			$arrErr[] = form_error('Please enter a valid email address.', 'email');
		}
		if (!preg_match("/^[_\.0-9a-zA-Z-]+@([0-9a-zA-Z][0-9a-zA-Z-]+\.)+[a-zA-Z]{2,6}$/i", trim($arrVals['email']))){
			$arrErr[] = form_error('Please enter a valid email address.', 'email');
		}

	   //	if (!(strlen(trim($arrVals['phone']))) && !check_phone(trim($arrVals['phone']))) {
		//	$arrErr[] = form_error('Please enter a valid 10-digit phone number. (i.e. 123-456-7890)', 'phone');
		//}


	//if (!(strlen(trim($arrVals['vehicle_year'])) && is_numeric(trim($arrVals['vehicle_year'])))) {
	//	$arrErr[] = form_error('Please enter a valid year for the primary vehicle. (ex: 1997, 97, 07, etc.)', 'vehicle_year', 'year');
	//}

	if (!strlen(trim($arrVals['vehicle_make']))) {
		$arrErr[] = form_error('Please enter the amount of rooms you are moving.', 'vehicle_make', 'make');
	}

	//if (!strlen(trim($arrVals['vehicle_model']))) {
	//	$arrErr[] = form_error('Please enter the model for the primary vehicle.', 'vehicle_model', 'model');
	//}

	if (
		(
			//if any of these are filled out...
			strlen(trim($arrVals['vehicle_year2']))
			||
			strlen(trim($arrVals['vehicle_make2']))
			||
			strlen(trim($arrVals['vehicle_model2']))
		)
		&&
		(
			//and any of these were left blank...
			!strlen(trim($arrVals['vehicle_year2']))
			||
			!strlen(trim($arrVals['vehicle_make2']))
			||
			!strlen(trim($arrVals['vehicle_model2']))
		)
	) {
		$arrErr[] = form_error('Please enter the year, ', 'vehicle_year2', 'year') . form_error('make and ', 'vehicle_make2', 'make') . form_error('model when listing a second vehicle.', 'vehicle_model2', 'model');
	}
	elseif (strlen(trim($arrVals['vehicle_year2'])) && !is_numeric(trim($arrVals['vehicle_year2']))) {
		$arrErr[] = form_error('Please enter a valid year for the second vehicle. (ex: 1997, 97, 07, etc.)', 'vehicle_year2', 'year');
	}

	if (strlen(trim($arrVals['vehicle_pickup_location'])) == 0) {
		$arrErr[] = form_error('Please enter the pickup city.', 'vehicle_pickup_location', 'city');
	}

	if (strlen(trim($arrVals['vehicle_pickup_state'])) == 0) {
		$arrErr[] = form_error('Please enter the pickup state.', 'vehicle_pickup_state', 'state');
	}

	if (strlen(trim($arrVals['vehicle_destination_city'])) == 0) {
		$arrErr[] = form_error('Please enter the destination city.', 'vehicle_destination_city', 'city');
	}

	if (strlen(trim($arrVals['vehicle_destination_state'])) == 0) {
		$arrErr[] = form_error('Please enter the destination state.', 'vehicle_destination_state', 'state');
	}

	if (strlen(trim($arrVals['comments'])) > 500) {
		$arrErr[] = form_error('Please ensure your comments are less than 500 characters in length.', 'comments');
	}

	if (!strlen(trim($arrVals['move_date'])) || strtotime(trim($arrVals['move_date'])) <= 0) {
		$arrErr[] = form_error('Please enter a valid move date. The format should be in <em>mm/dd/yyyy</em> format.', 'move_date', 'move date');
	}
		if($arrVals['phone']){
	$phone_number_formatted = preg_replace("([()-])", "", $arrVals['phone']);
	//echo $phone_number_formatted;
	$area_code = substr($phone_number_formatted, 0, 3);
	$prefix = substr($phone_number_formatted, 3, 3);

	//$sql = 'SELECT * FROM areacodes WHERE area_code = '.$area_code.' AND prefix = '.$prefix;
	//$result = mysql_query($sql) or die("Couldn't execute that query.".mysql_error());
   //	$num_rows = mysql_num_rows($result);
   //if($num_rows==0){
   //		$arrErr[] = form_error('Please ensure you entered a correct area code and prefix.', 'phone','area code and prefix');
   //	}

	//echo $sql;

}
$arrVals['move_date'] = date('m/d/Y', time()+86400);
}//end validation
if ($gDebug) printvar($arrVals, 'arrVals');


if (getParam('frmSubmit') == 'true' && sizeof($arrErr) == 0) {
rename('_config/config.inc.php', '_config/temp.inc.php');
copy('configs/moving/config.inc.php', '_config/config.inc.php');
	//insert new record
	$updateSQL['fields'] = "";
	$updateSQL['values'] = "";
	$newFieldsArray = array();
	foreach ($arrVars as $var) {
		if ($var[THE_VALUE] == 'id') continue;
		else {
			$updateSQL['fields'] .= (strlen($updateSQL['fields']) > 0) ? ", " : "";
			$updateSQL['values'] .= (strlen($updateSQL['values']) > 0) ? ", " : "";
			$updateSQL['fields'] .= "`" . $var[THE_VALUE] . "`";
			if ($var[THE_VALUE] == 'move_date') {
				$updateSQL['values'] .= getSQLValueString(date('Y-m-d', strtotime(trim($arrVals[$var[THE_VALUE]]))), 'date');
			}
			else {
				$updateSQL['values'] .= getSQLValueString($arrVals[$var[THE_VALUE]], $var[THE_TYPE], $var[THE_DEFINED_VALUE], $var[THE_NOT_DEFINED_VALUE], ARRAY_GLUE, $var[ALLOW_NULL_VALUE]);
			}
		}
	}

	if (!$db->Execute('INSERT INTO leads ('.$updateSQL['fields'].', `updated_at`, `created_at`) VALUES ('.$updateSQL['values'].', NOW(), NOW())')) {
		$arrErr[] = 'An error occured while while updating your data. Please try again.';
	} else {


		//redirect to next step
		function createRandomPassword() {

    $chars = "abcdefghijkmnopqrstuvwxyz023456789";
    srand((double)microtime()*1000000);
    $i = 0;
    $pass = '' ;

    while ($i <= 20) {
        $num = rand() % 33;
        $tmp = substr($chars, $num, 1);
        $pass = $pass . $tmp;
        $i++;
    }

    return $pass;

}

$password = createRandomPassword();

$formatted_date = date_format(new DateTime($arrVals['move_date']), 'Y-m-d');


$formatted_date_mysql = str_replace("/", "-",$arrVals['move_date']);
$formatted_date_mysql_split = preg_split("/-/", $formatted_date_mysql);
$formatted_date_mysql_final = $formatted_date_mysql_split[2]."-".$formatted_date_mysql_split[0]."-".$formatted_date_mysql_split[1];
//echo $formatted_date_mysql;
$sql_get_lead_id = "SELECT id FROM leads WHERE move_date = \"$formatted_date_mysql_final\" AND customername = \"$arrVals[customername]\" AND email = \"$arrVals[email]\"";

//echo $sql_get_lead_id;

$result_get_lead_id = mysql_query($sql_get_lead_id) or die("Couldn't execute that query.".mysql_error());

while($row_get_lead_id = mysql_fetch_array($result_get_lead_id)){
	$lead_id = $row_get_lead_id[0];
}
$sql_authenticate = "INSERT INTO quotes (lead_id, passcode, email) VALUES ('".$lead_id."', '".$password."', '".$arrVals['email']."')";

//echo $sql_authenticate;

$result_authenticate = mysql_query($sql_authenticate) or die("Couldn't execute that query.".mysql_error());
//echo $result_authenticate;
}

header( 'Location: '. PROJECT_URL . '/confirm.php?passcode='.$password.'&email='.$arrVals['email'].'' ) ;
}

elseif (getParam('frmSubmit') != 'true') {
	//set default date
	$arrVals['move_date'] = date('m/d/Y');
} //END: if (getParam('frmSubmit') == 'true' && sizeof($arrErr) == 0))
?>
<!DOCTYPE HTML>
<html>
<head>
		<?php include(WEB_ROOT.PROJECT_DIR.'/_includes/default-page-content-headers.inc.php'); ?>
		<?php include(WEB_ROOT.PROJECT_DIR.'/_includes/default-page-cache.inc.php'); ?>
        	 <script type="text/JavaScript" src="scripts/rounded_corners.inc.js"></script> <!-- Rounded Corners -->
     <script type='text/javascript' src="scripts/prototype.js"></script> <!-- Javascript Framework -->
    <script type='text/javascript' src="scripts/myTabz.js"></script> <!-- Vertical Tabs -->
	<script type='text/javascript' src="scripts/loadit.js"></script> <!-- Load Inner Javascript Functions -->
<script type="text/javascript" src="scripts/calendar.js"></script>
<script type="text/javascript" src="scripts/lang/calendar-en.js"></script> <!-- Date Picker -->
<script type="text/javascript" src="scripts/calendar-setup.js"></script> <!-- Date Picker -->
<script type="text/javaScript" src="scripts/qTip.js"></script>
<script type="text/javaScript" src="js/libs/jquery-1.10.1.min.js"></script>
<script type="text/javaScript" src="js/libs/jquery-ui.min.js"></script>
<? /*<script type="text/javaScript" src="jquery-1.4.2.js"></script>
<script type="text/javaScript" src="jquery-ui-1.8.5.custom.js"></script><!-- Tooltips -->*/ ?>
<script type="text/javaScript">
function toForm(){
var zi = jQuery("#initialZipcode").val();
var ci = jQuery("#initialCity").val();
var st = jQuery("#initialState").val();
jQuery("#vehicle_destination_zipcode").val(zi);
jQuery("#vehicle_destination_city").val(ci);
jQuery("#vehicle_destination_state").val(st);
jQuery("#twrapper").fadeOut('slow');
jQuery("#container").delay(1000).fadeIn('slow');
}
</script>
<style type="text/css">
.fixedfont1 {
color : #003399;
font-family: Verdana, Arial, Helvetica, sans-serif;
font-size: 12px;
font-weight: bold;
}
.fixedfont2 {
	color : #ffffff;
font-family : Verdana, Arial, Helevetica, sana-serif;
text-decoration : none;
font-size : 10px;
font-weight : bold;
}
.fixedfont3 {
	color : #000000;
font-family : Verdana, Arial, Helevetica, sana-serif;
text-decoration : none;
font-size : 10px;
font-weight : bold;
}
.fixedfont4 {
	color : #003399;
font-family : Verdana, Arial, Helevetica, sana-serif;
text-decoration : none;
font-size : 10px;
font-weight : bold;
}
.c7 {color: #333399; font-family: Verdana; font-size: 55%}
.c5 {color: #FFFFFF; font-family: Verdana; font-size: 64%}
.c4 {color: black; font-family: Verdana; font-size: 64%; text-align: center}
.c3 {color: white; font-family: Verdana; font-size: 64%}
.c2 {color: #003399; font-size:10px;}
.c3 a:hover{text-decoration:underline;}
#header{text-align: center}
#container{
width:750px;
margin:auto;
}
#main_content{
width:750px;
margin:auto;
background-color:#DDE6EE;
}

#page_title{
background-color:#3A5E81;
margin:auto;
margin-top:10px;
margin-bottom:10px;
width:300px;
font-size:14px;
color:#ffffff;
font-weight:bold;
padding:2px;
border:2px solid #ffffff;
text-align:center;
}
.left_contact{
width:325px;
}
div>.left_contact{
width:440px;
}

.form_title{
color:#000000;
font-family: Verdana;
font-size: 12px;
width:200px;
margin:5px 20px 5px 0px;
padding-top:5px;
height:25px;
display:inline;
}
.form_title_nw{
color:#000000;
font-family: Verdana;
font-size: 12px;
margin:5px 10px 5px 0px;
padding-top:5px;
height:25px;
display:inline;
}
.form_element{
width:200px;
margin-right:20px;
height:30px;
display:inline;
}

.form_row{
color:#000000;
font-family: Verdana;
font-size: 12px;
width:325px;
height:20px;
margin:5px;
}
.info_title{
background-color:#ffffff;
margin:auto;
margin-top:10px;
width:354px;
font-size:14px;
color:#3A5E81;
font-weight:bold;
padding:3px;
border:2px solid #3A5E81;
text-align:left;
}
.info_container{
border:2px solid #3A5E81;
border-top:0px;
padding:5px;
margin:auto;
width:350px;
}
.comments_title{
background-color:#ffffff;
margin:auto;
margin-top:10px;
width:350px;
font-size:14px;
color:#3A5E81;
font-weight:bold;
padding:3px;
border:2px solid #3A5E81;
text-align:left;
}
.comments_container{
border-top:0px;
padding:5px;
margin:auto;
width:350px;
}

.inner_title{
font-size:14px;
font-weight:bold;
color:#3A5E81;
background-color:#DDE6EE;
margin-bottom:-5px;
border-bottom:1px solid #3A5E81;
}

.inner_cont{
width:325px;
margin:auto;
margin-top:5px;
padding:5px;
}
.c4 a{
  color: #FF0000;
}
</style>
<title>Quotes Contact Form | Compare 5 Car Shipping Rates</title>
<meta name="Description" content="Fast, Free reliable car shipping quotes. Call or visit Hauling Depot 877-619-0710 for your vehicle shipping needs.">
<meta name="keywords" content="auto transporters,car shipping,vehicle shipping,auto transport,car shippers,auto shipping,car shipping quotes, auto transport rates">
<link href="homepagetestedit.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" type="text/css" href="styles/calendar-blue.css" />
</head>
<body>
<center>
<span style="color:#000000;font-size:24px;">Submitting Your Request...</span>
<div style="background-color:#FFFFFF;height:1500px;">&nbsp;</div>
<div id="container">
	<div id="header">
		<table width="750">
				<!--DWLayoutTable-->
				<tr>
					 <td height="90" colspan="3" valign="middle">
						  <a href="default.htm"><img src="images/haulingdepotthankyou.gif" width="425" height="75" border="0" /></a>
					 </td>
					 <td width="98" valign="middle" bgcolor="#FFFFFF">

						  <div class="c1">
							   <img src="images/NospamPolicy.jpg" width="89" height="88" />
						  </div>
					 </td><td width="204" valign="middle">
						  <div class="fixedfont1 c1">
							   Your Privacy is Our Policy!<br />
							   <br /><span class="c2">Our Business is Moving. We Do Not Use Your
							   Phone or Email For Any Other Purpose!</span></div></td></tr><tr> <td width="62" rowspan="4" valign="top"><img src="images/transport-contact.jpg" width="65" height="51"/>
					 </td>
					 <td width="84" height="21" align="center" valign="middle" bordercolor="#99CC66" bgcolor="#3A5E81" class="fixedfont2">
						  Live Help
					 </td>
					 <td colspan="3" align="center" valign="middle" bordercolor="#99CC66" bgcolor="#3A5E81">
						  <div class="c1">
							   <span class="c3"><a href="<?php echo HOME_LINK; ?>">Home</a> | <a href="car_shipping.php">Auto</a> | <a href="boat_transport.php">Boat</a> | <a href="motorcycle_shipping.php">Motorcycle</a> | <a href="international_canadian.php">Canadian Shipping</a> | <a href="<?php echo HOME_LINK ?>international.php">International</a> | <a href="<?php echo HOME_LINK; ?>contact.htm">Contact</a> |</span> <a href="about.htm">About</a>

						  </div>
					 </td>
				</tr>
				<tr>
					 <td height="26" align="center" valign="middle" bordercolor="#99CC66" bgcolor="#CCCCCC" class="fixedfont3">
						  877-619-0710
					 </td>
					 <td colspan="3" align="center" valign="middle" bgcolor="#CCCCCC">					     <span class="fixedfont3">Live
					     Quote Help Mon-Fri 9AM-6PM Est. Get Quotes By Email 24 Hours a Day
				     in Just Minutes.</span></td>
				</tr>
				<tr>
					 <td height="0"></td>
					 <td width="278"></td>
					 <td></td>
					 <td></td>
				</tr>
      </table>
</div>
	<div id="main_content">
		<div class="c4"><strong><br>
		  This Information Is Used  For The  Sole Purpose of Your Moving Quick
		  Quote Only. Compare &amp; You Save!</strong><br /> <?php include(WEB_ROOT.PROJECT_DIR.'/_includes/default-page-error-handler.inc.php'); ?></div>

    <form name="form1" id="form1" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">

		<input type="hidden" name="CategoryID" value="#CategoryID#">
		<input type="hidden" name="CategoryText" value="#GetCategory.CategoryText#">

		<input type="hidden" name="StateFrom" value="#StateFrom#">
		<input type="hidden" name="StateTo" value="#StateTo#">

		<input type="hidden" name="CustomerName_required" value="Please enter your name">
		<input type="hidden" name="Email_required" value="Its recommended to enter your e-mail address. If none type: <em>no email</em>">
		<input type="hidden" name="Phone_required" value="Its recommended to enter your phone as a backup to email. We have a very strict privacy policy & use it for your transport quote only! If you still do not wish to enter phone, Type: <em>no phone</em>">

		<input type="hidden" name="vehicle_year_required" value="Please enter the Year">

		<input type="hidden" name="vehicle_make_required" value="Please enter the Make">

		<input type="hidden" name="vehicle_model_required" value="Please enter the Model">

		<input type="hidden" name="vehicle_pickup_location_required" value="Please enter the pickup location">
		<input type="hidden" name="vehicle_pickup_state_required" value="Please enter the pickup state">
		<!--- <input type="hidden" name="Vehicle_pickup_zipcode_required" value="Please enter the pickup zipcode"> --->
		<input type="hidden" name="Vehicle_destination_city_required" value="Please enter the destination city">
		<input type="hidden" name="Vehicle_destination_state_required" value="Please enter the destination state">
		<!--- <input type="hidden" name="Vehicle_destination_zipcode_required" value="Please enter the destination zipcode"> --->
		<input type="hidden" name="move_month_required" value="Please enter the month of the move">
		<input type="hidden" name="move_day_required" value="Please enter the day of the move">
		<input type="hidden" name="move_year_required" value="Please enter the year of the move">
        <input type="hidden" name="email2" type="text" id="email2"  value="<?php echo htmlspecialchars(getParam('email2')); ?>" tabindex="18" />
		<div id="page_title">Free Moving Quotes</div>
		<table width="750">
		<tr>
		<td valign="top" width="325">
		<div style="width:325px; margin-left:5px;">
			<div class="info_title" >Moving Information</div>
			<div class="info_container" >
				<div class="inner_title" >Pick Up</div>
				<div class="inner_cont" >
					<table cellpadding="0" cellspacing="0">
						<tr>
							<td><div class="form_title">*City:</div></td>
							<td><div class="form_title">State:</div></td>
							<td><div class="form_title">Zip Code:</div></td>
						</tr>
						<tr>
							<td><div class="form_element"><input type="text" name="vehicle_pickup_location" value="<?php echo htmlspecialchars(getParam('vehicle_pickup_location')); ?>" id="vehicle_pickup_location" size="15" /></div></td>
							<td><div class="form_element"><input type="text" name="vehicle_pickup_state" id="vehicle_pickup_state" value="<?php echo htmlspecialchars(getParam('vehicle_pickup_state')); ?>" size="2" /></div></td>
							<td><div class="form_element"><input type="text" name="vehicle_pickup_zipcode" size="5" value="<?php echo htmlspecialchars(getParam('vehicle_pickup_zipcode')); ?>" /></div></td>
						</tr>
						<tr>

						</tr>
					</table>
				</div>
				<div class="inner_title" >Destination</div>
					<div class="inner_cont" >
					<table cellpadding="0" cellspacing="0">
						<tr>
							<td><div class="form_title">*City:</div></td>
							<td><div class="form_title">State:</div></td>
							<td><div class="form_title">Zip Code:</div></td>
						</tr>
						<tr>
							<td><div class="form_element"><input type="text" name="vehicle_destination_city" id="vehicle_destination_city"  value="<?php echo htmlspecialchars(getParam('vehicle_destination_city')); ?>" size="15" /></div></td>
							<td><div class="form_element"><input type="text" name="vehicle_destination_state" id="vehicle_destination_state" value="<?php echo htmlspecialchars(getParam('vehicle_destination_state')); ?>" size="2" /></div></td>
							<td><div class="form_element"><input type="text" name="vehicle_destination_zipcode" id="vehicle_destination_zipcode" value="<?php echo htmlspecialchars(getParam('vehicle_destination_zipcode')); ?>" size="5" /></div></td>
						</tr>
					</table>
				</div>
				<div class="form_row">*Estimated move date:
						<input title="Please select the date for your vehicle pickup" class="datep" name="move_date" type="text" id="move_date" value="<?php echo htmlspecialchars($arrVals['move_date']); ?>" maxlength="10" tabindex='15' width="50" /></p>
															<script type="text/javascript">
			Calendar.setup( { inputField	:	"move_date", ifFormat	:	"%m/%d/%Y",	button	:	"move_date" } );
        </script>
			  </div>
					<div class="form_row">Are you flexible on this move date:
						<select name="flexable_move">
							<option value="No">No</option>
							<option value="Yes">Yes</option>
						</select>
					</div>
		   <!--	 <div class="form_row">Carrier Type Requested:
						<select tabindex="5" name="carrier_type" id="carrier_type">
						<option value="Any"<?php if (getParam('carrier_type') == 'Any') echo ' selected="selected"'; ?>>Any</option>
																<option value="Open"<?php if (getParam('carrier_type') == 'Open') echo ' selected="selected"'; ?>>Open</option>
																<option value="Enclosed"<?php if (getParam('carrier_type') == 'Enclosed') echo ' selected="selected"'; ?>>Enclosed</option>
					</select>
			  </div> -->
			</div>


				<div class="info_title" >Additional Comments:</div>

				<div class="info_container" >
				  <div align="center"><textarea name="comments" id="comments" rows=5 cols=38><?php echo htmlspecialchars(getParam('comments')); ?></textarea></div>
				</div>
		</div>
		</td>
		<td valign="top"  width="325">
		<div class="left_contact">
			<div class="info_title" >Moving Information</div>
			<div class="info_container" >
					<table cellpadding="0" cellspacing="0">
						<tr>
							<td><div class="form_title"></div></td>
							<td><div class="form_title"></div></td>
							<td><div class="form_title"></div></td>
						</tr>
						<tr>
							<td><div class="form_element"><input name="vehicle_year" type="hidden" id="vehicle_year" size="4" value="" /> *Rooms To Move:</div></td>
							<td><div class="form_element"><input name="vehicle_make" type="text" id="vehicle_make" size="10" value="<?php echo htmlspecialchars(getParam('vehicle_make')); ?>" /></div></td>
							<td><div class="form_element"><input name="vehicle_model" type="hidden" id="vehicle_model" size="10" value="" /></div></td>
						</tr>
					</table>
					<br class="br_line"/>
					<div class="form_title">
				 <!--	Motorcycle Condition:
						<select tabindex="9" name="vehicle_condition1" id="vehicle_condition1">
																	<option value="Operational"<?php if (getParam('vehicle_condition1') == 'Operational') echo ' selected="selected"'; ?>>Operational</option>
																	<option value="Rolling, Non-Operational"<?php if (getParam('vehicle_condition1') == 'Rolling, Non-Operational') echo ' selected="selected"'; ?>>Rolling, Non-Operational</option>
																	<option value="Non-Operational"<?php if (getParam('vehicle_condition1') == 'Non-Operational') echo ' selected="selected"'; ?>>Non-Operational</option>
						   -->										</select>
					</div >

			</div>
			<div class="info_title" >Contact Information</div>
			<div class="info_container" style="min-height:100px" >
				<div style="float:right; width:95px; ">
				<img src="images/privacy_protection.png" alt="Privacy Protected" />
				</div>
				<div style="float:left; width:225px; ">
				<table width="170">
					<tr>
						<td><div class="form_title_nw">*Name: </div></td>
						<td><div class="form_element"><input name="customername" type="text" id="customername" size="20" value="<?php echo htmlspecialchars(getParam('customername')); ?>" /></div></td>
					</tr>
					<tr>
						<td><div class="form_title_nw">*Phone: </div></td>
						<td><div class="form_element"><input name="phone" type="text" id="phone" size="20" value="<?php echo htmlspecialchars(getParam('phone')); ?>" /></div></td>
					</tr>
					<tr>
						<td><div class="form_title_nw">*Email: </div></td>
						<td><div class="form_element"><input name="email" type="text" id="email" size="20" value="<?php echo htmlspecialchars(getParam('email')); ?>" /></div></td>
					</tr>
				</table>
				</div>
			</div>
		</div>
		<br><div style="width:100; margin-left:130px;"><input class="submit" type="submit" name="submit" id="submit" value="" tabindex="21" align="top"/>
         </div><div style="text-align:center;  "><span class="c7"><br>Please click only once. It may take a few seconds to process</span>
            </div>

		</td></tr></table>
        <input type="hidden" name="frmSubmit" value="true" />
								<?php
								$dataArray = array();
								if (!ARE_WE_LIVE) $dataArray['debug'] = $gDebug;
								writeHiddenFormFields($dataArray);
								?>

	  </form>
      <script type="text/javascript">
jQuery(function () {
    jQuery('#submit').click();
});

</script>
</div>
</div>
<script type="text/javascript">
  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-34289413-1']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>
<script type="text/javascript">var switchTo5x=true;</script><script type="text/javascript" src="http://w.sharethis.com/button/buttons.js"></script><script type="text/javascript">stLight.options({publisher:'56f2c973-5133-493d-9c81-8f4c3bb82851'});</script>
</body></html>
