<?php
/**
 * 1. Receives a ticker search term via web request
 * 2. Gets auto-complete entries from Yahoo finance
 * 3. Returns entries in format that jQuery autocomplete expects
 * 
 * @author Matthew Orr <matthew.orr@interworks.com>
 */

//get the search term from the request
$searchTerm = $_REQUEST['term'];

//set up output
$output = array();

//if search term provided, get auto-complete entries
if(strlen($searchTerm) > 0)
{
	//make request to yahoo
	$url = "http://autoc.finance.yahoo.com/autoc?query=$searchTerm&region=1&lang=en";
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	$response = curl_exec($ch);
	curl_close($ch);
	
	//output the data
	$jsonData = json_decode($response,true);
	foreach($jsonData["ResultSet"]["Result"] as $entry)
	{
		$output[] = array("label" => $entry['symbol'] . ": " . $entry['name'],
		                  "value" => $entry['symbol']);
	}
}

//show the output
echo json_encode($output);

?>
