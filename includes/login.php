<?php
		
		if (isset($_SESSION['uid'])) // If logged in
		{
			
			echo 	'<p class ="login-status text-white">You are logged in as:</p>\t
					<p style="color:cyan;">'. $_SESSION['uid'] .'</p>';
					
			echo	'\t','<form action="includes/logout.inc.php" method="post">
					<button type="submit" class="btn btn-default btn-sm active " name="logout-submit">Logout</button>
					</form>';
		
		}
		else // If logged out
		{
	  echo 	'<p class ="login-status text-white">You are logged out!</p>
	  		<div class="text-center">
	  			<a href="" class="btn btn-default btn-rounded mb-2" data-toggle="modal" data-target="#modalRegisterForm">Sign Up</a>
			</div>
        <div class="text-right">
          <a href="" class="btn btn-indigo btn-rounded mb-2" data-toggle="modal" data-target="#modalLoginForm">Login to your Account</a>
        </div>
        ';
      
			if($_SERVER['REQUEST_URI']=="/Homepage.php?error=wrongpsw") // User entered wrong password
			{
				echo '<p style="color:red">You entered the wrong password</p>';
			}
			if($_SERVER['REQUEST_URI']=="/Homepage.php?error=nouser") // User does not exist with that username
			{
				echo '<p style="color:red">This username does not exist</p>';
			}
		}
		?>