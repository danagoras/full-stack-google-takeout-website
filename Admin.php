<?php
	session_start();
	require 'includes/dbh.inc.php';
?>
<!DOCTYPE html>
<html>
<head>

<title>Admin</title>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta http-equiv="x-ua-compatible" content="ie=edge">
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.js"></script>
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.11.2/css/all.css">
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700&display=swap">
<link rel="stylesheet" href="css/bootstrap.min.css">
<link rel="stylesheet" href="css/mdb.min.css">
<link rel="stylesheet" href="css/homepage_style2.css">
</head>
<body>

<h1>Admin web page</h1>

<?php
    if (isset($_SESSION['uid'])) // User logged in
    {
        echo 'You are logged in as: ' .$_SESSION['uid'];
        echo	'<form action="includes\logoutadmin.inc.php" method="post">
					<button type="submit" class="btn btn-default btn-sm active " name="logout-submit">Logout</button>
					</form>';
			
		// Types of activities
		$activities_array = ["ON_BICYCLE","ON_FOOT","RUNNING","WALKING","IN_VEHICLE","STILL","TILTING"];
		// Number of activities per type
		$sql = "SELECT activitytype, userID, timestampMs FROM locations";
		$result = mysqli_query($conn, $sql);
		$activities = Array(); // chart 1
		$userID = Array(); // chart 2
		$timestamps = Array(); // chart 3
		$activity_counters =  array_fill(0, 7, 0);
		
		
		while($row = mysqli_fetch_assoc($result))
		{
			$activities[] = $row["activitytype"];
			$userID[] = $row["userID"];
			$timestamps[] = $row["timestampMs"];
		}
		$result->close();
		for($i=0; $i<7; $i++) // For each activity type
		{
			for($j=0;$j<count($activities);$j++) // Parse through all the activities
			{
				if($activities[$j] == $activities_array[$i]) // If current row has the correct activity
				{
					$activity_counters[$i]++;
				}
			}
		}
		
		echo	'<h1> 1st Chart </h1><canvas id="chart"></canvas>';
		
		// Users input chart
		$sql = "SELECT userID, username FROM users";
		$result = mysqli_query($conn, $sql);
		$usernames = Array();
		$userIDs = Array();
		
		
		while($row = mysqli_fetch_assoc($result))
		{
			$usernames[] = $row["username"];
			$userIDs[] = $row["userID"];
		}
		$result->close();
		$users_counter = array_fill(0, count($usernames), 0);
		
		for($i=0; $i<count($usernames);$i++) // Iterate through all the users
		{
			for($j=0;$j<count($activities);$j++) // Parse through all the activities
			{
				if($userIDs[$i] == $userID[$j])
				{
					$users_counter[$i]++;
				}
			}
		}
		
		
		echo	'<h1> 2nd chart </h1><canvas id="chart2"></canvas>';
		
		// Activities per month
		$months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
		$months_counter = array_fill(0, 12, 0);
		
		for($i=0;$i<count($months);$i++)
		{
			for($j=0;$j<count($timestamps);$j++)
			{
				
				if(date('F',$timestamps[$j]/1000) == $months[$i])
				{
					$months_counter[$i]++;
				}
			}
		}
		
		echo	'<h1> 3rd chart </h1><canvas id="chart3"></canvas>';
		
		// Activities per weekday
		$weekdays = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"];
		$weekdays_counter = array_fill(0, 7, 0);
		
		for($i=0;$i<count($weekdays);$i++)
		{
			for($j=0;$j<count($timestamps);$j++)
			{
				if(date('l',$timestamps[$j]/1000) == $weekdays[$i])
				{
					$weekdays_counter[$i]++;
				}
			}
		}
		
		echo	'<h1> 4th chart </h1><canvas id="chart4"></canvas>';
		
		// Activities per hour
		$hours = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23];
		$hours_counter = array_fill(0, 24, 0);
		
		for($i=0;$i<count($hours);$i++)
		{
			for($j=0;$j<count($timestamps);$j++)
			{
				if(date('H',$timestamps[$j]/1000) == $hours[$i])
				{
					$hours_counter[$i]++;
				}
			}
		}
		
		echo	'<h1> 5th chart </h1><canvas id="chart5"></canvas>';
		
		// Activities per year
		$years = Array();
		
		for($i=0;$i<count($timestamps); $i++)
		{
			if(!in_array(date('Y', $timestamps[$i]/1000), $years))
			{
				array_push($years, date('Y', $timestamps[$i]/1000));
			}
		}
		sort($years, SORT_NUMERIC);
		$years_counter = array_fill(0, count($years), 0);
		
		for($i=0;$i<count($years);$i++)
		{
			for($j=0;$j<count($timestamps);$j++)
			{
				if(date('Y',$timestamps[$j]/1000) == $years[$i])
				{
					$years_counter[$i]++;
				}
			}
		}
		
		echo	'<h1> 6th chart </h1><canvas id="chart6"></canvas>';
		
		// Heatmap for all users
		echo	'<form name="form1" id="form1" class="light-green-text" method="POST">
			Select Year Interval: <select name="start_year" id="start_year">
				<option value="2020">2020</option>
				<option value="2019">2019</option>
				<option value="2018">2018</option>
				<option value="2017">2017</option>
				<option value="2016">2016</option>
				<option value="2015">2015</option>
				<option value="2014">2014</option>
				<option value="2013">2013</option>
				<option value="2012">2012</option>
				<option value="2011">2011</option>
				<option value="2010">2010</option>
				<option value="2009">2009</option> selected="selected">Starting Year</option>
			  </select>
			- <select name="end_year" id="end_year">
				<option value="2020">2020</option>
				<option value="2019">2019</option>
				<option value="2018">2018</option>
				<option value="2017">2017</option>
				<option value="2016">2016</option>
				<option value="2015">2015</option>
				<option value="2014">2014</option>
				<option value="2013">2013</option>
				<option value="2012">2012</option>
				<option value="2011">2011</option>
				<option value="2010">2010</option>
				<option value="2009">2009</option> selected="selected">End Year</option>
			  </select>
			  Select Month Interval: <select name="start_month" id="start_month">
				<option value="January">January</option>
				<option value="February">February</option>
				<option value="March">March</option>
				<option value="April">April</option>
				<option value="May">May</option>
				<option value="June">June</option>
				<option value="July">July</option>
				<option value="August">August</option>
				<option value="September">September</option>
				<option value="October">October</option>
				<option value="November">November</option>
				<option value="December">December</option> selected="selected">Starting Month</option>
			  </select>
			  - <select name="end_month" id="end_month">
				<option value="January">January</option>
				<option value="February">February</option>
				<option value="March">March</option>
				<option value="April">April</option>
				<option value="May">May</option>
				<option value="June">June</option>
				<option value="July">July</option>
				<option value="August">August</option>
				<option value="September">September</option>
				<option value="October">October</option>
				<option value="November">November</option>
				<option value="December">December</option> selected="selected">End Month</option>
			  </select>
			Select Day Interval: <select name="start_day" id="start_day">
				<option value="Monday">Monday</option>
				<option value="Tuesday">Tuesday</option>
				<option value="Wednesday">Wednesday</option>
				<option value="Thursday">Thursday</option>
				<option value="Friday">Friday</option>
				<option value="Saturday">Saturday</option>
				<option value="Sunday">Sunday</option> selected="selected">Starting Day</option>
			  </select>
			- <select name="end_day" id="end_day">
				<option value="Monday">Monday</option>
				<option value="Tuesday">Tuesday</option>
				<option value="Wednesday">Wednesday</option>
				<option value="Thursday">Thursday</option>
				<option value="Friday">Friday</option>
				<option value="Saturday">Saturday</option>
				<option value="Sunday">Sunday</option> selected="selected">End Day</option>
			  </select>
			  Select Hour Interval: <select name="start_hour" id="start_hour">
				<option value="0">0</option>
				<option value="1">1</option>
				<option value="2">2</option>
				<option value="3">3</option>
				<option value="4">4</option>
				<option value="5">5</option>
				<option value="6">6</option>
				<option value="7">7</option>
				<option value="8">8</option>
				<option value="9">9</option>
				<option value="10">10</option>
				<option value="11">11</option>
				<option value="12">12</option>
				<option value="13">13</option>
				<option value="14">14</option>
				<option value="15">15</option>
				<option value="16">16</option>
				<option value="17">17</option>
				<option value="18">18</option>
				<option value="19">19</option>
				<option value="20">20</option>
				<option value="21">21</option>
				<option value="22">22</option>
				<option value="23">23</option> selected="selected">Starting Hour</option>
			  </select>
			 - <select name="end_hour" id="end_hour">
				<option value="0">0</option>
				<option value="1">1</option>
				<option value="2">2</option>
				<option value="3">3</option>
				<option value="4">4</option>
				<option value="5">5</option>
				<option value="6">6</option>
				<option value="7">7</option>
				<option value="8">8</option>
				<option value="9">9</option>
				<option value="10">10</option>
				<option value="11">11</option>
				<option value="12">12</option>
				<option value="13">13</option>
				<option value="14">14</option>
				<option value="15">15</option>
				<option value="16">16</option>
				<option value="17">17</option>
				<option value="18">18</option>
				<option value="19">19</option>
				<option value="20">20</option>
				<option value="21">21</option>
				<option value="22">22</option>
				<option value="23">23</option> selected="selected">End Hour</option>
			  </select>
			  Select Multiple Activity Types: <select name="select[]" multiple size = 7>
				<option value="ON_BICYCLE">ON_BICYCLE</option>
				<option value="ON_FOOT">ON_FOOT</option>
				<option value="RUNNING">RUNNING</option>
				<option value="WALKING">WALKING</option>
				<option value="IN_VEHICLE">IN_VEHICLE</option>
				<option value="STILL">STILL</option>
				<option value="TILTING">TILTING</option>
			  </select>
			  <input type="submit" value="Submit" name="interval-submit">  
			</form><br>';
			
		if(isset($_POST["interval-submit"]))  
		{ 
			// Check if any option is selected 
			if(isset($_POST["select"]))  
			{ 
				echo	'<h2 class="text-primary"> User Selection: </h2>';
				echo 	'<p class="text-primary"> Years: ' .$_POST['start_year']. '-' .$_POST['end_year']. '<br>Months: ' .$_POST['start_month']. '-' .$_POST['end_month']. '<br>Days: ' .$_POST['start_day']. '-' .$_POST['end_day']. '<br>Hours: ' .$_POST['start_hour']. '-' . $_POST['end_hour'].'<br>Activities: ';
				// Retrieving each selected option 
				foreach ($_POST['select'] as $select)  
				{
					print "$select<br>"; 
				}
				echo	'</p>';
			} 
			
			// Get the correct timestamps
			$start_timestamp = $_POST['start_day']." ".$_POST['start_month']." ".$_POST['start_year']." ".$_POST['start_hour'];
			echo	$start_timestamp;
			echo 	strtotime($start_timestamp);
		}
		else
		{
			echo "Select an option first!"; 
		} 
    }
    else 
    {
        echo '<form action="includes\admin.inc.php" method="post"> 
        <div class="login-box"> 
            <h1>Login</h1> 
  
            <div class="textbox"> 
                <i class="fa fa-user" aria-hidden="true"></i> 
                <input type="text" placeholder="Adminname"
                         name="name" value=""> 
            </div> 
  
            <div class="textbox"> 
                <i class="fa fa-lock" aria-hidden="true"></i> 
                <input type="password" placeholder="Password"
                         name="password" value=""> 
            </div> 
  
            <input class="button" type="submit"
                     name="login-btn" value="Sign In"> 
        </div> 
    </form> 

<h2> Delete all data from the table </h2>
<button type="button"  onclick="confirmDeletion()" name="delete-button">Delete Table</button><br>
<h3> Return to Homepage </h3> 
<a href="Homepage.php" class="button">Home</a>';
    }



