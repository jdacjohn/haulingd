<?php
require_once('configs/vehicle/config.inc.php');
require_once('_includes/noconfig.project.inc.php');
?>
<?php
//require_login();

$db = &connectToDB();
if($gDebug) $db->debug = true;

$_POST['move_date'] = $_POST['move_date_submit'];
$_POST['vehicle_pickup_location'] = $_POST['fromCity'];
$_POST['vehicle_destination_city'] = $_POST['toCity'];

$_POST['vehicle_pickup_state'] = format_state($_POST['fromState'], 'abbr');
$_POST['vehicle_destination_state'] = format_state($_POST['toState'], 'abbr');

function format_state( $input, $format = '' ) {
        if( ! $input || empty( $input ) )
            return;

        $states = array (
            'AL'=>'Alabama',
            'AK'=>'Alaska',
            'AZ'=>'Arizona',
            'AR'=>'Arkansas',
            'CA'=>'California',
            'CO'=>'Colorado',
            'CT'=>'Connecticut',
            'DE'=>'Delaware',
            'DC'=>'District Of Columbia',
            'FL'=>'Florida',
            'GA'=>'Georgia',
            'HI'=>'Hawaii',
            'ID'=>'Idaho',
            'IL'=>'Illinois',
            'IN'=>'Indiana',
            'IA'=>'Iowa',
            'KS'=>'Kansas',
            'KY'=>'Kentucky',
            'LA'=>'Louisiana',
            'ME'=>'Maine',
            'MD'=>'Maryland',
            'MA'=>'Massachusetts',
            'MI'=>'Michigan',
            'MN'=>'Minnesota',
            'MS'=>'Mississippi',
            'MO'=>'Missouri',
            'MT'=>'Montana',
            'NE'=>'Nebraska',
            'NV'=>'Nevada',
            'NH'=>'New Hampshire',
            'NJ'=>'New Jersey',
            'NM'=>'New Mexico',
            'NY'=>'New York',
            'NC'=>'North Carolina',
            'ND'=>'North Dakota',
            'OH'=>'Ohio',
            'OK'=>'Oklahoma',
            'OR'=>'Oregon',
            'PA'=>'Pennsylvania',
            'RI'=>'Rhode Island',
            'SC'=>'South Carolina',
            'SD'=>'South Dakota',
            'TN'=>'Tennessee',
            'TX'=>'Texas',
            'UT'=>'Utah',
            'VT'=>'Vermont',
            'VA'=>'Virginia',
            'WA'=>'Washington',
            'WV'=>'West Virginia',
            'WI'=>'Wisconsin',
            'WY'=>'Wyoming',
        );

        foreach( $states as $abbr => $name ) {
            if ( preg_match( "/\b($name)\b/", ucwords( strtolower( $input ) ), $match ) )  {
                if( 'abbr' == $format ){
                    return $abbr;
                }
                else return $name;
            }
            elseif( preg_match("/\b($abbr)\b/", strtoupper( $input ), $match) ) {
                if( 'abbr' == $format ){
                    return $abbr;
                }
                else return $name;
            }
        }
        return;
    }

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
    newFieldArray('moving', 'checkbox', '', '', false, false),
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
		        	$arrErr[] = form_error('Make sure the email you entered is valid.', 'email');
		}
        /*
		if (!(strlen(trim($arrVals['phone']))) && !check_phone(trim($arrVals['phone']))) {
			$arrErr[] = form_error('Please enter a valid 10-digit phone number. (i.e. 123-456-7890)', 'phone');
		}
       */

	if (!(strlen(trim($arrVals['vehicle_year'])) && is_numeric(trim($arrVals['vehicle_year'])))) {
		$arrErr[] = form_error('Please enter a valid year for the primary vehicle. (ex: 1997, 97, 07, etc.)', 'vehicle_year', 'year');
	}

	if (!strlen(trim($arrVals['vehicle_make']))) {
		$arrErr[] = form_error('Please enter the make for the primary vehicle.', 'vehicle_make', 'make');
	}

	if (!strlen(trim($arrVals['vehicle_model']))) {
		$arrErr[] = form_error('Please enter the model for the primary vehicle.', 'vehicle_model', 'model');
	}

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
		$arrErr[] = form_error('Please enter a valid move date.', 'move_date', 'move date');
	}
		if($arrVals['phone']){
	$phone_number_formatted = preg_replace("([()-])", "", $arrVals['phone']);
	//echo $phone_number_formatted;
	$area_code = substr($phone_number_formatted, 0, 3);
	$prefix = substr($phone_number_formatted, 3, 3);
    /*
	$sql = 'SELECT * FROM areacodes WHERE area_code = '.$area_code.' AND prefix = '.$prefix;
	$result = mysql_query($sql) or die("Couldn't execute that query.".mysql_error());
	$num_rows = mysql_num_rows($result);
	if($num_rows==0){
		$arrErr[] = form_error('Please ensure you entered a correct area code and prefix.', 'phone','area code and prefix');
	}
    */
	//echo $sql;

}

}//end validation
if ($gDebug) printvar($arrVals, 'arrVals');

if (getParam('frmSubmit') == 'true' && sizeof($arrErr) == 0) {

rename('_config/config.inc.php', '_config/temp.inc.php');
copy('configs/vehicle/config.inc.php', '_config/config.inc.php');
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
//$arrVals['move_date'] = date('m/d/Y', time()+86400);
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
}
header( 'Location: http://www.haulingdepot.com/confirm.php?passcode='.$password.'&email='.$arrVals['email'].'' ) ;
}

