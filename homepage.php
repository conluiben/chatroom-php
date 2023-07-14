<?php
	session_start();
	if(!(isset($_SESSION["loggedIn"]) && $_SESSION["loggedIn"])){
		session_unset();
		session_destroy();
		header("Location: login.php");
	}
?>
<!DOCTYPE html>
<html>
	<title> ChatApp Homepage </title>
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1"/>
		<link rel="icon" href=""/>
		<!--<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css"/>-->
		<link rel="stylesheet" href="font-awesome-4.5.0/css/font-awesome.min.css"/> <!-- brownout option -->
		<!--<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>-->
		<script src="node_modules/jquery/dist/jquery.min.js"></script>
		<!--<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>-->
		<script src="node_modules/popper.js/dist/umd/popper.min.js"></script> <!-- remove umd? -->
		<meta charset="utf-8"/>
		<link rel = "stylesheet" href = "css/homepage.css"/>
		<script src="js/homepage.js"></script>
	</head>
	<body>
		<div class = "container">
			<div id="divUpperArea">
				<h1> Welcome, <span class="labelUname"> ####### </span></h1>
				<button id = "btnClearChat">Clear Chat</button>
				<button id = "btnLogout">Log Out</button>
			</div>
			<div id="divAllMsgsBox">
				<!-- all divchatrows go here -->
			</div>
			<div id="divInputArea">
				<label for="inpMsgField">Online as <span class="labelUname">username123</span></label>
				<div class="divInputField">
					<div class="divUnamePrepend">
						<span class="labelUname">username123</span>
					</div>
					<input type="text" id="inpMsgField" placeholder="Type your message here..."/>
					<div class="divUnameAppend">
						<button id = "btnSend" type="button">
							<span>Send</span>
							<i class="fa fa-paper-plane"> </i>
						</button>
					</div>
				</div>
			</div>
		</div>
	</body>
</html>