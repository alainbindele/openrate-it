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

function loadVotesRequest(url,divID){
	event.preventDefault();

//	var the_data = document.getElementById('files-preview').value;
	//var the_data = editAreaLoader.getValue("files-preview");
	var fileName = document.getElementById('votesLoaded').value;
	var jsonRequest = {};
	jsonRequest['fileName'] = fileName;
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


//Saves the votes on db

function updateVotesRequest(url,divID){
	event.preventDefault();
	
//	var the_data = document.getElementById('files-preview').value;
	var the_data = editAreaLoader.getValue(divID);
	var fileName = document.getElementById('votesLoaded').value;
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
