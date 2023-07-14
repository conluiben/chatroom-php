"use strict";
var activeUname = null;
var jsSock; //websocket variables;
//jsSockMsg to be declared per function for local scope only; attributes: sessionID, action, etc.

//global chat variables
var emptyChat = false;

//chat load interval variables
var msgCurrDate = null; //most updated message, store only the DATE

function setupWebSocket(){
	jsSock = new WebSocket("ws://192.168.254.108:8000/conluiben/chat/chat_server.php");
	
	jsSock.onopen = function(){ //also assumes that the username information is already bound in the session
		// document.body.innerHTML += "WebSocket opened!<br/>";
		console.log("WebSocket opened!");
		setupJQuery(); //setup HTML behavior
		
		var jsSockMsg = new sockMsg();
		jsSockMsg["action"] = "firstPageLoad";
		jsSock.send(JSON.stringify(jsSockMsg));
	}
	jsSock.onerror = function(e){
		// document.body.innerHTML += ("Error connecting: " + e.data + "!<br/>");
		console.log("Error connecting: " + e.data + "!");
		jsSock.close();
	}
	jsSock.onmessage = function(e){ //get message with e.data
		var responseObj = JSON.parse(e.data);
		console.log("JSON response: ");
		console.log(responseObj);
		// copied from AJAX request of loadChat()
		if(responseObj["success"]===false){ //process the error
			if(responseObj["error"]==="already_logged_in"){ //coming from firstPageLoad action of WebSocket (user x2 log in)
				window.location.replace("login.php");
			}
			$("div#divAllMsgsBox").text("Error: " + responseObj["error"]);
		}
		else{
			if(responseObj["action"]==="pageLoadReady"){ //also loads the chatbox and updates the list of online users
				//NOTE that users with a successful reconnection of WebSocket will jump back here
				//05-08-2020: includes icons with INNER JOIN method
				if(responseObj["sessionID"]===getCookie("PHPSESSID")){
					$("div#divAllMsgsBox").empty();
					// obtain username of active user
					activeUname = responseObj["activeUser"]
					$("span.labelUname").text(activeUname);
					// console.log(responseObj["messages"]); //.length
					if(responseObj["messages"].length>0){
						emptyChat = false;
						for(var msg of responseObj["messages"]){
							newMsgRow(msg);
						}
					}
					else{
						emptyChat = true;
						setupEmptyChatBox();
					}
					$("div#divAllMsgsBox").scrollTop($("div#divAllMsgsBox").prop('scrollHeight'));
					
					//ALSO show users who are online
					console.log("Online Users: ");
					console.log(responseObj["onlineUsers"]);
				}
				else{ //other clients; notify them of new login
					console.log(responseObj["activeUser"] + " has just logged in!");
					console.log("Online Users (other clients call): ");
					console.log(responseObj["onlineUsers"]);
				}
			}
			else if(responseObj["action"]==="sentMsgAdded"){
				if(emptyChat){
					emptyChat = false;
					$("div#divAllMsgsBox").empty();
				}
				newMsgRow(responseObj["message"]);
				$("div#divAllMsgsBox").scrollTop($("div#divAllMsgsBox").prop('scrollHeight'));
				if(responseObj["sessionID"]===getCookie("PHPSESSID")){
					$("input#inpMsgField").val("");
					$("button#btnSend").prop("disabled",true);
				}
			}
			else if(responseObj["action"]==="clearChatDone"){
				emptyChat = true;
				setupEmptyChatBox();
			}
			else if(responseObj["action"]==="deadUserDetected"){
				console.log(responseObj["deadUser"] + " got disconnected!");
				//online users left? (needs another mysql request)
			}
		}
		
	}
	jsSock.onclose = function(){
		// document.body.innerHTML += ("Server closed!<br/>");
		console.log('Socket is closed. Reconnect will be attempted soon');
		// setTimeout(function(){ setupWebSocket(); }, 1000);
	}
}

window.onload = setupWebSocket;

window.onbeforeunload = function() { //close before unloading
	jsSock.onclose = function(){}; // disable onclose handler first
	jsSock.close();
};

function sockMsg(){ //object constructor for messages
	this["sessionID"] = getCookie("PHPSESSID");
}

// get cookie function from W3Schools
function getCookie(cname) { //cname is a string of the cookie name
	var name = cname + "=";
	var decodedCookie = decodeURIComponent(document.cookie);
	var ca = decodedCookie.split(';');
	for(var i = 0; i <ca.length; i++) {
		var c = ca[i];
		while (c.charAt(0) == ' ') {
			c = c.substring(1);
		}
		if (c.indexOf(name) == 0) {
			return c.substring(name.length, c.length);
		}
	}
	return "";
}

function formatTime(inpDate, isInParentheses) { //inpDate must be a Date object
	var hours = inpDate.getHours();
	var minutes = inpDate.getMinutes();
	var ampm = hours >= 12 ? 'PM' : 'AM';
	hours = hours % 12;
	hours = hours ? hours : 12; // the hour '0' should be '12'
	minutes = minutes < 10 ? '0'+minutes : minutes;
	var strTime = hours + ':' + minutes + ' ' + ampm;
	if(isInParentheses)
		return '('+strTime+')';
	else
		return strTime;
}

