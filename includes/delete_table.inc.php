<?php
if (true)
{
	require 'dbh.inc.php';
	
	$sql = "TRUNCATE TABLE locations";
	if(mysqli_query($conn, $sql) == TRUE)
	{
		header("Location: ../Admin.php?success=database_deleted");
	}
	else
	{
		header("Location: ../Admin.php?error=database_not_deleted");
	}
}
else
{
	header("Location: ../Admin.php");
	exit;
}
?>