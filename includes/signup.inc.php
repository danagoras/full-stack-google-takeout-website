<?php
// Did the user get to the site via a button
if (isset($_POST['signup-submit']))
{
	// Include Database Handler
	require 'dbh.inc.php';
	
	// Get the values
	$username = $_POST['uid'];
	$email = $_POST['mail'];
	$password = $_POST['psw'];
	$passwordRepeat = $_POST['psw-repeat'];
	
	// Encrypt the ID
	$encryptionMethod = "AES-256-CBC";
	$encrypteduserID = openssl_encrypt($email, $encryptionMethod, $password); 
	
	// Password creation
	$containsLetter  = preg_match('/[A-Z]/', $password);
	$containsDigit   = preg_match('/\d/', $password);
	$containsSpecial = preg_match('/[#@$%^&!?]/', $password);
	
	// Error Handlers
	if (empty($username) || empty($email) || empty($password) || empty($passwordRepeat)) // Empty content
	{
		header("Location: ../Homepage.php?error=emptyfields&username=".$username."&email=".$email);
		exit();
	}
	else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) // Wrong email
	{
		header("Location: ../Homepage.php?error=invalidemail&uid=".$username);
		exit();
	}
	else if (!preg_match("/^[a-zA-Z0-9]*$/", $username)) // Wrong Username
	{
		header("Location: ../Homepage.php?error=invalidusername&mail=".$email);
		exit();
	}
	else if (!preg_match("/^[a-zA-Z0-9]*$/", $username) && !filter_var($email, FILTER_VALIDATE_EMAIL)) // Wrong Username & Email
	{
		header("Location: ../Homepage.php?error=invalidusername&mail");
		exit();
	}
	else if($password !== $passwordRepeat) // The passwords match
	{
		header("Location: ../Homepage.php?error=passwordcheck&username=".$username."&email=".$email);
		exit();
	}
	else if (strlen($password)<8 || !$containsLetter || !$containsDigit || !$containsSpecial) // Password has the correct attributes
	{
		header("Location: ../Homepage.php?error=invalidpassword");
		exit();
	}
	else // SQL Injection Protection 
	{
		$sql = "SELECT username FROM users WHERE username=?";
		$stmt = mysqli_stmt_init($conn);
		if (!mysqli_stmt_prepare($stmt, $sql))
		{
			header("Location: ../Homepage.php?error=sqlerror");
			exit();
		}
		else
		{
			mysqli_stmt_bind_param($stmt, "s", $username);
			mysqli_stmt_execute($stmt);
			mysqli_stmt_store_result($stmt);
			$resultCheck = mysqli_stmt_num_rows($stmt);
			if ($resultCheck > 0) // Already have this user
			{
				header("Location: ../Homepage.php?error=usernametaken&email=".$email);
				exit();
			}
			else // New user
			{
				$sql = "INSERT INTO users (userID, username, password, email) VALUES (?, ?, ?, ?)";
				$stmt = mysqli_stmt_init($conn);
				if (!mysqli_stmt_prepare($stmt, $sql))
				{
					header("Location: ../Homepage.php?error=sqlerror");
					exit();
				}
				else
				{
					// MD5 hashing the password
					$hashedpsw = md5($password);
					
					mysqli_stmt_bind_param($stmt, "ssss",$encrypteduserID, $username, $hashedpsw, $email);
					mysqli_stmt_execute($stmt);
					header("Location: ../Homepage.php?signup=success");
					exit();
				}
			}
		}
	}
	mysqli_stmt_close($stmt);
	mysqli_close($conn);
	
}
else
{
	header("Location: ../Homepage.php");
	exit();
}?>