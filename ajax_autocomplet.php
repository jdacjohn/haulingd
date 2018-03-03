<?php

//CREDENTIALS FOR DB
define ('DBSERVER', 'localhost');
define ('DBUSER', 'haulingd_epot');
define ('DBPASS','j3b7g4nGi8nd');
define ('DBNAME','haulingd_haulingdepotleads');

//LET'S INITIATE CONNECT TO DB
$connection = new mysqli(DBSERVER, DBUSER, DBPASS) or die("Can't connect to server. Please check credentials and try again");
$result = mysqli_select_db($connection, DBNAME) or die("Can't select database. Please check DB name and try again");

//CREATE QUERY TO DB AND PUT RECEIVED DATA INTO ASSOCIATIVE ARRAY
if (isset($_REQUEST['query'])) {
    $query = mysqli_real_escape_string($connection, $_REQUEST['query']);
	$page  = mysqli_real_escape_string($connection, $_REQUEST['page']);
	$make  = isset( $_REQUEST['make'] ) ? mysqli_real_escape_string($connection, $_REQUEST['make']) : '';
	$year  = isset( $_REQUEST['year'] ) ? mysqli_real_escape_string($connection, $_REQUEST['year']) : '';
	
	$array = array();
	if( $page == 'model' )
	{
		$sql_query = "SELECT DISTINCT model FROM csv_car WHERE model LIKE '{$query}%' AND make = '{$make}'";
		if( $year ) $sql_query .= " AND year = '{$year}'";
	}
	elseif( $page == 'make' )
	{
		$sql_query = "SELECT DISTINCT make FROM csv_car WHERE make LIKE '{$query}%'";
	}
	
	if( isset( $sql_query ) )
	{
		$sql_query .= " LIMIT 10";
		$sql = mysqli_query($connection, $sql_query);
		
		while ($row = mysqli_fetch_array($sql)) {
			$array[] = array (
				'label' => $row[$page],
				'value' => $row[$page],
			);
		}
	}
	
    //RETURN JSON ARRAY
    echo json_encode ($array);
}
?>