elseif (getParam('frmSubmit') != 'true') {
	//set default date
	$arrVals['move_date'] = date('m/d/Y', time()+86400);
} //END: if (getParam('frmSubmit') == 'true' && sizeof($arrErr) == 0))
?>

<!DOCTYPE html>
<!--[if IE 7 ]><body class="ie ie7"><![endif]-->
<!--[if IE 8 ]><body class="ie ie8"><![endif]-->
<!--[if IE 9 ]><body class="ie ie9"><![endif]-->
<html class='no-js' lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0, user-scalable=no, maximum-scale=1.0">
	<title>Vehicle Shipping & Car Shipping Quotes | Free Auto Transport Quotes</title>
<meta name="Description" content="Get 5 Vehicle Shipping Quotes & Save Time and Money on Car Shipping. Instant Auto Transport Quotes Up To 50% Off Standard Rates. Call 877-619-0710">
<meta name="keywords" content="auto shipping quotes, car transport, car shipping, auto transport, car transporters, motorcycle shipping, auto transporters, car shippers, car shipping quotes, vehicle shipping quotes, auto shippers, car hauling, vehicle transport, motorcycle transport">
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"><meta name="robots" content="index,follow">
<link rel="canonical" href="http://www.haulingdepot.com/" />

	<link type="image/x-icon" href="img/favicon.ico" rel="shortcut icon">
	<link rel="stylesheet" href="css/bootstrap.css">
	<link rel="stylesheet" href="css/font-awesome.css">
	<link rel="stylesheet" href="css/main.css">
	<link rel="stylesheet" href="css/responsive.css">
    <style>
    .notice{
        color:#FFFFFF;
        text-align: center;
    }
    [required] {
    color:red;
    box-shadow: none;
}
    </style>

</head>
<body>
<!--===========================-->
<!--==========Header===========-->
<div id="preloader">
	<div id="status">
		<div class="spinner">
			<div class="bounce1"></div>
			<div class="bounce2"></div>
			<div class="bounce3"></div>
		</div>
	</div>
</div>

<div class="main-holder">
<header class='main-wrapper header'>
	<div class="container apex">
		<div class="row">

			<nav class="navbar header-navbar" role="navigation">
				<!-- Brand and toggle get grouped for better mobile display -->
				<div class="navbar-header">
					<div class="logo navbar-brand">
						<a href="index.php" title="Hauling Depot"></a>
					</div>
		      <button class='toggle-slide-left visible-xs collapsed navbar-toggle' type="button" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1"><i class="fa fa-bars"></i></button>
				</div>

		    <!-- Collect the nav links, forms, and other content for toggling -->
		    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
					<div class="navbar-right">
						<nav class='nav-menu navbar-left main-nav trig-mob slide-menu-left'>
							<ul class='list-unstyled'>
                           <!-- <li>
									<a href="#" data-scroll="features">
										<div class="inside">
											<div class="backside"> The HD Advantage </div>
											<div class="frontside"> The HD Advantage </div>
										</div>
									</a>
								</li> -->
                            <li>
									<a href="#" data-scroll="information">
										<div class="inside">
											<div class="backside"> How It Works </div>
											<div class="frontside"> How It Works </div>
										</div>
									</a>
								</li>

                                <li>
									<a href="#" data-scroll="services">
									<div class="inside">
										<div class="backside"> Services </div>
										<div class="frontside"> Services </div>
									</div>
									</a>
								</li>

								<li>
									<a href="#" data-scroll="testimonials">
									<div class="inside">
										<div class="backside"> Testimonials </div>
										<div class="frontside"> Testimonials </div>
									</div>
									</a>
								</li>
								<li>
									<a href="#" data-scroll="gallery">
										<div class="inside">
											<div class="backside"> Gallery </div>
											<div class="frontside"> Gallery </div>
										</div>
									</a>
								</li>
								<li>
									<a data-toggle="modal" role="button" href="#myModal">
										<div class="inside">
											<div class="backside"> Contact </div>
											<div class="frontside"> Contact </div>
										</div>
									</a>
								</li>
							</ul>
						</nav>
						<div class="wr-soc" style="position:relative;top:-28px;">
                        <div class="header-social">
						   <img src="img/contact.png" alt="Call Us" />
                           </div>
						</div>
                       <!-- <div class="wr-soc">
							<div class="header-social">
								<ul class='social-transform unstyled'>
								<li>
									<a href='#' target='blank' class='front'><div class="fa fa-facebook"></div></a>
								</li>
								<li>
									<a href='#' target='blank' class='front'><i class="fa fa-twitter"></i></a>
								</li>
								<li>
									<a href='#' target='blank' class='front'><i class="fa fa-google-plus"></i></a>
								</li>
								<li>
									<a href='#' target='blank' class='front'><i class='fa fa-vimeo-square'></i></a>
								</li>
								</ul>
							</div>
						</div>
                        -->
					</div>
		    </div><!-- /.navbar-collapse -->
			</nav>

		</div>
	</div>
</header>


