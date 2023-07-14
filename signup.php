<?php
	// Start the session
	session_start();
	if(isset($_SESSION["loggedIn"]) && $_SESSION["loggedIn"])
		header("Location: homepage.php");
?>
<!DOCTYPE html>
<html>
	<title> Sign Up </title>
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1"/>
		<meta charset="utf-8"/>
		<link rel="icon" href=""/>
		<!--<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css"/>-->
		<link rel="stylesheet" href="font-awesome-4.5.0/css/font-awesome.min.css"/> <!-- brownout option -->
		<!--<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>-->
		<script src="node_modules/jquery/dist/jquery.min.js"></script>
		<!--<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>-->
		<script src="node_modules/popper.js/dist/umd/popper.min.js"></script> <!-- remove umd? -->
		<link rel = "stylesheet" href = "css/signup.css"/>
		<script src="js/signup.js"></script>
	</head>
	<body>
		<div class = "container">
			<h1> Sign Up Now! </h1>
			<div id="divInpUname" class="form-group row">
				<label for="inpUname" class="col-form-label col-sm-3"> Username </label>
				<div class="col-sm-9">
					<input id="inpUname" type="text" class="form-control" required/>
					<p class="pInpInfo"><i class="fa fa-exclamation-circle"></i> <span>This username is already taken!</span></p>
				</div>
				<!--<p class="pInpInfo"><i class="fa fa-check"></i> This username is already taken!</p>-->
			</div>
			<!--<p class="pInpInfo"><i class="fa fa-exclamation-circle"></i> This username is already taken!</p>-->
			<div id="divInpPass" class="form-group row pass">
				<label for="inpPass" class="col-form-label col-sm-3"> Password </label>
				<div class="col-sm-9">
					<input id="inpPass" type="password" class="form-control" required/>
				</div>
			</div>
			<div id="divInpRePass" class="form-group row pass">
				<label for="inpRePass" class="col-form-label col-sm-3"> Re-type Password </label>
				<div class="col-sm-9">
					<input id="inpRePass" type="password" class="form-control" required/>
					<p class="pInpInfo"><i class="fa fa-exclamation-circle"></i> <span>Passwords do not match!</span></p>
				</div>
			</div>
			<div id="divInpIcon">
				<label> Choose your Icon: </label>
				<p class="pInpInfo"><i class="fa fa-exclamation-circle"></i> <span>Click on an icon below!</span></p>
				<div id="divIconList">
					<div class="divIcon"><img src="images/icons/cheetah.png"/></div>
					<div class="divIcon"><img src="images/icons/deer.png"/></div>
					<div class="divIcon"><img src="images/icons/fish.png"/></div>
					<div class="divIcon"><img src="images/icons/lion.png"/></div>
					<div class="divIcon"><img src="images/icons/poo.png"/></div>
					<div class="divIcon"><img src="images/icons/sea goat.png"/></div>
					<div class="divIcon"><img src="images/icons/squirrel.png"/></div>
					<div class="divIcon"><img src="images/icons/stork.png"/></div>
					<div class="divIcon"><img src="images/icons/whale.png"/></div>
					<!-- instead, make div with background image as below
					<img src="images/icons/cheetah.png"/>
					<img src="images/icons/deer.png" class="inpSelected"/>
					<img src="images/icons/fish.png"/>
					-->
				</div>
			</div>
			<div class="text-center">
				<button id = "btnSignUp" class="btn btn-lg btn-primary my-4">Sign Up</button>
				<p id="pLoginLine"> Already have an account? <span>Login here</span></p>
			</div>
		</div>
	</body>
</html>