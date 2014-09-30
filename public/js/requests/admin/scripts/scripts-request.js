/*
 * Application name: OpenRate-it!
 * A general-purpose polling platform
 * Copyright (C) 2014  Alain Bindele (alain.bindele@gmail.com)
 * This file is part of OpenRate-it!
 * OpenRate-it! is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * OpenRate-it! is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

//load and run a specific function on db


jQuery(document).on('ready', function() {
	jQuery('form#loadScriptForm').bind('submit', function(event){
		event.preventDefault();
		var response;
		var form = this;
		var jsonRequest={};
		
		jsonRequest.functionName	=	form.functionName.value;
		
		
        $.ajax({
            type: "GET",
            url: "/admin/load-script?functionName="+jsonRequest.functionName,
        }).done(function(text) { 
        		
        		$(".functionReturnDiv").innerHTML(text);
                
        		})
        	.fail(function (jqXHR, exception) {
        	    if (jqXHR.status === 0) {
        	        alert('Not connect.\n Verify Network.');
        	    } else if (jqXHR.status == 404) {
        	        alert('Requested page not found. [404]');
        	    } else if (jqXHR.status == 500) {
        	        alert('Internal Server Error [500].');
        	    } else if (exception === 'parsererror') {
        	        alert('Requested JSON parse failed.');
        	    } else if (exception === 'timeout') {
        	        alert('Time out error.');
        	    } else if (exception === 'abort') {
        	        alert('Ajax request aborted.');
        	    } else {
        	        alert('Uncaught Error.\n' + jqXHR.responseText);
        	    }
        	});
		
		return true;
	});
});


function loadScriptFunctionRequest(url,tagID){
	
	var request  = get_XmlHttp( );

	var the_data = document.getElementById('functionName').value;
	var url = url+'?functionName='+the_data;
	//alert(url);
	request.open("GET", url, true); // sets the request
	// adds a header to tell the PHP script to recognize the data as is sent via POST
	request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	request.send();		// sends the request

	// Check request status
	// If the response is received completely, will be transferred to the HTML tag with tagID
	request.onreadystatechange = function() {
		if (request.readyState == 4) {
			
			//Getting response
			var response     = request.responseText;
			
			//clean elements in current page
			document.getElementById(tagID).innerHTML ='';
			
			//fill with new content
			document.getElementById(tagID).innerHTML = response;
			
		}else{
			//Getting response
			var response     = request.responseText;
		
		}
	};	
}

// Saves the script on db

function saveScriptRequest(url,divID){
	event.preventDefault();

//	var the_data = document.getElementById('files-preview').value;
	var the_data = editAreaLoader.getValue(divID);
	var fileName = document.getElementById('scriptsLoaded').value;
	var jsonRequest = {};
	
	jsonRequest['fileName'] = fileName;
	jsonRequest['content']  = the_data;
	  $.ajax({
          type: "POST",
          url: url,
          data: JSON.stringify(jsonRequest),
          contentType: "application/json; charset=utf-8",
          dataType: "json"
      }).done(function(text) { 
	    	  response="success";
	  		//alert(response+":"+JSON.stringify(text));
	  		location.reload();
  		}).fail(function (jqXHR, exception) {
    	    if (jqXHR.status === 0) {
    	        alert('Not connect.\n Verify Network.');
    	    } else if (jqXHR.status == 404) {
    	        alert('Requested page not found. [404]');
    	    } else if (jqXHR.status == 500) {
    	        alert('Internal Server Error [500].');
    	    } else if (exception === 'parsererror') {
    	        alert('Requested JSON parse failed.');
    	    } else if (exception === 'timeout') {
    	        alert('Time out error.');
    	    } else if (exception === 'abort') {
    	        alert('Ajax request aborted.');
    	    } else {
    	        alert('Uncaught Error.\n' + jqXHR.responseText);
    	    }
    	});
	
	
}


function reloadErrorLog(tagID){
	
	var request  = get_XmlHttp( );

	var url = "/admin/get-error-log";
	request.open("GET", url, true); // sets the request
	// adds a header to tell the PHP script to recognize the data as is sent via POST
	request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	request.send();		// sends the request

	// Check request status
	// If the response is received completely, will be transferred to the HTML tag with tagID
	request.onreadystatechange = function() {
		if (request.readyState == 4) {
			//Getting response
			var response     = request.responseText;
			//clean elements in current page
			document.getElementById(tagID).innerHTML ='';
			//fill with new content
			document.getElementById(tagID).innerHTML = response;
			
		}
	};
	
}