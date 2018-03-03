<?php
set_time_limit(0);

$mysqli = new mysqli("localhost", "haulingd_epot", "j3b7g4nGi8nd", "haulingd_haulingdepotleads");

/* check connection */
if (mysqli_connect_errno()) {
	printf("Connect failed: %s\n", mysqli_connect_error());
	exit();
}

//$mysqli->query("CREATE TABLE `csv_car_24June2016` SELECT * FROM `csv_car`");

// Open and parse the csv file
$fh = fopen("CQA_Basic_2016.csv", "r");

while($line = fgetcsv($fh, 1000, ","))
{
	$model_id   = mysqli_real_escape_string($mysqli, $line[0]);
	$model_make = mysqli_real_escape_string($mysqli, ucwords($line[1]));
	
	$model_name = mysqli_real_escape_string($mysqli, $line[2]);
	$model_trim = mysqli_real_escape_string($mysqli, $line[3]);
	$car_model  = $model_name.' '.$model_trim;
	
	$model_year = mysqli_real_escape_string($mysqli, $line[4]);
	
	/*$result = $mysqli->query("SELECT `id` FROM `csv_car` WHERE make='$model_make' AND model='$car_model' AND year='$model_year'");

	if (!$result) {
		die($mysqli->error);
	}
	
	// Insert if not exist
	if ($result->num_rows == 0) {*/
		// Insert the data into the csv_car table
		$query = "INSERT INTO csv_car SET make='$model_make', model='$car_model', year='$model_year'";
		$insert_result = $mysqli->query($query);
		
		if (!$insert_result) {
			die($mysqli->error);
		}
	/*}
	
	$result->close();*/
}

fclose($fh);

echo "<br /><b>Import Done!!!</b>";

$mysqli->close();
?>