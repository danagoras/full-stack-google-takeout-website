<?php
	session_start();
	require 'includes/dbh.inc.php';
?>
<!DOCTYPE html>
<html>
<head>
<title>Ecologylife| Web Project</title>
<link rel="icon" 
      type="image/png" 
      href="images/ecologylife_icon.png">
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
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
<script type="text/javascript" src="js/leaflet-heat.js"></script>
</head>
<body>
<header>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top py-lg-0">
        <a class="navbar-brand" href="Homepage.php"><img id="logo" alt="Logo" src="images\ECOLIFE3.png" width="200" height="65"></a>
        <ul class="nav navbar-nav navbar-right">
          <?php
          include 'includes\login.php';
          ?>
          </ul>
    </nav>
    <div class="container">
      <div class="row">
        <div class="col-sm-12 data-info">
          <?php
          include 'includes\UserData.php';
          ?>
        </div>
      </div>
    </div>
</header>

<!--SignUp Form-->
<form action="includes/signup.inc.php" method="post">
<div class="modal fade" id="modalRegisterForm" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
  aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header text-center">
        <h4 class="modal-title w-100 font-weight-bold">Sign up</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body mx-3">
        <div class="md-form mb-5">
          <i class="fas fa-user prefix grey-text"></i>
          <input type="text" name="uid" placeholder="Username" required>
          <label data-error="wrong" data-success="right" for="orangeForm-name">Your name</label>
        </div>
        <div class="md-form mb-5">
          <i class="fas fa-envelope prefix grey-text"></i>
          <input type="email" name="mail" placeholder="Email" required>
          <label data-error="wrong" data-success="right" for="orangeForm-email">Your email</label>
        </div>

        <div class="md-form mb-4">
          <i class="fas fa-lock prefix grey-text"></i>
          <input type="password" name="psw" placeholder="Password" required>
          <label data-error="wrong" data-success="right" for="orangeForm-pass">Your password</label>  
        </div>

        <div class="md-form mb-4">
        <i class="fas fa-marker prefix grey-text"></i>
          <input type="password" name="psw-repeat" placeholder="Repeat your Password">
          <label data-error="wrong" data-success="right" for="orangeForm-repeat">Repeat your password</label>
        </div>

      </div>
      <div class="modal-footer d-flex justify-content-center">
        <button class="btn btn-deep-orange active" name="signup-submit">Sign up</button>
      </div>
    </div>
  </div>
</div>
</form>
<!-- Login Form-->
<form action="includes/login.inc.php" method="post">
        <div class="modal fade" id="modalLoginForm" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header text-center">
                <h4 class="modal-title w-100 font-weight-bold">Sign in</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body mx-3">
                <div class="md-form mb-5">
                  <i class="fas fa-envelope prefix grey-text"></i>
                  <input type="text" name="mailuid" placeholder="Username/Email" required>
                  <label data-error="wrong" data-success="right" for="defaultForm-email">Your Username</label>
                </div>
                <div class="md-form mb-4">
                  <i class="fas fa-lock prefix grey-text"></i>
                  <input type="password" name="psw" placeholder="Password" required>
                  <label data-error="wrong" data-success="right" for="defaultForm-pass">Your password</label>
                </div>
              </div>
              <div class="modal-footer d-flex justify-content-center">
                <button class="btn btn-default active" name="login-submit">Login</button>
              </div>
            </div>
          </div>
        </div>
</form>

