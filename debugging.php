<?php
	$servername = "localhost"; //OG: localhost
	$username = "root";
	$password = "";
	$dbName = "chat";
	$sqlConn = new mysqli($servername, $username, $password, $dbName);
	
	session_write_close();
	ignore_user_abort(true);
	set_time_limit(0);
	/*
	while(true){
		$lastActive = date("Y-m-d H:i:s");
		$sqlLatestDate = $sqlConn->query("SELECT time_sent FROM globalchat ORDER BY time_sent DESC LIMIT 1");
		$latestMsgDate = $sqlLatestDate->fetch_assoc()["time_sent"];
		while($lastActive>$latestMsgDate){
			sleep(1);
			$sqlLatestDate = $sqlConn->query("SELECT time_sent FROM globalchat ORDER BY time_sent DESC LIMIT 1");
			$latestMsgDate = $sqlLatestDate->fetch_assoc()["time_sent"];
			echo "$lastActive lastActive<br> $latestMsgDate latestMsgDate<br>";
		}
		echo ("I refreshed!");
		sleep(3);
	*/
	//$lastActive = date("Y-m-d H:i:s");
	/*
	$lastActive = new DateTime("now", new DateTimeZone("Asia/Manila"));
	// $lastActive = $lastActive->format("Y-m-d H:i:s"); //converts it to string!
	
	$sqlLatestDate = $sqlConn->query("SELECT time_sent FROM globalchat ORDER BY time_sent DESC LIMIT 1");
	$latestMsgDate = $sqlLatestDate->fetch_assoc()["time_sent"];
	
	while($lastActive>$latestMsgDate){
		sleep(1);
		$sqlLatestDate = $sqlConn->query("SELECT time_sent FROM globalchat ORDER BY time_sent DESC LIMIT 1");
		$latestMsgDate = new DateTime($sqlLatestDate->fetch_assoc()["time_sent"],new DateTimeZone("Asia/Manila"));
	}
	echo "new";
	*/
	echo "I'm new<br/>";
	echo addslashes("I'm a good one");
	//sleep(3);
?>