<!--===========================-->
<!--==========Content==========-->
<div class='main-wrapper content'>
	<section class="relative software_slider" style="padding-top:105px;background: url('http://image.motortrend.com/f/wot/1312_refreshing_or_revolting_2015_ford_mustang/53828537/2014-ford-mustang-three-quarters.jpg') no-repeat fixed 0px -200px / 100% auto">
		<div class="forma-slider">
			<div class="container">
				<div class="row">
					<div data-anchor="form_slider" id="form_slider">

						<div class="bx-wrapper" style="max-width: 100%;"><?php include(WEB_ROOT.PROJECT_DIR.'/_includes/default-page-error-handler.inc.php'); ?><div style="width: 100%; overflow: hidden; position: relative; height: 480px;" class="bx-viewport">
              <form action="contactform.php" method="post"><table style="width:100%;background-color:rgba(0, 0, 0, 0.3);border-radius:14px;color:#FFFFFF;text-align:center;"><tbody><tr style="height:100px;vertical-align:top;"><td style="width:33%"><h1>Vehicle Information</h1></td><td style="width:33%"><h1>Trip Information</h1></td><td style="width:34%"><h1>Contact Details</h1></td></tr>
                <tr style="vertical-align:top;">
                  <td><center><div class="relative" style="width: 80%;">

										<select name="vehicle_year" id="car-years" class="styled" style="color:#717171;" required="required">
                                        <option value="SelectYear">Select Year</option>
                                        <?php if(isset($_POST['vehicle_year'])) {echo "<option selected='selected' value='" . htmlspecialchars(getParam('vehicle_year')) . "'>" . htmlspecialchars(getParam('vehicle_year')) . "</option>";  } ?>
                                        </select>

									</div>
                                    <br><br><div class="relative" style="width: 80%;">
										<select name="vehicle_make" id="car-makes" class="styled" style="color:#717171;" required="required">
                                        <option value="SelectYear">Select Make</option>
                                        <?php if(isset($_POST['vehicle_make'])) {echo "<option selected='selected' value='" . htmlspecialchars(getParam('vehicle_make')) . "'>" . htmlspecialchars(getParam('vehicle_make')) . "</option>";  } ?>
                                        </select></div>
                                    <br><br>
                                    <div class="relative" style="width: 80%;">
									    <select name="vehicle_model" id="car-models" class="styled" style="color:#717171;" required="required">
                                        <option value="SelectYear">Select Model</option>
                                         <?php if(isset($_POST['vehicle_model'])) {echo "<option selected='selected' value='" . htmlspecialchars(getParam('vehicle_model')) . "'>" . htmlspecialchars(getParam('vehicle_model')) . "</option>";  } ?>
                                        </select>
                                        </div><br><br><br>

                    <div class="relative" style="width: 80%;">
										<select class="styled" style="color:#717171;" name="vehicle_condition1">
                                            <option value="Operational">Select Vehicle Condition:</option>
											<option value="Operational" <?php if (getParam('vehicle_condition1') == 'Operational') echo ' selected="selected"'; ?>>Operational</option>
                                            <option value="Rolling, Non-Operational" <?php if (getParam('vehicle_condition1') == 'Rolling, Non-Operational') echo ' selected="selected"'; ?>>Rolling, Non-Operational</option>
											<option value="Non-Operational" <?php if (getParam('vehicle_condition1') == 'Non-Operational') echo ' selected="selected"'; ?>>Non-Operational</option>
										</select>
									</div>
                    </center></td>


                  <td><div class="control-group">
                                       <input type="text" autocomplete="off" name="geocomplete" id="geocomplete" class="controls" placeholder="From Zipcode or City/State" style="width:80%" value="<?php if (isset($_POST['geocomplete']) && !empty($_POST['geocomplete'])) { echo $_POST['geocomplete']; } ?>" required="required">
                                       <br><a href="international-shipping.php" style="color:#FFF;font-size:10px;position:relative;top:-10px;float:left;left:10%"><img src="img/global.png" style="width:18px;float:left;position:relative;float:left;">Click here if you are shipping from outside the United States</a>
                                       <div class="fromDetails">
        <input type="text" id="fromZip" value="<?php if (isset($_POST['fromZip']) && !empty($_POST['fromZip'])) { echo $_POST['fromZip']; } ?>" name="postal_code" hidden="hidden">

        <input type="text" id="fromCity" value="<?php if (isset($_POST['fromCity']) && !empty($_POST['fromCity'])) { echo $_POST['fromCity']; } ?>" name="locality" hidden="hidden">

        <input type="text" id="fromCountry" value="<?php if (isset($_POST['fromCountry']) && !empty($_POST['fromCountry'])) { echo $_POST['fromCountry']; } ?>" name="country_short" hidden="hidden">

        <input type="text" id="fromState" value="<?php if (isset($_POST['fromState']) && !empty($_POST['fromState'])) { echo $_POST['fromState']; } ?>" name="administrative_area_level_1" hidden="hidden">
        </div>
                                       <br><br><center><img src="https://dev.vaadin.com/svn/doc/book-examples/branches/vaadin-7/WebContent/VAADIN/themes/book-examples/icons/vaadin-arrow-down-white.png"></center><br><br>
                                               <div class="toDetails">
        <input type="text" id="toZip" value="<?php if (isset($_POST['toZip']) && !empty($_POST['toZip'])) { echo $_POST['toZip']; } ?>" name="postal_code" hidden="hidden">

        <input type="text" id="toCity" value="<?php if (isset($_POST['toCity']) && !empty($_POST['toCity'])) { echo $_POST['toCity']; } ?>" name="locality" hidden="hidden">

        <input type="text" id="toCountry" value="<?php if (isset($_POST['toCountry']) && !empty($_POST['toCountry'])) { echo $_POST['toCountry']; } ?>" name="country_short" hidden="hidden">

        <input type="text" id="toState" value="<?php if (isset($_POST['toState']) && !empty($_POST['toState'])) { echo $_POST['toState']; } ?>" name="administrative_area_level_1" hidden="hidden">
        </div>
                                       <input type="text" autocomplete="off" name="geocomplete2" id="geocomplete2" class="controls" placeholder="To Zipcode or City/State" style="width:80%" value="<?php if (isset($_POST['geocomplete2']) && !empty($_POST['geocomplete2'])) {  echo $_POST['geocomplete2']; } ?>" required="required">
                                       <br><a href="international-shipping.php" style="color:#FFF;font-size:10px;position:relative;top:-10px;"><img src="img/global.png" style="width:18px;float:left;position:relative;top:-9px;left:10%">Click here if you are shipping to outside the United States</a>
                                       <br><br><br><input type="text" class="datepicker" name="move_date" type="text" id="move_date" style="width:80%" placeholder="Estimated Move Date" value="" data-value="<?php echo htmlspecialchars(getParam('move_date')); ?>" required="required">


                            </div></td><td>


                        <!--    <div class="relative">
										<select style="width: 340px; position: absolute; opacity: 0; height: 44px; font-size: 12px;" name="caoch" class="styled hasCustomSelect">
											<option value="Select Your Caoch">I am shipping a...</option>
											<option value="Vehicle">Vehicle</option>
											<option value="MC">Motorcycle</option>
											<option value="Boat">Boat</option>
                                            <option value="RV">RV</option>
										</select><span style="display: inline-block;" class="customSelect styled"><span style="width: 326px; display: inline-block;" class="customSelectInner">I am shipping a...</span></span>
									</div>-->
                                 <!--   <div class="relative">
										<select name="caoch" class="styled">
											<option value="Select Your Caoch">Make</option>
											<option value="Vehicle" >Vehicle</option>
											<option value="MC" >Motorcycle</option>
											<option value="Boat" >Boat</option>
                                            <option value="RV" >RV</option>
										</select>
									</div>
                                    <div class="relative">
										<select name="caoch" class="styled">
											<option value="Select Your Caoch">Model</option>
											<option value="Vehicle" >Vehicle</option>
											<option value="MC" >Motorcycle</option>
											<option value="Boat" >Boat</option>
                                            <option value="RV" >RV</option>
										</select>
									</div>      -->
                                         <div class="control-group">
                                         <input type="text" autocomplete="off" id="fullName" class="controls" placeholder="Full Name" style="width:80%" name="customername" value="<?php echo htmlspecialchars(getParam('customername')); ?>" required="required">
                                         <input type="text" autocomplete="off" id="phone" class="controls" placeholder="Phone Number" style="width:80%" name="phone" value="<?php echo htmlspecialchars(getParam('phone')); ?>" required="required"> <!-- data-pattnern="^[0-9]+$" -->
                                           <input type="text" autocomplete="off" id="email" class="controls" placeholder="Email Address" style="width:80%" name="email" value="<?php echo htmlspecialchars(getParam('email')); ?>" required="required">  <!-- data-pattern="^[-a-z0-9!#$%&'*+/=?^_`{|}~]+(.[-a-z0-9!#$%&'*+/=?^_`{|}~]+)*@([a-z0-9]([-a-z0-9]{0,61}[a-z0-9])?.)*(aero|arpa|asia|biz|cat|com|coop|edu|gov|info|int|jobs|mil|mobi|museum|name|net|org|pro|tel|travel|[a-z][a-z])$" -->
                                            <br><input type="checkbox" value="checked" id="moving" name="moving" <?php echo htmlspecialchars(getParam('moving')); ?>><span style="font-family:'Times New Roman';font-size:17px;"> Please also send me a home mover quote.</span><br><br>
                                           <textarea autocomplete="off" id="comments" class="controls" placeholder="Comments" style="width:80%" name="comments"><?php echo htmlspecialchars(getParam('comments')); ?></textarea><br>
                            </div>
                              <!--   <div class='control-group'>
									<input type="text" name="toZipcode" value="" placeholder='Destination Zipcode' data-required>
								</div>

								<div class='control-group'>
									<input type="text" name="email" value="" class='insert-attr' placeholder='Enter your mail' data-required>
								</div>
								<div class='control-group'>
									<input type="text" name="phone" value="" placeholder='Enter your telephone' data-required data-pattern="^[0-9]+$">
								</div>




									<div class="relative">
										<select name="file" class="styled">
											<option value="Select File">Select File</option>
											<option value="File 1">File 1</option>
											<option value="File 2">File 2</option>
											<option value="File 3">File 3</option>
										</select>
									</div>  -->