?>

<script>
function confirmDeletion()
{
	var r = confirm("Are you sure you want to delete the data in the database?");
	if(r == true)
	{
		window.location.href = 'includes/delete_table.inc.php';
	}
	else
	{
		txt = "Deletion cancelled";
	}
}
</script>
<script>
const ctx = document.getElementById('chart').getContext('2d');
ctx.canvas.width  = window.innerWidth;
ctx.canvas.height = window.innerHeight/2;
var activity_types = <?php echo json_encode($activities_array) ?>;
var data = <?php echo json_encode($activity_counters) ?>;
const ATChart = new Chart(ctx, {
    type: 'bar',

    // The data for our dataset
    data: {
        labels: activity_types,
        datasets: [{
            label: '# Activity Types',
			ylabels: ["ON_BICYCLE","ON_FOOT","RUNNING","WALKING","IN_VEHICLE","STILL","TILTING"],
            backgroundColor: 'rgb(255, 99, 132)',
            borderColor: 'rgb(255, 99, 132)',
            data: data
        }]
    }
});
</script>
<script>
const ctx2 = document.getElementById('chart2').getContext('2d');
ctx2.canvas.width  = window.innerWidth;
ctx2.canvas.height = window.innerHeight/2;
var users = <?php echo json_encode($usernames) ?>;
var data = <?php echo json_encode($users_counter) ?>;
const UserChart = new Chart(ctx2, {
    type: 'bar',

    // The data for our dataset
    data: {
        labels: users,
        datasets: [{
            label: 'Activities per user',
            backgroundColor: 'rgb(255, 99, 132)',
            borderColor: 'rgb(255, 99, 132)',
            data: data
        }]
    }
});
</script>
<script>
const ctx3 = document.getElementById('chart3').getContext('2d');
ctx3.canvas.width  = window.innerWidth;
ctx3.canvas.height = window.innerHeight/2;
var months = <?php echo json_encode($months) ?>;
var months_counter = <?php echo json_encode($months_counter) ?>;
const MonthsChart = new Chart(ctx3, {
    type: 'bar',

    // The data for our dataset
    data: {
        labels: months,
        datasets: [{
            label: 'Activities per month',
            backgroundColor: 'rgb(255, 99, 132)',
            borderColor: 'rgb(255, 99, 132)',
            data: months_counter
        }]
    }
});
</script>
<script>
const ctx4 = document.getElementById('chart4').getContext('2d');
ctx4.canvas.width  = window.innerWidth;
ctx4.canvas.height = window.innerHeight/2;
var weekdays = <?php echo json_encode($weekdays) ?>;
var weekdays_counter = <?php echo json_encode($weekdays_counter) ?>;
const WeekdaysChart = new Chart(ctx4, {
    type: 'bar',

    // The data for our dataset
    data: {
        labels: weekdays,
        datasets: [{
            label: 'Activities per day of the week',
            backgroundColor: 'rgb(255, 99, 132)',
            borderColor: 'rgb(255, 99, 132)',
            data: weekdays_counter
        }]
    }
});
</script>
<script>
const ctx5 = document.getElementById('chart5').getContext('2d');
ctx5.canvas.width  = window.innerWidth;
ctx5.canvas.height = window.innerHeight/2;
var hours = <?php echo json_encode($hours) ?>;
var hours_counter = <?php echo json_encode($hours_counter) ?>;
const hoursChart = new Chart(ctx5, {
    type: 'bar',

    // The data for our dataset
    data: {
        labels: hours,
        datasets: [{
            label: 'Activities per hour of the day',
            backgroundColor: 'rgb(255, 99, 132)',
            borderColor: 'rgb(255, 99, 132)',
            data: hours_counter
        }]
    }
});
</script>
<script>
const ctx6 = document.getElementById('chart6').getContext('2d');
ctx6.canvas.width  = window.innerWidth;
ctx6.canvas.height = window.innerHeight/2;
var years = <?php echo json_encode($years) ?>;
var years_counter = <?php echo json_encode($years_counter) ?>;
const YearsChart = new Chart(ctx6, {
    type: 'bar',

    // The data for our dataset
    data: {
        labels: years,
        datasets: [{
            label: 'Activities per year',
            backgroundColor: 'rgb(255, 99, 132)',
            borderColor: 'rgb(255, 99, 132)',
            data: years_counter
        }]
    }
});
</script>
<script type="text/javascript" src="js/jquery.min.js"></script> 
<script type="text/javascript" src="js/popper.min.js"></script>
<script type="text/javascript" src="js/bootstrap.min.js"></script>
<script type="text/javascript" src="js/mdb.min.js"></script>

</body>
</html>