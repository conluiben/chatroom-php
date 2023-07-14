<?php
//directory: cd htdocs/conluiben/chat/websocket
//from root: cd C:\xampp\htdocs\conluiben\chat\websocket
//based on YT video and cuelogic.com site
//also based on https://gist.github.com/sheolseol/e5942554b443e3d72bed
$host = '192.168.254.108'; //192.168.254.108
$port = 8000;
set_time_limit(0);

$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP); //Create TCP/IP sream socket
socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1); //reuseable port
socket_bind($socket, 0, $port); //bind socket to specified host
socket_listen($socket); //listen to port
$clients = array($socket); //create & add listning socket to the list

echo "Server running on host ".$host.":".$port."\n";
echo "-----------------------------\n\n";

while (true) {
	//manage multipal connections
	$changed = $clients;
	//returns the socket resources in $changed array
	socket_select($changed, $null, $null, 0, 10);
	
	//check for new socket
	if (in_array($socket, $changed)) {
		echo "Entered this part...\n";
		$socket_new = socket_accept($socket); //accept new socket
		$clients[] = $socket_new; //add socket to client array
		
		$header = socket_read($socket_new, 1024); //read data sent by the socket
		perform_handshaking($header, $socket_new, $host, $port); //perform websocket handshake
		
		socket_getpeername($socket_new, $ip); //get IP address of connected socket
		echo "Obtained IP: ".$ip."\n";
		$response = mask(json_encode(array('type'=>'system', 'message'=>$ip.' connected')));
		send_message($response); //notify all users about new connection
		
		//make room for new socket
		$found_socket = array_search($socket, $changed);
		unset($changed[$found_socket]);
	}
	//loop through all connected sockets
	foreach ($changed as $changed_socket) {	
		echo "Running loop through connected socket...\n";
		//check for any incomming data
		$bytesocket = @socket_recv($changed_socket, $buf, 1024, 0);
		while ($bytesocket >= 1){
		// while(socket_recv($changed_socket, $buf, 1024, 0) >= 1){
			$received_text = unmask($buf); //unmask data
			echo "Obtained Message: ".$received_text."\n";
			$tst_msg = json_decode($received_text, true); //json decode 
			$response_text = mask($received_text); //og: tst_msg (JSON encoded)
			
			send_message($response_text); //send data
			break 2; //exits the while and foreach loop
		}
		
		$buf = @socket_read($changed_socket, 1024, PHP_NORMAL_READ);
		if ($buf === false) { //check for disconnected clients
			// remove client for $clients array
			$found_socket = array_search($changed_socket, $clients);
			socket_getpeername($changed_socket, $ip);
			unset($clients[$found_socket]);
			
			//notify all users about disconnected connection
			$response = mask(json_encode(array('type'=>'system', 'message'=>$ip.' disconnected')));
			send_message($response);
		}
	}
}
// close the listening socket
socket_close($socket);