<center>
								 <button type="submit" value="" class="btn submit sub-form" name="submit" style="width:80%">Generate my free quotes now</button><br></center>
                                 <input type="hidden" name="frmSubmit" value="true" />
							</td></tr></tbody></table><div class="bx-controls bx-has-pager bx-has-controls-direction" id="dafault_pager">

						</div><div class="clearfix visible-xs visible-md"></div><table>








				</table></form></div><br></div></div></div>
			</div><!-- end container -->
		</div>
	</section>

	<section class='dark-blue'>
		<div class="container make-row">
			<div class="row">
				<div class="col-md-3 col-sm-6 make-md">
					<h4 class='division-h sem-h' id='animIt1'><img src="img/ebay-motors.png" alt="Ebay Motors" /></h4>
				</div>

				<ul class='list-unstyled seminars'>
					<li id='animIt2' class='col-md-3 col-sm-6'>
						<div class="media">
							<h4 class='division-h'><img src="img/carfax.png" alt="Carfax" /></h4>
						</div>
					</li>
					<li id='animIt3' class='col-md-3 col-sm-6'>
						<img src="img/fmcsa.png" alt="FMCSA" />
					</li>
					<li id='animIt4' class='col-md-3 col-sm-6'>
					   <img src="img/reviews.png" alt="Reviews" style="position:relative;top:-15px" />
					</li>
				</ul>
			</div>
		</div>
	</section>

	<section class="container" data-anchor="features">
		<div class="spacer6"></div>
			<h2 class='text-center xxh-Bold'>The HD Advantage</h2>
			<h3 class='text-center xmedium-h'></h3>
			<div class="row trainings" id='trainings'>
				<div class="col-md-4 col-xs-6 hov1">
					<figure class='thumbnails'>
						<i class='fa fa-shield'></i>
					</figure>
					<h4 class='xxsmall-h text-center transition-h'>Request a Quote</h4>
					<div class="full-text">
						Secure, Quick, & Spam Free Guaranteed.
					</div>
				</div>

				<!--<div class="col-md-3 col-xs-6 hov2">
					<figure class='thumbnails'>
						<i class='fa fa-heart-o'></i>
					</figure>
					<h4 class='xxsmall-h text-center transition-h'>Best Learning Programs</h4>
					<div class="full-text">
						Nulla ornare tortor quis rhoncus vulputate. Quisque vehicula quis sapien a accumsan
					</div>
				</div> -->

				<div class="col-md-4 col-xs-6 hov3">
					<figure class='thumbnails'>
						<i class='fa fa-refresh'></i>
					</figure>
					<h4 class='xxsmall-h text-center transition-h'>We Match You</h4>
					<div class="full-text">
						Our sophisticated engine matches you with service providers that you can sort by price, ratings, and reviews.
					</div>
				</div>

				<div class="col-md-4 col-xs-6 hov4">
					<figure class='thumbnails'>
						<i class='fa fa-book'></i>
					</figure>
					<h4 class='xxsmall-h text-center transition-h'>Instant Price Quotes</h4>
					<div class="full-text">
						Your service provider quotes are generated with moments of your submission for you to review.
					</div>
				</div>
			</div>
		<div class="offsetY-4"></div>
	</section>

	<section class="bg-darkblue">
    <h3 class='slide-title text-center'>How It Works</h3><br><br><br>
		<div class="container make-row" data-anchor="information">
			<div class="row">
				<div class="col-sm-6 media-wr" id='animIt5'>
					<figure class='media-news'>
						<a href='img/search-transport-companies.jpg' class="group1" title='Search Auto Transport Comapnies'>
							<img src="img/search-transport-companies.jpg" alt="alt..." >
							<i class="zoom-ico"></i>
						</a>
					</figure>
				</div>

				<div class="col-sm-6" id='animIt6'>
					<h2 class='xh-Bold'>Search for a transport company online</h2>
					<div class="excerpt">
						Here at Hauling Depot we make sure that after filling out one short form you get a clear view of the best options the market has to offer. We make it simple to make your choice over price, user reviews, and the many other service benefits vehicle shipping companies offer.
					</div>
					<a href="#" class='more'>
						<div class="inside">
							<div class="backside"> Get free online quotes now </div>
							<div class="frontside"> Get free online quotes now </div>
						</div>
					</a>
				</div>
			</div>
			<div class="spacer2"></div>
		</div>

		<div class="container make-row">
			<div class="row">
				<div class="col-sm-6" id='animIt7'>
					<h2 class='xh-Bold'>Schedule the pickup</h2>
					<div class="excerpt">
						Contact your service provider online or over the phone to easily schedule a pickup at a time and day that's most convenient for you.
					</div>
					<!--<a href="#" class='more'>
						<div class="inside">
							<div class="backside"> View more Videos </div>
							<div class="frontside"> View more Videos </div>
						</div>
					</a>-->
					<div class="spacer2"></div>
				</div>

				<div class="col-sm-6 media-wr" id='animIt8'>
					<figure class='media-news'>
						<a href="img/shutterstock_101235769.jpg" class="group3" title='Schedule Vehicle Shipping'>
							<img src="img/shutterstock_101235769.jpg" alt="Schedule Vehicle Shipping" >
							<i class="zoom-icoBw"></i>
						</a>
					</figure>
				</div>
			</div>
			<div class="spacer2"></div>
		</div>

		<div class="container make-row">
			<div class="row">
				<div class="col-sm-6 media-wr" id='animIt9'>
					<figure class='media-news'>
						<a href="img/vehicle-transit.jpg" class="group3" title='Vehicle Shipping Transit'>
							<img src="img/vehicle-transit.jpg" alt="Vehicle Shipping Transit" >
							<i class="zoom-icoBw"></i>
						</a>
					</figure>
				</div>

				<div class="col-sm-6" id='animIt10'>
					<h2 class='xh-Bold'>Your Vehicle, In Transit</h2>
					<div class="excerpt">
						All you have to do now is just wait for your vehicle to arrive. All of our service providers are bonded and insured, giving you peace of mind that your vehicle is in good hands and will arrive safe and sound.<!--<a href="#" class='more'>
						<div class="inside">
						   <div class="backside"> View Training Program (pdf - 453K) </div>
							<div class="frontside"> View Training Program (pdf - 453K) </div>
						</div>
					</a>-->
				</div>
			</div>
		</div>
	</section>

		<section class="container" data-anchor="services">
		<div class="spacer6"></div>
			<h2 class='text-center xxh-Bold'>The Services We Offer</h2>
			<h3 class='text-center xmedium-h'></h3>
			<div class="row trainings" id='trainings1'>
				<div class="col-md-3 col-xs-6 hov1">
					<figure class='thumbnails'>
						<i><img src="img/sprite/carW.svg" style="width:90px;position:relative;top:-5px;" alt="" /> </i>
					</figure>
					<h4 class='xxsmall-h text-center transition-h'>Vehicle</h4>
					<div class="full-text">
						Nulla ornare tortor quis rhoncus vulputate. Suspendisse commodo fringilla tellus vitae facilisis.
					</div>
				</div>

				<div class="col-md-3 col-xs-6 hov2">
					<figure class='thumbnails'>
						<i><img src="img/sprite/mcW.svg" style="width:90px;position:relative;top:-5px;" alt="" /> </i>
					</figure>
					<h4 class='xxsmall-h text-center transition-h'>Motorcycle</h4>
					<div class="full-text">
						Nulla ornare tortor quis rhoncus vulputate. Quisque vehicula quis sapien a accumsan
					</div>
				</div>

				<div class="col-md-3 col-xs-6 hov3">
					<figure class='thumbnails'>
						<i><img src="img/sprite/boatW.svg" style="width:90px;position:relative;top:-5px;" alt="" /> </i>
					</figure>
					<h4 class='xxsmall-h text-center transition-h'>Boat</h4>
					<div class="full-text">
						Nulla ornare tortor quis rhoncus vulputate. Fusce enim erat, volutpat id nisi quis, blandit sodales est
					</div>
				</div>

				<div class="col-md-3 col-xs-6 hov4">
					<figure class='thumbnails'>
						<i><img src="img/sprite/rvW.svg" style="width:90px;position:relative;top:-5px;" alt="" /> </i>
					</figure>
					<h4 class='xxsmall-h text-center transition-h'>RV</h4>
					<div class="full-text">
						Nulla ornare tortor quis rhoncus vulputate. Vivamus a enim
					</div>
				</div>
			</div>
		<div class="offsetY-4"></div>
	</section>

	<section id="aboutUs_slider" data-anchor="testimonials">
		<h3 class='slide-title text-center'>What people say about us?</h3>
		<h4 class='xxmedium-h text-center'>Testimonals of our Customers </h4>
		<div class="container">
			<ul class="aboutUs-slider unstyled">
				<li>
					<figure class="thumbnail">
						<a href="#">
							<img src="img/Jack.png" alt="Jack">
						</a>
					</figure>
					<div class="quote">
					The convinience while moving was absolutely worth every dollar I spent. The competative bids I got from haulingdepot.com made sure I got the best deal. Thanks.
					</div>
					<span class="author">Jack P.</span>
				</li>
                <li>
					<figure class="thumbnail">
						<a href="#">
							<img src="img/staticks/user2.png" alt="Sarah">
						</a>
					</figure>
					<div class="quote">
					Using hauling depot gave me options so I could easily select the most punctual & reliable shipper for the best price. This is how moving your car should be.
					</div>
					<span class="author">Sarah E. </span>
				</li>
				<li>
					<figure class="thumbnail">
						<a href="#">
							<img src="img/James.png" alt="James">
						</a>
					</figure>
					<div class="quote">
					Reviews were super helpful. Was very easy to choose the best shipper for the best price.
					</div>
					<span class="author">James B.</span>
				</li>

			</ul>
		</div>
	</section>

   <!--	<section class="container make-row" data-anchor="gallery">
		<h2 class='text-center xxh-Bold'>See Our Gallery</h2>
		<h3 class='text-center xmedium-h'></h3>

		<div class="row" id='gallery'>
			<div class="col-md-4 col-sm-6 animIt14">
				<figure class='media-news'>
					<a href="img/gallery1.jpg" class="group2 bwWrapper">
						<img src="img/gallery1.jpg" alt="Gallery 1" style="width:370px;height:183px;" >
						<i class="zoom-icoBw"></i>
					</a>
				</figure>
			</div>

			<div class="col-md-4 col-sm-6 animIt14">
				<figure class='media-news'>
					<a href="img/Ashburn.jpg" class="group2 bwWrapper">
						<img src="img/Ashburn.jpg" alt="Gallery 2" style="height:183px;width:370px;" >
						<i class="zoom-icoBw"></i>
					</a>
				</figure>
			</div>

			<div class="col-md-4 col-sm-6 animIt14">
				<figure class='media-news'>
					<a href="img/staticks/fullsize/img_1.jpg" class="group2 bwWrapper">
						<img src="img/staticks/pop1.jpg" alt="alt..." >
						<i class="zoom-icoBw"></i>
					</a>
				</figure>
			</div>

			<div class="col-md-4 col-sm-6 animIt15">
				<figure class='media-news'>
					<a href="img/staticks/fullsize/img_1.jpg" class="group2 bwWrapper">
						<img src="img/staticks/pop1.jpg" alt="alt..." >
						<i class="zoom-icoBw"></i>
					</a>
				</figure>
			</div>

			<div class="col-md-4 col-sm-6 animIt15">
				<figure class='media-news'>
					<a href="img/staticks/fullsize/img_1.jpg" class="group2 bwWrapper">
						<img src="img/staticks/pop1.jpg" alt="alt..." >
						<i class="zoom-icoBw"></i>
					</a>
				</figure>
			</div>

			<div class="col-md-4 col-sm-6 animIt15">
				<figure class='media-news'>
					<a href="img/staticks/fullsize/img_1.jpg" class="group2 bwWrapper">
						<img src="img/staticks/pop1.jpg" alt="alt..." >
						<i class="zoom-icoBw"></i>
					</a>
				</figure>
			</div>
		</div>
		<div class="spacer5"></div>
		<div class="text-center">
			<a href="#" class='more pull-none blue-text'>
				<div class="inside">
					<div class="backside"> View More Photo </div>
					<div class="frontside"> View More Photo </div>
				</div>
			</a>
		</div>
		<div class="spacer5"></div>
	</section> -->
