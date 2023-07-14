<?php
	// Start the session
	session_start();
	if(isset($_SESSION["loggedIn"]) && $_SESSION["loggedIn"])
		header("Location: homepage.php");
?>
<!DOCTYPE html>
<html>
	<title> Login to ChatApp </title>
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
		<link rel = "stylesheet" href = "css/login.css"/>
		<script src="js/login.js"></script>
	</head>
	<body>
		<div class = "container">
			<div id="divLoginArea">
				<h1 id = "hTitle"> PHP Open-Access Chatroom </h1>
				<div id="divInpFields">
					<input id = "inpName" type="text" placeholder="Username" name="uname"/>
					<input id = "inpPass" type="password" placeholder="Password" name="pass"/>
				</div>
				<button id = "btnSubmit">Submit</button>
				<p id="pSignUpLine"> Haven't registered yet? <span>Sign Up here</span></p>
				<!--<button id = "btnSignUp">Sign Up</button>-->
			</div>
		</div>
	</body>
</html>