function setupJQuery(){ //event listeners (onload) go here
	console.log("jQuery calls ready!");
	console.log($("button#btnSend").prop("disabled"));
	$("button#btnSend").prop("disabled","true");
	console.log($("button#btnSend").prop("disabled"));
	$("button#btnSend").on("click",function(){
		var inpMsg = $("input#inpMsgField").val();
		if(inpMsg.length>0){ //if legal message
			var jsSockMsg = new sockMsg();
			jsSockMsg["action"] = "sentMessage";
			jsSockMsg["message"] = inpMsg;
			jsSock.send(JSON.stringify(jsSockMsg));
			$("button#btnSend").prop("disabled","true"); //to be enabled again later
		}
	});
	$("button#btnClearChat").on("click",function(){
		var jsSockMsg = new sockMsg();
		jsSockMsg["action"] = "clearChat";
		jsSock.send(JSON.stringify(jsSockMsg));
	});
	$("button#btnLogout").on("click", function(){
		console.log("clicked the logout botton!");
		$("button#btnLogout").prop('disabled', true);
		$.post("process_inputs.php",{"action": "logout"},	function(data, status){
				$("button#btnLogout").prop('disabled', false);
				if(data==="success"){ //successfully disconnect
					window.location.replace("login.php");
				}
				else
					alert("Wait, I lagged. Try again?");
		});
	});
	$("input#inpMsgField").on("input",function(){
		if($("input#inpMsgField").val().length>0)
			$("button#btnSend").removeAttr("disabled");
		else
			$("button#btnSend").prop("disabled","true");
	});
	$("input#inpMsgField").on("keypress",function(event){
		if(event.keyCode===13){ //"Enter Key" keyCode is 13
			event.preventDefault();
			$("button#btnSend").trigger("click");
		}
	});
}

function newMsgRow(arrMsg){
	//arrMsg is an associative array for a single message (or simply an object in Javascript)
	//05-08-2020: arrMsg includes "icon" attribute for icon URL
	var msgDate = new Date(arrMsg["time_sent"].replace(/-/g, '/'));
	// var msgDate = new Date(msg["time_sent"].replace(' ','T'));
	var msgFromActive = false;
	if(arrMsg["sent_by"]===activeUname)
		msgFromActive = true; //implies that message was sent from active user
	
	var msgDateNoTime = new Date(msgDate.getTime());
	msgDateNoTime.setHours(0,0,0,0);
	
	//check if the date line needs to be added first
	var pDateText = "";
	if(msgCurrDate==null || msgCurrDate.getTime()<msgDateNoTime.getTime()){
		//print today, tomorrow, or actual date
		var dateNow = new Date();
		dateNow.setHours(0,0,0,0);
		
		var daysDiff = dateNow.getTime() - msgDateNoTime.getTime(); //86400000 is 1 day
		
		if(daysDiff===0)
			pDateText = "Today"; //automatically transforms to uppercase through CSS
		else if(daysDiff===86400000) //86400000ms in a day (may use this for multiples)
			pDateText = "Yesterday";
		else{
			var months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
			pDateText = (months[msgDate.getMonth()]) + " " + msgDate.getDate() + ", " + msgDate.getFullYear();
		}
		$("div#divAllMsgsBox").append(
			$("<div></div>").addClass("divDateLine").append(
				$("<p></p>").addClass("pDate").text(pDateText)));
		
		msgCurrDate = new Date(msgDate.getTime());
		msgCurrDate.setHours(0,0,0,0); //remove the time component!
	}
	
	var dRow = $("<div></div>");
	if(msgFromActive) //setting the important classes
		dRow.addClass("divSentRow");
	else
		dRow.addClass("divRcvdRow");
	// icon
	var iconUrl = "images/icons/"+arrMsg["icon"];
	var iIcon = $("<img/>").attr("src",iconUrl).addClass("imgDp");
	dRow.append(iIcon);
	
	var dContent = $("<div></div>").addClass("divContent");
	var dMsgDetails = $("<div></div>").addClass("divMsgDetails");
	var dMsgBox = $("<div></div>").addClass("divMsgBox");
	// .divMsgDetails
	dMsgDetails.append($("<small></small>").addClass("msgName").text(arrMsg["sent_by"]));
	dMsgDetails.append($("<small></small>").addClass("msgTime").text(formatTime(msgDate,true)));
	// .divMsgBox
	dMsgBox.append($("<div></div>").addClass("msgText").text(arrMsg["message"]));
	dMsgBox.append($("<small></small>").addClass("timeText").text(formatTime(msgDate,false)));
	
	dContent.append(dMsgDetails,dMsgBox);
	
	dRow.append(dContent);
	$("div#divAllMsgsBox").append(dRow);
}

function setupEmptyChatBox(){
	$("div#divAllMsgsBox").empty();
	$("div#divAllMsgsBox").append("<div id='divChatEmpty'><p> Start a new chat below! </p></div>");
}

//eof