</div>

<!--===========================-->
<!--=========Footer============-->
<footer class='main-wrapper footer'>
	<div class="partners" id='partners'>
		<div class="container make-row">
			<div class="row">
				<h4 class='division-h col-md-2 dark-text'>Our Partners</h4>

				<div id='animIt16' class='col-md-2'>
					<a href="#"><img src="img/staticks/sponsor1.png" alt="alt..."></a>
				</div>
				<div id='animIt17' class='col-md-2'>
					<a href="#"><img src="img/staticks/sponsor2.png" alt="alt..."></a>
				</div>
				<div id='animIt18' class='col-md-2'>
					<a href="#"><img src="img/staticks/sponsor3.png" alt="alt..."></a>
				</div>
				<div id='animIt19' class='col-md-2'>
					<a href="#"><img src="img/staticks/sponsor4.png" alt="alt..."></a>
				</div>
				<div id='animIt20' class='col-md-2'>
					<a href="#"><img src="img/staticks/sponsor5.png" alt="alt..."></a>
				</div>
			</div>
		</div>
	</div>
	<div class="container">
		<a href="#" data-scroll="form_slider" class='btn submit a-trig reg-footer'>Get your free quotes now</a>
	</div>
	<div class="container bottom">

		<ul class='social-transform footer-soc list-unstyled'>
			<li>
				<a href='#' target='blank' class='front'><div class="fa fa-facebook"></div></a>
			</li>
			<li>
				<a href='#' target='blank' class='front'><i class="fa fa-twitter"></i></a>
			</li>
			<li>
				<a href='#' target='blank' class='front'><i class="fa fa-google-plus"></i></a>
			</li>
			<li>
				<a href='#' target='blank' class='front'><i class='fa fa-vimeo-square'></i></a>
			</li>
		</ul>
		<div class="clearifx"></div>
		<span class="copyright">
			&#169;  <?php echo date("Y") ?>  HaulingDepot.com
		</span>
		<div class="container-fluid responsive-switcher hidden-md hidden-lg">
			<i class="fa fa-mobile"></i>
			Mobile version: Enabled
		</div>
	</div>
