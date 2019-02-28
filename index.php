<?php
include("source.php");
include("ipinfodb/class.IPInfoDB.php");
include("libchart-1.3/libchart/classes/libchart.php");

$sourceFile = fopen($source,'r') or die("unable to open source file!");

$columns= str_replace(PHP_EOL,"",fgets($sourceFile));
$columns= explode(',',$columns);

try{
	require 'config/sql.php';
    $conn=new mysqli($host, $user, $pass, $db);
}
catch(DBException $e) {
	echo 'The connect can not create: ' . $e->getMessage();
}

$query = "CREATE TABLE task_2_panda_group (";
foreach ($columns as $col){
	if($col=="id"){
		$query = $query.$col." int NOT NULL, ";
	}
	else if($col=="gender"){
		$query = $query.$col." enum('Female','Male') NOT NULL, ";
	}
	else{
		$query = $query.$col." varchar(40) NOT NULL, ";
	}
}

$query=$query."country varchar(40) NOT NULL, PRIMARY KEY(id));";
$conn->query($query) or die("unable to create table in DB!");


$stmt = "INSERT INTO `task_2_panda_group` (";
foreach($columns as $col){
	$stmt=$stmt."`".$col."`, ";
}
$stmt =$stmt."`country`) Values(".str_repeat("?, ",count($columns))."?)";
$stmt = $conn->prepare($stmt) or die("statement not prepared!");
$IPInfoDB = new IPInfoDB($key);
While(!feof($sourceFile)){
	$row=explode(',',str_replace(PHP_EOL,"",fgets($sourceFile)));


	$a_params= Array();
	$prep_param= Array();
	$param_types = "i".str_repeat("s",count($row));
	$a_params[] = & $param_types;
	for($i=0;$i<count($row);$i++){
		$a_params[] = & $row[$i];
	}
	$country =  $IPInfoDB->getCountry($row[count($row)-1])["countryName"];
	$a_params[] = & $country;
	
	print_r($country);
	call_user_func_array(array($stmt, 'bind_param'), $a_params);
	$stmt->execute();
}
$data= Array();
$results = $conn->query("SELECT `country` form `task_2_panda_group`");
if( $results->num_rows >0){
	while($row = $results->fetch_assoc()){
		if(isset($data[$row["country"]])){
			$data[$row["country"]]++;
		}
		else{
			$data[$row["country"]]=1;
		}
	}
}

$chart = new LabChartsBar();
$chart->setData(array_values($data));
$chart->setSize('500x700');
$chart->setLabels(implode("|",array_keys($data)));
$chart->setAxis(25);
$chart->setGrids(25);
$chart->setTitle('Numer of users per country');
echo '<img src='.$chart->getChart()."/>";

?>