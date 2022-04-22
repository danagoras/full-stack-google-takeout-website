<?php

if (isset($_POST['login-btn']))
{
    require 'dbh.inc.php';

    $adminname = $_POST['name'];
    $password = $_POST['password'];

    if(empty($adminname) || empty($password))
    {
        header("Location: ../Admin.php?error=emptyfields");
        $login_error = "Missing Name or Password";
        exit;

    }
    else
    {
		$sql = "SELECT * FROM admins WHERE name=?";
		$stmt = mysqli_stmt_init($conn);
		if (!mysqli_stmt_prepare($stmt, $sql))
		{
			header("Location: ../Admin.php?error=sqlerror");
			exit;
		}
		else
		{
			mysqli_stmt_bind_param($stmt, "s", $adminname);
			mysqli_stmt_execute($stmt);
			$result = mysqli_stmt_get_result($stmt);
			if ($row = mysqli_fetch_assoc($result))
			{
				//$password = md5($password);
				if($password !== $row['password'])
				{
					header("Location: ../Admin.php?error=wrongpassword");
					exit;
				}
				else if($password == $row['password'])
				{
					session_start();
					$_SESSION['uid'] = $row['name'];

					header("Location: ../Admin.php?login=success");
					exit;
				}
				else
				{
					header("Location: ../Admin.php?error=wrongpsw");
					exit;
				}
			}
			else
			{
				header("Location: ../Admin.php?error=nouser");
				exit;
			}
		}
	}
}
else
{
	header("Location: ../Admin.php");
	exit;
}
?> 
