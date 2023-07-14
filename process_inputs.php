<?php
	session_start();
	
	$servername = "localhost"; //OG: localhost
	$username = "root";
	$password = "";
	$dbName = "chat";
	
	$sqlConn = new mysqli($servername, $username, $password, $dbName);
	
	if ($_SERVER["REQUEST_METHOD"] == "POST") {
		if($_POST["action"]=="login"){
			// collect value of input field
			$inpName = safeInput($_POST['uname']);
			$inpPass = safeInput($_POST['pass']);
			
			$sqlSelectData = "SELECT * FROM users WHERE username = BINARY '" . $inpName. "' AND password = BINARY '" . $inpPass . "';";
			$outputData = $sqlConn->query($sqlSelectData);
			
			if($outputData->num_rows==1){
				$userRow = $outputData->fetch_assoc();
				// die(var_dump($userRow["online"]));
				if((int)$userRow["online"]===0){
					$_SESSION["uname"] = $inpName;
					// $_SESSION["pass"] = $pass; //BAD PRACTICE!
					$_SESSION["loggedIn"] = true;
					die("success");
					//update the MySQL database in chat_server.php
				}
				else //assumes ===1
					die("already_online"); //already logged in somewhere else
			}
			else
				die("wrong_info");
		}
		elseif($_POST["action"]=="logout"){
			session_unset();
			session_destroy();
			die("success");
		}
		elseif($_POST["action"]=="validateUname"){ //signup page
			$sqlCountQuery = "SELECT COUNT(username) AS unameTaken FROM users WHERE username = BINARY '" . $_POST["uname"] . "'";
			$outputData = $sqlConn->query($sqlCountQuery);
			$countRow = $outputData->fetch_assoc();
			die(($countRow["unameTaken"]==0) ? "valid" : "invalid");
		}
		elseif($_POST["action"]=="newSignUp"){
			$sqlNewRow = "INSERT INTO users (username,password,icon,online) VALUES ('" . $_POST["uname"] . "','" . $_POST["pass"] . "','" . $_POST["icon"] . "',0)";
			if($sqlConn->query($sqlNewRow))
				die("success");
			else
				die("failure: " . $sqlConn->error);
		}
	}
	$sqlConn->close();
	session_write_close(); //just added as of 04-29-2020
	
	function safeInput($data) {
		$data = trim($data);
		$data = stripslashes($data);
		$data = htmlspecialchars($data);
		return $data;
	}
?>