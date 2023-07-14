<?php
//from XAMPP shell: cd htdocs/conluiben/chat
//from root: cd C:\xampp\htdocs\conluiben\chat

$servername = "localhost"; //OG: localhost
$username = "root";
$password = "";
$dbName = "chat";

$sqlConn = new mysqli($servername, $username, $password, $dbName);
if($sqlConn->connect_error)
	die("Connection failed: " . $sqlConn->connect_error);

//date variables
date_default_timezone_set('Asia/Manila');

//register FINAL function to be run before totally quitting
register_shutdown_function("endScript"); //works if reaching end of script or triggering exit() or die()
sapi_windows_set_ctrl_handler('ctrl_handler'); //detect CTRL + C force stop

$host = '192.168.254.108'; //192.168.254.108
$port = 8000;
set_time_limit(0);

$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP); //Create TCP/IP stream socket
socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1); //reuseable port
socket_bind($socket, 0, $port); //bind socket to host
socket_listen($socket); //listen to port
$clients = array($socket); //create & add listening socket to the list

//reset online status of ALL users before starting
$sqlResetLogins = "UPDATE users SET online=0, ip_address=''";
if(!($sqlConn->query($sqlResetLogins))){
	die("Processing online resetting failed!");
}
while (true) {
	$changed = $clients;
	//returns the socket resources in $changed array
	socket_select($changed, $null, $null, 0, 10);
	
	//check for new socket
	if (in_array($socket, $changed)) {
		$socket_new = socket_accept($socket); //accept new socket
		$clients[] = $socket_new; //add socket to client array
		
		$header = socket_read($socket_new, 1024); //read data sent by the socket
		perform_handshaking($header, $socket_new, $host, $port); //perform websocket handshake
				
		//make room for new socket
		$found_socket = array_search($socket, $changed);
		unset($changed[$found_socket]);
		
		session_write_close();
	}
	
	//loop through all connected sockets
	foreach ($changed as $changed_socket) {	
		//check for any incoming data
		$bytesocket = @socket_recv($changed_socket, $buf, 2048, 0); //OG 3rd parameter: 1024
		$arrConcernedClients = array();
		$receivedText = "";
		$responseText = array();
		if ($bytesocket >= 1){
			//while(socket_recv($changed_socket, $buf, 1024, 0) >= 1){ //the original line was this, reruns socket_recv to read more data; consider going back to old solution if 1024 bytes is not enough
			$receivedText = unmask($buf); //unmask data
			$receivedObj = JSON_decode($receivedText, true);
			if(isset($receivedObj["action"])){
				if($receivedObj["action"]==="firstPageLoad"){
					$responseText["sessionID"] = $receivedObj["sessionID"];
					session_id($responseText["sessionID"]);
					session_start();
					
					//login validation case begins here
					$outputData = $sqlConn->query("SELECT online FROM users WHERE username = BINARY '" . $_SESSION["uname"] . "'");
					$outputRow = $outputData->fetch_assoc();
					$userStatus = (int)($outputRow["online"]); //status prior to login registration
					if($userStatus===1){ //already logged in! tell client to redirect to login
						array_push($arrConcernedClients, $changed_socket);
						$responseText["success"] = false;
						$responseText["error"] = "already_logged_in";
						
						session_unset();
						session_destroy();
					}
					else{
						$arrConcernedClients = $clients;
						
						//load the conversation history
						$sqlSelectData = "SELECT * FROM globalchat WHERE sent_by IS NOT NULL";
						$sqlSelectData = "SELECT globalchat.*,users.icon FROM globalchat JOIN users WHERE globalchat.sent_by=users.username";
						$outputData = $sqlConn->query($sqlSelectData);
						$allRows = array();
						while($row = $outputData->fetch_assoc()) //stops when $row is null
							array_push($allRows,$row);
						$responseText["messages"] = $allRows; //second parameter of JSON_decode (client) must be TRUE
						
						//update online status of user
						$responseText["action"] = "pageLoadReady";
						$responseText["activeUser"] = $_SESSION["uname"]; 
						socket_getpeername($socket_new, $newUserIP);
						$sqlNewLogin = "UPDATE users SET online=1, ip_address='" . $newUserIP . "' WHERE username = BINARY '" . $responseText["activeUser"] . "'";
						if($sqlConn->query($sqlNewLogin)){
							$responseText["success"] = true;
						}
						else{
							$responseText["success"] = false;
							$responseText["error"] = $sqlConn->error;
						}
						
						//get the list of all online users
						$sqlOnlineUsers = "SELECT username FROM users WHERE online>0";
						$outputData = $sqlConn->query($sqlOnlineUsers);
						$allOnline = array();
						while($row = $outputData->fetch_assoc()) //stops when $row is null
							array_push($allOnline,$row["username"]);
						$responseText["onlineUsers"] = $allOnline; //numeric array of online users
						
						//////////////////////////////////////////////////
						/*
						//send an EXTRA message to all clients excluding the current one (special case only); tells others of new 
						$arrConcernedClientsExtra = $clients;
						$found_socket = array_search($changed_socket, $arrConcernedClientsExtra);
						unset($arrConcernedClientsExtra[$found_socket]);
						
						//craft a new message
						$responseTextExtra = array();
						*/
						//////////////////////////////////////////////////
						session_write_close();
					}
				}
				else if($receivedObj["action"]==="sentMessage"){
					session_id($receivedObj["sessionID"]);
					session_start();
					$arrConcernedClients = $clients; //all clients are concerned (for now, but adjust for private conversations)
					// echo "Let's see: ".var_dump($receivedObj);
					$inpMsg = $receivedObj["message"]; //not to be used for sending, though! (shows the slashes)
					$inpSender = $_SESSION["uname"];
					$sqlNewRow = "INSERT INTO globalchat (sent_by,message) VALUES ('" . $inpSender . "','" . slashedMsg($inpMsg) . "')";
					if($sqlConn->query($sqlNewRow)){
						$responseText["success"] = true;
					}
					else{
						$responseText["success"] = false;
						$responseText["error"] = $sqlConn->error;
					}
					//should work all the time, I guess? MAYBE NO
					/*
					$sqlSelectData = "SELECT * FROM globalchat WHERE sent_by IS NOT NULL ORDER BY time_sent DESC LIMIT 1";
					$outputData = $sqlConn->query($sqlSelectData);
					$arrMsg = $outputData->fetch_assoc(); //only 1 message anyway
					*/
					//obtain icon of sender
					$sqlIconData = "SELECT icon FROM users WHERE username = BINARY '" . $_SESSION["uname"] . "'"; //should naturally only get 1, right?
					$outputData = $sqlConn->query($sqlIconData);
					$iconUser = $outputData->fetch_assoc(); //only 1 message anyway
					
					$responseText["action"] = "sentMsgAdded";
					$responseText["message"] = array("sent_by"=>$_SESSION["uname"],"message"=>$inpMsg,"time_sent"=>date('Y-m-d H:i:s'),"icon"=>$iconUser["icon"]);
					$responseText["sessionID"] = $receivedObj["sessionID"];
					// echo "responseText at end: ".var_dump($responseText);
					session_write_close();
				}
				else if($receivedObj["action"]==="clearChat"){
					//no need for session variables
					$arrConcernedClients = $clients;
					$sqlClearChat = "TRUNCATE globalchat";
					if($sqlConn->query($sqlClearChat)){
						$responseText["success"] = true;
					}
					else{
						$responseText["success"] = true;
						$responseText["error"] = $sqlConn->error;
					}
					$responseText["action"] = "clearChatDone";
				}
				$responseText = mask(JSON_encode($responseText));
				if(send_message($responseText, $arrConcernedClients))
					break; //exits the foreach loop, used to be outside if-statement
			}
			else{
				//just do nothing! ignore extra message
				// echo "Error with this: $receivedObj";
				// die("I died");
			}
		}
		//if the code reaches here, only one client gets the screen
		$buf = @socket_read($changed_socket, 1024, PHP_NORMAL_READ);
		if ($buf===false) { //check for disconnected clients
			socket_getpeername($changed_socket, $deadUserIP);
			// remove client from $clients array
			$found_socket = array_search($changed_socket, $clients);
			unset($clients[$found_socket]);
			
			//constructing the message
			$responseText = array();
			$responseText["action"] = "deadUserDetected";
			
			//obtain the details of the disconnected IP
			$sqlDeadUser = "SELECT username FROM users WHERE ip_address='" . $deadUserIP . "'";
			$outputData = $sqlConn->query($sqlDeadUser);
			$deadUserRow = ($outputData->fetch_assoc()); //only has 1 attribute: username
			
			$sqlNewDisconnect = "UPDATE users SET online=0, ip_address='' WHERE ip_address='" . $deadUserIP . "'";
			
			if(isset($deadUserRow["username"])){ //just to ensure that a "null got disconnected" notification doesn't show up
				$deadUserName = $deadUserRow["username"];
				if($sqlConn->query($sqlNewDisconnect)){
					$responseText["success"] = true;
					$responseText["deadUser"] = $deadUserName; //only 1 anyway (supposedly)
				}
				else{
					$responseText["success"] = false;
					$responseText["error"] = $sqlConn->error;
				}
				
				//notify all users about disconnection
				$responseText = mask(JSON_encode($responseText));
				send_message($responseText, $clients);
			}
		}
	}
}
// close the listening socket
// socket_close($socket); //moved to terminating function