function send_message($msg){
	global $clients;
	foreach($clients as $changed_socket){
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

//handshake new client.
function perform_handshaking($receved_header,$client_conn, $host, $port){
	$headers = array();
	$lines = preg_split("/\r\n/", $receved_header);
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
	socket_write($client_conn,$upgrade,strlen($upgrade));
}

/* based on YT video and cuelogic.com site
GLOBAL $clients;
GLOBAL $client_list;

$sockMain = socket_create(AF_INET, SOCK_STREAM, 0) or die("Could not create socket!");
socket_set_option($sockMain, SOL_SOCKET, SO_REUSEADDR, 1);
$sockResult = socket_bind($sockMain, $host, $port) or die("Could not bind to socket!");
$sockResult = socket_listen($sockMain, 3) or die("Could not set up socket listener!"); //what's the 3?
echo "Server started on " . $host;

$sockList = array($sockMain);

do{
	$changed = $sockList;
	foreach($changed as $sock){
		if($sock==$sockMain){ //the server page's socket
			$write = NULL;
			$except = NULL;
			socket_select($changed,$write,$except,NULL); //master socket changed
			$sktClient = socket_accept($sockMain);
			if($sktClient<0){
				echo "socket_accept() failed";
				continue;
			}
			else{
				echo "Connecting socket...";
				fnConnectacceptedSocket($sock,$sktClient);
				$sockMain = NULL;
			}
		}
		else{ //for clients connected to server
			$client = getClientBySocket($sock); //first client handshakes with server
			echo "Client:\n";
			echo var_dump($client);
			// echo "Sock:\n";
			// echo var_dump((int)$sock);
			if($client){
				echo "GotitHere";
				echo var_dump((int)$sock);
				if($clients[(int)$sock]["handshake"]==false || isset($clients[(int)$sock]["handshake"])){
					echo "GotitHere1";
					$bytes = @socket_recv($client,$data,2048,MSG_DONTWAIT);
					if((int)$bytes==0){
						echo "Go back up";
						continue;
					}
					echo "Handshaking headers from client: " . $data;
					if(handshake($client, $data, $sock))
						$clients[(int)$socket]["handshake"] = true;
				}
				elseif($clients[(int)$sock]["handshake"]==true){
					echo "GotitHere2";
					$bytes = @socket_recv($client, $data, 2048, MSG_DONTWAIT);
					if($data!=""){
						$decodedData = unmask($data);
						socket_write($client, encode("You have entered: " . $decodedData));
						echo "Data from Client: " . $decodedData;
						socket_close($sock);
					}
				}
				else{
					echo "I'm the weird case. You shouldn't see this.\n";
					echo var_dump($clients[(int)$sock]["handshake"]);
				}
				echo "Hello?";
			}
			else
				echo "No detected client!\n";
		}
	}
} while(true);
socket_close($sockMain);

function unmask($ogData){ //decoding something
	$length = ord($ogData[1]) & 127; //IDGI
	if($length==126){
		$masks = substr($ogData, 4, 4);
		$data = substr($ogData, 8);
	}
	elseif($length==127){
		$masks = substr($ogData, 10, 4);
		$data = substr($ogData, 14);
	}
	else{
		$masks = substr($ogData, 2, 4);
		$data = substr($ogData, 6);
	}
	
	$text = "";
	$lenData = strlen($data);
	for($i=0; $i<$lenData; ++$i){ //try $i++ (post-increment)
		$text.=$data[$i] ^ $masks[$i%4];
	}
	
	return $text;
}

function encode($inpText){
	$b1 = 0x80 | (0x1 & 0x0f);
	$len = strlen($inpText);
	
	if($len<=125)
		$header = pack('CC', $b1, 127, $len);
	elseif($len>125 && $length<65536)
		$header = pack('CCS', $b1, 126, $len);
	elseif($len>=65536)
		$header = pack('CCN', $b1, 127, $len);
		
	return $header.$text;
}

function fnConnectacceptedSocket($socket, $client){
	GLOBAL $clients;
	GLOBAL $client_list;
	
	$clients[(int)$socket]["id"] = uniqid();
	$clients[(int)$socket]["handshake"] = false;
	$clients[(int)$socket]["socket"] = $socket;
	echo "Accepted client\n";
	
	$client_list[(int)$socket] = $client;
}

function getClientBySocket($socket){
	GLOBAL $client_list;
	return $client_list[(int)$socket];
}

function handshake($client, $headers, $socket){
	if(preg_match("/Sec-Websocket-Version: (.*)\r\n/",$headers,$match))
		$version = $match[1];
	else{
		echo "The client doesn't support WebSocket!\n";
		return false;
	}
	
	if($version==13){
		//extract header variables
		if(preg_match("/GET (.*) HTTP/",$headers,$match))
			$root = $match[1];
		if(preg_match("/Host: (.*)\r\n/",$headers,$match))
			$host = $match[1];
		if(preg_match("/Origin: (.*)\r\n/",$headers,$match))
			$origin = $match[1];
		if(preg_match("/Sec-WebSocket-Key: (.*)\r\n/",$headers,$match))
			$key = $match[1];
		
		$acceptKey = $key . "258EAFA5-E914-47DA-95CA-C5AB0DC85B11";
		$acceptKey = base64_encode(sha1($acceptKey, true));
		
		$upgrade = "HTTP/1.1 101 Switching Protocols\r\n" . 
					"Upgrade: websocket\r\n" . 
					"Connection: Upgrade\r\n" . 
					"Sec-WebSocket-Accept: " . $acceptKey . "\r\n\r\n";
		
		socket_write($client, $upgrade); //send back to client
		return true;
	}
	else{
		echo "We need Version 13!";
		return false;
	}
}
*/

?>