</footer>


<!-- Top -->
<div id="back-top-wrapper" class="visible-lg">
	<p id="back-top" class='bounceOut'>
		<a href="#top">
			<span></span>
		</a>
	</p>
</div>

<!-- Modal -->
<div id="myModal" class="modal fade" tabindex="-1" aria-hidden="true">
	<div class="modal-wr">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>

		<form id='contact' action="request-form.php" method="post" accept-charset="utf-8" role="form">
			<input type="hidden" name='resultCaptcha' value=''>
			<div class='control-group'>
				<input type="text" name='name' value='' placeholder='Enter your name' data-required>
			</div>
			<div class='control-group'>
				<input type="text" name='email' value='' placeholder='Enter your mail' class='insert-attr' data-required>
			</div>
			<div class='control-group'>
				<textarea name='message' cols="30" rows="10" maxlength="300" placeholder='Enter your message ...' data-required></textarea>
			</div>
			<div class='control-group captcha'>
				<div class="picture-code">
					What is <span id="numb1">4</span> + <span id="numb2">1</span> (Anti-spam)
				</div>
				<input type="text" placeholder='Type here ...' name='name' id='chek' data-required data-pattern="5">
			</div>
			<button type="submit" value="Submit" class='btn submit' name="submit">Submit</button>
		</form>
	</div>