function send_message($msg, $arrClients){
	foreach($arrClients as $changed_socket){
		@socket_write($changed_socket,$msg,strlen($msg));
	}
	return true;
}

//Unmask incoming framed message
function unmask($text){
	$length = ord($text[1]) & 127;
	if($length == 126) {
		$masks = substr($text, 4, 4);
		$data = substr($text, 8);
	}
	elseif($length == 127) {
		$masks = substr($text, 10, 4);
		$data = substr($text, 14);
	}
	else {
		$masks = substr($text, 2, 4);
		$data = substr($text, 6);
	}
	$text = "";
	for ($i = 0; $i < strlen($data); ++$i) {
		$text .= $data[$i] ^ $masks[$i%4];
	}
	return $text;
}

//Encode message for transfer to client.
function mask($text){
	$b1 = 0x80 | (0x1 & 0x0f);
	$length = strlen($text);
	
	if($length <= 125)
		$header = pack('CC', $b1, $length);
	elseif($length > 125 && $length < 65536)
		$header = pack('CCn', $b1, 126, $length);
	elseif($length >= 65536)
		$header = pack('CCNN', $b1, 127, $length);
	return $header.$text;
}

function slashedMsg($inpMsg){ //only treats quotation marks as text; needs improvement!
	$inpMsg = addslashes($inpMsg);
	return $inpMsg;
}

