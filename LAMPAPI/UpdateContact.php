<?php

	$inData = getRequestInfo();
	
	// localhost, admin_username, password, database
	$conn = new mysqli("localhost", "group17", "cop4331c", "COP4331");
    
	$ID = $inData["ID"];
    $field = $inData["field"];
    $value = $inData["value"];

	// Attempt to connect to the server, and return error message if failed.
	if ($conn->connect_error) 
	{
		returnWithError( $conn->connect_error );
	} 
	else
	{	
		if(isDuplicate($conn, $ID, $field, $value))
		{
			returnWithError("That full name already exists!");
			$conn->close();
			return;
		}		

        $sql =  "UPDATE Contacts SET ".$field." = '".$value."' WHERE CID = ".$inData["CID"];

		if ($result = $conn->query($sql) != TRUE)
		{
            returnWithError($conn->error);
		}

		returnWithError("Update Success.");
		        
		$conn->close();
	}

	function getRequestInfo()
	{
		// json_decode converts a json string into a mixed variable type. True to set associative type to true.
		return json_decode(file_get_contents('php://input'), true);
	}

	function sendResultInfoAsJson( $obj )
	{
		header('Content-type: application/json');
		echo $obj;
	}
	
	function returnWithError( $err )
	{
		$retValue = '{"error":"' . $err . '"}';
		sendResultInfoAsJson( $retValue );
	}
	
	function debugging( $log )
	{
		$retValue = '{"log":"' . $log . '"}';
		sendResultInfoAsJson( $retValue );
	}

	function isDuplicate($conn, $ID, $field, $value)
	{
		if(($field != "contactFirstName" && $field != "contactLastName"))
		{
			return false;
		}

		// "contactFirstName == "contactFirstName" so currentTag = contactLastName
		$currentTag = ($field == "contactFirstName") ? "contactLastName" : "contactFirstName"; 

		// First get the current contacts original info.
		// SELECT contactFirstName FROM Contacts WHERE CID= n
		$sql = "SELECT ". $currentTag . " FROM Contacts WHERE ID=" . $ID;
		$result = $conn->query($sql);

		
		if($result->num_rows > 0)
		{
			debugging("Made it here!");

			$row = $result->fetch_assoc();
			// The other part of the name so we can verify the other name doesnt match.
			// CurrentValue will be the LastName
			$currentValue = $row[$currentTag];

			// get first last FROM Contacts WHERE lastName = lastNamesupplied and CID
			$sql = "SELECT contactFirstName,contactLastName FROM Contacts WHERE " . $currentTag . "='" . $value . "' AND ID=" . $ID;
			$result = $conn->query($sql);

			if($result->num_rows > 0)
			{
				debugging("Made it this far");		
				while ($row = mysql_fetch_array($result, MYSQL_ASSOC))
				{
					if($row[$currentTag] == $currentValue && $row[$field] == $value)
					{
						return true;
					}
				}

				return false;
			}
		}

	}
?>
