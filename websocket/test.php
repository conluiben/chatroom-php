<html>
<body>
    <!--
	<form method="POST" action="/*php/ echo htmlspecialchars($_SERVER["PHP_SELF"]) /php ?>">
		<input type="text" name="inpMsg" placeholder="Your message here..."/>
		<input type="submit" name="btnSend"/>
	</form>
	-->
	<h1> Hello! </h1>
	<script>
		var jsSock = new WebSocket("ws://192.168.254.108:8000/conluiben/chat/websocket/server.php");
		jsSock.onopen = function(){
			document.body.innerHTML += "WebSocket opened!<br/>";
			console.log("WebSocket opened!");
		}
		jsSock.onerror = function(e){
			document.body.innerHTML += ("Error connecting: " + e.data + "!<br/>");
			console.log("Error connecting: " + e.data + "!");
		}
		jsSock.onmessage = function(e){
			document.body.innerHTML += ("New Message: " + e.data + "!<br/>");
			console.log("New Message: " + e.data + "!");
		}
		jsSock.onclose = function(e){
			document.body.innerHTML += ("Server closed!<br/>");
			console.log("Server Closed!");
		}
		
		window.onbeforeunload = function() { //close before unloading
			jsSock.close();
		};
	</script>
</body>
</html>