</div>


</div>
	<div class="mask"></div>
	<script src="js/libs/jquery-1.10.1.min.js"></script>
	<script src="js/libs/bootstrap.min.js"></script>
	<script src="js/cross/modernizr.js"></script>
	<script src="js/jquery.bxslider.min.js"></script>
	<script src="js/jquery.customSelect.js"></script>
	<script src="js/jquery.validate.min.js"></script>
	<script src="js/jquery.colorbox-min.js"></script>
	<script src="js/jquery.waypoints.min.js"></script>
	<script src="js/jquery.parallax-1.1.3.js"></script>
    <script src="http://maps.googleapis.com/maps/api/js?sensor=false&amp;libraries=places"></script>
    <script src="js/ubilabs-geocomplete-d026f14/jquery.geocomplete.js"></script>
    <script src="js/pickdate356/picker.js"></script>
    <script src="js/pickdate356/picker.date.js"></script>
    <link rel="stylesheet" href="js/pickdate356/themes/default.css">
    <link rel="stylesheet" href="js/pickdate356/themes/default.date.css">
    <script>
      $(function(){

        $("#geocomplete").geocomplete({
		details: ".fromDetails",
		types: ['(regions)']
	});

	$("#geocomplete2").geocomplete({
		details: ".toDetails",
		types: ['(regions)']
	});

        $('.datepicker').pickadate({
  // An integer (positive/negative) sets it relative to today.
  min: +1,
  // `true` sets it to today. `false` removes any limits.
  max: false,
  formatSubmit: 'mm/dd/yyyy'
});

      });
    </script>
    <script>
    $( "form" ).submit(function( event ) {
    $("#fromCity").attr('name', "fromCity");
    $("#fromZip").attr('name', "fromZip");
    $("#fromState").attr('name', "fromState");
    $("#fromCountry").attr('name', "fromCountry");
    $("#toCity").attr('name', "toCity");
    $("#toZip").attr('name', "toZip");
    $("#toState").attr('name', "toState");
    $("#toCountry").attr('name', "toCountry");
    });

