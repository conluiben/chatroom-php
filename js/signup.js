"use strict";
var nameGood = false;
var passGood = false;
var iconGood = false;

function validateAllInput(){
	// nameGood = ($("input#inpUname").val().length>2);
	// passGood = ($("input#inpPass").val()===$("input#inpRePass").val() && $("input#inpPass").val().length>0);
	// iconGood = ($("div.inpSelected").length===1);
	
	if(nameGood && passGood && iconGood)
		$("button#btnSignUp").removeAttr("disabled");
	else
		$("button#btnSignUp").prop("disabled","true");
}

$(document).ready(function(){
	//first things first
	$("div.row p.pInpInfo").css("visibility","hidden");
	$("button#btnSignUp").prop("disabled","true");
	
	$("div#divInpUname input").on("input",function(){ //checking username
		$("div#divInpUname  p.pInpInfo").css("visibility","visible");
		if($("input#inpUname").val().length>2){
			$.post("process_inputs.php",{
				"action": "validateUname",
				"uname": $("input#inpUname").val()
			}, function(data, status){
				console.log(data);
				if(data==="valid"){
					$("div#divInpUname i").removeClass("fa-exclamation-circle").addClass("fa-check");
					$("div#divInpUname p.pInpInfo").css("color","green");
					$("div#divInpUname p.pInpInfo span").text("Valid username!");
					$("div#divInpUname input").removeClass("inputBad").addClass("inputGood");
					nameGood = true;
				}
				else{
					$("div#divInpUname i").removeClass("fa-check").addClass("fa-exclamation-circle");
					$("div#divInpUname p.pInpInfo").css("color","red");
					$("div#divInpUname p.pInpInfo span").text("This username is already taken!");
					$("div#divInpUname input").removeClass("inputGood").addClass("inputBad");
					nameGood = false;
				}
			});
		}
		else{
			$("div#divInpUname i").removeClass("fa-check").addClass("fa-exclamation-circle");
			$("div#divInpUname p.pInpInfo").css("color","red");
			$("div#divInpUname p.pInpInfo span").text("Please use at least three characters!");
			$("div#divInpUname input").removeClass("inputGood").addClass("inputBad");
			nameGood = false;
		}
		validateAllInput();
	});
	
	$("div.pass input").on("input",function(){ //checking password
		$("div#divInpRePass  p.pInpInfo").css("visibility","visible");
		if($("input#inpPass").val().length>0){
			if($("input#inpPass").val()===$("input#inpRePass").val()){
				$("div#divInpRePass i").removeClass("fa-exclamation-circle").addClass("fa-check");
				$("div#divInpRePass p.pInpInfo").css("color","green");
				$("div#divInpRePass p.pInpInfo span").text("Passwords match! Do not forget this...");
				$("div.pass input").removeClass("inputBad").addClass("inputGood");
				passGood = true;
			}
			else{
				$("div#divInpRePass i").removeClass("fa-check").addClass("fa-exclamation-circle");
				$("div#divInpRePass p.pInpInfo").css("color","red");
				$("div#divInpRePass p.pInpInfo span").text("Passwords do not match!");
				$("div.pass input").removeClass("inputGood").addClass("inputBad");
				passGood = false;
			}
		}
		else{
			$("div#divInpRePass i").removeClass("fa-check").addClass("fa-exclamation-circle");
			$("div#divInpRePass p.pInpInfo").css("color","red");
			$("div#divInpRePass p.pInpInfo span").text("Please enter a password!");
			$("div.pass input").removeClass("inputGood").addClass("inputBad");
			passGood = false;
		}
		validateAllInput();
	});
	
	//event listeners
	$("div.divIcon").on("click",function(e){
		$("div.divIcon").removeClass("inpSelected");
		$(this).addClass("inpSelected");
		$("div#divInpIcon p.pInpInfo").css("visibility","hidden");
		iconGood = true;
		validateAllInput();
	});
	$("button#btnSignUp").on("click",function(){ //does not trigger when disabled
		$.post("process_inputs.php",{
			"action": "newSignUp",
			"uname": $("input#inpUname").val(),
			"pass": $("input#inpPass").val(),
			"icon": $("div.inpSelected img").attr('src').split("/").pop()
		},function(data,status){
			if(data==="success"){
				alert("Sign up successful! Now try logging in.");
				window.location.replace("login.php");
			}
			else{
				console.log(data);
			}
		});
	});
	$("p#pLoginLine span").on("click",function(){
		window.location.replace("login.php");
	});
});