//handshake new client.
function perform_handshaking($inpHeader,$inpClient, $host, $port){
	$headers = array();
	$lines = preg_split("/\r\n/", $inpHeader);
	foreach($lines as $line){
		$line = chop($line);
		if(preg_match('/\A(\S+): (.*)\z/', $line, $matches))
			$headers[$matches[1]] = $matches[2];
	}

	$secKey = $headers['Sec-WebSocket-Key'];
	$secAccept = base64_encode(pack('H*', sha1($secKey . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
	//hand shaking header
	$upgrade  = "HTTP/1.1 101 Web Socket Protocol Handshake\r\n" .
	"Upgrade: websocket\r\n" .
	"Connection: Upgrade\r\n" .
	"Sec-WebSocket-Accept:$secAccept\r\n\r\n";
	socket_write($inpClient,$upgrade,strlen($upgrade));
}

//from PHP manual, only for PHP 7.4.0++
function ctrl_handler(int $event){ //to ensure that endScript runs
    switch ($event) {
        case PHP_WINDOWS_EVENT_CTRL_C:
            echo "You have pressed CTRL+C\n";
			die();
        case PHP_WINDOWS_EVENT_CTRL_BREAK:
            echo "You have pressed CTRL+BREAK\n";
			die();
    }
}

function endScript(){
	//deactivate server
	echo "\nDoing good!";
	socket_close($GLOBALS["socket"]);
	//reset online status
	if(!($GLOBALS["sqlConn"]->query($GLOBALS["sqlResetLogins"])))
		echo "\nFinal resetting failed: " . $GLOBALS["sqlConn"]->error;
	
	else
		echo "\nReset the online status!";
}

?>