<script> 
// The EMS chart
const ctx = document.getElementById('chart').getContext('2d');
ctx.canvas.width  = window.innerWidth;
ctx.canvas.height = window.innerHeight/2;
var months = <?php echo $months ?>;
var data = <?php echo $scores ?>;
const EMSChart = new Chart(ctx, {
    type: 'bar',

    // The data for our dataset
    data: {
        labels: months,
        datasets: [{
            label: 'Ecological Mobility Score',
            backgroundColor: 'rgb(255, 99, 132)',
            borderColor: 'rgb(255, 99, 132)',
            data: data
        }]
    }
});
</script>
<script>
// The Activity Percentages chart
const ctx2 = document.getElementById('activities').getContext('2d');
ctx2.canvas.width  = window.innerWidth;
ctx2.canvas.height = window.innerHeight/2;
var activities = <?php echo $activities_array_json ?>;
var ylabels = <?php echo $activity_prc_json ?>;
const ActivityChart = new Chart(ctx2, {
    type: 'bar',

    // The data for our dataset
    data: {
        labels: activities,
        datasets: [{
            label: 'Activities Percentages',
            backgroundColor: 'rgb(255, 99, 132)',
            borderColor: 'rgb(255, 99, 132)',
            data: ylabels
        }]
    }
});
</script>
<script>
// The Activity Hours Chart
var xlabels = <?php echo $activities_array_json ?>;
var ylabels = <?php echo $hour_index_json ?>;
var rlabels = <?php echo $max_values_json ?>;
var ctx3 = document.getElementById('hour_chart').getContext('2d');
ctx3.canvas.width  = window.innerWidth;
ctx3.canvas.height = window.innerHeight/2;
var HourChart = new Chart(ctx3, {
    // The type of chart we want to create
    type: 'bar',

    // The data for our dataset
    data: {
        labels: xlabels,
        datasets: [{
            label: 'Activity Hours',
            backgroundColor: 'rgb(255, 99, 132)',
            borderColor: 'rgb(255, 99, 132)',
            data: ylabels
        }]
    }
});
</script>
<script>
// The Activity Days Chart
var xlabels = <?php echo $activities_array_json ?>;
var ylabels = <?php echo $days_json ?>;
var ctx4 = document.getElementById('day_chart').getContext('2d');
ctx4.canvas.width  = window.innerWidth;
ctx4.canvas.height = window.innerHeight/2;

var config = {
	type: 'line',
	data: {
		xLabels: xlabels,
		yLabels: ['Sunday', 'Saturday', 'Friday', 'Thursday', 'Wednesday', 'Tuesday', 'Monday'],
		datasets: [{
			label: 'Activity Days',
			data: ylabels,
			fill: false ,
			backgroundColor: 'rgb(255, 99, 132)',
            borderColor: 'rgb(255, 99, 132)',
			showLine: false
		}]
	},
	options: {
		responsive: true,
		title: {
			display: true,
			text: 'Most common day for each activity'
		},
		scales: {
			xAxes: [{
				display: true,
				scaleLabel: {
					display: true,
					labelString: 'Activities'
				}
			}],
			yAxes: [{
				type: 'category',
				position: 'left',
				display: true,
				scaleLabel: {
					display: true,
					labelString: 'Days of the Week'
				},
				ticks: {
					reverse: true
				}
			}]
		}
	}
};

window.onload = function() {
	var ctx4 = document.getElementById('day_chart').getContext('2d');
	window.myLine = new Chart(ctx4, config);
};
</script>
<script>
var latitude = <?php echo $latitude ?>;
var longitude = <?php echo $longitude ?>;
var addressPoints = [];	
var map = L.map('map').setView([38.2490766,21.7105864] ,13);
	
L.tileLayer('https://api.maptiler.com/maps/streets/{z}/{x}/{y}.png?key=IMMFzKrdv5OJBbKwDXxp', {
attribution: '<a href="https://www.maptiler.com/copyright/" target="_blank">&copy; MapTiler</a> <a href="https://www.openstreetmap.org/copyright" target="_blank">&copy; OpenStreetMap contributors</a>',
}).addTo(map);
	
latitude.forEach(function(value, index)
{
	addressPoints.push([latitude[index], longitude[index]]);
});

var heat = L.heatLayer(addressPoints, {radius: 10, minOpacity: 0.5, gradient: {0.4: 'blue', 0.65: 'lime', 1: 'red'}}).addTo(map);
</script>

<script type="text/javascript" src="js/jquery.min.js"></script> 
<script type="text/javascript" src="js/popper.min.js"></script>
<script type="text/javascript" src="js/bootstrap.min.js"></script>
<script type="text/javascript" src="js/mdb.min.js"></script>
</body>
</html>