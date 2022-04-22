<?php
if (isset($_SESSION['uid'])) // User logged in
{
	echo	'<div class="container">';
	
	// Compute ecological mobility score
	$query_body = sprintf("SELECT count(*) FROM locations WHERE (activitytype = 'ON_BICYCLE' OR activitytype = 'ON_FOOT' OR activitytype = 'RUNNING' OR activitytype = 'WALKING') AND userID = '".$_SESSION['userid']."'");
	$query_vehicle = sprintf("SELECT count(*) FROM locations WHERE (activitytype = 'IN_VEHICLE' OR activitytype = 'STILL' OR activitytype = 'TILTING') AND userID = '".$_SESSION['userid']."'");
	$result = mysqli_query($conn, $query_body);
	$body_activity = $result->fetch_row();
	$result->close();
	
	$result = mysqli_query($conn, $query_vehicle);
	$other_activity = $result->fetch_row();
	$result->close();

	if(($body_activity[0] + $other_activity[0]) != 0)
	{
		$ecological_mobility_score =  $body_activity[0] / ($body_activity[0] + $other_activity[0]);		
	}
	else
	{
			$ecological_mobility_score = "No info";
	}
	
	// Show user data
	echo	'<main class="mt-5">
			<h1 class="text-primary"> User Data </h1>
			<div id="main-container"> 
			<p   class="text-success font-weight-bold"> Ecological Mobility Score: '. $ecological_mobility_score . '</p></div>  
			<h2  class="text-primary"> Upload your JSON Data: </h2>
			<form action="includes/User_Upload.inc.php" method="POST" enctype="multipart/form-data" class="light-green-text">
			Select a file: <input type="file" name="jsonFile" id="jsonFile"><br><br>
			<input type="submit" value="Submit" name="submit-json">
			</form>
			</main>';
			
	// Last upload date
	$uploadfile = 'uploads/uploadedfile.json';
	if (file_exists($uploadfile)) 
	{
		echo '<p class="font-weight-bold text-info">Last upload was on:' . date ("F d Y H:i:s.", filemtime($uploadfile)) . '<br>';
	}
	
	// Period of user data
	$date_query = sprintf("SELECT MIN(timestampMs) FROM locations WHERE userID = '".$_SESSION['userid']."'");  
	$result = mysqli_query($conn, $date_query);
	$first_date = $result->fetch_row();
	$result->close();
	
	$date_query = sprintf("SELECT MAX(timestampMs) FROM locations WHERE userID = '".$_SESSION['userid']."'"); 
	$result = mysqli_query($conn, $date_query);
	$last_date = $result->fetch_row();
	$result->close();
	
	if(isset($first_date[0]) && isset($first_date[0])) // If there is information
	{
		echo "The users data is from: " . date('F d Y H:i:s', $first_date[0]/1000) . " till " . date('F d Y H:i:s.', $last_date[0]/1000) . "<br></p>";
	}
	else
	{
		echo '<p class="font-weight-bold text-warning">There is no user information yet</p>';
	}
	
	// Ecological Mobility Score Canvas
	$sql = sprintf("SELECT activitytype, timestampMs FROM locations  WHERE userID = '".$_SESSION['userid']."' ORDER BY locationID DESC");
	$result = mysqli_query($conn, $sql);
	
	// Activity Counters
	$body_counter = 0;
	$non_body_counter = 0;
	$EMS = array();
	$months = array();
	$year_in_ms = 1000*60*60*24*30*12;
	$current_month = date('F', $last_date[0]/1000);
	$month_counter = 0;
	$last_year_date = date('F', $last_date[0]/1000)." ".date('Y', ($last_date[0]-$year_in_ms)/1000);
	
	while($row = mysqli_fetch_assoc($result))
	{
		// If we reached last years information end loop
		if($row["timestampMs"] < ($last_date[0] - $year_in_ms))
		{
			break;
		}
		if(date('F', $row["timestampMs"]/1000) == $current_month) // Same Month
		{
			$months[$month_counter] = date('F', $row["timestampMs"]/1000);
		}
		else // Different Month
		{
			// Ecological Mobility Score per month
			if($body_counter != 0 || $non_body_counter != 0)
			{
				$EMS[$month_counter] = $body_counter / ($body_counter + $non_body_counter);
			}
			// New Month Start
			$month_counter++;
			$body_counter = 0;
			$non_body_counter = 0;
			$current_month = date('F', $row["timestampMs"]/1000);
		}
		// Check for activity type
		if(($row["activitytype"] == "ON_BICYCLE") || ($row["activitytype"] == "ON_FOOT") || ($row["activitytype"] == "RUNNING") || ($row["activitytype"] == "WALKING"))
		{
			$body_counter++;
		}
		else if(($row["activitytype"] == "IN_VEHICLE") || ($row["activitytype"] == "STILL") || ($row["activitytype"] == "TILTING"))
		{
			$non_body_counter++;
		}
	}
	$result->close();
	
	if($body_counter != 0 || $non_body_counter != 0)
	{
		$EMS[$month_counter] = $body_counter / ($body_counter + $non_body_counter);
	}
	
	// Encode the arrays
	$scores = json_encode($EMS);
	$months = json_encode($months);
	
	/* Top Users Leaderboard */
	$user_table = "SELECT userID, username from users";
	$result = mysqli_query($conn, $user_table);
	$usernames = array(); // Username table
	$userIDs = array(); // User ID table
	$month_in_ms = 1000*60*60*24*30;
	$EMS_all = array();
	
	while($row = mysqli_fetch_assoc($result))
	{
		$usernames[] = $row["username"];
		$userIDs[] = $row["userID"];
	}
	
	// Get EMS of last month for all users
	for($i=0; $i<count($usernames); $i++)
	{
		$sql = "SELECT activitytype, timestampMs FROM locations  WHERE userID = '".$userIDs[$i]."' ORDER BY locationID DESC";
		$result = mysqli_query($conn, $sql);
		$body_counter = 0;
		$non_body_counter = 0;
		$user_last_date = "SELECT MAX(timestampMs) from locations WHERE userID = '".$userIDs[$i]."'";
		$output = mysqli_query($conn, $user_last_date);
		$users_ldate = $output->fetch_row();
		
		
		while($row = mysqli_fetch_assoc($result))
		{
			if($row["timestampMs"] < ($users_ldate[0] - $month_in_ms))
			{
				break;
			}
			if(($row["activitytype"] == "ON_BICYCLE") || ($row["activitytype"] == "ON_FOOT") || ($row["activitytype"] == "RUNNING") || ($row["activitytype"] == "WALKING"))
			{
				$body_counter++;
			}
			else if(($row["activitytype"] == "IN_VEHICLE") || ($row["activitytype"] == "STILL") || ($row["activitytype"] == "TILTING"))
			{
				$non_body_counter++;
			}
		}
		if(($body_counter != 0) || ($non_body_counter != 0))
		{
			$EMS_all[$i] = $body_counter / ($body_counter + $non_body_counter);
		}
		$result->close();
		$output->close();
	}
	
	echo	'<canvas id="chart"></canvas>
			<h3 class="text-primary"> Ecological Score Leaderboard </h3>
			<p class="text-primary"> Top 3 Users : </p>';
			
	arsort($EMS_all);

	$x = 0;
	while (++$x <= count($usernames))
	{
		$key = key($EMS_all);
		$value = current($EMS_all);
		next($EMS_all);
		if($x <= 3)
		{
			echo '<p   class="font-weight-bold text-info">'.$x." " . $usernames[$key] . " - " . $value . '<br>' ;
		}
		else if(isset($usernames[$key]))
		{
			if($usernames[$key] == $_SESSION['uid'])
			{
				echo '<br>' .$x. " " . $usernames[$key] . " - " . $value . '<br></p>' ;
			}
		}
		else if(!isset($usernames[$key]))
		{
			echo 'No data uploaded yet for current user';
		}
	}
	
	/* User Data Analysis */
	echo	'<h4 class="text-primary"> Data Analysis </h4>';
	
	// Choose years and months 
	echo	'<form name="form1" id="form1" class="light-green-text" method="POST">
			Select Month Interval: <select name="start_month" id="start_month">
				<option value="1">January</option>
				<option value="2">February</option>
				<option value="3">March</option>
				<option value="4">April</option>
				<option value="5">May</option>
				<option value="6">June</option>
				<option value="7">July</option>
				<option value="8">August</option>
				<option value="9">September</option>
				<option value="10">October</option>
				<option value="11">November</option>
				<option value="12">December</option> selected="selected">Starting Month</option>
			  </select>
			- <select name="end_month" id="end_month">
				<option value="1">January</option>
				<option value="2">February</option>
				<option value="3">March</option>
				<option value="4">April</option>
				<option value="5">May</option>
				<option value="6">June</option>
				<option value="7">July</option>
				<option value="8">August</option>
				<option value="9">September</option>
				<option value="10">October</option>
				<option value="11">November</option>
				<option value="12">December</option> selected="selected">End Month</option>
			  </select>
			Select Year Interval: <select name="start_year" id="start_year">
				<option value="1">2020</option>
				<option value="2">2019</option>
				<option value="3">2018</option>
				<option value="4">2017</option>
				<option value="5">2016</option>
				<option value="6">2015</option>
				<option value="7">2014</option>
				<option value="8">2013</option>
				<option value="9">2012</option>
				<option value="10">2011</option>
				<option value="11">2010</option>
				<option value="12">2009</option> selected="selected">Starting Year</option>
			  </select>
			- <select name="end_year" id="end_year">
				<option value="1">2020</option>
				<option value="2">2019</option>
				<option value="3">2018</option>
				<option value="4">2017</option>
				<option value="5">2016</option>
				<option value="6">2015</option>
				<option value="7">2014</option>
				<option value="8">2013</option>
				<option value="9">2012</option>
				<option value="10">2011</option>
				<option value="11">2010</option>
				<option value="12">2009</option> selected="selected">End Year</option>
			  </select>
			  <input type="submit" value="Submit" name="interval-submit">  
			</form><br>';
	
	if (isset($_POST['interval-submit']))
	{
		// Translating data from Cascading Dropdown Menu into years
		$years_array = [
			"1" => "2020",
			"2" => "2019",
			"3" => "2018",
			"4" => "2017",
			"5" => "2016",
			"6" => "2015",
			"7" => "2014",
			"8" => "2013",
			"9" => "2012",
			"10" => "2011",
			"11" => "2010",
			"12" => "2009",
		];
		$months_array = [
			"1" => "January",
			"2" => "February",
			"3" => "March",
			"4" => "April",
			"5" => "May",
			"6" => "June",
			"7" => "July",
			"8" => "August",
			"9" => "September",
			"10" => "October",
			"11" => "November",
			"12" => "December",
		];
		
		$sql = "SELECT count(*) FROM locations WHERE (activitytype = 'ON_BICYCLE' OR activitytype = 'ON_FOOT' OR activitytype = 'RUNNING' OR activitytype = 'WALKING' OR activitytype = 'IN_VEHICLE' OR activitytype = 'STILL' OR activitytype = 'TILTING') AND userID = '".$_SESSION['userid']."' AND timestampMs >= '".strtotime($years_array[$_POST['start_year']] . "-" . $_POST['start_month'])."' AND timestampMs <= '".strtotime($years_array[$_POST['end_year']] . "-" . $_POST['end_month'])."'";
		$result =  mysqli_query($conn, $sql);
		$output = $result->fetch_row();
		$num_activities = $output[0];
		$result->close();
		
		// Activity Types Array
		$activities_array = ["ON_BICYCLE","ON_FOOT","RUNNING","WALKING","IN_VEHICLE","STILL","TILTING"];
		// Activity Percentage for each activity
		$activity_prc = array();
		// Start and End date given by user
		$start_date = $months_array[$_POST['start_month']] . "-" . $years_array[$_POST['start_year']];
		$end_date = $months_array[$_POST['end_month']] . "-" . $years_array[$_POST['end_year']];
		
		// Calculate percentages
		if($num_activities>0)
		{
			for($i=0;$i<7;$i++)
			{
				$sql = "SELECT count(*) FROM locations WHERE activitytype = '".$activities_array[$i]."' AND userID = '".$_SESSION['userid']."' AND timestampMs >= '".strtotime($years_array[$_POST['start_year']] . "-" . $_POST['start_month'])."' AND timestampMs <= '".strtotime($years_array[$_POST['end_year']] . "-" . $_POST['end_month'])."'";
				$result = mysqli_query($conn, $sql);
				$output = $result->fetch_row();
				$activity_prc[$i] = ($output[0]/$num_activities) * 100;
				$result->close();
			}
			// Encode the arrays
			$activities_array_json = json_encode($activities_array);
			$activity_prc_json = json_encode($activity_prc);
			
			echo	'<p class="font-weight-bold text-warning">The Data is from ' .$start_date. ' until ' .$end_date.'</p>
					<canvas id="activities"></canvas>';
		}
		else
		{
			echo	'<p class="font-weight-bold text-warning">The user has no data between ' .$start_date. ' and ' .$end_date.'</p>';
		}
		/* Hour of day with most records per activity */
		if($num_activities>0)
		{
			echo 	'<p class="text-primary font-weight-bold">Hour of the day with most activities: </p>';
		
		// Counts the number of activities per hour for every day(column)
		$hourly_activity = array_fill(0, 7, array_fill(0, 24, 0));
		$activity_time[][] = array();
		$hour_index = array();
		$cur_timestampMs = strtotime($start_date)*1000;
		$last_timestampMs = strtotime($end_date)*1000;
		$max = 0;
		$max_values = array();
		// Value of an hour
		$hour = 1000*60*60;
		
		// Run SQL
		$sql = "SELECT timestampMs, activitytype FROM locations WHERE timestampMs >= '".strtotime($start_date)."' AND timestampMs <= '".strtotime($end_date)."' AND userID = '".$_SESSION['userid']."'";
		// Fetch Results
		if ($result = mysqli_query($conn, $sql)) 
		{
			$counter = 0;
			while($row = mysqli_fetch_assoc($result)) 
			{
				// Store results in activity_time
				$activity_time[$counter][0] = $row["timestampMs"];
				$activity_time[$counter][1] = $row["activitytype"];
				$counter++;
			}
			$size = $counter; // Get size of array
		}
		else
		{
		}
		// Iterate activity_time for each activity
		for($i=0;$i<count($activities_array);$i++)
		{
			for($counter=0;$counter<$size;$counter++)
			{
				if($activity_time[$counter][1] == $activities_array[$i]) // If the current row has the correct activity
				{
					$hourly_activity[$i][intval(date('H', $activity_time[$counter][0]/1000))]++;
				}
			}
			for($j=0;$j<24;$j++)
			{
				if($hourly_activity[$i][$j] > $max)
				{
					$max = $hourly_activity[$i][$j];
					$hour_index[$i] = $j;
				}
			}
			$max_values[$i] = $max;
			if($max == 0)
			{
				$hour_index[$i] = 0;
			}
			$max = 0;
		}
		$hour_index_json = json_encode($hour_index);
		$max_values_json = json_encode($max_values);
		echo	'<canvas id="hour_chart"></canvas>';
		
		/* Day of the week with most records per activity */
		if($num_activities>0)
		{
			echo 	'<p class="text-primary font-weight-bold">Day of the week with most activities: </p>';			
		}
		// Dictionary for days in a week
		$days_array = [
			"Mon" => 1,
			"Tue" => 2,
			"Wed" => 3,
			"Thu" => 4,
			"Fri" => 5,
			"Sat" => 6,
			"Sun" => 7,
		];

		$daily_activity = array_fill(0, 7, array_fill(0, 32, 0));
		$day_index = array();
		$cur_timestampMs = strtotime($start_date)*1000;
		$last_timestampMs = strtotime($end_date)*1000;
		$max = 0;
		$max_values = array();
		$weekdays = array();
		$days_table = array();
		// Value of a day
		$day = 1000*60*60*24;
		
		for($i=0;$i<count($activities_array);$i++)
		{
			for($counter=0;$counter<$size;$counter++)
			{
				if($activity_time[$counter][1] == $activities_array[$i]) // If the current row has the correct activity
				{
					$daily_activity[$i][$days_array[date('D', $activity_time[$counter][0]/1000)]]++;
				}
			}
			for($j=0;$j<7;$j++)
			{
				if($daily_activity[$i][$j] > $max)
				{
					$max = $daily_activity[$i][$j];
					$day_index[$i] = $j;
				}
			}
			$max_values[$i] = $max;
			if($max == 0)
			{
				$day_index[$i] = -1;
			}
			$max = 0;
		}
		
		// Get the array with the correct weekdays for each activity
		$weekdays = [
			1 => "Monday",
			2 => "Tuesday",
			3 => "Wednesday",
			4 => "Thursday",
			5 => "Friday",
			6 => "Saturday",
			7 => "Sunday",
		];
		foreach($day_index as $value)
		{
			if($value == -1)
			{
				array_push($days_table, '');
			}
			else
			{
				array_push($days_table, $weekdays[$value]);
			}
		}
		
		$days_json = json_encode($days_table);
		echo	'<canvas id="day_chart"></canvas>';
		
		// Heatmap
		if($num_activities>0)
		{
			echo	'<h4 class="text-primary"> Heatmap </h4>';
			echo	'<div id = "map"></div>';
		}
		// Coordinates
		$longitude = Array();
		$latitude = Array();
		$coord_counter = 0;
		// SQL
		$sql = "SELECT longitudeE7, latitudeE7 FROM locations WHERE userID = '".$_SESSION['userid']."'";
		$result = mysqli_query($conn, $sql);
		while($row = mysqli_fetch_assoc($result))
		{
			$longitude[$coord_counter] = $row["longitudeE7"];
			$latitude[$coord_counter] = $row["latitudeE7"];
			$coord_counter++;
		}
		// Correct the coordinate values
		for($i=0; $i<$coord_counter; $i++)
		{
			$longitude[$i] = $longitude[$i]/10000000;
			$latitude[$i] = $latitude[$i]/10000000;
		}
		
		$longitude = json_encode($longitude);
		$latitude = json_encode($latitude);
		}
	}
	else
	{
		echo	'<p class="font-weight-bold text-warning">No intervals given yet</p>';
	}
	
	echo	'<br><br><br></div>';
}
else // If User logged out
{
	echo	'<div class="container">
            <style>
            body{
                background-image: url("https://wallpaperaccess.com/full/366025.jpg");
            } </style>
            </div>
            <div class="fixed-bottom">
            <footer class="page-footer unique-color-dark pt-0"> 
            <div class="footer-copyright text-center pt-0">©2020
            <a href="Homepage.php">Project WEB</a>
            </div>
            <a href="Admin.php" class="btn btn-blue btn-mg btn-block">Login as Admin</a>
            </footer>
            </div>'; 
}
?>
