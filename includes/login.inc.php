<?php
// Did the user get to the site via a button
if (isset($_POST['login-submit']))
{
	require 'dbh.inc.php';
	
	$mailuid = $_POST['mailuid'];
	$password = $_POST['psw'];
	
	if (empty($mailuid) || empty($password))
	{
		header("Location: ../Homepage.php?error=emptyfields");
		$login_error = "Missing email or password";
		exit;
	}
	else
	{
		$sql = "SELECT * FROM users WHERE username=? OR email=?";
		$stmt = mysqli_stmt_init($conn);
		if (!mysqli_stmt_prepare($stmt, $sql))
		{
			header("Location: ../Homepage.php?error=sqlerror");
			exit;
		}
		else
		{
			mysqli_stmt_bind_param($stmt, "ss", $mailuid, $mailuid);
			mysqli_stmt_execute($stmt);
			$result = mysqli_stmt_get_result($stmt);
			if ($row = mysqli_fetch_assoc($result))
			{
				$hashed_psw = md5($password);
				// $pswCheck = password_verify($hashed_psw, $row['password']);
				if($hashed_psw !== $row['password'])
				{
					header("Location: ../Homepage.php?error=wrongpsw");
					exit;
				}
				else if($hashed_psw == $row['password']) // Correct info so start session
				{
					session_start();
					$_SESSION['userid'] = $row['userID'];
					$_SESSION['uid'] = $row['username'];
					
					header("Location: ../Homepage.php?login=success");
					exit;
				}
				else
				{
					header("Location: ../Homepage.php?error=wrongpsw");
					exit;
				}
			}
			else
			{
				header("Location: ../Homepage.php?error=nouser");
				exit;
			}
		}
	}
}
else
{
	header("Location: ../Homepage.php");
	exit;
}
?>