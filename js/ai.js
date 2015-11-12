APP_ROOT  = "aiconcepts.in"; 
APP_ROOT  = "localhost/language_ai"; 

$( document ).ready(function() {
    hideCorrection(); 
    $("#tabs").tabs(); 
    
    $(document).ajaxStart(function(){
    $("#wait").css("display", "block");
	});

	$(document).ajaxComplete(function(){
    $("#wait").css("display", "none");
	});


	$('#tabs ul li a').click(function() {
		var tab = $(this).attr("href").substr($(this).attr("href").length - 1);
		var tab = parseInt(tab);
		
		switch (tab) {
	    case 2:
	        showWordHistory();
	        break;
	    case 3:
	       showQuestionHistory();
	        break;
	    case 4:
	        showResponseHistory();
	        break;
		default:
			return false;
	}
	});


	 $( "#fromdate" ).datepicker({
	    dateFormat : 'yy-mm-dd'
	});
	
	 $( "#todate" ).datepicker({
	    dateFormat : 'yy-mm-dd'
	});
	 
	$( "#rfromdate" ).datepicker({
	    dateFormat : 'yy-mm-dd'
	});
	
	 $( "#rtodate" ).datepicker({
	    dateFormat : 'yy-mm-dd'
	});
  
});



function showCorrection(){
	$("#correct").show();
    $("#correction_text").show();	
}

function hideCorrection(){
	$("#correct").hide();
    $("#correction_text").hide();	
}

function updateQuestions(){
	
	var keyword =  $("#keyword").val();
	var fromdate = $("#fromdate").val();
	var todate =   $("#todate").val();
	
	if(todate.length > 8 && fromdate.length  < 8){
		alert("Please select the from date");
		return false;
	}
	
	var from = new Date(fromdate);
	var to = new Date(todate);
	
	if(from > to){
		alert("From date can't be greater than to date");
		return false;
	}
	
	showQuestionHistory(keyword,fromdate,todate);
	
}

function updateResponses(){
	
	var keyword =  "";
	var fromdate = $("#rfromdate").val();
	var todate =   $("#rtodate").val();
	
	if(todate.length > 8 && fromdate.length  < 8){
		alert("Please select the from date");
		return false;
	}
	
	var from = new Date(fromdate);
	var to = new Date(todate);
	
	if(from > to){
		alert("From date can't be greater than to date");
		return false;
	}
	
	showResponseHistory(keyword,fromdate,todate);
	
}

function updateResponsesKey(){
	
	var keyword =  $("#rkeyword").val();
	var fromdate = "";
	var todate =   "";	
	showResponseHistory(keyword,fromdate,todate);
	
}

function getResponse(){
	
		var query = $("#query").val();
        var captcha = $("#captcha").val();

		toggleButtonText();
		$.ajax({
   			url: 'http://' + APP_ROOT + '/index.php/welcome/response',
   			data: {
      			query: query,
				correct:false,
                captcha:captcha
   			},
   			error: function() {
      			$('#info').html('<p>An error has occurred</p>');
   			},
   			dataType: 'text',
		   success: function(data) {
		   	
		   	var dumbResponse = "Teach me to respond to \"" + query + "\"";
		   	
		   	if(data !="No Text Entered" && data != dumbResponse && data !="Ok" && data !="Wrong Captcha Entered"){
		   		showCorrection();
		   	}else{
		   		hideCorrection();		   		
		   	}
		   	
		   	if(data == dumbResponse){
		   		$("#talk").html("Respond This Way");
		   	}
		   	
		   	  $("#responsediv").html(data);
		   	  
		   	  d = new Date();
                          $("#capcha").attr("src", "index.php/Welcome/captcha/"+d.getTime());
 
		   },
                   type: 'POST',
                   async: false,
            });	
            	
		 $("#correction_text").val("");
}

function toggleButtonText(){
	
	var buttonText = $("#talk").html();
	if(buttonText == "Respond This Way")
	{
		$("#talk").html("Talk");
	}
	
}
function correct(){
	
		var query = $("#correction_text").val();
        	var captcha = $("#captcha").val();
	
		$.ajax({
   			url: 'http://' + APP_ROOT + '/index.php/welcome/response',
   			data: {
      			query: query,
				correct:true,
                captcha:captcha
   			},
   			error: function() {
      			//$('#info').html('<p>An error has occurred</p>');
   			},
   			dataType: 'text',
		   success: function(data) {
		   	
		   	if(data == "Ok"){
		   		hideCorrection();
		   	}
		   	  $("#responsediv").html(data);		   	  
		   	  d = new Date();
              		  $("#capcha").attr("src", "index.php/Welcome/captcha/"+d.getTime());
                
		   },
                   type: 'POST',
                   async: false,
            });		

}

function showQuestionHistory(keyword,fromdate,todate){
	
	$.ajax({
   			url: 'http://' + APP_ROOT + '/index.php/welcome/getQuestionHistory',
   			data: {
				keyword:keyword,
				fromdate:fromdate,
				todate:todate	
   			},
   			error: function() {
      			//$('#info').html('<p>An error has occurred</p>');
      			alert("error");
   			},
   			dataType: 'text',
		   success: function(data) {
		   		var res = '';
				var obj = jQuery.parseJSON(data);
				
				if(obj){
					obj.forEach(function(entry) {
    				res += "<tr><td>"	+ entry.question + "</td></tr>";
					});
                	$("#QuetionsTable tbody").html(res);
                	$("#QuetionsTable").DataTable();
				}else{
					alert("No data Found");
				}
		   },
                   type: 'POST',
                   async: false,
            });		

}
function showResponseHistory(keyword,fromdate,todate){
	
	$.ajax({
   			url: 'http://' + APP_ROOT + '/index.php/welcome/getResponseHistory',
   			data: {
				keyword:keyword,
				fromdate:fromdate,
				todate:todate	
   			},
   			error: function() {
      			//$('#info').html('<p>An error has occurred</p>');
      			alert("error");
   			},
   			dataType: 'text',
		   success: function(data) {
		   		var res = '';
				var obj = jQuery.parseJSON(data);
				
				if(obj){
					obj.forEach(function(entry) {
	    				res += "<tr><td>"	+ entry.query + "</td>";
	    				res += "<td>"	+ entry.response + "</td></tr>";
					});
	                $("#ResponseTable tbody").html(res);
	                $("#ResponseTable").DataTable();  
	              }else{
					alert("No data Found");
				}
		   },
                   type: 'POST',
                   async: false,
            });		

}
function showWordHistory(){
	
	$.ajax({
   			url: 'http://' + APP_ROOT + '/index.php/welcome/getWordHistory',
   			data: {

   			},
   			error: function() {
      			//$('#info').html('<p>An error has occurred</p>');
      			alert("error");
   			},
   			dataType: 'text',
		   success: function(data) {
		   		var res = '';
				var obj = jQuery.parseJSON(data);
				
				if(obj){					
					obj.forEach(function(entry) {
	    				res += "<tr><td>"	+ entry.word + "</td>";
	    				res += "<td>"	+ entry.ts + "</td></tr>";
					});
	                $("#WordTable tbody").html(res);
	                $("#WordTable").DataTable();  
				}else{
					alert("No data Found");
				}
				
		   },
                   type: 'POST',
                   async: false,
            });		

}