</script>
<script type = "text/javascript">
function combine() {
var fn = $("#fromCity").val();
var sn = $("#fromZip").val();
var cn = fn + " " + sn;
$("#fromCity").val(cn);
var fn2 = $("#toCity").val();
var sn2 = $("#toZip").val();
var cn2 = fn2 + " " + sn2;
$("#toCity").val(cn2);

$("#move_date").val("#move_date_sumbit");
}

</script>
	<script src="js/custom.js"></script>
	<!-- file loader -->
	<script src="js/loader.js"></script>
     <script type="text/javascript" src="http://www.carqueryapi.com/js/carquery.0.3.3.js"></script>

<script type="text/javascript">
$(document).ready(
function()
{
     //Create a variable for the CarQuery object.  You can call it whatever you like.
     var carquery = new CarQuery();

     //Run the carquery init function to get things started:
     carquery.init();

     //Optionally, you can pre-select a vehicle by passing year / make / model / trim to the init function:
     //carquery.init('2000', 'dodge', 'Viper', 11636);

     //Optional: Pass sold_in_us:true to the setFilters method to show only US models.
     carquery.setFilters( {sold_in_us:false} );

     //Optional: initialize the year, make, model, and trim drop downs by providing their element IDs
     carquery.initYearMakeModel('car-years', 'car-makes', 'car-models');

     //Optional: set the onclick event for a button to show car data.
     $('#cq-show-data').click(  function(){ carquery.populateCarData('car-model-data'); } );

     //Optional: set minimum and/or maximum year options.
     carquery.year_select_min=1990;
     carquery.year_select_max=2014;

});
</script>
<script type="text/javascript">
var timerlen = 5;
var slideAniLen = 250;

var timerID = new Array();
var startTime = new Array();
var obj = new Array();
var endHeight = new Array();
var moving = new Array();
var dir = new Array();

function slidedown(objname){
        if(moving[objname])
                return;

        if(document.getElementById(objname).style.display != "none")
                return; // cannot slide down something that is already visible

        moving[objname] = true;
        dir[objname] = "down";
        startslide(objname);
}

function slideup(objname){
        if(moving[objname])
                return;

        if(document.getElementById(objname).style.display == "none")
                return; // cannot slide up something that is already hidden

        moving[objname] = true;
        dir[objname] = "up";
        startslide(objname);
}

function startslide(objname){
        obj[objname] = document.getElementById(objname);

        endHeight[objname] = parseInt(obj[objname].style.height);
        startTime[objname] = (new Date()).getTime();

        if(dir[objname] == "down"){
                obj[objname].style.height = "1px";
        }

        obj[objname].style.display = "block";

        timerID[objname] = setInterval('slidetick(\'' + objname + '\');',timerlen);
}

function slidetick(objname){
        var elapsed = (new Date()).getTime() - startTime[objname];

        if (elapsed > slideAniLen)
                endSlide(objname)
        else {
                var d =Math.round(elapsed / slideAniLen * endHeight[objname]);
                if(dir[objname] == "up")
                        d = endHeight[objname] - d;

                obj[objname].style.height = d + "px";
        }

        return;
}

function endSlide(objname){
        clearInterval(timerID[objname]);

        if(dir[objname] == "up")
                obj[objname].style.display = "none";

        obj[objname].style.height = endHeight[objname] + "px";

        delete(moving[objname]);
        delete(timerID[objname]);
        delete(startTime[objname]);
        delete(endHeight[objname]);
        delete(obj[objname]);
        delete(dir[objname]);

        return;
}

function slide(s,o) {
            if (s) {
                slidedown(o);
            }
            else {
                slideup(o);
            }
        }

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-34289413-1']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>

</body>
</html>