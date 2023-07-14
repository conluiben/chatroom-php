"use strict";
$(document).ready(function(){
	$("button#btnSubmit").on("click", function(){
		$("button#btnSubmit").prop('disabled', true);
		$.post("process_inputs.php",{
			"action": "login",
			"uname": $("#inpName").val(),
			"pass": $("#inpPass").val()
		},	function(data, status){
				console.log("DATA: " + data);
				$("button#btnSubmit").prop('disabled', false);
				if(data==="success")
					window.location.replace("homepage.php");
				else{
					if(data==="wrong_info")
						alert("The username and password cannot be found! Try again...");
					else if(data==="already_online")
						alert("This account is already open in another session!");
				}
		});
	});
	$("input").on("keyup",function(event){
		if(event.keyCode===13){ //"Enter Key" keyCode is 13
			event.preventDefault();
			$("button#btnSubmit").trigger("click");
		}
	});
	$("p#pSignUpLine span").on("click",function(){
		window.location.replace("signup.php");
	});
});