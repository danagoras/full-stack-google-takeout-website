<?php
if(isset($_POST["submit-json"]))
{
	// Connect to the database
	require 'dbh.inc.php';

	$jsonFile = $_FILES['jsonFile'];
	
	$fileName = $_FILES['jsonFile']['name'];
	$fileTmpName = $_FILES['jsonFile']['tmp_name'];
	$fileSize = $_FILES['jsonFile']['size'];
	$fileError = $_FILES['jsonFile']['error'];
	$fileType = $_FILES['jsonFile']['type'];
	
	$fileExt = explode('.', $fileName);
	$fileActualExt = strtolower(end($fileExt));
	
	$allowed = array('json');
	
	function distance($lat1, $lon1, $lat2, $lon2, $unit) 
	{
		if (($lat1 == $lat2) && ($lon1 == $lon2)) {
			return 0;
		}
		else 
		{
			$theta = $lon1 - $lon2;
			$dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
			$dist = acos($dist);
			$dist = rad2deg($dist);
			$miles = $dist * 60 * 1.1515;
			$unit = strtoupper($unit);

			if ($unit == "K") 
			{
			  return ($miles * 1.609344);
			} 
			else if ($unit == "N") 
			{
			  return ($miles * 0.8684);
			} 
			else 
			{
			  return $miles;
			}
		}
	}
	
	if(in_array($fileActualExt, $allowed))
	{
		if($fileError === 0)
		{
			if($fileSize < 100000000)
			{
				session_start();
				// Create the correct file name
				$fileNameNew = "uploadedfile.".$fileActualExt;
				// Upload file to a folder
				$fileDestination = '../uploads/'.$fileNameNew;
				move_uploaded_file($fileTmpName, $fileDestination);
				$myfile = fopen("../uploads/".$fileNameNew, "r") or die("Unable to open file!");
				$readfile = fread($myfile,filesize("../uploads/".$fileNameNew));
				$output = json_decode($readfile);
				
				// echo $output->locations[0]->timestampMs;
				// Connect to the correct table
				$sql = "SELECT * FROM locations WHERE userID=?";
				$stmt = mysqli_stmt_init($conn);
				if (!mysqli_stmt_prepare($stmt, $sql))
				{
					header("Location: ../Homepage.php?error=sqlerror");
					exit();
				}
				else
				{
					$sql = "INSERT INTO locations (userID, heading, activitytype, activityconfidence, activitytimestampMs, verticalAccuracy, velocity, accuracy, longitudeE7, latitudeE7, altitude, timestampMs) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
					$stmt = mysqli_stmt_init($conn);
					if (!mysqli_stmt_prepare($stmt, $sql))
					{
						header("Location: ../Homepage.php?error=sqlerror");
						exit();
					}
					else
					{
						// Dealing with the missing values in each locations object
						// Iterating through all location objects
						
						for($i = 0;  $i < count($output->locations); $i++)
						{
							// Cut coordinates 10kms far from Patras
							// while(distance($output->locations[$i]->latitudeE7, $output->locations[$i]->longitudeE7, 38.230462,21.753150, "K") > 10)
							// {
								// $i++;
								// if($i == (count($output->locations) - 1)) 
								// {
									// break;
								// }
							// }
							//echo '<script>console.log(distance($output->locations[$i]->latitudeE7, $output->locations[$i]->longitudeE7, 38.230462,21.753150, "K")); </script>';
							
							$meme = distance(32.9697, -96.80322, 29.46786, -98.53506, "K");
							// Heading
							if(!isset($output->locations[$i]->heading))
							{
								$heading = 0;
							}
							else
							{
								$heading = $output->locations[$i]->heading;
							}
							// Activity Type
							if(!isset($output->locations[$i]->activity[0]->activity[0]->type))
							{
								$activitytype = "";
							}
							else
							{
								$activitytype = $output->locations[$i]->activity[0]->activity[0]->type;
							}
							// Activity Confidence
							if(!isset($output->locations[$i]->activity[0]->activity[0]->confidence))
							{
								$activityconfidence = 0;
							}
							else
							{
								$activityconfidence = $output->locations[$i]->activity[0]->activity[0]->confidence;
							}
							// Activity TimestampMs
							if(!isset($output->locations[$i]->activity[0]->timestampMs))
							{
								$activitytimestampMs = "";
							}
							else
							{
								$activitytimestampMs = $output->locations[$i]->activity[0]->timestampMs;
							}
							// Vertical Accuracy
							if(!isset($output->locations[$i]->verticalAccuracy))
							{
								$verticalAccuracy = 0;
							}
							else
							{
								$verticalAccuracy = $output->locations[$i]->verticalAccuracy;
							}
							// Velocity
							if(!isset($output->locations[$i]->velocity))
							{
								$velocity = 0;
							}
							else
							{
								$velocity =  $output->locations[$i]->velocity;
							}
							// Accuracy
							if(!isset($output->locations[$i]->accuracy))
							{
								$accuracy = 0;
							}
							else
							{
								$accuracy = $output->locations[$i]->accuracy;
							}
							// Longitude
							if(!isset($output->locations[$i]->longitudeE7)) // Not needed
							{
								$longitudeE7 = 0;
							}
							else
							{
								$longitudeE7 = $output->locations[$i]->longitudeE7;
							}
							// Latitude
							if(!isset($output->locations[$i]->latitudeE7)) // Not needed
							{
								$latitudeE7 = 0;
							}
							else
							{
								$latitudeE7 = $output->locations[$i]->latitudeE7;
							}
							// Altitude
							if(!isset($output->locations[$i]->altitude))
							{
								$altitude = 0;
							}
							else
							{
								$altitude = $output->locations[$i]->altitude;
							}
							// TimestampMs
							if(!isset($output->locations[$i]->timestampMs))
							{
								$TimestampMs = "";
							}
							else
							{
								$TimestampMs = $output->locations[$i]->timestampMs;
							}
							mysqli_stmt_bind_param($stmt, "sisisiiiddis", $_SESSION['userid'], $heading, $activitytype, $activityconfidence, $activitytimestampMs, $verticalAccuracy, $velocity, $accuracy, $longitudeE7, $latitudeE7, $altitude, $TimestampMs);
							mysqli_stmt_execute($stmt);
						}
						
						header("Location: ../Homepage.php?upload=success");
						exit();
					}
				}
			}
			else
			{
				echo '<div class="alert alert-warning alert-dismissible fade show" role="alert">
				<strong>Your file is too big.</strong></div>';
			}
		}
		else
		{
			echo '<div class="alert alert-warning alert-dismissible fade show" role="alert">
		<strong>There was an error uploading your file!</strong></div>';
		}
	}
	else
	{
		echo '<div class="alert alert-warning alert-dismissible fade show" role="alert">
		<strong>Error!</strong> You cannot upload files of this type!
		<button type="button" class="close" data-dismiss="alert" aria-label="Close">
		  <span aria-hidden="true">&times;</span>
		</button>
	  </div>';
	}
	mysqli_stmt_close($stmt);
	mysqli_close($conn);
}
?>