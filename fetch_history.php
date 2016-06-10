<?php

/**
 * Retrieves Google Finance historical data for a given ticker
 * and returns it (along with schema) for a Tableau Web Data Connector
 * 
 * @author Matthew Orr <matthew.orr@interworks.com>
 */

//constants
define('TYPE_SCHEMA',"schema");
define('TYPE_DATA',"data");

//get request vars
if(empty($_GET['type']))
{
	die("ERROR: Please specify whether you need the schema or the data");
}
elseif(empty($_GET['ticker']))
{
	die("ERROR: Please specify the stock ticker");
}
else
{
	$type = $_GET['type'];
	$ticker = $_GET['ticker'];
}

//generate the specified output
$output = "";
if($type == TYPE_SCHEMA)
{
	//hard-coded schema
	$columns = [["id" => "Date", "alias"=>"Date", "dataType" => "datetime"],
	            ["id" => "Open", "alias"=>"Open", "dataType" => "float"],
	            ["id" => "High", "alias"=>"High", "dataType" => "float"],
	            ["id" => "Low", "alias"=>"Low", "dataType" => "float"],
	            ["id" => "Close", "alias"=>"Close", "dataType" => "float"],
	            ["id" => "Volume", "alias"=>"Volume", "dataType" => "int"]];
	
	$tableInfo = ["id" => $ticker . "_history",
	              "alias" => "$ticker History",
	              "columns" => $columns];
	
	$output = json_encode($tableInfo);
}
elseif($type == TYPE_DATA)
{
	//check for dates
	$startDateStr = (empty($_GET['startdate'])) ? "15 years ago" : $_GET['startdate']; //default to 15 years ago, if not otherwise specified
	$startDateEnc = urlencode(date("M j, Y",strtotime($startDateStr)));
	$endDateStr = (empty($_GET['enddate'])) ? "today" : $_GET['enddate']; //default to today, if not otherwise specified
	$endDateEnc = urlencode(date("M j, Y",strtotime($endDateStr)));
	
	//make request to google
	$args = "q=" . urlencode($ticker) . "&startdate=$startDateEnc&enddate=$endDateEnc&output=csv";
	$url = "https://www.google.com/finance/historical?$args";
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	$response = curl_exec($ch);
	curl_close($ch);
	
	//convert to WDC data format
	$records = explode("\n",substr($response,3));
	$data = array();
	$header = explode(",",array_slice($records,0,1)[0]);
	foreach(array_slice($records,1) as $record)
	{
		$dataRow = array_combine($header,explode(",",$record));
		$data[] = $dataRow;
	}
	$output = json_encode($data);
}
else
{
	die("ERROR: Unknown type ($type).  Please specify schema or data.");
}
echo $output;

?>
