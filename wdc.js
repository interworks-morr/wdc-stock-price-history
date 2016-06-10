/**
 * Retrieves Google Finance historical data for a given ticker
 * and returns it (or just the schema) in the format needed by
 * a Tableau Web Data Connector
 * 
 * @author Matthew Orr <matthew.orr@interworks.com>
 */

$(function() {
	
	//event handlers
	function submitButtonOnClick()
	{
		try
		{
			tableau.connectionName = "Google Finance's Ticker History";
			
			//store the form data because the submit causes it to disappear
			var formData = {"ticker":$('#ticker').val(),
			                "startdate":$("#startdate").val(),
			                "enddate":$("#enddate").val()}
			tableau.connectionData = JSON.stringify(formData);
			tableau.submit();
		}
		catch(error)
		{
			alert("There was a problem using the Tableau web data connector javascript library. " + error);
		}
	}
	
	//tableau web data connector functionality
	try
	{
		var myConnector = tableau.makeConnector();
		myConnector.getSchema = function (schemaCallback)
		{
			var formData = JSON.parse(tableau.connectionData);
			var url = "fetch_history.php?type=schema&ticker=" + formData["ticker"];
			$.getJSON(url,function (data){ schemaCallback([data]); });
		};
		
		myConnector.getData = function(table, doneCallback)
		{
			var formData = JSON.parse(tableau.connectionData);
			var url = "fetch_history.php?type=data&ticker=" + formData["ticker"] + "&startdate=" + formData["startdate"] + "&enddate=" + formData["enddate"];
			$.getJSON(url,function(data)
			{
				table.appendRows(data);
				doneCallback();
			});
		};
		tableau.registerConnector(myConnector);
	}
	catch(error)
	{
		alert("There was a problem loading the Tableau web data connector javascript library.");
	}
	
	
	//onload functionality
	function wdcInitialize()
	{
		//show/hide warning message
		$('#tableau-warning-msg').hide();
		if (typeof tableauVersionBootstrap  == 'undefined' || !tableauVersionBootstrap)
		{
			$('#tableau-warning-msg').show();
		}
		
		//set up event handler for submit button
		$("#submitButton").click(submitButtonOnClick);
	}
	$(document).ready(wdcInitialize);
});
